@props([
    'title' => null,
    'subtitle' => null,
    'footer' => null,
    'headerAction' => null,
])

<div {{ $attributes->merge(['class' => 'bg-white rounded-2xl border border-slate-200/80 shadow-sm overflow-hidden']) }}>
    @if($title || $subtitle || $headerAction)
        <div class="px-6 py-4 border-b border-slate-100 flex items-center justify-between">
            <div>
                @if($title)
                    <h3 class="text-base font-bold text-slate-900 leading-snug">{{ $title }}</h3>
                @endif
                @if($subtitle)
                    <p class="text-xs text-slate-500 mt-0.5">{{ $subtitle }}</p>
                @endif
            </div>
            @if($headerAction)
                <div>
                    {{ $headerAction }}
                </div>
            @endif
        </div>
    @endif

    <div class="p-6">
        {{ $slot }}
    </div>

    @if($footer)
        <div class="px-6 py-3 bg-slate-50 border-t border-slate-100 text-xs text-slate-500">
            {{ $footer }}
        </div>
    @endif
</div>
