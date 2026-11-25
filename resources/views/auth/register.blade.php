@extends('layouts.app', ['title' => 'Daftar Akun'])

@section('content')
<!-- [M2] HALAMAN REGISTER SIPAFKA — SESUAI DESAIN PDF PAGE 3 -->
<div class="relative min-h-screen bg-[#0C1A40] text-white flex flex-col justify-between overflow-hidden">
    <!-- Background Gedung Kampus dengan Gradasi Biru Navy -->
    <div class="absolute inset-0 z-0">
        <div class="absolute inset-0 bg-gradient-to-r from-[#0C1A40]/95 via-[#0C1A40]/85 to-[#1e3a8a]/70 z-10"></div>
        <img src="{{ asset('images/gedung-kampus.jpg') }}"
             alt="Gedung Kampus FMIPA Universitas Halu Oleo"
             class="w-full h-full object-cover opacity-55">
    </div>

    <!-- Header Navbar -->
    @include('partials.m2-public-header', ['showAuthButtons' => false])

    <!-- Main Split Content Persis PDF Page 3 -->
    <div class="relative z-20 px-6 sm:px-8 lg:px-14 py-8 lg:py-10 max-w-7xl mx-auto w-full flex-1 flex items-center">
        <div class="grid grid-cols-1 lg:grid-cols-12 gap-10 lg:gap-12 items-center w-full">
            <!-- Left Register Form Card -->
            <div class="lg:col-span-5 flex justify-center lg:justify-start order-2 lg:order-1">
                <div class="bg-white rounded-3xl p-7 sm:p-9 shadow-2xl text-slate-800 w-full max-w-md">
                    <h2 class="text-2xl font-bold text-slate-900 mb-1">Daftar akun</h2>
                    <p class="text-xs text-slate-500 mb-5">Lengkapi data berikut.</p>

                    <form action="{{ route('register.post') }}" method="POST" class="space-y-3.5">
                        @csrf

                        <!-- Nama Lengkap -->
                        <div>
                            <label for="name" class="block text-xs font-semibold text-slate-700 mb-1">
                                Nama lengkap
                            </label>
                            <input type="text"
                                   name="name"
                                   id="name"
                                   value="{{ old('name') }}"
                                   placeholder="Nama lengkap"
                                   autocomplete="name"
                                   required
                                   autofocus
                                   class="w-full px-4 py-2.5 rounded-xl border {{ $errors->has('name') ? 'border-rose-400 focus:ring-rose-200' : 'border-slate-200 focus:ring-[#2563EB]' }} text-sm focus:outline-none focus:ring-2 focus:border-transparent transition placeholder-slate-400">
                            @error('name')
                                <p class="text-xs text-rose-600 mt-1">{{ $message }}</p>
                            @enderror
                        </div>

                        <!-- NIM -->
                        <div>
                            <label for="nim" class="block text-xs font-semibold text-slate-700 mb-1">
                                NIM
                            </label>
                            <input type="text"
                                   name="nim"
                                   id="nim"
                                   value="{{ old('nim') }}"
                                   placeholder="Nomor induk mahasiswa"
                                   required
                                   class="w-full px-4 py-2.5 rounded-xl border {{ $errors->has('nim') ? 'border-rose-400 focus:ring-rose-200' : 'border-slate-200 focus:ring-[#2563EB]' }} text-sm focus:outline-none focus:ring-2 focus:border-transparent transition placeholder-slate-400">
                            @error('nim')
                                <p class="text-xs text-rose-600 mt-1">{{ $message }}</p>
                            @enderror
                        </div>

                        <!-- Email -->
                        <div>
                            <label for="email" class="block text-xs font-semibold text-slate-700 mb-1">
                                Email
                            </label>
                            <input type="email"
                                   name="email"
                                   id="email"
                                   value="{{ old('email') }}"
                                   placeholder="nama@email.com"
                                   autocomplete="email"
                                   required
                                   class="w-full px-4 py-2.5 rounded-xl border {{ $errors->has('email') ? 'border-rose-400 focus:ring-rose-200' : 'border-slate-200 focus:ring-[#2563EB]' }} text-sm focus:outline-none focus:ring-2 focus:border-transparent transition placeholder-slate-400">
                            @error('email')
                                <p class="text-xs text-rose-600 mt-1">{{ $message }}</p>
                            @enderror
                        </div>

                        <!-- Jenjang & Nomor HP -->
                        <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                            <div>
                                <label for="jenjang" class="block text-xs font-semibold text-slate-700 mb-1">
                                    Jenjang
                                </label>
                                <select name="jenjang"
                                        id="jenjang"
                                        required
                                        class="w-full px-3.5 py-2.5 rounded-xl border {{ $errors->has('jenjang') ? 'border-rose-400 focus:ring-rose-200' : 'border-slate-200 focus:ring-[#2563EB]' }} text-sm bg-white focus:outline-none focus:ring-2 focus:border-transparent transition">
                                    <option value="S1" {{ old('jenjang', 'S1') === 'S1' ? 'selected' : '' }}>S1 (Sarjana)</option>
                                    <option value="D3" {{ old('jenjang') === 'D3' ? 'selected' : '' }}>D3 (Diploma)</option>
                                    <option value="S2" {{ old('jenjang') === 'S2' ? 'selected' : '' }}>S2 (Magister)</option>
                                    <option value="S3" {{ old('jenjang') === 'S3' ? 'selected' : '' }}>S3 (Doktor)</option>
                                </select>
                                @error('jenjang')
                                    <p class="text-xs text-rose-600 mt-1">{{ $message }}</p>
                                @enderror
                            </div>

                            <div>
                                <label for="phone" class="block text-xs font-semibold text-slate-700 mb-1">
                                    Nomor HP <span class="text-slate-400 font-normal">(opsional)</span>
                                </label>
                                <input type="tel"
                                       name="phone"
                                       id="phone"
                                       value="{{ old('phone') }}"
                                       placeholder="08xxxxxxxxxx"
                                       autocomplete="tel"
                                       class="w-full px-3.5 py-2.5 rounded-xl border {{ $errors->has('phone') ? 'border-rose-400 focus:ring-rose-200' : 'border-slate-200 focus:ring-[#2563EB]' }} text-sm focus:outline-none focus:ring-2 focus:border-transparent transition placeholder-slate-400">
                                @error('phone')
                                    <p class="text-xs text-rose-600 mt-1">{{ $message }}</p>
                                @enderror
                            </div>
                        </div>

                        <!-- Kata Sandi -->
                        <div>
                            <label for="password" class="block text-xs font-semibold text-slate-700 mb-1">
                                Kata sandi
                            </label>
                            <div class="relative">
                                <input type="password"
                                       name="password"
                                       id="password"
                                       placeholder="Buat kata sandi (minimal 8 karakter)"
                                       autocomplete="new-password"
                                       required
                                       class="w-full px-4 py-2.5 pr-11 rounded-xl border {{ $errors->has('password') ? 'border-rose-400 focus:ring-rose-200' : 'border-slate-200 focus:ring-[#2563EB]' }} text-sm focus:outline-none focus:ring-2 focus:border-transparent transition placeholder-slate-400">
                                <button type="button"
                                        onclick="toggleRegisterPassword('password')"
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

                        <!-- Konfirmasi Kata Sandi -->
                        <div>
                            <label for="password_confirmation" class="block text-xs font-semibold text-slate-700 mb-1">
                                Konfirmasi kata sandi
                            </label>
                            <input type="password"
                                   name="password_confirmation"
                                   id="password_confirmation"
                                   placeholder="Ulangi kata sandi"
                                   autocomplete="new-password"
                                   required
                                   class="w-full px-4 py-2.5 rounded-xl border border-slate-200 text-sm focus:outline-none focus:ring-2 focus:ring-[#2563EB] focus:border-transparent transition placeholder-slate-400">
                        </div>

                        <!-- Submit Button -->
                        <div class="pt-1.5">
                            <button type="submit"
                                    class="w-full bg-[#2563EB] hover:bg-blue-700 text-white font-semibold py-3 px-4 rounded-xl transition shadow-md shadow-blue-500/20 text-sm">
                                Daftar
                            </button>
                        </div>

                        <!-- Footer Links -->
                        <div class="text-center pt-1.5">
                            <p class="text-xs text-slate-600">
                                Sudah punya akun?
                                <a href="{{ route('login') }}" class="font-bold text-[#2563EB] hover:underline">Masuk</a>
                            </p>
                        </div>
                    </form>
                </div>
            </div>

            <!-- Right Headline Persis PDF Page 3 -->
            <div class="lg:col-span-7 order-1 lg:order-2">
                <h1 class="text-3xl sm:text-4xl lg:text-5xl font-extrabold text-white tracking-tight leading-tight mb-4">
                    Buat akun untuk mulai meminjam.
                </h1>
                <p class="text-sm sm:text-base text-slate-200/90 leading-relaxed max-w-lg">
                    Cukup beberapa data, lalu kamu bisa mengajukan peminjaman kapan saja.
                </p>
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
    function toggleRegisterPassword(inputId) {
        const input = document.getElementById(inputId);
        if (input) {
            input.type = input.type === 'password' ? 'text' : 'password';
        }
    }
</script>
@endpush
