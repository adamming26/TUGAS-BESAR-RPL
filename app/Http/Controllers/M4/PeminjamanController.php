<?php

namespace App\Http\Controllers\M4;

use App\Http\Controllers\Controller;
use App\Models\BorrowItem;
use App\Models\BorrowRequest;
use App\Models\Facility;
use App\Services\ScheduleCheckService;
use App\Services\StockService;
use Carbon\Carbon;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;
use Illuminate\View\View;

class PeminjamanController extends Controller
{
    public function __construct(
        protected ScheduleCheckService $scheduleService,
        protected StockService $stockService
    ) {
    }

    /**
     * Form Peminjaman Barang atau Laboratorium (Bab 3.7 Halaman 4 & 5).
     */
    public function create(Request $request, ?string $jenis = 'barang', ?int $id = null): View
    {
        $normalizedJenis = $this->normalizeType($jenis);

        $facilities = Facility::where('type', $normalizedJenis)
            ->where('is_active', true)
            ->orderBy('name')
            ->get();

        $selectedId = $id ?? ($request->filled('facility_id') ? $request->integer('facility_id') : null);
        $selectedFacility = $selectedId
            ? Facility::where('type', $normalizedJenis)->where('is_active', true)->find($selectedId)
            : null;

        $upcomingLabSchedules = collect();
        if ($normalizedJenis === 'ruangan') {
            $upcomingLabSchedules = BorrowRequest::with('facility')
                ->where('type', 'ruangan')
                ->whereIn('status', ['menunggu', 'disetujui', 'dipinjam'])
                ->whereDate('borrow_date', '>=', Carbon::today())
                ->orderBy('borrow_date')
                ->orderBy('start_time')
                ->take(8)
                ->get(['id', 'facility_id', 'borrow_date', 'start_time', 'end_time', 'status']);
        }

        return view('mahasiswa.form-peminjaman', [
            'jenis' => $normalizedJenis,
            'facilities' => $facilities,
            'selectedFacility' => $selectedFacility,
            'upcomingLabSchedules' => $upcomingLabSchedules,
        ]);
    }

    /**
     * Form khusus Peminjaman Barang (Bab 3.7 Halaman 4).
     */
    public function createBarang(Request $request, ?int $id = null): View
    {
        return $this->create($request, 'barang', $id);
    }

    /**
     * Form khusus Peminjaman Laboratorium (Bab 3.7 Halaman 5).
     */
    public function createLab(Request $request, ?int $id = null): View
    {
        return $this->create($request, 'ruangan', $id);
    }

    /**
     * Simpan pengajuan peminjaman barang atau laboratorium.
     */
    public function store(Request $request): RedirectResponse
    {
        if ($request->filled('type')) {
            $request->merge(['type' => $this->normalizeType($request->input('type'))]);
        }

        $validated = $request->validate([
            'type' => ['required', 'in:barang,ruangan'],
            'facility_id' => ['required', 'integer', 'exists:facilities,id'],
            'quantity' => ['nullable', 'integer', 'min:1'],
            'participant_count' => ['nullable', 'integer', 'min:1'],
            'borrow_date' => ['required', 'date', 'after_or_equal:today'],
            'start_time' => ['required', 'date_format:H:i'],
            'end_time' => ['required_without:duration_hours', 'nullable', 'date_format:H:i', 'after:start_time'],
            'duration_hours' => ['required_without:end_time', 'nullable', 'integer', 'min:1', 'max:12'],
            'purpose' => ['required', 'string', 'min:10', 'max:1000'],
        ], [
            'type.required' => 'Jenis peminjaman wajib ditentukan.',
            'type.in' => 'Jenis peminjaman tidak valid.',
            'facility_id.required' => 'Fasilitas yang akan dipinjam wajib dipilih.',
            'facility_id.exists' => 'Fasilitas yang dipilih tidak ditemukan.',
            'quantity.integer' => 'Jumlah barang harus berupa angka.',
            'quantity.min' => 'Jumlah barang minimal 1 unit.',
            'participant_count.integer' => 'Jumlah peserta harus berupa angka.',
            'participant_count.min' => 'Jumlah peserta minimal 1 orang.',
            'borrow_date.required' => 'Tanggal peminjaman wajib diisi.',
            'borrow_date.date' => 'Format tanggal peminjaman tidak valid.',
            'borrow_date.after_or_equal' => 'Tanggal peminjaman minimal hari ini.',
            'start_time.required' => 'Waktu mulai wajib diisi.',
            'start_time.date_format' => 'Format waktu mulai harus HH:MM.',
            'end_time.required_without' => 'Waktu selesai wajib diisi.',
            'end_time.date_format' => 'Format waktu selesai harus HH:MM.',
            'end_time.after' => 'Waktu selesai harus lebih akhir dari waktu mulai.',
            'duration_hours.required_without' => 'Durasi peminjaman wajib dipilih.',
            'duration_hours.integer' => 'Durasi peminjaman harus berupa angka.',
            'duration_hours.min' => 'Durasi peminjaman minimal 1 jam.',
            'duration_hours.max' => 'Durasi peminjaman maksimal 12 jam.',
            'purpose.required' => 'Tujuan peminjaman wajib dijelaskan secara rinci.',
            'purpose.min' => 'Penjelasan tujuan peminjaman minimal 10 karakter.',
            'purpose.max' => 'Penjelasan tujuan peminjaman maksimal 1000 karakter.',
        ]);

        [$startTimeStr, $endTimeStr, $durationHours] = $this->resolveScheduleRange(
            $validated['start_time'],
            $validated['end_time'] ?? null,
            isset($validated['duration_hours']) ? (int) $validated['duration_hours'] : null
        );

        $borrowDate = Carbon::parse($validated['borrow_date'])->toDateString();

        try {
            $facilityName = DB::transaction(function () use ($validated, $borrowDate, $startTimeStr, $endTimeStr, $durationHours) {
                $facility = Facility::where('id', $validated['facility_id'])
                    ->lockForUpdate()
                    ->firstOrFail();

                if (!$facility->is_active) {
                    throw ValidationException::withMessages([
                        'facility_id' => "Fasilitas {$facility->name} sedang tidak aktif dan tidak dapat dipinjam.",
                    ]);
                }

                if ($facility->type !== $validated['type']) {
                    throw ValidationException::withMessages([
                        'facility_id' => 'Fasilitas yang dipilih tidak sesuai dengan jenis formulir peminjaman.',
                    ]);
                }

                $quantity = (int) ($validated['quantity'] ?? 1);
                $participantCount = (int) ($validated['participant_count'] ?? 1);

                if ($validated['type'] === 'barang') {
                    if (!$this->stockService->hasSufficientStock($facility, $quantity)) {
                        throw ValidationException::withMessages([
                            'quantity' => "Stok {$facility->name} tidak mencukupi (Tersedia: {$facility->available_stock} unit).",
                        ]);
                    }
                } else {
                    if ($facility->capacity > 0 && $participantCount > $facility->capacity) {
                        throw ValidationException::withMessages([
                            'participant_count' => "Jumlah peserta ({$participantCount}) melebihi kapasitas {$facility->name} (maksimal {$facility->capacity} orang).",
                        ]);
                    }

                    $isAvailable = $this->scheduleService->isAvailable(
                        $facility->id,
                        $borrowDate,
                        $startTimeStr,
                        $endTimeStr
                    );

                    if (!$isAvailable) {
                        throw ValidationException::withMessages([
                            'start_time' => "Jadwal {$facility->name} bentrok pada tanggal dan rentang waktu tersebut. Silakan pilih waktu lain.",
                        ]);
                    }
                }

                $borrowRequest = BorrowRequest::create([
                    'code' => $this->generateUniqueRequestCode(),
                    'user_id' => Auth::id(),
                    'type' => $validated['type'],
                    'facility_id' => $facility->id,
                    'borrow_date' => $borrowDate,
                    'start_time' => $startTimeStr,
                    'end_time' => $endTimeStr,
                    'duration_hours' => $durationHours,
                    'purpose' => trim($validated['purpose']),
                    'participant_count' => $validated['type'] === 'ruangan' ? $participantCount : 1,
                    'status' => 'menunggu',
                ]);

                if ($validated['type'] === 'barang') {
                    BorrowItem::create([
                        'borrow_request_id' => $borrowRequest->id,
                        'facility_id' => $facility->id,
                        'quantity' => $quantity,
                    ]);
                } else {
                    $this->scheduleService->logSchedule(
                        $facility->id,
                        $borrowRequest->id,
                        $borrowDate,
                        $startTimeStr,
                        $endTimeStr,
                        'terjadwal'
                    );
                }

                return $facility->name;
            });
        } catch (ValidationException $e) {
            $firstMessage = collect($e->errors())->flatten()->first() ?? 'Data pengajuan belum valid.';

            return back()
                ->withInput()
                ->withErrors($e->errors())
                ->with('error', $firstMessage);
        }

        return redirect()
            ->route('mahasiswa.peminjaman-saya')
            ->with('success', "Pengajuan peminjaman {$facilityName} berhasil dikirim dan menunggu persetujuan Dosen.");
    }

    /**
     * Daftar Peminjaman Saya (khusus mahasiswa yang login).
     */
    public function index(Request $request): View
    {
        $userId = Auth::id();
        $baseQuery = BorrowRequest::where('user_id', $userId);

        $statusCounts = [
            'semua' => (clone $baseQuery)->count(),
            'menunggu' => (clone $baseQuery)->where('status', 'menunggu')->count(),
            'disetujui' => (clone $baseQuery)->where('status', 'disetujui')->count(),
            'dipinjam' => (clone $baseQuery)->where('status', 'dipinjam')->count(),
            'dikembalikan' => (clone $baseQuery)->whereIn('status', ['dikembalikan', 'selesai'])->count(),
            'ditolak' => (clone $baseQuery)->where('status', 'ditolak')->count(),
        ];

        $query = BorrowRequest::with(['facility', 'items.facility', 'approver', 'returnRecord'])
            ->where('user_id', $userId);

        $validStatuses = ['menunggu', 'disetujui', 'dipinjam', 'dikembalikan', 'selesai', 'ditolak'];
        if ($request->filled('status') && in_array($request->status, $validStatuses, true)) {
            if ($request->status === 'dikembalikan') {
                $query->whereIn('status', ['dikembalikan', 'selesai']);
            } else {
                $query->where('status', $request->status);
            }
        }

        if ($request->filled('type') && in_array($request->type, ['barang', 'ruangan'], true)) {
            $query->where('type', $request->type);
        }

        if ($request->filled('search')) {
            $search = trim((string) $request->search);
            $query->where(function ($subQuery) use ($search) {
                $subQuery->where('code', 'like', "%{$search}%")
                    ->orWhere('purpose', 'like', "%{$search}%")
                    ->orWhereHas('facility', fn ($facilityQuery) => $facilityQuery->where('name', 'like', "%{$search}%"));
            });
        }


        $requests = $query->latest()->paginate(10)->withQueryString();

        // Data peminjaman aktif pengguna
        $activeRequest = BorrowRequest::with([
            'facility',
            'items.facility',
            'activeQrToken'
        ])
            ->where('user_id', $userId)
            ->whereIn('status', ['disetujui', 'dipinjam'])
            ->latest()
            ->first();

        $activeQr = $activeRequest?->activeQrToken;

        return view('mahasiswa.peminjaman-saya', compact(
            'requests',
            'statusCounts',
            'activeRequest',
            'activeQr'
        ));

            }

    /**
     * Detail Peminjaman Saya (pembatasan kepemilikan data di backend).
     */
    public function show(int $id): View
    {
        $requestData = BorrowRequest::with([
            'facility',
            'items.facility',
            'user',
            'approver',
            'returnRecord.lecturer',
            'scheduleLogs',
        ])->findOrFail($id);

        if ((int) $requestData->user_id !== (int) Auth::id()) {
            abort(403, 'Anda tidak berwenang mengakses detail peminjaman mahasiswa lain.');
        }

        $statusTimeline = $this->buildStatusTimeline($requestData);

        return view('mahasiswa.detail-peminjaman', compact('requestData', 'statusTimeline'));
    }

    /**
     * Status dan Riwayat Peminjaman Mahasiswa.
     */
    public function riwayat(Request $request): View
    {
        $userId = Auth::id();
        $baseQuery = BorrowRequest::where('user_id', $userId);

        $statusCounts = [
            'semua' => (clone $baseQuery)->count(),
            'aktif' => (clone $baseQuery)->whereIn('status', ['menunggu', 'disetujui', 'dipinjam'])->count(),
            'selesai' => (clone $baseQuery)->whereIn('status', ['dikembalikan', 'selesai'])->count(),
            'ditolak' => (clone $baseQuery)->where('status', 'ditolak')->count(),
        ];

        $query = BorrowRequest::with(['facility', 'items.facility', 'approver', 'returnRecord.lecturer'])
            ->where('user_id', $userId);

        if ($request->filled('type') && in_array($request->type, ['barang', 'ruangan'], true)) {
            $query->where('type', $request->type);
        }

        $validStatuses = ['menunggu', 'disetujui', 'dipinjam', 'dikembalikan', 'selesai', 'ditolak'];
        if ($request->filled('status') && in_array($request->status, $validStatuses, true)) {
            $query->where('status', $request->status);
        }

        if ($request->filled('date')) {
            $query->whereDate('borrow_date', $request->date);
        }

        if ($request->filled('search')) {
            $search = trim((string) $request->search);
            $query->where(function ($subQuery) use ($search) {
                $subQuery->where('code', 'like', "%{$search}%")
                    ->orWhere('purpose', 'like', "%{$search}%")
                    ->orWhereHas('facility', fn ($facilityQuery) => $facilityQuery->where('name', 'like', "%{$search}%"));
            });
        }

        $riwayat = $query->latest()->paginate(10)->withQueryString();

        return view('mahasiswa.riwayat', compact('riwayat', 'statusCounts'));
    }

    protected function normalizeType(?string $jenis): string
    {
        $value = strtolower(trim((string) $jenis));

        return in_array($value, ['ruangan', 'lab', 'laboratorium'], true) ? 'ruangan' : 'barang';
    }

    /**
     * Hitung start_time, end_time, dan duration_hours secara konsisten.
     *
     * @return array{0: string, 1: string, 2: int}
     */
    protected function resolveScheduleRange(string $startInput, ?string $endInput, ?int $durationInput): array
    {
        $start = Carbon::createFromFormat('H:i', $startInput);

        if ($endInput) {
            $end = Carbon::createFromFormat('H:i', $endInput);
            $durationHours = max(1, (int) ceil($start->diffInMinutes($end) / 60));

            return [$start->format('H:i:s'), $end->format('H:i:s'), $durationHours];
        }

        $durationHours = max(1, $durationInput ?? 1);
        $end = (clone $start)->addHours($durationHours);

        if ($end->toDateString() !== $start->toDateString()) {
            throw ValidationException::withMessages([
                'duration_hours' => 'Rentang waktu peminjaman tidak boleh melewati pergantian hari.',
            ]);
        }

        return [$start->format('H:i:s'), $end->format('H:i:s'), $durationHours];
    }

    protected function generateUniqueRequestCode(): string
    {
        do {
            $code = 'REQ-' . date('Ymd') . '-' . strtoupper(Str::random(5));
        } while (BorrowRequest::where('code', $code)->exists());

        return $code;
    }

    /**
     * Susun riwayat linimasa status pengajuan peminjaman mahasiswa.
     *
     * @return array<int, array{title: string, description: string, time: string|null, state: string}>
     */
    protected function buildStatusTimeline(BorrowRequest $requestData): array
    {
        $status = $requestData->status;
        $createdAt = $requestData->created_at ? $requestData->created_at->isoFormat('D MMM Y, HH:mm') : null;
        $updatedAt = $requestData->updated_at ? $requestData->updated_at->isoFormat('D MMM Y, HH:mm') : null;

        $timeline = [
            [
                'title' => 'Pengajuan Dikirim',
                'description' => 'Permohonan peminjaman tercatat dalam sistem dan diteruskan untuk pemeriksaan Dosen.',
                'time' => $createdAt,
                'state' => 'done',
            ],
        ];

        if ($status === 'menunggu') {
            $timeline[] = [
                'title' => 'Menunggu Persetujuan Dosen',
                'description' => 'Pengajuan sedang dalam antrean tinjauan Dosen Penanggung Jawab (Modul M5).',
                'time' => null,
                'state' => 'current',
            ];
            $timeline[] = [
                'title' => 'Serah Terima Fasilitas',
                'description' => 'Menunggu pengajuan disetujui sebelum proses serah terima dilakukan.',
                'time' => null,
                'state' => 'upcoming',
            ];
            $timeline[] = [
                'title' => 'Pengembalian Selesai',
                'description' => 'Fasilitas dikembalikan dan diverifikasi setelah masa peminjaman berakhir.',
                'time' => null,
                'state' => 'upcoming',
            ];

            return $timeline;
        }

        if ($status === 'ditolak') {
            $timeline[] = [
                'title' => 'Pengajuan Ditolak',
                'description' => $requestData->reject_reason ?: 'Pengajuan tidak disetujui oleh Dosen.',
                'time' => $updatedAt,
                'state' => 'rejected',
            ];

            return $timeline;
        }

        $approverName = $requestData->approver ? $requestData->approver->name : 'Dosen Pembimbing';
        $timeline[] = [
            'title' => 'Disetujui Dosen',
            'description' => "Pengajuan telah disetujui oleh {$approverName}.",
            'time' => $updatedAt,
            'state' => $status === 'disetujui' ? 'current' : 'done',
        ];

        if ($status === 'disetujui') {
            $timeline[] = [
                'title' => 'Menunggu Serah Terima',
                'description' => 'Siap memasuki proses serah terima fasilitas sesuai jadwal yang diajukan.',
                'time' => null,
                'state' => 'upcoming',
            ];
            $timeline[] = [
                'title' => 'Pengembalian Selesai',
                'description' => 'Menunggu proses peminjaman dan pengembalian selesai.',
                'time' => null,
                'state' => 'upcoming',
            ];

            return $timeline;
        }

        $timeline[] = [
            'title' => 'Sedang Dipinjam / Digunakan',
            'description' => 'Fasilitas telah diserahkan dan sedang digunakan sesuai jadwal peminjaman.',
            'time' => $updatedAt,
            'state' => $status === 'dipinjam' ? 'current' : 'done',
        ];

        $returnTime = $requestData->returnRecord?->return_date
            ? Carbon::parse($requestData->returnRecord->return_date)->isoFormat('D MMM Y, HH:mm')
            : $updatedAt;

        $timeline[] = [
            'title' => $status === 'selesai' ? 'Selesai' : 'Dikembalikan',
            'description' => $requestData->returnRecord
                ? 'Pengembalian telah dicatat dengan kondisi: ' . $requestData->returnRecord->condition . '.'
                : 'Pengembalian fasilitas telah dikonfirmasi selesai.',
            'time' => in_array($status, ['dikembalikan', 'selesai'], true) ? $returnTime : null,
            'state' => in_array($status, ['dikembalikan', 'selesai'], true) ? 'done' : 'upcoming',
        ];

        return $timeline;
    }
}
