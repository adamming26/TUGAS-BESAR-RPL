@props([
    'headers' => [],
])

<div class="overflow-x-auto w-full rounded-2xl border border-slate-200/80 bg-white shadow-sm">
    <table {{ $attributes->merge(['class' => 'w-full text-left text-sm text-slate-600']) }}>
        @if(!empty($headers))
            <thead class="bg-slate-50/80 text-xs uppercase tracking-wider text-slate-500 font-bold border-b border-slate-200/80">
                <tr>
                    @foreach($headers as $header)
                        <th scope="col" class="px-6 py-3.5 whitespace-nowrap">
                            {{ $header }}
                        </th>
                    @endforeach
                </tr>
            </thead>
        @endif
        <tbody class="divide-y divide-slate-100">
            {{ $slot }}
        </tbody>
    </table>
</div>
