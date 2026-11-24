<?php

namespace App\Http\Controllers;

use App\Models\BorrowRequest;
use App\Models\Facility;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class AdminController extends Controller
{
    /**
     * Dashboard Admin (Figma Page 12).
     */
    public function dashboard()
    {
        // 1. Mini List Fasilitas
        $facilities = Facility::where('is_active', true)
            ->orderBy('type')
            ->orderBy('name')
            ->take(5)
            ->get();

        // 2. Mini List Transaksi Berjalan
        $activeTransactions = BorrowRequest::with(['user', 'facility', 'approver'])
            ->whereIn('status', ['menunggu', 'disetujui', 'dipinjam'])
            ->latest()
            ->take(6)
            ->get();

        return view('admin.dashboard', compact('facilities', 'activeTransactions'));
    }

    /**
     * Kelola Fasilitas Admin (Figma Page 13).
     */
    public function kelolaFasilitas(Request $request)
    {
        $query = Facility::query();

        if ($request->filled('type') && in_array($request->type, ['barang', 'ruangan'])) {
            $query->where('type', $request->type);
        }

        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                  ->orWhere('code', 'like', "%{$search}%");
            });
        }

        $facilities = $query->orderBy('type')->orderBy('name')->paginate(10);

        // Edit mode
        $editFacility = null;
        if ($request->filled('edit_id')) {
            $editFacility = Facility::find($request->edit_id);
        }

        return view('admin.kelola-fasilitas', compact('facilities', 'editFacility'));
    }

    /**
     * Simpan Fasilitas Barang Baru.
     */
    public function storeBarang(Request $request)
    {
        $validated = $request->validate([
            'code' => ['required', 'string', 'unique:facilities,code'],
            'name' => ['required', 'string', 'max:255'],
            'total_stock' => ['required', 'integer', 'min:1'],
            'condition' => ['required', 'in:Baik,Rusak Ringan,Rusak Berat'],
            'is_active' => ['required', 'boolean'],
            'description' => ['nullable', 'string'],
        ]);

        Facility::create([
            'code' => $validated['code'],
            'name' => $validated['name'],
            'type' => 'barang',
            'total_stock' => $validated['total_stock'],
            'available_stock' => $validated['total_stock'],
            'capacity' => 0,
            'condition' => $validated['condition'],
            'is_active' => $validated['is_active'],
            'description' => $validated['description'],
        ]);

        return redirect()->route('admin.kelola-fasilitas')->with('success', 'Fasilitas barang berhasil ditambahkan.');
    }

    /**
     * Update Fasilitas Barang.
     */
    public function updateBarang(Request $request, $id)
    {
        $facility = Facility::where('type', 'barang')->findOrFail($id);

        $validated = $request->validate([
            'code' => ['required', 'string', "unique:facilities,code,{$id}"],
            'name' => ['required', 'string', 'max:255'],
            'total_stock' => ['required', 'integer', 'min:0'],
            'available_stock' => ['required', 'integer', 'min:0'],
            'condition' => ['required', 'in:Baik,Rusak Ringan,Rusak Berat'],
            'is_active' => ['required', 'boolean'],
            'description' => ['nullable', 'string'],
        ]);

        $facility->update($validated);

        return redirect()->route('admin.kelola-fasilitas')->with('success', "Data fasilitas {$facility->name} berhasil diperbarui.");
    }

    /**
     * Hapus Fasilitas Barang.
     */
    public function destroyBarang($id)
    {
        $facility = Facility::where('type', 'barang')->findOrFail($id);
        $facility->delete();

        return redirect()->route('admin.kelola-fasilitas')->with('success', 'Fasilitas barang berhasil dihapus.');
    }

    /**
     * Simpan Fasilitas Laboratorium / Ruangan Baru.
     */
    public function storeRuangan(Request $request)
    {
        $validated = $request->validate([
            'code' => ['required', 'string', 'unique:facilities,code'],
            'name' => ['required', 'string', 'max:255'],
            'capacity' => ['required', 'integer', 'min:1'],
            'condition' => ['required', 'in:Baik,Rusak Ringan,Rusak Berat'],
            'is_active' => ['required', 'boolean'],
            'description' => ['nullable', 'string'],
        ]);

        Facility::create([
            'code' => $validated['code'],
            'name' => $validated['name'],
            'type' => 'ruangan',
            'total_stock' => 1,
            'available_stock' => 1,
            'capacity' => $validated['capacity'],
            'condition' => $validated['condition'],
            'is_active' => $validated['is_active'],
            'description' => $validated['description'],
        ]);

        return redirect()->route('admin.kelola-fasilitas')->with('success', 'Fasilitas laboratorium/ruangan berhasil ditambahkan.');
    }

    /**
     * Update Fasilitas Laboratorium / Ruangan.
     */
    public function updateRuangan(Request $request, $id)
    {
        $facility = Facility::where('type', 'ruangan')->findOrFail($id);

        $validated = $request->validate([
            'code' => ['required', 'string', "unique:facilities,code,{$id}"],
            'name' => ['required', 'string', 'max:255'],
            'capacity' => ['required', 'integer', 'min:1'],
            'condition' => ['required', 'in:Baik,Rusak Ringan,Rusak Berat'],
            'is_active' => ['required', 'boolean'],
            'description' => ['nullable', 'string'],
        ]);

        $facility->update($validated);

        return redirect()->route('admin.kelola-fasilitas')->with('success', "Data laboratorium {$facility->name} berhasil diperbarui.");
    }

    /**
     * Hapus Fasilitas Ruangan.
     */
    public function destroyRuangan($id)
    {
        $facility = Facility::where('type', 'ruangan')->findOrFail($id);
        $facility->delete();

        return redirect()->route('admin.kelola-fasilitas')->with('success', 'Fasilitas laboratorium berhasil dihapus.');
    }

    /**
     * Transaksi Berjalan dengan Rincian 6-Tahap Timeline (Figma Page 14).
     */
    public function transaksiBerjalan(Request $request)
    {
        $query = BorrowRequest::with(['user', 'facility', 'approver', 'qrTokens', 'returnRecord.lecturer'])
            ->whereIn('status', ['menunggu', 'disetujui', 'dipinjam']);

        if ($request->filled('tab')) {
            $tab = $request->tab;
            if ($tab === 'menunggu_serah') {
                $query->where('status', 'disetujui');
            } elseif ($tab === 'dipinjam') {
                $query->where('status', 'dipinjam');
            } elseif ($tab === 'menunggu_pengembalian') {
                $query->where('status', 'dipinjam')
                      ->whereHas('qrTokens', fn($q) => $q->where('type', 'pengembalian'));
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

        $transactions = $query->latest()->paginate(10);

        // Selected item for 6-step timeline detail on right panel
        $selectedId = $request->get('selected_id', $transactions->first()?->id);
        $selectedTx = $selectedId
            ? BorrowRequest::with(['user', 'facility', 'approver', 'qrTokens', 'returnRecord.lecturer'])->find($selectedId)
            : $transactions->first();

        return view('admin.transaksi-berjalan', compact('transactions', 'selectedTx'));
    }

    /**
     * Riwayat Transaksi Lengkap (Figma Page 15).
     */
    public function riwayatTransaksi(Request $request)
    {
        $query = BorrowRequest::with(['user', 'facility', 'approver', 'returnRecord.lecturer'])
            ->whereIn('status', ['dikembalikan', 'selesai', 'ditolak']);

        if ($request->filled('start_date')) {
            $query->whereDate('borrow_date', '>=', $request->start_date);
        }

        if ($request->filled('end_date')) {
            $query->whereDate('borrow_date', '<=', $request->end_date);
        }

        if ($request->filled('type') && in_array($request->type, ['barang', 'ruangan'])) {
            $query->where('type', $request->type);
        }

        if ($request->filled('status') && in_array($request->status, ['selesai', 'ditolak'])) {
            if ($request->status === 'selesai') {
                $query->whereIn('status', ['dikembalikan', 'selesai']);
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

        $riwayat = $query->latest()->paginate(10);

        return view('admin.riwayat-transaksi', compact('riwayat'));
    }

    /**
     * Kelola Pengguna (Admin, Dosen, Mahasiswa).
     */
    public function kelolaPengguna(Request $request)
    {
        $query = User::query();

        if ($request->filled('role')) {
            $query->where('role', $request->role);
        }

        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                  ->orWhere('email', 'like', "%{$search}%")
                  ->orWhere('nim_nip', 'like', "%{$search}%");
            });
        }

        $users = $query->orderBy('role')->orderBy('name')->paginate(15);

        return view('admin.pengguna', compact('users'));
    }
}
