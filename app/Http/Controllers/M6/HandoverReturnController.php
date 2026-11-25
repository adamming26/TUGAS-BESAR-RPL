<?php

namespace App\Http\Controllers\M6;

use App\Http\Controllers\Controller;
use App\Models\BorrowRequest;
use App\Models\Facility;
use App\Models\QrToken;
use App\Models\ReturnRecord;
use App\Models\ScheduleLog;
use App\Services\NotifikasiService;
use App\Services\QRCodeService;
use App\Services\StockService;
use Carbon\Carbon;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Validation\ValidationException;
use Illuminate\View\View;

class HandoverReturnController extends Controller
{
    public function __construct(
        protected QRCodeService $qrCodeService
    ) {
    }

    /**
     * Menampilkan daftar peminjaman aktif (status dipinjam) yang siap dikonfirmasi pengembaliannya.
     */
    public function index(Request $request): View
    {
        $query = BorrowRequest::with(['user', 'facility', 'items.facility', 'qrTokens', 'activeQrToken'])
            ->where('status', 'dipinjam')
            ->doesntHave('returnRecord');

        $typeFilter = $request->query('type');
        if ($typeFilter === 'lab') {
            $typeFilter = 'ruangan';
        }
        if (in_array($typeFilter, ['barang', 'ruangan'], true)) {
            $query->where('type', $typeFilter);
        }

        if ($request->filled('search')) {
            $search = trim((string) $request->query('search'));
            $query->where(function ($q) use ($search) {
                $q->where('code', 'like', "%{$search}%")
                    ->orWhereHas('user', function ($userQuery) use ($search) {
                        $userQuery->where('name', 'like', "%{$search}%")
                            ->orWhere('nim_nip', 'like', "%{$search}%");
                    })
                    ->orWhereHas('facility', function ($facilityQuery) use ($search) {
                        $facilityQuery->where('name', 'like', "%{$search}%")
                            ->orWhere('code', 'like', "%{$search}%");
                    });
            });
        }

        $activeLoans = $query->latest()->paginate(10)->withQueryString();

        $summary = [
            'total_active' => BorrowRequest::where('status', 'dipinjam')->doesntHave('returnRecord')->count(),
            'barang_active' => BorrowRequest::where('status', 'dipinjam')->where('type', 'barang')->doesntHave('returnRecord')->count(),
            'lab_active' => BorrowRequest::where('status', 'dipinjam')->where('type', 'ruangan')->doesntHave('returnRecord')->count(),
        ];

        return view('dosen.pengembalian.index', [
            'title' => 'Konfirmasi Pengembalian',
            'activeLoans' => $activeLoans,
            'summary' => $summary,
            'activeReturnCount' => $summary['total_active'],
            'selectedType' => $typeFilter,
        ]);
    }

    /**
     * Menampilkan Form Konfirmasi Pengembalian per transaksi (Bab 3.7 Halaman 9).
     */
    public function show(BorrowRequest $borrowRequest): View|RedirectResponse
    {
        $borrowRequest->load(['user', 'approver', 'facility', 'items.facility', 'qrTokens', 'activeQrToken', 'returnRecord']);

        if ($borrowRequest->returnRecord !== null) {
            return redirect()
                ->route('dosen.audit-pengembalian.show', $borrowRequest->returnRecord->id)
                ->with('info', "Pengembalian untuk transaksi {$borrowRequest->code} sudah pernah dikonfirmasi.");
        }

        if ($borrowRequest->status !== 'dipinjam') {
            return redirect()
                ->route('dosen.pengembalian.index')
                ->with('error', "Transaksi {$borrowRequest->code} tidak berada pada status dipinjam.");
        }

        $expectedQty = QrScannerController::resolveExpectedQuantity($borrowRequest);
        $handoverAt = QrScannerController::resolveHandoverTimestamp($borrowRequest);
        $activeReturnCount = BorrowRequest::where('status', 'dipinjam')->doesntHave('returnRecord')->count();

        return view('dosen.pengembalian.form', [
            'title' => 'Form Konfirmasi Pengembalian',
            'borrowRequest' => $borrowRequest,
            'expectedQty' => $expectedQty,
            'handoverAt' => $handoverAt,
            'activeReturnCount' => $activeReturnCount,
        ]);
    }

    /**
     * Memproses serah terima fasilitas setelah QR penyerahan dinyatakan valid.
     */
    public function handover(Request $request): RedirectResponse|JsonResponse
    {
        $validated = $request->validate([
            'token' => ['required', 'string', 'max:120'],
            'borrow_request_id' => ['nullable', 'integer'],
            'handover_notes' => ['nullable', 'string', 'max:500'],
        ], [
            'token.required' => 'Token QR penyerahan wajib diisi atau dipindai.',
        ]);

        $cleanToken = trim($validated['token']);
        $expectedBorrowRequestId = isset($validated['borrow_request_id']) ? (int) $validated['borrow_request_id'] : null;

        try {
            $outcome = DB::transaction(function () use ($cleanToken, $expectedBorrowRequestId, $validated) {
                $lockedToken = QrToken::where('token', $cleanToken)->lockForUpdate()->first();

                if (!$lockedToken) {
                    return $this->failResult('not_found', 'QR Code penyerahan tidak ditemukan dalam sistem.');
                }

                $borrowRequest = BorrowRequest::with(['user', 'facility', 'items.facility'])
                    ->where('id', $lockedToken->borrow_request_id)
                    ->lockForUpdate()
                    ->first();

                $verification = QrScannerController::verifyTokenForTransaction(
                    rawToken: $cleanToken,
                    expectedType: 'penyerahan',
                    expectedBorrowRequestId: $expectedBorrowRequestId,
                    qrCodeService: $this->qrCodeService
                );

                if (!$verification['valid']) {
                    return $this->failResult($verification['status'], $verification['message']);
                }

                if ($borrowRequest->type === 'barang') {
                    if ($borrowRequest->items->isNotEmpty()) {
                        foreach ($borrowRequest->items as $item) {
                            $facility = Facility::where('id', $item->facility_id)->lockForUpdate()->first();
                            if (!$facility || !StockService::reduce($facility->id, (int) $item->quantity)) {
                                return $this->failResult(
                                    'insufficient_stock',
                                    'Stok barang "' . ($facility?->name ?? 'Terkait') . '" tidak mencukupi untuk proses serah terima.'
                                );
                            }
                        }
                    } elseif ($borrowRequest->facility_id) {
                        $facility = Facility::where('id', $borrowRequest->facility_id)->lockForUpdate()->first();
                        if (!$facility || !StockService::reduce($facility->id, 1)) {
                            return $this->failResult(
                                'insufficient_stock',
                                'Stok barang "' . ($facility?->name ?? 'Terkait') . '" tidak mencukupi untuk proses serah terima.'
                            );
                        }
                    }
                }

                $this->qrCodeService->markAsUsed($lockedToken);
                $lockedToken->refresh();

                $borrowRequest->update([
                    'status' => 'dipinjam',
                ]);

                ScheduleLog::where('borrow_request_id', $borrowRequest->id)
                    ->update(['status' => 'berlangsung']);

                $lecturer = Auth::user();
                $handoverAt = $lockedToken->used_at ?? Carbon::now();

                Log::info('M6 Serah Terima Fasilitas Berhasil', [
                    'borrow_request_id' => $borrowRequest->id,
                    'borrow_code' => $borrowRequest->code,
                    'qr_token' => $lockedToken->token,
                    'handover_at' => $handoverAt->toDateTimeString(),
                    'mahasiswa_id' => $borrowRequest->user_id,
                    'mahasiswa_name' => $borrowRequest->user?->name,
                    'lecturer_id' => $lecturer?->id,
                    'lecturer_name' => $lecturer?->name,
                    'facility_id' => $borrowRequest->facility_id,
                    'facility_name' => $borrowRequest->facility?->name,
                    'handover_notes' => $validated['handover_notes'] ?? null,
                ]);

                if ($borrowRequest->user) {
                    NotifikasiService::kirim(
                        $borrowRequest->user,
                        'Serah Terima Fasilitas Dikonfirmasi',
                        "Fasilitas {$borrowRequest->facility?->name} ({$borrowRequest->code}) telah diserahkan oleh {$lecturer?->name} pada {$handoverAt->format('d M Y H:i')}.",
                        route('mahasiswa.peminjaman-saya')
                    );
                }

                return [
                    'ok' => true,
                    'borrowRequest' => $borrowRequest,
                    'handoverAt' => $handoverAt,
                ];
            });
        } catch (ValidationException $e) {
            throw $e;
        }

        if (!$outcome['ok']) {
            if ($request->expectsJson()) {
                return response()->json([
                    'success' => false,
                    'status' => $outcome['status'],
                    'message' => $outcome['message'],
                ], 422);
            }

            return back()->withInput()->with('error', $outcome['message']);
        }

        /** @var BorrowRequest $borrowRequest */
        $borrowRequest = $outcome['borrowRequest'];
        /** @var Carbon $handoverAt */
        $handoverAt = $outcome['handoverAt'];
        $successMessage = "Serah terima {$borrowRequest->code} kepada {$borrowRequest->user?->name} berhasil dikonfirmasi pada {$handoverAt->format('d M Y H:i')}.";

        if ($request->expectsJson()) {
            return response()->json([
                'success' => true,
                'message' => $successMessage,
                'borrow_request_id' => $borrowRequest->id,
                'status' => $borrowRequest->status,
            ]);
        }

        return redirect()
            ->route('dosen.pindai-qr', ['mode' => 'penyerahan'])
            ->with('success', $successMessage);
    }

    /**
     * Memproses konfirmasi pengembalian, pemeriksaan kondisi barang, pencatatan kerusakan,
     * audit pengembalian, dan pemulihan stok hanya untuk unit kondisi baik.
     */
    public function processReturn(Request $request): RedirectResponse|JsonResponse
    {
        $validated = $request->validate([
            'token' => ['required', 'string', 'max:120'],
            'borrow_request_id' => ['nullable', 'integer'],
            'condition' => ['required', 'in:Baik,Rusak Ringan,Rusak Berat,Hilang'],
            'good_quantity' => ['nullable', 'integer', 'min:0'],
            'damaged_quantity' => ['nullable', 'integer', 'min:0'],
            'lost_quantity' => ['nullable', 'integer', 'min:0'],
            'returned_quantity' => ['nullable', 'integer', 'min:0'],
            'notes' => ['nullable', 'string', 'max:1000'],
        ], [
            'token.required' => 'Token QR pengembalian wajib diisi atau dipindai.',
            'condition.required' => 'Kondisi fasilitas saat dikembalikan wajib dipilih.',
            'condition.in' => 'Pilihan kondisi barang harus Baik, Rusak Ringan, Rusak Berat, atau Hilang.',
        ]);

        $cleanToken = trim($validated['token']);
        $expectedBorrowRequestId = isset($validated['borrow_request_id']) ? (int) $validated['borrow_request_id'] : null;

        $outcome = DB::transaction(function () use ($cleanToken, $expectedBorrowRequestId, $validated) {
            $lockedToken = QrToken::where('token', $cleanToken)->lockForUpdate()->first();

            if (!$lockedToken) {
                return $this->failResult('not_found', 'QR Code pengembalian tidak ditemukan dalam sistem.');
            }

            $borrowRequest = BorrowRequest::with(['user', 'facility', 'items.facility', 'qrTokens'])
                ->where('id', $lockedToken->borrow_request_id)
                ->lockForUpdate()
                ->first();

            if (!$borrowRequest) {
                return $this->failResult('not_found', 'Transaksi peminjaman untuk QR Code ini tidak ditemukan.');
            }

            // Kunci baris return_records untuk mencegah pengembalian ganda & pemulihan stok dua kali
            $existingReturn = ReturnRecord::where('borrow_request_id', $borrowRequest->id)
                ->lockForUpdate()
                ->first();

            if ($existingReturn) {
                return $this->failResult('already_returned', 'Pengembalian untuk transaksi ini sudah pernah dicatat. Pemulihan stok ganda dicegah.');
            }

            $verification = QrScannerController::verifyTokenForTransaction(
                rawToken: $cleanToken,
                expectedType: 'pengembalian',
                expectedBorrowRequestId: $expectedBorrowRequestId,
                qrCodeService: $this->qrCodeService
            );

            if (!$verification['valid']) {
                return $this->failResult($verification['status'], $verification['message']);
            }

            $expectedQty = $borrowRequest->type === 'barang'
                ? QrScannerController::resolveExpectedQuantity($borrowRequest)
                : 1;

            [$goodQty, $damagedQty, $lostQty] = $this->resolveConditionQuantities($validated, $borrowRequest, $expectedQty);

            if ($borrowRequest->type === 'barang') {
                if (($goodQty + $damagedQty + $lostQty) !== $expectedQty) {
                    return $this->failResult(
                        'quantity_mismatch',
                        "Total unit baik ({$goodQty}), rusak ({$damagedQty}), dan hilang ({$lostQty}) harus sama dengan jumlah yang dipinjam ({$expectedQty} unit)."
                    );
                }
            } else {
                // Untuk laboratorium, validasi agar jumlah pemeriksaan konsisten dengan 1 fasilitas lab
                if (($goodQty + $damagedQty + $lostQty) < 1) {
                    $goodQty = $validated['condition'] === 'Baik' ? 1 : 0;
                    $damagedQty = in_array($validated['condition'], ['Rusak Ringan', 'Rusak Berat'], true) ? 1 : 0;
                    $lostQty = $validated['condition'] === 'Hilang' ? 1 : 0;
                }
            }

            $condition = $validated['condition'];
            $userNotes = trim((string) ($validated['notes'] ?? ''));

            if ($condition === 'Baik' && ($damagedQty > 0 || $lostQty > 0)) {
                return $this->failResult(
                    'condition_mismatch',
                    'Jika terdapat unit rusak atau hilang, silakan pilih kondisi selain Baik.'
                );
            }

            if (in_array($condition, ['Rusak Ringan', 'Rusak Berat'], true) && $damagedQty === 0 && $lostQty === 0) {
                return $this->failResult(
                    'condition_mismatch',
                    "Kondisi dipilih {$condition}, masukkan minimal 1 unit pada kolom jumlah rusak."
                );
            }

            if ($condition === 'Hilang' && $lostQty === 0) {
                return $this->failResult(
                    'condition_mismatch',
                    'Kondisi dipilih Hilang, masukkan minimal 1 unit pada kolom jumlah hilang.'
                );
            }

            if (($condition !== 'Baik' || $damagedQty > 0 || $lostQty > 0) && $userNotes === '') {
                return $this->failResult(
                    'notes_required',
                    'Catatan kerusakan atau kehilangan wajib diisi apabila kondisi tidak Baik atau ada unit rusak/hilang.'
                );
            }

            $lecturer = Auth::user();
            $handoverAt = QrScannerController::resolveHandoverTimestamp($borrowRequest);
            $returnAt = Carbon::now();

            // Pemulihan stok HANYA untuk fasilitas barang dan HANYA sebesar jumlah kondisi baik ($goodQty)
            if ($borrowRequest->type === 'barang' && $goodQty > 0) {
                $targetFacilityId = $borrowRequest->items->first()?->facility_id ?? $borrowRequest->facility_id;
                if ($targetFacilityId) {
                    Facility::where('id', $targetFacilityId)->lockForUpdate()->first();
                    StockService::restore((int) $targetFacilityId, $goodQty);
                }
            }

            $structuredNotes = $this->formatStructuredAuditNotes(
                userNotes: $userNotes,
                expectedQty: $borrowRequest->type === 'barang' ? $expectedQty : 1,
                goodQty: $goodQty,
                damagedQty: $damagedQty,
                lostQty: $lostQty,
                handoverAt: $handoverAt,
                borrowerName: $borrowRequest->user?->name ?? 'Mahasiswa',
                borrowerIdentity: $borrowRequest->user?->nim_nip ?? '-',
                receiverName: $lecturer?->name ?? 'Dosen',
                receiverIdentity: $lecturer?->nim_nip ?? '-'
            );

            $returnRecord = ReturnRecord::create([
                'borrow_request_id' => $borrowRequest->id,
                'lecturer_id' => $lecturer?->id,
                'return_date' => $returnAt,
                'returned_quantity' => $goodQty,
                'condition' => $condition,
                'notes' => $structuredNotes,
            ]);

            $this->qrCodeService->markAsUsed($lockedToken);

            $borrowRequest->update([
                'status' => 'dikembalikan',
            ]);

            ScheduleLog::where('borrow_request_id', $borrowRequest->id)
                ->update(['status' => 'selesai']);

            Log::info('M6 Audit Pengembalian Fasilitas', [
                'return_record_id' => $returnRecord->id,
                'borrow_request_id' => $borrowRequest->id,
                'borrow_code' => $borrowRequest->code,
                'facility_id' => $borrowRequest->facility_id,
                'facility_name' => $borrowRequest->facility?->name,
                'handover_at' => $handoverAt?->toDateTimeString(),
                'return_at' => $returnAt->toDateTimeString(),
                'handover_by_mahasiswa' => $borrowRequest->user?->name,
                'received_by_dosen' => $lecturer?->name,
                'condition' => $condition,
                'good_quantity' => $goodQty,
                'damaged_quantity' => $damagedQty,
                'lost_quantity' => $lostQty,
                'damage_notes' => $userNotes,
            ]);

            if ($borrowRequest->user) {
                NotifikasiService::kirim(
                    $borrowRequest->user,
                    'Pengembalian Fasilitas Dikonfirmasi',
                    "Pengembalian {$borrowRequest->facility?->name} ({$borrowRequest->code}) telah dikonfirmasi dengan kondisi {$condition}.",
                    route('mahasiswa.peminjaman-saya')
                );
            }

            return [
                'ok' => true,
                'returnRecord' => $returnRecord,
                'borrowRequest' => $borrowRequest,
                'goodQty' => $goodQty,
                'damagedQty' => $damagedQty,
                'lostQty' => $lostQty,
            ];
        });

        if (!$outcome['ok']) {
            if ($request->expectsJson()) {
                return response()->json([
                    'success' => false,
                    'status' => $outcome['status'],
                    'message' => $outcome['message'],
                ], 422);
            }

            return back()->withInput()->with('error', $outcome['message']);
        }

        /** @var ReturnRecord $returnRecord */
        $returnRecord = $outcome['returnRecord'];
        /** @var BorrowRequest $borrowRequest */
        $borrowRequest = $outcome['borrowRequest'];
        $goodQty = $outcome['goodQty'];

        $stockInfo = $borrowRequest->type === 'barang'
            ? " Stok dipulihkan sebanyak {$goodQty} unit (hanya unit kondisi baik)."
            : ' Penggunaan laboratorium telah selesai diaudit.';

        $successMessage = "Pengembalian {$borrowRequest->code} dari {$borrowRequest->user?->name} berhasil dikonfirmasi!{$stockInfo}";

        if ($request->expectsJson()) {
            return response()->json([
                'success' => true,
                'message' => $successMessage,
                'return_record_id' => $returnRecord->id,
                'redirect_url' => route('dosen.audit-pengembalian.show', $returnRecord->id),
            ]);
        }

        return redirect()
            ->route('dosen.audit-pengembalian.show', $returnRecord->id)
            ->with('success', $successMessage);
    }

    /**
     * Menentukan jumlah baik, rusak, dan hilang dari input form modern maupun fallback returned_quantity.
     *
     * @return array{0:int, 1:int, 2:int}
     */
    private function resolveConditionQuantities(array $validated, BorrowRequest $borrowRequest, int $expectedQty): array
    {
        $hasExplicitBreakdown = array_key_exists('good_quantity', $validated) && $validated['good_quantity'] !== null;

        if ($hasExplicitBreakdown) {
            return [
                max(0, (int) ($validated['good_quantity'] ?? 0)),
                max(0, (int) ($validated['damaged_quantity'] ?? 0)),
                max(0, (int) ($validated['lost_quantity'] ?? 0)),
            ];
        }

        $fallbackQty = (int) ($validated['returned_quantity'] ?? $expectedQty);
        $condition = $validated['condition'];

        if ($condition === 'Hilang') {
            return [0, 0, max(1, $expectedQty)];
        }

        if (in_array($condition, ['Rusak Ringan', 'Rusak Berat'], true)) {
            $damaged = max(1, min($expectedQty, $fallbackQty));
            $good = max(0, $expectedQty - $damaged);
            return [$good, $damaged, 0];
        }

        return [$fallbackQty, 0, 0];
    }

    /**
     * Menyimpan catatan kerusakan beserta ringkasan audit terstruktur di return_records.notes.
     */
    private function formatStructuredAuditNotes(
        string $userNotes,
        int $expectedQty,
        int $goodQty,
        int $damagedQty,
        int $lostQty,
        ?Carbon $handoverAt,
        string $borrowerName,
        string $borrowerIdentity,
        string $receiverName,
        string $receiverIdentity
    ): string {
        $noteBody = $userNotes !== '' ? $userNotes : 'Tidak ada kerusakan; seluruh fasilitas kembali lengkap.';
        $handoverText = $handoverAt ? $handoverAt->format('d M Y H:i') : '-';

        $auditSummary = sprintf(
            '[AUDIT|dipinjam=%d|baik=%d|rusak=%d|hilang=%d|serah=%s|penyerah=%s (%s)|penerima=%s (%s)]',
            $expectedQty,
            $goodQty,
            $damagedQty,
            $lostQty,
            $handoverText,
            $borrowerName,
            $borrowerIdentity,
            $receiverName,
            $receiverIdentity
        );

        return "{$noteBody}\n{$auditSummary}";
    }

    private function failResult(string $status, string $message): array
    {
        return [
            'ok' => false,
            'status' => $status,
            'message' => $message,
        ];
    }
}
