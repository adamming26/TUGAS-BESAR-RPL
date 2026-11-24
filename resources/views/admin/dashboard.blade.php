@extends('layouts.dashboard')

@section('content')
<!-- DASHBOARD ADMIN (M1 — SIPAFKA) -->
<div class="space-y-8">
    <!-- Header Page -->
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
        <div>
            <h1 class="text-2xl lg:text-3xl font-extrabold text-[#0C1A40] tracking-tight">Dashboard Admin</h1>
            <p class="text-xs sm:text-sm text-slate-500 mt-1">
                Ikhtisar sistem inventaris prodi, statistik pengguna, ketersediaan fasilitas, dan aktivitas terkini.
            </p>
        </div>
        <div class="flex items-center space-x-2">
            <a href="{{ route('admin.kelola-fasilitas', ['action' => 'new', 'type' => 'barang']) }}" 
               class="px-4 py-2 rounded-xl bg-[#2563EB] hover:bg-blue-700 text-white font-semibold text-xs transition shadow-sm inline-flex items-center space-x-1.5">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"></path>
                </svg>
                <span>Tambah Barang</span>
            </a>
            <a href="{{ route('admin.kelola-fasilitas', ['action' => 'new', 'type' => 'ruangan']) }}" 
               class="px-4 py-2 rounded-xl border border-slate-300 bg-white hover:bg-slate-50 text-slate-700 font-semibold text-xs transition shadow-sm inline-flex items-center space-x-1.5">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"></path>
                </svg>
                <span>Tambah Lab</span>
            </a>
        </div>
    </div>

    <!-- 1. KARTU STATISTIK UTAMA (Pengguna, Fasilitas, Ringkasan Barang, Ringkasan Lab) -->
    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-5">
        <!-- Stat 1: Total Pengguna -->
        <div class="bg-white rounded-3xl p-6 shadow-sm border border-slate-200/80 hover:shadow-md transition">
            <div class="flex items-center justify-between mb-3">
                <span class="text-xs font-bold uppercase tracking-wider text-slate-400">Total Pengguna</span>
                <div class="w-9 h-9 rounded-2xl bg-blue-50 text-[#2563EB] flex items-center justify-center">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z"></path>
                    </svg>
                </div>
            </div>
            <div class="text-3xl font-extrabold text-[#0C1A40] mb-3">{{ $userStats['total'] }}</div>
            <div class="flex items-center space-x-1.5 text-[11px] font-medium text-slate-500 pt-2 border-t border-slate-100">
                <span class="px-2 py-0.5 rounded-md bg-blue-50 text-blue-700 font-semibold">{{ $userStats['mahasiswa'] }} Mahasiswa</span>
                <span class="px-2 py-0.5 rounded-md bg-amber-50 text-amber-700 font-semibold">{{ $userStats['dosen'] }} Dosen</span>
                <span class="px-2 py-0.5 rounded-md bg-slate-100 text-slate-700 font-semibold">{{ $userStats['admin'] }} Admin</span>
            </div>
        </div>

        <!-- Stat 2: Total Fasilitas -->
        <div class="bg-white rounded-3xl p-6 shadow-sm border border-slate-200/80 hover:shadow-md transition">
            <div class="flex items-center justify-between mb-3">
                <span class="text-xs font-bold uppercase tracking-wider text-slate-400">Total Fasilitas</span>
                <div class="w-9 h-9 rounded-2xl bg-indigo-50 text-indigo-600 flex items-center justify-center">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 012-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10"></path>
                    </svg>
                </div>
            </div>
            <div class="text-3xl font-extrabold text-[#0C1A40] mb-3">{{ $facilityStats['total'] }}</div>
            <div class="flex items-center justify-between text-[11px] text-slate-500 pt-2 border-t border-slate-100 font-medium">
                <span>{{ $facilityStats['barang'] }} Jenis Barang</span>
                <span>•</span>
                <span>{{ $facilityStats['lab'] }} Laboratorium</span>
            </div>
        </div>

        <!-- Stat 3: Ringkasan Barang -->
        <div class="bg-white rounded-3xl p-6 shadow-sm border border-slate-200/80 hover:shadow-md transition">
            <div class="flex items-center justify-between mb-3">
                <span class="text-xs font-bold uppercase tracking-wider text-slate-400">Ringkasan Barang</span>
                <div class="w-9 h-9 rounded-2xl bg-emerald-50 text-[#059669] flex items-center justify-center">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4"></path>
                    </svg>
                </div>
            </div>
            <div class="text-3xl font-extrabold text-[#059669] mb-1">
                {{ $barangSummary['available_units'] }} <span class="text-xs font-bold text-slate-400">/ {{ $barangSummary['total_units'] }} Unit</span>
            </div>
            <p class="text-[11px] text-slate-500 mb-2">Stok fisik barang siap pakai</p>
            <div class="flex items-center justify-between text-[11px] pt-2 border-t border-slate-100 font-medium text-slate-600">
                <span class="text-[#059669]">{{ $barangSummary['kondisi_baik'] }} Kondisi Baik</span>
                <span class="text-[#D97706]">{{ $barangSummary['kondisi_rusak'] }} Rusak</span>
            </div>
        </div>

        <!-- Stat 4: Ringkasan Laboratorium -->
        <div class="bg-white rounded-3xl p-6 shadow-sm border border-slate-200/80 hover:shadow-md transition">
            <div class="flex items-center justify-between mb-3">
                <span class="text-xs font-bold uppercase tracking-wider text-slate-400">Laboratorium</span>
                <div class="w-9 h-9 rounded-2xl bg-purple-50 text-purple-600 flex items-center justify-center">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9.75 17L9 20l-1 1h8l-1-1-.75-3M3 13h18M5 17h14a2 2 0 002-2V5a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"></path>
                    </svg>
                </div>
            </div>
            <div class="text-3xl font-extrabold text-[#0C1A40] mb-1">
                {{ $labSummary['total_capacity'] }} <span class="text-xs font-bold text-slate-400">Kursi</span>
            </div>
            <p class="text-[11px] text-slate-500 mb-2">Kapasitas total peserta lab</p>
            <div class="flex items-center justify-between text-[11px] pt-2 border-t border-slate-100 font-medium text-slate-600">
                <span>{{ $labSummary['total_labs'] }} Unit Lab Komputer</span>
                <span class="text-[#059669]">{{ $labSummary['kondisi_baik'] }} Siap Pakai</span>
            </div>
        </div>
    </div>

    <!-- Peringatan Stok Kritis (Jika ada) -->
    @if($lowStockFacilities->isNotEmpty())
        <div class="bg-amber-50 border border-amber-200 rounded-3xl p-5 flex items-start space-x-3.5 shadow-sm">
            <div class="w-8 h-8 rounded-xl bg-amber-100 text-[#D97706] flex items-center justify-center shrink-0 mt-0.5">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"></path>
                </svg>
            </div>
            <div class="flex-1">
                <h4 class="text-sm font-bold text-amber-900">Perhatian: Stok Fasilitas Kritis / Terbatas</h4>
                <p class="text-xs text-amber-800 mt-0.5 leading-relaxed">
                    Terdapat {{ $lowStockFacilities->count() }} barang dengan stok tersedia sangat terbatas (<= 1 unit):
                    @foreach($lowStockFacilities as $low)
                        <span class="font-bold underline ml-1">{{ $low->name }} (Sisa: {{ $low->available_stock }})</span>{{ !$loop->last ? ',' : '.' }}
                    @endforeach
                    Harap lakukan pengecekan fisik atau pembaruan stok pada menu manajemen fasilitas.
                </p>
            </div>
            <a href="{{ route('admin.kelola-fasilitas', ['type' => 'barang']) }}" class="px-3.5 py-1.5 rounded-xl bg-[#D97706] text-white text-xs font-semibold hover:bg-amber-700 transition shrink-0">
                Kelola Stok
            </a>
        </div>
    @endif

    <!-- 2. KONTEN UTAMA: Aktivitas Sistem & Daftar Cepat Fasilitas -->
    <div class="grid grid-cols-1 lg:grid-cols-12 gap-6 items-start">
        <!-- Kolom Kiri (7 Cols): Aktivitas Sistem & Transaksi Terbaru Relevan -->
        <div class="lg:col-span-7 bg-white rounded-3xl p-6 sm:p-7 shadow-sm border border-slate-200/80 space-y-5">
            <div class="flex items-center justify-between">
                <div>
                    <h3 class="text-base font-bold text-[#0C1A40]">Aktivitas Sistem & Peminjaman</h3>
                    <p class="text-xs text-slate-500">Daftar permohonan peminjaman fasilitas terbaru yang masuk ke sistem.</p>
                </div>
                <a href="{{ route('admin.transaksi-berjalan') }}" class="text-xs text-[#2563EB] hover:underline font-semibold">
                    Semua Transaksi &rarr;
                </a>
            </div>

            <!-- Tabel Aktivitas Terbaru -->
            <div class="overflow-x-auto">
                <table class="w-full text-left border-collapse text-xs">
                    <thead>
                        <tr class="border-b border-slate-100 text-[11px] font-bold text-slate-400 uppercase tracking-wider">
                            <th class="py-3 px-3">Kode / Waktu</th>
                            <th class="py-3 px-3">Pemohon</th>
                            <th class="py-3 px-3">Fasilitas</th>
                            <th class="py-3 px-3 text-center">Status</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100 text-slate-700">
                        @forelse($recentActivities as $act)
                            <tr class="hover:bg-slate-50/80 transition">
                                <td class="py-3.5 px-3">
                                    <span class="font-mono font-semibold text-[#0C1A40] block">{{ $act->code }}</span>
                                    <span class="text-[10px] text-slate-400">{{ $act->borrow_date ? $act->borrow_date->format('d M Y') : '-' }}</span>
                                </td>
                                <td class="py-3.5 px-3">
                                    <span class="font-semibold text-slate-900 block">{{ $act->user->name ?? 'Pengguna' }}</span>
                                    <span class="text-[10px] text-slate-400 capitalize">{{ $act->user->role ?? '-' }}</span>
                                </td>
                                <td class="py-3.5 px-3">
                                    <span class="font-medium text-slate-900 block">{{ $act->facility->name ?? '-' }}</span>
                                    <span class="text-[10px] text-slate-400 capitalize">{{ $act->type === 'ruangan' ? 'Lab' : 'Barang' }}</span>
                                </td>
                                <td class="py-3.5 px-3 text-center">
                                    @php
                                        $statusClass = match($act->status) {
                                            'disetujui' => 'bg-emerald-50 text-[#059669] border-emerald-200',
                                            'menunggu' => 'bg-amber-50 text-[#D97706] border-amber-200',
                                            'dipinjam' => 'bg-blue-50 text-[#2563EB] border-blue-200',
                                            'dikembalikan', 'selesai' => 'bg-teal-50 text-teal-700 border-teal-200',
                                            'ditolak' => 'bg-rose-50 text-[#DC2626] border-rose-200',
                                            default => 'bg-slate-100 text-slate-700 border-slate-200',
                                        };
                                    @endphp
                                    <span class="inline-block px-2.5 py-1 rounded-full text-[10px] font-semibold border {{ $statusClass }} capitalize">
                                        {{ $act->status }}
                                    </span>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="4" class="py-8 text-center text-slate-400">
                                    Belum ada catatan aktivitas peminjaman di sistem.
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>

        <!-- Kolom Kanan (5 Cols): Data Cepat Fasilitas & Pengguna Baru -->
        <div class="lg:col-span-5 space-y-6">
            <!-- Widget Fasilitas Cepat -->
            <div class="bg-white rounded-3xl p-6 sm:p-7 shadow-sm border border-slate-200/80 space-y-4">
                <div class="flex items-center justify-between">
                    <div>
                        <h3 class="text-base font-bold text-[#0C1A40]">Data Fasilitas</h3>
                        <p class="text-xs text-slate-500">Status ketersediaan inventaris.</p>
                    </div>
                    <a href="{{ route('admin.kelola-fasilitas') }}" class="text-xs text-[#2563EB] hover:underline font-semibold">
                        Kelola &rarr;
                    </a>
                </div>

                <div class="divide-y divide-slate-100">
                    @foreach($facilities as $fac)
                        <div class="py-3 flex items-center justify-between text-xs">
                            <div class="flex items-center space-x-2.5">
                                <div class="w-8 h-8 rounded-xl {{ $fac->type === 'barang' ? 'bg-blue-50 text-[#2563EB]' : 'bg-purple-50 text-purple-600' }} flex items-center justify-center font-bold text-xs shrink-0">
                                    {{ $fac->type === 'barang' ? 'B' : 'L' }}
                                </div>
                                <div>
                                    <p class="font-semibold text-slate-900 leading-tight">{{ $fac->name }}</p>
                                    <p class="text-[10px] text-slate-400 font-mono">[{{ $fac->code }}] • {{ $fac->condition }}</p>
                                </div>
                            </div>
                            <div class="text-right">
                                <span class="font-bold text-slate-800">
                                    {{ $fac->type === 'barang' ? $fac->available_stock . '/' . $fac->total_stock : $fac->capacity . ' org' }}
                                </span>
                                <span class="block text-[10px] {{ $fac->is_active ? 'text-[#059669]' : 'text-[#DC2626]' }}">
                                    {{ $fac->is_active ? 'Aktif' : 'Nonaktif' }}
                                </span>
                            </div>
                        </div>
                    @endforeach
                </div>
            </div>

            <!-- Widget Pengguna Baru Terdaftar -->
            <div class="bg-white rounded-3xl p-6 sm:p-7 shadow-sm border border-slate-200/80 space-y-4">
                <div class="flex items-center justify-between">
                    <div>
                        <h3 class="text-base font-bold text-[#0C1A40]">Pengguna Baru</h3>
                        <p class="text-xs text-slate-500">Akun terdaftar terbaru di sistem.</p>
                    </div>
                    <a href="{{ route('admin.kelola-pengguna') }}" class="text-xs text-[#2563EB] hover:underline font-semibold">
                        Semua &rarr;
                    </a>
                </div>

                <div class="divide-y divide-slate-100">
                    @foreach($recentUsers as $usr)
                        <div class="py-2.5 flex items-center justify-between text-xs">
                            <div class="flex items-center space-x-2.5">
                                <div class="w-8 h-8 rounded-full bg-[#0C1A40] text-white flex items-center justify-center font-bold text-xs shrink-0">
                                    {{ strtoupper(substr($usr->name, 0, 1)) }}
                                </div>
                                <div>
                                    <p class="font-semibold text-slate-900 leading-tight">{{ $usr->name }}</p>
                                    <p class="text-[10px] text-slate-400 font-mono">{{ $usr->nim_nip ?: $usr->email }}</p>
                                </div>
                            </div>
                            <span class="px-2 py-0.5 rounded-full text-[10px] font-semibold border capitalize
                                {{ $usr->role === 'admin' ? 'bg-rose-50 text-[#DC2626] border-rose-200' : ($usr->role === 'dosen' ? 'bg-blue-50 text-[#2563EB] border-blue-200' : 'bg-emerald-50 text-[#059669] border-emerald-200') }}">
                                {{ $usr->role }}
                            </span>
                        </div>
                    @endforeach
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
