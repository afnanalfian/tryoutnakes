@php
    $nav = [
        ['Beranda', route('home'), request()->routeIs('home')],
        ['Paket Tryout', route('catalog.packages'), request()->routeIs('catalog.packages', 'catalog.package')],
        ['Beli Satuan', route('catalog.single'), request()->routeIs('catalog.single')],
        ['FAQ', route('pages.faq'), request()->routeIs('pages.faq')],
        ['Tentang', route('pages.about'), request()->routeIs('pages.about')],
    ];
@endphp
<header x-data="{ open: false, scrolled: false }" x-init="scrolled = window.scrollY > 8" @scroll.window="scrolled = window.scrollY > 8"
        class="sticky top-0 z-40 border-b transition-colors duration-200"
        :class="scrolled ? 'border-slate-200/80 bg-white/90 backdrop-blur-md' : 'border-transparent bg-white'">
    <div class="mx-auto flex h-16 max-w-7xl items-center justify-between gap-4 px-4 sm:h-[72px] sm:px-6 lg:px-8">
        <a href="{{ route('home') }}" class="shrink-0" aria-label="{{ setting('site_name') }} — Beranda"><x-logo /></a>

        <nav class="hidden items-center gap-1 lg:flex" aria-label="Navigasi utama">
            @foreach($nav as [$label, $url, $active])
                <a href="{{ $url }}" @class([
                    'relative rounded-lg px-3.5 py-2 text-sm font-semibold transition',
                    'text-navy-900 after:absolute after:inset-x-3.5 after:-bottom-[3px] after:h-0.5 after:rounded-full after:bg-brand-600' => $active,
                    'text-slate-600 hover:text-navy-900' => ! $active,
                ])>{{ $label }}</a>
            @endforeach
        </nav>

        <div class="flex items-center gap-2">
            @auth
                <a href="{{ route('cart') }}" class="relative inline-flex h-10 w-10 items-center justify-center rounded-xl text-navy-900 hover:bg-navy-50" aria-label="Keranjang">
                    <x-icon name="cart" class="h-[22px] w-[22px]" />
                    <livewire:shop.cart-counter :initial="$cartCount ?? 0" />
                </a>
                <a href="{{ auth()->user()->isAdmin() ? route('admin.dashboard') : route('dashboard') }}" class="btn-primary hidden rounded-full px-5 sm:inline-flex">
                    Dashboard <x-icon name="arrow-right" class="h-4 w-4" />
                </a>
            @else
                <a href="{{ route('login') }}" class="btn-ghost hidden sm:inline-flex">Masuk</a>
                <a href="{{ route('register') }}" class="btn-primary hidden rounded-full px-5 sm:inline-flex">Daftar Gratis <x-icon name="arrow-right" class="h-4 w-4" /></a>
            @endauth
            <button type="button" class="inline-flex h-10 w-10 items-center justify-center rounded-xl text-navy-900 hover:bg-navy-50 lg:hidden" @click="open = !open" :aria-expanded="open" aria-label="Menu">
                <x-icon name="menu" x-show="!open" class="h-6 w-6" />
                <x-icon name="x" x-show="open" x-cloak class="h-6 w-6" />
            </button>
        </div>
    </div>

    {{-- Menu mobile --}}
    <div x-show="open" x-cloak x-transition.origin.top @click.outside="open = false" class="border-t border-slate-100 bg-white lg:hidden">
        <nav class="mx-auto max-w-7xl space-y-1 px-4 py-4">
            @foreach($nav as [$label, $url, $active])
                <a href="{{ $url }}" @class(['block rounded-xl px-4 py-3 text-[15px] font-semibold', 'bg-navy-50 text-navy-900' => $active, 'text-slate-700 hover:bg-slate-50' => ! $active])>{{ $label }}</a>
            @endforeach
            <div class="grid grid-cols-2 gap-2 pt-3">
                @auth
                    <a href="{{ auth()->user()->isAdmin() ? route('admin.dashboard') : route('dashboard') }}" class="btn-primary col-span-2">Buka Dashboard</a>
                @else
                    <a href="{{ route('login') }}" class="btn-outline">Masuk</a>
                    <a href="{{ route('register') }}" class="btn-primary">Daftar</a>
                @endauth
            </div>
        </nav>
    </div>
</header>
