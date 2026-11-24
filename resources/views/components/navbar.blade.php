@props([
    'title' => 'SIPAFKA',
    'subtitle' => 'Peminjaman Fasilitas Kampus',
    'user' => null,
])

@php
    $currentUser = $user ?? auth()->user();
@endphp

<header class="bg-white/90 backdrop-blur-md border-b border-slate-200/80 sticky top-0 z-30 px-6 lg:px-10 py-3.5 flex items-center justify-between">
    <!-- Left Section: Mobile Toggle & Brand / Title -->
    <div class="flex items-center space-x-4">
        <button type="button" onclick="typeof toggleSidebar === 'function' ? toggleSidebar() : null" class="p-2 rounded-xl text-slate-600 hover:bg-slate-100 lg:hidden focus:outline-none" aria-label="Buka menu">
            <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16"></path>
            </svg>
        </button>

        <div>
            <h1 class="text-base lg:text-lg font-bold text-slate-900 leading-tight">{{ $title }}</h1>
            @if($subtitle)
                <p class="text-xs text-slate-500 leading-tight hidden sm:block">{{ $subtitle }}</p>
            @endif
        </div>
    </div>

    <!-- Center / Custom Slot -->
    <div class="flex-1 px-4 max-w-md hidden md:block">
        {{ $slot }}
    </div>

    <!-- Right Section: User Profile & Actions -->
    <div class="flex items-center space-x-3">
        @if($currentUser)
            <div class="text-right hidden sm:block">
                <p class="text-sm font-semibold text-slate-900 leading-tight">
                    {{ $currentUser->name }}
                </p>
                <p class="text-xs text-slate-500 capitalize leading-tight">
                    {{ $currentUser->role }}
                </p>
            </div>
            <div class="w-10 h-10 rounded-full bg-[#2563EB] text-white flex items-center justify-center font-bold text-sm shadow-sm ring-2 ring-blue-100">
                {{ strtoupper(substr($currentUser->name ?? 'U', 0, 1)) }}
            </div>
        @else
            <a href="{{ route('login') }}" class="px-4 py-2 rounded-xl bg-[#2563EB] text-white text-xs font-semibold hover:bg-blue-700 transition">
                Masuk
            </a>
        @endif
    </div>
</header>
