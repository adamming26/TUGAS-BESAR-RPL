@extends('layouts.dashboard')

@section('content')
<!-- [MARKER M6] : RIWAYAT TRANSAKSI LENGKAP ADMIN PERSIS FIGMA (PAGE 15) -->
<div class="space-y-6">
    <!-- Header Title -->
    <div>
        <h1 class="text-2xl font-bold text-slate-900">Riwayat Transaksi</h1>
        <p class="text-xs text-slate-500 mt-0.5">Arsip transaksi untuk mengetahui penggunaan barang dan lab.</p>
    </div>

    <!-- Container Card -->
    <div class="bg-white rounded-3xl p-6 sm:p-8 shadow-sm border border-slate-100 space-y-6">
        <h3 class="text-base font-bold text-slate-900">Semua riwayat</h3>

        <!-- Filter Bar Persis Figma -->
        <form method="GET" action="{{ route('admin.riwayat-transaksi') }}" class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-12 gap-3 items-end">
            <!-- Dari Tanggal -->
            <div class="lg:col-span-2">
                <label for="start_date" class="block text-xs font-semibold text-slate-700 mb-1.5">Dari tanggal</label>
                <input type="date" 
                       name="start_date" 
                       id="start_date" 
                       value="{{ request('start_date') }}"
                       class="w-full px-3 py-2 rounded-xl border border-slate-200 text-xs focus:outline-none focus:ring-2 focus:ring-[#2563EB]">
            </div>

            <!-- Sampai Tanggal -->
            <div class="lg:col-span-2">
                <label for="end_date" class="block text-xs font-semibold text-slate-700 mb-1.5">Sampai tanggal</label>
                <input type="date" 
                       name="end_date" 
                       id="end_date" 
                       value="{{ request('end_date') }}"
                       class="w-full px-3 py-2 rounded-xl border border-slate-200 text-xs focus:outline-none focus:ring-2 focus:ring-[#2563EB]">
            </div>

            <!-- Jenis -->
            <div class="lg:col-span-2">
                <label for="type" class="block text-xs font-semibold text-slate-700 mb-1.5">Jenis</label>
                <select name="type" id="type" 
                        class="w-full px-3 py-2 rounded-xl border border-slate-200 text-xs focus:outline-none focus:ring-2 focus:ring-[#2563EB] bg-white">
                    <option value="">Semua</option>
                    <option value="barang" {{ request('type') === 'barang' ? 'selected' : '' }}>Barang</option>
                    <option value="ruangan" {{ request('type') === 'ruangan' ? 'selected' : '' }}>Lab</option>
                </select>
            </div>

            <!-- Status -->
            <div class="lg:col-span-2">
                <label for="status" class="block text-xs font-semibold text-slate-700 mb-1.5">Status</label>
                <select name="status" id="status" 
                        class="w-full px-3 py-2 rounded-xl border border-slate-200 text-xs focus:outline-none focus:ring-2 focus:ring-[#2563EB] bg-white">
                    <option value="">Semua</option>
                    <option value="selesai" {{ request('status') === 'selesai' ? 'selected' : '' }}>Selesai</option>
                    <option value="ditolak" {{ request('status') === 'ditolak' ? 'selected' : '' }}>Ditolak</option>
                </select>
            </div>

            <!-- Tombol Terapkan Persis Figma -->
            <div class="lg:col-span-1">
                <button type="submit" 
                        class="w-full py-2 px-3 rounded-xl bg-[#2563EB] hover:bg-blue-700 text-white font-semibold text-xs transition shadow-sm">
                    Terapkan
                </button>
            </div>

            <!-- Cari Data -->
            <div class="lg:col-span-3">
                <label for="search" class="block text-xs font-semibold text-slate-700 mb-1.5">Cari</label>
                <div class="relative">
                    <input type="text" 
                           name="search" 
                           id="search" 
                           value="{{ request('search') }}"
                           placeholder="Cari data" 
                           class="w-full px-3 py-2 rounded-xl border border-slate-200 text-xs focus:outline-none focus:ring-2 focus:ring-[#2563EB]">
                    @if(request()->anyFilled(['start_date', 'end_date', 'type', 'status', 'search']))
                        <a href="{{ route('admin.riwayat-transaksi') }}" class="absolute right-2.5 top-2 text-slate-400 hover:text-slate-600 text-xs" title="Reset filter">&times;</a>
                    @endif
                </div>
            </div>
        </form>

        <!-- Tabel Riwayat Persis Figma -->
        <div class="overflow-x-auto">
            <table class="w-full text-left border-collapse">
                <thead>
                    <tr class="border-b border-slate-100 text-[11px] font-semibold text-slate-400 uppercase tracking-wider">
                        <th class="py-3 px-3">Tanggal</th>
                        <th class="py-3 px-3">Kode</th>
                        <th class="py-3 px-3">Fasilitas</th>
                        <th class="py-3 px-3">Peminjam</th>
                        <th class="py-3 px-3">Jenis</th>
                        <th class="py-3 px-3">Disetujui oleh</th>
                        <th class="py-3 px-3">Dikonfirmasi oleh</th>
                        <th class="py-3 px-3">Kondisi kembali</th>
                        <th class="py-3 px-3 text-right">Status</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-50 text-xs text-slate-700">
                    @forelse($riwayat as $item)
                        <tr class="hover:bg-slate-50/80 transition">
                            <td class="py-3.5 px-3 text-slate-600">
                                {{ \Carbon\Carbon::parse($item->borrow_date)->isoFormat('D MMM Y') }}
                            </td>
                            <td class="py-3.5 px-3 font-mono font-medium text-slate-600 text-[11px]">
                                [{{ $item->facility ? $item->facility->code : substr($item->code, -7) }}]
                            </td>
                            <td class="py-3.5 px-3 font-semibold text-slate-900">
                                {{ $item->facility ? $item->facility->name : 'Fasilitas' }}
                            </td>
                            <td class="py-3.5 px-3 text-slate-800">
                                {{ $item->user ? $item->user->name : 'Mahasiswa' }}
                            </td>
                            <td class="py-3.5 px-3 text-slate-500 capitalize">
                                {{ $item->type === 'ruangan' ? 'Lab' : 'Barang' }}
                            </td>
                            <td class="py-3.5 px-3 text-slate-600">
                                {{ $item->approver ? $item->approver->name : '-' }}
                            </td>
                            <td class="py-3.5 px-3 text-slate-600">
                                {{ $item->returnRecord?->lecturer ? $item->returnRecord->lecturer->name : ($item->approver ? $item->approver->name : '-') }}
                            </td>
                            <td class="py-3.5 px-3">
                                @if($item->returnRecord)
                                    <span class="text-slate-800">{{ $item->returnRecord->condition }}</span>
                                @else
                                    <span class="text-slate-400">-</span>
                                @endif
                            </td>
                            <td class="py-3.5 px-3 text-right">
                                @if(in_array($item->status, ['dikembalikan', 'selesai']))
                                    <span class="inline-block px-3 py-1 rounded-full text-[10px] font-semibold bg-emerald-50 text-emerald-700 border border-emerald-200">
                                        Selesai
                                    </span>
                                @elseif($item->status === 'ditolak')
                                    <span class="inline-block px-3 py-1 rounded-full text-[10px] font-semibold bg-rose-50 text-rose-700 border border-rose-200">
                                        Ditolak
                                    </span>
                                @else
                                    <span class="inline-block px-3 py-1 rounded-full text-[10px] font-semibold bg-slate-100 text-slate-700">
                                        {{ ucfirst($item->status) }}
                                    </span>
                                @endif
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="9" class="py-8 text-center text-slate-400">
                                Belum ada arsip riwayat transaksi yang tersimpan.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <!-- Footer Pagination Persis Figma -->
        <div class="flex flex-col sm:flex-row items-center justify-between gap-4 pt-4 border-t border-slate-100 text-xs text-slate-500">
            <div>
                Menampilkan {{ $riwayat->firstItem() ?? 0 }} dari {{ $riwayat->total() }} data
            </div>
            <div class="flex items-center space-x-2">
                @if($riwayat->onFirstPage())
                    <span class="px-4 py-2 rounded-xl border border-slate-200 text-slate-300 cursor-not-allowed">
                        Sebelumnya
                    </span>
                @else
                    <a href="{{ $riwayat->previousPageUrl() }}" class="px-4 py-2 rounded-xl border border-slate-200 text-slate-700 hover:bg-slate-50 transition">
                        Sebelumnya
                    </a>
                @endif

                @if($riwayat->hasMorePages())
                    <a href="{{ $riwayat->nextPageUrl() }}" class="px-4 py-2 rounded-xl border border-slate-200 text-slate-700 hover:bg-slate-50 transition">
                        Berikutnya
                    </a>
                @else
                    <span class="px-4 py-2 rounded-xl border border-slate-200 text-slate-300 cursor-not-allowed">
                        Berikutnya
                    </span>
                @endif
            </div>
        </div>
    </div>
</div>
@endsection
