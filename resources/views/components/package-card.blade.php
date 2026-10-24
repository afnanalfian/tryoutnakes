@props(['package'])
@php($savings = $package->savings())
@php($cats = $package->categories())
<article {{ $attributes->merge(['class' => 'group relative flex flex-col overflow-hidden rounded-3xl border bg-white transition hover:-translate-y-1 hover:shadow-lift '.($package->is_featured ? 'border-brand-200 shadow-lift ring-1 ring-brand-100' : 'border-slate-200/80 shadow-soft')]) }}>
    <a href="{{ route('catalog.package', $package) }}" class="relative block aspect-[16/9] overflow-hidden bg-navy-50">
        <img src="{{ $package->imageUrl() }}" alt="{{ $package->name }}" loading="lazy" decoding="async" width="640" height="360" class="h-full w-full object-cover transition duration-500 group-hover:scale-[1.03]">
        @if($package->badge)
            <span class="badge absolute left-3 top-3 bg-amber-400 px-3 py-1 text-amber-950 shadow">{{ $package->badge }}</span>
        @endif
        @if($savings > 0)
            <span class="badge absolute right-3 top-3 bg-rose-500 px-3 py-1 text-white shadow">Hemat {{ rupiah($savings) }}</span>
        @endif
    </a>
    <div class="flex flex-1 flex-col p-5">
        <div class="flex flex-wrap gap-1.5">
            @foreach($cats->take(3) as $c)
                <x-category-badge :category="$c" />
            @endforeach
        </div>
        <h3 class="mt-3 text-lg font-bold leading-snug text-navy-900"><a href="{{ route('catalog.package', $package) }}" class="hover:text-brand-700">{{ $package->name }}</a></h3>
        @if($package->tagline)<p class="mt-1 line-clamp-2 text-sm text-slate-500">{{ $package->tagline }}</p>@endif
        <ul class="mt-4 space-y-1.5 text-sm text-slate-600">
            <li class="flex items-center gap-2"><x-icon name="check-circle" class="h-4 w-4 text-mint-600" /> {{ $package->tryouts->count() }} tryout lengkap</li>
            <li class="flex items-center gap-2"><x-icon name="check-circle" class="h-4 w-4 text-mint-600" /> Pembahasan &amp; peringkat</li>
        </ul>
        <div class="mt-auto flex items-end justify-between gap-3 pt-5">
            <div>
                @if($savings > 0)<p class="text-xs text-slate-400 line-through">{{ rupiah($package->normalPrice()) }}</p>@endif
                <p class="text-xl font-extrabold tracking-tight text-navy-900">{{ rupiah($package->price) }}</p>
            </div>
            <a href="{{ route('catalog.package', $package) }}" class="btn-primary rounded-full px-4">Detail <x-icon name="arrow-right" class="h-4 w-4" /></a>
        </div>
    </div>
</article>
