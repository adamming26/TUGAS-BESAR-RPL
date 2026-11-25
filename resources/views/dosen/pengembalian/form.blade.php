@extends('layouts.dashboard')

@section('content')
<!-- [MARKER M6] : FORM KONFIRMASI PENGEMBALIAN & PEMERIKSAAN KONDISI (BAB 3.7 HALAMAN 9) -->
<div class="space-y-6">
    <!-- Header & Navigasi M6 -->
    <div class="flex flex-col lg:flex-row lg:items-center lg:justify-between gap-4">
        <div>
            <a href="{{ route('dosen.pengembalian.index') }}" class="text-xs font-semibold text-[#2563EB] hover:underline inline-flex items-center gap-1 mb-1">
                <span>&larr;</span>
                <span>Kembali ke Daftar Pengembalian</span>
            </a>
            <h1 class="text-2xl font-bold text-slate-900">Form Konfirmasi Pengembalian</h1>
            <p class="text-xs text-slate-500 mt-0.5">Verifikasi QR pengembalian mahasiswa, periksa kondisi barang, catat kerusakan, dan konfirmasi pemulihan stok.</p>
        </div>

        @include('dosen.partials.m6-nav-tabs', ['activeReturnCount' => $activeReturnCount])
    </div>

    @if($errors->any())
        <div class="bg-rose-50 border border-rose-200 text-rose-800 px-4 py-3 rounded-2xl text-xs space-y-1">
            <p class="font-bold">Periksa kembali isian konfirmasi pengembalian:</p>
            <ul class="list-disc list-inside space-y-0.5">
                @foreach($errors->all() as $err)
                    <li>{{ $err }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    <div class="grid grid-cols-1 lg:grid-cols-12 gap-6 items-start">
        <!-- Kolom Kiri: Data Peminjaman Aktif & Verifikasi QR Pengembalian (5 Cols) -->
        <div class="lg:col-span-5 space-y-6">
            <!-- Card Data Peminjaman Aktif -->
            <div class="bg-white rounded-3xl p-6 shadow-sm border border-slate-100 space-y-4">
                <div class="flex items-center justify-between border-b border-slate-100 pb-3">
                    <div>
                        <span class="text-[11px] font-semibold text-slate-400 uppercase tracking-wider">Transaksi Aktif</span>
                        <h3 class="text-base font-bold text-[#0C1A40] font-mono">{{ $borrowRequest->code }}</h3>
                    </div>
                    <span class="px-3 py-1 rounded-full text-[11px] font-bold bg-blue-50 text-[#2563EB] border border-blue-200">
                        Sedang Dipinjam
                    </span>
                </div>

                <div class="grid grid-cols-2 gap-3 text-xs">
                    <div>
                        <span class="text-slate-400 block">Pihak Penyerah (Mahasiswa)</span>
                        <span class="font-bold text-slate-900">{{ $borrowRequest->user?->name ?? 'Mahasiswa' }}</span>
                        <span class="block text-[11px] text-slate-500 font-mono">{{ $borrowRequest->user?->nim_nip ?? '-' }}</span>
                    </div>
                    <div>
                        <span class="text-slate-400 block">Pihak Penerima (Dosen)</span>
                        <span class="font-bold text-slate-900">{{ auth()->user()->name }}</span>
                        <span class="block text-[11px] text-slate-500 font-mono">{{ auth()->user()->nim_nip ?? '-' }}</span>
                    </div>
                    <div>
                        <span class="text-slate-400 block">Fasilitas</span>
                        <span class="font-bold text-slate-900">{{ $borrowRequest->facility?->name ?? 'Fasilitas' }}</span>
                        <span class="block text-[11px] text-slate-500 font-mono">{{ $borrowRequest->facility?->code ?? '-' }}</span>
                    </div>
                    <div>
                        <span class="text-slate-400 block">{{ $borrowRequest->type === 'barang' ? 'Jumlah Dipinjam' : 'Peserta Lab' }}</span>
                        <span class="font-bold text-[#2563EB] text-sm">
                            {{ $expectedQty }} {{ $borrowRequest->type === 'barang' ? 'unit' : 'orang' }}
                        </span>
                    </div>
                    <div>
                        <span class="text-slate-400 block">Waktu Serah Terima</span>
                        <span class="font-semibold text-slate-800">
                            {{ $handoverAt ? $handoverAt->format('d M Y H:i') : '-' }}
                        </span>
                    </div>
                    <div>
                        <span class="text-slate-400 block">Jadwal Pinjam</span>
                        <span class="font-semibold text-slate-800">
                            {{ \Carbon\Carbon::parse($borrowRequest->borrow_date)->format('d M Y') }},
                            {{ substr((string) $borrowRequest->start_time, 0, 5) }} - {{ substr((string) $borrowRequest->end_time, 0, 5) }}
                        </span>
                    </div>
                </div>

                @if($borrowRequest->type === 'barang' && $borrowRequest->facility)
                    <div class="p-3 rounded-2xl bg-slate-50 border border-slate-200/80 text-xs flex items-center justify-between">
                        <span class="text-slate-600">Stok tersedia saat ini:</span>
                        <span class="font-bold text-[#0C1A40]">{{ $borrowRequest->facility->available_stock }} / {{ $borrowRequest->facility->total_stock }} unit</span>
                    </div>
                @endif
            </div>

            <!-- Card Verifikasi QR Pengembalian Terikat borrow_request_id -->
            <div class="bg-white rounded-3xl p-6 shadow-sm border border-slate-100 space-y-4">
                <div class="flex items-center justify-between">
                    <h3 class="text-sm font-bold text-slate-900">Verifikasi QR Pengembalian</h3>
                    <span id="qr-verify-badge" class="px-2.5 py-0.5 rounded-full text-[10px] font-bold bg-amber-100 text-[#D97706]">
                        Belum Diverifikasi
                    </span>
                </div>

                <div id="form-camera-alert" class="hidden p-3 rounded-2xl border text-xs"></div>

                <div class="border-2 border-dashed border-blue-200 rounded-2xl p-4 text-center bg-blue-50/20 space-y-3">
                    <div id="form-qr-reader-wrapper" class="hidden">
                        <div id="form-qr-reader" class="rounded-xl overflow-hidden bg-slate-900"></div>
                    </div>

                    <div class="flex flex-wrap justify-center gap-2">
                        <button type="button"
                                id="btn-form-start-cam"
                                onclick="startReturnCamera()"
                                class="px-3.5 py-2 rounded-xl bg-[#2563EB] hover:bg-blue-700 text-white text-xs font-semibold transition">
                            Pindai via Kamera
                        </button>
                        <button type="button"
                                id="btn-form-stop-cam"
                                onclick="stopReturnCamera()"
                                class="hidden px-3.5 py-2 rounded-xl bg-slate-800 text-white text-xs font-semibold transition">
                            Hentikan Kamera
                        </button>
                    </div>

                    <div class="text-left pt-2 border-t border-blue-100">
                        <label for="return-token-input" class="block text-[11px] font-semibold text-slate-600 mb-1">
                            Token QR Pengembalian (<span class="font-mono">QR-KEMBALI-*</span>)
                        </label>
                        <div class="flex gap-2">
                            <input type="text"
                                   id="return-token-input"
                                   value="{{ old('token', '') }}"
                                   placeholder="Masukkan atau pindai token pengembalian..."
                                   class="flex-1 px-3 py-2 rounded-xl border border-slate-200 text-xs font-mono bg-white focus:outline-none focus:ring-2 focus:ring-[#2563EB]">
                            <button type="button"
                                    onclick="verifyReturnToken()"
                                    class="px-3.5 py-2 rounded-xl bg-[#0C1A40] hover:bg-slate-900 text-white text-xs font-semibold transition shrink-0">
                                Verifikasi
                            </button>
                        </div>
                    </div>
                </div>

                <div id="qr-verify-feedback" class="hidden p-3 rounded-2xl text-xs"></div>
            </div>
        </div>

        <!-- Kolom Kanan: Form Pemeriksaan Kondisi & Konfirmasi Akhir (7 Cols) -->
        <div class="lg:col-span-7 bg-white rounded-3xl p-6 sm:p-8 shadow-sm border border-slate-100 space-y-6">
            <div class="border-b border-slate-100 pb-4">
                <h3 class="text-base font-bold text-slate-900">Pemeriksaan Kondisi & Konfirmasi Pengembalian</h3>
                <p class="text-xs text-slate-500 mt-0.5">Stok hanya dipulihkan berdasarkan jumlah barang yang kembali dalam kondisi Baik.</p>
            </div>

            <form method="POST" action="{{ route('dosen.pindai-qr.pengembalian') }}" id="return-confirm-form" class="space-y-5">
                @csrf
                <input type="hidden" name="borrow_request_id" value="{{ $borrowRequest->id }}">
                <input type="hidden" name="token" id="return-form-token-hidden" value="{{ old('token', '') }}">

                <!-- Pilihan Kondisi Barang -->
                <div>
                    <label for="condition" class="block text-xs font-semibold text-slate-700 mb-1.5">Kondisi Fasilitas Saat Kembali</label>
                    <select name="condition"
                            id="condition"
                            onchange="handleConditionChange()"
                            class="w-full px-4 py-2.5 rounded-xl border border-slate-200 text-xs bg-white focus:outline-none focus:ring-2 focus:ring-[#2563EB]">
                        <option value="Baik" {{ old('condition', 'Baik') === 'Baik' ? 'selected' : '' }}>Baik (Layak pakai tanpa kerusakan)</option>
                        <option value="Rusak Ringan" {{ old('condition') === 'Rusak Ringan' ? 'selected' : '' }}>Rusak Ringan (Ada kerusakan ringan)</option>
                        <option value="Rusak Berat" {{ old('condition') === 'Rusak Berat' ? 'selected' : '' }}>Rusak Berat (Tidak berfungsi / perlu perbaikan)</option>
                        <option value="Hilang" {{ old('condition') === 'Hilang' ? 'selected' : '' }}>Hilang (Unit tidak kembali)</option>
                    </select>
                </div>

                <!-- Input Terpisah Jumlah Baik, Rusak, Hilang -->
                @if($borrowRequest->type === 'barang')
                    <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
                        <div class="p-4 rounded-2xl bg-emerald-50/60 border border-emerald-100">
                            <label for="good_quantity" class="block text-xs font-bold text-[#059669] mb-1">Jumlah Kembali Baik</label>
                            <input type="number"
                                   name="good_quantity"
                                   id="good_quantity"
                                   value="{{ old('good_quantity', $expectedQty) }}"
                                   min="0"
                                   max="{{ $expectedQty }}"
                                   oninput="refreshAuditSummary()"
                                   class="w-full px-3 py-2 rounded-xl border border-emerald-200 text-sm font-bold text-slate-900 bg-white focus:outline-none focus:ring-2 focus:ring-[#059669]">
                            <p class="text-[11px] text-emerald-700 mt-1">Menambah stok kembali</p>
                        </div>

                        <div class="p-4 rounded-2xl bg-amber-50/60 border border-amber-100">
                            <label for="damaged_quantity" class="block text-xs font-bold text-[#D97706] mb-1">Jumlah Rusak</label>
                            <input type="number"
                                   name="damaged_quantity"
                                   id="damaged_quantity"
                                   value="{{ old('damaged_quantity', 0) }}"
                                   min="0"
                                   max="{{ $expectedQty }}"
                                   oninput="refreshAuditSummary()"
                                   class="w-full px-3 py-2 rounded-xl border border-amber-200 text-sm font-bold text-slate-900 bg-white focus:outline-none focus:ring-2 focus:ring-[#D97706]">
                            <p class="text-[11px] text-amber-700 mt-1">Tidak menambah stok</p>
                        </div>

                        <div class="p-4 rounded-2xl bg-rose-50/60 border border-rose-100">
                            <label for="lost_quantity" class="block text-xs font-bold text-[#DC2626] mb-1">Jumlah Hilang</label>
                            <input type="number"
                                   name="lost_quantity"
                                   id="lost_quantity"
                                   value="{{ old('lost_quantity', 0) }}"
                                   min="0"
                                   max="{{ $expectedQty }}"
                                   oninput="refreshAuditSummary()"
                                   class="w-full px-3 py-2 rounded-xl border border-rose-200 text-sm font-bold text-slate-900 bg-white focus:outline-none focus:ring-2 focus:ring-[#DC2626]">
                            <p class="text-[11px] text-rose-700 mt-1">Tidak menambah stok</p>
                        </div>
                    </div>
                @else
                    <input type="hidden" name="good_quantity" id="good_quantity" value="{{ old('good_quantity', 1) }}">
                    <input type="hidden" name="damaged_quantity" id="damaged_quantity" value="{{ old('damaged_quantity', 0) }}">
                    <input type="hidden" name="lost_quantity" id="lost_quantity" value="{{ old('lost_quantity', 0) }}">
                @endif

                <!-- Catatan Kerusakan / Pemeriksaan -->
                <div>
                    <label for="notes" class="block text-xs font-semibold text-slate-700 mb-1.5">
                        Catatan Kerusakan / Pemeriksaan
                        <span id="form-notes-required" class="hidden text-[#DC2626] font-bold">*Wajib diisi</span>
                    </label>
                    <textarea name="notes"
                              id="notes"
                              rows="3"
                              oninput="refreshAuditSummary()"
                              placeholder="Jelaskan kondisi fasilitas, bagian yang rusak, atau keterangan kehilangan jika ada..."
                              class="w-full px-4 py-3 rounded-xl border border-slate-200 text-xs focus:outline-none focus:ring-2 focus:ring-[#2563EB]">{{ old('notes') }}</textarea>
                </div>

                <!-- Ringkasan Pemeriksaan Sebelum Konfirmasi Akhir -->
                <div class="p-5 rounded-2xl bg-[#F8FAFC] border border-slate-200 space-y-3">
                    <div class="flex items-center justify-between">
                        <h4 class="text-xs font-bold text-[#0C1A40] uppercase tracking-wider">Ringkasan Pemeriksaan Sebelum Konfirmasi Akhir</h4>
                        <span id="summary-valid-badge" class="px-2.5 py-0.5 rounded-full text-[10px] font-bold bg-emerald-100 text-[#059669]">Siap</span>
                    </div>

                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-2.5 text-xs text-slate-700">
                        <p><span class="text-slate-400">Kode Peminjaman:</span> <span class="font-mono font-bold text-slate-900">{{ $borrowRequest->code }}</span></p>
                        <p><span class="text-slate-400">Fasilitas:</span> <span class="font-semibold text-slate-900">{{ $borrowRequest->facility?->name }}</span></p>
                        <p><span class="text-slate-400">Kondisi Terpilih:</span> <span id="summary-condition" class="font-bold text-slate-900">Baik</span></p>
                        <p><span class="text-slate-400">Rincian Unit:</span> <span id="summary-breakdown" class="font-semibold text-slate-900">-</span></p>
                        <p><span class="text-slate-400">Pemulihan Stok:</span> <span id="summary-stock-restore" class="font-bold text-[#059669]">-</span></p>
                        <p><span class="text-slate-400">Status QR Pengembalian:</span> <span id="summary-qr-status" class="font-bold text-[#D97706]">Menunggu Verifikasi</span></p>
                    </div>

                    <p id="summary-validation-alert" class="hidden text-xs font-semibold text-[#DC2626] bg-rose-50 border border-rose-200 rounded-xl px-3 py-2"></p>
                </div>

                <div class="flex flex-col sm:flex-row items-center gap-3 pt-2">
                    <button type="submit"
                            id="btn-confirm-final"
                            class="w-full sm:flex-1 py-3 px-6 rounded-xl bg-[#2563EB] hover:bg-blue-700 disabled:bg-slate-300 disabled:cursor-not-allowed text-white font-semibold text-xs transition shadow-md shadow-blue-500/20">
                        Konfirmasi Pengembalian & Simpan Audit
                    </button>
                    <a href="{{ route('dosen.pengembalian.index') }}"
                       class="w-full sm:w-auto text-center py-3 px-5 rounded-xl border border-slate-200 text-slate-700 hover:bg-slate-50 text-xs font-semibold transition">
                        Batal
                    </a>
                </div>
            </form>
        </div>
    </div>
</div>

@push('scripts')
<script src="{{ asset('js/html5-qrcode.min.js') }}"></script>
<script>
    const borrowRequestId = {{ (int) $borrowRequest->id }};
    const isBarang = @json($borrowRequest->type === 'barang');
    const expectedTotal = {{ $borrowRequest->type === 'barang' ? (int) $expectedQty : 1 }};
    let qrVerified = false;
    let returnScanner = null;
    let isReturnCamRunning = false;

    async function startReturnCamera() {
        const alertBox = document.getElementById('form-camera-alert');
        alertBox.classList.add('hidden');

        if (typeof Html5Qrcode === 'undefined') {
            showFormCamAlert('Pustaka scanner kamera gagal dimuat. Gunakan input token manual.', 'error');
            return;
        }
        if (!window.isSecureContext && location.hostname !== 'localhost' && location.hostname !== '127.0.0.1') {
            showFormCamAlert('Kamera memerlukan koneksi HTTPS atau localhost. Gunakan input token manual.', 'warning');
            return;
        }

        try {
            const cams = await Html5Qrcode.getCameras();
            if (!cams || cams.length === 0) {
                showFormCamAlert('Kamera tidak tersedia pada perangkat ini. Gunakan input token manual.', 'error');
                return;
            }
            if (!returnScanner) {
                returnScanner = new Html5Qrcode("form-qr-reader");
            }
            document.getElementById('form-qr-reader-wrapper').classList.remove('hidden');
            document.getElementById('btn-form-start-cam').classList.add('hidden');
            document.getElementById('btn-form-stop-cam').classList.remove('hidden');

            await returnScanner.start(
                { facingMode: "environment" },
                { fps: 10, qrbox: { width: 210, height: 210 } },
                (decoded) => {
                    document.getElementById('return-token-input').value = decoded.trim();
                    stopReturnCamera();
                    verifyReturnToken();
                },
                () => {}
            );
            isReturnCamRunning = true;
        } catch (err) {
            stopReturnCamera();
            showFormCamAlert('Izin kamera ditolak atau kamera tidak dapat diakses. Gunakan input token manual.', 'error');
        }
    }

    async function stopReturnCamera() {
        if (returnScanner && isReturnCamRunning) {
            try { await returnScanner.stop(); } catch (e) {}
            isReturnCamRunning = false;
        }
        document.getElementById('form-qr-reader-wrapper').classList.add('hidden');
        document.getElementById('btn-form-start-cam').classList.remove('hidden');
        document.getElementById('btn-form-stop-cam').classList.add('hidden');
    }

    function showFormCamAlert(msg, type) {
        const box = document.getElementById('form-camera-alert');
        box.className = 'p-3 rounded-2xl border text-xs ' + (type === 'warning' ? 'bg-amber-50 border-amber-200 text-amber-800' : 'bg-rose-50 border-rose-200 text-rose-800');
        box.textContent = msg;
        box.classList.remove('hidden');
    }

    async function verifyReturnToken() {
        const token = document.getElementById('return-token-input').value.trim();
        const feedback = document.getElementById('qr-verify-feedback');
        const badge = document.getElementById('qr-verify-badge');
        const summaryQr = document.getElementById('summary-qr-status');

        if (!token) {
            qrVerified = false;
            badge.className = 'px-2.5 py-0.5 rounded-full text-[10px] font-bold bg-rose-100 text-[#DC2626]';
            badge.textContent = 'Token Kosong';
            feedback.className = 'p-3 rounded-2xl text-xs bg-rose-50 border border-rose-200 text-rose-800';
            feedback.textContent = 'Masukkan atau pindai token QR pengembalian terlebih dahulu.';
            feedback.classList.remove('hidden');
            refreshAuditSummary();
            return;
        }

        try {
            const res = await fetch("{{ route('dosen.pindai-qr.validasi') }}", {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'Accept': 'application/json',
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
                },
                body: JSON.stringify({
                    token: token,
                    mode: 'pengembalian',
                    borrow_request_id: borrowRequestId
                })
            });

            const data = await res.json();

            if (res.ok && data.valid) {
                qrVerified = true;
                document.getElementById('return-form-token-hidden').value = data.token;
                badge.className = 'px-2.5 py-0.5 rounded-full text-[10px] font-bold bg-emerald-100 text-[#059669]';
                badge.textContent = 'QR Valid';
                feedback.className = 'p-3 rounded-2xl text-xs bg-emerald-50 border border-emerald-200 text-emerald-800 font-medium';
                feedback.textContent = `Token terverifikasi untuk ${data.mahasiswa.name} (${data.borrow_request.code}). Berlaku hingga ${data.expires_at}.`;
                feedback.classList.remove('hidden');
                summaryQr.className = 'font-bold text-[#059669]';
                summaryQr.textContent = 'QR Valid & Terverifikasi';
            } else {
                qrVerified = false;
                badge.className = 'px-2.5 py-0.5 rounded-full text-[10px] font-bold bg-rose-100 text-[#DC2626]';
                badge.textContent = 'Tidak Valid';
                feedback.className = 'p-3 rounded-2xl text-xs bg-rose-50 border border-rose-200 text-rose-800 font-medium';
                feedback.textContent = data.message || 'Token QR tidak valid.';
                feedback.classList.remove('hidden');
                summaryQr.className = 'font-bold text-[#DC2626]';
                summaryQr.textContent = 'Belum Terverifikasi';
            }
        } catch (err) {
            qrVerified = false;
            feedback.className = 'p-3 rounded-2xl text-xs bg-rose-50 border border-rose-200 text-rose-800';
            feedback.textContent = 'Gagal terhubung ke server untuk validasi token.';
            feedback.classList.remove('hidden');
        }

        refreshAuditSummary();
    }

    function handleConditionChange() {
        const condition = document.getElementById('condition').value;
        if (!isBarang) {
            document.getElementById('good_quantity').value = condition === 'Baik' ? 1 : 0;
            document.getElementById('damaged_quantity').value = (condition === 'Rusak Ringan' || condition === 'Rusak Berat') ? 1 : 0;
            document.getElementById('lost_quantity').value = condition === 'Hilang' ? 1 : 0;
        }
        refreshAuditSummary();
    }

    function refreshAuditSummary() {
        const condition = document.getElementById('condition').value;
        const goodQty = parseInt(document.getElementById('good_quantity').value || 0, 10);
        const damagedQty = parseInt(document.getElementById('damaged_quantity').value || 0, 10);
        const lostQty = parseInt(document.getElementById('lost_quantity').value || 0, 10);
        const notes = document.getElementById('notes').value.trim();
        const tokenHidden = document.getElementById('return-form-token-hidden').value.trim();

        const notesRequired = condition !== 'Baik' || damagedQty > 0 || lostQty > 0;
        const reqLabel = document.getElementById('form-notes-required');
        if (notesRequired) {
            reqLabel.classList.remove('hidden');
        } else {
            reqLabel.classList.add('hidden');
        }

        document.getElementById('summary-condition').textContent = condition;
        document.getElementById('summary-breakdown').textContent = isBarang
            ? `Baik: ${goodQty} | Rusak: ${damagedQty} | Hilang: ${lostQty} (Total: ${goodQty + damagedQty + lostQty}/${expectedTotal})`
            : `1 Lab (${condition})`;

        document.getElementById('summary-stock-restore').textContent = isBarang
            ? `+${goodQty} unit dipulihkan (0 unit dari rusak/hilang)`
            : 'Tidak mengubah stok barang';

        const alertEl = document.getElementById('summary-validation-alert');
        const badgeEl = document.getElementById('summary-valid-badge');
        const errors = [];

        if (!tokenHidden && !qrVerified) {
            errors.push('Verifikasi token QR pengembalian terlebih dahulu.');
        }
        if (isBarang && (goodQty + damagedQty + lostQty) !== expectedTotal) {
            errors.push(`Total unit baik + rusak + hilang harus sama dengan ${expectedTotal} unit.`);
        }
        if (condition === 'Baik' && (damagedQty > 0 || lostQty > 0)) {
            errors.push('Pilih kondisi selain Baik jika ada unit rusak atau hilang.');
        }
        if ((condition === 'Rusak Ringan' || condition === 'Rusak Berat') && damagedQty === 0 && lostQty === 0) {
            errors.push(`Masukkan minimal 1 unit rusak untuk kondisi ${condition}.`);
        }
        if (condition === 'Hilang' && lostQty === 0) {
            errors.push('Masukkan minimal 1 unit hilang untuk kondisi Hilang.');
        }
        if (notesRequired && notes === '') {
            errors.push('Catatan kerusakan / kehilangan wajib diisi.');
        }

        if (errors.length > 0) {
            alertEl.textContent = errors[0];
            alertEl.classList.remove('hidden');
            badgeEl.className = 'px-2.5 py-0.5 rounded-full text-[10px] font-bold bg-amber-100 text-[#D97706]';
            badgeEl.textContent = 'Perlu Dilengkapi';
        } else {
            alertEl.classList.add('hidden');
            badgeEl.className = 'px-2.5 py-0.5 rounded-full text-[10px] font-bold bg-emerald-100 text-[#059669]';
            badgeEl.textContent = 'Siap Konfirmasi';
        }
    }

    document.getElementById('return-token-input').addEventListener('input', function () {
        document.getElementById('return-form-token-hidden').value = this.value.trim();
        qrVerified = false;
        refreshAuditSummary();
    });

    document.addEventListener('DOMContentLoaded', function () {
        refreshAuditSummary();
        if (document.getElementById('return-token-input').value.trim() !== '') {
            verifyReturnToken();
        }
    });
</script>
@endpush
@endsection
