@extends('layouts.dashboard', ['title' => $jenis === 'ruangan' ? 'Form Peminjaman Laboratorium' : 'Form Peminjaman Barang'])

@section('content')
<!-- [MARKER M4] : FORM PEMINJAMAN BARANG & LABORATORIUM (Bab 3.7 Halaman 4 & 5) -->
<div class="space-y-6 max-w-6xl">
    <!-- Breadcrumb & Header Title -->
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3">
        <div>
            <a href="{{ route('mahasiswa.dashboard') }}" class="text-xs font-semibold text-[#2563EB] hover:underline inline-flex items-center gap-1 mb-1">
                <span>&larr;</span>
                <span>Kembali ke Dashboard Mahasiswa</span>
            </a>
            <h1 class="text-2xl font-bold text-[#0C1A40]">
                {{ $jenis === 'ruangan' ? 'Form Peminjaman Laboratorium' : 'Form Peminjaman Barang' }}
            </h1>
            <p class="text-xs sm:text-sm text-slate-500 mt-0.5">
                {{ $jenis === 'ruangan'
                    ? 'Ajukan jadwal penggunaan laboratorium. Sistem memvalidasi kapasitas ruangan dan bentrok jadwal secara otomatis.'
                    : 'Ajukan peminjaman perlengkapan prodi. Sistem memvalidasi ketersediaan stok barang, jumlah, dan jadwal peminjaman.' }}
            </p>
        </div>

        <a href="{{ route('fasilitas.index') }}"
           class="px-4 py-2 rounded-xl border border-slate-200 bg-white hover:bg-slate-50 text-slate-700 text-xs font-semibold transition shadow-sm w-fit">
            Lihat Katalog Fasilitas
        </a>
    </div>

    @if($errors->any())
        <x-alert type="error" title="Pengajuan belum dapat disimpan">
            <ul class="list-disc list-inside space-y-0.5 text-xs">
                @foreach($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </x-alert>
    @endif

    <!-- Main Grid Form & Info -->
    <div class="grid grid-cols-1 lg:grid-cols-12 gap-6 items-start">
        <!-- Kolom Formulir Kiri (8 Cols) -->
        <div class="lg:col-span-8 bg-white rounded-3xl p-6 sm:p-8 shadow-sm border border-slate-200/80">
            <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-3 mb-6">
                <h2 class="text-base font-bold text-[#0C1A40]">
                    {{ $jenis === 'ruangan' ? 'Data Penggunaan Laboratorium' : 'Data Peminjaman Barang' }}
                </h2>

                <!-- Switcher Tabs Barang / Laboratorium -->
                <div class="flex items-center space-x-1 bg-slate-100 p-1 rounded-2xl w-fit">
                    <a href="{{ route('mahasiswa.form-barang') }}"
                       class="px-4 py-2 rounded-xl text-xs font-semibold transition {{ $jenis === 'barang' ? 'bg-[#2563EB] text-white shadow-sm' : 'text-slate-600 hover:text-slate-900' }}">
                        Peminjaman Barang
                    </a>
                    <a href="{{ route('mahasiswa.form-lab') }}"
                       class="px-4 py-2 rounded-xl text-xs font-semibold transition {{ $jenis === 'ruangan' ? 'bg-[#2563EB] text-white shadow-sm' : 'text-slate-600 hover:text-slate-900' }}">
                        Peminjaman Lab
                    </a>
                </div>
            </div>

            <!-- Form Pengajuan -->
            <form action="{{ route('mahasiswa.store-peminjaman') }}" method="POST" id="peminjaman-form" class="space-y-5">
                @csrf
                <input type="hidden" name="type" value="{{ $jenis }}">

                <!-- Row 1: Fasilitas & Jumlah/Peserta -->
                <div class="grid grid-cols-1 sm:grid-cols-12 gap-4">
                    <div class="sm:col-span-8">
                        <label for="facility_id" class="block text-xs font-semibold text-slate-700 mb-1.5">
                            {{ $jenis === 'ruangan' ? 'Pilih Laboratorium' : 'Pilih Barang' }} <span class="text-[#DC2626]">*</span>
                        </label>
                        <select name="facility_id" id="facility_id" required
                                class="w-full px-4 py-2.5 rounded-xl border border-slate-200 text-xs focus:outline-none focus:ring-2 focus:ring-[#2563EB] bg-white">
                            <option value="">{{ $jenis === 'ruangan' ? '-- Pilih Laboratorium --' : '-- Pilih Barang --' }}</option>
                            @foreach($facilities as $f)
                                <option value="{{ $f->id }}"
                                        {{ (string) old('facility_id', $selectedFacility?->id) === (string) $f->id ? 'selected' : '' }}
                                        data-stock="{{ $f->available_stock }}"
                                        data-capacity="{{ $f->capacity }}">
                                    {{ $f->name }} ({{ $f->code }})
                                    @if($f->type === 'barang')
                                        - Stok: {{ $f->available_stock }}/{{ $f->total_stock }} unit
                                    @else
                                        - Kapasitas: {{ $f->capacity }} orang
                                    @endif
                                </option>
                            @endforeach
                        </select>
                        @error('facility_id')
                            <p class="text-xs text-[#DC2626] mt-1">{{ $message }}</p>
                        @enderror
                    </div>

                    <div class="sm:col-span-4">
                        @if($jenis === 'ruangan')
                            <label for="participant_count" class="block text-xs font-semibold text-slate-700 mb-1.5">
                                Jumlah Peserta <span class="text-[#DC2626]">*</span>
                            </label>
                            <input type="number"
                                   name="participant_count"
                                   id="participant_count"
                                   min="1"
                                   value="{{ old('participant_count', 1) }}"
                                   required
                                   class="w-full px-4 py-2.5 rounded-xl border border-slate-200 text-xs focus:outline-none focus:ring-2 focus:ring-[#2563EB]">
                            <p id="limit-hint" class="text-[11px] text-slate-400 mt-1">Sesuai kapasitas lab.</p>
                            @error('participant_count')
                                <p class="text-xs text-[#DC2626] mt-1">{{ $message }}</p>
                            @enderror
                        @else
                            <label for="quantity" class="block text-xs font-semibold text-slate-700 mb-1.5">
                                Jumlah Barang <span class="text-[#DC2626]">*</span>
                            </label>
                            <input type="number"
                                   name="quantity"
                                   id="quantity"
                                   min="1"
                                   value="{{ old('quantity', 1) }}"
                                   required
                                   class="w-full px-4 py-2.5 rounded-xl border border-slate-200 text-xs focus:outline-none focus:ring-2 focus:ring-[#2563EB]">
                            <p id="limit-hint" class="text-[11px] text-slate-400 mt-1">Maksimal sesuai stok tersedia.</p>
                            @error('quantity')
                                <p class="text-xs text-[#DC2626] mt-1">{{ $message }}</p>
                            @enderror
                        @endif
                    </div>
                </div>

                <!-- Row 2: Tanggal, Waktu Mulai & Waktu Selesai -->
                <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
                    <div>
                        <label for="borrow_date" class="block text-xs font-semibold text-slate-700 mb-1.5">
                            {{ $jenis === 'ruangan' ? 'Tanggal Penggunaan' : 'Tanggal Peminjaman' }} <span class="text-[#DC2626]">*</span>
                        </label>
                        <input type="date"
                               name="borrow_date"
                               id="borrow_date"
                               min="{{ date('Y-m-d') }}"
                               value="{{ old('borrow_date', date('Y-m-d')) }}"
                               required
                               class="w-full px-4 py-2.5 rounded-xl border border-slate-200 text-xs focus:outline-none focus:ring-2 focus:ring-[#2563EB]">
                        @error('borrow_date')
                            <p class="text-xs text-[#DC2626] mt-1">{{ $message }}</p>
                        @enderror
                    </div>

                    <div>
                        <label for="start_time" class="block text-xs font-semibold text-slate-700 mb-1.5">
                            Waktu Mulai <span class="text-[#DC2626]">*</span>
                        </label>
                        <input type="time"
                               name="start_time"
                               id="start_time"
                               value="{{ old('start_time', '08:00') }}"
                               required
                               class="w-full px-4 py-2.5 rounded-xl border border-slate-200 text-xs focus:outline-none focus:ring-2 focus:ring-[#2563EB]">
                        @error('start_time')
                            <p class="text-xs text-[#DC2626] mt-1">{{ $message }}</p>
                        @enderror
                    </div>

                    <div>
                        <label for="end_time" class="block text-xs font-semibold text-slate-700 mb-1.5">
                            Waktu Selesai <span class="text-[#DC2626]">*</span>
                        </label>
                        <input type="time"
                               name="end_time"
                               id="end_time"
                               value="{{ old('end_time', '10:00') }}"
                               required
                               class="w-full px-4 py-2.5 rounded-xl border border-slate-200 text-xs focus:outline-none focus:ring-2 focus:ring-[#2563EB]">
                        @error('end_time')
                            <p class="text-xs text-[#DC2626] mt-1">{{ $message }}</p>
                        @enderror
                    </div>
                </div>

                <!-- Row 3: Tujuan Peminjaman / Penggunaan -->
                <div>
                    <label for="purpose" class="block text-xs font-semibold text-slate-700 mb-1.5">
                        {{ $jenis === 'ruangan' ? 'Tujuan Penggunaan Laboratorium' : 'Tujuan Peminjaman Barang' }} <span class="text-[#DC2626]">*</span>
                    </label>
                    <textarea name="purpose"
                              id="purpose"
                              rows="4"
                              minlength="10"
                              placeholder="{{ $jenis === 'ruangan' ? 'Contoh: Praktikum Basis Data Lanjut / Kegiatan Pelatihan Mahasiswa Prodi Ilmu Komputer' : 'Contoh: Presentasi Tugas Akhir atau perkuliahan mata kuliah Rekayasa Perangkat Lunak' }}"
                              required
                              class="w-full px-4 py-2.5 rounded-xl border border-slate-200 text-xs focus:outline-none focus:ring-2 focus:ring-[#2563EB]">{{ old('purpose') }}</textarea>
                    <p class="text-[11px] text-slate-400 mt-1">Minimal 10 karakter agar memudahkan verifikasi Dosen.</p>
                    @error('purpose')
                        <p class="text-xs text-[#DC2626] mt-1">{{ $message }}</p>
                    @enderror
                </div>

                <!-- Tombol Aksi -->
                <div class="flex flex-wrap items-center gap-3 pt-3 border-t border-slate-100">
                    <button type="submit"
                            class="px-6 py-2.5 rounded-xl bg-[#2563EB] hover:bg-blue-700 text-white font-semibold text-xs transition shadow-sm">
                        {{ $jenis === 'ruangan' ? 'Ajukan Peminjaman Lab' : 'Ajukan Peminjaman Barang' }}
                    </button>

                    <a href="{{ Route::has('mahasiswa.dashboard') ? route('mahasiswa.dashboard') : route('fasilitas.index') }}" 
                       class="px-6 py-2.5 rounded-xl border border-slate-200 text-slate-600 hover:bg-slate-50 font-semibold text-xs transition">
                        Batal
                    </a>
                    <a href="{{ route('mahasiswa.peminjaman-saya') }}"
                       class="px-5 py-2.5 rounded-xl border border-slate-200 text-slate-600 hover:bg-slate-50 font-semibold text-xs transition">
                        Lihat Peminjaman Saya
                    </a>

                    </a>
                </div>
            </form>
        </div>

        <!-- Kolom Info Kanan (4 Cols) -->
        <div class="lg:col-span-4 space-y-6">
            <!-- Validasi Sistem -->
            <div class="bg-white rounded-3xl p-6 shadow-sm border border-slate-200/80 space-y-3">
                <h3 class="text-sm font-bold text-[#0C1A40]">
                    {{ $jenis === 'ruangan' ? 'Validasi Peminjaman Lab' : 'Validasi Peminjaman Barang' }}
                </h3>

                @if($jenis === 'barang')
                    <div class="p-3.5 rounded-2xl bg-blue-50 border border-blue-100">
                        <span class="inline-block px-2.5 py-0.5 rounded-full text-[10px] font-bold bg-[#2563EB] text-white mb-1.5">
                            Cek Stok Otomatis
                        </span>
                        <p class="text-xs text-slate-600 leading-relaxed">
                            Jumlah barang yang diajukan tidak boleh melebihi stok tersedia saat pengajuan dikirim.
                        </p>
                    </div>
                    <div class="p-3.5 rounded-2xl bg-emerald-50 border border-emerald-100">
                        <span class="inline-block px-2.5 py-0.5 rounded-full text-[10px] font-bold bg-[#059669] text-white mb-1.5">
                            Validasi Jadwal
                        </span>
                        <p class="text-xs text-slate-600 leading-relaxed">
                            Tanggal peminjaman minimal hari ini dan waktu selesai harus lebih akhir dari waktu mulai.
                        </p>
                    </div>
                @else
                    <div class="p-3.5 rounded-2xl bg-blue-50 border border-blue-100">
                        <span class="inline-block px-2.5 py-0.5 rounded-full text-[10px] font-bold bg-[#2563EB] text-white mb-1.5">
                            Cek Kapasitas Lab
                        </span>
                        <p class="text-xs text-slate-600 leading-relaxed">
                            Jumlah peserta tidak boleh melebihi kapasitas kursi laboratorium yang dipilih.
                        </p>
                    </div>
                    <div class="p-3.5 rounded-2xl bg-amber-50 border border-amber-100">
                        <span class="inline-block px-2.5 py-0.5 rounded-full text-[10px] font-bold bg-[#D97706] text-white mb-1.5">
                            Anti Bentrok Jadwal
                        </span>
                        <p class="text-xs text-slate-600 leading-relaxed">
                            Sistem memeriksa jadwal aktif (Menunggu, Disetujui, Dipinjam) agar tidak terjadi tabrakan waktu.
                        </p>
                    </div>
                @endif
            </div>

            @if($jenis === 'ruangan')
                <!-- Jadwal Lab Terdekat -->
                <div class="bg-white rounded-3xl p-6 shadow-sm border border-slate-200/80">
                    <h3 class="text-sm font-bold text-[#0C1A40] mb-3">Jadwal Lab Terdaftar</h3>
                    @if($upcomingLabSchedules->isNotEmpty())
                        <div class="space-y-2.5">
                            @foreach($upcomingLabSchedules as $schedule)
                                <div class="p-3 rounded-2xl bg-slate-50 border border-slate-100 text-xs">
                                    <div class="flex items-center justify-between gap-2">
                                        <span class="font-semibold text-slate-900 truncate">{{ $schedule->facility?->name ?? 'Lab' }}</span>
                                        <x-badge :status="$schedule->status">{{ ucfirst($schedule->status) }}</x-badge>
                                    </div>
                                    <p class="text-[11px] text-slate-500 mt-1">
                                        {{ \Carbon\Carbon::parse($schedule->borrow_date)->isoFormat('D MMM Y') }}
                                        &middot; {{ substr($schedule->start_time, 0, 5) }} - {{ substr($schedule->end_time, 0, 5) }}
                                    </p>
                                </div>
                            @endforeach
                        </div>
                    @else
                        <p class="text-xs text-slate-400">Belum ada jadwal penggunaan lab aktif mendatang.</p>
                    @endif
                </div>
            @endif

            <!-- Alur Pengajuan -->
            <div class="bg-white rounded-3xl p-6 shadow-sm border border-slate-200/80">
                <h3 class="text-sm font-bold text-[#0C1A40] mb-4">Alur Setelah Diajukan</h3>
                <div class="space-y-3.5 text-xs">
                    <div class="flex items-start space-x-3">
                        <span class="w-6 h-6 rounded-full bg-blue-100 text-[#2563EB] font-bold flex items-center justify-center shrink-0">1</span>
                        <p class="text-slate-600 pt-0.5">Data tersimpan dengan status <strong class="text-[#D97706]">Menunggu</strong>.</p>
                    </div>
                    <div class="flex items-start space-x-3">
                        <span class="w-6 h-6 rounded-full bg-blue-100 text-[#2563EB] font-bold flex items-center justify-center shrink-0">2</span>
                        <p class="text-slate-600 pt-0.5">Pengajuan dibaca oleh modul persetujuan Dosen (M5).</p>
                    </div>
                    <div class="flex items-start space-x-3">
                        <span class="w-6 h-6 rounded-full bg-blue-100 text-[#2563EB] font-bold flex items-center justify-center shrink-0">3</span>
                        <p class="text-slate-600 pt-0.5">Pantau status dan riwayat peminjaman di menu <strong>Peminjaman Saya</strong>.</p>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
    (function () {
        const facilitySelect = document.getElementById('facility_id');
        const limitHint = document.getElementById('limit-hint');
        const qtyInput = document.getElementById('quantity');
        const participantInput = document.getElementById('participant_count');

        function updateFacilityHint() {
            if (!facilitySelect || !limitHint) return;
            const selected = facilitySelect.options[facilitySelect.selectedIndex];
            if (!selected || !selected.value) {
                limitHint.textContent = qtyInput ? 'Maksimal sesuai stok tersedia.' : 'Sesuai kapasitas lab.';
                return;
            }
            if (qtyInput) {
                const stock = parseInt(selected.dataset.stock || '0', 10);
                limitHint.textContent = 'Stok tersedia: ' + stock + ' unit.';
            }
            if (participantInput) {
                const capacity = parseInt(selected.dataset.capacity || '0', 10);
                limitHint.textContent = capacity > 0 ? 'Kapasitas maksimal: ' + capacity + ' orang.' : 'Sesuai kapasitas lab.';
            }
        }

        if (facilitySelect) {
            facilitySelect.addEventListener('change', updateFacilityHint);
            updateFacilityHint();
        }
    })();
</script>
@endpush
