@extends('layouts.dashboard')

@section('content')
<!-- [MARKER M2] : KELOLA FASILITAS ADMIN PERSIS FIGMA (PAGE 13) -->
<div class="space-y-6">
    <!-- Header Title -->
    <div>
        <h1 class="text-2xl font-bold text-slate-900">Kelola Fasilitas</h1>
        <p class="text-xs text-slate-500 mt-0.5">Tambah dan perbarui data barang serta lab beserta kode fasilitasnya.</p>
    </div>

    <!-- Main Grid: Daftar Fasilitas (Kiri 7 cols) & Tambah/Ubah Form (Kanan 5 cols) -->
    <div class="grid grid-cols-1 lg:grid-cols-12 gap-6 items-start">
        <!-- Kolom Kiri: Daftar Fasilitas (7 Cols) Persis Figma -->
        <div class="lg:col-span-7 bg-white rounded-3xl p-6 sm:p-8 shadow-sm border border-slate-100 space-y-5">
            <h3 class="text-base font-bold text-slate-900">Daftar fasilitas</h3>

            <!-- Filter Tabs & Cari Data -->
            <div class="flex flex-col sm:flex-row items-center justify-between gap-3">
                <div class="flex items-center space-x-1 bg-slate-100 p-1 rounded-2xl w-full sm:w-auto">
                    <a href="{{ route('admin.kelola-fasilitas') }}" 
                       class="px-4 py-1.5 rounded-xl text-xs font-semibold transition {{ !request('type') ? 'bg-[#2563EB] text-white shadow-sm' : 'text-slate-600 hover:text-slate-900' }}">
                        Semua
                    </a>
                    <a href="{{ route('admin.kelola-fasilitas', ['type' => 'barang']) }}" 
                       class="px-4 py-1.5 rounded-xl text-xs font-semibold transition {{ request('type') === 'barang' ? 'bg-[#2563EB] text-white shadow-sm' : 'text-slate-600 hover:text-slate-900' }}">
                        Barang
                    </a>
                    <a href="{{ route('admin.kelola-fasilitas', ['type' => 'ruangan']) }}" 
                       class="px-4 py-1.5 rounded-xl text-xs font-semibold transition {{ request('type') === 'ruangan' ? 'bg-[#2563EB] text-white shadow-sm' : 'text-slate-600 hover:text-slate-900' }}">
                        Lab
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
                               placeholder="Cari data" 
                               class="w-full pl-3 pr-8 py-2 rounded-xl border border-slate-200 text-xs focus:outline-none focus:ring-2 focus:ring-[#2563EB]">
                        @if(request('search'))
                            <a href="{{ route('admin.kelola-fasilitas', request()->only('type')) }}" class="absolute right-2.5 top-2 text-slate-400 hover:text-slate-600 text-xs">&times;</a>
                        @endif
                    </div>
                </form>
            </div>

            <!-- Tombol Tambah Fasilitas -->
            <div>
                <a href="{{ route('admin.kelola-fasilitas', ['action' => 'new']) }}" 
                   class="inline-block px-5 py-2.5 rounded-xl bg-[#2563EB] hover:bg-blue-700 text-white font-semibold text-xs transition shadow-sm">
                    Tambah fasilitas
                </a>
            </div>

            <!-- Tabel Daftar Fasilitas Persis Figma -->
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
                            <tr class="hover:bg-slate-50/80 transition {{ ($editFacility?->id == $item->id) ? 'bg-blue-50/60' : '' }}">
                                <td class="py-3.5 px-3 font-mono font-medium text-slate-600">
                                    [{{ $item->code }}]
                                </td>
                                <td class="py-3.5 px-3 font-semibold text-slate-900">
                                    {{ $item->name }}
                                </td>
                                <td class="py-3.5 px-3 text-slate-500 capitalize">
                                    {{ $item->type === 'ruangan' ? 'Lab' : 'Barang' }}
                                </td>
                                <td class="py-3.5 px-3 text-slate-600">
                                    @if($item->type === 'barang')
                                        {{ $item->available_stock }} / {{ $item->total_stock }}
                                    @else
                                        {{ $item->capacity }} orang
                                    @endif
                                </td>
                                <td class="py-3.5 px-3">
                                    <span class="text-slate-700">{{ $item->condition }}</span>
                                </td>
                                <td class="py-3.5 px-3 text-center">
                                    @if($item->is_active)
                                        <span class="inline-block px-2.5 py-1 rounded-full text-[10px] font-semibold bg-emerald-50 text-emerald-700 border border-emerald-200">
                                            Aktif
                                        </span>
                                    @else
                                        <span class="inline-block px-2.5 py-1 rounded-full text-[10px] font-semibold bg-rose-50 text-rose-700 border border-rose-200">
                                            Nonaktif
                                        </span>
                                    @endif
                                </td>
                                <td class="py-3.5 px-3 text-right">
                                    <a href="{{ route('admin.kelola-fasilitas', ['edit_id' => $item->id]) }}" 
                                       class="inline-block px-3.5 py-1.5 rounded-xl border border-slate-200 text-slate-700 hover:bg-slate-50 text-xs font-semibold transition">
                                        Ubah
                                    </a>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="7" class="py-8 text-center text-slate-400">
                                    Belum ada data fasilitas.
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            <!-- Pagination Persis Figma -->
            <div class="flex flex-col sm:flex-row items-center justify-between gap-4 pt-4 border-t border-slate-100 text-xs text-slate-500">
                <div>
                    Menampilkan {{ $facilities->firstItem() ?? 0 }} dari {{ $facilities->total() }} data
                </div>
                <div class="flex items-center space-x-2">
                    @if($facilities->onFirstPage())
                        <span class="px-4 py-2 rounded-xl border border-slate-200 text-slate-300 cursor-not-allowed">
                            Sebelumnya
                        </span>
                    @else
                        <a href="{{ $facilities->previousPageUrl() }}" class="px-4 py-2 rounded-xl border border-slate-200 text-slate-700 hover:bg-slate-50 transition">
                            Sebelumnya
                        </a>
                    @endif

                    @if($facilities->hasMorePages())
                        <a href="{{ $facilities->nextPageUrl() }}" class="px-4 py-2 rounded-xl border border-slate-200 text-slate-700 hover:bg-slate-50 transition">
                            Berikutnya
                        </a>
                    @else
                        <span class="px-4 py-2 rounded-xl border border-slate-200 text-slate-300 cursor-not-allowed">
                            Berikutnya
                        </span>
                    @endif
                </div>
            </div>
        </div>

        <!-- Kolom Kanan: Tambah / Ubah Fasilitas Form (5 Cols) Persis Figma -->
        <div class="lg:col-span-5 bg-white rounded-3xl p-6 sm:p-8 shadow-sm border border-slate-100 space-y-5">
            <h3 class="text-base font-bold text-slate-900">
                {{ $editFacility ? 'Ubah fasilitas' : 'Tambah / ubah fasilitas' }}
            </h3>

            <!-- Switcher Tabs Barang / Lab Form -->
            @php
                $formType = $editFacility ? $editFacility->type : (request('form_type', 'barang'));
            @endphp
            <div class="flex items-center space-x-1 bg-slate-100 p-1 rounded-2xl w-fit">
                <a href="{{ route('admin.kelola-fasilitas', array_merge(request()->query(), ['form_type' => 'barang'])) }}" 
                   class="px-5 py-1.5 rounded-xl text-xs font-semibold transition {{ $formType === 'barang' ? 'bg-[#2563EB] text-white shadow-sm' : 'text-slate-600 hover:text-slate-900' }}">
                    Barang
                </a>
                <a href="{{ route('admin.kelola-fasilitas', array_merge(request()->query(), ['form_type' => 'ruangan'])) }}" 
                   class="px-5 py-1.5 rounded-xl text-xs font-semibold transition {{ $formType === 'ruangan' ? 'bg-[#2563EB] text-white shadow-sm' : 'text-slate-600 hover:text-slate-900' }}">
                    Lab
                </a>
            </div>

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
                    <label for="code" class="block text-xs font-semibold text-slate-700 mb-1.5">Kode fasilitas</label>
                    <input type="text" 
                           name="code" 
                           id="code" 
                           value="{{ old('code', $editFacility?->code ?? ($formType === 'barang' ? 'BRG-0' . (App\Models\Facility::where('type','barang')->count() + 1) : 'LAB-0' . (App\Models\Facility::where('type','ruangan')->count() + 1))) }}"
                           placeholder="[KODE-00]" 
                           required
                           class="w-full px-4 py-2.5 rounded-xl border border-slate-200 text-xs focus:outline-none focus:ring-2 focus:ring-[#2563EB]">
                    @error('code')
                        <p class="text-xs text-rose-600 mt-1">{{ $message }}</p>
                    @enderror
                </div>

                <!-- Nama Fasilitas -->
                <div>
                    <label for="name" class="block text-xs font-semibold text-slate-700 mb-1.5">Nama</label>
                    <input type="text" 
                           name="name" 
                           id="name" 
                           value="{{ old('name', $editFacility?->name) }}"
                           placeholder="[Nama fasilitas]" 
                           required
                           class="w-full px-4 py-2.5 rounded-xl border border-slate-200 text-xs focus:outline-none focus:ring-2 focus:ring-[#2563EB]">
                    @error('name')
                        <p class="text-xs text-rose-600 mt-1">{{ $message }}</p>
                    @enderror
                </div>

                <!-- Stok Total / Kapasitas & Kondisi -->
                <div class="grid grid-cols-2 gap-3">
                    <div>
                        <label for="stock_cap" class="block text-xs font-semibold text-slate-700 mb-1.5">
                            {{ $formType === 'barang' ? 'Stok total' : 'Kapasitas' }}
                        </label>
                        @if($formType === 'barang')
                            <input type="number" 
                                   name="total_stock" 
                                   id="stock_cap" 
                                   min="0"
                                   value="{{ old('total_stock', $editFacility?->total_stock ?? 1) }}"
                                   required
                                   class="w-full px-3 py-2.5 rounded-xl border border-slate-200 text-xs focus:outline-none focus:ring-2 focus:ring-[#2563EB]">
                            @if($editFacility)
                                <input type="hidden" name="available_stock" value="{{ $editFacility->available_stock }}">
                            @endif
                        @else
                            <input type="number" 
                                   name="capacity" 
                                   id="stock_cap" 
                                   min="1"
                                   value="{{ old('capacity', $editFacility?->capacity ?? 50) }}"
                                   required
                                   class="w-full px-3 py-2.5 rounded-xl border border-slate-200 text-xs focus:outline-none focus:ring-2 focus:ring-[#2563EB]">
                        @endif
                    </div>

                    <div>
                        <label for="condition" class="block text-xs font-semibold text-slate-700 mb-1.5">Kondisi</label>
                        <select name="condition" id="condition" class="w-full px-3 py-2.5 rounded-xl border border-slate-200 text-xs bg-white focus:outline-none focus:ring-2 focus:ring-[#2563EB]">
                            <option value="Baik" {{ (old('condition', $editFacility?->condition) === 'Baik') ? 'selected' : '' }}>Baik</option>
                            <option value="Rusak Ringan" {{ (old('condition', $editFacility?->condition) === 'Rusak Ringan') ? 'selected' : '' }}>Rusak Ringan</option>
                            <option value="Rusak Berat" {{ (old('condition', $editFacility?->condition) === 'Rusak Berat') ? 'selected' : '' }}>Rusak Berat</option>
                        </select>
                    </div>
                </div>

                <!-- Status -->
                <div>
                    <label for="is_active" class="block text-xs font-semibold text-slate-700 mb-1.5">Status</label>
                    <select name="is_active" id="is_active" class="w-full px-4 py-2.5 rounded-xl border border-slate-200 text-xs bg-white focus:outline-none focus:ring-2 focus:ring-[#2563EB]">
                        <option value="1" {{ (old('is_active', $editFacility?->is_active ?? 1) == 1) ? 'selected' : '' }}>Aktif</option>
                        <option value="0" {{ (old('is_active', $editFacility?->is_active ?? 1) == 0) ? 'selected' : '' }}>Nonaktif</option>
                    </select>
                </div>

                <!-- Tombol Simpan & Batal Persis Figma -->
                <div class="flex items-center space-x-3 pt-3">
                    <button type="submit" 
                            class="px-6 py-2.5 rounded-xl bg-[#2563EB] hover:bg-blue-700 text-white font-semibold text-xs transition shadow-sm">
                        Simpan
                    </button>
                    <a href="{{ route('admin.kelola-fasilitas') }}" 
                       class="px-6 py-2.5 rounded-xl border border-slate-200 text-slate-600 hover:bg-slate-50 font-semibold text-xs transition">
                        Batal
                    </a>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection
