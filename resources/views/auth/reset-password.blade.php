@extends('layouts.app', ['title' => 'Atur Ulang Kata Sandi'])

@section('content')
<!-- [M2] HALAMAN RESET PASSWORD — KONSISTEN DENGAN DESAIN PDF PAGE 1-3 -->
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
            <!-- Left Form Card -->
            <div class="lg:col-span-5 flex justify-center lg:justify-start order-2 lg:order-1">
                <div class="bg-white rounded-3xl p-7 sm:p-10 shadow-2xl text-slate-800 w-full max-w-md">
                    <div class="w-12 h-12 rounded-2xl bg-emerald-50 border border-emerald-100 flex items-center justify-center text-emerald-600 mb-4">
                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"></path>
                        </svg>
                    </div>

                    <h2 class="text-2xl font-bold text-slate-900 mb-1">Atur ulang kata sandi</h2>
                    <p class="text-xs text-slate-500 mb-6">
                        Buat kata sandi baru yang kuat untuk mengamankan akun Anda.
                    </p>

                    @if($errors->has('token'))
                        <div class="mb-4 p-3.5 rounded-xl bg-rose-50 border border-rose-200 text-rose-700 text-xs font-medium">
                            {{ $errors->first('token') }}
                        </div>
                    @endif

                    <form method="POST" action="{{ route('password.update') }}" class="space-y-4">
                        @csrf
                        <input type="hidden" name="token" value="{{ $token }}">

                        <!-- Email -->
                        <div>
                            <label for="email" class="block text-xs font-semibold text-slate-700 mb-1.5">
                                Email akun
                            </label>
                            <input type="email"
                                   id="email"
                                   name="email"
                                   value="{{ old('email', $email) }}"
                                   placeholder="nama@email.com"
                                   autocomplete="email"
                                   required
                                   class="w-full px-4 py-3 rounded-xl border {{ $errors->has('email') ? 'border-rose-400 focus:ring-rose-200' : 'border-slate-200 focus:ring-[#2563EB]' }} text-sm focus:outline-none focus:ring-2 focus:border-transparent transition placeholder-slate-400">
                            @error('email')
                                <p class="text-xs text-rose-600 mt-1">{{ $message }}</p>
                            @enderror
                        </div>

                        <!-- Kata Sandi Baru -->
                        <div>
                            <label for="password" class="block text-xs font-semibold text-slate-700 mb-1.5">
                                Kata sandi baru
                            </label>
                            <input type="password"
                                   id="password"
                                   name="password"
                                   placeholder="Minimal 8 karakter"
                                   autocomplete="new-password"
                                   required
                                   autofocus
                                   class="w-full px-4 py-3 rounded-xl border {{ $errors->has('password') ? 'border-rose-400 focus:ring-rose-200' : 'border-slate-200 focus:ring-[#2563EB]' }} text-sm focus:outline-none focus:ring-2 focus:border-transparent transition placeholder-slate-400">
                            @error('password')
                                <p class="text-xs text-rose-600 mt-1">{{ $message }}</p>
                            @enderror
                        </div>

                        <!-- Konfirmasi Kata Sandi Baru -->
                        <div>
                            <label for="password_confirmation" class="block text-xs font-semibold text-slate-700 mb-1.5">
                                Konfirmasi kata sandi baru
                            </label>
                            <input type="password"
                                   id="password_confirmation"
                                   name="password_confirmation"
                                   placeholder="Ulangi kata sandi baru"
                                   autocomplete="new-password"
                                   required
                                   class="w-full px-4 py-3 rounded-xl border border-slate-200 text-sm focus:outline-none focus:ring-2 focus:ring-[#2563EB] focus:border-transparent transition placeholder-slate-400">
                        </div>

                        <div class="pt-1">
                            <button type="submit"
                                    class="w-full bg-[#2563EB] hover:bg-blue-700 text-white font-semibold py-3 px-4 rounded-xl transition shadow-md shadow-blue-500/20 text-sm">
                                Simpan Kata Sandi Baru
                            </button>
                        </div>

                        <div class="text-center pt-2">
                            <p class="text-xs text-slate-600">
                                Batal atur ulang?
                                <a href="{{ route('login') }}" class="font-bold text-[#2563EB] hover:underline">Kembali ke Masuk</a>
                            </p>
                        </div>
                    </form>
                </div>
            </div>

            <!-- Right Info -->
            <div class="lg:col-span-7 order-1 lg:order-2">
                <p class="text-xs font-bold tracking-[0.2em] uppercase text-blue-300 mb-3">KEAMANAN AKUN</p>
                <h1 class="text-3xl sm:text-4xl lg:text-5xl font-extrabold text-white tracking-tight leading-tight mb-4">
                    Buat kata sandi baru untuk kembali masuk.
                </h1>
                <p class="text-sm sm:text-base text-slate-200/90 leading-relaxed max-w-lg">
                    Gunakan minimal 8 karakter kombinasi huruf dan angka. Setelah berhasil disimpan, tautan pemulihan lama tidak lagi berlaku.
                </p>
            </div>
        </div>
    </div>
</div>
@endsection
