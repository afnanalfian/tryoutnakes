{{-- Grafik garis SVG ringan (tanpa library). series: [['name'=>..,'color'=>..,'values'=>[...]]] --}}
@props(['labels' => [], 'series' => [], 'height' => 220, 'max' => null, 'threshold' => null, 'suffix' => ''])
@php
    $w = 640; $h = $height; $pl = 36; $pr = 12; $pt = 14; $pb = 28;
    $n = max(1, count($labels));
    $all = collect($series)->flatMap(fn ($s) => $s['values'])->filter(fn ($v) => $v !== null);
    $ymax = $max ?? max(10, (float) ($all->max() ?? 10));
    $ymax = $ymax > 0 ? $ymax : 1;
    $x = fn ($i) => $n === 1 ? ($pl + ($w - $pl - $pr) / 2) : $pl + ($w - $pl - $pr) * $i / ($n - 1);
    $y = fn ($v) => $pt + ($h - $pt - $pb) * (1 - min($v, $ymax) / $ymax);
    $ticks = collect(range(0, 4))->map(fn ($k) => round($ymax * $k / 4));
@endphp
<div {{ $attributes->merge(['class' => 'w-full']) }}>
    <svg viewBox="0 0 {{ $w }} {{ $h }}" class="h-auto w-full" role="img" aria-label="Grafik progres">
        @foreach($ticks as $t)
            <line x1="{{ $pl }}" x2="{{ $w - $pr }}" y1="{{ $y($t) }}" y2="{{ $y($t) }}" stroke="#e2e8f0" stroke-dasharray="3 4" />
            <text x="{{ $pl - 8 }}" y="{{ $y($t) + 4 }}" text-anchor="end" font-size="11" fill="#94a3b8">{{ $t }}</text>
        @endforeach
        @if($threshold !== null)
            <line x1="{{ $pl }}" x2="{{ $w - $pr }}" y1="{{ $y($threshold) }}" y2="{{ $y($threshold) }}" stroke="#f59e0b" stroke-width="1.5" stroke-dasharray="6 5" />
            <text x="{{ $w - $pr }}" y="{{ $y($threshold) - 6 }}" text-anchor="end" font-size="11" font-weight="600" fill="#b45309">Passing {{ $threshold }}{{ $suffix }}</text>
        @endif
        @foreach($series as $s)
            @php($pts = collect($s['values'])->map(fn ($v, $i) => $v === null ? null : [$x($i), $y($v), $v])->filter()->values())
            @if($pts->count() > 1)
                <path d="M{{ $pts->map(fn ($p) => round($p[0], 1).','.round($p[1], 1))->implode(' L') }}" fill="none" stroke="{{ $s['color'] }}" stroke-width="2.5" stroke-linejoin="round" stroke-linecap="round" />
                @if(!empty($s['area']))
                    <path d="M{{ round($pts->first()[0], 1) }},{{ $h - $pb }} L{{ $pts->map(fn ($p) => round($p[0], 1).','.round($p[1], 1))->implode(' L') }} L{{ round($pts->last()[0], 1) }},{{ $h - $pb }} Z" fill="{{ $s['color'] }}" opacity=".08" />
                @endif
            @endif
            @foreach($pts as $p)
                <circle cx="{{ $p[0] }}" cy="{{ $p[1] }}" r="4" fill="#fff" stroke="{{ $s['color'] }}" stroke-width="2.2"><title>{{ $s['name'] }}: {{ score_format($p[2]) }}{{ $suffix }}</title></circle>
            @endforeach
        @endforeach
        @foreach($labels as $i => $l)
            @if($n <= 8 || $i % (int) ceil($n / 8) === 0)
                <text x="{{ $x($i) }}" y="{{ $h - 8 }}" text-anchor="middle" font-size="11" fill="#64748b">{{ $l }}</text>
            @endif
        @endforeach
    </svg>
    @if(count($series) > 1)
        <div class="mt-2 flex flex-wrap gap-4 text-xs text-slate-600">
            @foreach($series as $s)
                <span class="inline-flex items-center gap-1.5"><span class="h-2.5 w-2.5 rounded-full" style="background: {{ $s['color'] }}"></span>{{ $s['name'] }}</span>
            @endforeach
        </div>
    @endif
</div>
