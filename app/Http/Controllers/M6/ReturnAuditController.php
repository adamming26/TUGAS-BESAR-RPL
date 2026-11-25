<?php

namespace App\Http\Controllers\M6;

use App\Http\Controllers\Controller;
use App\Models\BorrowRequest;
use App\Models\ReturnRecord;
use Illuminate\Http\Request;
use Illuminate\View\View;

class ReturnAuditController extends Controller
{
    /**
     * Menampilkan Riwayat Serah Terima & Audit Pengembalian untuk Dosen.
     */
    public function index(Request $request): View
    {
        $query = ReturnRecord::with([
            'borrowRequest.user',
            'borrowRequest.facility',
            'borrowRequest.items.facility',
            'borrowRequest.qrTokens',
            'lecturer',
        ]);

        if ($request->filled('condition') && in_array($request->query('condition'), ['Baik', 'Rusak Ringan', 'Rusak Berat', 'Hilang'], true)) {
            $query->where('condition', $request->query('condition'));
        }

        $typeFilter = $request->query('type');
        if ($typeFilter === 'lab') {
            $typeFilter = 'ruangan';
        }
        if (in_array($typeFilter, ['barang', 'ruangan'], true)) {
            $query->whereHas('borrowRequest', fn ($q) => $q->where('type', $typeFilter));
        }

        if ($request->filled('date_from')) {
            $query->whereDate('return_date', '>=', $request->query('date_from'));
        }

        if ($request->filled('date_to')) {
            $query->whereDate('return_date', '<=', $request->query('date_to'));
        }

        if ($request->filled('search')) {
            $search = trim((string) $request->query('search'));
            $query->where(function ($q) use ($search) {
                $q->where('notes', 'like', "%{$search}%")
                    ->orWhereHas('borrowRequest', function ($br) use ($search) {
                        $br->where('code', 'like', "%{$search}%")
                            ->orWhereHas('user', function ($u) use ($search) {
                                $u->where('name', 'like', "%{$search}%")
                                    ->orWhere('nim_nip', 'like', "%{$search}%");
                            })
                            ->orWhereHas('facility', function ($f) use ($search) {
                                $f->where('name', 'like', "%{$search}%")
                                    ->orWhere('code', 'like', "%{$search}%");
                            });
                    });
            });
        }

        $auditRecords = $query->latest('return_date')->paginate(10)->withQueryString();

        // Transform metadata audit per baris untuk memudahkan tampilan Blade
        $auditRecords->getCollection()->transform(function (ReturnRecord $record) {
            $record->audit_meta = self::parseAuditMetadata($record);
            return $record;
        });

        // Log serah terima yang masih aktif dipinjam (belum dikembalikan)
        $activeHandovers = BorrowRequest::with(['user', 'facility', 'items.facility', 'qrTokens', 'approver'])
            ->where('status', 'dipinjam')
            ->doesntHave('returnRecord')
            ->latest()
            ->take(6)
            ->get();

        $stats = [
            'total_audits' => ReturnRecord::count(),
            'baik_count' => ReturnRecord::where('condition', 'Baik')->count(),
            'issue_count' => ReturnRecord::whereIn('condition', ['Rusak Ringan', 'Rusak Berat', 'Hilang'])->count(),
            'total_good_units' => (int) ReturnRecord::sum('returned_quantity'),
        ];

        $activeReturnCount = BorrowRequest::where('status', 'dipinjam')
            ->doesntHave('returnRecord')
            ->count();

        return view('dosen.audit-pengembalian.index', [
            'title' => 'Riwayat & Audit Pengembalian',
            'auditRecords' => $auditRecords,
            'activeHandovers' => $activeHandovers,
            'stats' => $stats,
            'activeReturnCount' => $activeReturnCount,
            'selectedType' => $typeFilter,
        ]);
    }

    /**
     * Menampilkan detail lengkap satu catatan Audit Pengembalian.
     */
    public function show(ReturnRecord $returnRecord): View
    {
        $returnRecord->load([
            'borrowRequest.user',
            'borrowRequest.approver',
            'borrowRequest.facility',
            'borrowRequest.items.facility',
            'borrowRequest.qrTokens',
            'lecturer',
        ]);

        $auditMeta = self::parseAuditMetadata($returnRecord);

        $activeReturnCount = BorrowRequest::where('status', 'dipinjam')
            ->doesntHave('returnRecord')
            ->count();

        return view('dosen.audit-pengembalian.show', [
            'title' => 'Detail Audit Pengembalian',
            'returnRecord' => $returnRecord,
            'borrowRequest' => $returnRecord->borrowRequest,
            'auditMeta' => $auditMeta,
            'activeReturnCount' => $activeReturnCount,
        ]);
    }

    /**
     * Memisahkan catatan kerusakan dari tag metadata audit terstruktur di return_records.notes.
     */
    public static function parseAuditMetadata(ReturnRecord $record): array
    {
        $borrowRequest = $record->borrowRequest;
        $rawNotes = (string) ($record->notes ?? '');
        $cleanNotes = $rawNotes;

        $expectedQty = $borrowRequest
            ? ($borrowRequest->type === 'barang' ? QrScannerController::resolveExpectedQuantity($borrowRequest) : 1)
            : max(1, (int) $record->returned_quantity);

        $goodQty = (int) $record->returned_quantity;
        $damagedQty = 0;
        $lostQty = 0;

        if (preg_match('/\[AUDIT\|([^\]]+)\]/', $rawNotes, $matches)) {
            $cleanNotes = trim(str_replace($matches[0], '', $rawNotes));
            $pairs = explode('|', $matches[1]);
            foreach ($pairs as $pair) {
                if (str_contains($pair, '=')) {
                    [$key, $val] = explode('=', $pair, 2);
                    if ($key === 'dipinjam') {
                        $expectedQty = (int) $val;
                    } elseif ($key === 'baik') {
                        $goodQty = (int) $val;
                    } elseif ($key === 'rusak') {
                        $damagedQty = (int) $val;
                    } elseif ($key === 'hilang') {
                        $lostQty = (int) $val;
                    }
                }
            }
        } else {
            // Fallback apabila record berasal dari controller lain / seeder
            $diff = max(0, $expectedQty - $goodQty);
            if ($record->condition === 'Hilang') {
                $lostQty = $diff > 0 ? $diff : $expectedQty;
            } elseif (in_array($record->condition, ['Rusak Ringan', 'Rusak Berat'], true)) {
                $damagedQty = $diff;
            }
        }

        $handoverToken = $borrowRequest?->qrTokens
            ?->where('type', 'penyerahan')
            ->where('is_used', true)
            ->sortByDesc('used_at')
            ->first();

        $returnToken = $borrowRequest?->qrTokens
            ?->where('type', 'pengembalian')
            ->where('is_used', true)
            ->sortByDesc('used_at')
            ->first();

        return [
            'clean_notes' => $cleanNotes !== '' ? $cleanNotes : '-',
            'expected_quantity' => $expectedQty,
            'good_quantity' => $goodQty,
            'damaged_quantity' => $damagedQty,
            'lost_quantity' => $lostQty,
            'handover_at' => $handoverToken?->used_at,
            'handover_token' => $handoverToken,
            'return_token' => $returnToken,
            'handover_by_name' => $borrowRequest?->user?->name ?? 'Mahasiswa',
            'handover_by_identity' => $borrowRequest?->user?->nim_nip ?? '-',
            'received_by_name' => $record->lecturer?->name ?? 'Dosen',
            'received_by_identity' => $record->lecturer?->nim_nip ?? '-',
        ];
    }
}
