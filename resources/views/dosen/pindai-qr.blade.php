@extends('layouts.dashboard')

@section('content')
<!-- [MARKER M6] : HALAMAN SCANNER KAMERA QR CODE DOSEN (BAB 3.7 HALAMAN 8 / PDF HALAMAN 10) -->
<div class="space-y-6">
    <!-- Header & Navigasi M6 -->
    <div class="flex flex-col lg:flex-row lg:items-center lg:justify-between gap-4">
        <div>
            <h1 class="text-2xl font-bold text-slate-900">Pindai QR Code</h1>
            <p class="text-xs text-slate-500 mt-0.5">Validasi QR Code saat penyerahan dan pengembalian, lalu konfirmasi transaksinya.</p>
        </div>

        @include('dosen.partials.m6-nav-tabs', ['activeReturnCount' => $activeReturnCount])
    </div>

    <!-- Validasi Error Laravel (jika ada dari POST) -->
    @if($errors->any())
        <div class="bg-rose-50 border border-rose-200 text-rose-800 px-4 py-3 rounded-2xl text-xs space-y-1">
            <p class="font-bold">Konfirmasi transaksi belum dapat diproses:</p>
            <ul class="list-disc list-inside space-y-0.5">
                @foreach($errors->all() as $err)
                    <li>{{ $err }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    <!-- Main Grid: Scanner & Kemungkinan Hasil (Kiri 7 Cols) vs Detail Transaksi (Kanan 5 Cols) -->
    <div class="grid grid-cols-1 lg:grid-cols-12 gap-6 items-start">
        <!-- Kolom Kiri (7 Cols) -->
        <div class="lg:col-span-7 space-y-6">
            <!-- Card Pindai QR Code -->
            <div class="bg-white rounded-3xl p-6 sm:p-8 shadow-sm border border-slate-100 space-y-5">
                <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3">
                    <h3 class="text-base font-bold text-slate-900">Pindai QR Code</h3>

                    <!-- Switcher Tabs Penyerahan / Pengembalian -->
                    <div class="flex items-center space-x-1 bg-slate-100 p-1 rounded-2xl w-fit">
                        <button type="button"
                                onclick="setPageMode('penyerahan')"
                                id="tab-penyerahan-page"
                                class="px-5 py-1.5 rounded-xl text-xs font-semibold transition {{ $initialMode === 'penyerahan' ? 'bg-[#2563EB] text-white shadow-sm' : 'text-slate-600 hover:text-slate-900' }}">
                            Penyerahan
                        </button>
                        <button type="button"
                                onclick="setPageMode('pengembalian')"
                                id="tab-pengembalian-page"
                                class="px-5 py-1.5 rounded-xl text-xs font-semibold transition {{ $initialMode === 'pengembalian' ? 'bg-[#2563EB] text-white shadow-sm' : 'text-slate-600 hover:text-slate-900' }}">
                            Pengembalian
                        </button>
                    </div>
                </div>

                <!-- Alert Penanganan Kamera / Izin / HTTPS -->
                <div id="camera-status-box" class="hidden p-4 rounded-2xl border text-xs leading-relaxed">
                    <p id="camera-status-title" class="font-bold mb-1"></p>
                    <p id="camera-status-message"></p>
                </div>

                <!-- Scanner Viewport Area -->
                <div class="border-2 border-dashed border-blue-300 rounded-3xl p-6 sm:p-8 text-center bg-blue-50/25 space-y-4">
                    <!-- Area Video Kamera Real-Time (Html5Qrcode) -->
                    <div id="qr-reader-wrapper" class="hidden max-w-md mx-auto">
                        <div id="qr-reader" class="rounded-2xl overflow-hidden border border-blue-200 bg-slate-900"></div>
                        <p class="text-[11px] text-slate-500 mt-2">Posisikan QR Code mahasiswa di dalam kotak pemindai.</p>
                    </div>

                    <!-- Placeholder Sebelum Kamera Aktif -->
                    <div id="qr-camera-placeholder" class="py-4">
                        <div class="w-16 h-16 mx-auto mb-3 rounded-2xl bg-blue-100 text-[#2563EB] flex items-center justify-center">
                            <svg class="w-8 h-8" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 9a2 2 0 012-2h.93a2 2 0 001.664-.89l.812-1.22A2 2 0 0110.07 4h3.86a2 2 0 011.664.89l.812 1.22A2 2 0 0018.07 7H19a2 2 0 012 2v9a2 2 0 01-2 2H5a2 2 0 01-2-2V9z"></path>
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 13a3 3 0 11-6 0 3 3 0 016 0z"></path>
                            </svg>
                        </div>
                        <p class="text-xs text-[#2563EB] font-semibold">Arahkan kamera ke QR Code Mahasiswa</p>
                        <p class="text-[11px] text-slate-500 mt-1">Klik tombol Aktifkan Kamera atau masukkan token QR secara manual jika kamera tidak tersedia.</p>
                    </div>

                    <!-- Tombol Kontrol Kamera -->
                    <div class="flex flex-wrap items-center justify-center gap-2">
                        <button type="button"
                                id="btn-start-camera"
                                onclick="startQrCamera()"
                                class="inline-flex items-center gap-1.5 px-4 py-2 rounded-xl bg-[#2563EB] hover:bg-blue-700 text-white text-xs font-semibold transition shadow-sm">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 10l4.553-2.276A1 1 0 0121 8.618v6.764a1 1 0 01-1.447.894L15 14M5 18h8a2 2 0 002-2V8a2 2 0 00-2-2H5a2 2 0 00-2 2v8a2 2 0 002 2z"></path>
                            </svg>
                            <span>Aktifkan Kamera</span>
                        </button>

                        <button type="button"
                                id="btn-stop-camera"
                                onclick="stopQrCamera()"
                                class="hidden inline-flex items-center gap-1.5 px-4 py-2 rounded-xl bg-slate-800 hover:bg-slate-900 text-white text-xs font-semibold transition">
                            <span>Hentikan Kamera</span>
                        </button>
                    </div>

                    <!-- Fallback Input Token Manual -->
                    <div class="max-w-sm mx-auto pt-3 border-t border-blue-200/60">
                        <label for="manual-token" class="block text-[11px] font-semibold text-slate-600 mb-1.5 text-left">
                            Input Token QR Manual (Fallback Non-Kamera)
                        </label>
                        <div class="flex gap-2">
                            <input type="text"
                                   id="manual-token"
                                   value="{{ $prefilledToken }}"
                                   placeholder="Contoh: QR-SERAH-XXXXXXXXXXXX"
                                   class="flex-1 px-3 py-2 rounded-xl border border-slate-200 text-xs font-mono focus:outline-none focus:ring-2 focus:ring-[#2563EB] bg-white">
                            <button type="button"
                                    id="btn-validate-manual"
                                    onclick="validateManualToken()"
                                    class="px-4 py-2 rounded-xl bg-[#0C1A40] hover:bg-slate-900 text-white text-xs font-semibold transition shrink-0">
                                Cek Validitas
                            </button>
                        </div>
                    </div>
                </div>

                <div class="flex items-center justify-between">
                    <button type="button"
                            onclick="resetScanner()"
                            class="px-5 py-2 rounded-xl border border-slate-200 text-slate-700 hover:bg-slate-50 text-xs font-semibold transition">
                        Pindai ulang
                    </button>

                    <span class="text-[11px] text-slate-400">
                        Format kontrak M5: <span class="font-mono text-slate-600">QR-SERAH-*</span> / <span class="font-mono text-slate-600">QR-KEMBALI-*</span>
                    </span>
                </div>
            </div>

            <!-- Card Kemungkinan Hasil Validasi (Sesuai PDF Bab 3.7 Halaman 8) -->
            <div class="bg-white rounded-3xl p-6 sm:p-8 shadow-sm border border-slate-100 space-y-4">
                <h3 class="text-base font-bold text-slate-900">Kemungkinan hasil validasi</h3>

                <!-- Status 1: QR Valid -->
                <div id="guide-status-valid" class="p-4 rounded-2xl bg-blue-50/70 border border-blue-100 space-y-1 transition">
                    <span class="inline-block px-2.5 py-0.5 rounded-full text-[10px] font-bold bg-emerald-100 text-[#059669]">
                        QR valid
                    </span>
                    <p class="text-xs text-slate-600">
                        Masih berlaku dan belum digunakan. Detail transaksi tampil di sebelah kanan.
                    </p>
                </div>

                <!-- Status 2: QR Kedaluwarsa -->
                <div id="guide-status-expired" class="p-4 rounded-2xl bg-slate-50 border border-slate-200/80 space-y-1 transition">
                    <span class="inline-block px-2.5 py-0.5 rounded-full text-[10px] font-bold bg-rose-100 text-[#DC2626]">
                        QR kedaluwarsa
                    </span>
                    <p class="text-xs text-slate-600">
                        Masa berlaku 15 menit sudah habis. Transaksi tidak dapat dikonfirmasi.
                    </p>
                </div>

                <!-- Status 3: QR Sudah Digunakan -->
                <div id="guide-status-already_used" class="p-4 rounded-2xl bg-slate-50 border border-slate-200/80 space-y-1 transition">
                    <span class="inline-block px-2.5 py-0.5 rounded-full text-[10px] font-bold bg-rose-100 text-[#DC2626]">
                        QR sudah digunakan
                    </span>
                    <p class="text-xs text-slate-600">
                        QR menjadi nonaktif segera setelah berhasil dipindai untuk mencegah transaksi ganda.
                    </p>
                </div>
            </div>
        </div>

        <!-- Kolom Kanan: Detail Transaksi & Form Konfirmasi (5 Cols) -->
        <div class="lg:col-span-5 space-y-6">
            <div class="bg-white rounded-3xl p-6 sm:p-8 shadow-sm border border-slate-100 space-y-5">
                <div class="flex items-center justify-between">
                    <h3 class="text-base font-bold text-slate-900">Detail transaksi</h3>
                    <span id="badge-mode-kanan" class="px-2.5 py-0.5 rounded-full text-[10px] font-bold bg-blue-100 text-[#2563EB]">
                        {{ $initialMode === 'pengembalian' ? 'Pengembalian' : 'Penyerahan' }}
                    </span>
                </div>

                <!-- State 1: Menunggu Pemindaian -->
                <div id="panel-idle" class="p-5 rounded-2xl bg-slate-50 border border-slate-200/80 text-center space-y-2">
                    <div class="w-10 h-10 mx-auto rounded-xl bg-slate-200/70 text-slate-500 flex items-center justify-center">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v1m6 11h2m-6 0h-2v4m0-11v3m0 0h.01M12 12h4.01M16 20h4M4 12h4m12 0h.01M5 8h2a1 1 0 001-1V5a1 1 0 00-1-1H5a1 1 0 00-1 1v2a1 1 0 001 1z"></path>
                        </svg>
                    </div>
                    <p class="text-xs font-bold text-slate-700">Belum Ada QR Dipindai</p>
                    <p class="text-[11px] text-slate-500 leading-relaxed">
                        Pindai QR Code mahasiswa menggunakan kamera atau masukkan token manual untuk menampilkan detail transaksi yang tervalidasi.
                    </p>
                </div>

                <!-- State 2: Hasil Validasi Gagal -->
                <div id="panel-error" class="hidden p-4 rounded-2xl bg-rose-50 border border-rose-200 space-y-2">
                    <div class="flex items-center gap-2">
                        <span id="error-status-badge" class="px-2.5 py-0.5 rounded-full text-[10px] font-bold bg-rose-100 text-[#DC2626]">
                            Token Tidak Valid
                        </span>
                    </div>
                    <p id="error-status-message" class="text-xs text-rose-800 font-medium leading-relaxed"></p>
                </div>

                <!-- State 3: Hasil Validasi Valid -->
                <div id="panel-valid" class="hidden space-y-5">
                    <div class="p-4 rounded-2xl bg-blue-50/70 border border-blue-100 space-y-3">
                        <div class="flex items-center justify-between gap-2">
                            <span class="px-2.5 py-0.5 rounded-full text-[10px] font-bold bg-emerald-100 text-[#059669]">
                                QR valid
                            </span>
                            <span id="valid-borrow-code" class="font-mono text-[11px] font-bold text-[#0C1A40]"></span>
                        </div>

                        <div class="text-xs text-slate-700 space-y-1.5">
                            <p><span class="font-semibold text-slate-900">Peminjam:</span> <span id="nama-peminjam">-</span></p>
                            <p><span class="font-semibold text-slate-900">Fasilitas:</span> <span id="nama-fasilitas">-</span></p>
                            <p><span class="font-semibold text-slate-900">Jenis & Jumlah:</span> <span id="detail-jumlah">-</span></p>
                            <p><span class="font-semibold text-slate-900">Jadwal:</span> <span id="detail-jadwal">-</span></p>
                            <p><span class="font-semibold text-slate-900">Keperluan:</span> <span id="detail-keperluan">-</span></p>
                            <p id="row-handover-at" class="hidden"><span class="font-semibold text-slate-900">Waktu Serah Terima:</span> <span id="detail-handover-at">-</span></p>
                            <p class="text-[11px] text-[#D97706] font-semibold pt-1">
                                Berlaku <span id="countdown-text">15:00</span> lagi. Menjadi nonaktif setelah dipindai.
                            </p>
                        </div>
                    </div>

                    <!-- Form Konfirmasi Serah Terima (Mode: penyerahan) -->
                    <form id="form-handover" method="POST" action="{{ route('dosen.pindai-qr.penyerahan') }}" class="space-y-4">
                        @csrf
                        <input type="hidden" name="token" id="handover-token-hidden">
                        <input type="hidden" name="borrow_request_id" id="handover-request-id-hidden">

                        <div>
                            <label for="handover-notes" class="block text-xs font-semibold text-slate-700 mb-1.5">Catatan Serah Terima (Opsional)</label>
                            <input type="text"
                                   name="handover_notes"
                                   id="handover-notes"
                                   placeholder="Misal: Unit lengkap beserta kabel power"
                                   class="w-full px-3.5 py-2.5 rounded-xl border border-slate-200 text-xs focus:outline-none focus:ring-2 focus:ring-[#2563EB]">
                        </div>

                        <button type="submit"
                                class="w-full py-3 rounded-xl bg-[#2563EB] hover:bg-blue-700 text-white font-semibold text-xs transition shadow-md shadow-blue-500/20">
                            Konfirmasi Serah Terima
                        </button>
                    </form>

                    <!-- Form Konfirmasi Pengembalian (Mode: pengembalian) -->
                    <form id="form-return" method="POST" action="{{ route('dosen.pindai-qr.pengembalian') }}" class="hidden space-y-4">
                        @csrf
                        <input type="hidden" name="token" id="return-token-hidden">
                        <input type="hidden" name="borrow_request_id" id="return-request-id-hidden">

                        <div>
                            <label for="kondisi-page" class="block text-xs font-semibold text-slate-700 mb-1.5">Kondisi barang / fasilitas</label>
                            <select name="condition"
                                    id="kondisi-page"
                                    onchange="updateReturnPreview()"
                                    class="w-full px-3 py-2.5 rounded-xl border border-slate-200 text-xs bg-white focus:outline-none focus:ring-2 focus:ring-[#2563EB]">
                                <option value="Baik">Baik</option>
                                <option value="Rusak Ringan">Rusak Ringan</option>
                                <option value="Rusak Berat">Rusak Berat</option>
                                <option value="Hilang">Hilang</option>
                            </select>
                        </div>

                        <!-- Breakdown Jumlah Kondisi Barang -->
                        <div id="return-qty-grid" class="grid grid-cols-3 gap-2.5">
                            <div>
                                <label for="good-qty-page" class="block text-[11px] font-semibold text-[#059669] mb-1">Kondisi Baik</label>
                                <input type="number"
                                       name="good_quantity"
                                       id="good-qty-page"
                                       value="1"
                                       min="0"
                                       oninput="updateReturnPreview()"
                                       class="w-full px-3 py-2 rounded-xl border border-slate-200 text-xs focus:outline-none focus:ring-2 focus:ring-[#2563EB]">
                            </div>
                            <div>
                                <label for="damaged-qty-page" class="block text-[11px] font-semibold text-[#D97706] mb-1">Rusak</label>
                                <input type="number"
                                       name="damaged_quantity"
                                       id="damaged-qty-page"
                                       value="0"
                                       min="0"
                                       oninput="updateReturnPreview()"
                                       class="w-full px-3 py-2 rounded-xl border border-slate-200 text-xs focus:outline-none focus:ring-2 focus:ring-[#2563EB]">
                            </div>
                            <div>
                                <label for="lost-qty-page" class="block text-[11px] font-semibold text-[#DC2626] mb-1">Hilang</label>
                                <input type="number"
                                       name="lost_quantity"
                                       id="lost-qty-page"
                                       value="0"
                                       min="0"
                                       oninput="updateReturnPreview()"
                                       class="w-full px-3 py-2 rounded-xl border border-slate-200 text-xs focus:outline-none focus:ring-2 focus:ring-[#2563EB]">
                            </div>
                        </div>

                        <div>
                            <label for="catatan-page" class="block text-xs font-semibold text-slate-700 mb-1.5">
                                Catatan pemeriksaan / kerusakan
                                <span id="notes-required-badge" class="hidden text-[#DC2626] font-bold">*Wajib</span>
                            </label>
                            <textarea name="notes"
                                      id="catatan-page"
                                      rows="2"
                                      oninput="updateReturnPreview()"
                                      placeholder="Opsional jika Baik; wajib jelaskan kerusakan/kehilangan jika ada"
                                      class="w-full px-3.5 py-2.5 rounded-xl border border-slate-200 text-xs focus:outline-none focus:ring-2 focus:ring-[#2563EB]"></textarea>
                        </div>

                        <!-- Ringkasan Pemeriksaan Sebelum Konfirmasi Akhir -->
                        <div class="p-3.5 rounded-2xl bg-slate-50 border border-slate-200/80 text-[11px] space-y-1">
                            <p class="font-bold text-[#0C1A40]">Ringkasan Pemeriksaan Akhir</p>
                            <p id="return-summary-qty" class="text-slate-600">-</p>
                            <p id="return-summary-stock" class="text-[#059669] font-semibold">-</p>
                            <p id="return-summary-warning" class="hidden text-[#DC2626] font-semibold"></p>
                        </div>

                        <div class="space-y-2 pt-1">
                            <button type="submit"
                                    id="btn-submit-return"
                                    class="w-full py-3 rounded-xl bg-[#2563EB] hover:bg-blue-700 text-white font-semibold text-xs transition shadow-md shadow-blue-500/20">
                                Konfirmasi Pengembalian
                            </button>

                            <a id="link-full-return-form"
                               href="#"
                               class="block text-center py-2 rounded-xl border border-slate-200 text-slate-700 hover:bg-slate-50 text-xs font-semibold transition">
                                Buka Form Konfirmasi Pengembalian Lengkap
                            </a>
                        </div>
                    </form>
                </div>
            </div>

            <!-- Ringkasan Antrean Aktif untuk Referensi Cepat Dosen -->
            <div class="bg-white rounded-3xl p-6 shadow-sm border border-slate-100 space-y-3">
                <div class="flex items-center justify-between">
                    <h4 class="text-xs font-bold text-[#0C1A40] uppercase tracking-wider">Antrean Transaksi Siap Pindai</h4>
                    <a href="{{ route('dosen.pengembalian.index') }}" class="text-[11px] font-semibold text-[#2563EB] hover:underline">
                        Lihat Semua
                    </a>
                </div>

                @if($pendingHandovers->isEmpty() && $activeLoans->isEmpty())
                    <p class="text-xs text-slate-400 py-2">Belum ada transaksi disetujui atau sedang dipinjam saat ini.</p>
                @else
                    <div class="space-y-2 max-h-56 overflow-y-auto pr-1">
                        @foreach($pendingHandovers as $item)
                            <div class="p-3 rounded-2xl bg-emerald-50/60 border border-emerald-100 flex items-center justify-between gap-2 text-xs">
                                <div class="min-w-0">
                                    <p class="font-bold text-slate-900 truncate">{{ $item->user?->name }} &middot; {{ $item->facility?->name }}</p>
                                    <p class="text-[11px] text-slate-500 font-mono">{{ $item->code }} &middot; Siap Diserahkan</p>
                                </div>
                                <span class="px-2 py-0.5 rounded-full text-[10px] font-bold bg-emerald-100 text-[#059669] shrink-0">Penyerahan</span>
                            </div>
                        @endforeach

                        @foreach($activeLoans as $loan)
                            <div class="p-3 rounded-2xl bg-blue-50/60 border border-blue-100 flex items-center justify-between gap-2 text-xs">
                                <div class="min-w-0">
                                    <p class="font-bold text-slate-900 truncate">{{ $loan->user?->name }} &middot; {{ $loan->facility?->name }}</p>
                                    <p class="text-[11px] text-slate-500 font-mono">{{ $loan->code }} &middot; Sedang Dipinjam</p>
                                </div>
                                <a href="{{ route('dosen.pengembalian.show', $loan->id) }}"
                                   class="px-2.5 py-1 rounded-xl bg-[#2563EB] text-white text-[10px] font-semibold hover:bg-blue-700 transition shrink-0">
                                    Periksa
                                </a>
                            </div>
                        @endforeach
                    </div>
                @endif
            </div>
        </div>
    </div>
</div>

@push('scripts')
<script src="{{ asset('js/html5-qrcode.min.js') }}"></script>
<script>
    let currentMode = @json($initialMode);
    let html5QrCode = null;
    let isScanning = false;
    let countdownInterval = null;
    let activeExpectedQty = 1;
    let activeFacilityType = 'barang';
    const selectedBorrowRequestId = @json($selectedBorrowRequest?->id);

    function setPageMode(mode) {
        currentMode = mode;
        const tabPenyerahan = document.getElementById('tab-penyerahan-page');
        const tabPengembalian = document.getElementById('tab-pengembalian-page');
        const badgeKanan = document.getElementById('badge-mode-kanan');

        if (mode === 'penyerahan') {
            tabPenyerahan.className = "px-5 py-1.5 rounded-xl text-xs font-semibold bg-[#2563EB] text-white shadow-sm transition";
            tabPengembalian.className = "px-5 py-1.5 rounded-xl text-xs font-semibold text-slate-600 hover:text-slate-900 transition";
            badgeKanan.textContent = "Penyerahan";
        } else {
            tabPengembalian.className = "px-5 py-1.5 rounded-xl text-xs font-semibold bg-[#2563EB] text-white shadow-sm transition";
            tabPenyerahan.className = "px-5 py-1.5 rounded-xl text-xs font-semibold text-slate-600 hover:text-slate-900 transition";
            badgeKanan.textContent = "Pengembalian";
        }

        const formHandover = document.getElementById('form-handover');
        const formReturn = document.getElementById('form-return');
        if (!document.getElementById('panel-valid').classList.contains('hidden')) {
            if (mode === 'penyerahan') {
                formHandover.classList.remove('hidden');
                formReturn.classList.add('hidden');
            } else {
                formHandover.classList.add('hidden');
                formReturn.classList.remove('hidden');
            }
        }
    }

    function showCameraStatus(type, title, message) {
        const box = document.getElementById('camera-status-box');
        const titleEl = document.getElementById('camera-status-title');
        const msgEl = document.getElementById('camera-status-message');

        box.classList.remove('hidden', 'bg-amber-50', 'border-amber-200', 'text-amber-800', 'bg-rose-50', 'border-rose-200', 'text-rose-800', 'bg-blue-50', 'border-blue-200', 'text-blue-800');
        if (type === 'error') {
            box.classList.add('bg-rose-50', 'border-rose-200', 'text-rose-800');
        } else if (type === 'warning') {
            box.classList.add('bg-amber-50', 'border-amber-200', 'text-amber-800');
        } else {
            box.classList.add('bg-blue-50', 'border-blue-200', 'text-blue-800');
        }

        titleEl.textContent = title;
        msgEl.textContent = message;
    }

    function hideCameraStatus() {
        document.getElementById('camera-status-box').classList.add('hidden');
    }

    async function startQrCamera() {
        hideCameraStatus();

        if (typeof Html5Qrcode === 'undefined') {
            showCameraStatus('error', 'Pustaka Scanner Tidak Tersedia', 'Modul kamera html5-qrcode gagal dimuat. Silakan gunakan input token manual di bawah.');
            return;
        }

        if (!window.isSecureContext && location.hostname !== 'localhost' && location.hostname !== '127.0.0.1') {
            showCameraStatus('warning', 'Koneksi Memerlukan HTTPS', 'Akses kamera browser hanya diizinkan pada HTTPS atau localhost. Gunakan input token manual untuk melanjutkan.');
            return;
        }

        if (!navigator.mediaDevices || !navigator.mediaDevices.getUserMedia) {
            showCameraStatus('error', 'Kamera Tidak Didukung Browser', 'Perangkat atau browser Anda tidak mendukung akses kamera WebRTC. Gunakan input token manual.');
            return;
        }

        try {
            const devices = await Html5Qrcode.getCameras();
            if (!devices || devices.length === 0) {
                showCameraStatus('error', 'Kamera Tidak Ditemukan', 'Tidak ada kamera yang terdeteksi pada perangkat ini. Gunakan input token manual.');
                return;
            }

            if (!html5QrCode) {
                html5QrCode = new Html5Qrcode("qr-reader");
            }

            document.getElementById('qr-camera-placeholder').classList.add('hidden');
            document.getElementById('qr-reader-wrapper').classList.remove('hidden');
            document.getElementById('btn-start-camera').classList.add('hidden');
            document.getElementById('btn-stop-camera').classList.remove('hidden');

            await html5QrCode.start(
                { facingMode: "environment" },
                { fps: 10, qrbox: { width: 230, height: 230 } },
                (decodedText) => {
                    document.getElementById('manual-token').value = decodedText.trim();
                    stopQrCamera();
                    verifyTokenBackend(decodedText.trim());
                },
                () => {}
            );
            isScanning = true;
        } catch (err) {
            document.getElementById('qr-camera-placeholder').classList.remove('hidden');
            document.getElementById('qr-reader-wrapper').classList.add('hidden');
            document.getElementById('btn-start-camera').classList.remove('hidden');
            document.getElementById('btn-stop-camera').classList.add('hidden');

            const name = err?.name || '';
            const msg = String(err?.message || err || '');

            if (name === 'NotAllowedError' || name === 'PermissionDeniedError' || msg.toLowerCase().includes('permission')) {
                showCameraStatus('error', 'Izin Kamera Ditolak', 'Anda menolak izin akses kamera pada browser. Aktifkan izin kamera pada pengaturan situs browser atau gunakan input token manual.');
            } else if (name === 'NotFoundError' || msg.toLowerCase().includes('not found')) {
                showCameraStatus('error', 'Perangkat Kamera Tidak Tersedia', 'Kamera tidak ditemukan atau tidak terpasang. Silakan ketik token QR secara manual.');
            } else if (name === 'NotReadableError') {
                showCameraStatus('warning', 'Kamera Sedang Digunakan', 'Kamera sedang dipakai aplikasi lain. Tutup aplikasi tersebut atau gunakan input token manual.');
            } else {
                showCameraStatus('error', 'Gagal Membuka Kamera', 'Tidak dapat mengaktifkan kamera (' + msg + '). Gunakan input token manual sebagai alternatif.');
            }
        }
    }

    async function stopQrCamera() {
        if (html5QrCode && isScanning) {
            try {
                await html5QrCode.stop();
            } catch (e) {}
            isScanning = false;
        }
        document.getElementById('qr-reader-wrapper').classList.add('hidden');
        document.getElementById('qr-camera-placeholder').classList.remove('hidden');
        document.getElementById('btn-start-camera').classList.remove('hidden');
        document.getElementById('btn-stop-camera').classList.add('hidden');
    }

    function validateManualToken() {
        const token = document.getElementById('manual-token').value.trim();
        if (!token) {
            showErrorPanel('not_found', 'Silakan pindai QR Code atau ketik token QR terlebih dahulu.');
            return;
        }
        verifyTokenBackend(token);
    }

    async function verifyTokenBackend(token) {
        resetGuideHighlights();

        try {
            const payload = {
                token: token,
                mode: currentMode
            };
            if (selectedBorrowRequestId) {
                payload.borrow_request_id = selectedBorrowRequestId;
            }

            const res = await fetch("{{ route('dosen.pindai-qr.validasi') }}", {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'Accept': 'application/json',
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
                },
                body: JSON.stringify(payload)
            });

            const data = await res.json();

            if (res.ok && data.valid) {
                renderValidTransaction(data);
            } else {
                showErrorPanel(data.status || 'invalid', data.message || 'Token QR tidak valid.');
            }
        } catch (err) {
            showErrorPanel('network_error', 'Terjadi kesalahan koneksi saat memvalidasi token ke server.');
        }
    }

    function renderValidTransaction(data) {
        document.getElementById('panel-idle').classList.add('hidden');
        document.getElementById('panel-error').classList.add('hidden');
        document.getElementById('panel-valid').classList.remove('hidden');

        highlightGuideCard('valid');
        setPageMode(data.type);

        const req = data.borrow_request;
        const mhs = data.mahasiswa;
        const fac = data.facility;

        activeExpectedQty = parseInt(req.expected_quantity || 1, 10);
        activeFacilityType = req.type || 'barang';

        document.getElementById('valid-borrow-code').textContent = req.code;
        document.getElementById('nama-peminjam').textContent = `${mhs.name} (${mhs.nim_nip})`;
        document.getElementById('nama-fasilitas').textContent = `${fac.name} [${fac.code}]`;
        document.getElementById('detail-jumlah').textContent = req.type === 'barang'
            ? `${req.type_label} · ${activeExpectedQty} unit`
            : `${req.type_label} · ${activeExpectedQty} peserta`;
        document.getElementById('detail-jadwal').textContent = `${req.borrow_date}, ${req.time_range}`;
        document.getElementById('detail-keperluan').textContent = req.purpose;

        const handoverRow = document.getElementById('row-handover-at');
        if (req.handover_at) {
            handoverRow.classList.remove('hidden');
            document.getElementById('detail-handover-at').textContent = req.handover_at;
        } else {
            handoverRow.classList.add('hidden');
        }

        // Populate form fields
        document.getElementById('handover-token-hidden').value = data.token;
        document.getElementById('handover-request-id-hidden').value = req.id;

        document.getElementById('return-token-hidden').value = data.token;
        document.getElementById('return-request-id-hidden').value = req.id;
        document.getElementById('link-full-return-form').href = data.return_form_url;

        // Reset return inspection inputs
        document.getElementById('kondisi-page').value = 'Baik';
        const qtyToCheck = activeFacilityType === 'barang' ? activeExpectedQty : 1;
        document.getElementById('good-qty-page').value = qtyToCheck;
        document.getElementById('damaged-qty-page').value = 0;
        document.getElementById('lost-qty-page').value = 0;
        document.getElementById('catatan-page').value = '';

        const qtyGrid = document.getElementById('return-qty-grid');
        if (activeFacilityType === 'barang') {
            qtyGrid.classList.remove('hidden');
        } else {
            qtyGrid.classList.add('hidden');
        }

        const formHandover = document.getElementById('form-handover');
        const formReturn = document.getElementById('form-return');
        if (data.type === 'penyerahan') {
            formHandover.classList.remove('hidden');
            formReturn.classList.add('hidden');
        } else {
            formHandover.classList.add('hidden');
            formReturn.classList.remove('hidden');
            updateReturnPreview();
        }

        startCountdown(parseInt(data.remaining_seconds || 0, 10));
    }

    function showErrorPanel(status, message) {
        stopCountdown();
        document.getElementById('panel-idle').classList.add('hidden');
        document.getElementById('panel-valid').classList.add('hidden');
        document.getElementById('panel-error').classList.remove('hidden');

        const labels = {
            not_found: 'Token Tidak Ditemukan',
            expired: 'QR Kedaluwarsa',
            already_used: 'QR Sudah Digunakan',
            wrong_type: 'Mode Transaksi Tidak Sesuai',
            wrong_borrow_request: 'Token Tidak Sesuai Peminjaman',
            invalid_status: 'Status Peminjaman Tidak Sesuai'
        };

        document.getElementById('error-status-badge').textContent = labels[status] || 'Validasi Gagal';
        document.getElementById('error-status-message').textContent = message;

        if (status === 'expired' || status === 'already_used') {
            highlightGuideCard(status);
        }
    }

    function resetGuideHighlights() {
        ['valid', 'expired', 'already_used'].forEach(key => {
            const el = document.getElementById('guide-status-' + key);
            if (!el) return;
            el.classList.remove('ring-2', 'ring-[#059669]', 'ring-[#DC2626]');
        });
    }

    function highlightGuideCard(status) {
        resetGuideHighlights();
        const el = document.getElementById('guide-status-' + status);
        if (!el) return;
        el.classList.add('ring-2', status === 'valid' ? 'ring-[#059669]' : 'ring-[#DC2626]');
    }

    function startCountdown(seconds) {
        stopCountdown();
        let remaining = Math.max(0, seconds);
        const textEl = document.getElementById('countdown-text');

        const renderTime = () => {
            const m = String(Math.floor(remaining / 60)).padStart(2, '0');
            const s = String(remaining % 60).padStart(2, '0');
            textEl.textContent = `${m}:${s}`;
        };

        renderTime();
        countdownInterval = setInterval(() => {
            if (remaining <= 0) {
                stopCountdown();
                showErrorPanel('expired', 'Masa berlaku 15 menit sudah habis. Minta mahasiswa memperbarui QR Code.');
                return;
            }
            remaining -= 1;
            renderTime();
        }, 1000);
    }

    function stopCountdown() {
        if (countdownInterval) {
            clearInterval(countdownInterval);
            countdownInterval = null;
        }
    }

    function updateReturnPreview() {
        const condition = document.getElementById('kondisi-page').value;
        const goodQty = parseInt(document.getElementById('good-qty-page').value || 0, 10);
        const damagedQty = parseInt(document.getElementById('damaged-qty-page').value || 0, 10);
        const lostQty = parseInt(document.getElementById('lost-qty-page').value || 0, 10);
        const notes = document.getElementById('catatan-page').value.trim();

        const requiredBadge = document.getElementById('notes-required-badge');
        const notesRequired = condition !== 'Baik' || damagedQty > 0 || lostQty > 0;
        if (notesRequired) {
            requiredBadge.classList.remove('hidden');
        } else {
            requiredBadge.classList.add('hidden');
        }

        const expected = activeFacilityType === 'barang' ? activeExpectedQty : 1;
        const totalInput = goodQty + damagedQty + lostQty;

        document.getElementById('return-summary-qty').textContent = activeFacilityType === 'barang'
            ? `Dipinjam: ${expected} unit | Baik: ${goodQty}, Rusak: ${damagedQty}, Hilang: ${lostQty}`
            : `Fasilitas Lab | Kondisi: ${condition}`;

        document.getElementById('return-summary-stock').textContent = activeFacilityType === 'barang'
            ? `Pemulihan stok: +${goodQty} unit (rusak/hilang tidak menambah stok)`
            : 'Laboratorium tidak memengaruhi stok barang.';

        const warnEl = document.getElementById('return-summary-warning');
        const submitBtn = document.getElementById('btn-submit-return');
        let warningMsg = '';

        if (activeFacilityType === 'barang' && totalInput !== expected) {
            warningMsg = `Total unit (${totalInput}) harus sama dengan jumlah dipinjam (${expected}).`;
        } else if (notesRequired && notes === '') {
            warningMsg = 'Catatan kerusakan/kehilangan wajib diisi.';
        }

        if (warningMsg) {
            warnEl.textContent = warningMsg;
            warnEl.classList.remove('hidden');
        } else {
            warnEl.classList.add('hidden');
        }
        submitBtn.disabled = false;
    }

    function resetScanner() {
        stopQrCamera();
        stopCountdown();
        resetGuideHighlights();
        hideCameraStatus();
        document.getElementById('manual-token').value = '';
        document.getElementById('panel-valid').classList.add('hidden');
        document.getElementById('panel-error').classList.add('hidden');
        document.getElementById('panel-idle').classList.remove('hidden');
    }

    document.addEventListener('DOMContentLoaded', function () {
        const prefilled = document.getElementById('manual-token').value.trim();
        if (prefilled !== '') {
            verifyTokenBackend(prefilled);
        }
    });
</script>
@endpush
@endsection
