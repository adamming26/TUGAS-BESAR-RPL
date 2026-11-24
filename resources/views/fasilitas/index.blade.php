@extends('layouts.app')

@section('content')
<div class="min-h-screen bg-[#F8FAFC]">
    <!-- Navbar -->
    <div class="relative bg-[#0C1A40] text-white">
        @include('partials.navbar')
        
        <!-- Header Hero Banner -->
        <div class="pt-28 pb-16 px-6 lg:px-12 max-w-7xl mx-auto">
            <span class="inline-block px-3 py-1 rounded-full bg-blue-500/20 border border-blue-400/30 text-blue-300 text-xs font-semibold uppercase tracking-wider mb-3">
                Katalog Inventaris Prodi
            </span>
            <h1 class="text-3xl lg:text-4xl font-extrabold text-white tracking-tight mb-2">
                Fasilitas Kampus & Laboratorium
            </h1>
            <p class="text-sm text-slate-300 max-w-xl">
                Cek ketersediaan perangkat proyektor, kabel ekstensi, serta jadwal laboratorium komputer Program Studi Ilmu Komputer FMIPA UHO.
            </p>
        </div>
    </div>

    <!-- Main Content Container -->
    <div class="max-w-7xl mx-auto px-6 lg:px-12 py-10 space-y-8">
        <!-- Filter Bar -->
        <div class="bg-white rounded-3xl p-5 shadow-sm border border-slate-100 flex flex-col sm:flex-row items-center justify-between gap-4">
            <div class="flex items-center space-x-1 bg-slate-100 p-1 rounded-2xl w-full sm:w-auto">
                <a href="{{ route('fasilitas.index') }}" 
                   class="px-4 py-2 rounded-xl text-xs font-semibold transition {{ !request('type') ? 'bg-[#2563EB] text-white shadow-sm' : 'text-slate-600 hover:text-slate-900' }}">
                    Semua
                </a>
                <a href="{{ route('fasilitas.index', ['type' => 'barang']) }}" 
                   class="px-4 py-2 rounded-xl text-xs font-semibold transition {{ request('type') === 'barang' ? 'bg-[#2563EB] text-white shadow-sm' : 'text-slate-600 hover:text-slate-900' }}">
                    Barang
                </a>
                <a href="{{ route('fasilitas.index', ['type' => 'ruangan']) }}" 
                   class="px-4 py-2 rounded-xl text-xs font-semibold transition {{ request('type') === 'ruangan' ? 'bg-[#2563EB] text-white shadow-sm' : 'text-slate-600 hover:text-slate-900' }}">
                    Laboratorium
                </a>
            </div>

            <form method="GET" action="{{ route('fasilitas.index') }}" class="w-full sm:w-72">
                @if(request('type'))
                    <input type="hidden" name="type" value="{{ request('type') }}">
                @endif
                <div class="relative">
                    <input type="text" 
                           name="search" 
                           value="{{ request('search') }}"
                           placeholder="Cari fasilitas..." 
                           class="w-full px-4 py-2 rounded-xl border border-slate-200 text-xs focus:outline-none focus:ring-2 focus:ring-[#2563EB]">
                    @if(request('search'))
                        <a href="{{ route('fasilitas.index', request()->only('type')) }}" class="absolute right-3 top-2 text-slate-400 hover:text-slate-600 text-xs">&times;</a>
                    @endif
                </div>
            </form>
        </div>

        <!-- Grid Cards Fasilitas -->
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
            @forelse($facilities as $item)
                <div class="bg-white rounded-3xl p-6 shadow-sm border border-slate-100 flex flex-col justify-between hover:shadow-md transition">
                    <div>
                        <div class="flex items-center justify-between mb-4">
                            <span class="px-2.5 py-1 rounded-full text-[10px] font-bold font-mono bg-slate-100 text-slate-600">
                                {{ $item->code }}
                            </span>
                            @if($item->type === 'barang')
                                @if($item->available_stock > 1)
                                    <span class="px-2.5 py-1 rounded-full text-[10px] font-semibold bg-emerald-50 text-emerald-700 border border-emerald-200">
                                        Tersedia ({{ $item->available_stock }} unit)
                                    </span>
                                @elseif($item->available_stock === 1)
                                    <span class="px-2.5 py-1 rounded-full text-[10px] font-semibold bg-amber-50 text-amber-700 border border-amber-200">
                                        Terbatas (1 unit)
                                    </span>
                                @else
                                    <span class="px-2.5 py-1 rounded-full text-[10px] font-semibold bg-rose-50 text-rose-700 border border-rose-200">
                                        Habis
                                    </span>
                                @endif
                            @else
                                <span class="px-2.5 py-1 rounded-full text-[10px] font-semibold bg-blue-50 text-blue-700 border border-blue-200">
                                    Kapasitas {{ $item->capacity }} kursi
                                </span>
                            @endif
                        </div>

                        <h3 class="text-lg font-bold text-slate-900 mb-1.5">{{ $item->name }}</h3>
                        <p class="text-xs text-slate-500 leading-relaxed mb-4">
                            {{ $item->description ?: 'Fasilitas penunjang perkuliahan Program Studi Ilmu Komputer.' }}
                        </p>
                    </div>

                    <div class="pt-4 border-t border-slate-100 flex items-center justify-between">
                        <span class="text-xs text-slate-400 capitalize">{{ $item->type === 'ruangan' ? 'Laboratorium' : 'Barang' }}</span>
                        @auth
                            @if(auth()->user()->role === 'mahasiswa')
                                <a href="{{ Route::has('mahasiswa.form-peminjaman') ? route('mahasiswa.form-peminjaman', ['jenis' => ($item->type === 'ruangan' ? 'lab' : 'barang'), 'id' => $item->id]) : route('home') }}" 
                                   class="px-4 py-2 rounded-xl bg-[#2563EB] hover:bg-blue-700 text-white font-semibold text-xs transition shadow-sm">
                                    {{ $item->type === 'ruangan' ? 'Ajukan Izin' : 'Pinjam Sekarang' }}
                                </a>
                            @endif
                        @else
                            <a href="{{ route('login') }}" 
                               class="px-4 py-2 rounded-xl border border-[#2563EB] text-[#2563EB] hover:bg-blue-50 font-semibold text-xs transition">
                                Masuk untuk Pinjam
                            </a>
                        @endauth
                    </div>
                </div>
            @empty
                <div class="col-span-3 py-16 text-center text-slate-400">
                    <p class="text-sm">Tidak ada fasilitas yang sesuai dengan pencarian Anda.</p>
                </div>
            @endforelse
        </div>

        <!-- Pagination -->
        <div class="pt-4">
            {{ $facilities->links() }}
        </div>
    </div>
</div>
@endsection
