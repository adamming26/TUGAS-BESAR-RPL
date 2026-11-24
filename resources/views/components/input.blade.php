@props([
    'label' => null,
    'name' => null,
    'type' => 'text',
    'placeholder' => null,
    'value' => null,
    'required' => false,
    'disabled' => false,
    'hint' => null,
    'error' => null,
])

@php
    $inputName = $name ?? '';
    $hasErrorsVar = isset($errors) && $errors instanceof \Illuminate\Support\ViewErrorBag;
    $errorMessage = $error ?? ($name && $hasErrorsVar ? $errors->first($name) : null);
    $hasError = !empty($errorMessage);
    $inputValue = old($name, $value);
@endphp

<div class="w-full space-y-1.5">
    @if($label)
        <label for="{{ $inputName }}" class="block text-xs font-semibold text-slate-700">
            {{ $label }}
            @if($required)
                <span class="text-rose-500">*</span>
            @endif
        </label>
    @endif

    <div class="relative">
        <input 
            type="{{ $type }}" 
            id="{{ $inputName }}" 
            name="{{ $inputName }}" 
            value="{{ $inputValue }}" 
            placeholder="{{ $placeholder }}"
            {{ $required ? 'required' : '' }}
            {{ $disabled ? 'disabled' : '' }}
            {{ $attributes->merge([
                'class' => 'w-full px-4 py-2.5 text-sm rounded-xl border bg-white transition focus:outline-none focus:ring-2 ' . 
                ($hasError 
                    ? 'border-rose-400 focus:ring-rose-200 focus:border-rose-500 text-rose-900' 
                    : 'border-slate-300 focus:ring-blue-100 focus:border-[#2563EB] text-slate-800') . 
                ($disabled ? ' bg-slate-100 cursor-not-allowed opacity-75' : '')
            ]) }}
        />
    </div>

    @if($errorMessage)
        <p class="text-xs text-rose-600 font-medium">{{ $errorMessage }}</p>
    @elseif($hint)
        <p class="text-[11px] text-slate-400">{{ $hint }}</p>
    @endif
</div>
