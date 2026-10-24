@props(['label', 'value', 'icon' => 'chart', 'tone' => 'navy', 'hint' => null])
@php($tones = ['navy' => 'bg-navy-50 text-navy-700', 'brand' => 'bg-brand-50 text-brand-700', 'mint' => 'bg-mint-50 text-mint-700', 'amber' => 'bg-amber-50 text-amber-700', 'rose' => 'bg-rose-50 text-rose-700'])
<div {{ $attributes->merge(['class' => 'card p-4 sm:p-5']) }}>
    <div class="flex items-start justify-between gap-3">
        <div class="min-w-0">
            <p class="text-xs font-semibold uppercase tracking-wide text-slate-500">{{ $label }}</p>
            <p class="mt-1.5 truncate text-2xl font-extrabold tracking-tight text-navy-900">{{ $value }}</p>
            @if($hint)<p class="mt-1 text-xs text-slate-500">{{ $hint }}</p>@endif
        </div>
        <span class="inline-flex h-10 w-10 shrink-0 items-center justify-center rounded-xl {{ $tones[$tone] ?? $tones['navy'] }}"><x-icon :name="$icon" /></span>
    </div>
</div>
