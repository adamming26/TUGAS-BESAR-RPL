<?php

namespace App\Http\Controllers;

use App\Models\Facility;
use Illuminate\Http\Request;

class HomeController extends Controller
{
    /**
     * Halaman Landing Publik SIPAFKA (Figma Page 1).
     */
    public function index()
    {
        $barangFacilities = Facility::where('type', 'barang')->where('is_active', true)->get();
        $labFacilities = Facility::where('type', 'ruangan')->where('is_active', true)->get();

        return view('home', compact('barangFacilities', 'labFacilities'));
    }

    /**
     * Halaman Katalog Fasilitas Publik.
     */
    public function fasilitas(Request $request)
    {
        $query = Facility::where('is_active', true);

        if ($request->filled('type') && in_array($request->type, ['barang', 'ruangan'])) {
            $query->where('type', $request->type);
        }

        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                  ->orWhere('code', 'like', "%{$search}%")
                  ->orWhere('description', 'like', "%{$search}%");
            });
        }

        $facilities = $query->orderBy('name')->paginate(12);

        return view('fasilitas.index', compact('facilities'));
    }
}
