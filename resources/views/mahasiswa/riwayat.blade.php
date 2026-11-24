@extends('layouts.dashboard')

@section('content')
<!-- [MARKER M3] : RIWAYAT PEMINJAMAN MAHASISWA PERSIS FIGMA (PAGE 8) -->
<div class="space-y-6">
    <!-- Header Title -->
    <div>
        <h1 class="text-2xl font-bold text-slate-900">Riwayat Peminjaman</h1>
        <p class="text-xs text-slate-500 mt-0.5">Catatan seluruh peminjaman dan pengembalian Anda.</p>
    </div>

    <!-- Container Card -->
    <div class="bg-white rounded-3xl p-6 sm:p-8 shadow-sm border border-slate-100 space-y-6">
        <h3 class="text-base font-bold text-slate-900">Semua riwayat</h3>

        <!-- Filter Bar Persis Figma -->
        <form method="GET" action="{{ route('mahasiswa.riwayat') }}" class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-12 gap-4 items-end">
            <!-- Cari -->
            <div class="lg:col-span-5">
                <label for="search" class="block text-xs font-semibold text-slate-700 mb-1.5">Cari</label>
                <input type="text" 
                       name="search" 
                       id="search" 
                       value="{{ request('search') }}"
                       placeholder="Cari data" 
                       class="w-full px-4 py-2.5 rounded-xl border border-slate-200 text-xs focus:outline-none focus:ring-2 focus:ring-[#2563EB]">
            </div>

            <!-- Jenis -->
            <div class="lg:col-span-2">
                <label for="type" class="block text-xs font-semibold text-slate-700 mb-1.5">Jenis</label>
                <select name="type" id="type" onchange="this.form.submit()" 
                        class="w-full px-3 py-2.5 rounded-xl border border-slate-200 text-xs focus:outline-none focus:ring-2 focus:ring-[#2563EB] bg-white">
                    <option value="">Semua</option>
                    <option value="barang" {{ request('type') === 'barang' ? 'selected' : '' }}>Barang</option>
                    <option value="ruangan" {{ request('type') === 'ruangan' ? 'selected' : '' }}>Lab</option>
                </select>
            </div>

            <!-- Status -->
            <div class="lg:col-span-2">
                <label for="status" class="block text-xs font-semibold text-slate-700 mb-1.5">Status</label>
                <select name="status" id="status" onchange="this.form.submit()" 
                        class="w-full px-3 py-2.5 rounded-xl border border-slate-200 text-xs focus:outline-none focus:ring-2 focus:ring-[#2563EB] bg-white">
                    <option value="">Semua</option>
                    <option value="dikembalikan" {{ request('status') === 'dikembalikan' ? 'selected' : '' }}>Dikembalikan</option>
                    <option value="selesai" {{ request('status') === 'selesai' ? 'selected' : '' }}>Selesai</option>
                    <option value="ditolak" {{ request('status') === 'ditolak' ? 'selected' : '' }}>Ditolak</option>
                </select>
            </div>

            <!-- Dari Tanggal -->
            <div class="lg:col-span-3">
                <label for="date" class="block text-xs font-semibold text-slate-700 mb-1.5">Dari tanggal</label>
                <div class="flex items-center space-x-2">
                    <input type="date" 
                           name="date" 
                           id="date" 
                           value="{{ request('date') }}" 
                           onchange="this.form.submit()"
                           class="w-full px-3 py-2.5 rounded-xl border border-slate-200 text-xs focus:outline-none focus:ring-2 focus:ring-[#2563EB]">
                    @if(request()->anyFilled(['search', 'type', 'status', 'date']))
                        <a href="{{ route('mahasiswa.riwayat') }}" class="p-2.5 text-xs text-slate-400 hover:text-slate-600 rounded-xl border border-slate-200" title="Reset filter">
                            &times;
                        </a>
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
                        <th class="py-3 px-3">Fasilitas</th>
                        <th class="py-3 px-3">Jenis</th>
                        <th class="py-3 px-3">Diputuskan oleh</th>
                        <th class="py-3 px-3">Waktu kembali</th>
                        <th class="py-3 px-3 text-right">Status</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-50 text-xs text-slate-700">
                    @forelse($riwayat as $item)
                        <tr class="hover:bg-slate-50/80 transition">
                            <td class="py-3.5 px-3 text-slate-600">
                                {{ \Carbon\Carbon::parse($item->borrow_date)->isoFormat('D MMM Y') }}
                            </td>
                            <td class="py-3.5 px-3 font-semibold text-slate-900">
                                {{ $item->facility ? $item->facility->name : 'Fasilitas' }}
                            </td>
                            <td class="py-3.5 px-3 text-slate-500 capitalize">
                                {{ $item->type === 'ruangan' ? 'Lab' : 'Barang' }}
                            </td>
                            <td class="py-3.5 px-3 text-slate-600">
                                {{ $item->approver ? $item->approver->name : '-' }}
                            </td>
                            <td class="py-3.5 px-3 text-slate-600">
                                @if($item->returnRecord)
                                    {{ \Carbon\Carbon::parse($item->returnRecord->return_date)->isoFormat('D MMM Y, HH:mm') }}
                                @else
                                    -
                                @endif
                            </td>
                            <td class="py-3.5 px-3 text-right">
                                @if(in_array($item->status, ['dikembalikan']))
                                    <span class="inline-block px-3 py-1 rounded-full text-[10px] font-semibold bg-emerald-50 text-emerald-700 border border-emerald-200">
                                        Dikembalikan
                                    </span>
                                @elseif($item->status === 'selesai')
                                    <span class="inline-block px-3 py-1 rounded-full text-[10px] font-semibold bg-teal-50 text-teal-700 border border-teal-200">
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
                            <td colspan="6" class="py-8 text-center text-slate-400">
                                Belum ada catatan riwayat peminjaman.
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
