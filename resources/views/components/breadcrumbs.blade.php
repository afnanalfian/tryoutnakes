@props(['items' => []])
<nav aria-label="Breadcrumb" {{ $attributes->merge(['class' => 'text-xs font-medium text-slate-500']) }}>
    <ol class="flex flex-wrap items-center gap-1.5">
        <li><a href="{{ route('home') }}" class="hover:text-navy-900">Beranda</a></li>
        @foreach($items as [$label, $url])
            <li class="flex items-center gap-1.5"><x-icon name="chevron-right" class="h-3 w-3" />
                @if($loop->last)<span class="text-navy-900">{{ $label }}</span>@else<a href="{{ $url }}" class="hover:text-navy-900">{{ $label }}</a>@endif
            </li>
        @endforeach
    </ol>
</nav>
