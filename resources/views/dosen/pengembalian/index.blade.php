@extends('layouts.dashboard')

@section('content')
<!-- [MARKER M6] : DAFTAR PEMINJAMAN AKTIF SIAP KONFIRMASI PENGEMBALIAN -->
<div class="space-y-6">
    <!-- Header & Navigasi M6 -->
    <div class="flex flex-col lg:flex-row lg:items-center lg:justify-between gap-4">
        <div>
            <h1 class="text-2xl font-bold text-slate-900">Konfirmasi Pengembalian</h1>
            <p class="text-xs text-slate-500 mt-0.5">Pilih transaksi peminjaman aktif untuk pemeriksaan kondisi barang, verifikasi QR, dan pemulihan stok.</p>
        </div>

        @include('dosen.partials.m6-nav-tabs', ['activeReturnCount' => $activeReturnCount])
    </div>

    <!-- Ringkasan Statistik Peminjaman Aktif -->
    <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
        <div class="bg-white rounded-2xl p-5 border border-slate-100 shadow-sm">
            <p class="text-[11px] font-semibold text-slate-400 uppercase tracking-wider">Sedang Dipinjam</p>
            <p class="text-2xl font-extrabold text-[#0C1A40] mt-1">{{ $summary['total_active'] }}</p>
            <p class="text-[11px] text-slate-500 mt-0.5">Menunggu konfirmasi pengembalian</p>
        </div>

        <div class="bg-white rounded-2xl p-5 border border-slate-100 shadow-sm">
            <p class="text-[11px] font-semibold text-slate-400 uppercase tracking-wider">Peminjaman Barang</p>
            <p class="text-2xl font-extrabold text-[#2563EB] mt-1">{{ $summary['barang_active'] }}</p>
            <p class="text-[11px] text-slate-500 mt-0.5">Memerlukan cek kondisi & stok kembali</p>
        </div>

        <div class="bg-white rounded-2xl p-5 border border-slate-100 shadow-sm">
            <p class="text-[11px] font-semibold text-slate-400 uppercase tracking-wider">Penggunaan Laboratorium</p>
            <p class="text-2xl font-extrabold text-[#059669] mt-1">{{ $summary['lab_active'] }}</p>
            <p class="text-[11px] text-slate-500 mt-0.5">Lab aktif berlangsung</p>
        </div>
    </div>

    <!-- Daftar Peminjaman Aktif -->
    <div class="bg-white rounded-3xl p-6 sm:p-8 shadow-sm border border-slate-100 space-y-6">
        <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3">
            <!-- Filter Jenis -->
            <div class="flex items-center space-x-1 bg-slate-100 p-1 rounded-2xl w-fit">
                <a href="{{ route('dosen.pengembalian.index', request()->only('search')) }}"
                   class="px-4 py-1.5 rounded-xl text-xs font-semibold transition {{ !$selectedType ? 'bg-[#2563EB] text-white shadow-sm' : 'text-slate-600 hover:text-slate-900' }}">
                    Semua
                </a>
                <a href="{{ route('dosen.pengembalian.index', array_merge(request()->only('search'), ['type' => 'barang'])) }}"
                   class="px-4 py-1.5 rounded-xl text-xs font-semibold transition {{ $selectedType === 'barang' ? 'bg-[#2563EB] text-white shadow-sm' : 'text-slate-600 hover:text-slate-900' }}">
                    Barang
                </a>
                <a href="{{ route('dosen.pengembalian.index', array_merge(request()->only('search'), ['type' => 'ruangan'])) }}"
                   class="px-4 py-1.5 rounded-xl text-xs font-semibold transition {{ $selectedType === 'ruangan' ? 'bg-[#2563EB] text-white shadow-sm' : 'text-slate-600 hover:text-slate-900' }}">
                    Laboratorium
                </a>
            </div>

            <!-- Pencarian -->
            <form method="GET" action="{{ route('dosen.pengembalian.index') }}" class="w-full sm:w-72">
                @if($selectedType)
                    <input type="hidden" name="type" value="{{ $selectedType }}">
                @endif
                <div class="flex gap-2">
                    <input type="text"
                           name="search"
                           value="{{ request('search') }}"
                           placeholder="Cari kode, nama peminjam, fasilitas..."
                           class="flex-1 px-3.5 py-2 rounded-xl border border-slate-200 text-xs focus:outline-none focus:ring-2 focus:ring-[#2563EB]">
                    <button type="submit"
                            class="px-4 py-2 rounded-xl bg-[#0C1A40] hover:bg-slate-900 text-white text-xs font-semibold transition">
                        Cari
                    </button>
                </div>
            </form>
        </div>

        <div class="overflow-x-auto">
            <table class="w-full text-left border-collapse">
                <thead>
                    <tr class="border-b border-slate-100 text-[11px] font-semibold text-slate-400 uppercase tracking-wider">
                        <th class="py-3 px-3">Kode</th>
                        <th class="py-3 px-3">Peminjam</th>
                        <th class="py-3 px-3">Fasilitas</th>
                        <th class="py-3 px-3">Jenis</th>
                        <th class="py-3 px-3">Jumlah</th>
                        <th class="py-3 px-3">Waktu Serah Terima</th>
                        <th class="py-3 px-3">Status</th>
                        <th class="py-3 px-3 text-right">Aksi</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-50 text-xs text-slate-700">
                    @forelse($activeLoans as $loan)
                        @php
                            $expectedQty = \App\Http\Controllers\M6\QrScannerController::resolveExpectedQuantity($loan);
                            $handoverAt = \App\Http\Controllers\M6\QrScannerController::resolveHandoverTimestamp($loan);
                        @endphp
                        <tr class="hover:bg-slate-50/80 transition">
                            <td class="py-3.5 px-3 font-mono font-bold text-[#0C1A40]">
                                {{ $loan->code }}
                            </td>
                            <td class="py-3.5 px-3">
                                <p class="font-semibold text-slate-900">{{ $loan->user?->name ?? 'Mahasiswa' }}</p>
                                <p class="text-[11px] text-slate-400 font-mono">{{ $loan->user?->nim_nip ?? '-' }}</p>
                            </td>
                            <td class="py-3.5 px-3">
                                <p class="font-semibold text-slate-900">{{ $loan->facility?->name ?? 'Fasilitas' }}</p>
                                <p class="text-[11px] text-slate-400 font-mono">{{ $loan->facility?->code ?? '-' }}</p>
                            </td>
                            <td class="py-3.5 px-3">
                                <span class="px-2.5 py-0.5 rounded-full text-[10px] font-semibold bg-slate-100 text-slate-700">
                                    {{ $loan->type === 'ruangan' ? 'Laboratorium' : 'Barang' }}
                                </span>
                            </td>
                            <td class="py-3.5 px-3 font-semibold text-slate-800">
                                {{ $expectedQty }} {{ $loan->type === 'ruangan' ? 'peserta' : 'unit' }}
                            </td>
                            <td class="py-3.5 px-3 text-slate-600">
                                @if($handoverAt)
                                    {{ $handoverAt->format('d M Y H:i') }}
                                @else
                                    {{ \Carbon\Carbon::parse($loan->borrow_date)->format('d M Y') }} {{ substr((string) $loan->start_time, 0, 5) }}
                                @endif
                            </td>
                            <td class="py-3.5 px-3">
                                <span class="inline-block px-2.5 py-1 rounded-full text-[10px] font-semibold bg-blue-50 text-[#2563EB] border border-blue-200">
                                    Dipinjam
                                </span>
                            </td>
                            <td class="py-3.5 px-3 text-right">
                                <a href="{{ route('dosen.pengembalian.show', $loan->id) }}"
                                   class="inline-flex items-center px-3.5 py-2 rounded-xl bg-[#2563EB] hover:bg-blue-700 text-white text-xs font-semibold transition shadow-sm">
                                    Periksa & Konfirmasi
                                </a>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="8" class="py-10 text-center text-slate-400">
                                Tidak ada transaksi peminjaman aktif yang menunggu pengembalian.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @if($activeLoans->hasPages())
            <div class="pt-4 border-t border-slate-100">
                {{ $activeLoans->links() }}
            </div>
        @endif
    </div>
</div>
@endsection
