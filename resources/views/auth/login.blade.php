@extends('layouts.app')

@section('content')
<!-- [MARKER M1] : HALAMAN MASUK / LOGIN PERSIS FIGMA (PAGE 2) -->
<div class="relative min-h-screen bg-[#0C1A40] text-white flex flex-col justify-between overflow-hidden">
    <!-- Background FMIPA Building with Blue Tint Overlay -->
    <div class="absolute inset-0 z-0">
        <div class="absolute inset-0 bg-gradient-to-r from-[#0C1A40]/95 via-[#0C1A40]/85 to-[#1e3a8a]/75 z-10"></div>
        <img src="https://images.unsplash.com/photo-1541339907198-e08756dedf3f?auto=format&fit=crop&w=2000&q=80" 
             alt="Gedung Kampus FMIPA UHO" 
             class="w-full h-full object-cover opacity-50">
    </div>

    <!-- Header Navbar -->
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

        <div class="flex items-center space-x-6 text-sm font-medium">
            <a href="{{ route('home') }}" class="text-white hover:text-blue-200 transition">Beranda</a>
            <a href="{{ route('fasilitas.index') }}" class="text-white/90 hover:text-white transition">Fasilitas</a>
        </div>
    </header>

    <!-- Main Content Grid -->
    <div class="relative z-20 px-8 lg:px-14 py-8 lg:py-12 max-w-7xl mx-auto w-full flex-1 flex items-center">
        <div class="grid grid-cols-1 lg:grid-cols-12 gap-12 items-center w-full">
            <!-- Left Headline Text -->
            <div class="lg:col-span-7">
                <h1 class="text-4xl lg:text-5xl font-extrabold text-white tracking-tight leading-tight mb-4">
                    Kelola peminjaman fasilitas prodi dalam satu tempat.
                </h1>
                <p class="text-base text-slate-200/90 leading-relaxed max-w-lg">
                    Mahasiswa mengajukan dan memantau, dosen memeriksa dan menyetujui, admin mengelola fasilitas dan memindai QR Code.
                </p>
            </div>

            <!-- Right Login Form Card Persis Figma -->
            <div class="lg:col-span-5 flex justify-center lg:justify-end">
                <div class="bg-white rounded-3xl p-8 sm:p-10 shadow-2xl text-slate-800 w-full max-w-md">
                    <h2 class="text-2xl font-bold text-slate-900 mb-1">Masuk ke akun</h2>
                    <p class="text-xs text-slate-500 mb-6">Gunakan akun sesuai peran Anda.</p>

                    <form action="{{ route('login.post') }}" method="POST" class="space-y-4">
                        @csrf
                        <!-- NIM / NIP / Email Input -->
                        <div>
                            <label for="identity" class="block text-xs font-semibold text-slate-700 mb-1.5">
                                NIM / NIP / Email
                            </label>
                            <input type="text" 
                                   name="identity" 
                                   id="identity" 
                                   value="{{ old('identity') }}"
                                   placeholder="Masukkan identitas akun" 
                                   required
                                   class="w-full px-4 py-3 rounded-xl border border-slate-200 text-sm focus:outline-none focus:ring-2 focus:ring-[#2563EB] focus:border-transparent transition placeholder-slate-400">
                            @error('identity')
                                <p class="text-xs text-rose-600 mt-1">{{ $message }}</p>
                            @enderror
                        </div>

                        <!-- Password Input -->
                        <div>
                            <label for="password" class="block text-xs font-semibold text-slate-700 mb-1.5">
                                Kata sandi
                            </label>
                            <input type="password" 
                                   name="password" 
                                   id="password" 
                                   placeholder="Masukkan kata sandi" 
                                   required
                                   class="w-full px-4 py-3 rounded-xl border border-slate-200 text-sm focus:outline-none focus:ring-2 focus:ring-[#2563EB] focus:border-transparent transition placeholder-slate-400">
                            @error('password')
                                <p class="text-xs text-rose-600 mt-1">{{ $message }}</p>
                            @enderror
                        </div>

                        <!-- Submit Button -->
                        <div class="pt-2">
                            <button type="submit" 
                                    class="w-full bg-[#2563EB] hover:bg-blue-700 text-white font-semibold py-3 px-4 rounded-xl transition shadow-md shadow-blue-500/20 text-sm">
                                Masuk
                            </button>
                        </div>

                        <!-- Footer Links -->
                        <div class="text-center pt-2">
                            <p class="text-xs text-slate-600">
                                Belum punya akun? 
                                <a href="{{ route('register') }}" class="font-bold text-[#2563EB] hover:underline">Daftar</a>
                            </p>
                            <p class="text-[11px] text-slate-400 mt-2 leading-tight">
                                Setelah masuk, kamu diarahkan ke halaman sesuai hak akses.
                            </p>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
