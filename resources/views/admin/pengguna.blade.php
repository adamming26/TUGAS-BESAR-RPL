@extends('layouts.dashboard')

@section('content')
<!-- [MARKER M6] : KELOLA PENGGUNA ADMIN -->
<div class="space-y-6">
    <div>
        <h1 class="text-2xl font-bold text-slate-900">Kelola Pengguna</h1>
        <p class="text-xs text-slate-500 mt-0.5">Daftar akun civitas akademika (Mahasiswa, Dosen, dan Admin).</p>
    </div>

    <div class="bg-white rounded-3xl p-6 sm:p-8 shadow-sm border border-slate-100 space-y-6">
        <div class="flex flex-col sm:flex-row items-center justify-between gap-3">
            <div class="flex items-center space-x-1 bg-slate-100 p-1 rounded-2xl w-full sm:w-auto">
                <a href="{{ route('admin.kelola-pengguna') }}" 
                   class="px-4 py-1.5 rounded-xl text-xs font-semibold transition {{ !request('role') ? 'bg-[#2563EB] text-white shadow-sm' : 'text-slate-600 hover:text-slate-900' }}">
                    Semua
                </a>
                <a href="{{ route('admin.kelola-pengguna', ['role' => 'mahasiswa']) }}" 
                   class="px-4 py-1.5 rounded-xl text-xs font-semibold transition {{ request('role') === 'mahasiswa' ? 'bg-[#2563EB] text-white shadow-sm' : 'text-slate-600 hover:text-slate-900' }}">
                    Mahasiswa
                </a>
                <a href="{{ route('admin.kelola-pengguna', ['role' => 'dosen']) }}" 
                   class="px-4 py-1.5 rounded-xl text-xs font-semibold transition {{ request('role') === 'dosen' ? 'bg-[#2563EB] text-white shadow-sm' : 'text-slate-600 hover:text-slate-900' }}">
                    Dosen
                </a>
                <a href="{{ route('admin.kelola-pengguna', ['role' => 'admin']) }}" 
                   class="px-4 py-1.5 rounded-xl text-xs font-semibold transition {{ request('role') === 'admin' ? 'bg-[#2563EB] text-white shadow-sm' : 'text-slate-600 hover:text-slate-900' }}">
                    Admin
                </a>
            </div>

            <form method="GET" action="{{ route('admin.kelola-pengguna') }}" class="w-full sm:w-64">
                @if(request('role'))
                    <input type="hidden" name="role" value="{{ request('role') }}">
                @endif
                <div class="relative">
                    <input type="text" 
                           name="search" 
                           value="{{ request('search') }}"
                           placeholder="Cari pengguna..." 
                           class="w-full px-4 py-2 rounded-xl border border-slate-200 text-xs focus:ring-2 focus:ring-[#2563EB]">
                </div>
            </form>
        </div>

        <div class="overflow-x-auto">
            <table class="w-full text-left border-collapse">
                <thead>
                    <tr class="border-b border-slate-100 text-[11px] font-semibold text-slate-400 uppercase tracking-wider">
                        <th class="py-3 px-3">Nama</th>
                        <th class="py-3 px-3">NIM / NIP</th>
                        <th class="py-3 px-3">Email</th>
                        <th class="py-3 px-3">Peran</th>
                        <th class="py-3 px-3 text-right">Terdaftar</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-50 text-xs text-slate-700">
                    @forelse($users as $u)
                        <tr class="hover:bg-slate-50/80 transition">
                            <td class="py-3.5 px-3 font-semibold text-slate-900">
                                {{ $u->name }}
                            </td>
                            <td class="py-3.5 px-3 font-mono text-slate-600">
                                {{ $u->nim_nip ?: '-' }}
                            </td>
                            <td class="py-3.5 px-3 text-slate-600">
                                {{ $u->email }}
                            </td>
                            <td class="py-3.5 px-3">
                                @if($u->role === 'admin')
                                    <span class="px-2.5 py-1 rounded-full text-[10px] font-semibold bg-rose-50 text-rose-700 border border-rose-200 uppercase">Admin</span>
                                @elseif($u->role === 'dosen')
                                    <span class="px-2.5 py-1 rounded-full text-[10px] font-semibold bg-blue-50 text-blue-700 border border-blue-200 uppercase">Dosen</span>
                                @else
                                    <span class="px-2.5 py-1 rounded-full text-[10px] font-semibold bg-emerald-50 text-emerald-700 border border-emerald-200 uppercase">Mahasiswa</span>
                                @endif
                            </td>
                            <td class="py-3.5 px-3 text-right text-slate-400">
                                {{ $u->created_at->format('d M Y') }}
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="5" class="py-8 text-center text-slate-400">
                                Tidak ada data pengguna yang sesuai.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <div class="pt-4 border-t border-slate-100 text-xs">
            {{ $users->links() }}
        </div>
    </div>
</div>
@endsection
