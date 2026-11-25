{{-- [MARKER M6] : Navigasi Internal Modul 6 (Scanner QR, Konfirmasi Pengembalian, Riwayat & Audit) --}}
@php
    $returnCount = $activeReturnCount ?? \App\Models\BorrowRequest::where('status', 'dipinjam')->doesntHave('returnRecord')->count();
@endphp

<div class="bg-white rounded-2xl p-1.5 border border-slate-200/80 shadow-sm inline-flex flex-wrap items-center gap-1.5">
    <a href="{{ route('dosen.pindai-qr') }}"
       class="inline-flex items-center gap-2 px-4 py-2 rounded-xl text-xs font-semibold transition {{ request()->routeIs('dosen.pindai-qr') ? 'bg-[#0C1A40] text-white shadow-sm' : 'text-slate-600 hover:text-slate-900 hover:bg-slate-100' }}">
        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v1m6 11h2m-6 0h-2v4m0-11v3m0 0h.01M12 12h4.01M16 20h4M4 12h4m12 0h.01M5 8h2a1 1 0 001-1V5a1 1 0 00-1-1H5a1 1 0 00-1 1v2a1 1 0 001 1zm12 0h2a1 1 0 001-1V5a1 1 0 00-1-1h-2a1 1 0 00-1 1v2a1 1 0 001 1zM5 20h2a1 1 0 001-1v-2a1 1 0 00-1-1H5a1 1 0 00-1 1v2a1 1 0 001 1z"></path>
        </svg>
        <span>Pindai QR Code</span>
    </a>

    <a href="{{ route('dosen.pengembalian.index') }}"
       class="inline-flex items-center gap-2 px-4 py-2 rounded-xl text-xs font-semibold transition {{ request()->routeIs('dosen.pengembalian.*') ? 'bg-[#2563EB] text-white shadow-sm' : 'text-slate-600 hover:text-slate-900 hover:bg-slate-100' }}">
        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path>
        </svg>
        <span>Konfirmasi Pengembalian</span>
        @if($returnCount > 0)
            <span class="px-2 py-0.5 rounded-full text-[10px] font-bold {{ request()->routeIs('dosen.pengembalian.*') ? 'bg-white/20 text-white' : 'bg-blue-100 text-[#2563EB]' }}">
                {{ $returnCount }}
            </span>
        @endif
    </a>

    <a href="{{ route('dosen.audit-pengembalian.index') }}"
       class="inline-flex items-center gap-2 px-4 py-2 rounded-xl text-xs font-semibold transition {{ request()->routeIs('dosen.audit-pengembalian.*') ? 'bg-[#0C1A40] text-white shadow-sm' : 'text-slate-600 hover:text-slate-900 hover:bg-slate-100' }}">
        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-3 7h3m-3 4h3m-6-4h.01M9 16h.01"></path>
        </svg>
        <span>Riwayat & Audit</span>
    </a>
</div>
