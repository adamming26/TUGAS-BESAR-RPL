<?php

namespace App\Http\Controllers;

use App\Models\BorrowItem;
use App\Models\BorrowRequest;
use App\Models\Facility;
use App\Models\QrToken;
use App\Models\ScheduleLog;
use App\Services\QRCodeService;
use App\Services\ScheduleCheckService;
use App\Services\StockService;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Str;

class MahasiswaController extends Controller
{
    protected QRCodeService $qrCodeService;
    protected ScheduleCheckService $scheduleService;
    protected StockService $stockService;

    public function __construct(
        QRCodeService $qrCodeService,
        ScheduleCheckService $scheduleService,
        StockService $stockService
    ) {
        $this->qrCodeService = $qrCodeService;
        $this->scheduleService = $scheduleService;
        $this->stockService = $stockService;
    }

    /**
     * Beranda Mahasiswa (Figma Page 4).
     */
    public function dashboard(Request $request)
    {
        $user = Auth::user();

        // 1. Data Fasilitas untuk Tabel Ketersediaan
        $facilitiesQuery = Facility::where('is_active', true);
        if ($request->filled('type') && in_array($request->type, ['barang', 'ruangan'])) {
            $facilitiesQuery->where('type', $request->type);
        }
        if ($request->filled('search')) {
            $search = $request->search;
            $facilitiesQuery->where('name', 'like', "%{$search}%");
        }
        $facilities = $facilitiesQuery->orderBy('type')->orderBy('name')->get();

        // 2. Status Pengajuan Terbaru Pengguna
        $recentRequests = BorrowRequest::with(['facility', 'items.facility'])
            ->where('user_id', $user->id)
            ->latest()
            ->take(4)
            ->get();

        // 3. QR Code Peminjaman Aktif (jika ada yang disetujui atau sedang dipinjam)
        $activeRequest = BorrowRequest::with(['facility', 'items.facility', 'activeQrToken'])
            ->where('user_id', $user->id)
            ->whereIn('status', ['disetujui', 'dipinjam'])
            ->latest()
            ->first();

        $activeQr = null;
        if ($activeRequest) {
            $activeQr = $activeRequest->activeQrToken;
            // Jika token kedaluwarsa atau belum ada, terbitkan otomatis
            if (!$activeQr || $activeQr->isExpired()) {
                $type = $activeRequest->status === 'dipinjam' ? 'pengembalian' : 'penyerahan';
                $activeQr = $this->qrCodeService->generateToken($activeRequest, $type);
            }
        }

        return view('mahasiswa.dashboard', compact('facilities', 'recentRequests', 'activeRequest', 'activeQr'));
    }

    /**
     * Form Pengajuan Peminjaman Barang & Lab (Figma Pages 5 & 6).
     */
    public function formPeminjaman($jenis = 'barang', $id = null)
    {
        $jenis = in_array($jenis, ['barang', 'ruangan', 'lab']) ? ($jenis === 'lab' ? 'ruangan' : $jenis) : 'barang';

        $facilities = Facility::where('type', $jenis)
            ->where('is_active', true)
            ->orderBy('name')
            ->get();

        $selectedFacility = $id ? Facility::find($id) : null;

        return view('mahasiswa.form-peminjaman', compact('jenis', 'facilities', 'selectedFacility'));
    }

    /**
     * Simpan Pengajuan Peminjaman.
     */
    public function storePeminjaman(Request $request)
    {
        $validated = $request->validate([
            'type' => ['required', 'in:barang,ruangan'],
            'facility_id' => ['required', 'exists:facilities,id'],
            'quantity' => ['nullable', 'integer', 'min:1'],
            'participant_count' => ['nullable', 'integer', 'min:1'],
            'borrow_date' => ['required', 'date', 'after_or_equal:today'],
            'start_time' => ['required', 'date_format:H:i'],
            'duration_hours' => ['required', 'integer', 'min:1', 'max:8'],
            'purpose' => ['required', 'string', 'min:10'],
        ], [
            'facility_id.required' => 'Fasilitas yang akan dipinjam wajib dipilih.',
            'borrow_date.required' => 'Tanggal peminjaman wajib diisi.',
            'borrow_date.after_or_equal' => 'Tanggal peminjaman minimal hari ini.',
            'start_time.required' => 'Jam mulai wajib diisi.',
            'duration_hours.required' => 'Durasi peminjaman wajib dipilih.',
            'purpose.required' => 'Tujuan peminjaman wajib dijelaskan secara rinci.',
            'purpose.min' => 'Penjelasan tujuan peminjaman minimal 10 karakter.',
        ]);

        $facility = Facility::findOrFail($validated['facility_id']);
        $startTime = Carbon::createFromFormat('H:i', $validated['start_time']);
        $endTime = (clone $startTime)->addHours((int)$validated['duration_hours'])->format('H:i:s');
        $startTimeStr = $startTime->format('H:i:s');

        // Validasi khusus untuk Barang (Stok)
        if ($validated['type'] === 'barang') {
            $qty = $validated['quantity'] ?? 1;
            if (!$this->stockService->hasSufficientStock($facility, $qty)) {
                return back()->withInput()->with('error', "Stok {$facility->name} tidak mencukupi (Tersedia: {$facility->available_stock} unit).");
            }
        }

        // Validasi khusus untuk Ruangan / Lab (Bentrok Jadwal)
        if ($validated['type'] === 'ruangan') {
            $isAvailable = $this->scheduleService->isAvailable(
                $facility->id,
                $validated['borrow_date'],
                $startTimeStr,
                $endTime
            );

            if (!$isAvailable) {
                return back()->withInput()->with('error', "Ruangan {$facility->name} sudah dipesan pada tanggal dan jam tersebut. Silakan pilih waktu lain.");
            }
        }

        $code = 'REQ-' . date('Ymd') . '-' . strtoupper(Str::random(5));

        $borrowRequest = BorrowRequest::create([
            'code' => $code,
            'user_id' => Auth::id(),
            'type' => $validated['type'],
            'facility_id' => $facility->id,
            'borrow_date' => $validated['borrow_date'],
            'start_time' => $startTimeStr,
            'end_time' => $endTime,
            'duration_hours' => $validated['duration_hours'],
            'purpose' => $validated['purpose'],
            'participant_count' => $validated['type'] === 'ruangan' ? ($validated['participant_count'] ?? 1) : 1,
            'status' => 'menunggu',
        ]);

        // Catat item barang
        if ($validated['type'] === 'barang') {
            BorrowItem::create([
                'borrow_request_id' => $borrowRequest->id,
                'facility_id' => $facility->id,
                'quantity' => $validated['quantity'] ?? 1,
            ]);
        } else {
            // Catat log jadwal reservasi lab
            $this->scheduleService->logSchedule(
                $facility->id,
                $borrowRequest->id,
                $validated['borrow_date'],
                $startTimeStr,
                $endTime,
                'terjadwal'
            );
        }

        return redirect()->route('mahasiswa.peminjaman-saya')->with('success', "Pengajuan peminjaman {$facility->name} berhasil dikirim! Menunggu persetujuan Dosen.");
    }

    /**
     * Halaman Peminjaman Saya (Figma Page 7).
     */
    public function peminjamanSaya(Request $request)
    {
        $user = Auth::user();

        $query = BorrowRequest::with(['facility', 'items.facility', 'activeQrToken'])
            ->where('user_id', $user->id);

        if ($request->filled('status') && in_array($request->status, ['menunggu', 'disetujui', 'dipinjam', 'ditolak'])) {
            $query->where('status', $request->status);
        }

        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('code', 'like', "%{$search}%")
                  ->orWhereHas('facility', fn($sub) => $sub->where('name', 'like', "%{$search}%"));
            });
        }

        $requests = $query->latest()->get();

        // Cari transaksi aktif untuk widget QR Code
        $activeRequest = BorrowRequest::with(['facility', 'items.facility', 'activeQrToken'])
            ->where('user_id', $user->id)
            ->whereIn('status', ['disetujui', 'dipinjam'])
            ->latest()
            ->first();

        $activeQr = null;
        if ($activeRequest) {
            $activeQr = $activeRequest->activeQrToken;
            if (!$activeQr || $activeQr->isExpired()) {
                $type = $activeRequest->status === 'dipinjam' ? 'pengembalian' : 'penyerahan';
                $activeQr = $this->qrCodeService->generateToken($activeRequest, $type);
            }
        }

        return view('mahasiswa.peminjaman-saya', compact('requests', 'activeRequest', 'activeQr'));
    }

    /**
     * Detail Peminjaman Tertentu.
     */
    public function detailPeminjaman($id)
    {
        $requestData = BorrowRequest::with(['facility', 'items.facility', 'user', 'approver', 'activeQrToken', 'returnRecord.lecturer'])
            ->where('user_id', Auth::id())
            ->findOrFail($id);

        return view('mahasiswa.detail-peminjaman', compact('requestData'));
    }

    /**
     * Tampilkan QR Code Peminjaman (15 Menit).
     */
    public function showQrCode($id)
    {
        $borrowRequest = BorrowRequest::with(['facility', 'items.facility', 'activeQrToken'])
            ->where('user_id', Auth::id())
            ->whereIn('status', ['disetujui', 'dipinjam'])
            ->findOrFail($id);

        $qrToken = $borrowRequest->activeQrToken;
        if (!$qrToken || $qrToken->isExpired()) {
            $type = $borrowRequest->status === 'dipinjam' ? 'pengembalian' : 'penyerahan';
            $qrToken = $this->qrCodeService->generateToken($borrowRequest, $type);
        }

        return view('mahasiswa.qr-code', compact('borrowRequest', 'qrToken'));
    }

    /**
     * Perbarui / Terbitkan Ulang QR Code Baru (Jika Kedaluwarsa).
     */
    public function generateQr($id)
    {
        $borrowRequest = BorrowRequest::where('user_id', Auth::id())
            ->whereIn('status', ['disetujui', 'dipinjam'])
            ->findOrFail($id);

        $type = $borrowRequest->status === 'dipinjam' ? 'pengembalian' : 'penyerahan';
        $this->qrCodeService->generateToken($borrowRequest, $type);

        return back()->with('success', 'Token QR Code baru berhasil diterbitkan (Berlaku 15 menit).');
    }

    /**
     * Ajukan Pengembalian Fasilitas.
     */
    public function ajukanPengembalian($id)
    {
        $borrowRequest = BorrowRequest::where('user_id', Auth::id())
            ->where('status', 'dipinjam')
            ->findOrFail($id);

        // Terbitkan QR token pengembalian
        $this->qrCodeService->generateToken($borrowRequest, 'pengembalian');

        return redirect()->route('mahasiswa.peminjaman-saya')->with('success', 'QR Code pengembalian telah diterbitkan. Tunjukkan kepada Dosen saat mengembalikan fasilitas.');
    }

    /**
     * Riwayat Peminjaman Mahasiswa (Figma Page 8).
     */
    public function riwayat(Request $request)
    {
        $user = Auth::user();

        $query = BorrowRequest::with(['facility', 'approver', 'returnRecord.lecturer'])
            ->where('user_id', $user->id)
            ->whereIn('status', ['dikembalikan', 'selesai', 'ditolak']);

        if ($request->filled('type') && in_array($request->type, ['barang', 'ruangan'])) {
            $query->where('type', $request->type);
        }

        if ($request->filled('status') && in_array($request->status, ['dikembalikan', 'selesai', 'ditolak'])) {
            $query->where('status', $request->status);
        }

        if ($request->filled('date')) {
            $query->whereDate('borrow_date', $request->date);
        }

        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('code', 'like', "%{$search}%")
                  ->orWhereHas('facility', fn($sub) => $sub->where('name', 'like', "%{$search}%"));
            });
        }

        $riwayat = $query->latest()->paginate(10);

        return view('mahasiswa.riwayat', compact('riwayat'));
    }
}
