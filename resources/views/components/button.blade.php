@props([
    'variant' => 'primary', // primary (blue), navy, success (green), danger (red), warning (yellow), outline, secondary
    'size' => 'md', // sm, md, lg
    'type' => 'button',
    'href' => null,
])

@php
    $baseClasses = "inline-flex items-center justify-center font-semibold rounded-xl transition duration-150 focus:outline-none focus:ring-2 focus:ring-offset-2 disabled:opacity-50 disabled:cursor-not-allowed";

    $sizeClasses = match($size) {
        'sm' => 'px-3 py-1.5 text-xs',
        'lg' => 'px-6 py-3 text-base',
        default => 'px-4 py-2.5 text-sm',
    };

    $variantClasses = match($variant) {
        'primary' => 'bg-[#2563EB] hover:bg-blue-700 text-white shadow-sm focus:ring-blue-500',
        'navy' => 'bg-[#0C1A40] hover:bg-navy-800 text-white shadow-sm focus:ring-navy-900',
        'success' => 'bg-[#059669] hover:bg-emerald-700 text-white shadow-sm focus:ring-emerald-500',
        'danger' => 'bg-[#DC2626] hover:bg-rose-700 text-white shadow-sm focus:ring-rose-500',
        'warning' => 'bg-[#D97706] hover:bg-amber-700 text-white shadow-sm focus:ring-amber-500',
        'outline' => 'border border-slate-300 bg-white text-slate-700 hover:bg-slate-50 focus:ring-slate-400',
        default => 'bg-slate-100 hover:bg-slate-200 text-slate-700 focus:ring-slate-300',
    };

    $classes = "{$baseClasses} {$sizeClasses} {$variantClasses}";
@endphp

@if($href)
    <a href="{{ $href }}" {{ $attributes->merge(['class' => $classes]) }}>
        {{ $slot }}
    </a>
@else
    <button type="{{ $type }}" {{ $attributes->merge(['class' => $classes]) }}>
        {{ $slot }}
    </button>
@endif
