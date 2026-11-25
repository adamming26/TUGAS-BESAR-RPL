@extends('layouts.app', ['title' => 'Panduan Penggunaan'])

@section('content')
<!-- [M2] HALAMAN PANDUAN PENGGUNAAN SIPAFKA -->
<div class="min-h-screen bg-[#F8FAFC] flex flex-col">
    <!-- Hero Header -->
    <div class="relative bg-[#0C1A40] text-white overflow-hidden">
        <div class="absolute inset-0 z-0">
            <div class="absolute inset-0 bg-gradient-to-r from-[#0C1A40]/95 via-[#0C1A40]/85 to-[#1e3a8a]/75 z-10"></div>
            <img src="https://images.unsplash.com/photo-1541339907198-e08756dedf3f?auto=format&fit=crop&w=2000&q=80"
                 alt="Gedung Kampus FMIPA UHO"
                 class="w-full h-full object-cover opacity-40">
        </div>

        @include('partials.m2-public-header', ['showAuthButtons' => true])

        <div class="relative z-20 max-w-7xl mx-auto px-6 sm:px-8 lg:px-14 pt-6 pb-16">
            <p class="text-xs font-bold tracking-[0.2em] uppercase text-blue-300 mb-2">DOKUMENTASI PENGGUNA</p>
            <h1 class="text-3xl sm:text-4xl font-extrabold tracking-tight mb-3">
                Panduan Penggunaan SIPAFKA
            </h1>
            <p class="text-sm sm:text-base text-slate-200/90 max-w-2xl leading-relaxed">
                Pelajari cara mendaftar akun, masuk sesuai peran, memulihkan kata sandi, mengelola profil, serta memahami alur peminjaman fasilitas Prodi Ilmu Komputer FMIPA UHO.
            </p>
        </div>
    </div>

    <!-- Main Guide Content -->
    <div class="flex-1 max-w-7xl mx-auto w-full px-6 sm:px-8 lg:px-14 py-12 space-y-10">
        <!-- 1. Alur Penggunaan -->
        <section class="bg-white rounded-3xl p-6 sm:p-8 shadow-sm border border-slate-100">
            <h2 class="text-xl font-bold text-slate-900 mb-1">Alur Umum Layanan</h2>
            <p class="text-xs text-slate-500 mb-6">Langkah ringkas dari pembuatan akun hingga pengembalian fasilitas.</p>

            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-5">
                <div class="p-5 rounded-2xl bg-slate-50 border border-slate-100">
                    <div class="w-9 h-9 rounded-xl bg-[#2563EB] text-white font-bold text-sm flex items-center justify-center mb-3">1</div>
                    <h3 class="text-sm font-bold text-slate-900 mb-1">Registrasi Akun</h3>
                    <p class="text-xs text-slate-600 leading-relaxed">
                        Mahasiswa mendaftar melalui halaman Daftar dengan mengisi Nama lengkap, NIM, Email, Jenjang, Nomor HP, dan Kata sandi.
                    </p>
                </div>
                <div class="p-5 rounded-2xl bg-slate-50 border border-slate-100">
                    <div class="w-9 h-9 rounded-xl bg-[#2563EB] text-white font-bold text-sm flex items-center justify-center mb-3">2</div>
                    <h3 class="text-sm font-bold text-slate-900 mb-1">Masuk Sesuai Peran</h3>
                    <p class="text-xs text-slate-600 leading-relaxed">
                        Masuk menggunakan Email, NIM, atau NIP beserta kata sandi. Sistem mengarahkan otomatis ke halaman sesuai hak akses.
                    </p>
                </div>
                <div class="p-5 rounded-2xl bg-slate-50 border border-slate-100">
                    <div class="w-9 h-9 rounded-xl bg-[#2563EB] text-white font-bold text-sm flex items-center justify-center mb-3">3</div>
                    <h3 class="text-sm font-bold text-slate-900 mb-1">Pengajuan & Persetujuan</h3>
                    <p class="text-xs text-slate-600 leading-relaxed">
                        Mahasiswa mengajukan peminjaman barang atau izin lab, kemudian dosen memeriksa dan memberikan keputusan.
                    </p>
                </div>
                <div class="p-5 rounded-2xl bg-slate-50 border border-slate-100">
                    <div class="w-9 h-9 rounded-xl bg-[#2563EB] text-white font-bold text-sm flex items-center justify-center mb-3">4</div>
                    <h3 class="text-sm font-bold text-slate-900 mb-1">Serah Terima QR Code</h3>
                    <p class="text-xs text-slate-600 leading-relaxed">
                        Pengajuan yang disetujui menerbitkan QR Code untuk dipindai saat penyerahan dan pengembalian fasilitas.
                    </p>
                </div>
            </div>
        </section>

        <!-- 2. Peran Pengguna -->
        <section class="grid grid-cols-1 md:grid-cols-3 gap-6">
            <div class="bg-white rounded-3xl p-6 shadow-sm border border-slate-100">
                <span class="inline-block px-3 py-1 rounded-full text-[11px] font-bold bg-blue-50 text-[#2563EB] mb-3">MAHASISWA</span>
                <h3 class="text-base font-bold text-slate-900 mb-2">Pemohon Fasilitas</h3>
                <ul class="text-xs text-slate-600 space-y-2 list-disc list-inside leading-relaxed">
                    <li>Mendaftar akun secara mandiri (peran default: Mahasiswa).</li>
                    <li>Melihat ketersediaan barang dan laboratorium komputer.</li>
                    <li>Mengajukan peminjaman dan memantau status pengajuan.</li>
                    <li>Mengelola informasi profil dan kata sandi pribadi.</li>
                </ul>
            </div>

            <div class="bg-white rounded-3xl p-6 shadow-sm border border-slate-100">
                <span class="inline-block px-3 py-1 rounded-full text-[11px] font-bold bg-emerald-50 text-emerald-700 mb-3">DOSEN</span>
                <h3 class="text-base font-bold text-slate-900 mb-2">Pemeriksa & Penyetuju</h3>
                <ul class="text-xs text-slate-600 space-y-2 list-disc list-inside leading-relaxed">
                    <li>Masuk menggunakan NIP atau email institusi yang terdaftar.</li>
                    <li>Memeriksa detail pengajuan peminjaman mahasiswa.</li>
                    <li>Menyetujui atau menolak pengajuan serta memindai QR Code.</li>
                    <li>Mengelola profil pribadi dan keamanan akun.</li>
                </ul>
            </div>

            <div class="bg-white rounded-3xl p-6 shadow-sm border border-slate-100">
                <span class="inline-block px-3 py-1 rounded-full text-[11px] font-bold bg-slate-100 text-[#0C1A40] mb-3">ADMIN</span>
                <h3 class="text-base font-bold text-slate-900 mb-2">Pengelola Prodi</h3>
                <ul class="text-xs text-slate-600 space-y-2 list-disc list-inside leading-relaxed">
                    <li>Masuk menggunakan ID Admin atau email administrator.</li>
                    <li>Mengelola inventaris fasilitas barang dan laboratorium.</li>
                    <li>Mengelola akun pengguna dan memantau seluruh transaksi.</li>
                    <li>Mengelola profil administrator secara mandiri.</li>
                </ul>
            </div>
        </section>

        <!-- 3. Bantuan Akun & Profil -->
        <section class="bg-white rounded-3xl p-6 sm:p-8 shadow-sm border border-slate-100">
            <h2 class="text-xl font-bold text-slate-900 mb-1">Bantuan Akun & Keamanan</h2>
            <p class="text-xs text-slate-500 mb-6">Pertanyaan yang sering muncul terkait autentikasi dan profil pengguna.</p>

            <div class="grid grid-cols-1 md:grid-cols-2 gap-5">
                <div class="p-5 rounded-2xl border border-slate-100 bg-slate-50/70">
                    <h3 class="text-sm font-bold text-slate-900 mb-1.5">Lupa kata sandi akun?</h3>
                    <p class="text-xs text-slate-600 leading-relaxed mb-3">
                        Gunakan fitur Lupa Kata Sandi dengan memasukkan email yang terdaftar. Tautan reset bertoken akan dikirimkan agar Anda dapat membuat kata sandi baru secara aman.
                    </p>
                    @guest
                        <a href="{{ route('password.request') }}" class="text-xs font-semibold text-[#2563EB] hover:underline">
                            Buka halaman Lupa Kata Sandi &rarr;
                        </a>
                    @endguest
                </div>

                <div class="p-5 rounded-2xl border border-slate-100 bg-slate-50/70">
                    <h3 class="text-sm font-bold text-slate-900 mb-1.5">Ingin memperbarui nama, email, atau nomor HP?</h3>
                    <p class="text-xs text-slate-600 leading-relaxed mb-3">
                        Setelah masuk, buka halaman Profil Saya lalu pilih Edit Profil. Anda hanya dapat memperbarui informasi milik akun Anda sendiri, sementara NIM/NIP dan peran dikelola oleh Admin.
                    </p>
                    @auth
                        <a href="{{ route('profile.index') }}" class="text-xs font-semibold text-[#2563EB] hover:underline">
                            Buka Profil Saya &rarr;
                        </a>
                    @else
                        <a href="{{ route('login') }}" class="text-xs font-semibold text-[#2563EB] hover:underline">
                            Masuk ke Akun &rarr;
                        </a>
                    @endauth
                </div>
            </div>
        </section>
    </div>

    <!-- Footer -->
    <footer class="bg-white border-t border-slate-200 px-6 sm:px-8 lg:px-14 py-6">
        <div class="max-w-7xl mx-auto flex flex-col sm:flex-row items-center justify-between gap-3 text-xs text-slate-500">
            <p>&copy; {{ date('Y') }} SIPAFKA — Program Studi Ilmu Komputer FMIPA Universitas Halu Oleo.</p>
            <div class="flex items-center space-x-5">
                <a href="{{ route('home') }}" class="hover:text-[#2563EB] transition">Beranda</a>
                <a href="{{ route('fasilitas.index') }}" class="hover:text-[#2563EB] transition">Fasilitas</a>
                @guest
                    <a href="{{ route('login') }}" class="hover:text-[#2563EB] transition">Masuk</a>
                    <a href="{{ route('register') }}" class="hover:text-[#2563EB] transition">Daftar</a>
                @else
                    <a href="{{ route('profile.index') }}" class="hover:text-[#2563EB] transition">Profil</a>
                @endguest
            </div>
        </div>
    </footer>
</div>
@endsection
