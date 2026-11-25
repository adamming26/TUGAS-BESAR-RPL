@extends('layouts.app', ['title' => 'Beranda'])

@section('content')
@php
    $ajukanUrl = route('login');
    if (auth()->check()) {
        $role = auth()->user()->role;
        if ($role === 'mahasiswa' && \Illuminate\Support\Facades\Route::has('mahasiswa.form-peminjaman')) {
            $ajukanUrl = route('mahasiswa.form-peminjaman', 'barang');
        } elseif ($role === 'admin' && \Illuminate\Support\Facades\Route::has('admin.dashboard')) {
            $ajukanUrl = route('admin.dashboard');
        } elseif ($role === 'dosen' && \Illuminate\Support\Facades\Route::has('dosen.dashboard')) {
            $ajukanUrl = route('dosen.dashboard');
        } else {
            $ajukanUrl = route('profile.index');
        }
    }

    $barangTags = isset($barangFacilities) && $barangFacilities->isNotEmpty()
        ? $barangFacilities->pluck('name')->take(3)->all()
        : ['Proyektor', 'Kabel Terminal', 'Kabel HDMI'];
@endphp

<!-- [M2] LANDING PAGE SIPAFKA — PERSIS FIGMA (PAGE 1) -->
<section class="relative min-h-screen bg-[#0C1A40] text-white flex flex-col justify-between overflow-hidden">
    <!-- Background Gedung Kampus dengan Gradasi Biru Navy -->
    <div class="absolute inset-0 z-0">
        <div class="absolute inset-0 bg-gradient-to-r from-[#0C1A40]/95 via-[#0C1A40]/80 to-[#1e3a8a]/65 z-10"></div>
        <img src="{{ asset('images/gedung-kampus.jpg') }}"
             alt="Gedung Kampus FMIPA Universitas Halu Oleo"
             class="w-full h-full object-cover object-center opacity-60">
    </div>

    <!-- Header / Navigasi Persis Figma -->
    @include('partials.m2-public-header', ['showAuthButtons' => true])

    <!-- Hero Content Persis Figma Page 1 -->
    <div class="relative z-20 px-6 sm:px-8 lg:px-14 py-8 lg:py-16 max-w-7xl mx-auto w-full flex-1 flex flex-col justify-center">
        <p class="text-xs font-bold tracking-[0.2em] uppercase text-blue-300 mb-3">
            LAYANAN AKADEMIK PRODI
        </p>

        <h1 class="text-4xl sm:text-5xl lg:text-[54px] font-extrabold text-white tracking-tight leading-[1.12] max-w-2xl mb-5">
            Pinjam Fasilitas Prodi dengan Mudah
        </h1>

        <p class="text-sm sm:text-base text-slate-200/90 leading-relaxed max-w-xl mb-8">
            Ajukan peminjaman laboratorium komputer dan fasilitas barang seperti proyektor dan kabel secara online, tanpa antre dan tanpa berkas manual.
        </p>

        <!-- Primary & Secondary CTA -->
        <div class="flex flex-wrap items-center gap-3 sm:gap-4 mb-12 lg:mb-16">
            <a href="{{ $ajukanUrl }}"
               class="bg-white hover:bg-slate-100 text-[#0C1A40] font-bold text-sm px-7 py-3.5 rounded-2xl transition shadow-lg">
                Ajukan Peminjaman
            </a>
            <a href="{{ route('fasilitas.index') }}"
               class="bg-white/15 hover:bg-white/25 border border-white/35 text-white font-medium text-sm px-7 py-3.5 rounded-2xl backdrop-blur-sm transition">
                Lihat Fasilitas
            </a>
        </div>

        <!-- 2 Glassmorphism Facility Highlight Cards Persis Figma Page 1 -->
        <div class="grid grid-cols-1 md:grid-cols-2 gap-5 sm:gap-6 max-w-3xl">
            <!-- Card 1: Laboratorium Komputer -->
            <div class="bg-[#0C1A40]/55 backdrop-blur-md border border-white/20 rounded-3xl p-6 hover:bg-[#0C1A40]/70 transition">
                <div class="flex items-center justify-between mb-4">
                    <div class="w-10 h-10 rounded-xl bg-white/15 flex items-center justify-center text-white">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9.75 17L9 20l-1 1h8l-1-1-.75-3M3 13h18M5 17h14a2 2 0 002-2V5a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"></path>
                        </svg>
                    </div>
                    @if(isset($stats['total_lab']) && $stats['total_lab'] > 0)
                        <span class="text-[11px] font-medium text-blue-200 bg-white/10 border border-white/15 px-3 py-1 rounded-full">
                            {{ $stats['total_lab'] }} Lab Aktif
                        </span>
                    @endif
                </div>
                <h2 class="text-lg font-bold text-white mb-1.5">Laboratorium Komputer</h2>
                <p class="text-xs sm:text-sm text-slate-200/85 leading-relaxed">
                    Untuk praktikum dan kegiatan akademik di luar jadwal reguler.
                </p>
            </div>

            <!-- Card 2: Fasilitas Barang -->
            <div class="bg-[#0C1A40]/55 backdrop-blur-md border border-white/20 rounded-3xl p-6 hover:bg-[#0C1A40]/70 transition">
                <div class="flex items-center justify-between mb-4">
                    <div class="w-10 h-10 rounded-xl bg-white/15 flex items-center justify-center text-white">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4"></path>
                        </svg>
                    </div>
                    @if(isset($stats['total_unit_tersedia']) && $stats['total_unit_tersedia'] > 0)
                        <span class="text-[11px] font-medium text-blue-200 bg-white/10 border border-white/15 px-3 py-1 rounded-full">
                            {{ $stats['total_unit_tersedia'] }} Unit Tersedia
                        </span>
                    @endif
                </div>
                <h2 class="text-lg font-bold text-white mb-1">Fasilitas Barang</h2>
                <p class="text-xs sm:text-sm text-slate-200/85 mb-3">
                    Perlengkapan penunjang perkuliahan yang bisa dipinjam:
                </p>
                <div class="flex flex-wrap gap-2 text-xs">
                    @foreach($barangTags as $tag)
                        <span class="bg-white/15 border border-white/25 px-3 py-1 rounded-full text-slate-100">{{ $tag }}</span>
                    @endforeach
                    <span class="bg-white/15 border border-white/25 px-3 py-1 rounded-full text-slate-100">Dan lainnya</span>
                </div>
            </div>
        </div>
    </div>
</section>
@endsection
