<!-- [MARKER M1] : NAVBAR PUBLIK (BERANDA, FASILITAS, MASUK, DAFTAR) -->
<nav class="w-full bg-transparent absolute top-0 left-0 z-30 px-6 lg:px-12 py-5 flex items-center justify-between">
    <!-- Logo Kemendikbud & UHO -->
    <a href="{{ route('home') }}" class="flex items-center space-x-3 bg-white/95 backdrop-blur-sm px-4 py-2 rounded-2xl shadow-sm hover:opacity-95 transition">
        <div class="flex items-center space-x-2">
            <!-- Kemendikbud / Kampus Logo Dummy / SVGs -->
            <div class="w-8 h-8 rounded-full bg-blue-700 flex items-center justify-center text-white font-bold text-xs shadow-inner">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 14l9-5-9-5-9 5 9 5z"></path>
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 14l6.16-3.422a12.083 12.083 0 01.665 6.479A11.952 11.952 0 0012 20.055a11.952 11.952 0 00-6.824-2.998 12.078 12.078 0 01.665-6.479L12 14z"></path>
                </svg>
            </div>
            <div class="w-8 h-8 rounded-full bg-amber-500 flex items-center justify-center text-white font-bold text-xs shadow-inner">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"></path>
                </svg>
            </div>
            <div class="w-8 h-8 rounded-full bg-[#0C1A40] flex items-center justify-center text-white font-bold text-xs shadow-inner">
                <span class="text-[10px] tracking-tighter">UHO</span>
            </div>
        </div>
        <div class="hidden sm:block text-left pl-1 border-l border-slate-200">
            <p class="text-xs font-bold text-[#0C1A40] leading-none">SIPAFKA</p>
            <p class="text-[10px] text-slate-500 leading-tight">Ilmu Komputer UHO</p>
        </div>
    </a>

    <!-- Navigasi Menu Header -->
    <div class="flex items-center space-x-6 text-sm font-medium">
        <a href="{{ route('home') }}" class="text-white/90 hover:text-white transition">Beranda</a>
        <a href="{{ route('fasilitas.index') }}" class="text-white/90 hover:text-white transition">Fasilitas</a>

        @auth
            @if(auth()->user()->role === 'admin')
                <a href="{{ Route::has('admin.dashboard') ? route('admin.dashboard') : route('home') }}" class="bg-[#2563EB] hover:bg-blue-700 text-white px-5 py-2 rounded-xl transition font-medium shadow-md">Dashboard Admin</a>
            @elseif(auth()->user()->role === 'dosen')
                <a href="{{ Route::has('dosen.dashboard') ? route('dosen.dashboard') : route('home') }}" class="bg-[#2563EB] hover:bg-blue-700 text-white px-5 py-2 rounded-xl transition font-medium shadow-md">Dashboard Dosen</a>
            @else
                <a href="{{ Route::has('mahasiswa.dashboard') ? route('mahasiswa.dashboard') : route('home') }}" class="bg-[#2563EB] hover:bg-blue-700 text-white px-5 py-2 rounded-xl transition font-medium shadow-md">Dashboard</a>
            @endif
        @else
            <!-- [MARKER M1] : TOMBOL MASUK DAN DAFTAR PERSIS FIGMA -->
            <a href="{{ route('login') }}" class="border border-white/60 hover:bg-white/10 text-white px-6 py-2 rounded-xl transition font-medium">Masuk</a>
            <a href="{{ route('register') }}" class="bg-white hover:bg-slate-100 text-[#0C1A40] px-6 py-2 rounded-xl transition font-medium shadow-sm">Daftar</a>
        @endauth
    </div>
</nav>
