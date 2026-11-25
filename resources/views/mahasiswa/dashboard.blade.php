@extends('layouts.dashboard', ['title' => 'Dashboard Mahasiswa'])

@section('content')
<!-- [MARKER M4] : DASHBOARD MAHASISWA (Bab 3.7 Halaman 1) -->
<div class="space-y-6">
    <!-- Header Title & Aksi Cepat -->
    <div class="flex flex-col md:flex-row md:items-center md:justify-between gap-4">
        <div>
            <h1 class="text-2xl font-bold text-[#0C1A40]">Dashboard Mahasiswa</h1>
            <p class="text-xs sm:text-sm text-slate-500 mt-0.5">
                Pantau ringkasan peminjaman, ajukan peminjaman barang atau laboratorium, dan cek ketersediaan fasilitas.
            </p>
        </div>

        <div class="flex flex-wrap items-center gap-2.5">
            <a href="{{ route('fasilitas.index') }}"
               class="px-4 py-2.5 rounded-xl border border-slate-200 bg-white hover:bg-slate-50 text-[#0C1A40] font-semibold text-xs transition shadow-sm">
                Katalog Fasilitas
            </a>
            <a href="{{ route('mahasiswa.form-barang') }}"
               class="px-4 py-2.5 rounded-xl bg-[#2563EB] hover:bg-blue-700 text-white font-semibold text-xs transition shadow-sm">
                + Pinjam Barang
            </a>
            <a href="{{ route('mahasiswa.form-lab') }}"
               class="px-4 py-2.5 rounded-xl bg-[#0C1A40] hover:bg-slate-800 text-white font-semibold text-xs transition shadow-sm">
                + Pinjam Lab
            </a>
        </div>
    </div>

    <!-- Banner Selamat Datang -->
    <div class="relative overflow-hidden rounded-3xl bg-gradient-to-r from-[#0C1A40] via-[#1e3a8a] to-[#2563EB] p-6 sm:p-8 text-white shadow-lg">
        <div class="relative z-10 flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
            <div>
                <span class="inline-block px-3 py-1 rounded-full bg-white/15 border border-white/20 text-[11px] font-semibold tracking-wide mb-2">
                    NIM: {{ $user->nim_nip ?: '-' }}
                </span>
                <h2 class="text-2xl font-bold mb-1">
                    Selamat Datang, {{ $user->name }}
                </h2>
                <p class="text-sm text-blue-100">
                    Kelola pengajuan peminjaman fasilitas Program Studi Ilmu Komputer FMIPA UHO Anda di sini.
                </p>
            </div>
            <div class="flex flex-wrap gap-2.5 shrink-0">
                <a href="{{ route('mahasiswa.peminjaman-saya') }}"
                   class="px-5 py-2.5 rounded-xl bg-white text-[#0C1A40] hover:bg-blue-50 font-bold text-xs transition shadow-sm">
                    Daftar Peminjaman Saya
                </a>
                <a href="{{ route('mahasiswa.riwayat') }}"
                   class="px-5 py-2.5 rounded-xl bg-white/15 hover:bg-white/25 border border-white/30 text-white font-semibold text-xs transition">
                    Status &amp; Riwayat
                </a>
            </div>
        </div>
    </div>

    <!-- Ringkasan Jumlah Peminjaman Berdasarkan Status -->
    <div class="grid grid-cols-2 sm:grid-cols-3 lg:grid-cols-6 gap-4">
        <div class="bg-white rounded-2xl p-4 shadow-sm border border-slate-200/80">
            <p class="text-[11px] font-bold uppercase tracking-wider text-slate-400">Total Pengajuan</p>
            <p class="text-2xl font-extrabold text-[#0C1A40] mt-1">{{ $statusSummary['total'] }}</p>
            <p class="text-[11px] text-slate-500 mt-1">Semua pengajuan</p>
        </div>

        <div class="bg-white rounded-2xl p-4 shadow-sm border border-amber-200/80">
            <p class="text-[11px] font-bold uppercase tracking-wider text-[#D97706]">Menunggu</p>
            <p class="text-2xl font-extrabold text-[#D97706] mt-1">{{ $statusSummary['menunggu'] }}</p>
            <p class="text-[11px] text-slate-500 mt-1">Tinjauan Dosen</p>
        </div>

        <div class="bg-white rounded-2xl p-4 shadow-sm border border-emerald-200/80">
            <p class="text-[11px] font-bold uppercase tracking-wider text-[#059669]">Disetujui</p>
            <p class="text-2xl font-extrabold text-[#059669] mt-1">{{ $statusSummary['disetujui'] }}</p>
            <p class="text-[11px] text-slate-500 mt-1">Siap serah terima</p>
        </div>

        <div class="bg-white rounded-2xl p-4 shadow-sm border border-blue-200/80">
            <p class="text-[11px] font-bold uppercase tracking-wider text-[#2563EB]">Dipinjam</p>
            <p class="text-2xl font-extrabold text-[#2563EB] mt-1">{{ $statusSummary['dipinjam'] }}</p>
            <p class="text-[11px] text-slate-500 mt-1">Sedang digunakan</p>
        </div>

        <div class="bg-white rounded-2xl p-4 shadow-sm border border-teal-200/80">
            <p class="text-[11px] font-bold uppercase tracking-wider text-[#059669]">Selesai</p>
            <p class="text-2xl font-extrabold text-[#059669] mt-1">{{ $statusSummary['selesai'] }}</p>
            <p class="text-[11px] text-slate-500 mt-1">Sudah dikembalikan</p>
        </div>

        <div class="bg-white rounded-2xl p-4 shadow-sm border border-rose-200/80">
            <p class="text-[11px] font-bold uppercase tracking-wider text-[#DC2626]">Ditolak</p>
            <p class="text-2xl font-extrabold text-[#DC2626] mt-1">{{ $statusSummary['ditolak'] }}</p>
            <p class="text-[11px] text-slate-500 mt-1">Tidak disetujui</p>
        </div>
    </div>

    <!-- Main Grid: Ketersediaan Fasilitas (Kiri) & Peminjaman Terbaru + Akses Cepat (Kanan) -->
    <div class="grid grid-cols-1 lg:grid-cols-12 gap-6 items-start">
        <!-- Kolom Kiri: Ketersediaan Fasilitas (7 Cols) -->
        <div class="lg:col-span-7 bg-white rounded-3xl p-6 shadow-sm border border-slate-200/80">
            <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-2 mb-4">
                <div>
                    <h3 class="text-base font-bold text-[#0C1A40]">Ketersediaan Fasilitas</h3>
                    <p class="text-xs text-slate-500">Pilih fasilitas barang atau lab untuk langsung mengajukan peminjaman.</p>
                </div>
                <a href="{{ route('fasilitas.index') }}" class="text-xs font-semibold text-[#2563EB] hover:underline shrink-0">
                    Buka Katalog Lengkap &rarr;
                </a>
            </div>

            <!-- Filter Tabs & Pencarian -->
            <div class="flex flex-col sm:flex-row items-center justify-between gap-3 mb-5">
                <div class="flex items-center space-x-1 bg-slate-100 p-1 rounded-2xl w-full sm:w-auto">
                    <a href="{{ route('mahasiswa.dashboard') }}"
                       class="px-4 py-1.5 rounded-xl text-xs font-semibold transition {{ !request('type') ? 'bg-[#2563EB] text-white shadow-sm' : 'text-slate-600 hover:text-slate-900' }}">
                        Semua
                    </a>
                    <a href="{{ route('mahasiswa.dashboard', ['type' => 'barang']) }}"
                       class="px-4 py-1.5 rounded-xl text-xs font-semibold transition {{ request('type') === 'barang' ? 'bg-[#2563EB] text-white shadow-sm' : 'text-slate-600 hover:text-slate-900' }}">
                        Barang
                    </a>
                    <a href="{{ route('mahasiswa.dashboard', ['type' => 'ruangan']) }}"
                       class="px-4 py-1.5 rounded-xl text-xs font-semibold transition {{ request('type') === 'ruangan' ? 'bg-[#2563EB] text-white shadow-sm' : 'text-slate-600 hover:text-slate-900' }}">
                        Laboratorium
                    </a>
                </div>

                <form method="GET" action="{{ route('mahasiswa.dashboard') }}" class="w-full sm:w-60">
                    @if(request('type'))
                        <input type="hidden" name="type" value="{{ request('type') }}">
                    @endif
                    <div class="relative">
                        <input type="text"
                               name="search"
                               value="{{ request('search') }}"
                               placeholder="Cari fasilitas..."
                               class="w-full pl-3 pr-8 py-2 rounded-xl border border-slate-200 text-xs focus:outline-none focus:ring-2 focus:ring-[#2563EB] focus:border-transparent">
                        @if(request('search'))
                            <a href="{{ route('mahasiswa.dashboard', request()->only('type')) }}" class="absolute right-2.5 top-2 text-slate-400 hover:text-slate-600 text-xs">&times;</a>
                        @endif
                    </div>
                </form>
            </div>

            <!-- Tabel Fasilitas -->
            <div class="overflow-x-auto">
                <table class="w-full text-left border-collapse">
                    <thead>
                        <tr class="border-b border-slate-100 text-[11px] font-semibold text-slate-400 uppercase tracking-wider">
                            <th class="py-3 px-3">Fasilitas</th>
                            <th class="py-3 px-3">Jenis</th>
                            <th class="py-3 px-3">Ketersediaan</th>
                            <th class="py-3 px-3 text-center">Status</th>
                            <th class="py-3 px-3 text-right">Aksi</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-50 text-xs text-slate-700">
                        @forelse($facilities as $item)
                            <tr class="hover:bg-slate-50/80 transition">
                                <td class="py-3.5 px-3">
                                    <div class="font-semibold text-slate-900">{{ $item->name }}</div>
                                    <div class="text-[10px] font-mono text-slate-400">{{ $item->code }}</div>
                                </td>
                                <td class="py-3.5 px-3 text-slate-500">
                                    {{ $item->type === 'ruangan' ? 'Laboratorium' : 'Barang' }}
                                </td>
                                <td class="py-3.5 px-3">
                                    @if($item->type === 'barang')
                                        <span class="font-semibold text-slate-800">{{ $item->available_stock }}</span> / <span class="text-slate-400">{{ $item->total_stock }} unit</span>
                                    @else
                                        Kapasitas <span class="font-semibold text-slate-800">{{ $item->capacity }} orang</span>
                                    @endif
                                </td>
                                <td class="py-3.5 px-3 text-center">
                                    @if($item->type === 'barang')
                                        @if($item->available_stock > 1)
                                            <span class="inline-block px-2.5 py-1 rounded-full text-[10px] font-semibold bg-emerald-50 text-[#059669] border border-emerald-200">
                                                Tersedia
                                            </span>
                                        @elseif($item->available_stock === 1)
                                            <span class="inline-block px-2.5 py-1 rounded-full text-[10px] font-semibold bg-amber-50 text-[#D97706] border border-amber-200">
                                                Terbatas
                                            </span>
                                        @else
                                            <span class="inline-block px-2.5 py-1 rounded-full text-[10px] font-semibold bg-rose-50 text-[#DC2626] border border-rose-200">
                                                Habis
                                            </span>
                                        @endif
                                    @else
                                        <span class="inline-block px-2.5 py-1 rounded-full text-[10px] font-semibold bg-blue-50 text-[#2563EB] border border-blue-200">
                                            Cek Jadwal
                                        </span>
                                    @endif
                                </td>
                                <td class="py-3.5 px-3 text-right">
                                    @if($item->type === 'barang')
                                        @if($item->available_stock > 0)
                                            <a href="{{ route('mahasiswa.form-barang', $item->id) }}"
                                               class="inline-block px-3.5 py-1.5 rounded-xl border border-[#2563EB] text-[#2563EB] hover:bg-[#2563EB] hover:text-white transition font-semibold text-xs">
                                                Pinjam
                                            </a>
                                        @else
                                            <span class="text-slate-400 text-xs italic">Stok habis</span>
                                        @endif
                                    @else
                                        <a href="{{ route('mahasiswa.form-lab', $item->id) }}"
                                           class="inline-block px-3.5 py-1.5 rounded-xl border border-[#2563EB] text-[#2563EB] hover:bg-[#2563EB] hover:text-white transition font-semibold text-xs">
                                            Ajukan Lab
                                        </a>
                                    @endif
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="5" class="py-8 text-center text-slate-400">
                                    Belum ada fasilitas aktif yang sesuai dengan filter.
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>

        <!-- Kolom Kanan: Peminjaman Terbaru & Akses Cepat (5 Cols) -->
        <div class="lg:col-span-5 space-y-6">
            <!-- Widget Peminjaman Terbaru -->
            <div class="bg-white rounded-3xl p-6 shadow-sm border border-slate-200/80">
                <div class="flex items-center justify-between mb-4">
                    <div>
                        <h3 class="text-base font-bold text-[#0C1A40]">Peminjaman Terbaru</h3>
                        <p class="text-[11px] text-slate-500">Pengajuan terakhir milik Anda</p>
                    </div>
                    <a href="{{ route('mahasiswa.peminjaman-saya') }}" class="text-xs text-[#2563EB] hover:underline font-semibold">
                        Lihat Semua
                    </a>
                </div>

                <div class="space-y-3">
                    @forelse($recentRequests as $req)
                        <a href="{{ route('mahasiswa.detail-peminjaman', $req->id) }}"
                           class="block p-3.5 rounded-2xl bg-slate-50 hover:bg-blue-50/40 border border-slate-100 hover:border-blue-200 transition">
                            <div class="flex items-start justify-between gap-3">
                                <div class="min-w-0">
                                    <p class="text-xs font-bold text-slate-900 truncate">
                                        {{ $req->facility ? $req->facility->name : 'Fasilitas' }}
                                    </p>
                                    <p class="text-[10px] font-mono text-slate-400 mt-0.5">{{ $req->code }}</p>
                                    <p class="text-[11px] text-slate-500 mt-1">
                                        {{ \Carbon\Carbon::parse($req->borrow_date)->isoFormat('D MMM Y') }}
                                        &middot; {{ substr($req->start_time, 0, 5) }} - {{ substr($req->end_time, 0, 5) }}
                                    </p>
                                </div>
                                <div class="shrink-0">
                                    <x-badge :status="$req->status">
                                        @if($req->status === 'menunggu')
                                            Menunggu
                                        @elseif($req->status === 'disetujui')
                                            Disetujui
                                        @elseif($req->status === 'dipinjam')
                                            Dipinjam
                                        @elseif($req->status === 'ditolak')
                                            Ditolak
                                        @elseif($req->status === 'dikembalikan')
                                            Dikembalikan
                                        @else
                                            {{ ucfirst($req->status) }}
                                        @endif
                                    </x-badge>
                                </div>
                            </div>
                        </a>
                    @empty
                        <div class="py-6 text-center">
                            <p class="text-xs text-slate-400 mb-3">Anda belum memiliki pengajuan peminjaman.</p>
                            <a href="{{ route('mahasiswa.form-barang') }}"
                               class="inline-block px-4 py-2 rounded-xl bg-[#2563EB] text-white text-xs font-semibold hover:bg-blue-700 transition">
                                Buat Pengajuan Pertama
                            </a>
                        </div>
                    @endforelse
                </div>
            </div>

            <!-- Widget Akses Cepat Layanan Mahasiswa -->
            <div class="bg-white rounded-3xl p-6 shadow-sm border border-slate-200/80 space-y-3">
                <h3 class="text-base font-bold text-[#0C1A40]">Akses Layanan</h3>
                <p class="text-xs text-slate-500">Navigasi cepat menuju fitur peminjaman dan katalog fasilitas.</p>

                <div class="grid grid-cols-1 sm:grid-cols-2 gap-3 pt-1">
                    <a href="{{ route('mahasiswa.form-barang') }}"
                       class="p-3.5 rounded-2xl border border-slate-200 hover:border-[#2563EB] hover:bg-blue-50/40 transition">
                        <p class="text-xs font-bold text-[#0C1A40]">Form Peminjaman Barang</p>
                        <p class="text-[11px] text-slate-500 mt-0.5">Proyektor, kabel HDMI, terminal</p>
                    </a>
                    <a href="{{ route('mahasiswa.form-lab') }}"
                       class="p-3.5 rounded-2xl border border-slate-200 hover:border-[#2563EB] hover:bg-blue-50/40 transition">
                        <p class="text-xs font-bold text-[#0C1A40]">Form Peminjaman Lab</p>
                        <p class="text-[11px] text-slate-500 mt-0.5">Cek kapasitas &amp; bentrok jadwal</p>
                    </a>
                    <a href="{{ route('mahasiswa.peminjaman-saya') }}"
                       class="p-3.5 rounded-2xl border border-slate-200 hover:border-[#2563EB] hover:bg-blue-50/40 transition">
                        <p class="text-xs font-bold text-[#0C1A40]">Daftar Peminjaman Saya</p>
                        <p class="text-[11px] text-slate-500 mt-0.5">Status &amp; detail pengajuan Anda</p>
                    </a>
                    <a href="{{ route('fasilitas.index') }}"
                       class="p-3.5 rounded-2xl border border-slate-200 hover:border-[#2563EB] hover:bg-blue-50/40 transition">
                        <p class="text-xs font-bold text-[#0C1A40]">Katalog Fasilitas M3</p>
                        <p class="text-[11px] text-slate-500 mt-0.5">Daftar lengkap inventaris prodi</p>
                    </a>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
