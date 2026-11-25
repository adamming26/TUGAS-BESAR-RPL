@extends('layouts.dashboard', ['title' => 'Peminjaman Saya'])

@section('content')
<!-- [MARKER M4] : DAFTAR PEMINJAMAN SAYA (MAHASISWA) -->
<div class="space-y-6">
    <!-- Header Title & Actions -->
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
        <div>
            <h1 class="text-2xl font-bold text-[#0C1A40]">Daftar Peminjaman Saya</h1>
            <p class="text-xs sm:text-sm text-slate-500 mt-0.5">
                Seluruh pengajuan peminjaman barang dan laboratorium milik Anda beserta status terkininya.
            </p>
        </div>

        <div class="flex flex-wrap items-center gap-2">
            <a href="{{ route('mahasiswa.form-barang') }}"
               class="px-4 py-2 rounded-xl bg-[#2563EB] hover:bg-blue-700 text-white font-semibold text-xs transition shadow-sm">
                + Pinjam Barang
            </a>
            <a href="{{ route('mahasiswa.form-lab') }}"
               class="px-4 py-2 rounded-xl bg-[#0C1A40] hover:bg-slate-800 text-white font-semibold text-xs transition shadow-sm">
                + Pinjam Lab
            </a>
        </div>
    </div>

    <!-- Main Grid -->
    <div class="grid grid-cols-1 lg:grid-cols-12 gap-6 items-start">
        <!-- Kolom Kiri: Daftar Peminjaman (9 Cols) -->
        <div class="lg:col-span-9 bg-white rounded-3xl p-6 shadow-sm border border-slate-200/80">
            <!-- Filter Status Tabs & Pencarian -->
            <div class="flex flex-col gap-3 mb-5">
                <div class="flex flex-wrap items-center gap-1.5 bg-slate-100 p-1.5 rounded-2xl">
                    <a href="{{ route('mahasiswa.peminjaman-saya', request()->only('type', 'search')) }}"
                       class="px-3.5 py-1.5 rounded-xl text-xs font-semibold transition {{ !request('status') ? 'bg-[#2563EB] text-white shadow-sm' : 'text-slate-600 hover:text-slate-900' }}">
                        Semua ({{ $statusCounts['semua'] }})
                    </a>
                    <a href="{{ route('mahasiswa.peminjaman-saya', array_merge(request()->only('type', 'search'), ['status' => 'menunggu'])) }}"
                       class="px-3.5 py-1.5 rounded-xl text-xs font-semibold transition {{ request('status') === 'menunggu' ? 'bg-[#D97706] text-white shadow-sm' : 'text-slate-600 hover:text-slate-900' }}">
                        Menunggu ({{ $statusCounts['menunggu'] }})
                    </a>
                    <a href="{{ route('mahasiswa.peminjaman-saya', array_merge(request()->only('type', 'search'), ['status' => 'disetujui'])) }}"
                       class="px-3.5 py-1.5 rounded-xl text-xs font-semibold transition {{ request('status') === 'disetujui' ? 'bg-[#059669] text-white shadow-sm' : 'text-slate-600 hover:text-slate-900' }}">
                        Disetujui ({{ $statusCounts['disetujui'] }})
                    </a>
                    <a href="{{ route('mahasiswa.peminjaman-saya', array_merge(request()->only('type', 'search'), ['status' => 'dipinjam'])) }}"
                       class="px-3.5 py-1.5 rounded-xl text-xs font-semibold transition {{ request('status') === 'dipinjam' ? 'bg-[#2563EB] text-white shadow-sm' : 'text-slate-600 hover:text-slate-900' }}">
                        Dipinjam ({{ $statusCounts['dipinjam'] }})
                    </a>
                    <a href="{{ route('mahasiswa.peminjaman-saya', array_merge(request()->only('type', 'search'), ['status' => 'dikembalikan'])) }}"
                       class="px-3.5 py-1.5 rounded-xl text-xs font-semibold transition {{ request('status') === 'dikembalikan' ? 'bg-[#059669] text-white shadow-sm' : 'text-slate-600 hover:text-slate-900' }}">
                        Selesai ({{ $statusCounts['dikembalikan'] }})
                    </a>
                    <a href="{{ route('mahasiswa.peminjaman-saya', array_merge(request()->only('type', 'search'), ['status' => 'ditolak'])) }}"
                       class="px-3.5 py-1.5 rounded-xl text-xs font-semibold transition {{ request('status') === 'ditolak' ? 'bg-[#DC2626] text-white shadow-sm' : 'text-slate-600 hover:text-slate-900' }}">
                        Ditolak ({{ $statusCounts['ditolak'] }})
                    </a>
                </div>

                <form method="GET" action="{{ route('mahasiswa.peminjaman-saya') }}" class="flex flex-col sm:flex-row gap-3">
                    @if(request('status'))
                        <input type="hidden" name="status" value="{{ request('status') }}">
                    @endif
                    <select name="type" onchange="this.form.submit()"
                            class="px-3.5 py-2 rounded-xl border border-slate-200 text-xs bg-white focus:outline-none focus:ring-2 focus:ring-[#2563EB]">
                        <option value="">Semua Jenis</option>
                        <option value="barang" {{ request('type') === 'barang' ? 'selected' : '' }}>Barang</option>
                        <option value="ruangan" {{ request('type') === 'ruangan' ? 'selected' : '' }}>Laboratorium</option>
                    </select>
                    <div class="relative flex-1">
                        <input type="text"
                               name="search"
                               value="{{ request('search') }}"
                               placeholder="Cari kode pengajuan, fasilitas, atau tujuan..."
                               class="w-full pl-3.5 pr-20 py-2 rounded-xl border border-slate-200 text-xs focus:outline-none focus:ring-2 focus:ring-[#2563EB]">
                        <button type="submit"
                                class="absolute right-1.5 top-1 px-3 py-1 rounded-lg bg-[#2563EB] text-white text-[11px] font-semibold hover:bg-blue-700 transition">
                            Cari
                        </button>
                    </div>
                </form>
            </div>

            <!-- Tabel Peminjaman -->
            <div class="overflow-x-auto">
                <table class="w-full text-left border-collapse">
                    <thead>
                        <tr class="border-b border-slate-100 text-[11px] font-semibold text-slate-400 uppercase tracking-wider">
                            <th class="py-3 px-3">Kode &amp; Tgl Pengajuan</th>
                            <th class="py-3 px-3">Fasilitas</th>
                            <th class="py-3 px-3">Jadwal Pakai</th>
                            <th class="py-3 px-3 text-center">Status</th>
                            <th class="py-3 px-3 text-right">Aksi</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-50 text-xs text-slate-700">
                        @forelse($requests as $req)
                            <tr class="hover:bg-slate-50/80 transition">
                                <td class="py-3.5 px-3">
                                    <div class="font-mono font-bold text-[#0C1A40]">{{ $req->code }}</div>
                                    <div class="text-[11px] text-slate-400 mt-0.5">
                                        Diajukan {{ $req->created_at ? $req->created_at->isoFormat('D MMM Y, HH:mm') : '-' }}
                                    </div>
                                </td>
                                <td class="py-3.5 px-3">
                                    <div class="font-semibold text-slate-900">
                                        {{ $req->facility ? $req->facility->name : 'Fasilitas' }}
                                    </div>
                                    <div class="text-[11px] text-slate-500 mt-0.5">
                                        {{ $req->type === 'ruangan' ? 'Laboratorium' : 'Barang' }}
                                        &middot;
                                        @if($req->type === 'ruangan')
                                            {{ $req->participant_count ?? 1 }} peserta
                                        @else
                                            {{ $req->items->first()?->quantity ?? 1 }} unit
                                        @endif
                                    </div>
                                </td>
                                <td class="py-3.5 px-3 text-slate-600">
                                    <div class="font-medium">{{ \Carbon\Carbon::parse($req->borrow_date)->isoFormat('D MMM Y') }}</div>
                                    <div class="text-[11px] text-slate-400">
                                        {{ substr($req->start_time, 0, 5) }} - {{ substr($req->end_time, 0, 5) }} WITA
                                    </div>
                                </td>
                                <td class="py-3.5 px-3 text-center">
                                    <x-badge :status="$req->status">
                                        @if($req->status === 'menunggu')
                                            Menunggu Dosen
                                        @elseif($req->status === 'disetujui')
                                            Disetujui
                                        @elseif($req->status === 'dipinjam')
                                            Dipinjam
                                        @elseif($req->status === 'dikembalikan')
                                            Dikembalikan
                                        @elseif($req->status === 'ditolak')
                                            Ditolak
                                        @else
                                            {{ ucfirst($req->status) }}
                                        @endif
                                    </x-badge>
                                </td>
                                <td class="py-3.5 px-3 text-right">
                                    <a href="{{ route('mahasiswa.detail-peminjaman', $req->id) }}"
                                       class="inline-block px-3.5 py-1.5 rounded-xl border border-[#2563EB] text-[#2563EB] hover:bg-[#2563EB] hover:text-white font-semibold text-xs transition">
                                        Detail
                                    </a>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="5" class="py-10 text-center text-slate-400">
                                    Belum ada pengajuan peminjaman pada kategori ini.
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            @if($requests->hasPages())
                <div class="pt-4 mt-4 border-t border-slate-100">
                    {{ $requests->links() }}
                </div>
            @endif
        </div>

        <!-- Kolom Kanan: Arti Status & Navigasi Riwayat (3 Cols) -->
        <div class="lg:col-span-3 space-y-6">
            <div class="bg-white rounded-3xl p-6 shadow-sm border border-slate-200/80">
                <h3 class="text-sm font-bold text-[#0C1A40] mb-4">Keterangan Status</h3>
                <div class="space-y-3 text-xs">
                    <div class="p-3 rounded-2xl bg-amber-50/70 border border-amber-100">
                        <span class="font-bold text-[#D97706] block">Menunggu</span>
                        <span class="text-slate-600 text-[11px]">Menunggu persetujuan Dosen (M5).</span>
                    </div>
                    <div class="p-3 rounded-2xl bg-emerald-50/70 border border-emerald-100">
                        <span class="font-bold text-[#059669] block">Disetujui</span>
                        <span class="text-slate-600 text-[11px]">Pengajuan disetujui, siap serah terima.</span>
                    </div>
                    <div class="p-3 rounded-2xl bg-blue-50/70 border border-blue-100">
                        <span class="font-bold text-[#2563EB] block">Dipinjam</span>
                        <span class="text-slate-600 text-[11px]">Fasilitas sedang digunakan.</span>
                    </div>
                    <div class="p-3 rounded-2xl bg-teal-50/70 border border-teal-100">
                        <span class="font-bold text-[#059669] block">Dikembalikan / Selesai</span>
                        <span class="text-slate-600 text-[11px]">Pengembalian telah tercatat selesai.</span>
                    </div>
                    <div class="p-3 rounded-2xl bg-rose-50/70 border border-rose-100">
                        <span class="font-bold text-[#DC2626] block">Ditolak</span>
                        <span class="text-slate-600 text-[11px]">Lihat alasan penolakan di halaman detail.</span>
                    </div>
                </div>
            </div>

            <div class="bg-white rounded-3xl p-6 shadow-sm border border-slate-200/80 space-y-3">
                <h3 class="text-sm font-bold text-[#0C1A40]">Riwayat &amp; Katalog</h3>
                <a href="{{ route('mahasiswa.riwayat') }}"
                   class="block w-full text-center py-2.5 rounded-xl bg-[#0C1A40] hover:bg-slate-800 text-white text-xs font-semibold transition">
                    Buka Riwayat Peminjaman
                </a>
                <a href="{{ route('fasilitas.index') }}"
                   class="block w-full text-center py-2.5 rounded-xl border border-slate-200 hover:bg-slate-50 text-slate-700 text-xs font-semibold transition">
                    Lihat Katalog Fasilitas
                </a>
            </div>
        </div>
    </div>
</div>
@endsection
