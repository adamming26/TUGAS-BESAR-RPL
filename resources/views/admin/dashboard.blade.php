@extends('layouts.dashboard')

@section('content')
<!-- [MARKER M2] & [MARKER M6] : DASHBOARD ADMIN PERSIS FIGMA (PAGE 12) -->
<div class="space-y-6">
    <!-- Header Title -->
    <div>
        <h1 class="text-2xl font-bold text-slate-900">Dashboard Admin</h1>
        <p class="text-xs text-slate-500 mt-0.5">Kelola data barang dan lab, serta pantau transaksi peminjaman.</p>
    </div>

    <!-- Main Grid: Kelola Fasilitas (Kiri 7 cols) & Transaksi Berjalan (Kanan 5 cols) -->
    <div class="grid grid-cols-1 lg:grid-cols-12 gap-6 items-start">
        <!-- Kolom Kiri: Kelola Fasilitas (7 Cols) Persis Figma -->
        <div class="lg:col-span-7 bg-white rounded-3xl p-6 sm:p-8 shadow-sm border border-slate-100 space-y-5">
            <h3 class="text-base font-bold text-slate-900">Kelola fasilitas</h3>

            <!-- Tombol Tambah Barang & Tambah Lab -->
            <div class="flex items-center space-x-3">
                <a href="{{ route('admin.kelola-fasilitas', ['open' => 'barang']) }}" 
                   class="px-5 py-2.5 rounded-xl bg-[#2563EB] hover:bg-blue-700 text-white font-semibold text-xs transition shadow-sm">
                    Tambah barang
                </a>
                <a href="{{ route('admin.kelola-fasilitas', ['open' => 'ruangan']) }}" 
                   class="px-5 py-2.5 rounded-xl border border-slate-200 text-slate-700 hover:bg-slate-50 font-semibold text-xs transition">
                    Tambah lab
                </a>
            </div>

            <!-- Tabel Mini Fasilitas -->
            <div class="overflow-x-auto">
                <table class="w-full text-left border-collapse">
                    <thead>
                        <tr class="border-b border-slate-100 text-[11px] font-semibold text-slate-400 uppercase tracking-wider">
                            <th class="py-3 px-3">Nama</th>
                            <th class="py-3 px-3">Stok / Kapasitas</th>
                            <th class="py-3 px-3 text-center">Status</th>
                            <th class="py-3 px-3 text-right">Aksi</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-50 text-xs text-slate-700">
                        @forelse($facilities as $f)
                            <tr class="hover:bg-slate-50/80 transition">
                                <td class="py-3.5 px-3 font-semibold text-slate-900">
                                    {{ $f->name }}
                                </td>
                                <td class="py-3.5 px-3 text-slate-600">
                                    @if($f->type === 'barang')
                                        {{ $f->available_stock }} / {{ $f->total_stock }}
                                    @else
                                        {{ $f->capacity }} orang
                                    @endif
                                </td>
                                <td class="py-3.5 px-3 text-center">
                                    @if($f->is_active)
                                        <span class="inline-block px-2.5 py-1 rounded-full text-[10px] font-semibold bg-emerald-50 text-emerald-700 border border-emerald-200">
                                            Aktif
                                        </span>
                                    @else
                                        <span class="inline-block px-2.5 py-1 rounded-full text-[10px] font-semibold bg-rose-50 text-rose-700 border border-rose-200">
                                            Nonaktif
                                        </span>
                                    @endif
                                </td>
                                <td class="py-3.5 px-3 text-right">
                                    <a href="{{ route('admin.kelola-fasilitas', ['edit_id' => $f->id]) }}" 
                                       class="inline-block px-4 py-1.5 rounded-xl border border-slate-200 text-slate-700 hover:bg-slate-50 text-xs font-semibold transition">
                                        Ubah
                                    </a>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="4" class="py-6 text-center text-slate-400">
                                    Belum ada data fasilitas terdaftar.
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>

        <!-- Kolom Kanan: Transaksi Berjalan (5 Cols) Persis Figma -->
        <div class="lg:col-span-5 bg-white rounded-3xl p-6 sm:p-8 shadow-sm border border-slate-100 space-y-4">
            <div class="flex items-center justify-between">
                <h3 class="text-base font-bold text-slate-900">Transaksi berjalan</h3>
                <a href="{{ route('admin.transaksi-berjalan') }}" class="text-xs text-[#2563EB] hover:underline font-semibold">Lihat Semua</a>
            </div>

            <!-- Tabel Mini Transaksi Berjalan -->
            <div class="overflow-x-auto">
                <table class="w-full text-left border-collapse">
                    <thead>
                        <tr class="border-b border-slate-100 text-[11px] font-semibold text-slate-400 uppercase tracking-wider">
                            <th class="py-3 px-3">Peminjam</th>
                            <th class="py-3 px-3">Fasilitas</th>
                            <th class="py-3 px-3 text-right">Status</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-50 text-xs text-slate-700">
                        @forelse($activeTransactions as $tx)
                            <tr class="hover:bg-slate-50/80 transition">
                                <td class="py-3.5 px-3 font-semibold text-slate-900">
                                    {{ $tx->user ? $tx->user->name : 'Mahasiswa' }}
                                </td>
                                <td class="py-3.5 px-3 text-slate-600">
                                    {{ $tx->facility ? $tx->facility->name : 'Fasilitas' }}
                                </td>
                                <td class="py-3.5 px-3 text-right">
                                    @if($tx->status === 'dipinjam')
                                        <span class="inline-block px-2.5 py-1 rounded-full text-[10px] font-semibold bg-blue-50 text-[#2563EB] border border-blue-200">
                                            Dipinjam
                                        </span>
                                    @elseif($tx->status === 'disetujui')
                                        <span class="inline-block px-2.5 py-1 rounded-full text-[10px] font-semibold bg-amber-50 text-amber-700 border border-amber-200">
                                            Menunggu serah
                                        </span>
                                    @else
                                        <span class="inline-block px-2.5 py-1 rounded-full text-[10px] font-semibold bg-slate-100 text-slate-700">
                                            {{ ucfirst($tx->status) }}
                                        </span>
                                    @endif
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="3" class="py-6 text-center text-slate-400">
                                    Tidak ada transaksi peminjaman berjalan.
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>
@endsection
