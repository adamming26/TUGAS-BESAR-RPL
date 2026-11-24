@props([
    'role' => null,
])

@php
    $currentRole = $role ?? auth()->user()->role ?? 'mahasiswa';
@endphp

<!-- Kerangka Sidebar Bersama SIPAFKA -->
<aside class="w-64 bg-[#0C1A40] min-h-screen text-white flex flex-col justify-between py-6 px-4 shrink-0 transition-all duration-200">
    <div>
        <!-- Logo / Brand Prodi -->
        <div class="px-3 mb-8">
            <div class="flex items-center space-x-2 bg-white/10 p-2.5 rounded-2xl backdrop-blur-sm">
                <div class="w-8 h-8 rounded-full bg-blue-600 flex items-center justify-center text-white text-xs font-bold shadow">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 14l9-5-9-5-9 5 9 5z"></path>
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 14l6.16-3.422a12.083 12.083 0 01.665 6.479A11.952 11.952 0 0012 20.055a11.952 11.952 0 00-6.824-2.998 12.078 12.078 0 01.665-6.479L12 14z"></path>
                    </svg>
                </div>
                <div class="w-8 h-8 rounded-full bg-amber-500 flex items-center justify-center text-white text-xs font-bold shadow">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"></path>
                    </svg>
                </div>
                <div class="w-8 h-8 rounded-full bg-white text-[#0C1A40] flex items-center justify-center text-[10px] font-black shadow">
                    UHO
                </div>
                <div class="pl-1">
                    <span class="text-xs font-bold tracking-tight block leading-tight">SIPAFKA</span>
                    <span class="text-[9px] text-slate-300 block leading-none">Ilmu Komputer</span>
                </div>
            </div>
        </div>

        <!-- Role Badge Header -->
        <div class="px-4 mb-3">
            <p class="text-[11px] font-bold text-slate-400 tracking-widest uppercase">{{ strtoupper($currentRole) }}</p>
        </div>

        <!-- Navigation Menu -->
        <nav class="space-y-1.5 px-1">
            {{-- // START M1 --}}
            <!-- Beranda Umum M1 -->
            <a href="{{ route('home') }}" class="flex items-center px-4 py-2.5 rounded-xl text-sm font-medium transition {{ request()->routeIs('home') ? 'bg-[#2563EB] text-white shadow-sm' : 'text-slate-300 hover:text-white hover:bg-white/10' }}">
                <span>Beranda Utama</span>
            </a>
            {{-- // END M1 --}}

            {{-- // START M2 --}}
            <!-- Menu Navigasi Modul M2 (Manajemen Fasilitas & Inventaris Admin) -->
            {{-- // END M2 --}}

            {{-- // START M3 --}}
            <!-- Menu Navigasi Modul M3 (Peminjaman Fasilitas Mahasiswa) -->
            {{-- // END M3 --}}

            {{-- // START M4 --}}
            <!-- Menu Navigasi Modul M4 (Persetujuan & Keputusan Dosen) -->
            {{-- // END M4 --}}

            {{-- // START M5 --}}
            <!-- Menu Navigasi Modul M5 (QR Code Token & Pindai) -->
            {{-- // END M5 --}}

            {{-- // START M6 --}}
            <!-- Menu Navigasi Modul M6 (Monitoring, Log Jadwal & Pelaporan) -->
            {{-- // END M6 --}}

            {{ $slot }}
        </nav>
    </div>

    <!-- Footer Sidebar / Auth M1 -->
    <div class="px-4 pt-4 border-t border-white/10">
        {{-- // START M1 --}}
        @auth
            <form method="POST" action="{{ route('logout') }}" id="sidebar-logout-form">
                @csrf
                <button type="submit" class="text-xs text-slate-400 hover:text-white transition flex items-center space-x-1">
                    <span class="capitalize">{{ $currentRole }}</span>
                    <span>·</span>
                    <span class="font-semibold text-rose-400 hover:text-rose-300">Keluar</span>
                </button>
            </form>
        @else
            <a href="{{ route('login') }}" class="text-xs text-slate-300 hover:text-white font-medium">Masuk ke Akun &rarr;</a>
        @endauth
        {{-- // END M1 --}}
    </div>
</aside>
