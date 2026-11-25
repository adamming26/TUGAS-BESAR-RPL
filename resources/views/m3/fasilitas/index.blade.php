@extends('layouts.app', ['title' => $pageTitle])

@section('content')
<!-- [M3] KATALOG FASILITAS KAMPUS — SESUAI WARNA & DESAIN BERSAMA SIPAFKA -->
<div class="min-h-screen bg-[#F8FAFC] flex flex-col">
    <!-- Hero Header -->
    <div class="relative bg-[#0C1A40] text-white overflow-hidden">
        <div class="absolute inset-0 z-0">
            <div class="absolute inset-0 bg-gradient-to-r from-[#0C1A40]/95 via-[#0C1A40]/85 to-[#1e3a8a]/75 z-10"></div>
            <img src="{{ asset('images/gedung-kampus.jpg') }}"
                 alt="Gedung Kampus FMIPA Universitas Halu Oleo"
                 class="w-full h-full object-cover opacity-35">
        </div>

        @include('partials.m2-public-header', ['showAuthButtons' => true])

        <div class="relative z-20 max-w-7xl mx-auto px-6 sm:px-8 lg:px-14 pt-6 pb-14">
            <span class="inline-block px-3 py-1 rounded-full bg-blue-500/20 border border-blue-400/30 text-blue-200 text-xs font-semibold uppercase tracking-wider mb-3">
                Katalog Inventaris Prodi
            </span>
            <h1 class="text-3xl sm:text-4xl font-extrabold tracking-tight text-white mb-2">
                {{ $pageTitle }}
            </h1>
            <p class="text-sm sm:text-base text-slate-200/90 max-w-2xl leading-relaxed">
                Telusuri daftar fasilitas barang dan laboratorium komputer Program Studi Ilmu Komputer FMIPA UHO, cek stok atau kapasitas, serta periksa jadwal ketersediaan sebelum mengajukan peminjaman.
            </p>
        </div>
    </div>

    <!-- Main Content -->
    <div class="flex-1 max-w-7xl mx-auto w-full px-6 sm:px-8 lg:px-14 py-8 space-y-6">
        <!-- Breadcrumb -->
        @php
            $breadcrumbItems = [
                ['label' => 'Katalog Fasilitas', 'url' => route('fasilitas.index')],
            ];
            if ($activeScope === 'barang') {
                $breadcrumbItems[] = ['label' => 'Katalog Barang'];
            } elseif ($activeScope === 'laboratorium') {
                $breadcrumbItems[] = ['label' => 'Katalog Laboratorium'];
            }
        @endphp
        <x-breadcrumb :items="$breadcrumbItems" />

        <!-- Ringkasan Statistik Katalog -->
        <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
            <div class="bg-white rounded-2xl p-5 shadow-sm border border-slate-100 flex items-center justify-between">
                <div>
                    <p class="text-xs font-semibold text-slate-500 uppercase tracking-wider">Total Fasilitas Aktif</p>
                    <p class="text-2xl font-extrabold text-[#0C1A40] mt-1">{{ $summary['total'] }}</p>
                    <p class="text-xs text-slate-500 mt-0.5">Barang &amp; Laboratorium siap pakai</p>
                </div>
                <div class="w-11 h-11 rounded-2xl bg-slate-100 text-[#0C1A40] flex items-center justify-center shrink-0">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2H6a2 2 0 01-2-2V6zM14 6a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2h-2a2 2 0 01-2-2V6zM4 16a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2H6a2 2 0 01-2-2v-2zM14 16a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2h-2a2 2 0 01-2-2v-2z"></path>
                    </svg>
                </div>
            </div>

            <div class="bg-white rounded-2xl p-5 shadow-sm border border-slate-100 flex items-center justify-between">
                <div>
                    <p class="text-xs font-semibold text-slate-500 uppercase tracking-wider">Fasilitas Barang</p>
                    <p class="text-2xl font-extrabold text-[#2563EB] mt-1">{{ $summary['barang_count'] }} <span class="text-xs font-semibold text-slate-500">jenis</span></p>
                    <p class="text-xs text-[#059669] font-medium mt-0.5">{{ $summary['barang_available_units'] }} unit tersedia saat ini</p>
                </div>
                <div class="w-11 h-11 rounded-2xl bg-blue-50 text-[#2563EB] flex items-center justify-center shrink-0">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4"></path>
                    </svg>
                </div>
            </div>

            <div class="bg-white rounded-2xl p-5 shadow-sm border border-slate-100 flex items-center justify-between">
                <div>
                    <p class="text-xs font-semibold text-slate-500 uppercase tracking-wider">Laboratorium Komputer</p>
                    <p class="text-2xl font-extrabold text-[#059669] mt-1">{{ $summary['lab_count'] }} <span class="text-xs font-semibold text-slate-500">ruangan</span></p>
                    <p class="text-xs text-slate-500 mt-0.5">Total kapasitas {{ $summary['lab_total_capacity'] }} orang</p>
                </div>
                <div class="w-11 h-11 rounded-2xl bg-emerald-50 text-[#059669] flex items-center justify-center shrink-0">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9.75 17L9 20l-1 1h8l-1-1-.75-3M3 13h18M5 17h14a2 2 0 002-2V5a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"></path>
                    </svg>
                </div>
            </div>
        </div>

        <!-- Bar Pencarian dan Filter Fasilitas -->
        <div class="bg-white rounded-3xl p-5 shadow-sm border border-slate-100 space-y-4">
            <div class="flex flex-col lg:flex-row lg:items-center justify-between gap-4">
                <!-- Tabs Jenis Fasilitas -->
                <div class="flex flex-wrap items-center gap-1 bg-slate-100 p-1.5 rounded-2xl w-full lg:w-auto">
                    <a href="{{ route('fasilitas.index', array_filter(['search' => $search, 'condition' => $condition, 'status' => $statusFilter])) }}"
                       class="px-4 py-2 rounded-xl text-xs font-semibold transition {{ !$normalizedType && $activeScope === 'semua' ? 'bg-[#2563EB] text-white shadow-sm' : 'text-slate-600 hover:text-slate-900' }}">
                        Semua ({{ $summary['total'] }})
                    </a>
                    <a href="{{ route('fasilitas.barang', array_filter(['search' => $search, 'condition' => $condition, 'status' => $statusFilter])) }}"
                       class="px-4 py-2 rounded-xl text-xs font-semibold transition {{ $normalizedType === 'barang' ? 'bg-[#2563EB] text-white shadow-sm' : 'text-slate-600 hover:text-slate-900' }}">
                        Barang ({{ $summary['barang_count'] }})
                    </a>
                    <a href="{{ route('fasilitas.laboratorium', array_filter(['search' => $search, 'condition' => $condition, 'status' => $statusFilter])) }}"
                       class="px-4 py-2 rounded-xl text-xs font-semibold transition {{ $normalizedType === 'ruangan' ? 'bg-[#2563EB] text-white shadow-sm' : 'text-slate-600 hover:text-slate-900' }}">
                        Laboratorium ({{ $summary['lab_count'] }})
                    </a>
                </div>

                <!-- Form Pencarian & Filter Lanjutan -->
                @php
                    $filterAction = match ($activeScope) {
                        'barang' => route('fasilitas.barang'),
                        'laboratorium' => route('fasilitas.laboratorium'),
                        default => route('fasilitas.index'),
                    };
                @endphp
                <form method="GET" action="{{ $filterAction }}" class="grid grid-cols-1 sm:grid-cols-2 lg:flex items-center gap-3 w-full lg:w-auto">
                    @if($activeScope === 'semua' && $normalizedType)
                        <input type="hidden" name="type" value="{{ $normalizedType }}">
                    @endif

                    <!-- Pencarian Nama / Kode -->
                    <div class="relative sm:col-span-2 lg:w-64">
                        <input type="text"
                               name="search"
                               value="{{ $search }}"
                               placeholder="Cari nama atau kode fasilitas..."
                               class="w-full pl-9 pr-4 py-2.5 rounded-xl border border-slate-200 text-xs text-slate-700 focus:outline-none focus:ring-2 focus:ring-[#2563EB] focus:border-[#2563EB]">
                        <svg class="w-4 h-4 text-slate-400 absolute left-3 top-2.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"></path>
                        </svg>
                    </div>

                    <!-- Filter Kondisi -->
                    <select name="condition"
                            aria-label="Filter kondisi"
                            class="px-3.5 py-2.5 rounded-xl border border-slate-200 text-xs text-slate-700 bg-white focus:outline-none focus:ring-2 focus:ring-[#2563EB]">
                        <option value="">Semua Kondisi</option>
                        <option value="Baik" {{ $condition === 'Baik' ? 'selected' : '' }}>Baik</option>
                        <option value="Rusak Ringan" {{ $condition === 'Rusak Ringan' ? 'selected' : '' }}>Rusak Ringan</option>
                        <option value="Rusak Berat" {{ $condition === 'Rusak Berat' ? 'selected' : '' }}>Rusak Berat</option>
                    </select>

                    <!-- Filter Status Ketersediaan -->
                    <select name="status"
                            aria-label="Filter status ketersediaan"
                            class="px-3.5 py-2.5 rounded-xl border border-slate-200 text-xs text-slate-700 bg-white focus:outline-none focus:ring-2 focus:ring-[#2563EB]">
                        <option value="">Semua Status</option>
                        <option value="tersedia" {{ $statusFilter === 'tersedia' ? 'selected' : '' }}>Tersedia</option>
                        <option value="terbatas" {{ $statusFilter === 'terbatas' ? 'selected' : '' }}>Stok Terbatas</option>
                        <option value="habis" {{ $statusFilter === 'habis' ? 'selected' : '' }}>Habis / Tidak Tersedia</option>
                    </select>

                    <div class="flex items-center gap-2">
                        <button type="submit"
                                class="px-4 py-2.5 rounded-xl bg-[#2563EB] hover:bg-blue-700 text-white text-xs font-semibold transition shadow-sm">
                            Cari
                        </button>
                        @if($search !== '' || $condition || $statusFilter || ($activeScope === 'semua' && $normalizedType))
                            <a href="{{ $filterAction }}"
                               class="px-3 py-2.5 rounded-xl border border-slate-200 text-slate-600 hover:bg-slate-50 text-xs font-medium transition">
                                Reset
                            </a>
                        @endif
                    </div>
                </form>
            </div>
        </div>

        <!-- Grid Kartu Fasilitas -->
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
            @forelse($facilities as $item)
                @php
                    $statusInfo = $item->catalog_status;
                    $borrowAction = $item->borrow_action;
                    $isLab = $item->isRuangan();
                @endphp
                <article class="bg-white rounded-3xl shadow-sm border border-slate-100 overflow-hidden flex flex-col justify-between hover:shadow-md hover:border-blue-200 transition">
                    <div>
                        <!-- Gambar Fasilitas (jika tersedia) atau Visual Ikon -->
                        @if($item->catalog_image)
                            <div class="relative h-44 bg-slate-100 overflow-hidden">
                                <img src="{{ $item->catalog_image }}"
                                     alt="{{ $item->name }}"
                                     class="w-full h-full object-cover">
                                <div class="absolute top-3 left-3 flex items-center gap-2">
                                    <span class="px-2.5 py-1 rounded-full text-[10px] font-bold font-mono bg-[#0C1A40]/90 text-white shadow-sm">
                                        {{ $item->code }}
                                    </span>
                                </div>
                                <div class="absolute top-3 right-3">
                                    <x-badge :status="$statusInfo['badge_status']">{{ $statusInfo['label'] }}</x-badge>
                                </div>
                            </div>
                        @else
                            <div class="relative h-32 px-6 py-4 flex items-center justify-between {{ $isLab ? 'bg-gradient-to-r from-[#0C1A40] to-[#1e3a8a]' : 'bg-gradient-to-r from-[#0C1A40] to-slate-800' }} text-white">
                                <div class="flex items-center gap-3">
                                    <div class="w-12 h-12 rounded-2xl bg-white/15 border border-white/20 flex items-center justify-center shrink-0">
                                        @if($isLab)
                                            <svg class="w-6 h-6 text-blue-200" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9.75 17L9 20l-1 1h8l-1-1-.75-3M3 13h18M5 17h14a2 2 0 002-2V5a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"></path>
                                            </svg>
                                        @else
                                            <svg class="w-6 h-6 text-blue-200" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4"></path>
                                            </svg>
                                        @endif
                                    </div>
                                    <div>
                                        <span class="inline-block px-2 py-0.5 rounded-md bg-white/15 text-[10px] font-bold font-mono tracking-wider text-blue-100">
                                            {{ $item->code }}
                                        </span>
                                        <p class="text-[11px] text-slate-300 mt-1">
                                            {{ $isLab ? 'Laboratorium Komputer' : 'Fasilitas Barang' }}
                                        </p>
                                    </div>
                                </div>
                                <div class="self-start">
                                    <x-badge :status="$statusInfo['badge_status']">{{ $statusInfo['label'] }}</x-badge>
                                </div>
                            </div>
                        @endif

                        <!-- Body Informasi Fasilitas -->
                        <div class="p-6 pb-4">
                            <div class="flex flex-wrap items-center gap-2 mb-2.5">
                                <span class="px-2.5 py-0.5 rounded-full text-[11px] font-semibold {{ $isLab ? 'bg-blue-50 text-[#2563EB]' : 'bg-slate-100 text-[#0C1A40]' }}">
                                    {{ $isLab ? 'Laboratorium' : 'Barang' }}
                                </span>
                                <x-badge :status="strtolower($item->condition)">{{ $item->condition }}</x-badge>
                            </div>

                            <h2 class="text-lg font-bold text-slate-900 mb-1.5 leading-snug">
                                <a href="{{ route('fasilitas.show', $item->id) }}" class="hover:text-[#2563EB] transition">
                                    {{ $item->name }}
                                </a>
                            </h2>

                            <p class="text-xs text-slate-500 leading-relaxed line-clamp-3 mb-4">
                                {{ $item->description ?: 'Fasilitas penunjang kegiatan perkuliahan dan praktikum Program Studi Ilmu Komputer.' }}
                            </p>

                            <!-- Ringkasan Stok atau Kapasitas & Jadwal -->
                            <div class="grid grid-cols-2 gap-3 p-3.5 rounded-2xl bg-[#F8FAFC] border border-slate-100 text-xs">
                                @if($isLab)
                                    <div>
                                        <span class="text-[10px] font-semibold text-slate-400 uppercase block">Kapasitas</span>
                                        <span class="font-bold text-slate-900">{{ $item->capacity }} orang</span>
                                    </div>
                                    <div>
                                        <span class="text-[10px] font-semibold text-slate-400 uppercase block">Jadwal Aktif</span>
                                        <span class="font-bold text-slate-900">{{ $item->active_schedule_count }} jadwal</span>
                                    </div>
                                @else
                                    <div>
                                        <span class="text-[10px] font-semibold text-slate-400 uppercase block">Stok Tersedia</span>
                                        <span class="font-bold {{ $item->available_stock > 0 ? 'text-[#059669]' : 'text-[#DC2626]' }}">
                                            {{ $item->available_stock }} / {{ $item->total_stock }} unit
                                        </span>
                                    </div>
                                    <div>
                                        <span class="text-[10px] font-semibold text-slate-400 uppercase block">Jadwal Aktif</span>
                                        <span class="font-bold text-slate-900">{{ $item->active_schedule_count }} peminjaman</span>
                                    </div>
                                @endif
                            </div>
                        </div>
                    </div>

                    <!-- Footer Aksi -->
                    <div class="px-6 py-4 border-t border-slate-100 bg-slate-50/40 flex items-center justify-between gap-2.5">
                        <a href="{{ route('fasilitas.show', $item->id) }}"
                           class="flex-1 text-center px-3.5 py-2 rounded-xl border border-slate-300 bg-white hover:bg-slate-50 text-slate-700 font-semibold text-xs transition">
                            Detail &amp; Jadwal
                        </a>

                        @auth
                            @if(auth()->user()->role === 'mahasiswa')
                                @if(!$statusInfo['borrowable'])
                                    <button type="button" disabled
                                            class="flex-1 px-3.5 py-2 rounded-xl bg-slate-200 text-slate-500 font-semibold text-xs cursor-not-allowed">
                                        Tidak Tersedia
                                    </button>
                                @elseif($borrowAction['m4_available'])
                                    <a href="{{ $borrowAction['url'] }}"
                                       class="flex-1 text-center px-3.5 py-2 rounded-xl bg-[#2563EB] hover:bg-blue-700 text-white font-semibold text-xs transition shadow-sm">
                                        {{ $borrowAction['label'] }}
                                    </a>
                                @else
                                    <button type="button" disabled
                                            title="Menunggu integrasi Form Peminjaman (Modul M4)"
                                            class="flex-1 px-3.5 py-2 rounded-xl bg-slate-200 text-slate-500 font-semibold text-xs cursor-not-allowed">
                                        Form M4 Belum Aktif
                                    </button>
                                @endif
                            @endif
                        @else
                            <a href="{{ route('login') }}"
                               class="flex-1 text-center px-3.5 py-2 rounded-xl bg-[#2563EB] hover:bg-blue-700 text-white font-semibold text-xs transition shadow-sm">
                                Masuk untuk Pinjam
                            </a>
                        @endauth
                    </div>
                </article>
            @empty
                <div class="col-span-full bg-white rounded-3xl p-12 text-center border border-slate-100 shadow-sm">
                    <div class="w-14 h-14 mx-auto mb-4 rounded-2xl bg-slate-100 text-slate-400 flex items-center justify-center">
                        <svg class="w-7 h-7" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"></path>
                        </svg>
                    </div>
                    <h3 class="text-base font-bold text-slate-800 mb-1">Fasilitas tidak ditemukan</h3>
                    <p class="text-xs text-slate-500 mb-4">Tidak ada fasilitas yang sesuai dengan kata kunci atau filter yang Anda pilih.</p>
                    <a href="{{ route('fasilitas.index') }}"
                       class="inline-flex items-center px-4 py-2 rounded-xl bg-[#2563EB] hover:bg-blue-700 text-white text-xs font-semibold transition">
                        Tampilkan Semua Fasilitas
                    </a>
                </div>
            @endforelse
        </div>

        <!-- Pagination -->
        @if($facilities->hasPages())
            <div class="pt-2">
                {{ $facilities->links() }}
            </div>
        @endif
    </div>

    <!-- Footer -->
    <footer class="bg-white border-t border-slate-200 px-6 sm:px-8 lg:px-14 py-6 mt-8">
        <div class="max-w-7xl mx-auto flex flex-col sm:flex-row items-center justify-between gap-3 text-xs text-slate-500">
            <p>&copy; {{ date('Y') }} SIPAFKA — Program Studi Ilmu Komputer FMIPA Universitas Halu Oleo.</p>
            <div class="flex items-center space-x-5">
                <a href="{{ route('home') }}" class="hover:text-[#2563EB] transition">Beranda</a>
                <a href="{{ route('fasilitas.index') }}" class="hover:text-[#2563EB] transition">Katalog Fasilitas</a>
                <a href="{{ route('panduan.index') }}" class="hover:text-[#2563EB] transition">Panduan</a>
            </div>
        </div>
    </footer>
</div>
@endsection
