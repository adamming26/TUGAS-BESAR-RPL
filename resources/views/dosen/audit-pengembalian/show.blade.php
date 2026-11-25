@extends('layouts.dashboard')

@section('content')
<!-- [MARKER M6] : DETAIL LENGKAP AUDIT PENGEMBALIAN -->
@php
    $badgeClass = match ($returnRecord->condition) {
        'Baik' => 'bg-emerald-50 text-[#059669] border-emerald-200',
        'Rusak Ringan' => 'bg-amber-50 text-[#D97706] border-amber-200',
        default => 'bg-rose-50 text-[#DC2626] border-rose-200',
    };
@endphp

<div class="space-y-6 max-w-5xl">
    <!-- Header & Navigasi M6 -->
    <div class="flex flex-col lg:flex-row lg:items-center lg:justify-between gap-4">
        <div>
            <a href="{{ route('dosen.audit-pengembalian.index') }}" class="text-xs font-semibold text-[#2563EB] hover:underline inline-flex items-center gap-1 mb-1">
                <span>&larr;</span>
                <span>Kembali ke Riwayat & Audit</span>
            </a>
            <h1 class="text-2xl font-bold text-slate-900">Detail Audit Pengembalian</h1>
            <p class="text-xs text-slate-500 mt-0.5">Rekaman lengkap audit transaksi {{ $borrowRequest?->code ?? ('#' . $returnRecord->borrow_request_id) }}.</p>
        </div>

        @include('dosen.partials.m6-nav-tabs', ['activeReturnCount' => $activeReturnCount])
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-12 gap-6 items-start">
        <!-- Informasi Utama Audit (8 Cols) -->
        <div class="lg:col-span-8 space-y-6">
            <div class="bg-white rounded-3xl p-6 sm:p-8 shadow-sm border border-slate-100 space-y-6">
                <div class="flex flex-wrap items-center justify-between gap-3 pb-4 border-b border-slate-100">
                    <div>
                        <span class="text-[11px] font-semibold text-slate-400 uppercase tracking-wider">ID & Kode Peminjaman</span>
                        <h2 class="text-lg font-extrabold text-[#0C1A40] font-mono">
                            #{{ $returnRecord->borrow_request_id }} &middot; {{ $borrowRequest?->code ?? '-' }}
                        </h2>
                    </div>
                    <span class="px-3.5 py-1 rounded-full text-xs font-bold border {{ $badgeClass }}">
                        Kondisi: {{ $returnRecord->condition }}
                    </span>
                </div>

                <!-- Grid 9 Field Audit Wajib M6 -->
                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4 text-xs">
                    <div class="p-4 rounded-2xl bg-[#F8FAFC] border border-slate-200/70">
                        <span class="text-slate-400 block mb-1">Fasilitas yang Dipinjam</span>
                        <p class="font-bold text-slate-900 text-sm">{{ $borrowRequest?->facility?->name ?? 'Fasilitas' }}</p>
                        <p class="text-[11px] text-slate-500 font-mono">{{ $borrowRequest?->facility?->code ?? '-' }} &middot; {{ $borrowRequest?->type === 'ruangan' ? 'Laboratorium' : 'Barang' }}</p>
                    </div>

                    <div class="p-4 rounded-2xl bg-[#F8FAFC] border border-slate-200/70">
                        <span class="text-slate-400 block mb-1">Jumlah Kembali Kondisi Baik</span>
                        <p class="font-extrabold text-[#059669] text-lg">{{ $auditMeta['good_quantity'] }} unit</p>
                        <p class="text-[11px] text-slate-500">
                            Dari total {{ $auditMeta['expected_quantity'] }} · Rusak: {{ $auditMeta['damaged_quantity'] }} · Hilang: {{ $auditMeta['lost_quantity'] }}
                        </p>
                    </div>

                    <div class="p-4 rounded-2xl bg-[#F8FAFC] border border-slate-200/70">
                        <span class="text-slate-400 block mb-1">Waktu Serah Terima</span>
                        <p class="font-bold text-slate-900">
                            {{ $auditMeta['handover_at'] ? $auditMeta['handover_at']->format('d M Y, H:i:s') : 'Belum tercatat via QR penyerahan' }}
                        </p>
                    </div>

                    <div class="p-4 rounded-2xl bg-[#F8FAFC] border border-slate-200/70">
                        <span class="text-slate-400 block mb-1">Waktu Pengembalian</span>
                        <p class="font-bold text-slate-900">
                            {{ $returnRecord->return_date ? $returnRecord->return_date->format('d M Y, H:i:s') : '-' }}
                        </p>
                    </div>

                    <div class="p-4 rounded-2xl bg-[#F8FAFC] border border-slate-200/70">
                        <span class="text-slate-400 block mb-1">Pihak yang Menyerahkan (Peminjam)</span>
                        <p class="font-bold text-slate-900">{{ $auditMeta['handover_by_name'] }}</p>
                        <p class="text-[11px] text-slate-500 font-mono">NIM/NIP: {{ $auditMeta['handover_by_identity'] }}</p>
                    </div>

                    <div class="p-4 rounded-2xl bg-[#F8FAFC] border border-slate-200/70">
                        <span class="text-slate-400 block mb-1">Pihak yang Menerima (Dosen)</span>
                        <p class="font-bold text-slate-900">{{ $auditMeta['received_by_name'] }}</p>
                        <p class="text-[11px] text-slate-500 font-mono">NIP: {{ $auditMeta['received_by_identity'] }}</p>
                    </div>
                </div>

                <!-- Catatan Kerusakan & Pemeriksaan -->
                <div class="space-y-2">
                    <span class="text-xs font-bold text-slate-700 block">Catatan Kerusakan / Pemeriksaan Kondisi</span>
                    <div class="p-4 rounded-2xl border {{ $returnRecord->condition === 'Baik' ? 'bg-slate-50 border-slate-200/80 text-slate-700' : 'bg-rose-50/70 border-rose-200 text-rose-900' }} text-xs leading-relaxed">
                        {{ $auditMeta['clean_notes'] }}
                    </div>
                </div>
            </div>
        </div>

        <!-- Sidebar Jejak Token & Pemulihan Stok (4 Cols) -->
        <div class="lg:col-span-4 space-y-6">
            <div class="bg-white rounded-3xl p-6 shadow-sm border border-slate-100 space-y-4">
                <h3 class="text-sm font-bold text-slate-900">Ringkasan Pemulihan Stok</h3>

                @if($borrowRequest?->type === 'barang')
                    <div class="p-4 rounded-2xl bg-emerald-50 border border-emerald-100 space-y-1 text-xs">
                        <p class="font-bold text-[#059669]">+{{ $auditMeta['good_quantity'] }} unit dipulihkan</p>
                        <p class="text-emerald-800">Stok tersedia fasilitas saat ini: <strong>{{ $borrowRequest?->facility?->available_stock ?? '-' }} / {{ $borrowRequest?->facility?->total_stock ?? '-' }}</strong></p>
                        @if($auditMeta['damaged_quantity'] > 0 || $auditMeta['lost_quantity'] > 0)
                            <p class="text-[#DC2626] font-semibold pt-1">
                                {{ $auditMeta['damaged_quantity'] + $auditMeta['lost_quantity'] }} unit rusak/hilang tidak menambah stok.
                            </p>
                        @endif
                    </div>
                @else
                    <div class="p-4 rounded-2xl bg-blue-50 border border-blue-100 text-xs text-slate-700">
                        Transaksi fasilitas <strong>Laboratorium</strong> telah diselesaikan dan log jadwal diperbarui menjadi <span class="font-bold text-[#059669]">selesai</span>.
                    </div>
                @endif
            </div>

            <div class="bg-white rounded-3xl p-6 shadow-sm border border-slate-100 space-y-4">
                <h3 class="text-sm font-bold text-slate-900">Jejak Token QR M5 & M6</h3>

                <div class="space-y-3 text-xs">
                    <div class="p-3 rounded-2xl bg-slate-50 border border-slate-200/80 space-y-1">
                        <span class="text-[10px] font-bold uppercase tracking-wider text-slate-400">QR Penyerahan</span>
                        <p class="font-mono font-bold text-[#0C1A40] break-all">{{ $auditMeta['handover_token']?->token ?? '-' }}</p>
                        <p class="text-[11px] text-slate-500">
                            Dipindai: {{ $auditMeta['handover_token']?->used_at?->format('d M Y H:i') ?? '-' }}
                        </p>
                    </div>

                    <div class="p-3 rounded-2xl bg-slate-50 border border-slate-200/80 space-y-1">
                        <span class="text-[10px] font-bold uppercase tracking-wider text-slate-400">QR Pengembalian</span>
                        <p class="font-mono font-bold text-[#0C1A40] break-all">{{ $auditMeta['return_token']?->token ?? '-' }}</p>
                        <p class="text-[11px] text-slate-500">
                            Dipindai: {{ $auditMeta['return_token']?->used_at?->format('d M Y H:i') ?? '-' }} · Status: Nonaktif (Sudah digunakan)
                        </p>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
