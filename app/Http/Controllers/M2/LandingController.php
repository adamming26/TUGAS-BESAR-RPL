<?php

namespace App\Http\Controllers\M2;

use App\Http\Controllers\Controller;
use App\Models\Facility;
use Illuminate\View\View;

class LandingController extends Controller
{
    /**
     * Halaman Landing Page SIPAFKA (PDF Page 1).
     */
    public function index(): View
    {
        $barangFacilities = Facility::where('type', 'barang')
            ->where('is_active', true)
            ->orderBy('name')
            ->get();

        $labFacilities = Facility::where('type', 'ruangan')
            ->where('is_active', true)
            ->orderBy('name')
            ->get();

        $stats = [
            'total_lab' => $labFacilities->count(),
            'total_barang' => $barangFacilities->count(),
            'total_unit_tersedia' => (int) $barangFacilities->sum('available_stock'),
        ];

        return view('home', compact('barangFacilities', 'labFacilities', 'stats'));
    }

    /**
     * Halaman Panduan Penggunaan SIPAFKA.
     */
    public function panduan(): View
    {
        return view('panduan.index');
    }
}
