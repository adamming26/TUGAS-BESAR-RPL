@extends('layouts.app', ['title' => 'Masuk'])

@section('content')
<!-- [M2] HALAMAN LOGIN SIPAFKA — SESUAI DESAIN PDF PAGE 2 -->
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

    <!-- Main Split Content Persis PDF Page 2 -->
    <div class="relative z-20 px-6 sm:px-8 lg:px-14 py-8 lg:py-12 max-w-7xl mx-auto w-full flex-1 flex items-center">
        <div class="grid grid-cols-1 lg:grid-cols-12 gap-10 lg:gap-12 items-center w-full">
            <!-- Left Headline -->
            <div class="lg:col-span-7">
                <h1 class="text-3xl sm:text-4xl lg:text-5xl font-extrabold text-white tracking-tight leading-tight mb-4">
                    Kelola peminjaman fasilitas prodi dalam satu tempat.
                </h1>
                <p class="text-sm sm:text-base text-slate-200/90 leading-relaxed max-w-lg">
                    Mahasiswa mengajukan dan memantau, dosen memeriksa dan menyetujui, admin mengelola fasilitas dan memindai QR Code.
                </p>
            </div>

            <!-- Right Login Card -->
            <div class="lg:col-span-5 flex justify-center lg:justify-end">
                <div class="bg-white rounded-3xl p-7 sm:p-10 shadow-2xl text-slate-800 w-full max-w-md">
                    <h2 class="text-2xl font-bold text-slate-900 mb-1">Masuk ke akun</h2>
                    <p class="text-xs text-slate-500 mb-6">Gunakan akun sesuai peran Anda.</p>

                    @if(session('status'))
                        <div class="mb-4 p-3.5 rounded-xl bg-emerald-50 border border-emerald-200 text-emerald-700 text-xs font-medium">
                            {{ session('status') }}
                        </div>
                    @endif

                    @if($errors->any())
                        <div class="mb-4 p-3.5 rounded-xl bg-rose-50 border border-rose-200 text-rose-700 text-xs font-medium">
                            {{ $errors->first() }}
                        </div>
                    @endif

                    <form action="{{ route('login.post') }}" method="POST" class="space-y-4">
                        @csrf

                        <!-- NIM / NIP / Email -->
                        <div>
                            <label for="identity" class="block text-xs font-semibold text-slate-700 mb-1.5">
                                NIM / NIP / Email
                            </label>
                            <input type="text"
                                   name="identity"
                                   id="identity"
                                   value="{{ old('identity') }}"
                                   placeholder="Masukkan identitas akun"
                                   autocomplete="username"
                                   required
                                   autofocus
                                   class="w-full px-4 py-3 rounded-xl border {{ $errors->has('identity') ? 'border-rose-400 focus:ring-rose-200' : 'border-slate-200 focus:ring-[#2563EB]' }} text-sm focus:outline-none focus:ring-2 focus:border-transparent transition placeholder-slate-400">
                            @error('identity')
                                <p class="text-xs text-rose-600 mt-1">{{ $message }}</p>
                            @enderror
                        </div>

                        <!-- Kata Sandi -->
                        <div>
                            <div class="flex items-center justify-between mb-1.5">
                                <label for="password" class="block text-xs font-semibold text-slate-700">
                                    Kata sandi
                                </label>
                                <a href="{{ route('password.request') }}" class="text-xs font-semibold text-[#2563EB] hover:underline">
                                    Lupa kata sandi?
                                </a>
                            </div>
                            <div class="relative">
                                <input type="password"
                                       name="password"
                                       id="password"
                                       placeholder="Masukkan kata sandi"
                                       autocomplete="current-password"
                                       required
                                       class="w-full px-4 py-3 pr-11 rounded-xl border {{ $errors->has('password') ? 'border-rose-400 focus:ring-rose-200' : 'border-slate-200 focus:ring-[#2563EB]' }} text-sm focus:outline-none focus:ring-2 focus:border-transparent transition placeholder-slate-400">
                                <button type="button"
                                        onclick="togglePasswordVisibility('password')"
                                        class="absolute inset-y-0 right-3 flex items-center text-slate-400 hover:text-slate-600"
                                        aria-label="Tampilkan atau sembunyikan kata sandi">
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"></path>
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"></path>
                                    </svg>
                                </button>
                            </div>
                            @error('password')
                                <p class="text-xs text-rose-600 mt-1">{{ $message }}</p>
                            @enderror
                        </div>

                        <!-- Ingat Saya -->
                        <div class="flex items-center justify-between pt-0.5">
                            <label class="inline-flex items-center gap-2 text-xs text-slate-600 cursor-pointer select-none">
                                <input type="checkbox" name="remember" value="1" {{ old('remember') ? 'checked' : '' }}
                                       class="rounded border-slate-300 text-[#2563EB] focus:ring-[#2563EB]">
                                <span>Ingat sesi saya</span>
                            </label>
                        </div>

                        <!-- Submit Button -->
                        <div class="pt-1">
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

@push('scripts')
<script>
    function togglePasswordVisibility(inputId) {
        const input = document.getElementById(inputId);
        if (input) {
            input.type = input.type === 'password' ? 'text' : 'password';
        }
    }
</script>
@endpush
