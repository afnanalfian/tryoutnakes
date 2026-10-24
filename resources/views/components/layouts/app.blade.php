@props(['title' => null, 'header' => null])
@php
    $user = auth()->user();
    $menu = [
        ['Beranda', 'dashboard', 'home', ['dashboard']],
        ['Tryout Saya', 'my-tryouts.index', 'clipboard', ['my-tryouts.*']],
        ['Riwayat & Hasil', 'results.index', 'document', ['results.*']],
        ['Progres', 'progress', 'trending', ['progress']],
        ['Pesanan', 'orders.index', 'card', ['orders.*', 'checkout']],
    ];
    $shop = [
        ['Paket Tryout', 'catalog.packages', 'cube'],
        ['Beli Satuan', 'catalog.single', 'tag'],
    ];
@endphp
<!DOCTYPE html>
<html lang="id">
<head>
    @include('partials.head', ['title' => $title ? $title.' | '.setting('site_name') : null, 'noindex' => true])
</head>
<body class="min-h-screen bg-paper font-sans" x-data="{ side: false }">
    <div class="flex min-h-screen">
        {{-- Sidebar --}}
        <aside class="fixed inset-y-0 left-0 z-50 hidden w-[264px] flex-col border-r border-slate-200/80 bg-white lg:flex">
            <div class="flex h-[72px] items-center px-6"><a href="{{ route('home') }}"><x-logo /></a></div>
            <nav class="flex-1 space-y-1 overflow-y-auto px-4 pb-4 scrollbar-thin">
                <p class="px-3 pb-2 pt-3 text-[11px] font-bold uppercase tracking-[.16em] text-slate-400">Belajar</p>
                @foreach($menu as [$label, $route, $icon, $match])
                    <a href="{{ route($route) }}" wire:navigate @class(['nav-link', 'nav-link-active' => request()->routeIs(...$match)])>
                        <x-icon :name="$icon" /> {{ $label }}
                    </a>
                @endforeach
                <p class="px-3 pb-2 pt-5 text-[11px] font-bold uppercase tracking-[.16em] text-slate-400">Belanja</p>
                @foreach($shop as [$label, $route, $icon])
                    <a href="{{ route($route) }}" @class(['nav-link', 'nav-link-active' => request()->routeIs($route)])><x-icon :name="$icon" /> {{ $label }}</a>
                @endforeach
                <a href="{{ route('cart') }}" wire:navigate @class(['nav-link', 'nav-link-active' => request()->routeIs('cart')])>
                    <x-icon name="cart" /> Keranjang
                    @if(($cartCount ?? 0) > 0)<span class="ml-auto rounded-full bg-brand-600 px-2 py-0.5 text-[11px] font-bold text-white">{{ $cartCount }}</span>@endif
                </a>
            </nav>
            <div class="m-4 rounded-2xl bg-gradient-to-br from-navy-900 to-navy-700 p-4 text-white">
                <p class="text-sm font-semibold">Butuh bantuan?</p>
                <p class="mt-1 text-xs text-navy-200">Admin siap membantu via WhatsApp.</p>
                @if($wa = wa_link(setting('contact_whatsapp'), 'Halo Admin, saya '.$user->name.' butuh bantuan.'))
                    <a href="{{ $wa }}" target="_blank" rel="noopener" class="btn btn-sm mt-3 w-full bg-white text-navy-900 hover:bg-navy-50"><x-icon name="whatsapp" class="h-4 w-4" /> Chat Admin</a>
                @endif
            </div>
        </aside>

        <div class="flex min-w-0 flex-1 flex-col lg:pl-[264px]">
            {{-- Topbar --}}
            <header class="sticky top-0 z-40 border-b border-slate-200/80 bg-white/90 backdrop-blur-md">
                <div class="flex h-16 items-center justify-between gap-3 px-4 sm:px-6 lg:h-[72px] lg:px-8">
                    <div class="flex min-w-0 items-center gap-3">
                        <a href="{{ route('home') }}" class="lg:hidden"><x-logo compact /></a>
                        <div class="min-w-0">
                            <p class="truncate text-[15px] font-bold text-navy-900 sm:text-lg">{{ $header ?? $title ?? 'Dashboard' }}</p>
                        </div>
                    </div>
                    <div class="flex items-center gap-1.5">
                        <a href="{{ route('cart') }}" class="relative inline-flex h-10 w-10 items-center justify-center rounded-xl text-navy-900 hover:bg-navy-50 lg:hidden" aria-label="Keranjang">
                            <x-icon name="cart" />
                            <livewire:shop.cart-counter :initial="$cartCount ?? 0" />
                        </a>
                        <livewire:user.notification-bell />
                        @include('partials.user-menu')
                    </div>
                </div>
            </header>

            @if($activeAttempt ?? null)
                @include('partials.active-attempt-banner')
            @endif

            <main class="flex-1 px-4 pb-28 pt-5 sm:px-6 lg:px-8 lg:pb-10 lg:pt-8">
                {{ $slot }}
            </main>
        </div>
    </div>

    {{-- Navigasi bawah (mobile) --}}
    <nav class="pb-safe fixed inset-x-0 bottom-0 z-40 border-t border-slate-200 bg-white/95 backdrop-blur-md lg:hidden" aria-label="Navigasi">
        <div class="mx-auto grid max-w-lg grid-cols-5">
            @foreach([['Beranda', 'dashboard', 'home', ['dashboard']], ['Tryout', 'my-tryouts.index', 'clipboard', ['my-tryouts.*']], ['Hasil', 'results.index', 'document', ['results.*']], ['Progres', 'progress', 'trending', ['progress']], ['Belanja', 'catalog.packages', 'cube', ['catalog.*', 'cart', 'checkout', 'orders.*']]] as [$label, $route, $icon, $match])
                @php($on = request()->routeIs(...$match))
                <a href="{{ route($route) }}" @if(!str_starts_with($route, 'catalog')) wire:navigate @endif class="flex flex-col items-center gap-1 pb-1 pt-2 text-[10.5px] font-semibold {{ $on ? 'text-navy-900' : 'text-slate-400' }}">
                    <span class="inline-flex h-8 w-12 items-center justify-center rounded-full {{ $on ? 'bg-navy-100' : '' }}"><x-icon :name="$icon" class="h-[21px] w-[21px]" /></span>
                    {{ $label }}
                </a>
            @endforeach
        </div>
    </nav>

    @include('partials.flash')
    @livewireScriptConfig
</body>
</html>
