@props(['title' => null])
<!DOCTYPE html>
<html lang="id">
<head>
    @include('partials.head', ['title' => $title ? $title.' | '.setting('site_name') : null, 'noindex' => true])
</head>
<body class="min-h-screen bg-white font-sans">
    <div class="grid min-h-screen lg:grid-cols-[1.05fr_1fr]">
        <div class="relative hidden overflow-hidden bg-navy-950 lg:block">
            <img src="{{ asset('images/hero/hero-dark-1536.webp') }}" alt="" class="absolute inset-0 h-full w-full object-cover opacity-70" width="1536" height="1024">
            <div class="absolute inset-0 bg-gradient-to-t from-navy-950 via-navy-950/40 to-navy-950/10"></div>
            <div class="relative flex h-full flex-col justify-between p-10">
                <a href="{{ route('home') }}"><x-logo dark /></a>
                <div class="max-w-md">
                    <p class="font-display text-4xl font-semibold leading-tight text-white">Latihan hari ini, <span class="italic text-brand-300">lulus</span> di hari H.</p>
                    <p class="mt-4 text-navy-200">Simulasi CAT SKD &amp; SKB khusus tenaga kesehatan — lengkap dengan pembahasan, peringkat, dan grafik progres.</p>
                    <div class="mt-6 flex flex-wrap gap-2 text-xs font-semibold text-white">
                        @foreach(['TWK · TIU · TKP', '8 Profesi Nakes', 'Pembahasan Lengkap'] as $chip)
                            <span class="rounded-full border border-white/20 bg-white/10 px-3 py-1.5 backdrop-blur">{{ $chip }}</span>
                        @endforeach
                    </div>
                </div>
            </div>
        </div>
        <div class="flex flex-col">
            <div class="flex items-center justify-between px-5 py-5 sm:px-10">
                <a href="{{ route('home') }}" class="lg:hidden"><x-logo /></a>
                <a href="{{ route('home') }}" class="ml-auto inline-flex items-center gap-1.5 text-sm font-semibold text-slate-500 hover:text-navy-900"><x-icon name="arrow-left" class="h-4 w-4" /> Beranda</a>
            </div>
            <div class="flex flex-1 items-center justify-center px-5 pb-12 sm:px-10">
                <div class="w-full max-w-md">{{ $slot }}</div>
            </div>
        </div>
    </div>
    @include('partials.flash')
    @livewireScriptConfig
</body>
</html>
