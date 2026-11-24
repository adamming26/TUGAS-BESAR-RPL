@props([
    'type' => 'info', // success, error, warning, info
    'title' => null,
    'dismissible' => true,
])

@php
    $typeClasses = match($type) {
        'success' => 'bg-emerald-50 text-emerald-800 border-emerald-200',
        'error', 'danger' => 'bg-rose-50 text-rose-800 border-rose-200',
        'warning' => 'bg-amber-50 text-amber-800 border-amber-200',
        default => 'bg-blue-50 text-blue-800 border-blue-200',
    };

    $iconColor = match($type) {
        'success' => 'text-[#059669]',
        'error', 'danger' => 'text-[#DC2626]',
        'warning' => 'text-[#D97706]',
        default => 'text-[#2563EB]',
    };
@endphp

<div {{ $attributes->merge(['class' => "p-4 rounded-2xl border flex items-start space-x-3 shadow-sm {$typeClasses} transition duration-200"]) }} role="alert">
    <!-- Icon -->
    <div class="shrink-0 mt-0.5">
        @if($type === 'success')
            <svg class="w-5 h-5 {{ $iconColor }}" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path>
            </svg>
        @elseif($type === 'error' || $type === 'danger')
            <svg class="w-5 h-5 {{ $iconColor }}" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path>
            </svg>
        @elseif($type === 'warning')
            <svg class="w-5 h-5 {{ $iconColor }}" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"></path>
            </svg>
        @else
            <svg class="w-5 h-5 {{ $iconColor }}" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path>
            </svg>
        @endif
    </div>

    <!-- Message Content -->
    <div class="flex-1 text-sm leading-relaxed">
        @if($title)
            <h4 class="font-bold mb-0.5">{{ $title }}</h4>
        @endif
        <div>{{ $slot }}</div>
    </div>

    <!-- Dismiss Button -->
    @if($dismissible)
        <button type="button" onclick="this.closest('[role=alert]').remove()" class="text-slate-400 hover:text-slate-600 focus:outline-none p-1 shrink-0" aria-label="Tutup notifikasi">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
            </svg>
        </button>
    @endif
</div>
