@extends('layouts.dashboard')

@section('content')
<!-- [MARKER M6] : TRANSAKSI BERJALAN ADMIN DENGAN TIMELINE 6-TAHAP PERSIS FIGMA (PAGE 14) -->
<div class="space-y-6">
    <!-- Header Title -->
    <div>
        <h1 class="text-2xl font-bold text-slate-900">Transaksi</h1>
        <p class="text-xs text-slate-500 mt-0.5">Pantau peminjaman yang sedang berjalan dan tahapannya.</p>
    </div>

    <!-- Main Grid: Transaksi Berjalan (Kiri 8 cols) & Rincian Timeline 6-Tahap (Kanan 4 cols) -->
    <div class="grid grid-cols-1 lg:grid-cols-12 gap-6 items-start">
        <!-- Kolom Kiri: Tabel Transaksi Berjalan (8 Cols) Persis Figma -->
        <div class="lg:col-span-8 bg-white rounded-3xl p-6 sm:p-8 shadow-sm border border-slate-100 space-y-5">
            <h3 class="text-base font-bold text-slate-900">Transaksi berjalan</h3>

            <!-- Filter Tabs & Cari Data -->
            <div class="flex flex-col sm:flex-row items-center justify-between gap-3">
                <div class="flex flex-wrap items-center gap-1 bg-slate-100 p-1 rounded-2xl w-full sm:w-auto">
                    <a href="{{ route('admin.transaksi-berjalan') }}" 
                       class="px-3.5 py-1.5 rounded-xl text-xs font-semibold transition {{ !request('tab') ? 'bg-[#2563EB] text-white shadow-sm' : 'text-slate-600 hover:text-slate-900' }}">
                        Semua
                    </a>
                    <a href="{{ route('admin.transaksi-berjalan', ['tab' => 'menunggu_serah']) }}" 
                       class="px-3.5 py-1.5 rounded-xl text-xs font-semibold transition {{ request('tab') === 'menunggu_serah' ? 'bg-[#2563EB] text-white shadow-sm' : 'text-slate-600 hover:text-slate-900' }}">
                        Menunggu serah
                    </a>
                    <a href="{{ route('admin.transaksi-berjalan', ['tab' => 'dipinjam']) }}" 
                       class="px-3.5 py-1.5 rounded-xl text-xs font-semibold transition {{ request('tab') === 'dipinjam' ? 'bg-[#2563EB] text-white shadow-sm' : 'text-slate-600 hover:text-slate-900' }}">
                        Dipinjam
                    </a>
                    <a href="{{ route('admin.transaksi-berjalan', ['tab' => 'menunggu_pengembalian']) }}" 
                       class="px-3.5 py-1.5 rounded-xl text-xs font-semibold transition {{ request('tab') === 'menunggu_pengembalian' ? 'bg-[#2563EB] text-white shadow-sm' : 'text-slate-600 hover:text-slate-900' }}">
                        Menunggu pengembalian
                    </a>
                </div>

                <form method="GET" action="{{ route('admin.transaksi-berjalan') }}" class="w-full sm:w-56">
                    @if(request('tab'))
                        <input type="hidden" name="tab" value="{{ request('tab') }}">
                    @endif
                    <div class="relative">
                        <input type="text" 
                               name="search" 
                               value="{{ request('search') }}"
                               placeholder="Cari data" 
                               class="w-full pl-3 pr-8 py-2 rounded-xl border border-slate-200 text-xs focus:outline-none focus:ring-2 focus:ring-[#2563EB]">
                        @if(request('search'))
                            <a href="{{ route('admin.transaksi-berjalan', request()->only('tab')) }}" class="absolute right-2.5 top-2 text-slate-400 hover:text-slate-600 text-xs">&times;</a>
                        @endif
                    </div>
                </form>
            </div>

            <!-- Tabel Transaksi Persis Figma -->
            <div class="overflow-x-auto">
                <table class="w-full text-left border-collapse">
                    <thead>
                        <tr class="border-b border-slate-100 text-[11px] font-semibold text-slate-400 uppercase tracking-wider">
                            <th class="py-3 px-3">ID</th>
                            <th class="py-3 px-3">Peminjam</th>
                            <th class="py-3 px-3">Fasilitas</th>
                            <th class="py-3 px-3">Jenis</th>
                            <th class="py-3 px-3">Waktu</th>
                            <th class="py-3 px-3 text-center">Status</th>
                            <th class="py-3 px-3 text-right">Dikonfirmasi oleh</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-50 text-xs text-slate-700">
                        @forelse($transactions as $tx)
                            <tr onclick="window.location='{{ route('admin.transaksi-berjalan', array_merge(request()->query(), ['selected_id' => $tx->id])) }}'" 
                                class="cursor-pointer hover:bg-slate-50/80 transition {{ ($selectedTx?->id == $tx->id) ? 'bg-blue-50/60' : '' }}">
                                <td class="py-3.5 px-3 font-mono text-slate-500 text-[11px]">
                                    [{{ substr($tx->code, -5) }}]
                                </td>
                                <td class="py-3.5 px-3 font-semibold text-slate-900">
                                    {{ $tx->user ? $tx->user->name : 'Mahasiswa' }}
                                </td>
                                <td class="py-3.5 px-3 text-slate-700">
                                    {{ $tx->facility ? $tx->facility->name : 'Fasilitas' }}
                                </td>
                                <td class="py-3.5 px-3 text-slate-500 capitalize">
                                    {{ $tx->type === 'ruangan' ? 'Lab' : 'Barang' }}
                                </td>
                                <td class="py-3.5 px-3 text-slate-500 text-[11px]">
                                    {{ \Carbon\Carbon::parse($tx->borrow_date)->isoFormat('D MMM') }}, {{ substr($tx->start_time, 0, 5) }}
                                </td>
                                <td class="py-3.5 px-3 text-center">
                                    @if($tx->status === 'disetujui')
                                        <span class="inline-block px-2.5 py-1 rounded-full text-[10px] font-semibold bg-amber-50 text-amber-700 border border-amber-200">
                                            Menunggu serah
                                        </span>
                                    @elseif($tx->status === 'dipinjam')
                                        @if($tx->qrTokens->where('type', 'pengembalian')->isNotEmpty())
                                            <span class="inline-block px-2.5 py-1 rounded-full text-[10px] font-semibold bg-amber-50 text-amber-700 border border-amber-200">
                                                Menunggu pengembalian
                                            </span>
                                        @else
                                            <span class="inline-block px-2.5 py-1 rounded-full text-[10px] font-semibold bg-blue-50 text-[#2563EB] border border-blue-200">
                                                {{ $tx->type === 'ruangan' ? 'Berlangsung' : 'Dipinjam' }}
                                            </span>
                                        @endif
                                    @else
                                        <span class="inline-block px-2.5 py-1 rounded-full text-[10px] font-semibold bg-slate-100 text-slate-700">
                                            {{ ucfirst($tx->status) }}
                                        </span>
                                    @endif
                                </td>
                                <td class="py-3.5 px-3 text-right text-slate-600">
                                    {{ $tx->approver ? $tx->approver->name : '-' }}
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="7" class="py-8 text-center text-slate-400">
                                    Tidak ada transaksi berjalan dalam kategori ini.
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            <!-- Pagination Persis Figma -->
            <div class="flex flex-col sm:flex-row items-center justify-between gap-4 pt-4 border-t border-slate-100 text-xs text-slate-500">
                <div>
                    Menampilkan {{ $transactions->firstItem() ?? 0 }} dari {{ $transactions->total() }} data
                </div>
                <div class="flex items-center space-x-2">
                    @if($transactions->onFirstPage())
                        <span class="px-4 py-2 rounded-xl border border-slate-200 text-slate-300 cursor-not-allowed">
                            Sebelumnya
                        </span>
                    @else
                        <a href="{{ $transactions->previousPageUrl() }}" class="px-4 py-2 rounded-xl border border-slate-200 text-slate-700 hover:bg-slate-50 transition">
                            Sebelumnya
                        </a>
                    @endif

                    @if($transactions->hasMorePages())
                        <a href="{{ $transactions->nextPageUrl() }}" class="px-4 py-2 rounded-xl border border-slate-200 text-slate-700 hover:bg-slate-50 transition">
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

        <!-- Kolom Kanan: Rincian 6-Tahap Timeline Card (4 Cols) Persis Figma [ID-04] -->
        <div class="lg:col-span-4 bg-white rounded-3xl p-6 sm:p-8 shadow-sm border border-slate-100 space-y-6">
            <h3 class="text-base font-bold text-slate-900">
                Rincian [{{ $selectedTx ? substr($selectedTx->code, -5) : 'ID-04' }}]
            </h3>

            @if($selectedTx)
                <!-- 6-Tahap Stepper Timeline Persis Figma -->
                <div class="space-y-5 text-xs">
                    <!-- Tahap 1: Diajukan Mahasiswa -->
                    <div class="flex items-start space-x-3.5">
                        <div class="w-6 h-6 rounded-full bg-blue-100 text-[#2563EB] font-bold flex items-center justify-center shrink-0 text-xs">
                            1
                        </div>
                        <div>
                            <p class="font-semibold text-slate-900">Diajukan Mahasiswa</p>
                            <p class="text-[11px] text-slate-400">{{ \Carbon\Carbon::parse($selectedTx->created_at)->isoFormat('D MMM Y, HH:mm') }}</p>
                        </div>
                    </div>

                    <!-- Tahap 2: Disetujui Dosen -->
                    <div class="flex items-start space-x-3.5">
                        <div class="w-6 h-6 rounded-full {{ in_array($selectedTx->status, ['disetujui', 'dipinjam', 'dikembalikan', 'selesai']) ? 'bg-blue-100 text-[#2563EB]' : 'bg-slate-100 text-slate-400' }} font-bold flex items-center justify-center shrink-0 text-xs">
                            2
                        </div>
                        <div>
                            <p class="font-semibold text-slate-900">Disetujui Dosen</p>
                            <p class="text-[11px] text-slate-400">
                                @if(in_array($selectedTx->status, ['disetujui', 'dipinjam', 'dikembalikan', 'selesai']))
                                    {{ \Carbon\Carbon::parse($selectedTx->updated_at)->isoFormat('D MMM Y, HH:mm') }}
                                @else
                                    Menunggu persetujuan
                                @endif
                            </p>
                        </div>
                    </div>

                    <!-- Tahap 3: QR peminjaman terbit -->
                    <div class="flex items-start space-x-3.5">
                        <div class="w-6 h-6 rounded-full {{ in_array($selectedTx->status, ['disetujui', 'dipinjam', 'dikembalikan', 'selesai']) ? 'bg-blue-100 text-[#2563EB]' : 'bg-slate-100 text-slate-400' }} font-bold flex items-center justify-center shrink-0 text-xs">
                            3
                        </div>
                        <div>
                            <p class="font-semibold text-slate-900">QR peminjaman terbit</p>
                            <p class="text-[11px] text-slate-400">
                                @if($selectedTx->qrTokens->where('type', 'penyerahan')->first())
                                    {{ \Carbon\Carbon::parse($selectedTx->qrTokens->where('type', 'penyerahan')->first()->created_at)->isoFormat('D MMM Y, HH:mm') }}
                                @else
                                    -
                                @endif
                            </p>
                        </div>
                    </div>

                    <!-- Tahap 4: Diserahkan, QR dipindai Dosen -->
                    <div class="flex items-start space-x-3.5">
                        <div class="w-6 h-6 rounded-full {{ in_array($selectedTx->status, ['dipinjam', 'dikembalikan', 'selesai']) ? 'bg-blue-100 text-[#2563EB]' : 'bg-slate-100 text-slate-400' }} font-bold flex items-center justify-center shrink-0 text-xs">
                            4
                        </div>
                        <div>
                            <p class="font-semibold text-slate-900">Diserahkan, QR dipindai Dosen</p>
                            <p class="text-[11px] text-slate-400">
                                @if(in_array($selectedTx->status, ['dipinjam', 'dikembalikan', 'selesai']))
                                    Dikonfirmasi oleh {{ $selectedTx->approver?->name ?? 'Dosen' }}
                                @else
                                    Belum diserahkan
                                @endif
                            </p>
                        </div>
                    </div>

                    <!-- Tahap 5: Pengembalian diajukan -->
                    <div class="flex items-start space-x-3.5">
                        <div class="w-6 h-6 rounded-full {{ ($selectedTx->qrTokens->where('type', 'pengembalian')->isNotEmpty() || in_array($selectedTx->status, ['dikembalikan', 'selesai'])) ? 'bg-blue-100 text-[#2563EB]' : 'bg-slate-100 text-slate-400' }} font-bold flex items-center justify-center shrink-0 text-xs">
                            5
                        </div>
                        <div>
                            <p class="font-semibold text-slate-900">Pengembalian diajukan</p>
                            <p class="text-[11px] text-slate-400">
                                @if($selectedTx->qrTokens->where('type', 'pengembalian')->first())
                                    {{ \Carbon\Carbon::parse($selectedTx->qrTokens->where('type', 'pengembalian')->first()->created_at)->isoFormat('D MMM Y, HH:mm') }}
                                @else
                                    -
                                @endif
                            </p>
                        </div>
                    </div>

                    <!-- Tahap 6: Dikembalikan, QR dipindai Dosen -->
                    <div class="flex items-start space-x-3.5">
                        <div class="w-6 h-6 rounded-full {{ in_array($selectedTx->status, ['dikembalikan', 'selesai']) ? 'bg-blue-100 text-[#2563EB]' : 'bg-slate-100 text-slate-400' }} font-bold flex items-center justify-center shrink-0 text-xs">
                            6
                        </div>
                        <div>
                            <p class="font-semibold text-slate-900">Dikembalikan, QR dipindai Dosen</p>
                            <p class="text-[11px] text-slate-400">
                                @if($selectedTx->returnRecord)
                                    {{ \Carbon\Carbon::parse($selectedTx->returnRecord->return_date)->isoFormat('D MMM Y, HH:mm') }}
                                @else
                                    -
                                @endif
                            </p>
                        </div>
                    </div>
                </div>
            @else
                <p class="text-xs text-slate-400 text-center py-6">Pilih salah satu transaksi untuk melihat rincian tahapan.</p>
            @endif
        </div>
    </div>
</div>
@endsection
