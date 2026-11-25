@extends('layouts.dashboard', ['title' => 'Profil Saya'])

@section('content')
@php
    $roleLabel = match($user->role) {
        'admin' => 'Administrator',
        'dosen' => 'Dosen',
        default => 'Mahasiswa',
    };

    $identityLabel = match($user->role) {
        'admin' => 'ID Admin',
        'dosen' => 'NIP',
        default => 'NIM',
    };

    $initials = collect(explode(' ', trim($user->name)))
        ->filter()
        ->take(2)
        ->map(fn ($part) => strtoupper(mb_substr($part, 0, 1)))
        ->implode('');
@endphp

<!-- [M2] HALAMAN PROFIL PENGGUNA -->
<div class="space-y-6 max-w-5xl">
    <!-- Page Header -->
    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4">
        <div>
            <h1 class="text-2xl font-extrabold text-slate-900 tracking-tight">Profil Pengguna</h1>
            <p class="text-xs sm:text-sm text-slate-500 mt-0.5">
                Informasi akun yang sedang aktif pada sistem SIPAFKA.
            </p>
        </div>

        <div class="flex flex-wrap items-center gap-2.5">
            <a href="{{ route('profile.edit') }}"
               class="inline-flex items-center gap-2 px-5 py-2.5 rounded-xl bg-[#2563EB] hover:bg-blue-700 text-white text-xs sm:text-sm font-semibold transition shadow-sm">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"></path>
                </svg>
                <span>Edit Profil</span>
            </a>
            <a href="{{ route('panduan.index') }}"
               class="inline-flex items-center gap-2 px-4 py-2.5 rounded-xl border border-slate-200 bg-white hover:bg-slate-50 text-slate-700 text-xs sm:text-sm font-semibold transition">
                <span>Panduan</span>
            </a>
        </div>
    </div>

    <!-- Hero Identity Banner -->
    <div class="relative rounded-3xl bg-[#0C1A40] text-white p-6 sm:p-8 overflow-hidden shadow-md">
        <div class="absolute inset-0 bg-gradient-to-r from-[#0C1A40] via-[#142555]/95 to-[#2563EB]/60"></div>
        <div class="relative z-10 flex flex-col sm:flex-row sm:items-center justify-between gap-6">
            <div class="flex items-center gap-4 sm:gap-5">
                <div class="w-16 h-16 sm:w-20 sm:h-20 rounded-2xl bg-white/15 border border-white/25 backdrop-blur-sm flex items-center justify-center text-2xl font-extrabold text-white shrink-0">
                    {{ $initials ?: 'U' }}
                </div>
                <div class="min-w-0">
                    <span class="inline-flex items-center px-3 py-1 rounded-full text-[11px] font-bold uppercase tracking-wider bg-blue-500/30 border border-blue-300/30 text-blue-100 mb-2">
                        {{ $roleLabel }}
                    </span>
                    <h2 class="text-xl sm:text-2xl font-extrabold tracking-tight truncate">{{ $user->name }}</h2>
                    <p class="text-xs sm:text-sm text-slate-200/85 truncate">{{ $user->email }}</p>
                </div>
            </div>

            <div class="flex items-center gap-3 shrink-0">
                <a href="{{ route('profile.edit') }}#keamanan-sandi"
                   class="px-4 py-2.5 rounded-xl bg-white/15 hover:bg-white/25 border border-white/25 text-white text-xs font-semibold transition">
                    Ubah Kata Sandi
                </a>
                <form method="POST" action="{{ route('logout') }}">
                    @csrf
                    <button type="submit"
                            class="px-4 py-2.5 rounded-xl bg-rose-500/20 hover:bg-rose-500/30 border border-rose-300/30 text-rose-100 text-xs font-semibold transition">
                        Keluar
                    </button>
                </form>
            </div>
        </div>
    </div>

    <!-- Detail Informasi Akun -->
    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
        <div class="lg:col-span-2 bg-white rounded-3xl p-6 sm:p-8 shadow-sm border border-slate-100">
            <div class="flex items-center justify-between pb-4 mb-5 border-b border-slate-100">
                <div>
                    <h3 class="text-base font-bold text-slate-900">Informasi Pribadi</h3>
                    <p class="text-xs text-slate-500">Data identitas yang terhubung dengan akun SIPAFKA Anda.</p>
                </div>
                <a href="{{ route('profile.edit') }}" class="text-xs font-semibold text-[#2563EB] hover:underline">
                    Ubah Data
                </a>
            </div>

            <dl class="grid grid-cols-1 sm:grid-cols-2 gap-5">
                <div class="p-4 rounded-2xl bg-slate-50 border border-slate-100">
                    <dt class="text-[11px] font-semibold uppercase tracking-wider text-slate-400 mb-1">Nama Lengkap</dt>
                    <dd class="text-sm font-semibold text-slate-900">{{ $user->name }}</dd>
                </div>

                <div class="p-4 rounded-2xl bg-slate-50 border border-slate-100">
                    <dt class="text-[11px] font-semibold uppercase tracking-wider text-slate-400 mb-1">{{ $identityLabel }}</dt>
                    <dd class="text-sm font-semibold text-slate-900 font-mono">{{ $user->nim_nip ?: '-' }}</dd>
                </div>

                <div class="p-4 rounded-2xl bg-slate-50 border border-slate-100">
                    <dt class="text-[11px] font-semibold uppercase tracking-wider text-slate-400 mb-1">Alamat Email</dt>
                    <dd class="text-sm font-semibold text-slate-900 break-all">{{ $user->email }}</dd>
                </div>

                <div class="p-4 rounded-2xl bg-slate-50 border border-slate-100">
                    <dt class="text-[11px] font-semibold uppercase tracking-wider text-slate-400 mb-1">Nomor HP / WhatsApp</dt>
                    <dd class="text-sm font-semibold text-slate-900">{{ $user->phone ?: 'Belum diisi' }}</dd>
                </div>

                <div class="p-4 rounded-2xl bg-slate-50 border border-slate-100">
                    <dt class="text-[11px] font-semibold uppercase tracking-wider text-slate-400 mb-1">Peran Akun</dt>
                    <dd class="text-sm font-semibold text-slate-900">{{ $roleLabel }}</dd>
                </div>

                <div class="p-4 rounded-2xl bg-slate-50 border border-slate-100">
                    <dt class="text-[11px] font-semibold uppercase tracking-wider text-slate-400 mb-1">Terdaftar Sejak</dt>
                    <dd class="text-sm font-semibold text-slate-900">
                        {{ $user->created_at ? $user->created_at->format('d M Y') : '-' }}
                    </dd>
                </div>
            </dl>
        </div>

        <!-- Ringkasan Akses & Navigasi Cepat -->
        <div class="bg-white rounded-3xl p-6 sm:p-8 shadow-sm border border-slate-100 flex flex-col justify-between space-y-6">
            <div>
                <h3 class="text-base font-bold text-slate-900 pb-4 mb-4 border-b border-slate-100">Status & Akses</h3>

                <div class="space-y-3 text-xs">
                    <div class="p-4 rounded-2xl bg-emerald-50 border border-emerald-100 text-emerald-800">
                        <p class="font-bold mb-0.5">Sesi Aktif & Terverifikasi</p>
                        <p class="text-emerald-700/90 leading-relaxed">
                            Anda hanya dapat mengubah data profil dan kata sandi milik akun Anda sendiri.
                        </p>
                    </div>

                    @if($user->role === 'mahasiswa' && !\Illuminate\Support\Facades\Route::has('mahasiswa.dashboard'))
                        <div class="p-4 rounded-2xl bg-amber-50 border border-amber-200 text-amber-800">
                            <p class="font-bold mb-0.5">Info Modul Dashboard Mahasiswa</p>
                            <p class="leading-relaxed">
                                Modul Dashboard Mahasiswa (M4) belum diintegrasikan pada branch ini. Anda dapat mengakses katalog fasilitas, panduan, dan pengelolaan profil.
                            </p>
                        </div>
                    @elseif($user->role === 'dosen' && !\Illuminate\Support\Facades\Route::has('dosen.dashboard'))
                        <div class="p-4 rounded-2xl bg-amber-50 border border-amber-200 text-amber-800">
                            <p class="font-bold mb-0.5">Info Modul Dashboard Dosen</p>
                            <p class="leading-relaxed">
                                Modul Dashboard Dosen (M5) belum diintegrasikan pada branch ini. Anda dapat mengakses katalog fasilitas, panduan, dan pengelolaan profil.
                            </p>
                        </div>
                    @endif
                </div>
            </div>

            <div class="space-y-2 pt-2 border-t border-slate-100">
                <a href="{{ route('home') }}"
                   class="block w-full text-center px-4 py-2.5 rounded-xl border border-slate-200 text-slate-700 hover:bg-slate-50 text-xs font-semibold transition">
                    Kembali ke Beranda
                </a>
                <a href="{{ route('fasilitas.index') }}"
                   class="block w-full text-center px-4 py-2.5 rounded-xl border border-slate-200 text-slate-700 hover:bg-slate-50 text-xs font-semibold transition">
                    Lihat Katalog Fasilitas
                </a>
                @if($user->role === 'admin' && \Illuminate\Support\Facades\Route::has('admin.dashboard'))
                    <a href="{{ route('admin.dashboard') }}"
                       class="block w-full text-center px-4 py-2.5 rounded-xl bg-[#0C1A40] hover:bg-navy-800 text-white text-xs font-semibold transition">
                        Ke Dashboard Admin
                    </a>
                @endif
            </div>
        </div>
    </div>
</div>
@endsection
