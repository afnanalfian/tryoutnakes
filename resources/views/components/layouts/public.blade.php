@props(['title' => null, 'noindex' => false])
<!DOCTYPE html>
<html lang="id" class="scroll-smooth">
<head>
    @include('partials.head', ['title' => $title, 'noindex' => $noindex])
</head>
<body class="min-h-screen bg-white font-sans">
    <a href="#konten" class="sr-only focus:not-sr-only focus:fixed focus:left-3 focus:top-3 focus:z-[60] focus:rounded-lg focus:bg-white focus:px-4 focus:py-2 focus:shadow">Lewati ke konten</a>

    {{-- Topbar berjalan --}}
    @php($topbar = array_filter(array_map('trim', explode("\n", (string) setting('topbar_items')))))
    @if($topbar)
    <div class="relative overflow-hidden bg-navy-950 text-[10.5px] font-semibold uppercase tracking-[.16em] text-navy-100">
        <div class="mask-fade-x flex w-max animate-marquee gap-10 py-2 hover:[animation-play-state:paused]" aria-hidden="true">
            @foreach(range(1, 4) as $_)
                @foreach($topbar as $item)
                    <span class="inline-flex items-center gap-2 whitespace-nowrap"><span class="text-brand-300">✦</span> {{ $item }}</span>
                @endforeach
            @endforeach
        </div>
    </div>
    @endif

    @include('partials.public-header')

    @if($activeAttempt ?? null)
        @include('partials.active-attempt-banner')
    @endif

    <main id="konten">
        {{ $slot }}
    </main>

    @include('partials.public-footer')
    @include('partials.flash')
    @livewireScriptConfig
</body>
</html>
