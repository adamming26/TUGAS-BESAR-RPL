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

<!-- [M2] LANDING PAGE SIPAFKA — SESUAI DESAIN PDF PAGE 1 -->
<section class="relative min-h-screen bg-[#0C1A40] text-white flex flex-col justify-between overflow-hidden">
    <!-- Background Gedung Kampus dengan Gradasi Biru Navy -->
    <div class="absolute inset-0 z-0">
        <div class="absolute inset-0 bg-gradient-to-r from-[#0C1A40]/95 via-[#0C1A40]/80 to-[#1e3a8a]/65 z-10"></div>
        <img src="{{ asset('images/gedung-kampus.jpg') }}"
             alt="Gedung Kampus FMIPA Universitas Halu Oleo"
             class="w-full h-full object-cover object-center opacity-60">
    </div>

    <!-- Header / Navigasi -->
    @include('partials.m2-public-header', ['showAuthButtons' => true])

    <!-- Hero Content Persis PDF Page 1 -->
    <div class="relative z-20 px-6 sm:px-8 lg:px-14 py-10 lg:py-16 max-w-7xl mx-auto w-full flex-1 flex flex-col justify-center">
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
        <div class="flex flex-wrap items-center gap-3 sm:gap-4 mb-12 lg:mb-14">
            <a href="{{ $ajukanUrl }}"
               class="bg-white hover:bg-slate-100 text-[#0C1A40] font-bold text-sm px-7 py-3.5 rounded-2xl transition shadow-lg">
                Ajukan Peminjaman
            </a>
            <a href="{{ route('fasilitas.index') }}"
               class="bg-white/15 hover:bg-white/25 border border-white/35 text-white font-medium text-sm px-7 py-3.5 rounded-2xl backdrop-blur-sm transition">
                Lihat Fasilitas
            </a>
        </div>

        <!-- 2 Glassmorphism Facility Highlight Cards Persis PDF Page 1 -->
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

<!-- Ringkasan Informasi Aplikasi & Fasilitas Kampus -->
<section class="bg-[#F8FAFC] py-16 px-6 sm:px-8 lg:px-14">
    <div class="max-w-7xl mx-auto space-y-14">
        <!-- Ringkasan Fasilitas Tersedia -->
        <div>
            <div class="flex flex-col sm:flex-row sm:items-end justify-between gap-4 mb-8">
                <div>
                    <p class="text-xs font-bold uppercase tracking-widest text-[#2563EB] mb-1.5">Informasi Fasilitas Kampus</p>
                    <h2 class="text-2xl sm:text-3xl font-extrabold text-slate-900 tracking-tight">
                        Fasilitas Prodi Ilmu Komputer
                    </h2>
                    <p class="text-sm text-slate-500 mt-1">
                        Pantau ringkasan laboratorium komputer dan perlengkapan perkuliahan yang tersedia di SIPAFKA.
                    </p>
                </div>
                <a href="{{ route('fasilitas.index') }}"
                   class="inline-flex items-center gap-2 text-sm font-semibold text-[#2563EB] hover:text-blue-700 transition">
                    <span>Lihat semua fasilitas</span>
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"></path>
                    </svg>
                </a>
            </div>

            <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
                <!-- Daftar Laboratorium -->
                <div class="bg-white rounded-3xl p-6 sm:p-8 shadow-sm border border-slate-100">
                    <div class="flex items-center justify-between mb-5">
                        <div class="flex items-center gap-3">
                            <div class="w-10 h-10 rounded-2xl bg-blue-50 text-[#2563EB] flex items-center justify-center">
                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9.75 17L9 20l-1 1h8l-1-1-.75-3M3 13h18M5 17h14a2 2 0 002-2V5a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"></path>
                                </svg>
                            </div>
                            <div>
                                <h3 class="text-base font-bold text-slate-900">Laboratorium Komputer</h3>
                                <p class="text-xs text-slate-500">Ruang praktikum & kegiatan akademik</p>
                            </div>
                        </div>
                        <span class="px-3 py-1 rounded-full text-xs font-semibold bg-blue-50 text-[#2563EB]">
                            {{ $labFacilities->count() }} Ruangan
                        </span>
                    </div>

                    <div class="space-y-3">
                        @forelse($labFacilities->take(4) as $lab)
                            <div class="flex items-center justify-between gap-4 p-3.5 rounded-2xl bg-slate-50 border border-slate-100">
                                <div class="min-w-0">
                                    <p class="text-sm font-semibold text-slate-900 truncate">{{ $lab->name }}</p>
                                    <p class="text-xs text-slate-500 truncate">{{ $lab->description ?: 'Memerlukan persetujuan Dosen sebelum digunakan.' }}</p>
                                </div>
                                <span class="shrink-0 px-2.5 py-1 rounded-full text-[11px] font-semibold bg-blue-100/70 text-[#2563EB]">
                                    Kapasitas {{ $lab->capacity }}
                                </span>
                            </div>
                        @empty
                            <p class="text-xs text-slate-400 py-4 text-center">Belum ada data laboratorium aktif.</p>
                        @endforelse
                    </div>
                </div>

                <!-- Daftar Barang -->
                <div class="bg-white rounded-3xl p-6 sm:p-8 shadow-sm border border-slate-100">
                    <div class="flex items-center justify-between mb-5">
                        <div class="flex items-center gap-3">
                            <div class="w-10 h-10 rounded-2xl bg-indigo-50 text-[#0C1A40] flex items-center justify-center">
                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4"></path>
                                </svg>
                            </div>
                            <div>
                                <h3 class="text-base font-bold text-slate-900">Fasilitas Barang</h3>
                                <p class="text-xs text-slate-500">Perangkat penunjang perkuliahan</p>
                            </div>
                        </div>
                        <span class="px-3 py-1 rounded-full text-xs font-semibold bg-emerald-50 text-emerald-700">
                            {{ $barangFacilities->count() }} Jenis
                        </span>
                    </div>

                    <div class="space-y-3">
                        @forelse($barangFacilities->take(4) as $barang)
                            <div class="flex items-center justify-between gap-4 p-3.5 rounded-2xl bg-slate-50 border border-slate-100">
                                <div class="min-w-0">
                                    <p class="text-sm font-semibold text-slate-900 truncate">{{ $barang->name }}</p>
                                    <p class="text-xs text-slate-500 truncate">{{ $barang->description ?: 'Fasilitas barang operasional prodi.' }}</p>
                                </div>
                                @if($barang->available_stock > 0)
                                    <span class="shrink-0 px-2.5 py-1 rounded-full text-[11px] font-semibold bg-emerald-100/80 text-emerald-700">
                                        {{ $barang->available_stock }} / {{ $barang->total_stock }} Unit
                                    </span>
                                @else
                                    <span class="shrink-0 px-2.5 py-1 rounded-full text-[11px] font-semibold bg-rose-100 text-rose-700">
                                        Habis
                                    </span>
                                @endif
                            </div>
                        @empty
                            <p class="text-xs text-slate-400 py-4 text-center">Belum ada data fasilitas barang aktif.</p>
                        @endforelse
                    </div>
                </div>
            </div>
        </div>

        <!-- Ringkasan Singkat Cara Kerja SIPAFKA -->
        <div class="bg-[#0C1A40] rounded-3xl p-8 sm:p-10 text-white shadow-xl">
            <div class="flex flex-col lg:flex-row lg:items-center justify-between gap-6 mb-8">
                <div>
                    <p class="text-xs font-bold uppercase tracking-widest text-blue-300 mb-1.5">Tentang SIPAFKA</p>
                    <h2 class="text-2xl sm:text-3xl font-extrabold tracking-tight">Alur Peminjaman Transparan & Terintegrasi</h2>
                </div>
                <div class="flex flex-wrap gap-3">
                    <a href="{{ route('panduan.index') }}"
                       class="px-5 py-2.5 rounded-2xl bg-white/15 hover:bg-white/25 border border-white/25 text-sm font-medium transition">
                        Baca Panduan Lengkap
                    </a>
                    @guest
                        <a href="{{ route('register') }}"
                           class="px-5 py-2.5 rounded-2xl bg-[#2563EB] hover:bg-blue-700 text-sm font-semibold transition shadow-md">
                            Daftar Akun Sekarang
                        </a>
                    @endguest
                </div>
            </div>

            <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-5">
                <div class="bg-white/10 border border-white/15 rounded-2xl p-5">
                    <span class="inline-flex items-center justify-center w-8 h-8 rounded-xl bg-[#2563EB] text-white text-xs font-bold mb-3">1</span>
                    <h3 class="text-sm font-bold mb-1">Daftar & Masuk</h3>
                    <p class="text-xs text-slate-300 leading-relaxed">Buat akun mahasiswa menggunakan NIM dan email aktif, lalu masuk sesuai hak akses Anda.</p>
                </div>
                <div class="bg-white/10 border border-white/15 rounded-2xl p-5">
                    <span class="inline-flex items-center justify-center w-8 h-8 rounded-xl bg-[#2563EB] text-white text-xs font-bold mb-3">2</span>
                    <h3 class="text-sm font-bold mb-1">Pilih Fasilitas</h3>
                    <p class="text-xs text-slate-300 leading-relaxed">Cek ketersediaan proyektor, kabel, atau laboratorium komputer secara real-time.</p>
                </div>
                <div class="bg-white/10 border border-white/15 rounded-2xl p-5">
                    <span class="inline-flex items-center justify-center w-8 h-8 rounded-xl bg-[#2563EB] text-white text-xs font-bold mb-3">3</span>
                    <h3 class="text-sm font-bold mb-1">Persetujuan Dosen</h3>
                    <p class="text-xs text-slate-300 leading-relaxed">Pengajuan diperiksa dan disetujui oleh dosen pembimbing atau penanggung jawab.</p>
                </div>
                <div class="bg-white/10 border border-white/15 rounded-2xl p-5">
                    <span class="inline-flex items-center justify-center w-8 h-8 rounded-xl bg-[#2563EB] text-white text-xs font-bold mb-3">4</span>
                    <h3 class="text-sm font-bold mb-1">Serah Terima QR</h3>
                    <p class="text-xs text-slate-300 leading-relaxed">Tunjukkan QR Code saat pengambilan dan pengembalian fasilitas tanpa berkas manual.</p>
                </div>
            </div>
        </div>
    </div>
</section>

<!-- Footer -->
<footer class="bg-white border-t border-slate-200 px-6 sm:px-8 lg:px-14 py-6">
    <div class="max-w-7xl mx-auto flex flex-col sm:flex-row items-center justify-between gap-3 text-xs text-slate-500">
        <p>&copy; {{ date('Y') }} SIPAFKA — Sistem Informasi Peminjaman Fasilitas Kampus, Prodi Ilmu Komputer FMIPA UHO.</p>
        <div class="flex items-center space-x-5">
            <a href="{{ route('home') }}" class="hover:text-[#2563EB] transition">Beranda</a>
            <a href="{{ route('fasilitas.index') }}" class="hover:text-[#2563EB] transition">Fasilitas</a>
            <a href="{{ route('panduan.index') }}" class="hover:text-[#2563EB] transition">Panduan</a>
        </div>
    </div>
</footer>
@endsection
