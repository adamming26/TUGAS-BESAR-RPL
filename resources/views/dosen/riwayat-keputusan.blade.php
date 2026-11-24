@extends('layouts.dashboard')

@section('content')
<!-- [MARKER M4] : RIWAYAT KEPUTUSAN DOSEN PERSIS FIGMA (PAGE 11) -->
<div class="space-y-6">
    <!-- Header Title -->
    <div>
        <h1 class="text-2xl font-bold text-slate-900">Riwayat Keputusan</h1>
        <p class="text-xs text-slate-500 mt-0.5">Pengajuan yang sudah Anda setujui atau tolak, beserta status pemindaian QR.</p>
    </div>

    <!-- Container Card -->
    <div class="bg-white rounded-3xl p-6 sm:p-8 shadow-sm border border-slate-100 space-y-6">
        <h3 class="text-base font-bold text-slate-900">Semua keputusan</h3>

        <!-- Filter Bar Persis Figma -->
        <div class="flex flex-col sm:flex-row items-center justify-between gap-3">
            <div class="flex items-center space-x-1 bg-slate-100 p-1 rounded-2xl w-full sm:w-auto">
                <a href="{{ route('dosen.riwayat-keputusan') }}" 
                   class="px-5 py-1.5 rounded-xl text-xs font-semibold transition {{ !request('status') ? 'bg-[#2563EB] text-white shadow-sm' : 'text-slate-600 hover:text-slate-900' }}">
                    Semua
                </a>
                <a href="{{ route('dosen.riwayat-keputusan', ['status' => 'disetujui']) }}" 
                   class="px-5 py-1.5 rounded-xl text-xs font-semibold transition {{ request('status') === 'disetujui' ? 'bg-[#2563EB] text-white shadow-sm' : 'text-slate-600 hover:text-slate-900' }}">
                    Disetujui
                </a>
                <a href="{{ route('dosen.riwayat-keputusan', ['status' => 'ditolak']) }}" 
                   class="px-5 py-1.5 rounded-xl text-xs font-semibold transition {{ request('status') === 'ditolak' ? 'bg-[#2563EB] text-white shadow-sm' : 'text-slate-600 hover:text-slate-900' }}">
                    Ditolak
                </a>
            </div>

            <form method="GET" action="{{ route('dosen.riwayat-keputusan') }}" class="w-full sm:w-64">
                @if(request('status'))
                    <input type="hidden" name="status" value="{{ request('status') }}">
                @endif
                <div class="relative">
                    <input type="text" 
                           name="search" 
                           value="{{ request('search') }}"
                           placeholder="Cari data" 
                           class="w-full pl-3 pr-8 py-2 rounded-xl border border-slate-200 text-xs focus:outline-none focus:ring-2 focus:ring-[#2563EB]">
                    @if(request('search'))
                        <a href="{{ route('dosen.riwayat-keputusan', request()->only('status')) }}" class="absolute right-2.5 top-2 text-slate-400 hover:text-slate-600 text-xs">&times;</a>
                    @endif
                </div>
            </form>
        </div>

        <!-- Tabel Keputusan Persis Figma -->
        <div class="overflow-x-auto">
            <table class="w-full text-left border-collapse">
                <thead>
                    <tr class="border-b border-slate-100 text-[11px] font-semibold text-slate-400 uppercase tracking-wider">
                        <th class="py-3 px-3">Tanggal</th>
                        <th class="py-3 px-3">Pemohon</th>
                        <th class="py-3 px-3">Fasilitas</th>
                        <th class="py-3 px-3">Jenis</th>
                        <th class="py-3 px-3">Keputusan</th>
                        <th class="py-3 px-3">Catatan</th>
                        <th class="py-3 px-3 text-right">Pemindaian</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-50 text-xs text-slate-700">
                    @forelse($decisions as $item)
                        <tr class="hover:bg-slate-50/80 transition">
                            <td class="py-3.5 px-3 text-slate-600">
                                {{ \Carbon\Carbon::parse($item->borrow_date)->isoFormat('D MMM Y') }}
                            </td>
                            <td class="py-3.5 px-3 font-semibold text-slate-900">
                                {{ $item->user ? $item->user->name : 'Mahasiswa' }}
                            </td>
                            <td class="py-3.5 px-3 text-slate-700">
                                {{ $item->facility ? $item->facility->name : 'Fasilitas' }}
                            </td>
                            <td class="py-3.5 px-3 text-slate-500 capitalize">
                                {{ $item->type === 'ruangan' ? 'Lab' : 'Barang' }}
                            </td>
                            <td class="py-3.5 px-3">
                                @if(in_array($item->status, ['disetujui', 'dipinjam', 'dikembalikan', 'selesai']))
                                    <span class="inline-block px-2.5 py-1 rounded-full text-[10px] font-semibold bg-emerald-50 text-emerald-700 border border-emerald-200">
                                        Disetujui
                                    </span>
                                @else
                                    <span class="inline-block px-2.5 py-1 rounded-full text-[10px] font-semibold bg-rose-50 text-rose-700 border border-rose-200">
                                        Ditolak
                                    </span>
                                @endif
                            </td>
                            <td class="py-3.5 px-3 text-slate-500">
                                {{ $item->reject_reason ? Str::limit($item->reject_reason, 30) : '-' }}
                            </td>
                            <td class="py-3.5 px-3 text-right">
                                @if(in_array($item->status, ['dikembalikan', 'selesai']))
                                    <span class="inline-block px-3 py-1 rounded-full text-[10px] font-semibold bg-blue-50 text-[#2563EB] border border-blue-200">
                                        Pengembalian dipindai
                                    </span>
                                @elseif($item->status === 'dipinjam')
                                    <span class="inline-block px-3 py-1 rounded-full text-[10px] font-semibold bg-blue-50 text-[#2563EB] border border-blue-200">
                                        Penyerahan dipindai
                                    </span>
                                @elseif($item->status === 'disetujui')
                                    <span class="inline-block px-3 py-1 rounded-full text-[10px] font-semibold bg-amber-50 text-amber-700 border border-amber-200">
                                        Belum dipindai
                                    </span>
                                @else
                                    <span class="text-slate-400">-</span>
                                @endif
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="7" class="py-8 text-center text-slate-400">
                                Belum ada catatan keputusan yang terekam.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <!-- Footer Pagination Persis Figma -->
        <div class="flex flex-col sm:flex-row items-center justify-between gap-4 pt-4 border-t border-slate-100 text-xs text-slate-500">
            <div>
                Menampilkan {{ $decisions->firstItem() ?? 0 }} dari {{ $decisions->total() }} data
            </div>
            <div class="flex items-center space-x-2">
                @if($decisions->onFirstPage())
                    <span class="px-4 py-2 rounded-xl border border-slate-200 text-slate-300 cursor-not-allowed">
                        Sebelumnya
                    </span>
                @else
                    <a href="{{ $decisions->previousPageUrl() }}" class="px-4 py-2 rounded-xl border border-slate-200 text-slate-700 hover:bg-slate-50 transition">
                        Sebelumnya
                    </a>
                @endif

                @if($decisions->hasMorePages())
                    <a href="{{ $decisions->nextPageUrl() }}" class="px-4 py-2 rounded-xl border border-slate-200 text-slate-700 hover:bg-slate-50 transition">
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
