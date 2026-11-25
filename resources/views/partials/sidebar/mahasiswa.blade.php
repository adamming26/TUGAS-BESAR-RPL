<!-- [MARKER M3] & [MARKER M5] : SIDEBAR MAHASISWA PERSIS FIGMA -->
<aside class="w-full h-full min-h-screen bg-[#0C1A40] text-white flex flex-col justify-between py-6 px-4 shrink-0 transition-all duration-200">
    <div>
        <!-- Logo Bar -->
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

        <!-- Section Label -->
        <div class="px-4 mb-3">
            <p class="text-[11px] font-bold text-slate-400 tracking-widest uppercase">MAHASISWA</p>
        </div>

        <!-- Menu Navigation Items -->
        <nav class="space-y-1.5 px-1">
            <!-- Dashboard Mahasiswa -->
            <a href="{{ Route::has('mahasiswa.dashboard') ? route('mahasiswa.dashboard') : route('home') }}"
               class="flex items-center px-4 py-2.5 rounded-xl text-sm font-medium transition {{ request()->routeIs('mahasiswa.dashboard') ? 'bg-[#2563EB] text-white shadow-sm' : 'text-slate-300 hover:text-white hover:bg-white/10' }}">
                <span>Dashboard</span>
            </a>

            <!-- Form Peminjaman Barang -->
            <a href="{{ Route::has('mahasiswa.form-barang') ? route('mahasiswa.form-barang') : (Route::has('mahasiswa.form-peminjaman') ? route('mahasiswa.form-peminjaman', ['jenis' => 'barang']) : '#') }}"
               class="flex items-center px-4 py-2.5 rounded-xl text-sm font-medium transition {{ request()->routeIs('mahasiswa.form-barang') || (request()->routeIs('mahasiswa.form-peminjaman') && request()->route('jenis', 'barang') === 'barang') ? 'bg-[#2563EB] text-white shadow-sm' : 'text-slate-300 hover:text-white hover:bg-white/10' }}">
                <span>Pinjam Barang</span>
            </a>

            <!-- Form Peminjaman Laboratorium -->
            <a href="{{ Route::has('mahasiswa.form-lab') ? route('mahasiswa.form-lab') : (Route::has('mahasiswa.form-peminjaman') ? route('mahasiswa.form-peminjaman', ['jenis' => 'lab']) : '#') }}"
               class="flex items-center px-4 py-2.5 rounded-xl text-sm font-medium transition {{ request()->routeIs('mahasiswa.form-lab') || (request()->routeIs('mahasiswa.form-peminjaman') && in_array(request()->route('jenis'), ['lab', 'ruangan'])) ? 'bg-[#2563EB] text-white shadow-sm' : 'text-slate-300 hover:text-white hover:bg-white/10' }}">
                <span>Pinjam Laboratorium</span>
            </a>

            <!-- Daftar & Detail Peminjaman Saya -->
            <a href="{{ Route::has('mahasiswa.peminjaman-saya') ? route('mahasiswa.peminjaman-saya') : '#' }}"
               class="flex items-center px-4 py-2.5 rounded-xl text-sm font-medium transition {{ request()->routeIs('mahasiswa.peminjaman-saya') || request()->routeIs('mahasiswa.detail-peminjaman') ? 'bg-[#2563EB] text-white shadow-sm' : 'text-slate-300 hover:text-white hover:bg-white/10' }}">
                <span>Peminjaman Saya</span>
            </a>

            <!-- Status & Riwayat Peminjaman -->
            <a href="{{ Route::has('mahasiswa.riwayat') ? route('mahasiswa.riwayat') : '#' }}"
               class="flex items-center px-4 py-2.5 rounded-xl text-sm font-medium transition {{ request()->routeIs('mahasiswa.riwayat') ? 'bg-[#2563EB] text-white shadow-sm' : 'text-slate-300 hover:text-white hover:bg-white/10' }}">
                <span>Riwayat &amp; Status</span>
            </a>

            <!-- Katalog Fasilitas (M3) -->
            <a href="{{ Route::has('fasilitas.index') ? route('fasilitas.index') : '#' }}"
               class="flex items-center px-4 py-2.5 rounded-xl text-sm font-medium transition {{ request()->routeIs('fasilitas.index') ? 'bg-[#2563EB] text-white shadow-sm' : 'text-slate-300 hover:text-white hover:bg-white/10' }}">
                <span>Katalog Fasilitas</span>
            </a>

            <!-- Profil Saya (M2) -->
            <a href="{{ Route::has('profile.index') ? route('profile.index') : '#' }}"
               class="flex items-center px-4 py-2.5 rounded-xl text-sm font-medium transition {{ request()->routeIs('profile.*') ? 'bg-[#2563EB] text-white shadow-sm' : 'text-slate-300 hover:text-white hover:bg-white/10' }}">
                <span>Profil Saya</span>
            </a>
        </nav>
    </div>

    <!-- [MARKER M1] : Footer Sidebar Profil & Keluar -->
    <div class="px-4 pt-4 border-t border-white/10 flex items-center justify-between text-xs text-slate-400">
        <a href="{{ route('profile.index') }}" class="hover:text-white transition flex items-center space-x-1.5" title="Lihat Profil Mahasiswa">
            <span>Mahasiswa</span>
        </a>
        <span>·</span>
        <form method="POST" action="{{ route('logout') }}" id="logout-form-mahasiswa" class="inline">
            @csrf
            <button type="submit" class="font-semibold text-rose-400 hover:text-rose-300 transition">Keluar</button>
        </form>
    </div>
</aside>
