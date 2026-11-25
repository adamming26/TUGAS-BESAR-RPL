@extends('layouts.dashboard')

@section('content')
<!-- [MARKER M4] & [MARKER M5] : DASHBOARD DOSEN PERSIS FIGMA (PAGE 9) -->
<div class="space-y-6">
    <!-- Header Title -->
    <div>
        <h1 class="text-2xl font-bold text-slate-900">Dashboard Dosen</h1>
        <p class="text-xs text-slate-500 mt-0.5">Periksa pengajuan, beri keputusan, dan pindai QR Code saat penyerahan dan pengembalian.</p>
    </div>

    <!-- Main Grid: Pengajuan & Detail (Kiri 7 cols) vs Pindai QR Code (Kanan 5 cols) -->
    <div class="grid grid-cols-1 lg:grid-cols-12 gap-6 items-start">
        <!-- Kolom Kiri: Menunggu Keputusan & Detail Pengajuan (7 Cols) -->
        <div class="lg:col-span-7 space-y-6">
            <!-- Tabel Menunggu Keputusan Minimalis -->
            <div class="bg-white rounded-3xl p-6 shadow-sm border border-slate-200/80">
                <h3 class="text-base font-bold text-slate-900 mb-4 tracking-tight">Menunggu keputusan</h3>

                <div class="overflow-x-auto">
                    <table class="w-full text-left border-collapse">
                        <thead>
                            <tr class="border-b border-slate-200/60 text-[11px] font-semibold text-slate-400 uppercase tracking-wider">
                                <th class="py-3 px-3">Pemohon</th>
                                <th class="py-3 px-3">Fasilitas</th>
                                <th class="py-3 px-3">Waktu</th>
                                <th class="py-3 px-3 text-right">Status</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-100 text-xs text-slate-700">
                            @forelse($pendingRequests as $p)
                                <tr onclick="window.location='{{ route('dosen.dashboard', ['selected_id' => $p->id]) }}'" 
                                    class="cursor-pointer hover:bg-slate-50 transition {{ ($selectedRequest?->id == $p->id) ? 'bg-blue-50/50' : '' }}">
                                    <td class="py-3 px-3 font-semibold text-slate-900">
                                        {{ $p->user ? $p->user->name : 'Mahasiswa' }}
                                    </td>
                                    <td class="py-3 px-3 text-slate-600">
                                        {{ $p->facility ? $p->facility->name : 'Fasilitas' }}
                                    </td>
                                    <td class="py-3 px-3 text-slate-500">
                                        {{ \Carbon\Carbon::parse($p->borrow_date)->isoFormat('D MMM') }}, {{ substr($p->start_time, 0, 5) }}
                                    </td>
                                    <td class="py-3 px-3 text-right">
                                        <span class="inline-block px-2.5 py-1 rounded-full text-[10px] font-semibold bg-amber-50 text-amber-700 border border-amber-200">
                                            Menunggu
                                        </span>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="4" class="py-8 text-center text-slate-400">
                                        Tidak ada antrean pengajuan menunggu keputusan.
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>

            <!-- Detail Pengajuan Bersih & Rapi -->
            <div class="bg-white rounded-3xl p-6 shadow-sm border border-slate-200/80">
                <h3 class="text-base font-bold text-slate-900 mb-4 tracking-tight">Detail pengajuan</h3>

                @if($selectedRequest)
                    <!-- Alert Status Stok / Izin -->
                    <div class="p-4 rounded-2xl bg-slate-50 border border-slate-200 mb-5 space-y-2">
                        <span class="inline-block px-2.5 py-0.5 rounded-full text-[10px] font-semibold bg-blue-50 text-[#2563EB] border border-blue-200">
                            {{ $selectedRequest->type === 'barang' ? 'Stok mencukupi' : 'Izin Lab Diperlukan' }}
                        </span>
                        <div class="text-xs text-slate-700 space-y-1">
                            <p><span class="font-semibold text-slate-900">Pemohon:</span> {{ $selectedRequest->user->name }} (NIM: {{ $selectedRequest->user->nim_nip }})</p>
                            <p><span class="font-semibold text-slate-900">Fasilitas:</span> {{ $selectedRequest->facility ? $selectedRequest->facility->name : 'Fasilitas' }}, {{ $selectedRequest->items->first()?->quantity ?? $selectedRequest->participant_count }} unit/peserta</p>
                            <p><span class="font-semibold text-slate-900">Waktu:</span> {{ \Carbon\Carbon::parse($selectedRequest->borrow_date)->isoFormat('D MMMM Y') }}, {{ substr($selectedRequest->start_time, 0, 5) }} - {{ substr($selectedRequest->end_time, 0, 5) }} WITA ({{ $selectedRequest->duration_hours }} Jam)</p>
                            <p><span class="font-semibold text-slate-900">Tujuan:</span> {{ $selectedRequest->purpose }}</p>
                        </div>
                    </div>

                    <!-- Form Keputusan Catatan & Tombol -->
                    <form action="{{ route('dosen.putuskan-pengajuan', $selectedRequest->id) }}" method="POST" id="form-putusan">
                        @csrf
                        <div class="mb-4">
                            <label for="notes" class="block text-xs font-semibold text-slate-700 mb-1.5">
                                Catatan (opsional)
                            </label>
                            <textarea name="notes" 
                                      id="notes" 
                                      rows="3" 
                                      placeholder="Tulis alasan keputusan..." 
                                      class="w-full px-4 py-2.5 rounded-xl border border-slate-200 text-xs focus:outline-none focus:ring-2 focus:ring-[#2563EB]"></textarea>
                        </div>

                        <div class="flex items-center space-x-3">
                            <button type="submit" 
                                    name="action" 
                                    value="setujui" 
                                    class="px-6 py-2.5 rounded-xl bg-[#2563EB] hover:bg-blue-700 text-white font-semibold text-xs transition shadow-sm">
                                Setujui
                            </button>
                            <button type="submit" 
                                    name="action" 
                                    value="tolak" 
                                    class="px-6 py-2.5 rounded-xl border border-rose-300 text-rose-600 hover:bg-rose-50 font-semibold text-xs transition">
                                Tolak
                            </button>
                        </div>
                    </form>
                @else
                    <p class="text-xs text-slate-400 py-6 text-center">Pilih salah satu pengajuan untuk melihat detail.</p>
                @endif
            </div>
        </div>

        <!-- Kolom Kanan: Pindai QR Code & Konfirmasi Transaksi (5 Cols) Minimalis -->
        <div class="lg:col-span-5 bg-white rounded-3xl p-6 shadow-sm border border-slate-200/80 space-y-5">
            <h3 class="text-base font-bold text-slate-900 tracking-tight">Pindai QR Code</h3>

            <!-- Switcher Tabs Penyerahan / Pengembalian -->
            <div class="flex items-center space-x-1 bg-slate-100 p-1 rounded-2xl w-full">
                <button type="button" 
                        onclick="setScanMode('penyerahan')" 
                        id="tab-penyerahan"
                        class="flex-1 py-1.5 rounded-xl text-xs font-semibold bg-[#2563EB] text-white shadow-sm transition">
                    Penyerahan
                </button>
                <button type="button" 
                        onclick="setScanMode('pengembalian')" 
                        id="tab-pengembalian"
                        class="flex-1 py-1.5 rounded-xl text-xs font-semibold text-slate-600 hover:text-slate-900 transition">
                    Pengembalian
                </button>
            </div>

            <!-- Kamera Scanner Box Mockup Minimalis -->
            <div class="border border-dashed border-slate-300 rounded-2xl p-5 text-center bg-slate-50/70">
                <div class="w-12 h-12 mx-auto mb-2 rounded-xl bg-slate-200/80 text-slate-600 flex items-center justify-center">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 9a2 2 0 012-2h.93a2 2 0 001.664-.89l.812-1.22A2 2 0 0110.07 4h3.86a2 2 0 011.664.89l.812 1.22A2 2 0 0018.07 7H19a2 2 0 012 2v9a2 2 0 01-2 2H5a2 2 0 01-2-2V9z"></path>
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 13a3 3 0 11-6 0 3 3 0 016 0z"></path>
                    </svg>
                </div>
                <p class="text-xs text-slate-600 font-medium">Arahkan kamera ke QR Code Mahasiswa</p>
                <div class="mt-3">
                    <input type="text" 
                           id="manual-token-input" 
                           placeholder="Atau masukkan kode token manual..." 
                           class="w-full px-3 py-2 rounded-xl border border-slate-200 text-xs focus:outline-none focus:ring-2 focus:ring-[#2563EB]">
                    <button type="button" 
                            onclick="validateTokenManual()" 
                            class="mt-2 w-full py-2 rounded-xl bg-[#0C1A40] hover:bg-slate-900 text-white text-xs font-medium transition shadow-sm">
                        Periksa Token
                    </button>
                </div>
            </div>

            <!-- Form Detail Transaksi & Konfirmasi -->
            <div id="scan-result-container" class="space-y-4">
                <div class="flex items-center space-x-2">
                    <span id="badge-qr-valid" class="px-2.5 py-0.5 rounded-full text-[10px] font-semibold bg-emerald-50 text-emerald-700 border border-emerald-200">
                        QR valid
                    </span>
                    <span id="badge-scan-type" class="px-2.5 py-0.5 rounded-full text-[10px] font-semibold bg-blue-50 text-[#2563EB] border border-blue-200">
                        Penyerahan
                    </span>
                </div>

                <div class="text-xs text-slate-700 space-y-1">
                    <p><span class="font-semibold text-slate-900">Peminjam:</span> <span id="res-peminjam">Adam Muzakir Eni</span></p>
                    <p><span class="font-semibold text-slate-900">Barang:</span> <span id="res-fasilitas">Kabel HDMI, 1 unit</span></p>
                    <p class="text-[11px] text-amber-700 font-medium">Berlaku 15 menit. Menjadi nonaktif setelah dipindai.</p>
                </div>

                <!-- Form Konfirmasi Penyerahan / Pengembalian -->
                <form id="scan-confirm-form" method="POST" action="{{ route('dosen.proses-penyerahan') }}" class="space-y-3 pt-2 border-t border-slate-100">
                    @csrf
                    <input type="hidden" name="token" id="form-token-hidden" value="QR-SERAH-DEMO">

                    <div id="return-fields" class="space-y-3 hidden">
                        <div class="grid grid-cols-2 gap-3">
                            <div>
                                <label for="kondisi" class="block text-[11px] font-semibold text-slate-700 mb-1">Kondisi barang</label>
                                <select name="condition" id="kondisi" class="w-full px-3 py-2 rounded-xl border border-slate-200 text-xs bg-white">
                                    <option value="Baik">Baik</option>
                                    <option value="Rusak Ringan">Rusak Ringan</option>
                                    <option value="Rusak Berat">Rusak Berat</option>
                                </select>
                            </div>
                            <div>
                                <label for="qty_kembali" class="block text-[11px] font-semibold text-slate-700 mb-1">Jumlah dikembalikan</label>
                                <input type="number" name="returned_quantity" id="qty_kembali" value="1" min="1" class="w-full px-3 py-2 rounded-xl border border-slate-200 text-xs">
                            </div>
                        </div>

                        <div>
                            <label for="catatan_kembali" class="block text-[11px] font-semibold text-slate-700 mb-1">Catatan</label>
                            <input type="text" name="notes" id="catatan_kembali" placeholder="Opsional" class="w-full px-3 py-2 rounded-xl border border-slate-200 text-xs">
                        </div>
                    </div>

                    <button type="submit" 
                            id="btn-konfirmasi"
                            class="w-full py-2.5 rounded-xl bg-[#2563EB] hover:bg-blue-700 text-white font-semibold text-xs transition shadow-sm">
                        Konfirmasi transaksi
                    </button>
                </form>
            </div>
        </div>
    </div>
</div>

@push('scripts')
<script>
    let currentMode = 'penyerahan';

    function setScanMode(mode) {
        currentMode = mode;
        const tabPenyerahan = document.getElementById('tab-penyerahan');
        const tabPengembalian = document.getElementById('tab-pengembalian');
        const badgeType = document.getElementById('badge-scan-type');
        const returnFields = document.getElementById('return-fields');
        const form = document.getElementById('scan-confirm-form');

        if (mode === 'penyerahan') {
            tabPenyerahan.className = "flex-1 py-1.5 rounded-xl text-xs font-semibold bg-[#2563EB] text-white shadow-sm transition";
            tabPengembalian.className = "flex-1 py-1.5 rounded-xl text-xs font-semibold text-slate-600 hover:text-slate-900 transition";
            badgeType.textContent = "Penyerahan";
            returnFields.classList.add('hidden');
            form.action = "{{ route('dosen.proses-penyerahan') }}";
        } else {
            tabPengembalian.className = "flex-1 py-1.5 rounded-xl text-xs font-semibold bg-[#2563EB] text-white shadow-sm transition";
            tabPenyerahan.className = "flex-1 py-1.5 rounded-xl text-xs font-semibold text-slate-600 hover:text-slate-900 transition";
            badgeType.textContent = "Pengembalian";
            returnFields.classList.remove('hidden');
            form.action = "{{ route('dosen.proses-pengembalian') }}";
        }
    }

    function validateTokenManual() {
        const tokenInput = document.getElementById('manual-token-input').value.trim();
        if (!tokenInput) {
            alert('Silakan masukkan token terlebih dahulu.');
            return;
        }

        fetch("{{ route('dosen.validate-qr-ajax') }}", {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
            },
            body: JSON.stringify({ token: tokenInput })
        })
        .then(res => res.json())
        .then(data => {
            if (data.valid) {
                document.getElementById('form-token-hidden').value = data.token;
                document.getElementById('res-peminjam').textContent = data.mahasiswa + ' (' + data.nim + ')';
                document.getElementById('res-fasilitas').textContent = data.fasilitas + ', ' + data.jumlah + ' unit';
                setScanMode(data.type);
                alert('QR Code Valid! Silakan klik Konfirmasi transaksi.');
            } else {
                alert(data.message || 'Token QR tidak valid atau kedaluwarsa.');
            }
        })
        .catch(err => {
            console.error(err);
            alert('Terjadi kesalahan koneksi saat memvalidasi token.');
        });
    }
</script>
@endpush
@endsection
