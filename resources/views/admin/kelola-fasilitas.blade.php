@extends('layouts.dashboard')

@section('content')
<!-- KELOLA FASILITAS ADMIN (M1 — SIPAFKA) -->
<div class="space-y-6">
    <!-- Header Title -->
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
        <div>
            <h1 class="text-2xl font-bold text-slate-900 tracking-tight">Kelola Fasilitas Kampus</h1>
            <p class="text-xs text-slate-500 mt-1">Kelola data inventaris fasilitas barang dan ruangan laboratorium beserta ketersediaan stoknya.</p>
        </div>
        <div>
            <a href="{{ route('admin.kelola-fasilitas') }}" 
               class="inline-flex items-center space-x-2 px-4 py-2 rounded-xl bg-white border border-slate-200 text-xs font-semibold text-slate-700 hover:bg-slate-50 transition shadow-sm">
                <svg class="w-4 h-4 text-slate-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"></path>
                </svg>
                <span>Refresh Data</span>
            </a>
        </div>
    </div>

    <!-- Main Grid: Daftar Fasilitas (Kiri 7 cols) & Tambah/Ubah Form (Kanan 5 cols) -->
    <div class="grid grid-cols-1 lg:grid-cols-12 gap-6 items-start">
        <!-- Kolom Kiri: Daftar Fasilitas (7 Cols) -->
        <div class="lg:col-span-7 bg-white rounded-3xl p-6 sm:p-7 shadow-sm border border-slate-100 space-y-5">
            <div class="flex items-center justify-between">
                <div>
                    <h3 class="text-base font-bold text-slate-900">Daftar Fasilitas</h3>
                    <p class="text-xs text-slate-400 mt-0.5">Total {{ $facilities->total() }} fasilitas tercatat dalam sistem</p>
                </div>
                @if($editFacility)
                    <a href="{{ route('admin.kelola-fasilitas', request()->only('type', 'search')) }}" 
                       class="text-xs font-semibold text-[#2563EB] hover:underline flex items-center space-x-1">
                        <span>+ Mode Tambah Baru</span>
                    </a>
                @endif
            </div>

            <!-- Filter Tabs & Cari Data -->
            <div class="flex flex-col sm:flex-row items-center justify-between gap-3 pt-1">
                <div class="flex items-center space-x-1 bg-slate-100 p-1 rounded-2xl w-full sm:w-auto">
                    <a href="{{ route('admin.kelola-fasilitas', request()->only('search')) }}" 
                       class="px-3.5 py-1.5 rounded-xl text-xs font-semibold transition {{ !request('type') ? 'bg-[#2563EB] text-white shadow-sm' : 'text-slate-600 hover:text-slate-900' }}">
                        Semua ({{ $countAll ?? 0 }})
                    </a>
                    <a href="{{ route('admin.kelola-fasilitas', array_merge(request()->only('search'), ['type' => 'barang'])) }}" 
                       class="px-3.5 py-1.5 rounded-xl text-xs font-semibold transition {{ request('type') === 'barang' ? 'bg-[#2563EB] text-white shadow-sm' : 'text-slate-600 hover:text-slate-900' }}">
                        Barang ({{ $countBarang ?? 0 }})
                    </a>
                    <a href="{{ route('admin.kelola-fasilitas', array_merge(request()->only('search'), ['type' => 'ruangan'])) }}" 
                       class="px-3.5 py-1.5 rounded-xl text-xs font-semibold transition {{ request('type') === 'ruangan' ? 'bg-[#2563EB] text-white shadow-sm' : 'text-slate-600 hover:text-slate-900' }}">
                        Lab ({{ $countRuangan ?? 0 }})
                    </a>
                </div>

                <form method="GET" action="{{ route('admin.kelola-fasilitas') }}" class="w-full sm:w-56">
                    @if(request('type'))
                        <input type="hidden" name="type" value="{{ request('type') }}">
                    @endif
                    <div class="relative">
                        <input type="text" 
                               name="search" 
                               value="{{ request('search') }}"
                               placeholder="Cari kode / nama..." 
                               class="w-full pl-3 pr-8 py-2 rounded-xl border border-slate-200 text-xs focus:outline-none focus:ring-2 focus:ring-[#2563EB] bg-slate-50/50">
                        @if(request('search'))
                            <a href="{{ route('admin.kelola-fasilitas', request()->only('type')) }}" class="absolute right-2.5 top-2 text-slate-400 hover:text-slate-600 text-xs font-bold">&times;</a>
                        @endif
                    </div>
                </form>
            </div>

            <!-- Tabel Daftar Fasilitas -->
            <div class="overflow-x-auto">
                <table class="w-full text-left border-collapse">
                    <thead>
                        <tr class="border-b border-slate-100 text-[11px] font-semibold text-slate-400 uppercase tracking-wider">
                            <th class="py-3 px-3">Kode</th>
                            <th class="py-3 px-3">Nama</th>
                            <th class="py-3 px-3">Jenis</th>
                            <th class="py-3 px-3">Stok / Kapasitas</th>
                            <th class="py-3 px-3">Kondisi</th>
                            <th class="py-3 px-3 text-center">Status</th>
                            <th class="py-3 px-3 text-right">Aksi</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-50 text-xs text-slate-700">
                        @forelse($facilities as $item)
                            <tr class="hover:bg-slate-50/80 transition {{ ($editFacility?->id == $item->id) ? 'bg-blue-50/70 border-l-4 border-l-[#2563EB]' : '' }}">
                                <td class="py-3.5 px-3 font-mono font-medium text-slate-600">
                                    <span class="bg-slate-100 text-slate-700 px-2 py-0.5 rounded-md text-[11px] font-bold">
                                        {{ $item->code }}
                                    </span>
                                </td>
                                <td class="py-3.5 px-3">
                                    <span class="font-semibold text-slate-900 block">{{ $item->name }}</span>
                                    @if($item->description)
                                        <span class="text-[10px] text-slate-400 line-clamp-1 max-w-[140px]">{{ $item->description }}</span>
                                    @endif
                                </td>
                                <td class="py-3.5 px-3 text-slate-500 capitalize">
                                    @if($item->type === 'ruangan')
                                        <span class="inline-flex items-center text-purple-700 bg-purple-50 px-2 py-0.5 rounded-md font-medium text-[11px]">
                                            Laboratorium
                                        </span>
                                    @else
                                        <span class="inline-flex items-center text-blue-700 bg-blue-50 px-2 py-0.5 rounded-md font-medium text-[11px]">
                                            Barang
                                        </span>
                                    @endif
                                </td>
                                <td class="py-3.5 px-3 font-medium">
                                    @if($item->type === 'barang')
                                        <span class="{{ $item->available_stock <= 1 ? 'text-amber-600 font-bold' : 'text-slate-700' }}">
                                            {{ $item->available_stock }}
                                        </span>
                                        <span class="text-slate-400">/ {{ $item->total_stock }}</span>
                                    @else
                                        <span class="text-slate-700">{{ $item->capacity }} orang</span>
                                    @endif
                                </td>
                                <td class="py-3.5 px-3">
                                    @if($item->condition === 'Baik')
                                        <span class="inline-flex items-center text-emerald-700 bg-emerald-50 px-2 py-0.5 rounded-full text-[10px] font-medium border border-emerald-100">
                                            Baik
                                        </span>
                                    @elseif($item->condition === 'Rusak Ringan')
                                        <span class="inline-flex items-center text-amber-700 bg-amber-50 px-2 py-0.5 rounded-full text-[10px] font-medium border border-amber-100">
                                            Rusak Ringan
                                        </span>
                                    @else
                                        <span class="inline-flex items-center text-rose-700 bg-rose-50 px-2 py-0.5 rounded-full text-[10px] font-medium border border-rose-100">
                                            Rusak Berat
                                        </span>
                                    @endif
                                </td>
                                <td class="py-3.5 px-3 text-center">
                                    @if($item->is_active)
                                        <span class="inline-block px-2 py-0.5 rounded-full text-[10px] font-semibold bg-emerald-50 text-emerald-700 border border-emerald-200">
                                            Aktif
                                        </span>
                                    @else
                                        <span class="inline-block px-2 py-0.5 rounded-full text-[10px] font-semibold bg-slate-100 text-slate-500 border border-slate-200">
                                            Nonaktif
                                        </span>
                                    @endif
                                </td>
                                <td class="py-3.5 px-3 text-right">
                                    <div class="flex items-center justify-end space-x-1.5">
                                        <!-- Tombol Ubah -->
                                        <a href="{{ route('admin.kelola-fasilitas', array_merge(request()->query(), ['edit_id' => $item->id])) }}" 
                                           class="px-2.5 py-1 rounded-lg border border-slate-200 text-slate-700 hover:bg-slate-50 text-xs font-semibold transition {{ ($editFacility?->id == $item->id) ? 'bg-[#2563EB] text-white border-transparent' : '' }}"
                                           title="Ubah fasilitas">
                                            Ubah
                                        </a>

                                        <!-- Tombol Hapus dengan Konfirmasi Modal -->
                                        <form id="delete-facility-{{ $item->id }}" 
                                              method="POST" 
                                              action="{{ $item->type === 'barang' ? route('admin.destroy-barang', $item->id) : route('admin.destroy-ruangan', $item->id) }}" 
                                              class="inline">
                                            @csrf
                                            @method('DELETE')
                                            <button type="button" 
                                                    onclick="openConfirmModal('Hapus Fasilitas', 'Apakah Anda yakin ingin menghapus fasilitas \'{{ addslashes($item->name) }}\' ({{ $item->code }})? Data yang sudah dihapus tidak dapat dikembalikan.', 'Hapus', true, function() { document.getElementById('delete-facility-{{ $item->id }}').submit(); })"
                                                    class="px-2.5 py-1 rounded-lg border border-rose-200 text-rose-600 hover:bg-rose-50 text-xs font-semibold transition"
                                                    title="Hapus fasilitas">
                                                Hapus
                                            </button>
                                        </form>
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="7" class="py-12 text-center text-slate-400">
                                    <svg class="w-10 h-10 mx-auto text-slate-300 mb-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 012-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10"></path>
                                    </svg>
                                    Belum ada data fasilitas yang sesuai dengan pencarian atau filter.
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            <!-- Pagination -->
            <div class="flex flex-col sm:flex-row items-center justify-between gap-4 pt-4 border-t border-slate-100 text-xs text-slate-500">
                <div>
                    Menampilkan {{ $facilities->firstItem() ?? 0 }} - {{ $facilities->lastItem() ?? 0 }} dari {{ $facilities->total() }} data
                </div>
                <div class="flex items-center space-x-2">
                    @if($facilities->onFirstPage())
                        <span class="px-3 py-1.5 rounded-xl border border-slate-200 text-slate-300 cursor-not-allowed">
                            Sebelumnya
                        </span>
                    @else
                        <a href="{{ $facilities->previousPageUrl() }}" class="px-3 py-1.5 rounded-xl border border-slate-200 text-slate-700 hover:bg-slate-50 transition">
                            Sebelumnya
                        </a>
                    @endif

                    @if($facilities->hasMorePages())
                        <a href="{{ $facilities->nextPageUrl() }}" class="px-3 py-1.5 rounded-xl border border-slate-200 text-slate-700 hover:bg-slate-50 transition">
                            Berikutnya
                        </a>
                    @else
                        <span class="px-3 py-1.5 rounded-xl border border-slate-200 text-slate-300 cursor-not-allowed">
                            Berikutnya
                        </span>
                    @endif
                </div>
            </div>
        </div>

        <!-- Kolom Kanan: Tambah / Ubah Fasilitas Form (5 Cols) -->
        <div class="lg:col-span-5 bg-white rounded-3xl p-6 sm:p-7 shadow-sm border border-slate-100 space-y-5">
            <div class="flex items-center justify-between border-b border-slate-100 pb-4">
                <div>
                    <h3 class="text-base font-bold text-slate-900">
                        {{ $editFacility ? 'Ubah Fasilitas' : 'Tambah Fasilitas Baru' }}
                    </h3>
                    <p class="text-xs text-slate-400 mt-0.5">
                        {{ $editFacility ? 'Perbarui rincian data fasilitas terpilih' : 'Daftarkan barang atau lab ke sistem inventaris' }}
                    </p>
                </div>
                @if($editFacility)
                    <span class="bg-blue-50 text-[#2563EB] border border-blue-200 px-2.5 py-1 rounded-xl text-xs font-mono font-bold">
                        {{ $editFacility->code }}
                    </span>
                @endif
            </div>

            <!-- Switcher Tabs Barang / Lab Form (Hanya saat tambah baru) -->
            @php
                $formType = $editFacility ? $editFacility->type : (request('form_type', 'barang'));
            @endphp
            @if(!$editFacility)
                <div class="flex items-center space-x-1 bg-slate-100 p-1 rounded-2xl w-fit">
                    <a href="{{ route('admin.kelola-fasilitas', array_merge(request()->query(), ['form_type' => 'barang'])) }}" 
                       class="px-5 py-1.5 rounded-xl text-xs font-semibold transition {{ $formType === 'barang' ? 'bg-[#2563EB] text-white shadow-sm' : 'text-slate-600 hover:text-slate-900' }}">
                        Fasilitas Barang
                    </a>
                    <a href="{{ route('admin.kelola-fasilitas', array_merge(request()->query(), ['form_type' => 'ruangan'])) }}" 
                       class="px-5 py-1.5 rounded-xl text-xs font-semibold transition {{ $formType === 'ruangan' ? 'bg-[#2563EB] text-white shadow-sm' : 'text-slate-600 hover:text-slate-900' }}">
                        Laboratorium
                    </a>
                </div>
            @else
                <div class="text-xs text-slate-500 bg-slate-50 p-2.5 rounded-xl border border-slate-100 flex items-center justify-between">
                    <span>Tipe Fasilitas: <strong class="text-slate-800 capitalize">{{ $editFacility->type === 'ruangan' ? 'Laboratorium' : 'Barang' }}</strong></span>
                    <a href="{{ route('admin.kelola-fasilitas', request()->only('type', 'search')) }}" class="text-xs font-semibold text-[#2563EB] hover:underline">
                        Batal Ubah
                    </a>
                </div>
            @endif

            <!-- Form Action URL -->
            <form method="POST" 
                  action="{{ $editFacility 
                            ? ($editFacility->type === 'barang' ? route('admin.update-barang', $editFacility->id) : route('admin.update-ruangan', $editFacility->id)) 
                            : ($formType === 'barang' ? route('admin.store-barang') : route('admin.store-ruangan')) }}" 
                  class="space-y-4">
                @csrf
                @if($editFacility)
                    @method('PUT')
                @endif

                <!-- Kode Fasilitas -->
                <div>
                    <label for="code" class="block text-xs font-semibold text-slate-700 mb-1.5">
                        Kode Fasilitas <span class="text-rose-500">*</span>
                    </label>
                    <input type="text" 
                           name="code" 
                           id="code" 
                           value="{{ old('code', $editFacility?->code ?? ($formType === 'barang' ? 'BRG-0' . (App\Models\Facility::where('type','barang')->count() + 1) : 'LAB-0' . (App\Models\Facility::where('type','ruangan')->count() + 1))) }}"
                           placeholder="Contoh: BRG-01 atau LAB-01" 
                           required
                           class="w-full px-4 py-2.5 rounded-xl border {{ $errors->has('code') ? 'border-rose-400 ring-1 ring-rose-300' : 'border-slate-200' }} text-xs focus:outline-none focus:ring-2 focus:ring-[#2563EB]">
                    @error('code')
                        <p class="text-xs text-rose-600 mt-1">{{ $message }}</p>
                    @enderror
                </div>

                <!-- Nama Fasilitas -->
                <div>
                    <label for="name" class="block text-xs font-semibold text-slate-700 mb-1.5">
                        Nama Fasilitas <span class="text-rose-500">*</span>
                    </label>
                    <input type="text" 
                           name="name" 
                           id="name" 
                           value="{{ old('name', $editFacility?->name) }}"
                           placeholder="Contoh: Proyektor Epson EB-X400 / Lab Rekayasa" 
                           required
                           class="w-full px-4 py-2.5 rounded-xl border {{ $errors->has('name') ? 'border-rose-400 ring-1 ring-rose-300' : 'border-slate-200' }} text-xs focus:outline-none focus:ring-2 focus:ring-[#2563EB]">
                    @error('name')
                        <p class="text-xs text-rose-600 mt-1">{{ $message }}</p>
                    @enderror
                </div>

                <!-- Bagian Spesifik: Barang vs Lab -->
                @if($formType === 'barang')
                    <!-- Pengaturan Stok Barang -->
                    <div class="grid grid-cols-2 gap-3">
                        <div>
                            <label for="total_stock" class="block text-xs font-semibold text-slate-700 mb-1.5">
                                Stok Total <span class="text-rose-500">*</span>
                            </label>
                            <input type="number" 
                                   name="total_stock" 
                                   id="total_stock" 
                                   min="0"
                                   value="{{ old('total_stock', $editFacility?->total_stock ?? 1) }}"
                                   required
                                   class="w-full px-3 py-2.5 rounded-xl border {{ $errors->has('total_stock') ? 'border-rose-400 ring-1 ring-rose-300' : 'border-slate-200' }} text-xs focus:outline-none focus:ring-2 focus:ring-[#2563EB]">
                            @error('total_stock')
                                <p class="text-[11px] text-rose-600 mt-1">{{ $message }}</p>
                            @enderror
                        </div>

                        <div>
                            <label for="available_stock" class="block text-xs font-semibold text-slate-700 mb-1.5">
                                Stok Tersedia <span class="text-rose-500">*</span>
                            </label>
                            @if($editFacility)
                                <input type="number" 
                                       name="available_stock" 
                                       id="available_stock" 
                                       min="0"
                                       value="{{ old('available_stock', $editFacility->available_stock) }}"
                                       required
                                       class="w-full px-3 py-2.5 rounded-xl border {{ $errors->has('available_stock') ? 'border-rose-400 ring-1 ring-rose-300' : 'border-slate-200' }} text-xs focus:outline-none focus:ring-2 focus:ring-[#2563EB]">
                                @error('available_stock')
                                    <p class="text-[11px] text-rose-600 mt-1">{{ $message }}</p>
                                @enderror
                            @else
                                <div class="px-3 py-2.5 rounded-xl bg-slate-50 border border-slate-200 text-slate-500 text-xs">
                                    Sama dgn stok total
                                </div>
                            @endif
                        </div>
                    </div>
                @else
                    <!-- Pengaturan Kapasitas Lab -->
                    <div>
                        <label for="capacity" class="block text-xs font-semibold text-slate-700 mb-1.5">
                            Kapasitas Laboratorium (Orang) <span class="text-rose-500">*</span>
                        </label>
                        <input type="number" 
                               name="capacity" 
                               id="capacity" 
                               min="1"
                               value="{{ old('capacity', $editFacility?->capacity ?? 40) }}"
                               required
                               class="w-full px-4 py-2.5 rounded-xl border {{ $errors->has('capacity') ? 'border-rose-400 ring-1 ring-rose-300' : 'border-slate-200' }} text-xs focus:outline-none focus:ring-2 focus:ring-[#2563EB]">
                        @error('capacity')
                            <p class="text-xs text-rose-600 mt-1">{{ $message }}</p>
                        @enderror
                    </div>
                @endif

                <!-- Kondisi & Status -->
                <div class="grid grid-cols-2 gap-3">
                    <div>
                        <label for="condition" class="block text-xs font-semibold text-slate-700 mb-1.5">Kondisi</label>
                        <select name="condition" id="condition" class="w-full px-3 py-2.5 rounded-xl border border-slate-200 text-xs bg-white focus:outline-none focus:ring-2 focus:ring-[#2563EB]">
                            <option value="Baik" {{ (old('condition', $editFacility?->condition) === 'Baik') ? 'selected' : '' }}>Baik</option>
                            <option value="Rusak Ringan" {{ (old('condition', $editFacility?->condition) === 'Rusak Ringan') ? 'selected' : '' }}>Rusak Ringan</option>
                            <option value="Rusak Berat" {{ (old('condition', $editFacility?->condition) === 'Rusak Berat') ? 'selected' : '' }}>Rusak Berat</option>
                        </select>
                    </div>

                    <div>
                        <label for="is_active" class="block text-xs font-semibold text-slate-700 mb-1.5">Status Aktif</label>
                        <select name="is_active" id="is_active" class="w-full px-3 py-2.5 rounded-xl border border-slate-200 text-xs bg-white focus:outline-none focus:ring-2 focus:ring-[#2563EB]">
                            <option value="1" {{ (old('is_active', $editFacility?->is_active ?? 1) == 1) ? 'selected' : '' }}>Aktif</option>
                            <option value="0" {{ (old('is_active', $editFacility?->is_active ?? 1) == 0) ? 'selected' : '' }}>Nonaktif</option>
                        </select>
                    </div>
                </div>

                <!-- Deskripsi Fasilitas -->
                <div>
                    <label for="description" class="block text-xs font-semibold text-slate-700 mb-1.5">Deskripsi / Lokasi (Opsional)</label>
                    <textarea name="description" 
                              id="description" 
                              rows="3" 
                              placeholder="Keterangan spesifikasi, ruangan, atau catatan pemakaian..." 
                              class="w-full px-4 py-2.5 rounded-xl border border-slate-200 text-xs focus:outline-none focus:ring-2 focus:ring-[#2563EB]">{{ old('description', $editFacility?->description) }}</textarea>
                    @error('description')
                        <p class="text-xs text-rose-600 mt-1">{{ $message }}</p>
                    @enderror
                </div>

                <!-- Tombol Simpan & Batal -->
                <div class="flex items-center space-x-3 pt-3">
                    <button type="submit" 
                            class="px-6 py-2.5 rounded-xl bg-[#2563EB] hover:bg-blue-700 text-white font-semibold text-xs transition shadow-sm">
                        {{ $editFacility ? 'Simpan Perubahan' : 'Simpan Fasilitas' }}
                    </button>
                    @if($editFacility)
                        <a href="{{ route('admin.kelola-fasilitas', request()->only('type', 'search')) }}" 
                           class="px-6 py-2.5 rounded-xl border border-slate-200 text-slate-600 hover:bg-slate-50 font-semibold text-xs transition">
                            Batal
                        </a>
                    @else
                        <button type="reset" 
                                class="px-6 py-2.5 rounded-xl border border-slate-200 text-slate-600 hover:bg-slate-50 font-semibold text-xs transition">
                            Reset
                        </button>
                    @endif
                </div>
            </form>
        </div>
    </div>
</div>
@endsection
