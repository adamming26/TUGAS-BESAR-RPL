<?php

namespace App\Http\Controllers;

use App\Models\BorrowItem;
use App\Models\BorrowRequest;
use App\Models\Facility;
use App\Models\ScheduleLog;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class AdminController extends Controller
{
    /**
     * 1. DASHBOARD ADMIN
     * Tampilkan:
     * - Jumlah pengguna (Total, Mahasiswa, Dosen, Admin)
     * - Jumlah fasilitas (Total, Barang, Lab)
     * - Ringkasan barang (Stok unit, tersedia, kondisi)
     * - Ringkasan laboratorium (Kapasitas total, kondisi)
     * - Aktivitas sistem yang relevan bagi Admin
     */
    public function dashboard()
    {
        // Statistik Pengguna
        $userStats = [
            'total' => User::count(),
            'mahasiswa' => User::where('role', 'mahasiswa')->count(),
            'dosen' => User::where('role', 'dosen')->count(),
            'admin' => User::where('role', 'admin')->count(),
        ];

        // Statistik Fasilitas Keseluruhan
        $facilityStats = [
            'total' => Facility::count(),
            'barang' => Facility::where('type', 'barang')->count(),
            'lab' => Facility::where('type', 'ruangan')->count(),
            'aktif' => Facility::where('is_active', true)->count(),
            'nonaktif' => Facility::where('is_active', false)->count(),
        ];

        // Ringkasan Barang
        $barangTotalStock = Facility::where('type', 'barang')->sum('total_stock');
        $barangAvailableStock = Facility::where('type', 'barang')->sum('available_stock');
        $barangSummary = [
            'total_items' => $facilityStats['barang'],
            'total_units' => $barangTotalStock,
            'available_units' => $barangAvailableStock,
            'borrowed_units' => max(0, $barangTotalStock - $barangAvailableStock),
            'kondisi_baik' => Facility::where('type', 'barang')->where('condition', 'Baik')->count(),
            'kondisi_rusak' => Facility::where('type', 'barang')->whereIn('condition', ['Rusak Ringan', 'Rusak Berat'])->count(),
        ];

        // Ringkasan Laboratorium
        $labSummary = [
            'total_labs' => $facilityStats['lab'],
            'total_capacity' => Facility::where('type', 'ruangan')->sum('capacity'),
            'kondisi_baik' => Facility::where('type', 'ruangan')->where('condition', 'Baik')->count(),
            'kondisi_rusak' => Facility::where('type', 'ruangan')->whereIn('condition', ['Rusak Ringan', 'Rusak Berat'])->count(),
        ];

        // Fasilitas Stok Kritis / Habis
        $lowStockFacilities = Facility::where('type', 'barang')
            ->where('is_active', true)
            ->where('available_stock', '<=', 1)
            ->get();

        // Aktivitas Sistem Relevan bagi Admin (Transaksi & Pengajuan Terbaru)
        $recentActivities = BorrowRequest::with(['user', 'facility'])
            ->latest()
            ->take(6)
            ->get();

        // Pengguna yang baru terdaftar
        $recentUsers = User::latest()
            ->take(5)
            ->get();

        // Mini list fasilitas untuk widget cepat
        $facilities = Facility::where('is_active', true)
            ->orderBy('type')
            ->orderBy('name')
            ->take(5)
            ->get();

        return view('admin.dashboard', compact(
            'userStats',
            'facilityStats',
            'barangSummary',
            'labSummary',
            'lowStockFacilities',
            'recentActivities',
            'recentUsers',
            'facilities'
        ));
    }

    /**
     * 2. MANAJEMEN FASILITAS (Overview Halaman Fasilitas)
     */
    public function kelolaFasilitas(Request $request)
    {
        $query = Facility::query();

        if ($request->filled('type') && in_array($request->type, ['barang', 'ruangan'])) {
            $query->where('type', $request->type);
        }

        if ($request->filled('condition') && in_array($request->condition, ['Baik', 'Rusak Ringan', 'Rusak Berat'])) {
            $query->where('condition', $request->condition);
        }

        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                  ->orWhere('code', 'like', "%{$search}%")
                  ->orWhere('description', 'like', "%{$search}%");
            });
        }

        $facilities = $query->orderBy('type')->orderBy('name')->paginate(10);

        // Edit mode data jika parameter edit_id dikirim
        $editFacility = null;
        if ($request->filled('edit_id')) {
            $editFacility = Facility::find($request->edit_id);
        }

        // Statistik singkat untuk tab filter
        $countAll = Facility::count();
        $countBarang = Facility::where('type', 'barang')->count();
        $countRuangan = Facility::where('type', 'ruangan')->count();

        return view('admin.kelola-fasilitas', compact(
            'facilities',
            'editFacility',
            'countAll',
            'countBarang',
            'countRuangan'
        ));
    }

    /**
     * CRUD BARANG: Simpan Barang Baru
     */
    public function storeBarang(Request $request)
    {
        $validated = $request->validate([
            'code' => ['required', 'string', 'max:50', 'unique:facilities,code'],
            'name' => ['required', 'string', 'max:255'],
            'total_stock' => ['required', 'integer', 'min:1'],
            'condition' => ['required', 'in:Baik,Rusak Ringan,Rusak Berat'],
            'is_active' => ['required', 'boolean'],
            'description' => ['nullable', 'string', 'max:1000'],
        ], [
            'code.required' => 'Kode fasilitas barang wajib diisi.',
            'code.unique' => 'Kode barang sudah digunakan. Gunakan kode lain.',
            'name.required' => 'Nama barang wajib diisi.',
            'total_stock.required' => 'Jumlah stok barang wajib ditentukan.',
            'total_stock.min' => 'Stok minimal 1 unit.',
            'condition.required' => 'Kondisi barang wajib dipilih.',
        ]);

        Facility::create([
            'code' => strtoupper(trim($validated['code'])),
            'name' => trim($validated['name']),
            'type' => 'barang',
            'total_stock' => $validated['total_stock'],
            'available_stock' => $validated['total_stock'],
            'capacity' => 0,
            'condition' => $validated['condition'],
            'is_active' => $validated['is_active'],
            'description' => $validated['description'],
        ]);

        return redirect()->route('admin.kelola-fasilitas', ['type' => 'barang'])
            ->with('success', 'Fasilitas barang baru berhasil ditambahkan.');
    }

    /**
     * CRUD BARANG: Update Barang
     */
    public function updateBarang(Request $request, $id)
    {
        $facility = Facility::where('type', 'barang')->findOrFail($id);

        $validated = $request->validate([
            'code' => ['required', 'string', 'max:50', "unique:facilities,code,{$id}"],
            'name' => ['required', 'string', 'max:255'],
            'total_stock' => ['required', 'integer', 'min:0'],
            'available_stock' => ['required', 'integer', 'min:0', "lte:total_stock"],
            'condition' => ['required', 'in:Baik,Rusak Ringan,Rusak Berat'],
            'is_active' => ['required', 'boolean'],
            'description' => ['nullable', 'string', 'max:1000'],
        ], [
            'code.required' => 'Kode barang wajib diisi.',
            'code.unique' => 'Kode barang sudah digunakan.',
            'name.required' => 'Nama barang wajib diisi.',
            'total_stock.min' => 'Stok total tidak boleh negatif.',
            'available_stock.lte' => 'Stok tersedia tidak boleh melebihi stok total.',
        ]);

        $facility->update([
            'code' => strtoupper(trim($validated['code'])),
            'name' => trim($validated['name']),
            'total_stock' => $validated['total_stock'],
            'available_stock' => $validated['available_stock'],
            'condition' => $validated['condition'],
            'is_active' => $validated['is_active'],
            'description' => $validated['description'],
        ]);

        return redirect()->route('admin.kelola-fasilitas', ['type' => 'barang'])
            ->with('success', "Data fasilitas barang '{$facility->name}' berhasil diperbarui.");
    }

    /**
     * CRUD BARANG: Hapus Barang (Dengan Proteksi Integritas Data)
     */
    public function destroyBarang($id)
    {
        $facility = Facility::where('type', 'barang')->findOrFail($id);

        // Proteksi: Cek apakah barang sedang aktif dipinjam
        $hasActiveBorrows = BorrowRequest::where(function ($q) use ($facility) {
                $q->where('facility_id', $facility->id)
                  ->orWhereHas('items', fn($item) => $item->where('facility_id', $facility->id));
            })
            ->whereIn('status', ['menunggu', 'disetujui', 'dipinjam'])
            ->exists();

        if ($hasActiveBorrows) {
            return back()->with('error', "Barang '{$facility->name}' tidak dapat dihapus karena masih terkait transaksi peminjaman aktif.");
        }

        $facilityName = $facility->name;
        $facility->delete();

        return redirect()->route('admin.kelola-fasilitas', ['type' => 'barang'])
            ->with('success', "Fasilitas barang '{$facilityName}' berhasil dihapus.");
    }

    /**
     * CRUD LABORATORIUM: Simpan Lab Baru
     */
    public function storeRuangan(Request $request)
    {
        $validated = $request->validate([
            'code' => ['required', 'string', 'max:50', 'unique:facilities,code'],
            'name' => ['required', 'string', 'max:255'],
            'capacity' => ['required', 'integer', 'min:1'],
            'condition' => ['required', 'in:Baik,Rusak Ringan,Rusak Berat'],
            'is_active' => ['required', 'boolean'],
            'description' => ['nullable', 'string', 'max:1000'],
        ], [
            'code.required' => 'Kode fasilitas laboratorium wajib diisi.',
            'code.unique' => 'Kode laboratorium sudah terdaftar.',
            'name.required' => 'Nama laboratorium wajib diisi.',
            'capacity.required' => 'Kapasitas laboratorium wajib diisi.',
            'capacity.min' => 'Kapasitas laboratorium minimal 1 orang/kursi.',
        ]);

        Facility::create([
            'code' => strtoupper(trim($validated['code'])),
            'name' => trim($validated['name']),
            'type' => 'ruangan',
            'total_stock' => 1,
            'available_stock' => 1,
            'capacity' => $validated['capacity'],
            'condition' => $validated['condition'],
            'is_active' => $validated['is_active'],
            'description' => $validated['description'],
        ]);

        return redirect()->route('admin.kelola-fasilitas', ['type' => 'ruangan'])
            ->with('success', 'Fasilitas laboratorium baru berhasil ditambahkan.');
    }

    /**
     * CRUD LABORATORIUM: Update Lab
     */
    public function updateRuangan(Request $request, $id)
    {
        $facility = Facility::where('type', 'ruangan')->findOrFail($id);

        $validated = $request->validate([
            'code' => ['required', 'string', 'max:50', "unique:facilities,code,{$id}"],
            'name' => ['required', 'string', 'max:255'],
            'capacity' => ['required', 'integer', 'min:1'],
            'condition' => ['required', 'in:Baik,Rusak Ringan,Rusak Berat'],
            'is_active' => ['required', 'boolean'],
            'description' => ['nullable', 'string', 'max:1000'],
        ], [
            'code.required' => 'Kode laboratorium wajib diisi.',
            'code.unique' => 'Kode laboratorium sudah terdaftar.',
            'name.required' => 'Nama laboratorium wajib diisi.',
            'capacity.min' => 'Kapasitas minimal 1 orang/kursi.',
        ]);

        $facility->update([
            'code' => strtoupper(trim($validated['code'])),
            'name' => trim($validated['name']),
            'capacity' => $validated['capacity'],
            'condition' => $validated['condition'],
            'is_active' => $validated['is_active'],
            'description' => $validated['description'],
        ]);

        return redirect()->route('admin.kelola-fasilitas', ['type' => 'ruangan'])
            ->with('success', "Data laboratorium '{$facility->name}' berhasil diperbarui.");
    }

    /**
     * CRUD LABORATORIUM: Hapus Lab (Dengan Proteksi)
     */
    public function destroyRuangan($id)
    {
        $facility = Facility::where('type', 'ruangan')->findOrFail($id);

        // Proteksi: Cek peminjaman atau jadwal aktif
        $hasActiveBookings = BorrowRequest::where('facility_id', $facility->id)
            ->whereIn('status', ['menunggu', 'disetujui', 'dipinjam'])
            ->exists();

        $hasActiveSchedules = ScheduleLog::where('facility_id', $facility->id)
            ->whereIn('status', ['terjadwal', 'berlangsung'])
            ->exists();

        if ($hasActiveBookings || $hasActiveSchedules) {
            return back()->with('error', "Laboratorium '{$facility->name}' tidak dapat dihapus karena masih memiliki jadwal atau permohonan aktif.");
        }

        $facilityName = $facility->name;
        $facility->delete();

        return redirect()->route('admin.kelola-fasilitas', ['type' => 'ruangan'])
            ->with('success', "Fasilitas laboratorium '{$facilityName}' berhasil dihapus.");
    }

    /**
     * 4. MANAJEMEN PENGGUNA (Civitas Akademika)
     */
    public function kelolaPengguna(Request $request)
    {
        $query = User::query();

        if ($request->filled('role') && in_array($request->role, ['admin', 'dosen', 'mahasiswa'])) {
            $query->where('role', $request->role);
        }

        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                  ->orWhere('email', 'like', "%{$search}%")
                  ->orWhere('nim_nip', 'like', "%{$search}%")
                  ->orWhere('phone', 'like', "%{$search}%");
            });
        }

        $users = $query->orderBy('role')->orderBy('name')->paginate(15);

        // Edit user mode jika edit_id dikirimkan
        $editUser = null;
        if ($request->filled('edit_id')) {
            $editUser = User::find($request->edit_id);
        }

        // Statistik Pengguna untuk Tab
        $countTotal = User::count();
        $countMahasiswa = User::where('role', 'mahasiswa')->count();
        $countDosen = User::where('role', 'dosen')->count();
        $countAdmin = User::where('role', 'admin')->count();

        return view('admin.pengguna', compact(
            'users',
            'editUser',
            'countTotal',
            'countMahasiswa',
            'countDosen',
            'countAdmin'
        ));
    }

    /**
     * MANAJEMEN PENGGUNA: Update Data Profil Pengguna
     */
    public function updatePengguna(Request $request, $id)
    {
        $user = User::findOrFail($id);

        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'email', 'max:255', "unique:users,email,{$id}"],
            'nim_nip' => ['nullable', 'string', 'max:50', "unique:users,nim_nip,{$id}"],
            'phone' => ['nullable', 'string', 'max:20'],
        ], [
            'name.required' => 'Nama pengguna wajib diisi.',
            'email.required' => 'Alamat email wajib diisi.',
            'email.unique' => 'Email ini sudah digunakan oleh akun lain.',
            'nim_nip.unique' => 'NIM / NIP sudah terdaftar pada akun lain.',
        ]);

        $user->update([
            'name' => trim($validated['name']),
            'email' => strtolower(trim($validated['email'])),
            'nim_nip' => $validated['nim_nip'] ? trim($validated['nim_nip']) : null,
            'phone' => $validated['phone'] ? trim($validated['phone']) : null,
        ]);

        return redirect()->route('admin.kelola-pengguna')
            ->with('success', "Data pengguna '{$user->name}' berhasil diperbarui.");
    }

    /**
     * MANAJEMEN PENGGUNA: Pengaturan Role Sesuai Kewenangan Admin
     */
    public function updateRolePengguna(Request $request, $id)
    {
        $user = User::findOrFail($id);

        $validated = $request->validate([
            'role' => ['required', 'in:admin,dosen,mahasiswa'],
        ]);

        // Proteksi Keamanan: Admin tidak boleh mencabut role dirinya sendiri
        if ($user->id === Auth::id() && $validated['role'] !== 'admin') {
            return back()->with('error', 'Anda tidak dapat menurunkan kewenangan role akun Anda sendiri.');
        }

        $oldRole = $user->role;
        $user->update(['role' => $validated['role']]);

        return redirect()->route('admin.kelola-pengguna')
            ->with('success', "Peran pengguna '{$user->name}' berhasil diubah dari {$oldRole} menjadi {$validated['role']}.");
    }

    /**
     * MANAJEMEN PENGGUNA: Hapus Pengguna (Dengan Proteksi)
     */
    public function destroyPengguna($id)
    {
        $user = User::findOrFail($id);

        // Proteksi: Tidak boleh menghapus akun yang sedang login
        if ($user->id === Auth::id()) {
            return back()->with('error', 'Anda tidak dapat menghapus akun Anda sendiri.');
        }

        // Proteksi: Tidak boleh menghapus pengguna dengan peminjaman aktif
        $hasActiveBorrows = BorrowRequest::where('user_id', $user->id)
            ->whereIn('status', ['menunggu', 'disetujui', 'dipinjam'])
            ->exists();

        if ($hasActiveBorrows) {
            return back()->with('error', "Pengguna '{$user->name}' memiliki transaksi peminjaman aktif yang belum selesai.");
        }

        $userName = $user->name;
        $user->delete();

        return redirect()->route('admin.kelola-pengguna')
            ->with('success', "Pengguna '{$userName}' berhasil dihapus dari sistem.");
    }

    /**
     * Helper Monitoring Transaksi (Untuk integrasi modul jika ada)
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
        $selectedId = $request->get('selected_id', $transactions->first()?->id);
        $selectedTx = $selectedId
            ? BorrowRequest::with(['user', 'facility', 'approver', 'qrTokens', 'returnRecord.lecturer'])->find($selectedId)
            : $transactions->first();

        return view('admin.transaksi-berjalan', compact('transactions', 'selectedTx'));
    }

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
}
