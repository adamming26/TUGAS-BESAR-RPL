@extends('layouts.app', ['title' => 'Lupa Kata Sandi'])

@section('content')
<!-- [M2] HALAMAN FORGOT PASSWORD — KONSISTEN DENGAN DESAIN PDF PAGE 1-3 -->
<div class="relative min-h-screen bg-[#0C1A40] text-white flex flex-col justify-between overflow-hidden">
    <!-- Background Gedung Kampus dengan Gradasi Biru Navy -->
    <div class="absolute inset-0 z-0">
        <div class="absolute inset-0 bg-gradient-to-r from-[#0C1A40]/95 via-[#0C1A40]/85 to-[#1e3a8a]/70 z-10"></div>
        <img src="https://images.unsplash.com/photo-1541339907198-e08756dedf3f?auto=format&fit=crop&w=2000&q=80"
             alt="Gedung Kampus FMIPA Universitas Halu Oleo"
             class="w-full h-full object-cover opacity-55">
    </div>

    <!-- Header Navbar -->
    @include('partials.m2-public-header', ['showAuthButtons' => false])

    <!-- Main Split Content -->
    <div class="relative z-20 px-6 sm:px-8 lg:px-14 py-8 lg:py-12 max-w-7xl mx-auto w-full flex-1 flex items-center">
        <div class="grid grid-cols-1 lg:grid-cols-12 gap-10 lg:gap-12 items-center w-full">
            <!-- Left Info -->
            <div class="lg:col-span-7">
                <p class="text-xs font-bold tracking-[0.2em] uppercase text-blue-300 mb-3">PEMULIHAN AKUN</p>
                <h1 class="text-3xl sm:text-4xl lg:text-5xl font-extrabold text-white tracking-tight leading-tight mb-4">
                    Pulihkan akses akun SIPAFKA Anda dengan aman.
                </h1>
                <p class="text-sm sm:text-base text-slate-200/90 leading-relaxed max-w-lg">
                    Masukkan alamat email yang terdaftar. Sistem akan mengirimkan tautan bertoken untuk mengatur ulang kata sandi Anda.
                </p>
            </div>

            <!-- Right Form Card -->
            <div class="lg:col-span-5 flex justify-center lg:justify-end">
                <div class="bg-white rounded-3xl p-7 sm:p-10 shadow-2xl text-slate-800 w-full max-w-md">
                    <div class="w-12 h-12 rounded-2xl bg-blue-50 border border-blue-100 flex items-center justify-center text-[#2563EB] mb-4">
                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 7a2 2 0 012 2m4 0a6 6 0 01-7.743 5.743L11 17H9v2H7v2H4a1 1 0 01-1-1v-2.586a1 1 0 01.293-.707l5.964-5.964A6 6 0 1121 9z"></path>
                        </svg>
                    </div>

                    <h2 class="text-2xl font-bold text-slate-900 mb-1">Lupa kata sandi</h2>
                    <p class="text-xs text-slate-500 mb-6 leading-relaxed">
                        Kami akan mengirimkan tautan pemulihan kata sandi ke alamat email terdaftar Anda.
                    </p>

                    @if(session('status'))
                        <div class="mb-4 p-3.5 rounded-xl bg-emerald-50 border border-emerald-200 text-emerald-700 text-xs font-medium leading-relaxed">
                            {{ session('status') }}
                        </div>
                    @endif

                    @if(session('info'))
                        <div class="mb-4 p-3.5 rounded-xl bg-blue-50 border border-blue-200 text-blue-700 text-xs font-medium leading-relaxed">
                            {{ session('info') }}
                        </div>
                    @endif

                    <form method="POST" action="{{ route('password.email') }}" class="space-y-4">
                        @csrf

                        <div>
                            <label for="email" class="block text-xs font-semibold text-slate-700 mb-1.5">
                                Email terdaftar
                            </label>
                            <input type="email"
                                   id="email"
                                   name="email"
                                   value="{{ old('email') }}"
                                   placeholder="nama@email.com"
                                   autocomplete="email"
                                   required
                                   autofocus
                                   class="w-full px-4 py-3 rounded-xl border {{ $errors->has('email') ? 'border-rose-400 focus:ring-rose-200' : 'border-slate-200 focus:ring-[#2563EB]' }} text-sm focus:outline-none focus:ring-2 focus:border-transparent transition placeholder-slate-400">
                            @error('email')
                                <p class="text-xs text-rose-600 mt-1">{{ $message }}</p>
                            @enderror
                        </div>

                        <div class="pt-1">
                            <button type="submit"
                                    class="w-full bg-[#2563EB] hover:bg-blue-700 text-white font-semibold py-3 px-4 rounded-xl transition shadow-md shadow-blue-500/20 text-sm">
                                Kirim Tautan Reset Kata Sandi
                            </button>
                        </div>

                        <div class="text-center pt-2">
                            <p class="text-xs text-slate-600">
                                Ingat kata sandi Anda?
                                <a href="{{ route('login') }}" class="font-bold text-[#2563EB] hover:underline">Kembali ke Masuk</a>
                            </p>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
