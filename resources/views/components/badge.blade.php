@props([
    'status' => null,
    'variant' => null,
])

@php
    $inferredVariant = $variant;

    if (!$inferredVariant && $status) {
        $inferredVariant = match(strtolower($status)) {
            'disetujui', 'aktif', 'tersedia', 'selesai', 'baik' => 'success',
            'menunggu', 'terbatas', 'terjadwal' => 'warning',
            'ditolak', 'habis', 'rusak berat', 'rusak ringan', 'nonaktif' => 'danger',
            'dipinjam', 'berlangsung' => 'info',
            'dikembalikan' => 'teal',
            default => 'default',
        };
    }

    $classes = match($inferredVariant) {
        'success' => 'bg-emerald-50 text-[#059669] border border-emerald-200',
        'warning' => 'bg-amber-50 text-[#D97706] border border-amber-200',
        'danger' => 'bg-rose-50 text-[#DC2626] border border-rose-200',
        'info' => 'bg-blue-50 text-[#2563EB] border border-blue-200',
        'teal' => 'bg-teal-50 text-teal-700 border border-teal-200',
        'navy' => 'bg-slate-100 text-[#0C1A40] border border-slate-300',
        default => 'bg-slate-100 text-slate-700 border border-slate-200',
    };
@endphp

<span {{ $attributes->merge(['class' => "inline-flex items-center px-2.5 py-1 rounded-full text-xs font-semibold tracking-wide {$classes}"]) }}>
    {{ $slot }}
</span>
