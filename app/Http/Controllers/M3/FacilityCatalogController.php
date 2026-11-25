<?php

namespace App\Http\Controllers\M3;

use App\Http\Controllers\Controller;
use App\Models\BorrowRequest;
use App\Models\Facility;
use App\Models\ScheduleLog;
use App\Services\ScheduleCheckService;
use Carbon\Carbon;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Str;
use Illuminate\View\View;

class FacilityCatalogController extends Controller
{
    /**
     * Slot waktu standar operasional kampus untuk ringkasan waktu tersedia.
     */
    private const DAILY_SLOTS = [
        ['start' => '08:00', 'end' => '10:00', 'label' => '08:00 - 10:00'],
        ['start' => '10:00', 'end' => '12:00', 'label' => '10:00 - 12:00'],
        ['start' => '12:00', 'end' => '14:00', 'label' => '12:00 - 14:00'],
        ['start' => '14:00', 'end' => '16:00', 'label' => '14:00 - 16:00'],
        ['start' => '16:00', 'end' => '18:00', 'label' => '16:00 - 18:00'],
    ];

    /**
     * Halaman utama Katalog Fasilitas (Semua / Barang / Laboratorium).
     */
    public function index(Request $request): View
    {
        return $this->renderCatalog($request, null, 'Katalog Fasilitas', 'semua');
    }

    /**
     * Halaman khusus Katalog Barang.
     */
    public function barang(Request $request): View
    {
        return $this->renderCatalog($request, 'barang', 'Katalog Barang', 'barang');
    }

    /**
     * Halaman khusus Katalog Laboratorium.
     */
    public function laboratorium(Request $request): View
    {
        return $this->renderCatalog($request, 'ruangan', 'Katalog Laboratorium', 'laboratorium');
    }

    /**
     * Halaman Detail Fasilitas, Informasi Penggunaan, dan Jadwal.
     */
    public function show(Request $request, Facility $facility): View
    {
        $selectedDate = $this->parseDateOrDefault($request->query('tanggal'));
        $today = Carbon::today()->toDateString();

        // Jadwal penggunaan aktif pada tanggal terpilih
        $schedulesOnDate = BorrowRequest::with('user')
            ->where('facility_id', $facility->id)
            ->whereDate('borrow_date', $selectedDate)
            ->whereIn('status', ['menunggu', 'disetujui', 'dipinjam'])
            ->orderBy('start_time')
            ->get();

        // Jadwal mendatang yang relevan (hari ini ke depan, maks 8 item)
        $upcomingSchedules = BorrowRequest::with('user')
            ->where('facility_id', $facility->id)
            ->whereDate('borrow_date', '>=', $today)
            ->whereIn('status', ['menunggu', 'disetujui', 'dipinjam'])
            ->orderBy('borrow_date')
            ->orderBy('start_time')
            ->take(8)
            ->get();

        // Log jadwal resmi dari schedule_logs untuk referensi tambahan
        $scheduleLogs = ScheduleLog::with('borrowRequest')
            ->where('facility_id', $facility->id)
            ->whereDate('date', '>=', $today)
            ->whereIn('status', ['terjadwal', 'berlangsung'])
            ->orderBy('date')
            ->orderBy('start_time')
            ->take(6)
            ->get();

        // Slot waktu tersedia pada tanggal terpilih menggunakan ScheduleCheckService
        $timeSlots = $this->buildDailyTimeSlots($facility->id, $selectedDate);
        $freeWindows = $this->computeFreeTimeWindows($schedulesOnDate);

        // Statistik penggunaan fasilitas
        $usageStats = [
            'total_completed' => BorrowRequest::where('facility_id', $facility->id)
                ->whereIn('status', ['dikembalikan', 'selesai'])
                ->count(),
            'active_bookings' => BorrowRequest::where('facility_id', $facility->id)
                ->whereIn('status', ['menunggu', 'disetujui', 'dipinjam'])
                ->count(),
            'today_bookings' => BorrowRequest::where('facility_id', $facility->id)
                ->whereDate('borrow_date', $today)
                ->whereIn('status', ['menunggu', 'disetujui', 'dipinjam'])
                ->count(),
        ];

        // Pemeriksaan bentrok jadwal interaktif jika pengguna mengirimkan parameter waktu
        $scheduleCheck = null;
        $hasCheckParams = $request->filled('jam_mulai') || $request->filled('durasi') || $request->filled('cek_jadwal');
        if ($hasCheckParams) {
            $scheduleCheck = $this->evaluateScheduleCheck(
                $facility,
                $selectedDate,
                (string) $request->query('jam_mulai', '08:00'),
                (int) $request->query('durasi', 2),
                $request->query('jam_selesai')
            );
        }

        $imageUrl = $this->resolveFacilityImage($facility);
        $availability = $this->resolveAvailabilityStatus($facility, $today);
        $borrowAction = $this->resolveBorrowAction(
            $facility,
            $scheduleCheck['date'] ?? $selectedDate,
            $scheduleCheck['start_time_short'] ?? $request->query('jam_mulai'),
            $scheduleCheck['duration'] ?? ($request->filled('durasi') ? (int) $request->query('durasi') : null)
        );

        return view('m3.fasilitas.show', compact(
            'facility',
            'imageUrl',
            'availability',
            'borrowAction',
            'selectedDate',
            'schedulesOnDate',
            'upcomingSchedules',
            'scheduleLogs',
            'timeSlots',
            'freeWindows',
            'usageStats',
            'scheduleCheck'
        ));
    }

    /**
     * Pemeriksaan bentrok jadwal fasilitas (mendukung JSON maupun pengalihan halaman detail).
     */
    public function checkSchedule(Request $request, Facility $facility): JsonResponse|\Illuminate\Http\RedirectResponse
    {
        $validated = $request->validate([
            'tanggal' => ['required', 'date'],
            'jam_mulai' => ['required', 'date_format:H:i'],
            'durasi' => ['nullable', 'integer', 'min:1', 'max:8'],
            'jam_selesai' => ['nullable', 'date_format:H:i'],
        ], [
            'tanggal.required' => 'Tanggal pemeriksaan jadwal wajib diisi.',
            'tanggal.date' => 'Format tanggal tidak valid.',
            'jam_mulai.required' => 'Jam mulai wajib diisi.',
            'jam_mulai.date_format' => 'Format jam mulai harus JJ:MM.',
            'durasi.integer' => 'Durasi harus berupa angka jam.',
            'durasi.min' => 'Durasi minimal 1 jam.',
            'durasi.max' => 'Durasi maksimal 8 jam.',
        ]);

        $result = $this->evaluateScheduleCheck(
            $facility,
            $validated['tanggal'],
            $validated['jam_mulai'],
            (int) ($validated['durasi'] ?? 2),
            $validated['jam_selesai'] ?? null
        );

        if ($request->expectsJson()) {
            return response()->json([
                'facility_id' => $facility->id,
                'facility_name' => $facility->name,
                'available' => $result['is_available'],
                'has_conflict' => $result['has_conflict'],
                'stock_sufficient' => $result['stock_sufficient'],
                'date' => $result['date'],
                'start_time' => $result['start_time_short'],
                'end_time' => $result['end_time_short'],
                'message' => $result['message'],
                'conflicts' => $result['conflicts']->map(fn (BorrowRequest $item) => [
                    'id' => $item->id,
                    'code' => $item->code,
                    'start_time' => substr((string) $item->start_time, 0, 5),
                    'end_time' => substr((string) $item->end_time, 0, 5),
                    'status' => $item->status,
                ])->values(),
            ], $result['valid_range'] ? 200 : 422);
        }

        return redirect()->route('fasilitas.show', [
            'facility' => $facility->id,
            'tanggal' => $result['date'],
            'jam_mulai' => $result['start_time_short'],
            'durasi' => $result['duration'],
            'cek_jadwal' => 1,
        ]);
    }

    /**
     * Render katalog fasilitas dengan filter pencarian, jenis, kondisi, dan status ketersediaan.
     */
    private function renderCatalog(Request $request, ?string $forcedType, string $pageTitle, string $activeScope): View
    {
        $rawType = $forcedType ?? $request->query('type', $request->query('kategori'));
        $normalizedType = match (strtolower((string) $rawType)) {
            'barang' => 'barang',
            'ruangan', 'lab', 'laboratorium' => 'ruangan',
            default => null,
        };

        $search = trim((string) $request->query('search', ''));
        $condition = $request->query('condition');
        $statusFilter = $request->query('status');
        $today = Carbon::today()->toDateString();

        $query = Facility::where('is_active', true);

        if ($normalizedType) {
            $query->where('type', $normalizedType);
        }

        if ($search !== '') {
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                    ->orWhere('code', 'like', "%{$search}%")
                    ->orWhere('description', 'like', "%{$search}%");
            });
        }

        if ($condition && in_array($condition, ['Baik', 'Rusak Ringan', 'Rusak Berat'], true)) {
            $query->where('condition', $condition);
        } else {
            $condition = null;
        }

        if ($statusFilter === 'tersedia') {
            $query->where('condition', '!=', 'Rusak Berat')
                ->where(function ($q) {
                    $q->where('type', 'ruangan')
                        ->orWhere(function ($sub) {
                            $sub->where('type', 'barang')
                                ->where('available_stock', '>', 0);
                        });
                });
        } elseif ($statusFilter === 'terbatas') {
            $query->where('type', 'barang')
                ->where('available_stock', 1);
        } elseif ($statusFilter === 'habis') {
            $query->where(function ($q) {
                $q->where(function ($sub) {
                    $sub->where('type', 'barang')
                        ->where('available_stock', '<=', 0);
                })->orWhere('condition', 'Rusak Berat');
            });
        } else {
            $statusFilter = null;
        }

        $facilities = $query
            ->withCount([
                'borrowRequests as active_schedule_count' => function ($q) use ($today) {
                    $q->whereDate('borrow_date', '>=', $today)
                        ->whereIn('status', ['menunggu', 'disetujui', 'dipinjam']);
                },
            ])
            ->orderBy('type')
            ->orderBy('name')
            ->paginate(9)
            ->withQueryString();

        // Lengkapi metadata tampilan (gambar, status ketersediaan, tautan pengajuan M4)
        $facilities->getCollection()->transform(function (Facility $facility) use ($today) {
            $facility->setAttribute('catalog_image', $this->resolveFacilityImage($facility));
            $facility->setAttribute('catalog_status', $this->resolveAvailabilityStatus($facility, $today));
            $facility->setAttribute('borrow_action', $this->resolveBorrowAction($facility));

            return $facility;
        });

        // Ringkasan statistik katalog aktif
        $summary = [
            'total' => Facility::where('is_active', true)->count(),
            'barang_count' => Facility::where('is_active', true)->where('type', 'barang')->count(),
            'barang_available_units' => (int) Facility::where('is_active', true)->where('type', 'barang')->sum('available_stock'),
            'lab_count' => Facility::where('is_active', true)->where('type', 'ruangan')->count(),
            'lab_total_capacity' => (int) Facility::where('is_active', true)->where('type', 'ruangan')->sum('capacity'),
        ];

        return view('m3.fasilitas.index', compact(
            'facilities',
            'pageTitle',
            'activeScope',
            'normalizedType',
            'search',
            'condition',
            'statusFilter',
            'summary'
        ));
    }

    /**
     * Evaluasi bentrok jadwal menggunakan ScheduleCheckService dari M1.
     */
    private function evaluateScheduleCheck(
        Facility $facility,
        string $dateInput,
        string $startTimeInput,
        int $durationHours = 2,
        ?string $endTimeInput = null
    ): array {
        $parsedDate = $this->parseDateOrDefault($dateInput);
        $duration = max(1, min(8, $durationHours));

        try {
            $start = Carbon::createFromFormat('H:i', substr(trim($startTimeInput), 0, 5));
        } catch (\Throwable) {
            $start = Carbon::createFromFormat('H:i', '08:00');
        }

        if ($endTimeInput && preg_match('/^\d{2}:\d{2}$/', trim($endTimeInput))) {
            $end = Carbon::createFromFormat('H:i', trim($endTimeInput));
            $validRange = $end->gt($start);
            $duration = max(1, (int) ceil($start->diffInMinutes($end, false) / 60));
        } else {
            $end = $start->copy()->addHours($duration);
            $validRange = $end->isSameDay($start);
        }

        $startShort = $start->format('H:i');
        $endShort = $validRange ? $end->format('H:i') : '23:59';
        $startDb = $startShort . ':00';
        $endDb = $endShort . ':00';

        $isPastDate = Carbon::parse($parsedDate)->lt(Carbon::today());
        $stockSufficient = $facility->isRuangan() || $facility->available_stock > 0;
        $facilityUsable = $facility->is_active && $facility->condition !== 'Rusak Berat';

        if (!$validRange) {
            return [
                'valid_range' => false,
                'is_available' => false,
                'has_conflict' => false,
                'stock_sufficient' => $stockSufficient,
                'facility_usable' => $facilityUsable,
                'is_past_date' => $isPastDate,
                'date' => $parsedDate,
                'start_time_short' => $startShort,
                'end_time_short' => $endShort,
                'duration' => $duration,
                'conflicts' => collect(),
                'message' => 'Rentang waktu melebihi batas hari yang sama. Pilih jam mulai atau durasi lebih awal.',
            ];
        }

        // Integrasi langsung dengan ScheduleCheckService::isAvailable dari M1
        $scheduleAvailable = ScheduleCheckService::isAvailable(
            $facility->id,
            $parsedDate,
            $startDb,
            $endDb
        );

        $conflicts = collect();
        if (!$scheduleAvailable) {
            $conflicts = BorrowRequest::where('facility_id', $facility->id)
                ->whereDate('borrow_date', $parsedDate)
                ->whereIn('status', ['menunggu', 'disetujui', 'dipinjam'])
                ->where('start_time', '<', $endDb)
                ->where('end_time', '>', $startDb)
                ->orderBy('start_time')
                ->get();
        }

        $overallAvailable = $scheduleAvailable && $facilityUsable && ($facility->isRuangan() ? true : $stockSufficient);

        if (!$facilityUsable) {
            $message = 'Fasilitas sedang nonaktif atau dalam kondisi Rusak Berat sehingga tidak dapat dipinjam.';
        } elseif (!$scheduleAvailable) {
            $message = "Jadwal bentrok! {$facility->name} sudah memiliki pengajuan/penggunaan aktif pada {$startShort} - {$endShort}.";
        } elseif ($facility->isBarang() && !$stockSufficient) {
            $message = "Stok {$facility->name} saat ini habis (0/{$facility->total_stock} unit tersedia).";
        } elseif ($isPastDate) {
            $message = "Tidak ada bentrok jadwal tercatat pada {$startShort} - {$endShort}, namun tanggal yang dipilih sudah berlalu.";
        } else {
            $message = "Jadwal tersedia! {$facility->name} dapat diajukan pada {$startShort} - {$endShort}.";
        }

        return [
            'valid_range' => true,
            'is_available' => $overallAvailable,
            'schedule_available' => $scheduleAvailable,
            'has_conflict' => !$scheduleAvailable,
            'stock_sufficient' => $stockSufficient,
            'facility_usable' => $facilityUsable,
            'is_past_date' => $isPastDate,
            'date' => $parsedDate,
            'start_time_short' => $startShort,
            'end_time_short' => $endShort,
            'duration' => $duration,
            'conflicts' => $conflicts,
            'message' => $message,
        ];
    }

    /**
     * Susun status ketersediaan slot waktu harian menggunakan ScheduleCheckService.
     */
    private function buildDailyTimeSlots(int $facilityId, string $date): array
    {
        return array_map(function (array $slot) use ($facilityId, $date) {
            $available = ScheduleCheckService::isAvailable(
                $facilityId,
                $date,
                $slot['start'] . ':00',
                $slot['end'] . ':00'
            );

            return [
                'start' => $slot['start'],
                'end' => $slot['end'],
                'label' => $slot['label'],
                'available' => $available,
            ];
        }, self::DAILY_SLOTS);
    }

    /**
     * Hitung rentang waktu kosong pada jam operasional kampus (07:00 - 18:00).
     */
    private function computeFreeTimeWindows($schedules): array
    {
        $dayStart = 7 * 60;
        $dayEnd = 18 * 60;

        $booked = $schedules->map(function (BorrowRequest $schedule) {
            $startParts = explode(':', substr((string) $schedule->start_time, 0, 5));
            $endParts = explode(':', substr((string) $schedule->end_time, 0, 5));

            return [
                'start' => ((int) $startParts[0]) * 60 + ((int) ($startParts[1] ?? 0)),
                'end' => ((int) $endParts[0]) * 60 + ((int) ($endParts[1] ?? 0)),
            ];
        })->sortBy('start')->values();

        $cursor = $dayStart;
        $windows = [];

        foreach ($booked as $interval) {
            $start = max($dayStart, $interval['start']);
            $end = min($dayEnd, $interval['end']);

            if ($start > $cursor) {
                $windows[] = sprintf('%02d:%02d - %02d:%02d', intdiv($cursor, 60), $cursor % 60, intdiv($start, 60), $start % 60);
            }
            $cursor = max($cursor, $end);
        }

        if ($cursor < $dayEnd) {
            $windows[] = sprintf('%02d:%02d - %02d:%02d', intdiv($cursor, 60), $cursor % 60, intdiv($dayEnd, 60), $dayEnd % 60);
        }

        return $windows;
    }

    /**
     * Resolve gambar fasilitas jika tersedia (atribut model atau file statis per kode fasilitas).
     */
    private function resolveFacilityImage(Facility $facility): ?string
    {
        foreach (['image_url', 'image', 'image_path', 'photo'] as $attribute) {
            $value = $facility->getAttribute($attribute);
            if (is_string($value) && trim($value) !== '') {
                $trimmed = trim($value);
                if (Str::startsWith($trimmed, ['http://', 'https://', '/'])) {
                    return $trimmed;
                }

                return asset($trimmed);
            }
        }

        $slugCode = Str::lower((string) $facility->code);
        foreach (['jpg', 'jpeg', 'png', 'webp'] as $ext) {
            $relative = "images/facilities/{$slugCode}.{$ext}";
            if (file_exists(public_path($relative))) {
                return asset($relative);
            }

            $storageRelative = "storage/facilities/{$slugCode}.{$ext}";
            if (file_exists(public_path($storageRelative))) {
                return asset($storageRelative);
            }
        }

        return null;
    }

    /**
     * Tentukan status ketersediaan fasilitas untuk badge dan CTA.
     */
    private function resolveAvailabilityStatus(Facility $facility, string $today): array
    {
        if (!$facility->is_active) {
            return [
                'label' => 'Nonaktif',
                'badge_status' => 'nonaktif',
                'borrowable' => false,
            ];
        }

        if ($facility->condition === 'Rusak Berat') {
            return [
                'label' => 'Rusak Berat',
                'badge_status' => 'rusak berat',
                'borrowable' => false,
            ];
        }

        if ($facility->isBarang()) {
            if ($facility->available_stock > 1) {
                return [
                    'label' => 'Tersedia',
                    'badge_status' => 'tersedia',
                    'borrowable' => true,
                ];
            }

            if ($facility->available_stock === 1) {
                return [
                    'label' => 'Terbatas',
                    'badge_status' => 'terbatas',
                    'borrowable' => true,
                ];
            }

            return [
                'label' => 'Habis',
                'badge_status' => 'habis',
                'borrowable' => false,
            ];
        }

        $hasTodaySchedule = BorrowRequest::where('facility_id', $facility->id)
            ->whereDate('borrow_date', $today)
            ->whereIn('status', ['menunggu', 'disetujui', 'dipinjam'])
            ->exists();

        if ($hasTodaySchedule) {
            return [
                'label' => 'Terjadwal Hari Ini',
                'badge_status' => 'terjadwal',
                'borrowable' => true,
            ];
        }

        return [
            'label' => 'Tersedia',
            'badge_status' => 'tersedia',
            'borrowable' => true,
        ];
    }

    /**
     * Tentukan URL pengalihan ke form peminjaman milik M4 menggunakan facility_id.
     */
    private function resolveBorrowAction(
        Facility $facility,
        ?string $date = null,
        ?string $startTime = null,
        ?int $duration = null
    ): array {
        $jenisParam = $facility->isRuangan() ? 'lab' : 'barang';
        $extraParams = array_filter([
            'facility_id' => $facility->id,
            'borrow_date' => $date,
            'start_time' => $startTime,
            'duration_hours' => $duration,
        ], fn ($value) => $value !== null && $value !== '');

        $m4Url = null;
        if (Route::has('mahasiswa.form-peminjaman')) {
            $m4Url = route('mahasiswa.form-peminjaman', array_merge([
                'jenis' => $jenisParam,
                'id' => $facility->id,
            ], $extraParams));
        } elseif (Route::has('mahasiswa.peminjaman.create')) {
            $m4Url = route('mahasiswa.peminjaman.create', array_merge([
                'type' => $facility->type,
            ], $extraParams));
        } elseif (Route::has('mahasiswa.peminjaman.form')) {
            $m4Url = route('mahasiswa.peminjaman.form', array_merge([
                'type' => $facility->type,
            ], $extraParams));
        }

        return [
            'm4_available' => $m4Url !== null,
            'url' => $m4Url,
            'label' => $facility->isRuangan() ? 'Ajukan Izin Lab' : 'Ajukan Peminjaman',
        ];
    }

    private function parseDateOrDefault(?string $date): string
    {
        if (!$date) {
            return Carbon::today()->toDateString();
        }

        try {
            return Carbon::parse($date)->toDateString();
        } catch (\Throwable) {
            return Carbon::today()->toDateString();
        }
    }
}
