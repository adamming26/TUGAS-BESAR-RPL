@extends('layouts.app', ['title' => $facility->name . ' - Detail Fasilitas'])

@section('content')
@php
    $isLab = $facility->isRuangan();
    $formattedSelectedDate = \Carbon\Carbon::parse($selectedDate)->locale('id')->translatedFormat('d F Y');
    $typeLabel = $isLab ? 'Laboratorium' : 'Barang';
    $typeRoute = $isLab ? route('fasilitas.laboratorium') : route('fasilitas.barang');
@endphp

<!-- [M3] HALAMAN DETAIL FASILITAS & JADWAL PENGGUNAAN -->
<div class="min-h-screen bg-[#F8FAFC] flex flex-col">
    <!-- Hero Header -->
    <div class="relative bg-[#0C1A40] text-white overflow-hidden">
        <div class="absolute inset-0 z-0">
            <div class="absolute inset-0 bg-gradient-to-r from-[#0C1A40]/95 via-[#0C1A40]/88 to-[#1e3a8a]/75 z-10"></div>
            <img src="https://images.unsplash.com/photo-1541339907198-e08756dedf3f?auto=format&fit=crop&w=2000&q=80"
                 alt="Gedung Kampus FMIPA Universitas Halu Oleo"
                 class="w-full h-full object-cover opacity-30">
        </div>

        @include('partials.m2-public-header', ['showAuthButtons' => true])

        <div class="relative z-20 max-w-7xl mx-auto px-6 sm:px-8 lg:px-14 pt-4 pb-12">
            <div class="mb-4">
                <a href="{{ route('fasilitas.index') }}"
                   class="inline-flex items-center gap-1.5 text-xs font-semibold text-blue-200 hover:text-white transition">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"></path>
                    </svg>
                    <span>Kembali ke Katalog Fasilitas</span>
                </a>
            </div>

            <div class="flex flex-col lg:flex-row lg:items-end justify-between gap-4">
                <div>
                    <div class="flex flex-wrap items-center gap-2 mb-3">
                        <span class="px-3 py-1 rounded-full text-xs font-bold font-mono bg-white/15 border border-white/25 text-white">
                            {{ $facility->code }}
                        </span>
                        <span class="px-3 py-1 rounded-full text-xs font-semibold bg-blue-500/25 border border-blue-300/30 text-blue-100">
                            {{ $isLab ? 'Laboratorium Komputer' : 'Fasilitas Barang' }}
                        </span>
                        <x-badge :status="$availability['badge_status']">{{ $availability['label'] }}</x-badge>
                    </div>
                    <h1 class="text-3xl sm:text-4xl font-extrabold tracking-tight text-white">
                        {{ $facility->name }}
                    </h1>
                </div>

                <div class="flex items-center gap-3">
                    <div class="bg-white/10 backdrop-blur-sm border border-white/20 rounded-2xl px-5 py-3 text-left">
                        @if($isLab)
                            <span class="block text-[10px] uppercase tracking-wider text-blue-200 font-semibold">Kapasitas Ruangan</span>
                            <span class="text-lg font-extrabold text-white">{{ $facility->capacity }} orang</span>
                        @else
                            <span class="block text-[10px] uppercase tracking-wider text-blue-200 font-semibold">Ketersediaan Stok</span>
                            <span class="text-lg font-extrabold text-white">{{ $facility->available_stock }} / {{ $facility->total_stock }} unit</span>
                        @endif
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Main Content -->
    <div class="flex-1 max-w-7xl mx-auto w-full px-6 sm:px-8 lg:px-14 py-8 space-y-6">
        <x-breadcrumb :items="[
            ['label' => 'Katalog Fasilitas', 'url' => route('fasilitas.index')],
            ['label' => $typeLabel, 'url' => $typeRoute],
            ['label' => $facility->name],
        ]" />

        @if(!$facility->is_active || $facility->condition === 'Rusak Berat')
            <x-alert type="danger" title="Fasilitas Tidak Tersedia untuk Peminjaman" :dismissible="false">
                Fasilitas ini berstatus <strong>{{ !$facility->is_active ? 'Nonaktif' : 'Rusak Berat' }}</strong> sehingga pengajuan peminjaman sementara ditutup.
            </x-alert>
        @endif

        <div class="grid grid-cols-1 lg:grid-cols-12 gap-6 items-start">
            <!-- KOLOM KIRI: DETAIL, INFORMASI PENGGUNAAN & JADWAL (8 Cols) -->
            <div class="lg:col-span-8 space-y-6">
                <!-- 1. Informasi Lengkap Fasilitas -->
                <section class="bg-white rounded-3xl shadow-sm border border-slate-100 overflow-hidden">
                    @if($imageUrl)
                        <div class="h-64 sm:h-72 bg-slate-100 overflow-hidden border-b border-slate-100">
                            <img src="{{ $imageUrl }}"
                                 alt="{{ $facility->name }}"
                                 class="w-full h-full object-cover">
                        </div>
                    @endif

                    <div class="p-6 sm:p-8 space-y-6">
                        <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-3">
                            <div>
                                <h2 class="text-lg font-bold text-[#0C1A40]">Informasi Lengkap Fasilitas</h2>
                                <p class="text-xs text-slate-500 mt-0.5">Spesifikasi inventaris dan kondisi terkini di Program Studi Ilmu Komputer.</p>
                            </div>
                            <x-badge :status="strtolower($facility->condition)">Kondisi: {{ $facility->condition }}</x-badge>
                        </div>

                        <p class="text-sm text-slate-600 leading-relaxed">
                            {{ $facility->description ?: 'Fasilitas penunjang kegiatan perkuliahan, praktikum, dan aktivitas akademik di Program Studi Ilmu Komputer FMIPA Universitas Halu Oleo.' }}
                        </p>

                        <!-- Spesifikasi Grid -->
                        <div class="grid grid-cols-2 sm:grid-cols-4 gap-4">
                            <div class="p-4 rounded-2xl bg-[#F8FAFC] border border-slate-100">
                                <span class="text-[11px] font-semibold text-slate-400 uppercase block">Kode</span>
                                <span class="text-sm font-bold font-mono text-[#0C1A40] mt-1 block">{{ $facility->code }}</span>
                            </div>

                            <div class="p-4 rounded-2xl bg-[#F8FAFC] border border-slate-100">
                                <span class="text-[11px] font-semibold text-slate-400 uppercase block">Jenis</span>
                                <span class="text-sm font-bold text-slate-900 mt-1 block">{{ $isLab ? 'Laboratorium' : 'Barang' }}</span>
                            </div>

                            @if($isLab)
                                <div class="p-4 rounded-2xl bg-[#F8FAFC] border border-slate-100">
                                    <span class="text-[11px] font-semibold text-slate-400 uppercase block">Kapasitas</span>
                                    <span class="text-sm font-bold text-[#2563EB] mt-1 block">{{ $facility->capacity }} orang</span>
                                </div>
                            @else
                                <div class="p-4 rounded-2xl bg-[#F8FAFC] border border-slate-100">
                                    <span class="text-[11px] font-semibold text-slate-400 uppercase block">Stok Tersedia</span>
                                    <span class="text-sm font-bold {{ $facility->available_stock > 0 ? 'text-[#059669]' : 'text-[#DC2626]' }} mt-1 block">
                                        {{ $facility->available_stock }} / {{ $facility->total_stock }} unit
                                    </span>
                                </div>
                            @endif

                            <div class="p-4 rounded-2xl bg-[#F8FAFC] border border-slate-100">
                                <span class="text-[11px] font-semibold text-slate-400 uppercase block">Status</span>
                                <span class="text-sm font-bold text-slate-900 mt-1 block">{{ $availability['label'] }}</span>
                            </div>
                        </div>
                    </div>
                </section>

                <!-- 2. Informasi Penggunaan & Ketentuan -->
                <section class="bg-white rounded-3xl p-6 sm:p-8 shadow-sm border border-slate-100 space-y-5">
                    <div>
                        <h2 class="text-lg font-bold text-[#0C1A40]">Informasi Penggunaan</h2>
                        <p class="text-xs text-slate-500 mt-0.5">Ringkasan aktivitas penggunaan dan aturan peminjaman fasilitas.</p>
                    </div>

                    <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
                        <div class="p-4 rounded-2xl bg-blue-50/70 border border-blue-100">
                            <span class="text-[11px] font-semibold text-[#2563EB] uppercase block">Jadwal Hari Ini</span>
                            <span class="text-2xl font-extrabold text-[#0C1A40] mt-1 block">{{ $usageStats['today_bookings'] }}</span>
                            <span class="text-[11px] text-slate-500">Penggunaan terjadwal</span>
                        </div>
                        <div class="p-4 rounded-2xl bg-amber-50/70 border border-amber-100">
                            <span class="text-[11px] font-semibold text-[#D97706] uppercase block">Pengajuan Aktif</span>
                            <span class="text-2xl font-extrabold text-[#0C1A40] mt-1 block">{{ $usageStats['active_bookings'] }}</span>
                            <span class="text-[11px] text-slate-500">Menunggu / Disetujui / Dipinjam</span>
                        </div>
                        <div class="p-4 rounded-2xl bg-emerald-50/70 border border-emerald-100">
                            <span class="text-[11px] font-semibold text-[#059669] uppercase block">Riwayat Selesai</span>
                            <span class="text-2xl font-extrabold text-[#0C1A40] mt-1 block">{{ $usageStats['total_completed'] }}</span>
                            <span class="text-[11px] text-slate-500">Transaksi selesai tercatat</span>
                        </div>
                    </div>

                    <div class="p-4 rounded-2xl bg-[#F8FAFC] border border-slate-200/70 text-xs text-slate-600 space-y-2 leading-relaxed">
                        <p class="font-bold text-slate-800">Ketentuan Peminjaman {{ $isLab ? 'Laboratorium' : 'Barang' }}:</p>
                        @if($isLab)
                            <ul class="list-disc list-inside space-y-1">
                                <li>Kapasitas maksimal ruangan adalah <strong>{{ $facility->capacity }} peserta</strong>.</li>
                                <li>Penggunaan laboratorium wajib bebas bentrok jadwal dan mendapatkan persetujuan Dosen.</li>
                                <li>Tunjukkan QR Code peminjaman kepada Dosen penanggung jawab saat mulai dan selesai penggunaan.</li>
                            </ul>
                        @else
                            <ul class="list-disc list-inside space-y-1">
                                <li>Total inventaris berjumlah <strong>{{ $facility->total_stock }} unit</strong> dengan <strong>{{ $facility->available_stock }} unit</strong> tersedia saat ini.</li>
                                <li>Peminjaman memerlukan persetujuan Dosen serta validasi QR Code saat penyerahan dan pengembalian.</li>
                                <li>Pengembalian wajib dilakukan sesuai durasi dan dalam kondisi baik.</li>
                            </ul>
                        @endif
                    </div>
                </section>

                <!-- 3. Jadwal Penggunaan Fasilitas -->
                <section class="bg-white rounded-3xl p-6 sm:p-8 shadow-sm border border-slate-100 space-y-6">
                    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4">
                        <div>
                            <h2 class="text-lg font-bold text-[#0C1A40]">Jadwal Penggunaan Fasilitas</h2>
                            <p class="text-xs text-slate-500 mt-0.5">Menampilkan jadwal aktif pada tanggal {{ $formattedSelectedDate }}.</p>
                        </div>

                        <form method="GET" action="{{ route('fasilitas.show', $facility->id) }}" class="flex items-center gap-2">
                            <input type="date"
                                   name="tanggal"
                                   value="{{ $selectedDate }}"
                                   aria-label="Pilih tanggal jadwal"
                                   class="px-3.5 py-2 rounded-xl border border-slate-200 text-xs text-slate-700 focus:outline-none focus:ring-2 focus:ring-[#2563EB]">
                            <button type="submit"
                                    class="px-4 py-2 rounded-xl bg-[#0C1A40] hover:bg-slate-800 text-white text-xs font-semibold transition">
                                Lihat Tanggal
                            </button>
                        </form>
                    </div>

                    <!-- Tabel Jadwal Pada Tanggal Terpilih -->
                    <div class="overflow-x-auto border border-slate-100 rounded-2xl">
                        <table class="w-full text-left border-collapse">
                            <thead>
                                <tr class="bg-slate-50 border-b border-slate-100 text-[11px] font-semibold text-slate-500 uppercase tracking-wider">
                                    <th class="py-3 px-4">Waktu</th>
                                    <th class="py-3 px-4">Durasi</th>
                                    <th class="py-3 px-4">Keperluan</th>
                                    <th class="py-3 px-4 text-center">Status</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-slate-100 text-xs text-slate-700">
                                @forelse($schedulesOnDate as $schedule)
                                    <tr class="hover:bg-slate-50/70 transition">
                                        <td class="py-3.5 px-4 font-semibold text-[#0C1A40] whitespace-nowrap">
                                            {{ substr((string) $schedule->start_time, 0, 5) }} - {{ substr((string) $schedule->end_time, 0, 5) }}
                                        </td>
                                        <td class="py-3.5 px-4 whitespace-nowrap">{{ $schedule->duration_hours }} jam</td>
                                        <td class="py-3.5 px-4">{{ $schedule->purpose }}</td>
                                        <td class="py-3.5 px-4 text-center">
                                            <x-badge :status="$schedule->status">{{ ucfirst($schedule->status) }}</x-badge>
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="4" class="py-8 px-4 text-center text-slate-400">
                                            Belum ada jadwal penggunaan pada tanggal {{ $formattedSelectedDate }}.
                                        </td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>

                    <!-- Jadwal Mendatang yang Relevan -->
                    <div class="pt-2">
                        <h3 class="text-sm font-bold text-slate-900 mb-3">Jadwal Mendatang yang Relevan</h3>
                        @if($upcomingSchedules->isNotEmpty())
                            <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                                @foreach($upcomingSchedules as $upcoming)
                                    <div class="p-3.5 rounded-2xl bg-[#F8FAFC] border border-slate-100 flex items-center justify-between gap-3">
                                        <div class="min-w-0">
                                            <p class="text-xs font-bold text-[#0C1A40]">
                                                {{ \Carbon\Carbon::parse($upcoming->borrow_date)->locale('id')->translatedFormat('d M Y') }}
                                                · {{ substr((string) $upcoming->start_time, 0, 5) }}-{{ substr((string) $upcoming->end_time, 0, 5) }}
                                            </p>
                                            <p class="text-[11px] text-slate-500 truncate mt-0.5">{{ $upcoming->purpose }}</p>
                                        </div>
                                        <x-badge :status="$upcoming->status">{{ ucfirst($upcoming->status) }}</x-badge>
                                    </div>
                                @endforeach
                            </div>
                        @else
                            <p class="text-xs text-slate-400 py-3 px-4 rounded-2xl bg-slate-50 border border-slate-100">
                                Tidak ada jadwal peminjaman mendatang yang terdaftar untuk fasilitas ini.
                            </p>
                        @endif
                    </div>

                    @if($scheduleLogs->isNotEmpty())
                        <div class="pt-2">
                            <h3 class="text-sm font-bold text-slate-900 mb-3">Log Jadwal Terdaftar</h3>
                            <div class="space-y-2">
                                @foreach($scheduleLogs as $log)
                                    <div class="flex items-center justify-between px-4 py-2.5 rounded-xl bg-slate-50 border border-slate-100 text-xs">
                                        <span class="font-medium text-slate-700">
                                            {{ \Carbon\Carbon::parse($log->date)->locale('id')->translatedFormat('d M Y') }}
                                            ({{ substr((string) $log->start_time, 0, 5) }} - {{ substr((string) $log->end_time, 0, 5) }})
                                        </span>
                                        <x-badge :status="$log->status">{{ ucfirst($log->status) }}</x-badge>
                                    </div>
                                @endforeach
                            </div>
                        </div>
                    @endif
                </section>
            </div>

            <!-- KOLOM KANAN: CEK BENTROK JADWAL, WAKTU TERSEDIA & TOMBOL M4 (4 Cols) -->
            <div class="lg:col-span-4 space-y-6">
                <!-- 1. Pemeriksaan Bentrok Jadwal (ScheduleCheckService) -->
                <section class="bg-white rounded-3xl p-6 shadow-sm border border-slate-100 space-y-4">
                    <div>
                        <h2 class="text-base font-bold text-[#0C1A40]">Cek Bentrok Jadwal</h2>
                        <p class="text-xs text-slate-500 mt-0.5">Periksa ketersediaan waktu sebelum mengajukan peminjaman.</p>
                    </div>

                    <form method="GET" action="{{ route('fasilitas.cek-jadwal', $facility->id) }}" class="space-y-3.5">
                        <div>
                            <label for="cek-tanggal" class="block text-xs font-semibold text-slate-700 mb-1">Tanggal</label>
                            <input type="date"
                                   id="cek-tanggal"
                                   name="tanggal"
                                   value="{{ old('tanggal', $scheduleCheck['date'] ?? $selectedDate) }}"
                                   required
                                   class="w-full px-3.5 py-2.5 rounded-xl border border-slate-200 text-xs text-slate-800 focus:outline-none focus:ring-2 focus:ring-[#2563EB]">
                            @error('tanggal')
                                <p class="text-[11px] text-[#DC2626] mt-1">{{ $message }}</p>
                            @enderror
                        </div>

                        <div class="grid grid-cols-2 gap-3">
                            <div>
                                <label for="cek-jam-mulai" class="block text-xs font-semibold text-slate-700 mb-1">Jam Mulai</label>
                                <input type="time"
                                       id="cek-jam-mulai"
                                       name="jam_mulai"
                                       value="{{ old('jam_mulai', $scheduleCheck['start_time_short'] ?? '08:00') }}"
                                       required
                                       class="w-full px-3.5 py-2.5 rounded-xl border border-slate-200 text-xs text-slate-800 focus:outline-none focus:ring-2 focus:ring-[#2563EB]">
                                @error('jam_mulai')
                                    <p class="text-[11px] text-[#DC2626] mt-1">{{ $message }}</p>
                                @enderror
                            </div>

                            <div>
                                <label for="cek-durasi" class="block text-xs font-semibold text-slate-700 mb-1">Durasi</label>
                                @php
                                    $selectedDuration = (int) old('durasi', $scheduleCheck['duration'] ?? 2);
                                @endphp
                                <select id="cek-durasi"
                                        name="durasi"
                                        class="w-full px-3.5 py-2.5 rounded-xl border border-slate-200 text-xs text-slate-800 bg-white focus:outline-none focus:ring-2 focus:ring-[#2563EB]">
                                    @for($h = 1; $h <= 8; $h++)
                                        <option value="{{ $h }}" {{ $selectedDuration === $h ? 'selected' : '' }}>{{ $h }} Jam</option>
                                    @endfor
                                </select>
                                @error('durasi')
                                    <p class="text-[11px] text-[#DC2626] mt-1">{{ $message }}</p>
                                @enderror
                            </div>
                        </div>

                        <button type="submit"
                                class="w-full py-2.5 px-4 rounded-xl bg-[#2563EB] hover:bg-blue-700 text-white text-xs font-semibold transition shadow-sm">
                            Periksa Ketersediaan Jadwal
                        </button>
                    </form>

                    @if($scheduleCheck)
                        <div class="pt-2">
                            @if($scheduleCheck['is_available'] && !$scheduleCheck['is_past_date'])
                                <div class="p-4 rounded-2xl bg-emerald-50 border border-emerald-200 space-y-1.5">
                                    <span class="inline-block px-2.5 py-0.5 rounded-full text-[10px] font-bold bg-[#059669] text-white">
                                        Jadwal Tersedia
                                    </span>
                                    <p class="text-xs font-semibold text-emerald-900">
                                        {{ \Carbon\Carbon::parse($scheduleCheck['date'])->locale('id')->translatedFormat('d M Y') }},
                                        {{ $scheduleCheck['start_time_short'] }} - {{ $scheduleCheck['end_time_short'] }}
                                    </p>
                                    <p class="text-xs text-emerald-800 leading-relaxed">{{ $scheduleCheck['message'] }}</p>
                                </div>
                            @elseif($scheduleCheck['has_conflict'] || !$scheduleCheck['valid_range'] || !$scheduleCheck['facility_usable'] || !$scheduleCheck['stock_sufficient'])
                                <div class="p-4 rounded-2xl bg-rose-50 border border-rose-200 space-y-2">
                                    <span class="inline-block px-2.5 py-0.5 rounded-full text-[10px] font-bold bg-[#DC2626] text-white">
                                        {{ $scheduleCheck['has_conflict'] ? 'Jadwal Bentrok' : 'Tidak Tersedia' }}
                                    </span>
                                    <p class="text-xs text-rose-900 leading-relaxed font-medium">{{ $scheduleCheck['message'] }}</p>

                                    @if($scheduleCheck['conflicts']->isNotEmpty())
                                        <div class="pt-1 space-y-1.5">
                                            <p class="text-[11px] font-bold text-rose-800">Jadwal yang bertabrakan:</p>
                                            @foreach($scheduleCheck['conflicts'] as $conflict)
                                                <div class="px-3 py-2 rounded-xl bg-white/80 border border-rose-200 text-[11px] text-rose-900 flex items-center justify-between">
                                                    <span class="font-semibold">
                                                        {{ substr((string) $conflict->start_time, 0, 5) }} - {{ substr((string) $conflict->end_time, 0, 5) }}
                                                    </span>
                                                    <span class="uppercase font-bold text-[10px]">{{ $conflict->status }}</span>
                                                </div>
                                            @endforeach
                                        </div>
                                    @endif
                                </div>
                            @else
                                <div class="p-4 rounded-2xl bg-amber-50 border border-amber-200 space-y-1.5">
                                    <span class="inline-block px-2.5 py-0.5 rounded-full text-[10px] font-bold bg-[#D97706] text-white">
                                        Tanggal Lampau
                                    </span>
                                    <p class="text-xs text-amber-900 leading-relaxed">{{ $scheduleCheck['message'] }}</p>
                                </div>
                            @endif
                        </div>
                    @endif
                </section>

                <!-- 2. Informasi Waktu Tersedia -->
                <section class="bg-white rounded-3xl p-6 shadow-sm border border-slate-100 space-y-4">
                    <div>
                        <h2 class="text-base font-bold text-[#0C1A40]">Waktu Tersedia ({{ $formattedSelectedDate }})</h2>
                        <p class="text-xs text-slate-500 mt-0.5">Rentang waktu kosong dan status slot standar (08:00 - 18:00).</p>
                    </div>

                    <!-- Rentang Waktu Bebas -->
                    <div>
                        <p class="text-[11px] font-semibold text-slate-500 uppercase tracking-wider mb-2">Rentang Waktu Kosong</p>
                        @if(count($freeWindows) > 0)
                            <div class="flex flex-wrap gap-2">
                                @foreach($freeWindows as $window)
                                    <span class="px-3 py-1 rounded-full text-xs font-semibold bg-emerald-50 text-[#059669] border border-emerald-200">
                                        {{ $window }}
                                    </span>
                                @endforeach
                            </div>
                        @else
                            <p class="text-xs text-[#DC2626]">Seluruh jam operasional telah terjadwal.</p>
                        @endif
                    </div>

                    <!-- Daftar Slot 2 Jam -->
                    <div class="space-y-2 pt-1">
                        <p class="text-[11px] font-semibold text-slate-500 uppercase tracking-wider">Status Slot Cepat</p>
                        @foreach($timeSlots as $slot)
                            <a href="{{ route('fasilitas.show', ['facility' => $facility->id, 'tanggal' => $selectedDate, 'jam_mulai' => $slot['start'], 'durasi' => 2, 'cek_jadwal' => 1]) }}"
                               class="flex items-center justify-between px-3.5 py-2.5 rounded-xl border transition {{ $slot['available'] ? 'bg-[#F8FAFC] border-slate-200 hover:border-[#2563EB]' : 'bg-rose-50/50 border-rose-200' }}">
                                <span class="text-xs font-semibold text-slate-800">{{ $slot['label'] }}</span>
                                @if($slot['available'])
                                    <span class="px-2.5 py-0.5 rounded-full text-[10px] font-bold bg-emerald-50 text-[#059669] border border-emerald-200">
                                        Tersedia
                                    </span>
                                @else
                                    <span class="px-2.5 py-0.5 rounded-full text-[10px] font-bold bg-rose-50 text-[#DC2626] border border-rose-200">
                                        Bentrok
                                    </span>
                                @endif
                            </a>
                        @endforeach
                    </div>
                </section>

                <!-- 3. Tombol Pengalihan ke Form Peminjaman M4 -->
                <section id="ajukan-peminjaman" class="bg-white rounded-3xl p-6 shadow-sm border border-slate-100 space-y-4">
                    <div>
                        <h2 class="text-base font-bold text-[#0C1A40]">Ajukan Peminjaman</h2>
                        <p class="text-xs text-slate-500 mt-0.5">
                            @if($isLab)
                                Pengajuan laboratorium diteruskan ke form peminjaman (M4) dengan pemeriksaan jadwal dan izin Dosen.
                            @else
                                Pengajuan barang diteruskan ke form peminjaman (M4) untuk fasilitas ini.
                            @endif
                        </p>
                    </div>

                    @auth
                        @if(auth()->user()->role === 'mahasiswa')
                            @if(!$availability['borrowable'])
                                <button type="button" disabled
                                        class="w-full py-3 px-4 rounded-xl bg-slate-200 text-slate-500 text-xs font-semibold cursor-not-allowed">
                                    Fasilitas Tidak Tersedia
                                </button>
                            @elseif($scheduleCheck && $scheduleCheck['has_conflict'])
                                <div class="p-3 rounded-xl bg-rose-50 border border-rose-200 text-[11px] text-[#DC2626] font-medium">
                                    Slot waktu yang Anda periksa bentrok. Pilih jam lain sebelum melanjutkan ke form peminjaman.
                                </div>
                                @if($borrowAction['m4_available'])
                                    <a href="{{ $borrowAction['url'] }}"
                                       class="block w-full text-center py-3 px-4 rounded-xl border border-[#2563EB] text-[#2563EB] hover:bg-blue-50 text-xs font-semibold transition">
                                        Lanjutkan ke Form Peminjaman (Pilih Waktu Lain)
                                    </a>
                                @endif
                            @elseif($borrowAction['m4_available'])
                                <a href="{{ $borrowAction['url'] }}"
                                   class="block w-full text-center py-3 px-4 rounded-xl bg-[#2563EB] hover:bg-blue-700 text-white text-xs font-semibold transition shadow-sm">
                                    {{ $borrowAction['label'] }}
                                </a>
                            @else
                                <div class="p-3 rounded-xl bg-amber-50 border border-amber-200 text-[11px] text-amber-800 leading-relaxed">
                                    Tautan pengajuan ke Form Peminjaman (Modul M4) untuk <span class="font-mono font-bold">facility_id={{ $facility->id }}</span> siap dihubungkan setelah rute Modul M4 aktif.
                                </div>
                                <button type="button" disabled
                                        class="w-full py-3 px-4 rounded-xl bg-slate-200 text-slate-500 text-xs font-semibold cursor-not-allowed">
                                    Menunggu Integrasi Form M4
                                </button>
                            @endif
                        @else
                            <div class="p-3.5 rounded-2xl bg-slate-50 border border-slate-200 text-xs text-slate-600 leading-relaxed">
                                Anda masuk sebagai <strong class="capitalize">{{ auth()->user()->role }}</strong>. Pengajuan peminjaman hanya dapat dilakukan oleh akun <strong>Mahasiswa</strong>.
                            </div>
                        @endif
                    @else
                        <a href="{{ route('login') }}"
                           class="block w-full text-center py-3 px-4 rounded-xl bg-[#2563EB] hover:bg-blue-700 text-white text-xs font-semibold transition shadow-sm">
                            Masuk untuk Ajukan Peminjaman
                        </a>
                    @endauth
                </section>
            </div>
        </div>
    </div>

    <!-- Footer -->
    <footer class="bg-white border-t border-slate-200 px-6 sm:px-8 lg:px-14 py-6 mt-8">
        <div class="max-w-7xl mx-auto flex flex-col sm:flex-row items-center justify-between gap-3 text-xs text-slate-500">
            <p>&copy; {{ date('Y') }} SIPAFKA — Program Studi Ilmu Komputer FMIPA Universitas Halu Oleo.</p>
            <div class="flex items-center space-x-5">
                <a href="{{ route('home') }}" class="hover:text-[#2563EB] transition">Beranda</a>
                <a href="{{ route('fasilitas.index') }}" class="hover:text-[#2563EB] transition">Katalog Fasilitas</a>
                <a href="{{ route('panduan.index') }}" class="hover:text-[#2563EB] transition">Panduan</a>
            </div>
        </div>
    </footer>
</div>
@endsection
