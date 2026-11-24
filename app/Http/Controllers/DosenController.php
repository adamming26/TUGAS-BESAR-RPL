<?php

namespace App\Http\Controllers;

use App\Models\BorrowRequest;
use App\Models\QrToken;
use App\Models\ReturnRecord;
use App\Models\ScheduleLog;
use App\Services\QRCodeService;
use App\Services\StockService;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class DosenController extends Controller
{
    protected QRCodeService $qrCodeService;
    protected StockService $stockService;

    public function __construct(QRCodeService $qrCodeService, StockService $stockService)
    {
        $this->qrCodeService = $qrCodeService;
        $this->stockService = $stockService;
    }

    /**
     * Dashboard Dosen Terpadu (Figma Page 9).
     */
    public function dashboard(Request $request)
    {
        // 1. Pengajuan Masuk (Menunggu Keputusan)
        $pendingRequests = BorrowRequest::with(['user', 'facility', 'items.facility'])
            ->where('status', 'menunggu')
            ->latest()
            ->get();

        // Pengajuan terpilih untuk panel detail (default ke yang pertama)
        $selectedId = $request->get('selected_id', $pendingRequests->first()?->id);
        $selectedRequest = $selectedId
            ? BorrowRequest::with(['user', 'facility', 'items.facility'])->find($selectedId)
            : $pendingRequests->first();

        // 2. Daftar Pengajuan yang sedang aktif dipinjam / disetujui untuk panel pindai QR
        $activeTokens = QrToken::with(['borrowRequest.user', 'borrowRequest.facility', 'borrowRequest.items.facility'])
            ->where('is_used', false)
            ->where('expires_at', '>', now())
            ->latest()
            ->take(5)
            ->get();

        return view('dosen.dashboard', compact('pendingRequests', 'selectedRequest', 'activeTokens'));
    }

    /**
     * Daftar Pengajuan Masuk.
     */
    public function pengajuanMasuk()
    {
        $requests = BorrowRequest::with(['user', 'facility', 'items.facility'])
            ->where('status', 'menunggu')
            ->latest()
            ->paginate(15);

        return view('dosen.pengajuan-masuk', compact('requests'));
    }

    /**
     * Detail Pengajuan.
     */
    public function detailPengajuan($id)
    {
        $requestData = BorrowRequest::with(['user', 'facility', 'items.facility', 'activeQrToken'])->findOrFail($id);
        return view('dosen.detail-pengajuan', compact('requestData'));
    }

    /**
     * Putuskan Pengajuan (Setujui atau Tolak) (Figma Page 9).
     */
    public function putuskanPengajuan(Request $request, $id)
    {
        $borrowRequest = BorrowRequest::with(['facility', 'items.facility'])->findOrFail($id);

        $action = $request->input('action'); // 'setujui' atau 'tolak'
        $notes = $request->input('notes');

        if ($action === 'setujui') {
            // Periksa stok jika barang
            if ($borrowRequest->type === 'barang' && $borrowRequest->facility) {
                $itemQty = $borrowRequest->items->first()?->quantity ?? 1;
                if (!$this->stockService->hasSufficientStock($borrowRequest->facility, $itemQty)) {
                    return back()->with('error', 'Gagal menyetujui: Stok barang saat ini tidak mencukupi.');
                }
            }

            $borrowRequest->update([
                'status' => 'disetujui',
                'approved_by' => Auth::id(),
            ]);

            // Terbitkan QR Code penyerahan otomatis
            $this->qrCodeService->generateToken($borrowRequest, 'penyerahan');

            return redirect()->route('dosen.dashboard')->with('success', "Pengajuan {$borrowRequest->code} berhasil disetujui! Token QR penyerahan telah diterbitkan ke mahasiswa.");
        } elseif ($action === 'tolak') {
            $borrowRequest->update([
                'status' => 'ditolak',
                'reject_reason' => $notes ?: 'Pengajuan belum memenuhi persyaratan prodi.',
                'approved_by' => Auth::id(),
            ]);

            return redirect()->route('dosen.dashboard')->with('success', "Pengajuan {$borrowRequest->code} telah ditolak.");
        }

        return back()->with('error', 'Aksi keputusan tidak valid.');
    }

    /**
     * Halaman Khusus Pemindaian QR Code (Figma Page 10).
     */
    public function pindaiQr()
    {
        return view('dosen.pindai-qr');
    }

    /**
     * Endpoint Validasi QR Code secara asynchronous/AJAX.
     */
    public function validateQrAjax(Request $request)
    {
        $token = $request->input('token');
        if (!$token) {
            return response()->json(['valid' => false, 'message' => 'Token QR tidak boleh kosong.']);
        }

        $result = $this->qrCodeService->validateToken($token);

        if (!$result['valid']) {
            return response()->json([
                'valid' => false,
                'status' => $result['status'],
                'message' => $result['message'],
            ]);
        }

        $qr = $result['qrToken'];
        $req = $qr->borrowRequest;

        return response()->json([
            'valid' => true,
            'status' => 'valid',
            'token' => $qr->token,
            'type' => $qr->type,
            'remaining_seconds' => $this->qrCodeService->getRemainingSeconds($qr),
            'mahasiswa' => $req->user->name,
            'nim' => $req->user->nim_nip,
            'fasilitas' => $req->facility ? $req->facility->name : 'Fasilitas Terkait',
            'jumlah' => $req->items->first()?->quantity ?? $req->participant_count ?? 1,
            'borrow_request_id' => $req->id,
        ]);
    }

    /**
     * Konfirmasi Penyerahan Barang / Lab (Scan QR Penyerahan).
     */
    public function prosesPenyerahan(Request $request)
    {
        $tokenStr = $request->input('token');
        $result = $this->qrCodeService->validateToken($tokenStr);

        if (!$result['valid'] || $result['qrToken']->type !== 'penyerahan') {
            return back()->with('error', 'Token QR penyerahan tidak valid atau sudah kedaluwarsa.');
        }

        $qr = $result['qrToken'];
        $borrowRequest = $qr->borrowRequest;

        // Kurangi stok barang
        if ($borrowRequest->type === 'barang' && $borrowRequest->facility) {
            $qty = $borrowRequest->items->first()?->quantity ?? 1;
            $this->stockService->decreaseStock($borrowRequest->facility, $qty);
        }

        // Tandai QR used & update status pengajuan
        $this->qrCodeService->markAsUsed($qr);
        $borrowRequest->update(['status' => 'dipinjam']);

        // Update schedule log jika ada
        ScheduleLog::where('borrow_request_id', $borrowRequest->id)->update(['status' => 'berlangsung']);

        return redirect()->route('dosen.dashboard')->with('success', "Penyerahan fasilitas untuk {$borrowRequest->user->name} berhasil divalidasi!");
    }

    /**
     * Konfirmasi Pengembalian Barang / Lab (Scan QR Pengembalian) (Figma Page 9 & 10).
     */
    public function prosesPengembalian(Request $request)
    {
        $tokenStr = $request->input('token');
        $result = $this->qrCodeService->validateToken($tokenStr);

        if (!$result['valid'] || $result['qrToken']->type !== 'pengembalian') {
            return back()->with('error', 'Token QR pengembalian tidak valid atau sudah kedaluwarsa.');
        }

        $qr = $result['qrToken'];
        $borrowRequest = $qr->borrowRequest;

        $condition = $request->input('condition', 'Baik');
        $returnedQty = $request->input('returned_quantity', 1);
        $notes = $request->input('notes');

        // Catat pengembalian
        ReturnRecord::create([
            'borrow_request_id' => $borrowRequest->id,
            'lecturer_id' => Auth::id(),
            'return_date' => Carbon::now(),
            'returned_quantity' => $returnedQty,
            'condition' => $condition,
            'notes' => $notes,
        ]);

        // Kembalikan stok barang
        if ($borrowRequest->type === 'barang' && $borrowRequest->facility) {
            $this->stockService->increaseStock($borrowRequest->facility, $returnedQty);
        }

        // Tandai QR used & status selesai
        $this->qrCodeService->markAsUsed($qr);
        $borrowRequest->update(['status' => 'dikembalikan']);

        ScheduleLog::where('borrow_request_id', $borrowRequest->id)->update(['status' => 'selesai']);

        return redirect()->route('dosen.dashboard')->with('success', "Pengembalian fasilitas dari {$borrowRequest->user->name} berhasil dikonfirmasi!");
    }

    /**
     * Riwayat Keputusan Dosen (Figma Page 11).
     */
    public function riwayatKeputusan(Request $request)
    {
        $query = BorrowRequest::with(['user', 'facility', 'qrTokens', 'returnRecord'])
            ->whereNotNull('approved_by');

        if ($request->filled('status') && in_array($request->status, ['disetujui', 'ditolak'])) {
            if ($request->status === 'disetujui') {
                $query->whereIn('status', ['disetujui', 'dipinjam', 'dikembalikan', 'selesai']);
            } else {
                $query->where('status', 'ditolak');
            }
        }

        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('code', 'like', "%{$search}%")
                  ->orWhereHas('user', fn($u) => $u->where('name', 'like', "%{$search}%"))
                  ->orWhereHas('facility', fn($f) => $f->where('name', 'like', "%{$search}%"));
            });
        }

        $decisions = $query->latest()->paginate(10);

        return view('dosen.riwayat-keputusan', compact('decisions'));
    }
}
