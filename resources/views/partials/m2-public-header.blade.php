@php
    $showAuthButtons = $showAuthButtons ?? true;
@endphp

<!-- Header Publik & Autentikasi M2 — Konsisten dengan Desain PDF Page 1-3 -->
<header class="relative z-20 w-full px-6 sm:px-8 lg:px-14 py-6">
    <div class="max-w-7xl mx-auto flex items-center justify-between gap-4">
        <!-- 3 Logos Brand Pill Persis Desain PDF -->
        <a href="{{ route('home') }}" class="flex items-center space-x-2 bg-white/95 backdrop-blur-sm px-4 py-2 rounded-2xl shadow-sm hover:opacity-95 transition shrink-0">
            <div class="w-8 h-8 rounded-full bg-blue-700 flex items-center justify-center text-white text-xs font-bold shadow-inner" aria-hidden="true">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 14l9-5-9-5-9 5 9 5z"></path>
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 14l6.16-3.422a12.083 12.083 0 01.665 6.479A11.952 11.952 0 0012 20.055a11.952 11.952 0 00-6.824-2.998 12.078 12.078 0 01.665-6.479L12 14z"></path>
                </svg>
            </div>
            <div class="w-8 h-8 rounded-full bg-amber-500 flex items-center justify-center text-white text-xs font-bold shadow-inner" aria-hidden="true">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"></path>
                </svg>
            </div>
            <div class="w-8 h-8 rounded-full bg-[#0C1A40] flex items-center justify-center text-white text-xs font-bold shadow-inner">
                <span class="text-[10px] tracking-tight">UHO</span>
            </div>
        </a>

        <!-- Desktop Navigation -->
        <nav class="hidden md:flex items-center space-x-6 text-sm font-medium" aria-label="Navigasi Utama">
            <a href="{{ route('home') }}"
               class="{{ request()->routeIs('home') ? 'text-white font-semibold' : 'text-white/85 hover:text-white' }} transition">
                Beranda
            </a>
            <a href="{{ route('fasilitas.index') }}"
               class="{{ request()->routeIs('fasilitas.index') ? 'text-white font-semibold' : 'text-white/85 hover:text-white' }} transition">
                Fasilitas
            </a>
            <a href="{{ route('panduan.index') }}"
               class="{{ request()->routeIs('panduan.index') ? 'text-white font-semibold' : 'text-white/85 hover:text-white' }} transition">
                Panduan
            </a>

            @if($showAuthButtons)
                @auth
                    <a href="{{ route('profile.index') }}"
                       class="text-white/90 hover:text-white transition">
                        Profil
                    </a>

                    @if(auth()->user()->role === 'admin' && \Illuminate\Support\Facades\Route::has('admin.dashboard'))
                        <a href="{{ route('admin.dashboard') }}"
                           class="bg-[#2563EB] hover:bg-blue-700 text-white px-5 py-2.5 rounded-2xl transition font-semibold shadow-md">
                            Dashboard Admin
                        </a>
                    @elseif(auth()->user()->role === 'dosen' && \Illuminate\Support\Facades\Route::has('dosen.dashboard'))
                        <a href="{{ route('dosen.dashboard') }}"
                           class="bg-[#2563EB] hover:bg-blue-700 text-white px-5 py-2.5 rounded-2xl transition font-semibold shadow-md">
                            Dashboard Dosen
                        </a>
                    @elseif(auth()->user()->role === 'mahasiswa' && \Illuminate\Support\Facades\Route::has('mahasiswa.dashboard'))
                        <a href="{{ route('mahasiswa.dashboard') }}"
                           class="bg-[#2563EB] hover:bg-blue-700 text-white px-5 py-2.5 rounded-2xl transition font-semibold shadow-md">
                            Dashboard Mahasiswa
                        </a>
                    @else
                        <a href="{{ route('profile.index') }}"
                           class="bg-white hover:bg-slate-100 text-[#0C1A40] px-5 py-2.5 rounded-2xl transition font-semibold shadow-sm">
                            Akun Saya
                        </a>
                    @endif
                @else
                    <a href="{{ route('login') }}"
                       class="border border-white/60 hover:bg-white/10 text-white px-6 py-2.5 rounded-2xl transition font-medium">
                        Masuk
                    </a>
                    <a href="{{ route('register') }}"
                       class="bg-white hover:bg-slate-100 text-[#0C1A40] px-6 py-2.5 rounded-2xl transition font-semibold shadow-sm">
                        Daftar
                    </a>
                @endauth
            @endif
        </nav>

        <!-- Mobile Menu Button -->
        <button type="button"
                onclick="toggleM2MobileMenu()"
                class="md:hidden p-2.5 rounded-xl bg-white/10 hover:bg-white/20 border border-white/20 text-white focus:outline-none"
                aria-label="Buka menu navigasi">
            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16"></path>
            </svg>
        </button>
    </div>

    <!-- Mobile Dropdown Navigation -->
    <div id="m2-mobile-menu" class="hidden md:hidden mt-4 max-w-7xl mx-auto bg-[#0C1A40]/95 backdrop-blur-lg border border-white/15 rounded-2xl p-4 space-y-2 shadow-xl">
        <a href="{{ route('home') }}" class="block px-4 py-2.5 rounded-xl text-sm font-medium text-white hover:bg-white/10 transition">Beranda</a>
        <a href="{{ route('fasilitas.index') }}" class="block px-4 py-2.5 rounded-xl text-sm font-medium text-white/90 hover:bg-white/10 transition">Fasilitas</a>
        <a href="{{ route('panduan.index') }}" class="block px-4 py-2.5 rounded-xl text-sm font-medium text-white/90 hover:bg-white/10 transition">Panduan</a>

        @if($showAuthButtons)
            <div class="pt-2 mt-2 border-t border-white/15 flex flex-col gap-2">
                @auth
                    <a href="{{ route('profile.index') }}" class="block text-center px-4 py-2.5 rounded-xl bg-white/10 text-white text-sm font-medium">
                        Profil Saya
                    </a>
                    @if(auth()->user()->role === 'admin' && \Illuminate\Support\Facades\Route::has('admin.dashboard'))
                        <a href="{{ route('admin.dashboard') }}" class="block text-center px-4 py-2.5 rounded-xl bg-[#2563EB] text-white text-sm font-semibold">
                            Dashboard Admin
                        </a>
                    @elseif(auth()->user()->role === 'dosen' && \Illuminate\Support\Facades\Route::has('dosen.dashboard'))
                        <a href="{{ route('dosen.dashboard') }}" class="block text-center px-4 py-2.5 rounded-xl bg-[#2563EB] text-white text-sm font-semibold">
                            Dashboard Dosen
                        </a>
                    @elseif(auth()->user()->role === 'mahasiswa' && \Illuminate\Support\Facades\Route::has('mahasiswa.dashboard'))
                        <a href="{{ route('mahasiswa.dashboard') }}" class="block text-center px-4 py-2.5 rounded-xl bg-[#2563EB] text-white text-sm font-semibold">
                            Dashboard Mahasiswa
                        </a>
                    @endif
                    <form method="POST" action="{{ route('logout') }}">
                        @csrf
                        <button type="submit" class="w-full px-4 py-2.5 rounded-xl border border-rose-400/50 text-rose-200 hover:bg-rose-500/20 text-sm font-medium">
                            Keluar
                        </button>
                    </form>
                @else
                    <a href="{{ route('login') }}" class="block text-center border border-white/60 text-white px-4 py-2.5 rounded-xl text-sm font-medium">
                        Masuk
                    </a>
                    <a href="{{ route('register') }}" class="block text-center bg-white text-[#0C1A40] px-4 py-2.5 rounded-xl text-sm font-semibold">
                        Daftar
                    </a>
                @endauth
            </div>
        @endif
    </div>
</header>

@once
    @push('scripts')
    <script>
        function toggleM2MobileMenu() {
            const menu = document.getElementById('m2-mobile-menu');
            if (menu) {
                menu.classList.toggle('hidden');
            }
        }
    </script>
    @endpush
@endonce
