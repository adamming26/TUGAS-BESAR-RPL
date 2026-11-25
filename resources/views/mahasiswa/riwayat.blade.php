@extends('layouts.dashboard', ['title' => 'Status & Riwayat Peminjaman'])

@section('content')
<!-- [MARKER M4] : STATUS DAN RIWAYAT PEMINJAMAN MAHASISWA -->
<div class="space-y-6">
    <!-- Header Title -->
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
        <div>
            <h1 class="text-2xl font-bold text-[#0C1A40]">Status &amp; Riwayat Peminjaman</h1>
            <p class="text-xs sm:text-sm text-slate-500 mt-0.5">
                Pantau seluruh histori pengajuan, status persetujuan, dan penyelesaian peminjaman Anda.
            </p>
        </div>

        <a href="{{ route('mahasiswa.peminjaman-saya') }}"
           class="px-4 py-2.5 rounded-xl border border-slate-200 bg-white hover:bg-slate-50 text-[#0C1A40] font-semibold text-xs transition shadow-sm w-fit">
            Lihat Daftar Peminjaman Saya
        </a>
    </div>

    <!-- Ringkasan Status Riwayat -->
    <div class="grid grid-cols-2 lg:grid-cols-4 gap-4">
        <div class="bg-white rounded-2xl p-4 shadow-sm border border-slate-200/80">
            <p class="text-[11px] font-bold uppercase tracking-wider text-slate-400">Total Riwayat</p>
            <p class="text-2xl font-extrabold text-[#0C1A40] mt-1">{{ $statusCounts['semua'] }}</p>
        </div>
        <div class="bg-white rounded-2xl p-4 shadow-sm border border-blue-200/80">
            <p class="text-[11px] font-bold uppercase tracking-wider text-[#2563EB]">Sedang Diproses / Aktif</p>
            <p class="text-2xl font-extrabold text-[#2563EB] mt-1">{{ $statusCounts['aktif'] }}</p>
        </div>
        <div class="bg-white rounded-2xl p-4 shadow-sm border border-emerald-200/80">
            <p class="text-[11px] font-bold uppercase tracking-wider text-[#059669]">Selesai / Dikembalikan</p>
            <p class="text-2xl font-extrabold text-[#059669] mt-1">{{ $statusCounts['selesai'] }}</p>
        </div>
        <div class="bg-white rounded-2xl p-4 shadow-sm border border-rose-200/80">
            <p class="text-[11px] font-bold uppercase tracking-wider text-[#DC2626]">Ditolak</p>
            <p class="text-2xl font-extrabold text-[#DC2626] mt-1">{{ $statusCounts['ditolak'] }}</p>
        </div>
    </div>

    <!-- Container Card -->
    <div class="bg-white rounded-3xl p-6 sm:p-8 shadow-sm border border-slate-200/80 space-y-6">
        <h2 class="text-base font-bold text-[#0C1A40]">Catatan Status &amp; Riwayat</h2>

        <!-- Filter Bar -->
        <form method="GET" action="{{ route('mahasiswa.riwayat') }}" class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-12 gap-4 items-end">
            <div class="lg:col-span-4">
                <label for="search" class="block text-xs font-semibold text-slate-700 mb-1.5">Cari Pengajuan</label>
                <input type="text"
                       name="search"
                       id="search"
                       value="{{ request('search') }}"
                       placeholder="Kode pengajuan atau nama fasilitas..."
                       class="w-full px-4 py-2.5 rounded-xl border border-slate-200 text-xs focus:outline-none focus:ring-2 focus:ring-[#2563EB]">
            </div>

            <div class="lg:col-span-2">
                <label for="type" class="block text-xs font-semibold text-slate-700 mb-1.5">Jenis</label>
                <select name="type" id="type" onchange="this.form.submit()"
                        class="w-full px-3 py-2.5 rounded-xl border border-slate-200 text-xs focus:outline-none focus:ring-2 focus:ring-[#2563EB] bg-white">
                    <option value="">Semua</option>
                    <option value="barang" {{ request('type') === 'barang' ? 'selected' : '' }}>Barang</option>
                    <option value="ruangan" {{ request('type') === 'ruangan' ? 'selected' : '' }}>Laboratorium</option>
                </select>
            </div>

            <div class="lg:col-span-3">
                <label for="status" class="block text-xs font-semibold text-slate-700 mb-1.5">Status</label>
                <select name="status" id="status" onchange="this.form.submit()"
                        class="w-full px-3 py-2.5 rounded-xl border border-slate-200 text-xs focus:outline-none focus:ring-2 focus:ring-[#2563EB] bg-white">
                    <option value="">Semua Status</option>
                    <option value="menunggu" {{ request('status') === 'menunggu' ? 'selected' : '' }}>Menunggu</option>
                    <option value="disetujui" {{ request('status') === 'disetujui' ? 'selected' : '' }}>Disetujui</option>
                    <option value="dipinjam" {{ request('status') === 'dipinjam' ? 'selected' : '' }}>Dipinjam</option>
                    <option value="dikembalikan" {{ request('status') === 'dikembalikan' ? 'selected' : '' }}>Dikembalikan</option>
                    <option value="selesai" {{ request('status') === 'selesai' ? 'selected' : '' }}>Selesai</option>
                    <option value="ditolak" {{ request('status') === 'ditolak' ? 'selected' : '' }}>Ditolak</option>
                </select>
            </div>

            <div class="lg:col-span-3">
                <label for="date" class="block text-xs font-semibold text-slate-700 mb-1.5">Tanggal Penggunaan</label>
                <div class="flex items-center space-x-2">
                    <input type="date"
                           name="date"
                           id="date"
                           value="{{ request('date') }}"
                           onchange="this.form.submit()"
                           class="w-full px-3 py-2.5 rounded-xl border border-slate-200 text-xs focus:outline-none focus:ring-2 focus:ring-[#2563EB]">
                    @if(request()->anyFilled(['search', 'type', 'status', 'date']))
                        <a href="{{ route('mahasiswa.riwayat') }}" class="px-3 py-2.5 text-xs text-slate-500 hover:text-slate-700 rounded-xl border border-slate-200" title="Reset filter">
                            Reset
                        </a>
                    @endif
                </div>
            </div>
        </form>

        <!-- Tabel Riwayat -->
        <div class="overflow-x-auto">
            <table class="w-full text-left border-collapse">
                <thead>
                    <tr class="border-b border-slate-100 text-[11px] font-semibold text-slate-400 uppercase tracking-wider">
                        <th class="py-3 px-3">Kode &amp; Tanggal</th>
                        <th class="py-3 px-3">Fasilitas</th>
                        <th class="py-3 px-3">Jenis</th>
                        <th class="py-3 px-3">Peninjau</th>
                        <th class="py-3 px-3">Waktu Kembali</th>
                        <th class="py-3 px-3">Status</th>
                        <th class="py-3 px-3 text-right">Detail</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-50 text-xs text-slate-700">
                    @forelse($riwayat as $item)
                        <tr class="hover:bg-slate-50/80 transition">
                            <td class="py-3.5 px-3">
                                <div class="font-mono font-bold text-[#0C1A40]">{{ $item->code }}</div>
                                <div class="text-[11px] text-slate-500 mt-0.5">
                                    {{ \Carbon\Carbon::parse($item->borrow_date)->isoFormat('D MMM Y') }}
                                </div>
                            </td>
                            <td class="py-3.5 px-3">
                                <div class="font-semibold text-slate-900">
                                    {{ $item->facility ? $item->facility->name : 'Fasilitas' }}
                                </div>
                                <div class="text-[11px] text-slate-400">
                                    {{ substr($item->start_time, 0, 5) }} - {{ substr($item->end_time, 0, 5) }} WITA
                                </div>
                            </td>
                            <td class="py-3.5 px-3 text-slate-500">
                                {{ $item->type === 'ruangan' ? 'Laboratorium' : 'Barang' }}
                            </td>
                            <td class="py-3.5 px-3 text-slate-600">
                                {{ $item->approver ? $item->approver->name : '-' }}
                            </td>
                            <td class="py-3.5 px-3 text-slate-600">
                                @if($item->returnRecord)
                                    {{ \Carbon\Carbon::parse($item->returnRecord->return_date)->isoFormat('D MMM Y, HH:mm') }}
                                @else
                                    -
                                @endif
                            </td>
                            <td class="py-3.5 px-3">
                                <x-badge :status="$item->status">
                                    @if($item->status === 'menunggu')
                                        Menunggu
                                    @elseif($item->status === 'disetujui')
                                        Disetujui
                                    @elseif($item->status === 'dipinjam')
                                        Dipinjam
                                    @elseif($item->status === 'dikembalikan')
                                        Dikembalikan
                                    @elseif($item->status === 'ditolak')
                                        Ditolak
                                    @else
                                        {{ ucfirst($item->status) }}
                                    @endif
                                </x-badge>
                            </td>
                            <td class="py-3.5 px-3 text-right">
                                <a href="{{ route('mahasiswa.detail-peminjaman', $item->id) }}"
                                   class="inline-block px-3 py-1.5 rounded-xl border border-[#2563EB] text-[#2563EB] hover:bg-[#2563EB] hover:text-white font-semibold text-xs transition">
                                    Detail
                                </a>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="7" class="py-8 text-center text-slate-400">
                                Belum ada catatan riwayat peminjaman.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <!-- Footer Pagination -->
        <div class="flex flex-col sm:flex-row items-center justify-between gap-4 pt-4 border-t border-slate-100 text-xs text-slate-500">
            <div>
                Menampilkan {{ $riwayat->firstItem() ?? 0 }} - {{ $riwayat->lastItem() ?? 0 }} dari {{ $riwayat->total() }} data
            </div>
            <div class="flex items-center space-x-2">
                @if($riwayat->onFirstPage())
                    <span class="px-4 py-2 rounded-xl border border-slate-200 text-slate-300 cursor-not-allowed">
                        Sebelumnya
                    </span>
                @else
                    <a href="{{ $riwayat->previousPageUrl() }}" class="px-4 py-2 rounded-xl border border-slate-200 text-slate-700 hover:bg-slate-50 transition">
                        Sebelumnya
                    </a>
                @endif

                @if($riwayat->hasMorePages())
                    <a href="{{ $riwayat->nextPageUrl() }}" class="px-4 py-2 rounded-xl border border-slate-200 text-slate-700 hover:bg-slate-50 transition">
                        Berikutnya
                    </a>
                @else
                    <span class="px-4 py-2 rounded-xl border border-slate-200 text-slate-300 cursor-not-allowed">
                        Berikutnya
                    </span>
                @endif
            </div>
        </div>
    </div>
</div>
@endsection
