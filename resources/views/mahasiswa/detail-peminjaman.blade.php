@extends('layouts.dashboard')

@section('content')
<!-- [MARKER M3] : DETAIL PEMINJAMAN MAHASISWA -->
<div class="space-y-6 max-w-5xl">
    <div class="flex items-center justify-between">
        <div>
            <a href="{{ route('mahasiswa.peminjaman-saya') }}" class="text-xs font-semibold text-[#2563EB] hover:underline flex items-center space-x-1 mb-1">
                <span>&larr;</span>
                <span>Kembali ke Peminjaman Saya</span>
            </a>
            <h1 class="text-2xl font-bold text-slate-900">Detail Peminjaman [{{ $requestData->code }}]</h1>
        </div>
        <div>
            @if($requestData->status === 'menunggu')
                <span class="px-3 py-1 rounded-full bg-amber-50 text-amber-700 border border-amber-200 text-xs font-semibold">Menunggu Dosen</span>
            @elseif($requestData->status === 'disetujui')
                <span class="px-3 py-1 rounded-full bg-emerald-50 text-emerald-700 border border-emerald-200 text-xs font-semibold">Disetujui</span>
            @elseif($requestData->status === 'dipinjam')
                <span class="px-3 py-1 rounded-full bg-blue-50 text-blue-700 border border-blue-200 text-xs font-semibold">Sedang Dipinjam</span>
            @elseif($requestData->status === 'ditolak')
                <span class="px-3 py-1 rounded-full bg-rose-50 text-rose-700 border border-rose-200 text-xs font-semibold">Ditolak</span>
            @else
                <span class="px-3 py-1 rounded-full bg-teal-50 text-teal-700 border border-teal-200 text-xs font-semibold">Selesai</span>
            @endif
        </div>
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-12 gap-6 items-start">
        <!-- Kolom Kiri: Informasi Peminjaman (8 Cols) -->
        <div class="lg:col-span-8 bg-white rounded-3xl p-6 sm:p-8 shadow-sm border border-slate-100 space-y-6">
            <h3 class="text-base font-bold text-slate-900 pb-3 border-b border-slate-100">Informasi Peminjaman</h3>

            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4 text-xs">
                <div>
                    <span class="text-slate-400 block mb-1">KODE PENGAJUAN</span>
                    <span class="font-mono font-bold text-slate-800 text-sm">{{ $requestData->code }}</span>
                </div>
                <div>
                    <span class="text-slate-400 block mb-1">TANGGAL PINJAM</span>
                    <span class="font-semibold text-slate-800 text-sm">{{ \Carbon\Carbon::parse($requestData->borrow_date)->isoFormat('D MMMM Y') }}</span>
                </div>
                <div>
                    <span class="text-slate-400 block mb-1">FASILITAS</span>
                    <span class="font-bold text-slate-900 text-sm">{{ $requestData->facility ? $requestData->facility->name : 'Fasilitas' }}</span>
                </div>
                <div>
                    <span class="text-slate-400 block mb-1">JENIS</span>
                    <span class="px-2.5 py-0.5 rounded-full bg-slate-100 font-semibold text-slate-700 capitalize">{{ $requestData->type === 'ruangan' ? 'Laboratorium' : 'Barang' }}</span>
                </div>
                <div>
                    <span class="text-slate-400 block mb-1">KUANTITAS / PESERTA</span>
                    <span class="font-semibold text-slate-800">{{ $requestData->items->first()?->quantity ?? $requestData->participant_count }} {{ $requestData->type === 'ruangan' ? 'orang' : 'unit' }}</span>
                </div>
                <div>
                    <span class="text-slate-400 block mb-1">JAM PENGGUNAAN</span>
                    <span class="font-semibold text-slate-800">{{ substr($requestData->start_time, 0, 5) }} - {{ substr($requestData->end_time, 0, 5) }} WITA ({{ $requestData->duration_hours }} Jam)</span>
                </div>
            </div>

            <div>
                <span class="text-slate-400 text-xs block mb-1">TUJUAN PEMINJAMAN</span>
                <p class="text-xs text-slate-700 bg-slate-50 p-4 rounded-2xl border border-slate-100 leading-relaxed">
                    {{ $requestData->purpose }}
                </p>
            </div>

            @if($requestData->reject_reason)
                <div class="p-4 rounded-2xl bg-rose-50 border border-rose-100">
                    <span class="text-xs font-bold text-rose-800 block mb-1">Catatan Penolakan Dosen:</span>
                    <p class="text-xs text-rose-700">{{ $requestData->reject_reason }}</p>
                </div>
            @endif
        </div>

        <!-- Kolom Kanan: Aksi QR & Kontak (4 Cols) -->
        <div class="lg:col-span-4 space-y-6">
            @if(in_array($requestData->status, ['disetujui', 'dipinjam']))
                <div class="bg-white rounded-3xl p-6 shadow-sm border border-slate-100 text-center space-y-4">
                    <h3 class="text-sm font-bold text-slate-900">QR Code Validasi</h3>
                    <a href="{{ route('mahasiswa.qr-code', $requestData->id) }}" 
                       class="block w-full py-2.5 rounded-xl bg-[#2563EB] hover:bg-blue-700 text-white font-semibold text-xs transition shadow-sm">
                        Buka Layar QR Code
                    </a>
                </div>
            @endif

            <div class="bg-white rounded-3xl p-6 shadow-sm border border-slate-100 space-y-3">
                <h3 class="text-sm font-bold text-slate-900">Dosen Pembimbing</h3>
                <p class="text-xs text-slate-600">
                    {{ $requestData->approver ? $requestData->approver->name : 'Dosen Pembina Ruang / Barang' }}
                </p>
                <p class="text-[11px] text-slate-400">
                    Status: {{ $requestData->approver ? 'Telah meninjau permohonan' : 'Menunggu tinjauan dosen' }}
                </p>
            </div>
        </div>
    </div>
</div>
@endsection
