@if ($paginator->hasPages())
<nav role="navigation" aria-label="Navigasi halaman" class="flex items-center justify-between gap-3">
    <p class="hidden text-sm text-slate-500 sm:block">
        Menampilkan <span class="font-semibold text-navy-900">{{ $paginator->firstItem() }}</span>–<span class="font-semibold text-navy-900">{{ $paginator->lastItem() }}</span> dari <span class="font-semibold text-navy-900">{{ $paginator->total() }}</span>
    </p>
    <div class="flex items-center gap-1">
        @if ($paginator->onFirstPage())
            <span class="inline-flex h-9 w-9 items-center justify-center rounded-lg text-slate-300"><x-icon name="chevron-left" class="h-4 w-4" /></span>
        @else
            <a href="{{ $paginator->previousPageUrl() }}" wire:click.prevent="previousPage('{{ $paginator->getPageName() }}')" rel="prev" class="inline-flex h-9 w-9 items-center justify-center rounded-lg text-slate-600 hover:bg-navy-50"><x-icon name="chevron-left" class="h-4 w-4" /></a>
        @endif
        @foreach ($elements as $element)
            @if (is_string($element))
                <span class="px-2 text-sm text-slate-400">…</span>
            @endif
            @if (is_array($element))
                @foreach ($element as $page => $url)
                    @if ($page == $paginator->currentPage())
                        <span aria-current="page" class="inline-flex h-9 min-w-9 items-center justify-center rounded-lg bg-navy-900 px-3 text-sm font-semibold text-white">{{ $page }}</span>
                    @else
                        <a href="{{ $url }}" wire:click.prevent="gotoPage({{ $page }}, '{{ $paginator->getPageName() }}')" class="inline-flex h-9 min-w-9 items-center justify-center rounded-lg px-3 text-sm font-medium text-slate-600 hover:bg-navy-50">{{ $page }}</a>
                    @endif
                @endforeach
            @endif
        @endforeach
        @if ($paginator->hasMorePages())
            <a href="{{ $paginator->nextPageUrl() }}" wire:click.prevent="nextPage('{{ $paginator->getPageName() }}')" rel="next" class="inline-flex h-9 w-9 items-center justify-center rounded-lg text-slate-600 hover:bg-navy-50"><x-icon name="chevron-right" class="h-4 w-4" /></a>
        @else
            <span class="inline-flex h-9 w-9 items-center justify-center rounded-lg text-slate-300"><x-icon name="chevron-right" class="h-4 w-4" /></span>
        @endif
    </div>
</nav>
@endif
