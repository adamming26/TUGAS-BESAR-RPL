@extends('layouts.dashboard', ['title' => 'Edit Profil'])

@section('content')
@php
    $identityLabel = match($user->role) {
        'admin' => 'ID Admin',
        'dosen' => 'NIP',
        default => 'NIM',
    };
@endphp

<!-- [M2] HALAMAN EDIT PROFIL & PERUBAHAN KATA SANDI -->
<div class="space-y-6 max-w-5xl">
    <!-- Page Header -->
    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4">
        <div>
            <h1 class="text-2xl font-extrabold text-slate-900 tracking-tight">Edit Profil & Keamanan</h1>
            <p class="text-xs sm:text-sm text-slate-500 mt-0.5">
                Perbarui informasi kontak atau ganti kata sandi akun Anda secara aman.
            </p>
        </div>

        <a href="{{ route('profile.index') }}"
           class="inline-flex items-center gap-2 px-4 py-2.5 rounded-xl border border-slate-200 bg-white hover:bg-slate-50 text-slate-700 text-xs sm:text-sm font-semibold transition w-fit">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"></path>
            </svg>
            <span>Kembali ke Profil</span>
        </a>
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
        <!-- Card 1: Form Edit Informasi Profil -->
        <div class="bg-white rounded-3xl p-6 sm:p-8 shadow-sm border border-slate-100 space-y-5">
            <div class="pb-4 border-b border-slate-100">
                <h2 class="text-base font-bold text-slate-900">Informasi Akun</h2>
                <p class="text-xs text-slate-500 mt-0.5">Perubahan hanya berlaku untuk akun yang sedang login.</p>
            </div>

            <form method="POST" action="{{ route('profile.update') }}" class="space-y-4">
                @csrf
                @method('PUT')

                <div>
                    <label for="name" class="block text-xs font-semibold text-slate-700 mb-1.5">
                        Nama lengkap <span class="text-rose-500">*</span>
                    </label>
                    <input type="text"
                           name="name"
                           id="name"
                           value="{{ old('name', $user->name) }}"
                           required
                           class="w-full px-4 py-2.5 rounded-xl border {{ $errors->has('name') ? 'border-rose-400 focus:ring-rose-200' : 'border-slate-200 focus:ring-[#2563EB]' }} text-sm focus:outline-none focus:ring-2 transition">
                    @error('name')
                        <p class="text-xs text-rose-600 mt-1">{{ $message }}</p>
                    @enderror
                </div>

                <div>
                    <label for="email" class="block text-xs font-semibold text-slate-700 mb-1.5">
                        Alamat Email <span class="text-rose-500">*</span>
                    </label>
                    <input type="email"
                           name="email"
                           id="email"
                           value="{{ old('email', $user->email) }}"
                           required
                           class="w-full px-4 py-2.5 rounded-xl border {{ $errors->has('email') ? 'border-rose-400 focus:ring-rose-200' : 'border-slate-200 focus:ring-[#2563EB]' }} text-sm focus:outline-none focus:ring-2 transition">
                    @error('email')
                        <p class="text-xs text-rose-600 mt-1">{{ $message }}</p>
                    @enderror
                </div>

                <div>
                    <label for="phone" class="block text-xs font-semibold text-slate-700 mb-1.5">
                        Nomor HP / WhatsApp
                    </label>
                    <input type="tel"
                           name="phone"
                           id="phone"
                           value="{{ old('phone', $user->phone) }}"
                           placeholder="08xxxxxxxxxx"
                           class="w-full px-4 py-2.5 rounded-xl border {{ $errors->has('phone') ? 'border-rose-400 focus:ring-rose-200' : 'border-slate-200 focus:ring-[#2563EB]' }} text-sm focus:outline-none focus:ring-2 transition">
                    @error('phone')
                        <p class="text-xs text-rose-600 mt-1">{{ $message }}</p>
                    @enderror
                </div>

                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4 pt-1">
                    <div>
                        <label class="block text-xs font-semibold text-slate-500 mb-1.5">{{ $identityLabel }}</label>
                        <input type="text"
                               value="{{ $user->nim_nip ?: '-' }}"
                               readonly
                               disabled
                               class="w-full px-4 py-2.5 rounded-xl border border-slate-200 bg-slate-50 text-slate-400 text-xs font-mono cursor-not-allowed">
                    </div>

                    <div>
                        <label class="block text-xs font-semibold text-slate-500 mb-1.5">Peran Akun</label>
                        <input type="text"
                               value="{{ ucfirst($user->role) }}"
                               readonly
                               disabled
                               class="w-full px-4 py-2.5 rounded-xl border border-slate-200 bg-slate-50 text-slate-400 text-xs cursor-not-allowed">
                    </div>
                </div>
                <p class="text-[11px] text-slate-400">
                    {{ $identityLabel }} dan peran akun ditetapkan oleh Administrator dan tidak dapat diubah mandiri.
                </p>

                <div class="pt-2 flex items-center gap-3">
                    <button type="submit"
                            class="px-6 py-2.5 rounded-xl bg-[#2563EB] hover:bg-blue-700 text-white font-semibold text-xs sm:text-sm transition shadow-sm">
                        Simpan Perubahan
                    </button>
                    <a href="{{ route('profile.index') }}"
                       class="px-5 py-2.5 rounded-xl border border-slate-200 text-slate-600 hover:bg-slate-50 font-semibold text-xs sm:text-sm transition">
                        Batal
                    </a>
                </div>
            </form>
        </div>

        <!-- Card 2: Form Ubah Kata Sandi -->
        <div id="keamanan-sandi" class="bg-white rounded-3xl p-6 sm:p-8 shadow-sm border border-slate-100 space-y-5">
            <div class="pb-4 border-b border-slate-100">
                <h2 class="text-base font-bold text-slate-900">Ubah Kata Sandi</h2>
                <p class="text-xs text-slate-500 mt-0.5">Gunakan kata sandi baru minimal 8 karakter untuk menjaga keamanan akun.</p>
            </div>

            <form method="POST" action="{{ route('profile.password') }}" class="space-y-4">
                @csrf
                @method('PUT')

                <div>
                    <label for="current_password" class="block text-xs font-semibold text-slate-700 mb-1.5">
                        Kata sandi saat ini <span class="text-rose-500">*</span>
                    </label>
                    <input type="password"
                           name="current_password"
                           id="current_password"
                           autocomplete="current-password"
                           required
                           class="w-full px-4 py-2.5 rounded-xl border {{ $errors->has('current_password') ? 'border-rose-400 focus:ring-rose-200' : 'border-slate-200 focus:ring-[#2563EB]' }} text-sm focus:outline-none focus:ring-2 transition">
                    @error('current_password')
                        <p class="text-xs text-rose-600 mt-1">{{ $message }}</p>
                    @enderror
                </div>

                <div>
                    <label for="password" class="block text-xs font-semibold text-slate-700 mb-1.5">
                        Kata sandi baru <span class="text-rose-500">*</span>
                    </label>
                    <input type="password"
                           name="password"
                           id="password"
                           placeholder="Minimal 8 karakter"
                           autocomplete="new-password"
                           required
                           class="w-full px-4 py-2.5 rounded-xl border {{ $errors->has('password') ? 'border-rose-400 focus:ring-rose-200' : 'border-slate-200 focus:ring-[#2563EB]' }} text-sm focus:outline-none focus:ring-2 transition">
                    @error('password')
                        <p class="text-xs text-rose-600 mt-1">{{ $message }}</p>
                    @enderror
                </div>

                <div>
                    <label for="password_confirmation" class="block text-xs font-semibold text-slate-700 mb-1.5">
                        Konfirmasi kata sandi baru <span class="text-rose-500">*</span>
                    </label>
                    <input type="password"
                           name="password_confirmation"
                           id="password_confirmation"
                           placeholder="Ulangi kata sandi baru"
                           autocomplete="new-password"
                           required
                           class="w-full px-4 py-2.5 rounded-xl border border-slate-200 text-sm focus:outline-none focus:ring-2 focus:ring-[#2563EB] transition">
                </div>

                <div class="pt-2">
                    <button type="submit"
                            class="px-6 py-2.5 rounded-xl bg-[#0C1A40] hover:bg-navy-800 text-white font-semibold text-xs sm:text-sm transition shadow-sm">
                        Perbarui Kata Sandi
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection
