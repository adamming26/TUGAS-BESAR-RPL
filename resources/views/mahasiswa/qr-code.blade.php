@extends('layouts.dashboard', ['title' => 'QR Code Peminjaman'])

@section('content')
<!-- [MARKER M5] : TAMPILAN KHUSUS QR CODE TOKEN MAHASISWA -->
<div class="max-w-xl mx-auto space-y-6">
    <!-- Header Navigasi -->
    <div class="flex items-center justify-between">
        <a href="{{ route('mahasiswa.peminjaman-saya') }}" class="text-xs font-semibold text-[#2563EB] hover:text-blue-800 flex items-center space-x-1.5 transition">
            <span>&larr;</span>
            <span>Kembali ke Peminjaman Saya</span>
        </a>
        <span class="text-xs font-mono text-slate-500 font-medium">Ref: {{ $borrowRequest->code }}</span>
    </div>

    <!-- Kartu Utama QR Code Minimalis & Profesional -->
    <div class="bg-white rounded-3xl p-6 sm:p-8 shadow-sm border border-slate-200/80 text-center">
        <!-- Status Badge -->
        <div class="mb-4">
            @if($qrToken->isExpired())
                <span class="inline-flex items-center px-3 py-1 rounded-full text-xs font-semibold bg-rose-50 text-rose-700 border border-rose-200">
                    <span class="w-1.5 h-1.5 rounded-full bg-rose-500 mr-2"></span>
                    QR Code Kedaluwarsa
                </span>
            @else
                <span class="inline-flex items-center px-3 py-1 rounded-full text-xs font-semibold bg-emerald-50 text-emerald-700 border border-emerald-200">
                    <span class="w-1.5 h-1.5 rounded-full bg-emerald-500 mr-2"></span>
                    QR Code {{ $qrToken->type === 'penyerahan' ? 'Penyerahan Fasilitas' : 'Pengembalian Fasilitas' }}
                </span>
            @endif
        </div>

        <h2 class="text-xl sm:text-2xl font-bold text-slate-900 mb-1 tracking-tight">
            {{ $borrowRequest->facility ? $borrowRequest->facility->name : 'Fasilitas Terkait' }}
        </h2>
        <p class="text-xs text-slate-500 mb-6 max-w-md mx-auto">
            @if($qrToken->isExpired())
                Masa berlaku 15 menit telah berakhir. Silakan klik tombol perbarui di bawah untuk menerbitkan kode baru.
            @else
                Tunjukkan QR Code ini kepada Dosen saat verifikasi fisik {{ $qrToken->type === 'penyerahan' ? 'penyerahan' : 'pengembalian' }}.
            @endif
        </p>

        <!-- QR Code Visual Display Box: Latar Putih Bersih, Kontras Tinggi, Quiet Zone Lebar -->
        <div class="inline-block p-5 bg-white rounded-2xl border border-slate-200 shadow-sm mb-4">
            @if($qrToken->isExpired())
                <div class="w-52 h-52 mx-auto rounded-xl bg-slate-50 border border-dashed border-rose-200 flex flex-col items-center justify-center p-6 text-rose-600">
                    <svg class="w-12 h-12 text-rose-400 mb-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                    </svg>
                    <span class="font-bold text-sm text-rose-700">KEDALUWARSA</span>
                    <span class="text-[11px] text-slate-500 mt-0.5">Batas 15 menit telah habis</span>
                </div>
            @else
                <div class="flex flex-col items-center justify-center">
                    <!-- Target Elemen Canvas / Image QRCode Riil -->
                    <div id="qrcode-target" class="flex items-center justify-center p-2 bg-white"></div>
                    <noscript>
                        <div class="p-3 bg-white border border-slate-900 rounded-xl">
                            <span class="font-mono text-xs">{{ $qrToken->token }}</span>
                        </div>
                    </noscript>
                </div>
            @endif
        </div>

        <!-- Token Monospace Box -->
        <div class="mb-6">
            <span class="inline-block px-4 py-1.5 rounded-lg bg-slate-100 font-mono text-xs font-bold text-slate-800 border border-slate-200 tracking-wider">
                {{ $qrToken->token }}
            </span>
        </div>

        <!-- Kartu Informasi Pendukung Terstruktur -->
        <div class="bg-slate-50/80 rounded-2xl border border-slate-200/80 p-4 sm:p-5 text-left mb-6 space-y-2.5 text-xs text-slate-700">
            <div class="flex justify-between items-center py-1 border-b border-slate-200/60">
                <span class="text-slate-500">Kode Pengajuan:</span>
                <span class="font-mono font-semibold text-slate-900">{{ $borrowRequest->code }}</span>
            </div>
            <div class="flex justify-between items-center py-1 border-b border-slate-200/60">
                <span class="text-slate-500">Pemohon:</span>
                <span class="font-semibold text-slate-900">{{ auth()->user()->name }} ({{ auth()->user()->nim_nip ?? 'Mahasiswa' }})</span>
            </div>
            <div class="flex justify-between items-center py-1 border-b border-slate-200/60">
                <span class="text-slate-500">Fasilitas:</span>
                <span class="font-semibold text-slate-900">
                    {{ $borrowRequest->facility ? $borrowRequest->facility->name : '-' }}
                    @if($borrowRequest->type === 'barang')
                        ({{ $borrowRequest->items->sum('quantity') ?: 1 }} unit)
                    @endif
                </span>
            </div>
            <div class="flex justify-between items-center py-1 border-b border-slate-200/60">
                <span class="text-slate-500">Jadwal Penggunaan:</span>
                <span class="font-medium text-slate-800">
                    {{ \Carbon\Carbon::parse($borrowRequest->borrow_date)->translatedFormat('d M Y') }} &bull; {{ substr($borrowRequest->start_time, 0, 5) }} - {{ substr($borrowRequest->end_time, 0, 5) }} WITA
                </span>
            </div>
            <div class="flex justify-between items-center py-1 border-b border-slate-200/60">
                <span class="text-slate-500">Waktu Terbit:</span>
                <span class="font-medium text-slate-800">{{ \Carbon\Carbon::parse($qrToken->created_at)->format('H:i:s') }} WITA</span>
            </div>
            <div class="flex justify-between items-center py-1 border-b border-slate-200/60">
                <span class="text-slate-500">Waktu Kedaluwarsa:</span>
                <span class="font-medium text-slate-800">{{ \Carbon\Carbon::parse($qrToken->expires_at)->format('H:i:s') }} WITA</span>
            </div>
            <div class="flex justify-between items-center pt-1">
                <span class="text-slate-500">Sisa Waktu QR:</span>
                @if($qrToken->isExpired())
                    <span class="font-semibold text-rose-600">Habis</span>
                @else
                    <span id="qr-live-timer" class="font-semibold text-amber-700 font-mono">Menghitung...</span>
                @endif
            </div>
        </div>

        <!-- Tombol Aksi Perbarui QR Code -->
        <div class="max-w-sm mx-auto">
            <form method="POST" action="{{ route('mahasiswa.generate-qr', $borrowRequest->id) }}">
                @csrf
                <button type="submit" 
                        class="w-full py-2.5 px-4 rounded-xl bg-[#2563EB] hover:bg-blue-700 text-white font-semibold text-xs transition shadow-sm">
                    Perbarui QR Code Baru
                </button>
            </form>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script src="{{ asset('js/qrcode.min.js') }}"></script>
<script>
    document.addEventListener('DOMContentLoaded', function () {
        @if(!$qrToken->isExpired())
            // Render Real High-Contrast Scannable QR Code
            const target = document.getElementById("qrcode-target");
            if (target && typeof QRCode !== 'undefined') {
                new QRCode(target, {
                    text: "{{ $qrToken->token }}",
                    width: 200,
                    height: 200,
                    colorDark: "#000000",
                    colorLight: "#ffffff",
                    correctLevel: QRCode.CorrectLevel.H
                });
            }

            // Real-time Countdown Timer
            const expiresAt = new Date("{{ \Carbon\Carbon::parse($qrToken->expires_at)->toIso8601String() }}").getTime();
            const timerEl = document.getElementById("qr-live-timer");

            function updateTimer() {
                const now = new Date().getTime();
                const distance = expiresAt - now;

                if (distance <= 0) {
                    if (timerEl) {
                        timerEl.textContent = "Kedaluwarsa (00:00)";
                        timerEl.className = "font-semibold text-rose-600 font-mono";
                    }
                    return;
                }

                const minutes = Math.floor((distance % (1000 * 60 * 60)) / (1000 * 60));
                const seconds = Math.floor((distance % (1000 * 60)) / 1000);
                const formatted = String(minutes).padStart(2, '0') + ":" + String(seconds).padStart(2, '0');

                if (timerEl) {
                    timerEl.textContent = formatted + " menit";
                }
            }

            updateTimer();
            setInterval(updateTimer, 1000);
        @endif
    });
</script>
@endpush
