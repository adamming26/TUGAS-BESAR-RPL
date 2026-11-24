@extends('layouts.dashboard')

@section('content')
<!-- [MARKER M5] : TAMPILAN KHUSUS QR CODE TOKEN MAHASISWA -->
<div class="max-w-xl mx-auto space-y-6">
    <div class="flex items-center justify-between">
        <a href="{{ route('mahasiswa.peminjaman-saya') }}" class="text-xs font-semibold text-[#2563EB] hover:underline flex items-center space-x-1">
            <span>&larr;</span>
            <span>Kembali ke Peminjaman Saya</span>
        </a>
        <span class="text-xs font-mono text-slate-400 font-medium">[{{ $borrowRequest->code }}]</span>
    </div>

    <div class="bg-white rounded-3xl p-8 sm:p-10 shadow-sm border border-slate-100 text-center">
        <!-- Badge Status QR -->
        @if($qrToken->isExpired())
            <div class="inline-block px-3 py-1 rounded-full text-xs font-bold uppercase tracking-wider mb-3 bg-rose-50 text-rose-700 border border-rose-200">
                QR Code Kedaluwarsa
            </div>
        @else
            <div class="inline-block px-3 py-1 rounded-full text-xs font-bold uppercase tracking-wider mb-3 bg-blue-50 text-[#2563EB] border border-blue-200">
                QR Code {{ $qrToken->type === 'penyerahan' ? 'Penyerahan Fasilitas' : 'Pengembalian Fasilitas' }}
            </div>
        @endif

        <h2 class="text-2xl font-bold text-slate-900 mb-1">
            {{ $borrowRequest->facility ? $borrowRequest->facility->name : 'Fasilitas Terkait' }}
        </h2>
        <p class="text-xs text-slate-500 mb-6">
            @if($qrToken->isExpired())
                Masa berlaku 15 menit telah habis. Silakan klik tombol "Perbarui QR Code" di bawah untuk mendapatkan kode baru.
            @else
                Tunjukkan kode ini kepada Dosen saat {{ $qrToken->type === 'penyerahan' ? 'penyerahan' : 'pengembalian' }}.
            @endif
        </p>

        <!-- QR Code Visual Display Box -->
        <div class="p-6 bg-slate-50 rounded-3xl border border-slate-200/80 inline-block mb-6 shadow-inner">
            @if($qrToken->isExpired())
                <div class="w-48 h-48 mx-auto rounded-2xl bg-rose-50 border-2 border-dashed border-rose-300 flex flex-col items-center justify-center p-6 text-rose-600">
                    <svg class="w-12 h-12 text-rose-500 mb-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                    </svg>
                    <span class="font-bold text-sm text-rose-800">KEDALUWARSA</span>
                    <span class="text-[10px] text-rose-500 mt-0.5">Batas 15 menit selesai</span>
                </div>
            @else
                <div class="p-3 bg-white border-2 border-slate-900 rounded-2xl shadow-sm">
                    <svg class="w-48 h-48" viewBox="0 0 100 100" fill="currentColor">
                        <rect x="5" y="5" width="25" height="25" rx="3" fill="#0C1A40"/>
                        <rect x="10" y="10" width="15" height="15" fill="#fff"/>
                        <rect x="13" y="13" width="9" height="9" fill="#0C1A40"/>

                        <rect x="70" y="5" width="25" height="25" rx="3" fill="#0C1A40"/>
                        <rect x="75" y="10" width="15" height="15" fill="#fff"/>
                        <rect x="78" y="13" width="9" height="9" fill="#0C1A40"/>

                        <rect x="5" y="70" width="25" height="25" rx="3" fill="#0C1A40"/>
                        <rect x="10" y="75" width="15" height="15" fill="#fff"/>
                        <rect x="13" y="78" width="9" height="9" fill="#0C1A40"/>

                        <rect x="35" y="10" width="8" height="8" fill="#0C1A40"/>
                        <rect x="48" y="15" width="6" height="6" fill="#0C1A40"/>
                        <rect x="35" y="25" width="12" height="6" fill="#0C1A40"/>
                        <rect x="10" y="40" width="8" height="8" fill="#0C1A40"/>
                        <rect x="25" y="45" width="6" height="10" fill="#0C1A40"/>
                        <rect x="40" y="40" width="20" height="20" rx="2" fill="#2563EB"/>
                        <rect x="70" y="40" width="10" height="8" fill="#0C1A40"/>
                        <rect x="85" y="45" width="10" height="6" fill="#0C1A40"/>
                        <rect x="40" y="70" width="8" height="10" fill="#0C1A40"/>
                        <rect x="55" y="75" width="12" height="6" fill="#0C1A40"/>
                        <rect x="75" y="70" width="15" height="8" fill="#0C1A40"/>
                        <rect x="70" y="85" width="8" height="10" fill="#0C1A40"/>
                        <rect x="85" y="85" width="10" height="10" fill="#0C1A40"/>
                    </svg>
                </div>
            @endif
        </div>

        <!-- Token String -->
        <div class="mb-4">
            <span class="px-4 py-1.5 rounded-xl bg-slate-100 font-mono text-xs font-bold text-slate-800">
                {{ $qrToken->token }}
            </span>
        </div>

        <!-- Countdown / Perbarui Button -->
        <div class="space-y-4 max-w-sm mx-auto">
            @if(!$qrToken->isExpired())
                <div class="p-3 rounded-2xl bg-amber-50 border border-amber-200">
                    <p class="text-xs font-semibold text-amber-800">
                        Berlaku hingga: {{ \Carbon\Carbon::parse($qrToken->expires_at)->isoFormat('HH:mm:ss') }} WITA
                    </p>
                    <p class="text-[10px] text-amber-700/80 mt-0.5">Token QR akan hangus otomatis setelah 15 menit.</p>
                </div>
            @endif

            <form method="POST" action="{{ route('mahasiswa.generate-qr', $borrowRequest->id) }}">
                @csrf
                <button type="submit" 
                        class="w-full py-3 rounded-xl bg-[#2563EB] hover:bg-blue-700 text-white font-semibold text-xs transition shadow-sm">
                    Perbarui QR Code Baru
                </button>
            </form>
        </div>
    </div>
</div>
@endsection
