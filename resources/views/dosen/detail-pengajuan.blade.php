@extends('layouts.dashboard')

@section('content')
<!-- [MARKER M4] : DETAIL PENGAJUAN MASUK DOSEN -->
<div class="space-y-6 max-w-4xl">
    <div class="flex items-center justify-between">
        <div>
            <a href="{{ route('dosen.dashboard') }}" class="text-xs font-semibold text-[#2563EB] hover:underline flex items-center space-x-1 mb-1">
                <span>&larr;</span>
                <span>Kembali ke Dashboard Dosen</span>
            </a>
            <h1 class="text-2xl font-bold text-slate-900">Pengajuan [{{ $requestData->code }}]</h1>
        </div>
        <span class="px-3 py-1 rounded-full text-xs font-semibold bg-amber-50 text-amber-700 border border-amber-200">
            {{ ucfirst($requestData->status) }}
        </span>
    </div>

    <div class="bg-white rounded-3xl p-6 sm:p-8 shadow-sm border border-slate-100 space-y-6">
        <h3 class="text-base font-bold text-slate-900 pb-3 border-b border-slate-100">Rincian Permohonan Mahasiswa</h3>

        <div class="grid grid-cols-1 sm:grid-cols-2 gap-4 text-xs">
            <div>
                <span class="text-slate-400 block mb-1">NAMA MAHASISWA</span>
                <span class="font-bold text-slate-900 text-sm">{{ $requestData->user->name }}</span>
            </div>
            <div>
                <span class="text-slate-400 block mb-1">NIM / EMAIL</span>
                <span class="font-semibold text-slate-800">{{ $requestData->user->nim_nip }} ({{ $requestData->user->email }})</span>
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
                <span class="text-slate-400 block mb-1">JUMLAH / PESERTA</span>
                <span class="font-semibold text-slate-800">{{ $requestData->items->first()?->quantity ?? $requestData->participant_count }} unit/orang</span>
            </div>
            <div>
                <span class="text-slate-400 block mb-1">WAKTU PEMAKAIAN</span>
                <span class="font-semibold text-slate-800">{{ \Carbon\Carbon::parse($requestData->borrow_date)->isoFormat('D MMMM Y') }}, {{ substr($requestData->start_time, 0, 5) }} - {{ substr($requestData->end_time, 0, 5) }} WITA</span>
            </div>
        </div>

        <div>
            <span class="text-slate-400 text-xs block mb-1">KEPERLUAN / TUJUAN</span>
            <p class="text-xs text-slate-700 bg-slate-50 p-4 rounded-2xl border border-slate-100 leading-relaxed">
                {{ $requestData->purpose }}
            </p>
        </div>

        @if($requestData->status === 'menunggu')
            <form action="{{ route('dosen.putuskan-pengajuan', $requestData->id) }}" method="POST" class="pt-4 border-t border-slate-100 space-y-4">
                @csrf
                <div>
                    <label for="notes" class="block text-xs font-semibold text-slate-700 mb-1.5">Catatan Keputusan</label>
                    <textarea name="notes" id="notes" rows="3" placeholder="Tulis catatan jika menyetujui atau alasan bila menolak..." class="w-full px-4 py-2.5 rounded-xl border border-slate-200 text-xs focus:ring-2 focus:ring-[#2563EB]"></textarea>
                </div>

                <div class="flex items-center space-x-3">
                    <button type="submit" name="action" value="setujui" class="px-6 py-2.5 rounded-xl bg-[#2563EB] hover:bg-blue-700 text-white font-semibold text-xs transition shadow-sm">
                        Setujui Pengajuan
                    </button>
                    <button type="submit" name="action" value="tolak" class="px-6 py-2.5 rounded-xl border border-rose-300 text-rose-600 hover:bg-rose-50 font-semibold text-xs transition">
                        Tolak Pengajuan
                    </button>
                </div>
            </form>
        @endif
    </div>
</div>
@endsection
