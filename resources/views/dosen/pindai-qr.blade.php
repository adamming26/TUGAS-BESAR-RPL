@extends('layouts.dashboard')

@section('content')
<!-- [MARKER M5] : HALAMAN PINDAI QR DOSEN PERSIS FIGMA (PAGE 10) -->
<div class="space-y-6">
    <!-- Header Title -->
    <div>
        <h1 class="text-2xl font-bold text-slate-900">Pindai QR Code</h1>
        <p class="text-xs text-slate-500 mt-0.5">Validasi QR Code saat penyerahan dan pengembalian, lalu konfirmasi transaksinya.</p>
    </div>

    <!-- Main Grid: Scanner & Panduan (Kiri 7 cols) vs Detail Transaksi (Kanan 5 cols) -->
    <div class="grid grid-cols-1 lg:grid-cols-12 gap-6 items-start">
        <!-- Kolom Kiri (7 Cols) -->
        <div class="lg:col-span-7 space-y-6">
            <!-- Box Pindai QR Code Persis Figma -->
            <div class="bg-white rounded-3xl p-6 sm:p-8 shadow-sm border border-slate-100 space-y-5">
                <h3 class="text-base font-bold text-slate-900">Pindai QR Code</h3>

                <!-- Switcher Tabs Penyerahan / Pengembalian -->
                <div class="flex items-center space-x-1 bg-slate-100 p-1 rounded-2xl w-fit">
                    <button type="button" 
                            onclick="setPageMode('penyerahan')" 
                            id="tab-penyerahan-page"
                            class="px-5 py-1.5 rounded-xl text-xs font-semibold bg-[#2563EB] text-white shadow-sm transition">
                        Penyerahan
                    </button>
                    <button type="button" 
                            onclick="setPageMode('pengembalian')" 
                            id="tab-pengembalian-page"
                            class="px-5 py-1.5 rounded-xl text-xs font-semibold text-slate-600 hover:text-slate-900 transition">
                        Pengembalian
                    </button>
                </div>

                <!-- Scanner Viewport Area Persis Figma -->
                <div class="border-2 border-dashed border-blue-300 rounded-3xl p-12 text-center bg-blue-50/20">
                    <div class="w-16 h-16 mx-auto mb-3 rounded-2xl bg-blue-100 text-[#2563EB] flex items-center justify-center">
                        <svg class="w-8 h-8" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 9a2 2 0 012-2h.93a2 2 0 001.664-.89l.812-1.22A2 2 0 0110.07 4h3.86a2 2 0 011.664.89l.812 1.22A2 2 0 0018.07 7H19a2 2 0 012 2v9a2 2 0 01-2 2H5a2 2 0 01-2-2V9z"></path>
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 13a3 3 0 11-6 0 3 3 0 016 0z"></path>
                        </svg>
                    </div>
                    <p class="text-xs text-blue-600 font-semibold mb-4">Arahkan kamera ke QR Code Mahasiswa</p>

                    <div class="max-w-xs mx-auto">
                        <input type="text" 
                               id="manual-token" 
                               placeholder="Atau ketik token manual..." 
                               class="w-full px-3 py-2 rounded-xl border border-slate-200 text-xs focus:outline-none focus:ring-2 focus:ring-[#2563EB] bg-white">
                        <button type="button" 
                                onclick="validateManualToken()" 
                                class="mt-2 w-full py-1.5 rounded-xl bg-[#0C1A40] hover:bg-slate-900 text-white text-xs font-medium transition">
                            Cek Validitas Token
                        </button>
                    </div>
                </div>

                <div>
                    <button type="button" 
                            onclick="location.reload()" 
                            class="px-5 py-2 rounded-xl border border-slate-200 text-slate-700 hover:bg-slate-50 text-xs font-semibold transition">
                        Pindai ulang
                    </button>
                </div>
            </div>

            <!-- Card Kemungkinan Hasil Validasi Persis Figma -->
            <div class="bg-white rounded-3xl p-6 sm:p-8 shadow-sm border border-slate-100 space-y-4">
                <h3 class="text-base font-bold text-slate-900">Kemungkinan hasil validasi</h3>

                <!-- Status 1: QR Valid -->
                <div class="p-4 rounded-2xl bg-blue-50/70 border border-blue-100 space-y-1">
                    <span class="inline-block px-2.5 py-0.5 rounded-full text-[10px] font-bold bg-emerald-100 text-emerald-800">
                        QR valid
                    </span>
                    <p class="text-xs text-slate-600">
                        Masih berlaku dan belum digunakan. Detail transaksi tampil di sebelah kanan.
                    </p>
                </div>

                <!-- Status 2: QR Kedaluwarsa -->
                <div class="p-4 rounded-2xl bg-slate-50 border border-slate-200/80 space-y-1">
                    <span class="inline-block px-2.5 py-0.5 rounded-full text-[10px] font-bold bg-rose-100 text-rose-800">
                        QR kedaluwarsa
                    </span>
                    <p class="text-xs text-slate-600">
                        Masa berlaku 15 menit sudah habis. Transaksi tidak dapat dikonfirmasi.
                    </p>
                </div>

                <!-- Status 3: QR Sudah Digunakan -->
                <div class="p-4 rounded-2xl bg-slate-50 border border-slate-200/80 space-y-1">
                    <span class="inline-block px-2.5 py-0.5 rounded-full text-[10px] font-bold bg-rose-100 text-rose-800">
                        QR sudah digunakan
                    </span>
                    <p class="text-xs text-slate-600">
                        QR pengembalian menjadi nonaktif setelah berhasil dipindai.
                    </p>
                </div>
            </div>
        </div>

        <!-- Kolom Kanan: Detail Transaksi & Form Konfirmasi (5 Cols) Persis Figma -->
        <div class="lg:col-span-5 bg-white rounded-3xl p-6 sm:p-8 shadow-sm border border-slate-100 space-y-5">
            <h3 class="text-base font-bold text-slate-900">Detail transaksi</h3>

            <!-- Alert Card Detail Hasil Pindai -->
            <div class="p-4 rounded-2xl bg-blue-50/70 border border-blue-100 space-y-3">
                <div class="flex items-center space-x-2">
                    <span class="px-2.5 py-0.5 rounded-full text-[10px] font-bold bg-emerald-100 text-emerald-800">
                        QR valid
                    </span>
                    <span id="badge-mode-kanan" class="px-2.5 py-0.5 rounded-full text-[10px] font-bold bg-blue-100 text-[#2563EB]">
                        Pengembalian
                    </span>
                </div>

                <div class="text-xs text-slate-700 space-y-1">
                    <p><span class="font-semibold text-slate-900">Peminjam:</span> <span id="nama-peminjam">Adam Muzakir Eni (F1G125001)</span></p>
                    <p><span class="font-semibold text-slate-900">Barang:</span> <span id="nama-fasilitas">Kabel HDMI, 1 unit</span></p>
                    <p class="text-[11px] text-amber-600 font-medium">Berlaku 09:41 lagi. Menjadi nonaktif setelah dipindai.</p>
                </div>
            </div>

            <!-- Form Konfirmasi Transaksi Persis Figma -->
            <form id="form-scan-page" method="POST" action="{{ route('dosen.proses-pengembalian') }}" class="space-y-4">
                @csrf
                <input type="hidden" name="token" id="token-hidden-page" value="QR-KEMBALI-DEMO">

                <div class="grid grid-cols-2 gap-3">
                    <div>
                        <label for="kondisi-page" class="block text-xs font-semibold text-slate-700 mb-1.5">Kondisi barang</label>
                        <select name="condition" id="kondisi-page" class="w-full px-3 py-2.5 rounded-xl border border-slate-200 text-xs bg-white focus:outline-none focus:ring-2 focus:ring-[#2563EB]">
                            <option value="Baik">Baik</option>
                            <option value="Rusak Ringan">Rusak Ringan</option>
                            <option value="Rusak Berat">Rusak Berat</option>
                        </select>
                    </div>

                    <div>
                        <label for="qty-page" class="block text-xs font-semibold text-slate-700 mb-1.5">Jumlah dikembalikan</label>
                        <input type="number" name="returned_quantity" id="qty-page" value="1" min="1" class="w-full px-3 py-2.5 rounded-xl border border-slate-200 text-xs focus:outline-none focus:ring-2 focus:ring-[#2563EB]">
                    </div>
                </div>

                <div>
                    <label for="catatan-page" class="block text-xs font-semibold text-slate-700 mb-1.5">Catatan</label>
                    <input type="text" name="notes" id="catatan-page" placeholder="Opsional" class="w-full px-4 py-2.5 rounded-xl border border-slate-200 text-xs focus:outline-none focus:ring-2 focus:ring-[#2563EB]">
                </div>

                <div class="pt-2">
                    <button type="submit" class="w-full py-3 rounded-xl bg-[#2563EB] hover:bg-blue-700 text-white font-semibold text-xs transition shadow-md shadow-blue-500/20">
                        Konfirmasi transaksi
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

@push('scripts')
<script>
    function setPageMode(mode) {
        const tabPenyerahan = document.getElementById('tab-penyerahan-page');
        const tabPengembalian = document.getElementById('tab-pengembalian-page');
        const badgeKanan = document.getElementById('badge-mode-kanan');
        const form = document.getElementById('form-scan-page');

        if (mode === 'penyerahan') {
            tabPenyerahan.className = "px-5 py-1.5 rounded-xl text-xs font-semibold bg-[#2563EB] text-white shadow-sm transition";
            tabPengembalian.className = "px-5 py-1.5 rounded-xl text-xs font-semibold text-slate-600 hover:text-slate-900 transition";
            badgeKanan.textContent = "Penyerahan";
            form.action = "{{ route('dosen.proses-penyerahan') }}";
        } else {
            tabPengembalian.className = "px-5 py-1.5 rounded-xl text-xs font-semibold bg-[#2563EB] text-white shadow-sm transition";
            tabPenyerahan.className = "px-5 py-1.5 rounded-xl text-xs font-semibold text-slate-600 hover:text-slate-900 transition";
            badgeKanan.textContent = "Pengembalian";
            form.action = "{{ route('dosen.proses-pengembalian') }}";
        }
    }

    function validateManualToken() {
        const token = document.getElementById('manual-token').value.trim();
        if (!token) return alert('Silakan masukkan token terlebih dahulu.');

        fetch("{{ route('dosen.validate-qr-ajax') }}", {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
            },
            body: JSON.stringify({ token: token })
        })
        .then(res => res.json())
        .then(data => {
            if (data.valid) {
                document.getElementById('token-hidden-page').value = data.token;
                document.getElementById('nama-peminjam').textContent = data.mahasiswa + ' (' + data.nim + ')';
                document.getElementById('nama-fasilitas').textContent = data.fasilitas + ', ' + data.jumlah + ' unit';
                setPageMode(data.type);
                alert('QR Code Valid! Silakan konfirmasi transaksi di panel sebelah kanan.');
            } else {
                alert(data.message || 'Token QR tidak valid.');
            }
        })
        .catch(err => {
            console.error(err);
            alert('Terjadi kesalahan jaringan.');
        });
    }
</script>
@endpush
@endsection
