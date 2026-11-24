@extends('layouts.app')

@section('title', 'Daftar Fasilitas')
@section('page_title', 'Daftar Fasilitas')

@section('content')
<div class="mb-6 flex flex-col sm:flex-row sm:items-center justify-between gap-4">
    <div>
        <h2 class="text-2xl font-extrabold text-slate-900 tracking-tight">Daftar Fasilitas Kampus</h2>
        <p class="text-sm text-slate-500">Pilih fasilitas yang ingin Anda pinjam untuk keperluan perkuliahan atau praktikum.</p>
    </div>
</div>

<!-- Filter & Search Bar -->
<div class="bg-white rounded-2xl p-4 sm:p-5 mb-8 shadow-xs border border-slate-200 flex flex-col sm:flex-row items-center justify-between gap-4">
    <div class="flex items-center gap-2 w-full sm:w-auto">
        <a href="{{ route('mahasiswa.fasilitas', ['kategori' => 'semua', 'search' => request('search')]) }}"
           class="px-4 py-2 rounded-xl text-xs font-bold transition-all {{ $kategori === 'semua' ? 'bg-blue-600 text-white shadow-md' : 'bg-slate-100 text-slate-600 hover:bg-slate-200' }}">
            Semua
        </a>
        <a href="{{ route('mahasiswa.fasilitas', ['kategori' => 'barang', 'search' => request('search')]) }}"
           class="px-4 py-2 rounded-xl text-xs font-bold transition-all {{ $kategori === 'barang' ? 'bg-blue-600 text-white shadow-md' : 'bg-slate-100 text-slate-600 hover:bg-slate-200' }}">
            Barang
        </a>
        <a href="{{ route('mahasiswa.fasilitas', ['kategori' => 'lab', 'search' => request('search')]) }}"
           class="px-4 py-2 rounded-xl text-xs font-bold transition-all {{ $kategori === 'lab' ? 'bg-blue-600 text-white shadow-md' : 'bg-slate-100 text-slate-600 hover:bg-slate-200' }}">
            Lab Komputer
        </a>
    </div>

    <form method="GET" action="{{ route('mahasiswa.fasilitas') }}" class="w-full sm:w-72">
        <input type="hidden" name="kategori" value="{{ $kategori }}">
        <div class="relative">
            <input type="text" name="search" value="{{ $search }}" placeholder="Cari fasilitas..."
                   class="w-full pl-10 pr-4 py-2 rounded-xl border border-slate-200 text-xs focus:ring-2 focus:ring-blue-600 focus:outline-none">
            <svg class="w-4 h-4 text-slate-400 absolute left-3.5 top-2.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"></path></svg>
        </div>
    </form>
</div>

<!-- Grid Fasilitas Barang -->
@if($kategori !== 'lab' && $barangList->isNotEmpty())
    <div class="mb-8">
        <h3 class="text-base font-bold text-slate-900 mb-4 flex items-center gap-2">
            <svg class="w-5 h-5 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4"></path></svg>
            Fasilitas Barang
        </h3>
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
            @foreach($barangList as $b)
                <div class="bg-white rounded-2xl p-5 shadow-xs border border-slate-200 flex flex-col justify-between hover:border-blue-300 transition-all">
                    <div>
                        <div class="flex items-center justify-between mb-3">
                            <span class="px-2.5 py-1 rounded-lg bg-slate-100 font-mono text-xs font-bold text-slate-700">{{ $b->kode_barang }}</span>
                            @if($b->stok_tersedia > 0)
                                <span class="px-2.5 py-0.5 rounded-full bg-emerald-50 text-emerald-700 border border-emerald-200 text-[11px] font-bold">Tersedia</span>
                            @else
                                <span class="px-2.5 py-0.5 rounded-full bg-rose-50 text-rose-700 border border-rose-200 text-[11px] font-bold">Habis</span>
                            @endif
                        </div>
                        <h4 class="font-bold text-slate-900 text-base mb-1">{{ $b->nama_barang }}</h4>
                        <p class="text-xs text-slate-500 mb-4">{{ $b->deskripsi ?: 'Perlengkapan perkuliahan.' }}</p>
                    </div>

                    <div class="pt-4 border-t border-slate-100 flex items-center justify-between">
                        <div>
                            <span class="text-[10px] text-slate-400 uppercase font-bold block">Ketersediaan</span>
                            <span class="text-sm font-extrabold text-slate-900">{{ $b->stok_tersedia }} <span class="text-xs font-normal text-slate-500">/ {{ $b->stok_total }} unit</span></span>
                        </div>
                        @if($b->stok_tersedia > 0)
                            <a href="{{ route('mahasiswa.form-peminjaman', ['jenis' => 'BARANG', 'id' => $b->id_barang]) }}"
                               class="px-4 py-2 rounded-xl bg-blue-600 hover:bg-blue-700 text-white font-bold text-xs shadow-md transition-all">
                                Ajukan
                            </a>
                        @else
                            <button disabled class="px-4 py-2 rounded-xl bg-slate-100 text-slate-400 font-bold text-xs cursor-not-allowed">
                                Tidak Tersedia
                            </button>
                        @endif
                    </div>
                </div>
            @endforeach
        </div>
    </div>
@endif

<!-- Grid Fasilitas Lab & Ruangan -->
@if($kategori !== 'barang' && $ruanganList->isNotEmpty())
    <div>
        <h3 class="text-base font-bold text-slate-900 mb-4 flex items-center gap-2">
            <svg class="w-5 h-5 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"></path></svg>
            Laboratorium & Ruangan
        </h3>
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
            @foreach($ruanganList as $r)
                <div class="bg-white rounded-2xl p-5 shadow-xs border border-slate-200 flex flex-col justify-between hover:border-blue-300 transition-all">
                    <div>
                        <div class="flex items-center justify-between mb-3">
                            <span class="px-2.5 py-1 rounded-lg bg-blue-50 font-mono text-xs font-bold text-blue-700">{{ $r->kode_ruangan }}</span>
                            <span class="px-2.5 py-0.5 rounded-full bg-blue-50 text-blue-700 border border-blue-200 text-[11px] font-bold">{{ $r->status }}</span>
                        </div>
                        <h4 class="font-bold text-slate-900 text-base mb-1">{{ $r->nama_ruangan }}</h4>
                        <p class="text-xs text-slate-500 mb-2 flex items-center gap-1.5">
                            <svg class="w-3.5 h-3.5 text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z"></path></svg>
                            {{ $r->lokasi }}
                        </p>
                        <p class="text-xs text-slate-500 mb-4">{{ $r->deskripsi ?: 'Laboratorium praktikum dan kegiatan akademik.' }}</p>
                    </div>

                    <div class="pt-4 border-t border-slate-100 flex items-center justify-between">
                        <div>
                            <span class="text-[10px] text-slate-400 uppercase font-bold block">Kapasitas</span>
                            <span class="text-sm font-extrabold text-slate-900">{{ $r->kapasitas }} <span class="text-xs font-normal text-slate-500">orang</span></span>
                        </div>
                        <a href="{{ route('mahasiswa.form-peminjaman', ['jenis' => 'RUANGAN', 'id' => $r->id_ruangan]) }}"
                           class="px-4 py-2 rounded-xl bg-blue-600 hover:bg-blue-700 text-white font-bold text-xs shadow-md transition-all">
                            Ajukan Izin Lab
                        </a>
                    </div>
                </div>
            @endforeach
        </div>
    </div>
@endif
@endsection
