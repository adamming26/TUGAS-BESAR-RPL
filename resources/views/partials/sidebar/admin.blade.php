<!-- [MARKER M2] & [MARKER M6] : SIDEBAR ADMIN PERSIS FIGMA -->
<aside class="w-64 bg-[#0C1A40] min-h-screen text-white flex flex-col justify-between py-6 px-4 shrink-0 transition-all duration-200">
    <div>
        <!-- Logo Bar 3 Lambang -->
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
                    <span class="text-[9px] text-slate-300 block leading-none">Administrator</span>
                </div>
            </div>
        </div>

        <!-- Section Label -->
        <div class="px-4 mb-3">
            <p class="text-[11px] font-bold text-slate-400 tracking-widest uppercase">ADMIN</p>
        </div>

        <!-- Menu Navigation Items -->
        <nav class="space-y-1.5 px-1">
            <!-- Beranda Admin -->
            <a href="{{ Route::has('admin.dashboard') ? route('admin.dashboard') : route('home') }}" 
               class="flex items-center px-4 py-2.5 rounded-xl text-sm font-medium transition {{ request()->routeIs('admin.dashboard') ? 'bg-[#2563EB] text-white shadow-sm' : 'text-slate-300 hover:text-white hover:bg-white/10' }}">
                <span>Beranda</span>
            </a>

            <!-- [MARKER M2] : Kelola Fasilitas -->
            <a href="{{ Route::has('admin.kelola-fasilitas') ? route('admin.kelola-fasilitas') : '#' }}" 
               class="flex items-center px-4 py-2.5 rounded-xl text-sm font-medium transition {{ request()->routeIs('admin.kelola-fasilitas') ? 'bg-[#2563EB] text-white shadow-sm' : 'text-slate-300 hover:text-white hover:bg-white/10' }}">
                <span>Kelola Fasilitas</span>
            </a>

            <!-- [MARKER M6] : Transaksi Berjalan -->
            <a href="{{ Route::has('admin.transaksi-berjalan') ? route('admin.transaksi-berjalan') : '#' }}" 
               class="flex items-center px-4 py-2.5 rounded-xl text-sm font-medium transition {{ request()->routeIs('admin.transaksi-berjalan') ? 'bg-[#2563EB] text-white shadow-sm' : 'text-slate-300 hover:text-white hover:bg-white/10' }}">
                <span>Transaksi</span>
            </a>

            <!-- [MARKER M6] : Riwayat Transaksi -->
            <a href="{{ Route::has('admin.riwayat-transaksi') ? route('admin.riwayat-transaksi') : '#' }}" 
               class="flex items-center px-4 py-2.5 rounded-xl text-sm font-medium transition {{ request()->routeIs('admin.riwayat-transaksi') ? 'bg-[#2563EB] text-white shadow-sm' : 'text-slate-300 hover:text-white hover:bg-white/10' }}">
                <span>Riwayat</span>
            </a>
        </nav>
    </div>

    <!-- [MARKER M1] : Footer Sidebar Keluar / Logout -->
    <div class="px-4 pt-4 border-t border-white/10">
        <form method="POST" action="{{ route('logout') }}" id="logout-form-admin">
            @csrf
            <button type="submit" class="text-xs text-slate-400 hover:text-white transition flex items-center space-x-1">
                <span>Admin</span>
                <span>·</span>
                <span class="font-semibold text-rose-400 hover:text-rose-300">Keluar</span>
            </button>
        </form>
    </div>
</aside>
