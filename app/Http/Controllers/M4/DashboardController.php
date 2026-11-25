<?php

namespace App\Http\Controllers\M4;

use App\Http\Controllers\Controller;
use App\Models\BorrowRequest;
use App\Models\Facility;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;

class DashboardController extends Controller
{
    /**
     * Tampilkan Dashboard Mahasiswa (Bab 3.7 Halaman 1).
     */
    public function index(Request $request): View
    {
        $user = Auth::user();

        $baseQuery = BorrowRequest::where('user_id', $user->id);

        $statusSummary = [
            'total' => (clone $baseQuery)->count(),
            'menunggu' => (clone $baseQuery)->where('status', 'menunggu')->count(),
            'disetujui' => (clone $baseQuery)->where('status', 'disetujui')->count(),
            'dipinjam' => (clone $baseQuery)->where('status', 'dipinjam')->count(),
            'selesai' => (clone $baseQuery)->whereIn('status', ['dikembalikan', 'selesai'])->count(),
            'ditolak' => (clone $baseQuery)->where('status', 'ditolak')->count(),
        ];

        $recentRequests = BorrowRequest::with(['facility', 'items.facility', 'approver'])
            ->where('user_id', $user->id)
            ->latest()
            ->take(5)
            ->get();

        $facilitiesQuery = Facility::where('is_active', true);

        if ($request->filled('type') && in_array($request->type, ['barang', 'ruangan'], true)) {
            $facilitiesQuery->where('type', $request->type);
        }

        if ($request->filled('search')) {
            $search = trim((string) $request->search);
            $facilitiesQuery->where(function ($query) use ($search) {
                $query->where('name', 'like', "%{$search}%")
                    ->orWhere('code', 'like', "%{$search}%");
            });
        }

        $facilities = $facilitiesQuery->orderBy('type')->orderBy('name')->get();

        return view('mahasiswa.dashboard', compact(
            'user',
            'statusSummary',
            'recentRequests',
            'facilities'
        ));
    }
}
