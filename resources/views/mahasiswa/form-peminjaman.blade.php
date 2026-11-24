@extends('layouts.dashboard')

@section('content')
<!-- [MARKER M3] : FORM PENGAJUAN PEMINJAMAN PERSIS FIGMA (PAGE 5 & 6) -->
<div class="space-y-6 max-w-6xl">
    <!-- Header Title -->
    <div>
        <h1 class="text-2xl font-bold text-slate-900">
            {{ $jenis === 'ruangan' ? 'Ajukan Peminjaman Lab' : 'Ajukan Peminjaman' }}
        </h1>
        <p class="text-xs text-slate-500 mt-0.5">
            {{ $jenis === 'ruangan' 
                ? 'Lab komputer disediakan untuk praktikum. Penggunaan lab harus mendapat izin Dosen terlebih dahulu.' 
                : 'Isi data peminjaman. Stok barang diperiksa sistem; penggunaan lab memerlukan izin Dosen.' }}
        </p>
    </div>

    <!-- Main Grid Form & Stepper Info -->
    <div class="grid grid-cols-1 lg:grid-cols-12 gap-6 items-start">
        <!-- Kolom Formulir Kiri (8 Cols) -->
        <div class="lg:col-span-8 bg-white rounded-3xl p-6 sm:p-8 shadow-sm border border-slate-100">
            <h3 class="text-base font-bold text-slate-900 mb-4">
                {{ $jenis === 'ruangan' ? 'Data penggunaan lab' : 'Data peminjaman' }}
            </h3>

            <!-- Switcher Tabs Barang / Lab Persis Figma -->
            <div class="flex items-center space-x-1 bg-slate-100 p-1 rounded-2xl w-fit mb-6">
                <a href="{{ route('mahasiswa.form-peminjaman', 'barang') }}" 
                   class="px-5 py-2 rounded-xl text-xs font-semibold transition {{ $jenis === 'barang' ? 'bg-[#2563EB] text-white shadow-sm' : 'text-slate-600 hover:text-slate-900' }}">
                    Barang
                </a>
                <a href="{{ route('mahasiswa.form-peminjaman', 'lab') }}" 
                   class="px-5 py-2 rounded-xl text-xs font-semibold transition {{ $jenis === 'ruangan' ? 'bg-[#2563EB] text-white shadow-sm' : 'text-slate-600 hover:text-slate-900' }}">
                    Lab
                </a>
            </div>

            <!-- Form Pengajuan -->
            <form action="{{ route('mahasiswa.store-peminjaman') }}" method="POST" id="peminjaman-form" class="space-y-4">
                @csrf
                <input type="hidden" name="type" value="{{ $jenis }}">

                <!-- Row 1: Fasilitas & Jumlah/Peserta -->
                <div class="grid grid-cols-1 sm:grid-cols-12 gap-4">
                    <div class="sm:col-span-8">
                        <label for="facility_id" class="block text-xs font-semibold text-slate-700 mb-1.5">
                            {{ $jenis === 'ruangan' ? 'Lab' : 'Fasilitas' }}
                        </label>
                        <select name="facility_id" id="facility_id" required 
                                class="w-full px-4 py-2.5 rounded-xl border border-slate-200 text-xs focus:outline-none focus:ring-2 focus:ring-[#2563EB] bg-white">
                            <option value="">{{ $jenis === 'ruangan' ? '[Pilih lab]' : '[Pilih barang]' }}</option>
                            @foreach($facilities as $f)
                                <option value="{{ $f->id }}" 
                                    {{ (old('facility_id', $selectedFacility?->id) == $f->id) ? 'selected' : '' }}
                                    data-stock="{{ $f->available_stock }}"
                                    data-capacity="{{ $f->capacity }}">
                                    {{ $f->name }} {{ $f->type === 'barang' ? "(Stok: {$f->available_stock})" : "(Kapasitas: {$f->capacity} kursi)" }}
                                </option>
                            @endforeach
                        </select>
                        @error('facility_id')
                            <p class="text-xs text-rose-600 mt-1">{{ $message }}</p>
                        @enderror
                    </div>

                    <div class="sm:col-span-4">
                        <label for="quantity" class="block text-xs font-semibold text-slate-700 mb-1.5">
                            {{ $jenis === 'ruangan' ? 'Jumlah peserta' : 'Jumlah' }}
                        </label>
                        @if($jenis === 'ruangan')
                            <input type="number" 
                                   name="participant_count" 
                                   id="participant_count" 
                                   min="1" 
                                   max="100" 
                                   value="{{ old('participant_count', 1) }}" 
                                   required
                                   class="w-full px-4 py-2.5 rounded-xl border border-slate-200 text-xs focus:outline-none focus:ring-2 focus:ring-[#2563EB]">
                        @else
                            <input type="number" 
                                   name="quantity" 
                                   id="quantity" 
                                   min="1" 
                                   max="20" 
                                   value="{{ old('quantity', 1) }}" 
                                   required
                                   class="w-full px-4 py-2.5 rounded-xl border border-slate-200 text-xs focus:outline-none focus:ring-2 focus:ring-[#2563EB]">
                        @endif
                    </div>
                </div>

                <!-- Row 2: Tanggal & Jam Mulai -->
                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                    <div>
                        <label for="borrow_date" class="block text-xs font-semibold text-slate-700 mb-1.5">Tanggal</label>
                        <input type="date" 
                               name="borrow_date" 
                               id="borrow_date" 
                               value="{{ old('borrow_date', date('Y-m-d')) }}" 
                               required
                               class="w-full px-4 py-2.5 rounded-xl border border-slate-200 text-xs focus:outline-none focus:ring-2 focus:ring-[#2563EB]">
                    </div>

                    <div>
                        <label for="start_time" class="block text-xs font-semibold text-slate-700 mb-1.5">Jam mulai</label>
                        <input type="time" 
                               name="start_time" 
                               id="start_time" 
                               value="{{ old('start_time', '08:00') }}" 
                               required
                               class="w-full px-4 py-2.5 rounded-xl border border-slate-200 text-xs focus:outline-none focus:ring-2 focus:ring-[#2563EB]">
                    </div>
                </div>

                <!-- Row 3: Durasi -->
                <div>
                    <label for="duration_hours" class="block text-xs font-semibold text-slate-700 mb-1.5">Durasi</label>
                    <select name="duration_hours" id="duration_hours" required 
                            class="w-full px-4 py-2.5 rounded-xl border border-slate-200 text-xs focus:outline-none focus:ring-2 focus:ring-[#2563EB] bg-white">
                        <option value="">[Pilih durasi]</option>
                        <option value="1" {{ old('duration_hours') == '1' ? 'selected' : '' }}>1 Jam</option>
                        <option value="2" {{ old('duration_hours', '2') == '2' ? 'selected' : '' }}>2 Jam</option>
                        <option value="3" {{ old('duration_hours') == '3' ? 'selected' : '' }}>3 Jam</option>
                        <option value="4" {{ old('duration_hours') == '4' ? 'selected' : '' }}>4 Jam</option>
                        <option value="5" {{ old('duration_hours') == '5' ? 'selected' : '' }}>5 Jam</option>
                    </select>
                </div>

                <!-- Row 4: Tujuan Peminjaman -->
                <div>
                    <label for="purpose" class="block text-xs font-semibold text-slate-700 mb-1.5">
                        {{ $jenis === 'ruangan' ? 'Tujuan penggunaan lab' : 'Tujuan peminjaman' }}
                    </label>
                    <textarea name="purpose" 
                              id="purpose" 
                              rows="3" 
                              placeholder="{{ $jenis === 'ruangan' ? 'Jelaskan keperluan penggunaan lab' : 'Jelaskan keperluan peminjaman' }}" 
                              required
                              class="w-full px-4 py-2.5 rounded-xl border border-slate-200 text-xs focus:outline-none focus:ring-2 focus:ring-[#2563EB]">{{ old('purpose') }}</textarea>
                </div>

                <p class="text-[11px] text-slate-400">
                    {{ $jenis === 'ruangan' ? 'Pengajuan lab akan otomatis diteruskan kepada Dosen Pengelola Lab.' : 'Untuk lab, kolom Jumlah diganti Jumlah peserta.' }}
                </p>

                <!-- Tombol Aksi Persis Figma -->
                <div class="flex items-center space-x-3 pt-4">
                    <button type="submit" 
                            class="px-6 py-2.5 rounded-xl bg-[#2563EB] hover:bg-blue-700 text-white font-semibold text-xs transition shadow-sm">
                        {{ $jenis === 'ruangan' ? 'Ajukan izin penggunaan' : 'Ajukan peminjaman' }}
                    </button>
                    <a href="{{ route('mahasiswa.dashboard') }}" 
                       class="px-6 py-2.5 rounded-xl border border-slate-200 text-slate-600 hover:bg-slate-50 font-semibold text-xs transition">
                        Batal
                    </a>
                </div>
            </form>
        </div>

        <!-- Kolom Info Kanan (4 Cols) Persis Figma -->
        <div class="lg:col-span-4 space-y-6">
            <!-- Box 1: Pemeriksaan Stok dan Izin / Ketentuan Izin Lab -->
            <div class="bg-white rounded-3xl p-6 shadow-sm border border-slate-100">
                <h3 class="text-sm font-bold text-slate-900 mb-3">
                    {{ $jenis === 'ruangan' ? 'Ketentuan izin lab' : 'Pemeriksaan stok dan izin' }}
                </h3>

                @if($jenis === 'barang')
                    <!-- Box Barang -->
                    <div class="space-y-3">
                        <div class="p-3.5 rounded-2xl bg-blue-50 border border-blue-100">
                            <span class="inline-block px-2.5 py-0.5 rounded-full text-[10px] font-bold bg-blue-600 text-white mb-1.5">
                                Stok mencukupi
                            </span>
                            <p class="text-xs text-slate-600 leading-relaxed">
                                Contoh hasil untuk barang. Sistem memeriksa kuota barang secara otomatis saat form dikirimkan.
                            </p>
                        </div>

                        <div class="p-3.5 rounded-2xl bg-slate-50 border border-slate-200/80">
                            <span class="inline-block px-2.5 py-0.5 rounded-full text-[10px] font-bold bg-slate-700 text-white mb-1.5">
                                Izin Dosen diperlukan
                            </span>
                            <p class="text-xs text-slate-600 leading-relaxed">
                                Lab tidak diperiksa bentrok jadwal. Penggunaan lab harus mendapat izin Dosen terlebih dahulu.
                            </p>
                        </div>
                    </div>
                @else
                    <!-- Box Lab -->
                    <div class="p-4 rounded-2xl bg-blue-50 border border-blue-100">
                        <span class="inline-block px-2.5 py-0.5 rounded-full text-[10px] font-bold bg-blue-600 text-white mb-2">
                            Izin Dosen diperlukan
                        </span>
                        <p class="text-xs text-slate-600 leading-relaxed">
                            Lab tidak diperiksa bentrok jadwal atau stok. Permohonan langsung diteruskan ke Dosen untuk diberi izin.
                        </p>
                    </div>
                @endif
            </div>

            <!-- Box 2: Alur Setelah Diajukan (Stepper 1-4) Persis Figma -->
            <div class="bg-white rounded-3xl p-6 shadow-sm border border-slate-100">
                <h3 class="text-sm font-bold text-slate-900 mb-4">Alur setelah diajukan</h3>

                <div class="space-y-4 text-xs">
                    <div class="flex items-start space-x-3">
                        <div class="w-6 h-6 rounded-full bg-blue-100 text-[#2563EB] font-bold flex items-center justify-center shrink-0 text-xs">
                            1
                        </div>
                        <p class="text-slate-600 pt-0.5">
                            {{ $jenis === 'ruangan' ? 'Mahasiswa mengajukan izin penggunaan lab' : 'Sistem memeriksa stok (khusus barang)' }}
                        </p>
                    </div>

                    <div class="flex items-start space-x-3">
                        <div class="w-6 h-6 rounded-full bg-blue-100 text-[#2563EB] font-bold flex items-center justify-center shrink-0 text-xs">
                            2
                        </div>
                        <p class="text-slate-600 pt-0.5">
                            {{ $jenis === 'ruangan' ? 'Dosen menyetujui atau menolak izin' : 'Dosen menyetujui atau menolak (izin lab)' }}
                        </p>
                    </div>

                    <div class="flex items-start space-x-3">
                        <div class="w-6 h-6 rounded-full bg-blue-100 text-[#2563EB] font-bold flex items-center justify-center shrink-0 text-xs">
                            3
                        </div>
                        <p class="text-slate-600 pt-0.5">
                            QR Code terbit, berlaku 15 menit
                        </p>
                    </div>

                    <div class="flex items-start space-x-3">
                        <div class="w-6 h-6 rounded-full bg-blue-100 text-[#2563EB] font-bold flex items-center justify-center shrink-0 text-xs">
                            4
                        </div>
                        <p class="text-slate-600 pt-0.5">
                            {{ $jenis === 'ruangan' ? 'Dosen memindai QR saat penggunaan lab' : 'Dosen memindai QR saat penyerahan' }}
                        </p>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
