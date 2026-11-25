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
        return app(\App\Http\Controllers\M4\PeminjamanController::class)->store($request);
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
        return app(\App\Http\Controllers\M4\PeminjamanController::class)->show((int) $id);
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
        return app(\App\Http\Controllers\M4\PeminjamanController::class)->riwayat($request);
    }
}
