@extends('layouts.dashboard', ['title' => 'Detail Peminjaman ' . $requestData->code])

@section('content')
<!-- [MARKER M4] : DETAIL PEMINJAMAN SAYA & RIWAYAT STATUS -->
<div class="space-y-6 max-w-6xl">
    <!-- Header -->
    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-3">
        <div>
            <div class="flex items-center gap-3 mb-1">
                <a href="{{ route('mahasiswa.peminjaman-saya') }}" class="text-xs font-semibold text-[#2563EB] hover:underline flex items-center space-x-1">
                    <span>&larr;</span>
                    <span>Daftar Peminjaman Saya</span>
                </a>
                <span class="text-slate-300">|</span>
                <a href="{{ route('mahasiswa.riwayat') }}" class="text-xs font-semibold text-slate-500 hover:text-[#2563EB]">
                    Riwayat Peminjaman
                </a>
            </div>
            <h1 class="text-2xl font-bold text-[#0C1A40]">Detail Peminjaman {{ $requestData->code }}</h1>
        </div>
        <div>
            <x-badge :status="$requestData->status">
                @if($requestData->status === 'menunggu')
                    Menunggu Persetujuan Dosen
                @elseif($requestData->status === 'disetujui')
                    Disetujui
                @elseif($requestData->status === 'dipinjam')
                    Sedang Dipinjam
                @elseif($requestData->status === 'dikembalikan')
                    Dikembalikan
                @elseif($requestData->status === 'ditolak')
                    Ditolak
                @else
                    {{ ucfirst($requestData->status) }}
                @endif
            </x-badge>
        </div>
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-12 gap-6 items-start">
        <!-- Kolom Kiri: Informasi Peminjaman & Riwayat Status (8 Cols) -->
        <div class="lg:col-span-8 space-y-6">
            <div class="bg-white rounded-3xl p-6 sm:p-8 shadow-sm border border-slate-200/80 space-y-6">
                <h2 class="text-base font-bold text-[#0C1A40] pb-3 border-b border-slate-100">Informasi Peminjaman</h2>

                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4 text-xs">
                    <div class="p-3.5 rounded-2xl bg-slate-50 border border-slate-100">
                        <span class="text-[11px] font-semibold text-slate-400 uppercase block mb-1">Kode Pengajuan</span>
                        <span class="font-mono font-bold text-[#0C1A40] text-sm">{{ $requestData->code }}</span>
                    </div>
                    <div class="p-3.5 rounded-2xl bg-slate-50 border border-slate-100">
                        <span class="text-[11px] font-semibold text-slate-400 uppercase block mb-1">Tanggal Pengajuan</span>
                        <span class="font-semibold text-slate-800 text-sm">
                            {{ $requestData->created_at ? $requestData->created_at->isoFormat('D MMMM Y, HH:mm') : '-' }}
                        </span>
                    </div>
                    <div class="p-3.5 rounded-2xl bg-slate-50 border border-slate-100">
                        <span class="text-[11px] font-semibold text-slate-400 uppercase block mb-1">Fasilitas Diajukan</span>
                        <span class="font-bold text-slate-900 text-sm">
                            {{ $requestData->facility ? $requestData->facility->name : 'Fasilitas' }}
                        </span>
                        @if($requestData->facility?->code)
                            <span class="text-[11px] font-mono text-slate-500 block mt-0.5">{{ $requestData->facility->code }}</span>
                        @endif
                    </div>
                    <div class="p-3.5 rounded-2xl bg-slate-50 border border-slate-100">
                        <span class="text-[11px] font-semibold text-slate-400 uppercase block mb-1">Jenis &amp; Jumlah</span>
                        <span class="font-semibold text-slate-800 text-sm">
                            {{ $requestData->type === 'ruangan' ? 'Laboratorium' : 'Barang' }}
                            &middot;
                            @if($requestData->type === 'ruangan')
                                {{ $requestData->participant_count ?? 1 }} peserta
                            @else
                                {{ $requestData->items->first()?->quantity ?? 1 }} unit
                            @endif
                        </span>
                    </div>
                    <div class="p-3.5 rounded-2xl bg-slate-50 border border-slate-100">
                        <span class="text-[11px] font-semibold text-slate-400 uppercase block mb-1">Tanggal Penggunaan</span>
                        <span class="font-semibold text-slate-800 text-sm">
                            {{ \Carbon\Carbon::parse($requestData->borrow_date)->isoFormat('D MMMM Y') }}
                        </span>
                    </div>
                    <div class="p-3.5 rounded-2xl bg-slate-50 border border-slate-100">
                        <span class="text-[11px] font-semibold text-slate-400 uppercase block mb-1">Waktu Penggunaan</span>
                        <span class="font-semibold text-slate-800 text-sm">
                            {{ substr($requestData->start_time, 0, 5) }} - {{ substr($requestData->end_time, 0, 5) }} WITA
                            ({{ $requestData->duration_hours }} Jam)
                        </span>
                    </div>
                </div>

                <div>
                    <span class="text-[11px] font-semibold text-slate-400 uppercase block mb-1.5">Tujuan Peminjaman</span>
                    <p class="text-xs sm:text-sm text-slate-700 bg-slate-50 p-4 rounded-2xl border border-slate-100 leading-relaxed">
                        {{ $requestData->purpose }}
                    </p>
                </div>

                @if($requestData->reject_reason)
                    <div class="p-4 rounded-2xl bg-rose-50 border border-rose-200">
                        <span class="text-xs font-bold text-[#DC2626] block mb-1">Catatan Penolakan Dosen:</span>
                        <p class="text-xs text-rose-800">{{ $requestData->reject_reason }}</p>
                    </div>
                @endif

                @if($requestData->returnRecord)
                    <div class="p-4 rounded-2xl bg-emerald-50 border border-emerald-200 space-y-1.5">
                        <span class="text-xs font-bold text-[#059669] block">Catatan Pengembalian (M6):</span>
                        <p class="text-xs text-emerald-900">
                            Dikembalikan pada {{ \Carbon\Carbon::parse($requestData->returnRecord->return_date)->isoFormat('D MMMM Y, HH:mm') }}
                            &middot; Kondisi: <strong>{{ $requestData->returnRecord->condition }}</strong>
                            &middot; Jumlah: <strong>{{ $requestData->returnRecord->returned_quantity }}</strong>
                        </p>
                        @if($requestData->returnRecord->notes)
                            <p class="text-xs text-emerald-800">{{ $requestData->returnRecord->notes }}</p>
                        @endif
                    </div>
                @endif
            </div>

            <!-- Riwayat Status (Timeline) -->
            <div class="bg-white rounded-3xl p-6 sm:p-8 shadow-sm border border-slate-200/80">
                <h2 class="text-base font-bold text-[#0C1A40] pb-3 border-b border-slate-100 mb-5">Riwayat Status Peminjaman</h2>

                <div class="space-y-4">
                    @forelse(($statusTimeline ?? []) as $step)
                        <div class="flex items-start gap-3.5">
                            <div class="mt-0.5 shrink-0">
                                @if($step['state'] === 'done')
                                    <span class="w-6 h-6 rounded-full bg-[#059669] text-white flex items-center justify-center text-xs font-bold">&#10003;</span>
                                @elseif($step['state'] === 'current')
                                    <span class="w-6 h-6 rounded-full bg-[#2563EB] text-white flex items-center justify-center text-xs font-bold">&bull;</span>
                                @elseif($step['state'] === 'rejected')
                                    <span class="w-6 h-6 rounded-full bg-[#DC2626] text-white flex items-center justify-center text-xs font-bold">&times;</span>
                                @else
                                    <span class="w-6 h-6 rounded-full bg-slate-200 text-slate-500 flex items-center justify-center text-xs font-bold">{{ $loop->iteration }}</span>
                                @endif
                            </div>
                            <div class="flex-1 pb-3 border-b border-slate-100 last:border-b-0 last:pb-0">
                                <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-1">
                                    <h3 class="text-xs sm:text-sm font-bold text-slate-900">{{ $step['title'] }}</h3>
                                    @if($step['time'])
                                        <span class="text-[11px] text-slate-400">{{ $step['time'] }}</span>
                                    @endif
                                </div>
                                <p class="text-xs text-slate-600 mt-0.5 leading-relaxed">{{ $step['description'] }}</p>
                            </div>
                        </div>
                    @empty
                        <p class="text-xs text-slate-400 italic">Riwayat status belum tersedia.</p>
                    @endforelse
                </div>
            </div>
        </div>

        <!-- Kolom Kanan: Info Pemohon & Peninjau (4 Cols) -->
        <div class="lg:col-span-4 space-y-6">
            <div class="bg-white rounded-3xl p-6 shadow-sm border border-slate-200/80 space-y-3">
                <h3 class="text-sm font-bold text-[#0C1A40]">Pemohon</h3>
                <div class="text-xs space-y-1">
                    <p class="font-semibold text-slate-900">{{ $requestData->user?->name }}</p>
                    <p class="font-mono text-slate-500">NIM: {{ $requestData->user?->nim_nip ?: '-' }}</p>
                </div>
            </div>

            <div class="bg-white rounded-3xl p-6 shadow-sm border border-slate-200/80 space-y-2.5">
                <h3 class="text-sm font-bold text-[#0C1A40]">Dosen Penanggung Jawab</h3>
                <p class="text-xs font-semibold text-slate-800">
                    {{ $requestData->approver ? $requestData->approver->name : 'Menunggu peninjauan Dosen' }}
                </p>
                <p class="text-[11px] text-slate-500 leading-relaxed">
                    {{ $requestData->approver
                        ? 'Pengajuan ini telah ditinjau oleh Dosen melalui modul persetujuan.'
                        : 'Data pengajuan sudah tersedia untuk diperiksa pada modul persetujuan Dosen (M5).' }}
                </p>
            </div>

            <div class="bg-white rounded-3xl p-6 shadow-sm border border-slate-200/80 space-y-2.5">
                <a href="{{ route('mahasiswa.peminjaman-saya') }}"
                   class="block w-full text-center py-2.5 rounded-xl bg-[#2563EB] hover:bg-blue-700 text-white text-xs font-semibold transition">
                    Kembali ke Daftar Peminjaman
                </a>
                <a href="{{ route('mahasiswa.dashboard') }}"
                   class="block w-full text-center py-2.5 rounded-xl border border-slate-200 hover:bg-slate-50 text-slate-700 text-xs font-semibold transition">
                    Ke Dashboard Mahasiswa
                </a>
            </div>
        </div>
    </div>
</div>
@endsection
