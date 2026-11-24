@extends('layouts.app')

@section('title', 'Pengajuan Masuk')
@section('page_title', 'Pengajuan Masuk')

@section('content')
<div class="mb-6 flex flex-col sm:flex-row sm:items-center justify-between gap-4">
    <div>
        <h2 class="text-2xl font-extrabold text-slate-900 tracking-tight">Daftar Pengajuan Masuk</h2>
        <p class="text-sm text-slate-500">Periksa pengajuan peminjaman fasilitas dari mahasiswa dan berikan keputusan.</p>
    </div>
</div>

<div class="bg-white rounded-3xl p-6 shadow-xs border border-slate-200">
    <!-- Filter Tabs & Search -->
    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4 mb-6">
        <div class="flex items-center gap-2 overflow-x-auto pb-1 sm:pb-0">
            <a href="{{ route('dosen.pengajuan-masuk', ['status' => 'menunggu', 'search' => request('search')]) }}"
               class="px-4 py-2 rounded-xl text-xs font-bold transition-all {{ $status === 'menunggu' ? 'bg-blue-600 text-white shadow-xs' : 'bg-slate-100 text-slate-600 hover:bg-slate-200' }}">
                Menunggu Keputusan
            </a>
            <a href="{{ route('dosen.pengajuan-masuk', ['status' => 'disetujui', 'search' => request('search')]) }}"
               class="px-4 py-2 rounded-xl text-xs font-bold transition-all {{ $status === 'disetujui' ? 'bg-blue-600 text-white shadow-xs' : 'bg-slate-100 text-slate-600 hover:bg-slate-200' }}">
                Disetujui
            </a>
            <a href="{{ route('dosen.pengajuan-masuk', ['status' => 'ditolak', 'search' => request('search')]) }}"
               class="px-4 py-2 rounded-xl text-xs font-bold transition-all {{ $status === 'ditolak' ? 'bg-blue-600 text-white shadow-xs' : 'bg-slate-100 text-slate-600 hover:bg-slate-200' }}">
                Ditolak
            </a>
        </div>

        <form method="GET" action="{{ route('dosen.pengajuan-masuk') }}" class="w-full sm:w-72">
            <input type="hidden" name="status" value="{{ $status }}">
            <div class="relative">
                <input type="text" name="search" value="{{ $search }}" placeholder="Cari pemohon, kode, fasilitas..."
                       class="w-full pl-10 pr-4 py-2 rounded-xl border border-slate-200 text-xs focus:ring-2 focus:ring-blue-600 focus:outline-none">
                <svg class="w-4 h-4 text-slate-400 absolute left-3.5 top-2.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"></path></svg>
            </div>
        </form>
    </div>

    <!-- Table -->
    <div class="overflow-x-auto rounded-2xl border border-slate-100">
        <table class="w-full text-left text-xs min-w-[720px] border-collapse">
            <thead>
                <tr class="bg-slate-50/80 border-b border-slate-200 text-slate-500 uppercase font-bold tracking-wider text-[11px]">
                    <th class="py-3.5 px-4 text-left">Kode &amp; Pemohon</th>
                    <th class="py-3.5 px-4 text-left">Fasilitas</th>
                    <th class="py-3.5 px-4 text-left">Jenis</th>
                    <th class="py-3.5 px-4 text-left">Jadwal Penggunaan</th>
                    <th class="py-3.5 px-4 text-left">Status</th>
                    <th class="py-3.5 px-4 text-right">Aksi</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-slate-100 text-slate-700 bg-white">
                @forelse($pengajuanList as $p)
                    <tr class="hover:bg-slate-50/60 transition-colors">
                        <td class="py-4 px-4">
                            <div class="font-bold text-slate-900 text-sm leading-tight">{{ $p->user->nama }}</div>
                            <div class="text-[10px] text-slate-400 font-mono mt-0.5">{{ $p->kode_peminjaman }} • NIM: {{ $p->user->nomor_induk }}</div>
                        </td>
                        <td class="py-4 px-4 font-semibold text-slate-800">
                            {{ $p->nama_fasilitas }} ({{ $p->jumlah }} unit)
                        </td>
                        <td class="py-4 px-4 whitespace-nowrap">
                            <span class="inline-flex items-center px-2.5 py-1 rounded-lg text-xs font-semibold {{ $p->jenis_fasilitas === 'BARANG' ? 'bg-slate-100 text-slate-700' : 'bg-blue-50 text-blue-700 border border-blue-100' }}">
                                {{ $p->jenis_fasilitas === 'BARANG' ? 'Barang' : 'Lab' }}
                            </span>
                        </td>
                        <td class="py-4 px-4 whitespace-nowrap">
                            <div class="font-medium text-slate-800">{{ $p->tanggal_peminjaman->format('d/m/Y') }}</div>
                            <div class="text-[10px] text-slate-400 font-mono">{{ substr($p->jam_mulai, 0, 5) }} - {{ substr($p->jam_selesai, 0, 5) }} ({{ $p->durasi }})</div>
                        </td>
                        <td class="py-4 px-4 whitespace-nowrap">
                            @if($p->status === 'MENUNGGU PERSETUJUAN')
                                <span class="inline-flex items-center px-3 py-1 rounded-full bg-amber-50 text-amber-700 border border-amber-200 text-xs font-bold leading-none">Menunggu Dosen</span>
                            @elseif(in_array($p->status, ['DISETUJUI', 'MENUNGGU PENYERAHAN']))
                                <span class="inline-flex items-center px-3 py-1 rounded-full bg-emerald-50 text-emerald-700 border border-emerald-200 text-xs font-bold leading-none">Disetujui</span>
                            @elseif($p->status === 'DIPINJAM')
                                <span class="inline-flex items-center px-3 py-1 rounded-full bg-blue-50 text-blue-700 border border-blue-200 text-xs font-bold leading-none">Dipinjam</span>
                            @elseif($p->status === 'KEDALUWARSA')
                                <span class="inline-flex items-center px-3 py-1 rounded-full bg-rose-50 text-rose-700 border border-rose-200 text-xs font-bold leading-none">Kedaluwarsa</span>
                            @elseif($p->status === 'DITOLAK')
                                <span class="inline-flex items-center px-3 py-1 rounded-full bg-rose-50 text-rose-700 border border-rose-200 text-xs font-bold leading-none">Ditolak</span>
                            @else
                                <span class="inline-flex items-center px-3 py-1 rounded-full bg-slate-100 text-slate-700 text-xs font-bold leading-none">{{ $p->status }}</span>
                            @endif
                        </td>
                        <td class="py-4 px-4 text-right whitespace-nowrap">
                            <a href="{{ route('dosen.detail-pengajuan', $p->id_peminjaman) }}"
                               class="inline-flex items-center gap-1.5 px-3.5 py-1.5 rounded-xl bg-blue-600 hover:bg-blue-700 text-white font-bold text-xs shadow-xs transition-all">
                                Periksa &amp; Putuskan
                            </a>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="6" class="py-12 text-center text-slate-400">
                            Tidak ada pengajuan yang sesuai filter.
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    <div class="mt-4 pt-3 border-t border-slate-100">
        {{ $pengajuanList->links() }}
    </div>
</div>
@endsection
