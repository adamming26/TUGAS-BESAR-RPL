@extends('layouts.dashboard')

@section('content')
<!-- [MARKER M3] & [MARKER M5] : PEMINJAMAN SAYA PERSIS FIGMA (PAGE 7) -->
<div class="space-y-6">
    <!-- Header Title -->
    <div>
        <h1 class="text-2xl font-bold text-slate-900">Peminjaman Saya</h1>
        <p class="text-xs text-slate-500 mt-0.5">Pantau pengajuan, tampilkan QR Code, dan ajukan pengembalian.</p>
    </div>

    <!-- Main Grid: Daftar Peminjaman (Kiri) & QR Code + Arti Status (Kanan) -->
    <div class="grid grid-cols-1 lg:grid-cols-12 gap-6 items-start">
        <!-- Kolom Kiri: Daftar Peminjaman (8 Cols) -->
        <div class="lg:col-span-8 bg-white rounded-3xl p-6 shadow-sm border border-slate-100">
            <h3 class="text-base font-bold text-slate-900 mb-4">Daftar peminjaman</h3>

            <!-- Filter Status Tabs & Cari Data -->
            <div class="flex flex-col sm:flex-row items-center justify-between gap-3 mb-5">
                <div class="flex flex-wrap items-center gap-1 bg-slate-100 p-1 rounded-2xl w-full sm:w-auto">
                    <a href="{{ route('mahasiswa.peminjaman-saya') }}" 
                       class="px-3.5 py-1.5 rounded-xl text-xs font-semibold transition {{ !request('status') ? 'bg-[#2563EB] text-white shadow-sm' : 'text-slate-600 hover:text-slate-900' }}">
                        Semua
                    </a>
                    <a href="{{ route('mahasiswa.peminjaman-saya', ['status' => 'menunggu']) }}" 
                       class="px-3.5 py-1.5 rounded-xl text-xs font-semibold transition {{ request('status') === 'menunggu' ? 'bg-[#2563EB] text-white shadow-sm' : 'text-slate-600 hover:text-slate-900' }}">
                        Menunggu
                    </a>
                    <a href="{{ route('mahasiswa.peminjaman-saya', ['status' => 'disetujui']) }}" 
                       class="px-3.5 py-1.5 rounded-xl text-xs font-semibold transition {{ request('status') === 'disetujui' ? 'bg-[#2563EB] text-white shadow-sm' : 'text-slate-600 hover:text-slate-900' }}">
                        Disetujui
                    </a>
                    <a href="{{ route('mahasiswa.peminjaman-saya', ['status' => 'dipinjam']) }}" 
                       class="px-3.5 py-1.5 rounded-xl text-xs font-semibold transition {{ request('status') === 'dipinjam' ? 'bg-[#2563EB] text-white shadow-sm' : 'text-slate-600 hover:text-slate-900' }}">
                        Dipinjam
                    </a>
                    <a href="{{ route('mahasiswa.peminjaman-saya', ['status' => 'ditolak']) }}" 
                       class="px-3.5 py-1.5 rounded-xl text-xs font-semibold transition {{ request('status') === 'ditolak' ? 'bg-[#2563EB] text-white shadow-sm' : 'text-slate-600 hover:text-slate-900' }}">
                        Ditolak
                    </a>
                </div>

                <form method="GET" action="{{ route('mahasiswa.peminjaman-saya') }}" class="w-full sm:w-56">
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
                            <a href="{{ route('mahasiswa.peminjaman-saya', request()->only('status')) }}" class="absolute right-2.5 top-2 text-slate-400 hover:text-slate-600 text-xs">&times;</a>
                        @endif
                    </div>
                </form>
            </div>

            <!-- Tabel Peminjaman Persis Figma -->
            <div class="overflow-x-auto">
                <table class="w-full text-left border-collapse">
                    <thead>
                        <tr class="border-b border-slate-100 text-[11px] font-semibold text-slate-400 uppercase tracking-wider">
                            <th class="py-3 px-3">Fasilitas</th>
                            <th class="py-3 px-3">Jenis</th>
                            <th class="py-3 px-3">Waktu</th>
                            <th class="py-3 px-3 text-center">Status</th>
                            <th class="py-3 px-3 text-right">Aksi</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-50 text-xs text-slate-700">
                        @forelse($requests as $req)
                            <tr class="hover:bg-slate-50/80 transition">
                                <td class="py-3.5 px-3">
                                    <div class="font-semibold text-slate-900">
                                        {{ $req->facility ? $req->facility->name : 'Fasilitas' }}
                                    </div>
                                    <div class="text-[10px] text-slate-400 font-mono mt-0.5">
                                        {{ $req->code }}
                                    </div>
                                </td>
                                <td class="py-3.5 px-3 text-slate-500 capitalize">
                                    {{ $req->type === 'ruangan' ? 'Lab' : 'Barang' }}
                                </td>
                                <td class="py-3.5 px-3 text-slate-600">
                                    <div>{{ \Carbon\Carbon::parse($req->borrow_date)->isoFormat('D MMM Y') }}</div>
                                    <div class="text-[10px] text-slate-400">{{ substr($req->start_time, 0, 5) }} - {{ substr($req->end_time, 0, 5) }} WITA</div>
                                </td>
                                <td class="py-3.5 px-3 text-center">
                                    @if($req->status === 'menunggu')
                                        <span class="inline-block px-2.5 py-1 rounded-full text-[10px] font-semibold bg-amber-50 text-amber-700 border border-amber-200">
                                            {{ $req->type === 'ruangan' ? 'Menunggu izin Dosen' : 'Menunggu Dosen' }}
                                        </span>
                                    @elseif($req->status === 'disetujui')
                                        <span class="inline-block px-2.5 py-1 rounded-full text-[10px] font-semibold bg-emerald-50 text-emerald-700 border border-emerald-200">
                                            Disetujui
                                        </span>
                                    @elseif($req->status === 'dipinjam')
                                        <span class="inline-block px-2.5 py-1 rounded-full text-[10px] font-semibold bg-blue-50 text-blue-700 border border-blue-200">
                                            Dipinjam
                                        </span>
                                    @elseif($req->status === 'ditolak')
                                        <span class="inline-block px-2.5 py-1 rounded-full text-[10px] font-semibold bg-rose-50 text-rose-700 border border-rose-200">
                                            Ditolak
                                        </span>
                                    @else
                                        <span class="inline-block px-2.5 py-1 rounded-full text-[10px] font-semibold bg-teal-50 text-teal-700 border border-teal-200">
                                            Selesai
                                        </span>
                                    @endif
                                </td>
                                <td class="py-3.5 px-3 text-right">
                                    @if($req->status === 'menunggu')
                                        <span class="text-slate-400 text-xs italic">Menunggu keputusan</span>
                                    @elseif($req->status === 'disetujui')
                                        <a href="{{ route('mahasiswa.qr-code', $req->id) }}" 
                                           class="inline-block px-3 py-1.5 rounded-xl bg-[#2563EB] hover:bg-blue-700 text-white font-medium text-xs transition shadow-sm">
                                            Tampilkan QR
                                        </a>
                                    @elseif($req->status === 'dipinjam')
                                        <form method="POST" action="{{ route('mahasiswa.ajukan-pengembalian', $req->id) }}" class="inline">
                                            @csrf
                                            <button type="submit" 
                                                    class="px-3 py-1.5 rounded-xl border border-[#2563EB] text-[#2563EB] hover:bg-blue-50 font-medium text-xs transition">
                                                Ajukan pengembalian
                                            </button>
                                        </form>
                                    @elseif($req->status === 'ditolak')
                                        <button type="button" 
                                                onclick="openConfirmModal('Catatan Penolakan Dosen', '{{ addslashes($req->reject_reason ?: 'Tidak memenuhi persyaratan peminjaman prodi.') }}', 'Tutup', false)" 
                                                class="px-3 py-1.5 rounded-xl border border-slate-200 text-slate-600 hover:bg-slate-50 font-medium text-xs transition">
                                            Lihat catatan
                                        </button>
                                    @else
                                        <span class="text-emerald-600 font-semibold text-xs">Selesai</span>
                                    @endif
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="5" class="py-8 text-center text-slate-400">
                                    Belum ada transaksi peminjaman dalam kategori ini.
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>

        <!-- Kolom Kanan: QR Code Widget & Arti Status (4 Cols) -->
        <div class="lg:col-span-4 space-y-6">
            <!-- QR Code Peminjaman Minimalis & Scannable -->
            <div class="bg-white rounded-3xl p-6 shadow-sm border border-slate-200/80 text-center">
                <h3 class="text-base font-bold text-slate-900 mb-3 tracking-tight">QR Code Peminjaman</h3>

                @if($activeRequest && $activeQr)
                    <!-- Tampilan QR Code Kontras Tinggi -->
                    <div class="flex justify-center mb-3">
                        <div class="p-3 bg-white border border-slate-200 rounded-2xl shadow-sm inline-block">
                            <div id="peminjaman-qrcode-target" class="flex items-center justify-center p-1 bg-white"></div>
                            <noscript>
                                <span class="font-mono text-xs font-bold">{{ $activeQr->token }}</span>
                            </noscript>
                        </div>
                    </div>

                    <!-- Token Monospace Pill -->
                    <div class="mb-2.5">
                        <span class="inline-block px-3 py-1 rounded-lg bg-slate-100 font-mono text-[11px] font-bold text-slate-800 border border-slate-200">
                            {{ $activeQr->token }}
                        </span>
                    </div>

                    <div class="mb-3">
                        @if($activeQr->isExpired())
                            <span class="inline-block px-3 py-1 rounded-full text-xs font-semibold bg-rose-50 text-rose-700 border border-rose-200">
                                Kedaluwarsa
                            </span>
                        @else
                            <span id="peminjaman-qr-countdown" class="inline-block px-3 py-1 rounded-full text-xs font-semibold bg-amber-50 text-amber-700 border border-amber-200 font-mono">
                                Berlaku 15:00 menit
                            </span>
                        @endif
                    </div>

                    <p class="text-xs text-slate-500 leading-relaxed max-w-xs mx-auto mb-4">
                        Tunjukkan kode ini kepada Dosen saat verifikasi {{ $activeQr->type === 'penyerahan' ? 'penyerahan' : 'pengembalian' }}.
                    </p>

                    <div class="space-y-2">
                        <a href="{{ route('mahasiswa.qr-code', $activeRequest->id) }}"
                           class="block w-full py-2 rounded-xl border border-slate-200 hover:bg-slate-50 text-slate-700 text-xs font-semibold transition">
                            Buka Layar Penuh QR
                        </a>

                        @if($activeRequest->status === 'dipinjam')
                            <form method="POST" action="{{ route('mahasiswa.ajukan-pengembalian', $activeRequest->id) }}">
                                @csrf
                                <button type="submit" class="w-full py-2.5 rounded-xl bg-[#2563EB] hover:bg-blue-700 text-white text-xs font-semibold transition shadow-sm">
                                    Ajukan Pengembalian
                                </button>
                            </form>
                        @endif
                    </div>
                @else
                    <div class="py-6 text-slate-400">
                        <p class="text-xs font-medium text-slate-500">Tidak ada QR Code aktif saat ini.</p>
                        <p class="text-[11px] text-slate-400 mt-0.5">QR Code akan terbit setelah pengajuan disetujui Dosen.</p>
                    </div>
                @endif
            </div>

            <!-- Card Arti Status Persis Figma -->
            <div class="bg-white rounded-3xl p-6 shadow-sm border border-slate-100">
                <h3 class="text-base font-bold text-slate-900 mb-4">Arti status</h3>

                <div class="space-y-3.5 text-xs">
                    <div class="flex items-center justify-between">
                        <span class="px-2.5 py-1 rounded-full text-[10px] font-semibold bg-amber-50 text-amber-700 border border-amber-200">
                            Menunggu
                        </span>
                        <span class="text-slate-500 text-right">Sedang diperiksa Dosen</span>
                    </div>

                    <div class="flex items-center justify-between">
                        <span class="px-2.5 py-1 rounded-full text-[10px] font-semibold bg-emerald-50 text-emerald-700 border border-emerald-200">
                            Disetujui
                        </span>
                        <span class="text-slate-500 text-right">QR Code diterbitkan</span>
                    </div>

                    <div class="flex items-center justify-between">
                        <span class="px-2.5 py-1 rounded-full text-[10px] font-semibold bg-blue-50 text-blue-700 border border-blue-200">
                            Dipinjam
                        </span>
                        <span class="text-slate-500 text-right">Sudah diserahkan</span>
                    </div>

                    <div class="flex items-center justify-between">
                        <span class="px-2.5 py-1 rounded-full text-[10px] font-semibold bg-rose-50 text-rose-700 border border-rose-200">
                            Ditolak
                        </span>
                        <span class="text-slate-500 text-right">Lihat catatan Dosen</span>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
@if($activeRequest && $activeQr && !$activeQr->isExpired())
<script src="{{ asset('js/qrcode.min.js') }}"></script>
<script>
    document.addEventListener('DOMContentLoaded', function () {
        const target = document.getElementById("peminjaman-qrcode-target");
        if (target && typeof QRCode !== 'undefined') {
            new QRCode(target, {
                text: "{{ $activeQr->token }}",
                width: 140,
                height: 140,
                colorDark: "#000000",
                colorLight: "#ffffff",
                correctLevel: QRCode.CorrectLevel.M
            });
        }

        const expiresAt = new Date("{{ \Carbon\Carbon::parse($activeQr->expires_at)->toIso8601String() }}").getTime();
        const countdownEl = document.getElementById("peminjaman-qr-countdown");

        function updateCountdown() {
            const now = new Date().getTime();
            const distance = expiresAt - now;

            if (distance <= 0) {
                if (countdownEl) {
                    countdownEl.textContent = "Kedaluwarsa";
                    countdownEl.className = "inline-block px-3 py-1 rounded-full text-xs font-semibold bg-rose-50 text-rose-700 border border-rose-200 font-mono";
                }
                return;
            }

            const minutes = Math.floor((distance % (1000 * 60 * 60)) / (1000 * 60));
            const seconds = Math.floor((distance % (1000 * 60)) / 1000);
            const formatted = String(minutes).padStart(2, '0') + ":" + String(seconds).padStart(2, '0');

            if (countdownEl) {
                countdownEl.textContent = "Berlaku " + formatted + " menit";
            }
        }

        updateCountdown();
        setInterval(updateCountdown, 1000);
    });
</script>
@endif
@endpush
