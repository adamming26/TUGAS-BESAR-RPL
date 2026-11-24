@extends('layouts.dashboard')

@section('content')
<!-- MANAJEMEN PENGGUNA (CIVITAS AKADEMIKA) — ADMIN SIPAFKA -->
<div class="space-y-6">
    <!-- Header Title -->
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
        <div>
            <h1 class="text-2xl font-bold text-slate-900 tracking-tight">Kelola Pengguna</h1>
            <p class="text-xs text-slate-500 mt-1">Manajemen akun civitas akademika Universitas Halu Oleo (Mahasiswa, Dosen, dan Admin).</p>
        </div>
        <div>
            <a href="{{ route('admin.kelola-pengguna') }}" 
               class="inline-flex items-center space-x-2 px-4 py-2 rounded-xl bg-white border border-slate-200 text-xs font-semibold text-slate-700 hover:bg-slate-50 transition shadow-sm">
                <svg class="w-4 h-4 text-slate-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"></path>
                </svg>
                <span>Refresh Data</span>
            </a>
        </div>
    </div>

    <!-- Quick Stat Cards -->
    <div class="grid grid-cols-2 lg:grid-cols-4 gap-4">
        <div class="bg-white p-5 rounded-2xl border border-slate-100 shadow-sm flex items-center space-x-3.5">
            <div class="w-10 h-10 rounded-xl bg-blue-50 text-[#2563EB] flex items-center justify-center shrink-0">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z"></path>
                </svg>
            </div>
            <div>
                <p class="text-[11px] font-medium text-slate-400 uppercase">Total Akun</p>
                <h4 class="text-xl font-bold text-slate-900 leading-tight">{{ $countTotal ?? 0 }}</h4>
            </div>
        </div>

        <div class="bg-white p-5 rounded-2xl border border-slate-100 shadow-sm flex items-center space-x-3.5">
            <div class="w-10 h-10 rounded-xl bg-emerald-50 text-emerald-600 flex items-center justify-center shrink-0">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"></path>
                </svg>
            </div>
            <div>
                <p class="text-[11px] font-medium text-slate-400 uppercase">Mahasiswa</p>
                <h4 class="text-xl font-bold text-slate-900 leading-tight">{{ $countMahasiswa ?? 0 }}</h4>
            </div>
        </div>

        <div class="bg-white p-5 rounded-2xl border border-slate-100 shadow-sm flex items-center space-x-3.5">
            <div class="w-10 h-10 rounded-xl bg-indigo-50 text-indigo-600 flex items-center justify-center shrink-0">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19.428 15.428a2 2 0 00-1.022-.547l-2.387-.477a6 6 0 00-3.86.517l-.318.158a6 6 0 01-3.86.517L6.05 15.21a2 2 0 00-1.806.547M8 4h8l-1 1v5.172a2 2 0 00.586 1.414l5 5c1.26 1.26.367 3.414-1.415 3.414H4.828c-1.782 0-2.674-2.154-1.414-3.414l5-5A2 2 0 009 10.172V5L8 4z"></path>
                </svg>
            </div>
            <div>
                <p class="text-[11px] font-medium text-slate-400 uppercase">Dosen</p>
                <h4 class="text-xl font-bold text-slate-900 leading-tight">{{ $countDosen ?? 0 }}</h4>
            </div>
        </div>

        <div class="bg-white p-5 rounded-2xl border border-slate-100 shadow-sm flex items-center space-x-3.5">
            <div class="w-10 h-10 rounded-xl bg-rose-50 text-rose-600 flex items-center justify-center shrink-0">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z"></path>
                </svg>
            </div>
            <div>
                <p class="text-[11px] font-medium text-slate-400 uppercase">Administrator</p>
                <h4 class="text-xl font-bold text-slate-900 leading-tight">{{ $countAdmin ?? 0 }}</h4>
            </div>
        </div>
    </div>

    <!-- Panel Edit Pengguna (Aktif jika $editUser dipilih) -->
    @if($editUser)
        <div class="bg-gradient-to-r from-blue-50/80 via-indigo-50/40 to-white rounded-3xl p-6 sm:p-7 border border-blue-200/80 shadow-sm">
            <div class="flex items-center justify-between border-b border-blue-100 pb-4 mb-5">
                <div>
                    <h3 class="text-base font-bold text-slate-900 flex items-center space-x-2">
                        <span>Ubah Profil Pengguna: {{ $editUser->name }}</span>
                        <span class="text-xs font-mono font-medium text-[#2563EB] bg-white px-2 py-0.5 rounded-lg border border-blue-200">
                            ID: {{ $editUser->id }}
                        </span>
                    </h3>
                    <p class="text-xs text-slate-500 mt-0.5">Perbarui rincian identitas civitas akademika atau ubah kewenangan peran.</p>
                </div>
                <a href="{{ route('admin.kelola-pengguna', request()->only('role', 'search')) }}" 
                   class="px-3.5 py-1.5 rounded-xl border border-slate-200 bg-white text-slate-600 hover:bg-slate-50 font-semibold text-xs transition">
                    Tutup Edit
                </a>
            </div>

            <div class="grid grid-cols-1 lg:grid-cols-12 gap-6">
                <!-- Form Update Profil (8 Cols) -->
                <form method="POST" action="{{ route('admin.pengguna.update', $editUser->id) }}" class="lg:col-span-8 space-y-4">
                    @csrf
                    @method('PUT')

                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                        <div>
                            <label for="edit_name" class="block text-xs font-semibold text-slate-700 mb-1.5">Nama Lengkap <span class="text-rose-500">*</span></label>
                            <input type="text" 
                                   name="name" 
                                   id="edit_name" 
                                   value="{{ old('name', $editUser->name) }}" 
                                   required
                                   class="w-full px-4 py-2.5 rounded-xl border border-slate-200 text-xs focus:ring-2 focus:ring-[#2563EB] bg-white">
                            @error('name')
                                <p class="text-xs text-rose-600 mt-1">{{ $message }}</p>
                            @enderror
                        </div>

                        <div>
                            <label for="edit_email" class="block text-xs font-semibold text-slate-700 mb-1.5">Alamat Email <span class="text-rose-500">*</span></label>
                            <input type="email" 
                                   name="email" 
                                   id="edit_email" 
                                   value="{{ old('email', $editUser->email) }}" 
                                   required
                                   class="w-full px-4 py-2.5 rounded-xl border border-slate-200 text-xs focus:ring-2 focus:ring-[#2563EB] bg-white">
                            @error('email')
                                <p class="text-xs text-rose-600 mt-1">{{ $message }}</p>
                            @enderror
                        </div>

                        <div>
                            <label for="edit_nim_nip" class="block text-xs font-semibold text-slate-700 mb-1.5">NIM / NIP</label>
                            <input type="text" 
                                   name="nim_nip" 
                                   id="edit_nim_nip" 
                                   value="{{ old('nim_nip', $editUser->nim_nip) }}" 
                                   placeholder="Contoh: F1G125001" 
                                   class="w-full px-4 py-2.5 rounded-xl border border-slate-200 text-xs focus:ring-2 focus:ring-[#2563EB] bg-white">
                            @error('nim_nip')
                                <p class="text-xs text-rose-600 mt-1">{{ $message }}</p>
                            @enderror
                        </div>

                        <div>
                            <label for="edit_phone" class="block text-xs font-semibold text-slate-700 mb-1.5">No. Telepon / WhatsApp</label>
                            <input type="text" 
                                   name="phone" 
                                   id="edit_phone" 
                                   value="{{ old('phone', $editUser->phone) }}" 
                                   placeholder="Contoh: 08123456789" 
                                   class="w-full px-4 py-2.5 rounded-xl border border-slate-200 text-xs focus:ring-2 focus:ring-[#2563EB] bg-white">
                            @error('phone')
                                <p class="text-xs text-rose-600 mt-1">{{ $message }}</p>
                            @enderror
                        </div>
                    </div>

                    <div class="flex items-center space-x-3 pt-2">
                        <button type="submit" 
                                class="px-5 py-2.5 rounded-xl bg-[#2563EB] hover:bg-blue-700 text-white font-semibold text-xs transition shadow-sm">
                            Simpan Perubahan Profil
                        </button>
                    </div>
                </form>

                <!-- Form Ubah Role Pengguna (4 Cols) -->
                <div class="lg:col-span-4 bg-white p-5 rounded-2xl border border-blue-100 shadow-sm space-y-3">
                    <h4 class="text-xs font-bold text-slate-900 uppercase tracking-wider">Kewenangan Peran (Role)</h4>
                    <p class="text-[11px] text-slate-500 leading-relaxed">
                        Ubah tingkat akses akun ini di dalam sistem SIPAFKA.
                    </p>

                    <form method="POST" action="{{ route('admin.pengguna.update-role', $editUser->id) }}" class="space-y-3">
                        @csrf
                        @method('PUT')

                        <div>
                            <select name="role" 
                                    class="w-full px-3 py-2.5 rounded-xl border border-slate-200 text-xs bg-slate-50 focus:bg-white focus:ring-2 focus:ring-[#2563EB] font-medium"
                                    {{ $editUser->id === auth()->id() ? 'disabled' : '' }}>
                                <option value="mahasiswa" {{ $editUser->role === 'mahasiswa' ? 'selected' : '' }}>Mahasiswa</option>
                                <option value="dosen" {{ $editUser->role === 'dosen' ? 'selected' : '' }}>Dosen</option>
                                <option value="admin" {{ $editUser->role === 'admin' ? 'selected' : '' }}>Administrator</option>
                            </select>
                            @if($editUser->id === auth()->id())
                                <p class="text-[10px] text-amber-600 mt-1">Anda tidak dapat menurunkan role akun yang sedang Anda gunakan.</p>
                            @endif
                        </div>

                        <button type="submit" 
                                {{ $editUser->id === auth()->id() ? 'disabled' : '' }}
                                class="w-full px-4 py-2 rounded-xl border border-slate-300 hover:bg-slate-50 text-slate-700 font-semibold text-xs transition {{ $editUser->id === auth()->id() ? 'opacity-50 cursor-not-allowed' : '' }}">
                            Ubah Peran Akun
                        </button>
                    </form>
                </div>
            </div>
        </div>
    @endif

    <!-- Main Table Container: Daftar Pengguna -->
    <div class="bg-white rounded-3xl p-6 sm:p-8 shadow-sm border border-slate-100 space-y-6">
        <!-- Filter Tabs & Cari Data -->
        <div class="flex flex-col sm:flex-row items-center justify-between gap-3">
            <div class="flex items-center space-x-1 bg-slate-100 p-1 rounded-2xl w-full sm:w-auto overflow-x-auto">
                <a href="{{ route('admin.kelola-pengguna', request()->only('search')) }}" 
                   class="px-4 py-1.5 rounded-xl text-xs font-semibold whitespace-nowrap transition {{ !request('role') ? 'bg-[#2563EB] text-white shadow-sm' : 'text-slate-600 hover:text-slate-900' }}">
                    Semua ({{ $countTotal ?? 0 }})
                </a>
                <a href="{{ route('admin.kelola-pengguna', array_merge(request()->only('search'), ['role' => 'mahasiswa'])) }}" 
                   class="px-4 py-1.5 rounded-xl text-xs font-semibold whitespace-nowrap transition {{ request('role') === 'mahasiswa' ? 'bg-[#2563EB] text-white shadow-sm' : 'text-slate-600 hover:text-slate-900' }}">
                    Mahasiswa ({{ $countMahasiswa ?? 0 }})
                </a>
                <a href="{{ route('admin.kelola-pengguna', array_merge(request()->only('search'), ['role' => 'dosen'])) }}" 
                   class="px-4 py-1.5 rounded-xl text-xs font-semibold whitespace-nowrap transition {{ request('role') === 'dosen' ? 'bg-[#2563EB] text-white shadow-sm' : 'text-slate-600 hover:text-slate-900' }}">
                    Dosen ({{ $countDosen ?? 0 }})
                </a>
                <a href="{{ route('admin.kelola-pengguna', array_merge(request()->only('search'), ['role' => 'admin'])) }}" 
                   class="px-4 py-1.5 rounded-xl text-xs font-semibold whitespace-nowrap transition {{ request('role') === 'admin' ? 'bg-[#2563EB] text-white shadow-sm' : 'text-slate-600 hover:text-slate-900' }}">
                    Admin ({{ $countAdmin ?? 0 }})
                </a>
            </div>

            <!-- Form Pencarian Pengguna -->
            <form method="GET" action="{{ route('admin.kelola-pengguna') }}" class="w-full sm:w-72">
                @if(request('role'))
                    <input type="hidden" name="role" value="{{ request('role') }}">
                @endif
                <div class="relative">
                    <input type="text" 
                           name="search" 
                           value="{{ request('search') }}"
                           placeholder="Cari nama / NIM / email..." 
                           class="w-full pl-3 pr-8 py-2 rounded-xl border border-slate-200 text-xs focus:ring-2 focus:ring-[#2563EB] bg-slate-50/50">
                    @if(request('search'))
                        <a href="{{ route('admin.kelola-pengguna', request()->only('role')) }}" class="absolute right-2.5 top-2 text-slate-400 hover:text-slate-600 text-xs font-bold">&times;</a>
                    @endif
                </div>
            </form>
        </div>

        <!-- Tabel Pengguna -->
        <div class="overflow-x-auto">
            <table class="w-full text-left border-collapse">
                <thead>
                    <tr class="border-b border-slate-100 text-[11px] font-semibold text-slate-400 uppercase tracking-wider">
                        <th class="py-3 px-3">Nama & Email</th>
                        <th class="py-3 px-3">NIM / NIP</th>
                        <th class="py-3 px-3">No. Telepon</th>
                        <th class="py-3 px-3">Peran (Role)</th>
                        <th class="py-3 px-3">Terdaftar</th>
                        <th class="py-3 px-3 text-right">Aksi</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-50 text-xs text-slate-700">
                    @forelse($users as $u)
                        <tr class="hover:bg-slate-50/80 transition {{ ($editUser?->id == $u->id) ? 'bg-blue-50/60 border-l-4 border-l-[#2563EB]' : '' }}">
                            <td class="py-3.5 px-3">
                                <div class="flex items-center space-x-3">
                                    <div class="w-8 h-8 rounded-full bg-slate-100 text-slate-600 flex items-center justify-center font-bold text-xs uppercase shrink-0">
                                        {{ substr($u->name, 0, 1) }}
                                    </div>
                                    <div>
                                        <div class="font-semibold text-slate-900 flex items-center space-x-1.5">
                                            <span>{{ $u->name }}</span>
                                            @if($u->id === auth()->id())
                                                <span class="text-[9px] font-bold bg-blue-100 text-[#2563EB] px-1.5 py-0.5 rounded-md">Anda</span>
                                            @endif
                                        </div>
                                        <div class="text-[11px] text-slate-400">{{ $u->email }}</div>
                                    </div>
                                </div>
                            </td>
                            <td class="py-3.5 px-3 font-mono text-slate-600">
                                {{ $u->nim_nip ?: '-' }}
                            </td>
                            <td class="py-3.5 px-3 text-slate-500">
                                {{ $u->phone ?: '-' }}
                            </td>
                            <td class="py-3.5 px-3">
                                @if($u->role === 'admin')
                                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-[10px] font-semibold bg-rose-50 text-rose-700 border border-rose-200 uppercase">
                                        Administrator
                                    </span>
                                @elseif($u->role === 'dosen')
                                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-[10px] font-semibold bg-blue-50 text-blue-700 border border-blue-200 uppercase">
                                        Dosen
                                    </span>
                                @else
                                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-[10px] font-semibold bg-emerald-50 text-emerald-700 border border-emerald-200 uppercase">
                                        Mahasiswa
                                    </span>
                                @endif
                            </td>
                            <td class="py-3.5 px-3 text-slate-400">
                                {{ $u->created_at ? $u->created_at->format('d M Y') : '-' }}
                            </td>
                            <td class="py-3.5 px-3 text-right">
                                <div class="flex items-center justify-end space-x-1.5">
                                    <!-- Tombol Edit Pengguna -->
                                    <a href="{{ route('admin.kelola-pengguna', array_merge(request()->query(), ['edit_id' => $u->id])) }}" 
                                       class="px-2.5 py-1 rounded-lg border border-slate-200 text-slate-700 hover:bg-slate-50 text-xs font-semibold transition {{ ($editUser?->id == $u->id) ? 'bg-[#2563EB] text-white border-transparent' : '' }}"
                                       title="Ubah profil / role pengguna">
                                        Ubah
                                    </a>

                                    <!-- Tombol Hapus Pengguna -->
                                    @if($u->id !== auth()->id())
                                        <form id="delete-user-{{ $u->id }}" 
                                              method="POST" 
                                              action="{{ route('admin.pengguna.destroy', $u->id) }}" 
                                              class="inline">
                                            @csrf
                                            @method('DELETE')
                                            <button type="button" 
                                                    onclick="openConfirmModal('Hapus Pengguna', 'Apakah Anda yakin ingin menghapus akun civitas akademika \'{{ addslashes($u->name) }}\' ({{ $u->email }})? Data peminjaman historis tetap dicatat namun akun tidak dapat digunakan lagi.', 'Hapus Akun', true, function() { document.getElementById('delete-user-{{ $u->id }}').submit(); })"
                                                    class="px-2.5 py-1 rounded-lg border border-rose-200 text-rose-600 hover:bg-rose-50 text-xs font-semibold transition"
                                                    title="Hapus pengguna">
                                                Hapus
                                            </button>
                                        </form>
                                    @else
                                        <span class="px-2.5 py-1 rounded-lg border border-slate-100 text-slate-300 text-xs font-medium cursor-not-allowed" title="Tidak dapat menghapus akun sendiri">
                                            Hapus
                                        </span>
                                    @endif
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" class="py-12 text-center text-slate-400">
                                <svg class="w-10 h-10 mx-auto text-slate-300 mb-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z"></path>
                                </svg>
                                Tidak ada data pengguna yang sesuai dengan pencarian atau filter.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <!-- Pagination -->
        <div class="pt-4 border-t border-slate-100 text-xs">
            {{ $users->links() }}
        </div>
    </div>
</div>
@endsection
