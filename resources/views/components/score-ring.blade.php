@props(['pct' => 0, 'size' => 160, 'stroke' => 12, 'label' => null, 'sub' => null, 'color' => '#1d55f0'])
@php($r = ($size - $stroke) / 2)
@php($c = 2 * M_PI * $r)
@php($off = $c * (1 - min(100, max(0, $pct)) / 100))
<div {{ $attributes->merge(['class' => 'relative inline-flex items-center justify-center']) }} style="width: {{ $size }}px; height: {{ $size }}px">
    <svg width="{{ $size }}" height="{{ $size }}" viewBox="0 0 {{ $size }} {{ $size }}" class="-rotate-90">
        <circle cx="{{ $size / 2 }}" cy="{{ $size / 2 }}" r="{{ $r }}" fill="none" stroke="#e3ebf7" stroke-width="{{ $stroke }}" />
        <circle cx="{{ $size / 2 }}" cy="{{ $size / 2 }}" r="{{ $r }}" fill="none" stroke="{{ $color }}" stroke-width="{{ $stroke }}" stroke-linecap="round"
                stroke-dasharray="{{ $c }}" stroke-dashoffset="{{ $off }}" style="transition: stroke-dashoffset 1s ease" />
    </svg>
    <div class="absolute inset-0 flex flex-col items-center justify-center text-center">
        <span class="font-display text-4xl font-semibold leading-none text-navy-900">{{ $label ?? round($pct) }}</span>
        @if($sub)<span class="mt-1 text-xs font-medium text-slate-500">{{ $sub }}</span>@endif
    </div>
</div>
