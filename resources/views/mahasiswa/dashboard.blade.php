@extends('layouts.dashboard')

@section('content')
<!-- [MARKER M3] & [MARKER M5] : BERANDA MAHASISWA PERSIS FIGMA (PAGE 4) -->
<div class="space-y-6">
    <!-- Header Title -->
    <div>
        <h1 class="text-2xl font-bold text-slate-900">Beranda Mahasiswa</h1>
        <p class="text-xs text-slate-500 mt-0.5">Cek ketersediaan, ajukan peminjaman, dan pantau statusnya.</p>
    </div>

    <!-- Banner Selamat Datang Persis Figma -->
    <div class="relative overflow-hidden rounded-3xl bg-gradient-to-r from-[#0C1A40] via-[#1e3a8a] to-[#2563EB] p-8 text-white shadow-lg">
        <div class="absolute right-0 top-0 bottom-0 w-1/2 opacity-25 pointer-events-none">
            <img src="{{ asset('images/gedung-kampus.jpg') }}" 
                 alt="Gedung Kampus FMIPA UHO" 
                 class="w-full h-full object-cover object-center">
        </div>
        <div class="relative z-10">
            <h2 class="text-2xl font-bold mb-1">
                Selamat Datang, {{ auth()->user()->name }}
            </h2>
            <p class="text-sm text-blue-100">
                Fasilitas apa yang ingin kamu pinjam hari ini?
            </p>
        </div>
    </div>

    <!-- Main Grid: Ketersediaan Fasilitas (Kiri) & Status/QR (Kanan) -->
    <div class="grid grid-cols-1 lg:grid-cols-12 gap-6 items-start">
        <!-- Kolom Kiri: Ketersediaan Fasilitas (8 Cols) -->
        <div class="lg:col-span-8 bg-white rounded-3xl p-6 shadow-sm border border-slate-100">
            <h3 class="text-base font-bold text-slate-900 mb-4">Ketersediaan fasilitas</h3>

            <!-- Filter Tabs & Pencarian Persis Figma -->
            <div class="flex flex-col sm:flex-row items-center justify-between gap-3 mb-5">
                <div class="flex items-center space-x-1 bg-slate-100 p-1 rounded-2xl w-full sm:w-auto">
                    <a href="{{ route('mahasiswa.dashboard') }}" 
                       class="px-4 py-1.5 rounded-xl text-xs font-semibold transition {{ !request('type') ? 'bg-[#2563EB] text-white shadow-sm' : 'text-slate-600 hover:text-slate-900' }}">
                        Semua
                    </a>
                    <a href="{{ route('mahasiswa.dashboard', ['type' => 'barang']) }}" 
                       class="px-4 py-1.5 rounded-xl text-xs font-semibold transition {{ request('type') === 'barang' ? 'bg-[#2563EB] text-white shadow-sm' : 'text-slate-600 hover:text-slate-900' }}">
                        Barang
                    </a>
                    <a href="{{ route('mahasiswa.dashboard', ['type' => 'ruangan']) }}" 
                       class="px-4 py-1.5 rounded-xl text-xs font-semibold transition {{ request('type') === 'ruangan' ? 'bg-[#2563EB] text-white shadow-sm' : 'text-slate-600 hover:text-slate-900' }}">
                        Lab
                    </a>
                </div>

                <form method="GET" action="{{ route('mahasiswa.dashboard') }}" class="w-full sm:w-64">
                    @if(request('type'))
                        <input type="hidden" name="type" value="{{ request('type') }}">
                    @endif
                    <div class="relative">
                        <input type="text" 
                               name="search" 
                               value="{{ request('search') }}"
                               placeholder="Cari data" 
                               class="w-full pl-3 pr-8 py-2 rounded-xl border border-slate-200 text-xs focus:outline-none focus:ring-2 focus:ring-[#2563EB] focus:border-transparent">
                        @if(request('search'))
                            <a href="{{ route('mahasiswa.dashboard', request()->only('type')) }}" class="absolute right-2.5 top-2 text-slate-400 hover:text-slate-600 text-xs">&times;</a>
                        @endif
                    </div>
                </form>
            </div>

            <!-- Tabel Fasilitas Persis Figma -->
            <div class="overflow-x-auto">
                <table class="w-full text-left border-collapse">
                    <thead>
                        <tr class="border-b border-slate-100 text-[11px] font-semibold text-slate-400 uppercase tracking-wider">
                            <th class="py-3 px-3">Fasilitas</th>
                            <th class="py-3 px-3">Jenis</th>
                            <th class="py-3 px-3">Ketersediaan</th>
                            <th class="py-3 px-3 text-center">Status</th>
                            <th class="py-3 px-3 text-right">Aksi</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-50 text-xs text-slate-700">
                        @forelse($facilities as $item)
                            <tr class="hover:bg-slate-50/80 transition">
                                <td class="py-3.5 px-3 font-semibold text-slate-900">
                                    {{ $item->name }}
                                </td>
                                <td class="py-3.5 px-3 text-slate-500 capitalize">
                                    {{ $item->type === 'ruangan' ? 'Lab' : 'Barang' }}
                                </td>
                                <td class="py-3.5 px-3">
                                    @if($item->type === 'barang')
                                        <span class="font-medium text-slate-800">{{ $item->available_stock }}</span> / <span class="text-slate-400">{{ $item->total_stock }}</span>
                                    @else
                                        Kapasitas <span class="font-medium text-slate-800">{{ $item->capacity }} orang</span>
                                    @endif
                                </td>
                                <td class="py-3.5 px-3 text-center">
                                    @if($item->type === 'barang')
                                        @if($item->available_stock > 1)
                                            <span class="inline-block px-2.5 py-1 rounded-full text-[10px] font-semibold bg-emerald-50 text-emerald-700 border border-emerald-200">
                                                Tersedia
                                            </span>
                                        @elseif($item->available_stock === 1)
                                            <span class="inline-block px-2.5 py-1 rounded-full text-[10px] font-semibold bg-amber-50 text-amber-700 border border-amber-200">
                                                Terbatas
                                            </span>
                                        @else
                                            <span class="inline-block px-2.5 py-1 rounded-full text-[10px] font-semibold bg-rose-50 text-rose-700 border border-rose-200">
                                                Habis
                                            </span>
                                        @endif
                                    @else
                                        <span class="inline-block px-2.5 py-1 rounded-full text-[10px] font-semibold bg-blue-50 text-blue-700 border border-blue-200">
                                            Perlu izin
                                        </span>
                                    @endif
                                </td>
                                <td class="py-3.5 px-3 text-right">
                                    @if($item->type === 'barang')
                                        @if($item->available_stock > 0)
                                            <a href="{{ route('mahasiswa.form-peminjaman', ['jenis' => 'barang', 'id' => $item->id]) }}" 
                                               class="inline-block px-3.5 py-1.5 rounded-xl border border-[#2563EB] text-[#2563EB] hover:bg-[#2563EB] hover:text-white transition font-medium text-xs">
                                                Ajukan
                                            </a>
                                        @else
                                            <span class="text-slate-400 text-xs italic">Tidak tersedia</span>
                                        @endif
                                    @else
                                        <a href="{{ route('mahasiswa.form-peminjaman', ['jenis' => 'lab', 'id' => $item->id]) }}" 
                                           class="inline-block px-3.5 py-1.5 rounded-xl border border-[#2563EB] text-[#2563EB] hover:bg-[#2563EB] hover:text-white transition font-medium text-xs">
                                            Ajukan izin
                                        </a>
                                    @endif
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="5" class="py-8 text-center text-slate-400">
                                    Belum ada fasilitas terdaftar dalam kategori ini.
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>

        <!-- Kolom Kanan: Status Pengajuan & QR Code (4 Cols) -->
        <div class="lg:col-span-4 space-y-6">
            <!-- Widget 1: Status Pengajuan Persis Figma -->
            <div class="bg-white rounded-3xl p-6 shadow-sm border border-slate-100">
                <div class="flex items-center justify-between mb-4">
                    <h3 class="text-base font-bold text-slate-900">Status pengajuan</h3>
                    <a href="{{ route('mahasiswa.peminjaman-saya') }}" class="text-xs text-[#2563EB] hover:underline font-semibold">Lihat Semua</a>
                </div>

                <div class="space-y-3">
                    @forelse($recentRequests as $req)
                        <div class="p-3.5 rounded-2xl bg-slate-50 border border-slate-100 flex items-center justify-between">
                            <div>
                                <p class="text-xs font-bold text-slate-900 leading-tight">
                                    {{ $req->facility ? $req->facility->name : 'Fasilitas' }}
                                </p>
                                <p class="text-[10px] text-slate-400 mt-0.5">
                                    Diajukan {{ \Carbon\Carbon::parse($req->borrow_date)->isoFormat('D MMM Y') }}
                                </p>
                            </div>
                            <div>
                                @if($req->status === 'menunggu')
                                    <span class="px-2.5 py-1 rounded-full text-[10px] font-semibold bg-amber-50 text-amber-700 border border-amber-200">
                                        Menunggu Dosen
                                    </span>
                                @elseif($req->status === 'disetujui')
                                    <span class="px-2.5 py-1 rounded-full text-[10px] font-semibold bg-emerald-50 text-emerald-700 border border-emerald-200">
                                        Disetujui
                                    </span>
                                @elseif($req->status === 'dipinjam')
                                    <span class="px-2.5 py-1 rounded-full text-[10px] font-semibold bg-blue-50 text-blue-700 border border-blue-200">
                                        Dipinjam
                                    </span>
                                @elseif($req->status === 'ditolak')
                                    <span class="px-2.5 py-1 rounded-full text-[10px] font-semibold bg-rose-50 text-rose-700 border border-rose-200">
                                        Ditolak
                                    </span>
                                @else
                                    <span class="px-2.5 py-1 rounded-full text-[10px] font-semibold bg-teal-50 text-teal-700 border border-teal-200">
                                        Selesai
                                    </span>
                                @endif
                            </div>
                        </div>
                    @empty
                        <p class="text-xs text-slate-400 py-3 text-center">Belum ada pengajuan aktif.</p>
                    @endforelse
                </div>
            </div>

            <!-- Widget 2: QR Code Peminjaman Persis Figma -->
            <div class="bg-white rounded-3xl p-6 shadow-sm border border-slate-100 text-center">
                <h3 class="text-base font-bold text-slate-900 mb-4">QR Code peminjaman</h3>

                @if($activeRequest && $activeQr)
                    <!-- Tampilan Gambar QR Code SVG Realistis -->
                    <div class="flex justify-center mb-4">
                        <div class="p-3 bg-white border-2 border-slate-800 rounded-2xl shadow-inner inline-block">
                            <!-- Visual QR Code SVG -->
                            <svg class="w-36 h-36" viewBox="0 0 100 100" fill="currentColor">
                                <!-- Outer corners -->
                                <rect x="5" y="5" width="25" height="25" rx="3" fill="#0C1A40"/>
                                <rect x="10" y="10" width="15" height="15" fill="#fff"/>
                                <rect x="13" y="13" width="9" height="9" fill="#0C1A40"/>

                                <rect x="70" y="5" width="25" height="25" rx="3" fill="#0C1A40"/>
                                <rect x="75" y="10" width="15" height="15" fill="#fff"/>
                                <rect x="78" y="13" width="9" height="9" fill="#0C1A40"/>

                                <rect x="5" y="70" width="25" height="25" rx="3" fill="#0C1A40"/>
                                <rect x="10" y="75" width="15" height="15" fill="#fff"/>
                                <rect x="13" y="78" width="9" height="9" fill="#0C1A40"/>

                                <!-- Random Matrix Pattern -->
                                <rect x="35" y="10" width="8" height="8" fill="#0C1A40"/>
                                <rect x="48" y="15" width="6" height="6" fill="#0C1A40"/>
                                <rect x="35" y="25" width="12" height="6" fill="#0C1A40"/>
                                <rect x="10" y="40" width="8" height="8" fill="#0C1A40"/>
                                <rect x="25" y="45" width="6" height="10" fill="#0C1A40"/>
                                <rect x="40" y="40" width="20" height="20" rx="2" fill="#2563EB"/>
                                <rect x="70" y="40" width="10" height="8" fill="#0C1A40"/>
                                <rect x="85" y="45" width="10" height="6" fill="#0C1A40"/>
                                <rect x="40" y="70" width="8" height="10" fill="#0C1A40"/>
                                <rect x="55" y="75" width="12" height="6" fill="#0C1A40"/>
                                <rect x="75" y="70" width="15" height="8" fill="#0C1A40"/>
                                <rect x="70" y="85" width="8" height="10" fill="#0C1A40"/>
                                <rect x="85" y="85" width="10" height="10" fill="#0C1A40"/>
                            </svg>
                        </div>
                    </div>

                    <!-- Countdown Badge Persis Figma -->
                    <div class="mb-3">
                        <span id="qr-countdown" class="inline-block px-3 py-1 rounded-full text-xs font-semibold bg-amber-100 text-amber-800">
                            Berlaku 15:00 menit lagi
                        </span>
                    </div>

                    <p class="text-xs text-slate-500 leading-relaxed max-w-xs mx-auto mb-5">
                        Tunjukkan kode ini kepada Dosen saat {{ $activeQr->type === 'penyerahan' ? 'penyerahan' : 'pengembalian' }}.
                    </p>

                    @if($activeRequest->status === 'dipinjam')
                        <form method="POST" action="{{ route('mahasiswa.ajukan-pengembalian', $activeRequest->id) }}">
                            @csrf
                            <button type="submit" class="w-full py-2.5 rounded-xl border border-slate-300 hover:bg-slate-50 text-slate-700 text-xs font-semibold transition">
                                Ajukan pengembalian
                            </button>
                        </form>
                    @endif
                @else
                    <div class="py-8 text-slate-400">
                        <div class="w-16 h-16 mx-auto mb-2 rounded-2xl bg-slate-100 flex items-center justify-center text-slate-300">
                            <svg class="w-8 h-8" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v1m6 11h2m-6 0h-2v4m0-11v3m0 0h.01M12 12h4.01M16 20h4M4 12h4m12 0h.01M5 8h2a1 1 0 001-1V5a1 1 0 00-1-1H5a1 1 0 00-1 1v2a1 1 0 001 1zm12 0h2a1 1 0 001-1V5a1 1 0 00-1-1h-2a1 1 0 00-1 1v2a1 1 0 001 1zM5 20h2a1 1 0 001-1v-2a1 1 0 00-1-1H5a1 1 0 00-1 1v2a1 1 0 001 1z"></path>
                            </svg>
                        </div>
                        <p class="text-xs">Tidak ada QR Code aktif saat ini.</p>
                        <p class="text-[11px] text-slate-400 mt-1">QR Code akan terbit setelah pengajuan disetujui Dosen.</p>
                    </div>
                @endif
            </div>
        </div>
    </div>
</div>
@endsection
