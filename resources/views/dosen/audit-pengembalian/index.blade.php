@extends('layouts.dashboard')

@section('content')
<!-- [MARKER M6] : RIWAYAT SERAH TERIMA & AUDIT PENGEMBALIAN DOSEN -->
<div class="space-y-6">
    <!-- Header & Navigasi M6 -->
    <div class="flex flex-col lg:flex-row lg:items-center lg:justify-between gap-4">
        <div>
            <h1 class="text-2xl font-bold text-slate-900">Riwayat & Audit Pengembalian</h1>
            <p class="text-xs text-slate-500 mt-0.5">Rekam jejak audit serah terima, pemeriksaan kondisi barang, catatan kerusakan, serta pemulihan stok.</p>
        </div>

        @include('dosen.partials.m6-nav-tabs', ['activeReturnCount' => $activeReturnCount])
    </div>

    <!-- Statistik Ringkas Audit -->
    <div class="grid grid-cols-2 lg:grid-cols-4 gap-4">
        <div class="bg-white rounded-2xl p-5 border border-slate-100 shadow-sm">
            <p class="text-[11px] font-semibold text-slate-400 uppercase tracking-wider">Total Audit</p>
            <p class="text-2xl font-extrabold text-[#0C1A40] mt-1">{{ $stats['total_audits'] }}</p>
            <p class="text-[11px] text-slate-500 mt-0.5">Pengembalian tercatat</p>
        </div>

        <div class="bg-white rounded-2xl p-5 border border-slate-100 shadow-sm">
            <p class="text-[11px] font-semibold text-slate-400 uppercase tracking-wider">Kondisi Baik</p>
            <p class="text-2xl font-extrabold text-[#059669] mt-1">{{ $stats['baik_count'] }}</p>
            <p class="text-[11px] text-slate-500 mt-0.5">Kembali utuh & layak</p>
        </div>

        <div class="bg-white rounded-2xl p-5 border border-slate-100 shadow-sm">
            <p class="text-[11px] font-semibold text-slate-400 uppercase tracking-wider">Rusak / Hilang</p>
            <p class="text-2xl font-extrabold text-[#DC2626] mt-1">{{ $stats['issue_count'] }}</p>
            <p class="text-[11px] text-slate-500 mt-0.5">Memiliki catatan kerusakan</p>
        </div>

        <div class="bg-white rounded-2xl p-5 border border-slate-100 shadow-sm">
            <p class="text-[11px] font-semibold text-slate-400 uppercase tracking-wider">Unit Kondisi Baik</p>
            <p class="text-2xl font-extrabold text-[#2563EB] mt-1">{{ $stats['total_good_units'] }}</p>
            <p class="text-[11px] text-slate-500 mt-0.5">Akumulasi unit kembali baik</p>
        </div>
    </div>

    <!-- Log Audit Pengembalian -->
    <div class="bg-white rounded-3xl p-6 sm:p-8 shadow-sm border border-slate-100 space-y-6">
        <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-2">
            <h3 class="text-base font-bold text-slate-900">Arsip Audit Pengembalian</h3>
            <span class="text-xs text-slate-500">Menampilkan riwayat serah terima & pengembalian terverifikasi</span>
        </div>

        <!-- Filter Bar -->
        <form method="GET" action="{{ route('dosen.audit-pengembalian.index') }}" class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-12 gap-3 items-end">
            <div class="lg:col-span-2">
                <label for="date_from" class="block text-xs font-semibold text-slate-700 mb-1">Dari tanggal</label>
                <input type="date" name="date_from" id="date_from" value="{{ request('date_from') }}"
                       class="w-full px-3 py-2 rounded-xl border border-slate-200 text-xs focus:outline-none focus:ring-2 focus:ring-[#2563EB]">
            </div>

            <div class="lg:col-span-2">
                <label for="date_to" class="block text-xs font-semibold text-slate-700 mb-1">Sampai tanggal</label>
                <input type="date" name="date_to" id="date_to" value="{{ request('date_to') }}"
                       class="w-full px-3 py-2 rounded-xl border border-slate-200 text-xs focus:outline-none focus:ring-2 focus:ring-[#2563EB]">
            </div>

            <div class="lg:col-span-2">
                <label for="type" class="block text-xs font-semibold text-slate-700 mb-1">Jenis</label>
                <select name="type" id="type" class="w-full px-3 py-2 rounded-xl border border-slate-200 text-xs bg-white focus:outline-none focus:ring-2 focus:ring-[#2563EB]">
                    <option value="">Semua</option>
                    <option value="barang" {{ $selectedType === 'barang' ? 'selected' : '' }}>Barang</option>
                    <option value="ruangan" {{ $selectedType === 'ruangan' ? 'selected' : '' }}>Laboratorium</option>
                </select>
            </div>

            <div class="lg:col-span-2">
                <label for="condition" class="block text-xs font-semibold text-slate-700 mb-1">Kondisi</label>
                <select name="condition" id="condition" class="w-full px-3 py-2 rounded-xl border border-slate-200 text-xs bg-white focus:outline-none focus:ring-2 focus:ring-[#2563EB]">
                    <option value="">Semua Kondisi</option>
                    <option value="Baik" {{ request('condition') === 'Baik' ? 'selected' : '' }}>Baik</option>
                    <option value="Rusak Ringan" {{ request('condition') === 'Rusak Ringan' ? 'selected' : '' }}>Rusak Ringan</option>
                    <option value="Rusak Berat" {{ request('condition') === 'Rusak Berat' ? 'selected' : '' }}>Rusak Berat</option>
                    <option value="Hilang" {{ request('condition') === 'Hilang' ? 'selected' : '' }}>Hilang</option>
                </select>
            </div>

            <div class="lg:col-span-3">
                <label for="search" class="block text-xs font-semibold text-slate-700 mb-1">Cari</label>
                <input type="text" name="search" id="search" value="{{ request('search') }}"
                       placeholder="Kode, peminjam, fasilitas, catatan..."
                       class="w-full px-3 py-2 rounded-xl border border-slate-200 text-xs focus:outline-none focus:ring-2 focus:ring-[#2563EB]">
            </div>

            <div class="lg:col-span-1 flex gap-1.5">
                <button type="submit" class="w-full py-2 px-3 rounded-xl bg-[#2563EB] hover:bg-blue-700 text-white text-xs font-semibold transition">
                    Filter
                </button>
            </div>
        </form>

        <div class="overflow-x-auto">
            <table class="w-full text-left border-collapse">
                <thead>
                    <tr class="border-b border-slate-100 text-[11px] font-semibold text-slate-400 uppercase tracking-wider">
                        <th class="py-3 px-3">Kode</th>
                        <th class="py-3 px-3">Fasilitas</th>
                        <th class="py-3 px-3">Penyerah (Mhs)</th>
                        <th class="py-3 px-3">Penerima (Dosen)</th>
                        <th class="py-3 px-3">Serah Terima</th>
                        <th class="py-3 px-3">Pengembalian</th>
                        <th class="py-3 px-3">Kondisi & Unit</th>
                        <th class="py-3 px-3 text-right">Aksi</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-50 text-xs text-slate-700">
                    @forelse($auditRecords as $record)
                        @php
                            $meta = $record->audit_meta;
                            $br = $record->borrowRequest;
                            $badgeClass = match ($record->condition) {
                                'Baik' => 'bg-emerald-50 text-[#059669] border-emerald-200',
                                'Rusak Ringan' => 'bg-amber-50 text-[#D97706] border-amber-200',
                                default => 'bg-rose-50 text-[#DC2626] border-rose-200',
                            };
                        @endphp
                        <tr class="hover:bg-slate-50/80 transition">
                            <td class="py-3.5 px-3 font-mono font-bold text-[#0C1A40]">
                                {{ $br?->code ?? ('#' . $record->borrow_request_id) }}
                            </td>
                            <td class="py-3.5 px-3">
                                <p class="font-semibold text-slate-900">{{ $br?->facility?->name ?? 'Fasilitas' }}</p>
                                <p class="text-[11px] text-slate-400">{{ $br?->type === 'ruangan' ? 'Laboratorium' : 'Barang' }}</p>
                            </td>
                            <td class="py-3.5 px-3">
                                <p class="font-semibold text-slate-800">{{ $meta['handover_by_name'] }}</p>
                                <p class="text-[11px] text-slate-400 font-mono">{{ $meta['handover_by_identity'] }}</p>
                            </td>
                            <td class="py-3.5 px-3">
                                <p class="font-semibold text-slate-800">{{ $meta['received_by_name'] }}</p>
                                <p class="text-[11px] text-slate-400 font-mono">{{ $meta['received_by_identity'] }}</p>
                            </td>
                            <td class="py-3.5 px-3 text-slate-600">
                                {{ $meta['handover_at'] ? $meta['handover_at']->format('d M Y H:i') : '-' }}
                            </td>
                            <td class="py-3.5 px-3 text-slate-600">
                                {{ $record->return_date ? $record->return_date->format('d M Y H:i') : '-' }}
                            </td>
                            <td class="py-3.5 px-3 space-y-1">
                                <span class="inline-block px-2.5 py-0.5 rounded-full text-[10px] font-semibold border {{ $badgeClass }}">
                                    {{ $record->condition }}
                                </span>
                                <p class="text-[11px] text-slate-500">
                                    Baik: <span class="font-bold text-slate-800">{{ $meta['good_quantity'] }}</span>
                                    @if($meta['damaged_quantity'] > 0)
                                        · Rusak: <span class="font-bold text-[#D97706]">{{ $meta['damaged_quantity'] }}</span>
                                    @endif
                                    @if($meta['lost_quantity'] > 0)
                                        · Hilang: <span class="font-bold text-[#DC2626]">{{ $meta['lost_quantity'] }}</span>
                                    @endif
                                </p>
                            </td>
                            <td class="py-3.5 px-3 text-right">
                                <a href="{{ route('dosen.audit-pengembalian.show', $record->id) }}"
                                   class="inline-flex items-center px-3 py-1.5 rounded-xl border border-slate-200 text-slate-700 hover:bg-slate-100 text-xs font-semibold transition">
                                    Detail Audit
                                </a>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="8" class="py-10 text-center text-slate-400">
                                Belum ada catatan audit pengembalian yang tersimpan.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @if($auditRecords->hasPages())
            <div class="pt-4 border-t border-slate-100">
                {{ $auditRecords->links() }}
            </div>
        @endif
    </div>

    <!-- Log Serah Terima Aktif (Belum Dikembalikan) -->
    <div class="bg-white rounded-3xl p-6 sm:p-8 shadow-sm border border-slate-100 space-y-4">
        <div class="flex items-center justify-between">
            <div>
                <h3 class="text-base font-bold text-slate-900">Log Serah Terima Aktif (Belum Dikembalikan)</h3>
                <p class="text-xs text-slate-500">Daftar fasilitas yang sudah melalui QR serah terima dan masih berada di tangan peminjam.</p>
            </div>
            <a href="{{ route('dosen.pengembalian.index') }}" class="text-xs font-semibold text-[#2563EB] hover:underline">
                Kelola Pengembalian &rarr;
            </a>
        </div>

        <div class="overflow-x-auto">
            <table class="w-full text-left border-collapse">
                <thead>
                    <tr class="border-b border-slate-100 text-[11px] font-semibold text-slate-400 uppercase tracking-wider">
                        <th class="py-2.5 px-3">Kode</th>
                        <th class="py-2.5 px-3">Mahasiswa</th>
                        <th class="py-2.5 px-3">Fasilitas</th>
                        <th class="py-2.5 px-3">Waktu Serah Terima</th>
                        <th class="py-2.5 px-3 text-right">Tindakan</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-50 text-xs text-slate-700">
                    @forelse($activeHandovers as $handover)
                        @php
                            $handoverTime = \App\Http\Controllers\M6\QrScannerController::resolveHandoverTimestamp($handover);
                        @endphp
                        <tr class="hover:bg-slate-50/80 transition">
                            <td class="py-3 px-3 font-mono font-bold text-[#0C1A40]">{{ $handover->code }}</td>
                            <td class="py-3 px-3">{{ $handover->user?->name ?? 'Mahasiswa' }} ({{ $handover->user?->nim_nip ?? '-' }})</td>
                            <td class="py-3 px-3 font-medium">{{ $handover->facility?->name ?? 'Fasilitas' }}</td>
                            <td class="py-3 px-3 text-slate-600">{{ $handoverTime ? $handoverTime->format('d M Y H:i') : '-' }}</td>
                            <td class="py-3 px-3 text-right">
                                <a href="{{ route('dosen.pengembalian.show', $handover->id) }}"
                                   class="inline-flex px-3 py-1.5 rounded-xl bg-[#2563EB] text-white text-[11px] font-semibold hover:bg-blue-700 transition">
                                    Konfirmasi Kembali
                                </a>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="5" class="py-6 text-center text-slate-400">
                                Semua serah terima telah diselesaikan pengembaliannya.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>
@endsection
