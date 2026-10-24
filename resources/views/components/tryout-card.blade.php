@props(['tryout', 'href' => null, 'owned' => false, 'showPrice' => false, 'attempt' => null])
@php($cat = $tryout->category)
<article {{ $attributes->merge(['class' => 'group flex flex-col overflow-hidden rounded-2xl border border-slate-200/80 bg-white shadow-soft transition hover:-translate-y-0.5 hover:shadow-lift']) }}>
    <a href="{{ $href ?? '#' }}" class="relative block aspect-[16/9] overflow-hidden bg-navy-50">
        <img src="{{ $tryout->imageUrl() }}" alt="{{ $tryout->title }}" loading="lazy" decoding="async" width="640" height="360" class="h-full w-full object-cover transition duration-500 group-hover:scale-[1.03]">
        <div class="absolute left-3 top-3 flex flex-wrap gap-1.5">
            <span class="badge bg-white/95 text-navy-900 shadow-sm backdrop-blur"><x-icon :name="$cat->icon" class="h-3.5 w-3.5" stroke="2" /> {{ $cat->isSkd() ? 'SKD' : 'SKB' }}</span>
            @if($tryout->is_free)<span class="badge bg-mint-500 text-white">Gratis</span>@endif
        </div>
        @if($attempt)
            <span class="badge absolute right-3 top-3 bg-navy-900/90 text-white">Nilai {{ score_format($attempt->score) }}</span>
        @endif
    </a>
    <div class="flex flex-1 flex-col p-4">
        <p class="text-[11px] font-bold uppercase tracking-[.14em]" style="color: {{ $cat->color }}">{{ $cat->label() }}</p>
        <h3 class="mt-1 line-clamp-2 font-bold leading-snug text-navy-900"><a href="{{ $href ?? '#' }}" class="hover:text-brand-700">{{ $tryout->title }}</a></h3>
        <div class="mt-3 flex flex-wrap items-center gap-x-4 gap-y-1 text-xs text-slate-500">
            <span class="inline-flex items-center gap-1.5"><x-icon name="document" class="h-4 w-4" /> {{ $tryout->questions_count ?? $tryout->questionCount() }} soal</span>
            <span class="inline-flex items-center gap-1.5"><x-icon name="clock" class="h-4 w-4" /> {{ $tryout->duration_minutes }} menit</span>
            @unless($cat->isSkd())<span class="inline-flex items-center gap-1.5"><x-icon name="chart" class="h-4 w-4" /> Skala {{ score_format($tryout->scale_max ?? 100) }}</span>@endunless
        </div>
        <div class="mt-auto pt-4">{{ $slot }}</div>
    </div>
</article>
