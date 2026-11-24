@extends('layouts.app')

@section('content')
<!-- [MARKER M1] : HALAMAN BERANDA / LANDING PAGE PERSIS FIGMA (PAGE 1) -->
<div class="relative min-h-screen bg-[#0C1A40] text-white flex flex-col justify-between overflow-hidden">
    <!-- Background FMIPA Building with Blue Tint Overlay -->
    <div class="absolute inset-0 z-0">
        <div class="absolute inset-0 bg-gradient-to-r from-[#0C1A40]/95 via-[#0C1A40]/80 to-[#1e3a8a]/70 z-10"></div>
        <img src="https://images.unsplash.com/photo-1541339907198-e08756dedf3f?auto=format&fit=crop&w=2000&q=80" 
             alt="Gedung Kampus FMIPA UHO" 
             class="w-full h-full object-cover opacity-60">
    </div>

    <!-- Header / Navbar -->
    <header class="relative z-20 w-full px-8 lg:px-14 py-6 flex items-center justify-between">
        <!-- 3 Logos Brand Pill Persis Figma -->
        <a href="{{ route('home') }}" class="flex items-center space-x-2 bg-white/95 backdrop-blur-sm px-4 py-2 rounded-2xl shadow-sm hover:opacity-95 transition">
            <div class="w-8 h-8 rounded-full bg-blue-700 flex items-center justify-center text-white text-xs font-bold shadow-inner">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 14l9-5-9-5-9 5 9 5z"></path>
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 14l6.16-3.422a12.083 12.083 0 01.665 6.479A11.952 11.952 0 0012 20.055a11.952 11.952 0 00-6.824-2.998 12.078 12.078 0 01.665-6.479L12 14z"></path>
                </svg>
            </div>
            <div class="w-8 h-8 rounded-full bg-amber-500 flex items-center justify-center text-white text-xs font-bold shadow-inner">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"></path>
                </svg>
            </div>
            <div class="w-8 h-8 rounded-full bg-[#0C1A40] flex items-center justify-center text-white text-xs font-bold shadow-inner">
                <span class="text-[10px] tracking-tight">UHO</span>
            </div>
        </a>

        <!-- Right Navigation Links -->
        <div class="flex items-center space-x-6 text-sm font-medium">
            <a href="{{ route('home') }}" class="text-white hover:text-blue-200 transition">Beranda</a>
            <a href="{{ route('fasilitas.index') }}" class="text-white/90 hover:text-white transition">Fasilitas</a>

            @auth
                @if(auth()->user()->role === 'admin')
                    <a href="{{ Route::has('admin.dashboard') ? route('admin.dashboard') : route('home') }}" class="bg-[#2563EB] hover:bg-blue-700 text-white px-5 py-2.5 rounded-2xl transition font-medium shadow-md">Dashboard Admin</a>
                @elseif(auth()->user()->role === 'dosen')
                    <a href="{{ Route::has('dosen.dashboard') ? route('dosen.dashboard') : route('home') }}" class="bg-[#2563EB] hover:bg-blue-700 text-white px-5 py-2.5 rounded-2xl transition font-medium shadow-md">Dashboard Dosen</a>
                @else
                    <a href="{{ Route::has('mahasiswa.dashboard') ? route('mahasiswa.dashboard') : route('home') }}" class="bg-[#2563EB] hover:bg-blue-700 text-white px-5 py-2.5 rounded-2xl transition font-medium shadow-md">Dashboard Mahasiswa</a>
                @endif
            @else
                <a href="{{ route('login') }}" class="border border-white/60 hover:bg-white/10 text-white px-6 py-2.5 rounded-2xl transition font-medium">Masuk</a>
                <a href="{{ route('register') }}" class="bg-white hover:bg-slate-100 text-[#0C1A40] px-6 py-2.5 rounded-2xl transition font-semibold shadow-sm">Daftar</a>
            @endauth
        </div>
    </header>

    <!-- Hero Section Content -->
    <div class="relative z-20 px-8 lg:px-14 py-8 lg:py-16 max-w-7xl mx-auto w-full flex-1 flex flex-col justify-center">
        <!-- Subtitle Kicker -->
        <p class="text-xs font-bold tracking-widest uppercase text-blue-300 mb-3">LAYANAN AKADEMIK PRODI</p>

        <!-- Big Headline -->
        <h1 class="text-4xl lg:text-5xl font-extrabold text-white tracking-tight leading-tight max-w-2xl mb-4">
            Pinjam Fasilitas Prodi dengan Mudah
        </h1>

        <!-- Supporting Text -->
        <p class="text-base text-slate-200/90 leading-relaxed max-w-xl mb-8">
            Ajukan peminjaman laboratorium komputer dan fasilitas barang seperti proyektor dan kabel secara online, tanpa antre dan tanpa berkas manual.
        </p>

        <!-- CTAs -->
        <div class="flex items-center space-x-4 mb-16">
            <a href="{{ auth()->check() ? (Route::has('mahasiswa.form-peminjaman') ? route('mahasiswa.form-peminjaman', 'barang') : route('home')) : route('login') }}" 
               class="bg-white hover:bg-slate-100 text-[#0C1A40] font-bold px-7 py-3 rounded-2xl transition shadow-lg">
                Ajukan Peminjaman
            </a>
            <a href="{{ route('fasilitas.index') }}" 
               class="bg-white/15 hover:bg-white/25 border border-white/30 text-white font-medium px-7 py-3 rounded-2xl backdrop-blur-sm transition">
                Lihat Fasilitas
            </a>
        </div>

        <!-- 2 Glassmorphism Feature Cards Persis Figma -->
        <div class="grid grid-cols-1 md:grid-cols-2 gap-6 max-w-3xl">
            <!-- Card 1: Laboratorium Komputer -->
            <div class="bg-white/10 backdrop-blur-md border border-white/15 rounded-3xl p-6 hover:bg-white/15 transition">
                <div class="w-10 h-10 rounded-xl bg-white/15 flex items-center justify-center text-white mb-4">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9.75 17L9 20l-1 1h8l-1-1-.75-3M3 13h18M5 17h14a2 2 0 002-2V5a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"></path>
                    </svg>
                </div>
                <h3 class="text-lg font-bold text-white mb-1.5">Laboratorium Komputer</h3>
                <p class="text-xs text-slate-300 leading-relaxed">
                    Untuk praktikum dan kegiatan akademik di luar jadwal reguler.
                </p>
            </div>

            <!-- Card 2: Fasilitas Barang -->
            <div class="bg-white/10 backdrop-blur-md border border-white/15 rounded-3xl p-6 hover:bg-white/15 transition">
                <div class="w-10 h-10 rounded-xl bg-white/15 flex items-center justify-center text-white mb-4">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4"></path>
                    </svg>
                </div>
                <h3 class="text-lg font-bold text-white mb-1">Fasilitas Barang</h3>
                <p class="text-xs text-slate-300 mb-3">
                    Perlengkapan penunjang perkuliahan yang bisa dipinjam:
                </p>
                <div class="flex flex-wrap gap-2 text-xs">
                    <span class="bg-white/15 border border-white/20 px-3 py-1 rounded-full text-slate-200">Proyektor</span>
                    <span class="bg-white/15 border border-white/20 px-3 py-1 rounded-full text-slate-200">Kabel Terminal</span>
                    <span class="bg-white/15 border border-white/20 px-3 py-1 rounded-full text-slate-200">Kabel HDMI</span>
                    <span class="bg-white/15 border border-white/20 px-3 py-1 rounded-full text-slate-200">Dan lainnya</span>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
