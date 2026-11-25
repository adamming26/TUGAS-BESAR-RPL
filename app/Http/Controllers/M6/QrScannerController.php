<?php

namespace App\Http\Controllers\M6;

use App\Http\Controllers\Controller;
use App\Models\BorrowRequest;
use App\Models\QrToken;
use App\Services\QRCodeService;
use Carbon\Carbon;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class QrScannerController extends Controller
{
    public function __construct(
        protected QRCodeService $qrCodeService
    ) {
    }

    /**
     * Menampilkan halaman Scanner Kamera QR Code Dosen (Bab 3.7 Halaman 8).
     */
    public function index(Request $request): View
    {
        $initialMode = in_array($request->query('mode'), ['penyerahan', 'pengembalian'], true)
            ? $request->query('mode')
            : 'penyerahan';

        $prefilledToken = trim((string) $request->query('token', ''));

        $selectedBorrowRequest = null;
        if ($request->filled('borrow_request_id')) {
            $selectedBorrowRequest = BorrowRequest::with(['user', 'facility', 'items.facility', 'activeQrToken'])
                ->find($request->integer('borrow_request_id'));
        }

        $pendingHandovers = BorrowRequest::with(['user', 'facility', 'items.facility', 'activeQrToken'])
            ->where('status', 'disetujui')
            ->latest()
            ->take(8)
            ->get();

        $activeLoans = BorrowRequest::with(['user', 'facility', 'items.facility', 'activeQrToken', 'qrTokens'])
            ->where('status', 'dipinjam')
            ->doesntHave('returnRecord')
            ->latest()
            ->take(8)
            ->get();

        $activeReturnCount = BorrowRequest::where('status', 'dipinjam')
            ->doesntHave('returnRecord')
            ->count();

        return view('dosen.pindai-qr', [
            'title' => 'Pindai QR Code',
            'initialMode' => $initialMode,
            'prefilledToken' => $prefilledToken,
            'selectedBorrowRequest' => $selectedBorrowRequest,
            'pendingHandovers' => $pendingHandovers,
            'activeLoans' => $activeLoans,
            'activeReturnCount' => $activeReturnCount,
        ]);
    }

    /**
     * Memvalidasi token QR dari kamera atau input manual secara AJAX/JSON di backend.
     */
    public function validateToken(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'token' => ['required', 'string', 'max:120'],
            'mode' => ['nullable', 'in:penyerahan,pengembalian'],
            'borrow_request_id' => ['nullable', 'integer'],
        ], [
            'token.required' => 'Token QR wajib diisi atau dipindai terlebih dahulu.',
        ]);

        $verification = self::verifyTokenForTransaction(
            rawToken: $validated['token'],
            expectedType: $validated['mode'] ?? null,
            expectedBorrowRequestId: isset($validated['borrow_request_id']) ? (int) $validated['borrow_request_id'] : null,
            qrCodeService: $this->qrCodeService
        );

        if (!$verification['valid']) {
            return response()->json([
                'valid' => false,
                'status' => $verification['status'],
                'message' => $verification['message'],
            ], 422);
        }

        /** @var QrToken $qrToken */
        $qrToken = $verification['qrToken'];
        /** @var BorrowRequest $borrowRequest */
        $borrowRequest = $verification['borrowRequest'];
        $borrowRequest->loadMissing(['user', 'facility', 'items.facility', 'qrTokens']);

        $expectedQty = self::resolveExpectedQuantity($borrowRequest);
        $handoverAt = self::resolveHandoverTimestamp($borrowRequest);
        $remainingSeconds = $this->qrCodeService->getRemainingSeconds($qrToken);

        return response()->json([
            'valid' => true,
            'status' => 'valid',
            'message' => $verification['message'],
            'token' => $qrToken->token,
            'type' => $qrToken->type,
            'type_label' => $qrToken->type === 'penyerahan' ? 'Penyerahan' : 'Pengembalian',
            'remaining_seconds' => $remainingSeconds,
            'expires_at' => $qrToken->expires_at->format('d M Y H:i'),
            'expires_at_iso' => $qrToken->expires_at->toIso8601String(),
            'borrow_request' => [
                'id' => $borrowRequest->id,
                'code' => $borrowRequest->code,
                'type' => $borrowRequest->type,
                'type_label' => $borrowRequest->type === 'barang' ? 'Barang' : 'Laboratorium',
                'status' => $borrowRequest->status,
                'borrow_date' => Carbon::parse($borrowRequest->borrow_date)->format('d M Y'),
                'time_range' => substr((string) $borrowRequest->start_time, 0, 5) . ' - ' . substr((string) $borrowRequest->end_time, 0, 5) . ' WITA',
                'duration_hours' => $borrowRequest->duration_hours,
                'purpose' => $borrowRequest->purpose,
                'expected_quantity' => $expectedQty,
                'handover_at' => $handoverAt ? $handoverAt->format('d M Y H:i:s') : null,
            ],
            'mahasiswa' => [
                'id' => $borrowRequest->user?->id,
                'name' => $borrowRequest->user?->name ?? 'Mahasiswa',
                'nim_nip' => $borrowRequest->user?->nim_nip ?? '-',
                'email' => $borrowRequest->user?->email ?? '-',
            ],
            'facility' => [
                'id' => $borrowRequest->facility?->id,
                'code' => $borrowRequest->facility?->code ?? '-',
                'name' => $borrowRequest->facility?->name ?? 'Fasilitas Terkait',
                'type' => $borrowRequest->facility?->type ?? $borrowRequest->type,
                'available_stock' => $borrowRequest->facility?->available_stock,
                'total_stock' => $borrowRequest->facility?->total_stock,
                'capacity' => $borrowRequest->facility?->capacity,
                'condition' => $borrowRequest->facility?->condition ?? 'Baik',
            ],
            'return_form_url' => route('dosen.pengembalian.show', $borrowRequest->id),
        ]);
    }

    /**
     * Memverifikasi seluruh syarat token QR untuk transaksi M6 (terdaftar, masa berlaku,
     * belum dipakai, kesesuaian jenis, keterikatan peminjaman, dan status peminjaman).
     */
    public static function verifyTokenForTransaction(
        string $rawToken,
        ?string $expectedType = null,
        ?int $expectedBorrowRequestId = null,
        ?QRCodeService $qrCodeService = null
    ): array {
        $service = $qrCodeService ?? app(QRCodeService::class);
        $cleanToken = trim($rawToken);

        if ($cleanToken === '') {
            return [
                'valid' => false,
                'status' => 'not_found',
                'message' => 'Token QR tidak boleh kosong.',
                'qrToken' => null,
                'borrowRequest' => null,
            ];
        }

        $result = $service->validateToken($cleanToken);

        if (!$result['valid']) {
            return [
                'valid' => false,
                'status' => $result['status'],
                'message' => $result['message'],
                'qrToken' => $result['qrToken'],
                'borrowRequest' => $result['qrToken']?->borrowRequest,
            ];
        }

        /** @var QrToken $qrToken */
        $qrToken = $result['qrToken'];
        $borrowRequest = $qrToken->borrowRequest;

        if (!$borrowRequest) {
            return [
                'valid' => false,
                'status' => 'not_found',
                'message' => 'Data peminjaman yang terhubung dengan QR Code ini tidak ditemukan.',
                'qrToken' => $qrToken,
                'borrowRequest' => null,
            ];
        }

        if ($expectedBorrowRequestId !== null && (int) $qrToken->borrow_request_id !== $expectedBorrowRequestId) {
            return [
                'valid' => false,
                'status' => 'wrong_borrow_request',
                'message' => 'Token QR tidak sesuai dengan transaksi peminjaman yang dipilih.',
                'qrToken' => $qrToken,
                'borrowRequest' => $borrowRequest,
            ];
        }

        if ($expectedType !== null && $qrToken->type !== $expectedType) {
            $actualLabel = $qrToken->type === 'penyerahan' ? 'Penyerahan' : 'Pengembalian';
            $expectedLabel = $expectedType === 'penyerahan' ? 'Penyerahan' : 'Pengembalian';

            return [
                'valid' => false,
                'status' => 'wrong_type',
                'message' => "Token QR ini berjenis {$actualLabel}, tidak dapat digunakan pada mode {$expectedLabel}.",
                'qrToken' => $qrToken,
                'borrowRequest' => $borrowRequest,
            ];
        }

        if ($qrToken->type === 'penyerahan' && $borrowRequest->status !== 'disetujui') {
            return [
                'valid' => false,
                'status' => 'invalid_status',
                'message' => "Status peminjaman ({$borrowRequest->status}) tidak sesuai untuk serah terima. Hanya pengajuan berstatus disetujui yang dapat diproses.",
                'qrToken' => $qrToken,
                'borrowRequest' => $borrowRequest,
            ];
        }

        if ($qrToken->type === 'pengembalian') {
            if ($borrowRequest->returnRecord()->exists() || in_array($borrowRequest->status, ['dikembalikan', 'selesai'], true)) {
                return [
                    'valid' => false,
                    'status' => 'invalid_status',
                    'message' => 'Pengembalian untuk transaksi peminjaman ini sudah pernah dikonfirmasi sebelumnya.',
                    'qrToken' => $qrToken,
                    'borrowRequest' => $borrowRequest,
                ];
            }

            if ($borrowRequest->status !== 'dipinjam') {
                return [
                    'valid' => false,
                    'status' => 'invalid_status',
                    'message' => "Status peminjaman ({$borrowRequest->status}) belum berstatus dipinjam sehingga belum dapat dikonfirmasi pengembaliannya.",
                    'qrToken' => $qrToken,
                    'borrowRequest' => $borrowRequest,
                ];
            }
        }

        return [
            'valid' => true,
            'status' => 'valid',
            'message' => $result['message'],
            'qrToken' => $qrToken,
            'borrowRequest' => $borrowRequest,
        ];
    }

    /**
     * Menentukan jumlah unit/peserta yang tercatat pada peminjaman.
     */
    public static function resolveExpectedQuantity(BorrowRequest $borrowRequest): int
    {
        if ($borrowRequest->type === 'barang') {
            $sum = (int) $borrowRequest->items->sum('quantity');
            return $sum > 0 ? $sum : 1;
        }

        return max(1, (int) ($borrowRequest->participant_count ?? 1));
    }

    /**
     * Mengambil waktu serah terima dari token penyerahan yang telah digunakan.
     */
    public static function resolveHandoverTimestamp(BorrowRequest $borrowRequest): ?Carbon
    {
        $handoverToken = $borrowRequest->qrTokens
            ->where('type', 'penyerahan')
            ->where('is_used', true)
            ->sortByDesc('used_at')
            ->first();

        return $handoverToken?->used_at;
    }
}
