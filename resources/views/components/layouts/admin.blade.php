@props(['title' => null])
@php
    $groups = [
        'Utama' => [
            ['Dashboard', 'admin.dashboard', 'grid', ['admin.dashboard']],
            ['Pesanan', 'admin.orders', 'card', ['admin.orders*']],
            ['Pengguna', 'admin.users', 'users', ['admin.users*']],
        ],
        'Konten Ujian' => [
            ['Bank Soal', 'admin.questions', 'book', ['admin.questions*']],
            ['Tryout', 'admin.tryouts', 'clipboard', ['admin.tryouts*']],
            ['Paket', 'admin.packages', 'cube', ['admin.packages*']],
            ['Kategori & Harga', 'admin.categories', 'tag', ['admin.categories']],
        ],
        'Situs' => [
            ['Metode Pembayaran', 'admin.payments', 'banknotes', ['admin.payments']],
            ['Banner Landing', 'admin.banners', 'photo', ['admin.banners']],
            ['FAQ', 'admin.faqs', 'question', ['admin.faqs']],
            ['Broadcast', 'admin.broadcast', 'megaphone', ['admin.broadcast']],
            ['Pengaturan', 'admin.settings', 'cog', ['admin.settings']],
        ],
    ];
    $pending = \App\Models\Order::query()->where('status', \App\Models\Order::STATUS_WAITING)->count();
@endphp
<!DOCTYPE html>
<html lang="id">
<head>
    @include('partials.head', ['title' => ($title ?? 'Admin').' · Admin '.setting('site_name'), 'noindex' => true])
</head>
<body class="min-h-screen bg-paper font-sans" x-data="{ side: false }">
    <div x-show="side" x-cloak x-transition.opacity class="fixed inset-0 z-40 bg-navy-950/40 lg:hidden" @click="side = false"></div>

    <aside class="fixed inset-y-0 left-0 z-50 flex w-[260px] -translate-x-full flex-col bg-navy-950 text-navy-100 transition-transform duration-200 lg:translate-x-0"
           :class="side ? 'translate-x-0' : '-translate-x-full lg:translate-x-0'">
        <div class="flex h-16 items-center justify-between px-5 lg:h-[72px]">
            <a href="{{ route('admin.dashboard') }}" wire:navigate><x-logo dark /></a>
            <button class="lg:hidden" @click="side = false" aria-label="Tutup"><x-icon name="x" /></button>
        </div>
        <nav class="flex-1 overflow-y-auto px-3 pb-6 scrollbar-thin">
            @foreach($groups as $group => $items)
                <p class="px-3 pb-2 pt-5 text-[10.5px] font-bold uppercase tracking-[.18em] text-navy-400">{{ $group }}</p>
                @foreach($items as [$label, $route, $icon, $match])
                    @php($on = request()->routeIs(...$match))
                    <a href="{{ route($route) }}" wire:navigate
                       class="flex items-center gap-3 rounded-xl px-3 py-2.5 text-sm font-medium transition {{ $on ? 'bg-white text-navy-950 shadow' : 'text-navy-200 hover:bg-white/10 hover:text-white' }}">
                        <x-icon :name="$icon" /> {{ $label }}
                        @if($route === 'admin.orders' && $pending)
                            <span class="ml-auto rounded-full bg-amber-400 px-2 py-0.5 text-[11px] font-bold text-amber-950">{{ $pending }}</span>
                        @endif
                    </a>
                @endforeach
            @endforeach
        </nav>
        <div class="border-t border-white/10 p-3">
            <a href="{{ route('home') }}" target="_blank" class="flex items-center gap-3 rounded-xl px-3 py-2.5 text-sm text-navy-200 hover:bg-white/10 hover:text-white"><x-icon name="eye" /> Lihat Situs</a>
        </div>
    </aside>

    <div class="lg:pl-[260px]">
        <header class="sticky top-0 z-30 border-b border-slate-200/80 bg-white/90 backdrop-blur-md">
            <div class="flex h-16 items-center justify-between gap-3 px-4 sm:px-6 lg:h-[72px] lg:px-8">
                <div class="flex min-w-0 items-center gap-3">
                    <button class="inline-flex h-10 w-10 items-center justify-center rounded-xl hover:bg-navy-50 lg:hidden" @click="side = true" aria-label="Menu"><x-icon name="menu" /></button>
                    <p class="truncate text-lg font-bold text-navy-900">{{ $title ?? 'Admin' }}</p>
                </div>
                <div class="flex items-center gap-1.5">
                    <livewire:user.notification-bell />
                    @include('partials.user-menu')
                </div>
            </div>
        </header>
        <main class="px-4 pb-16 pt-6 sm:px-6 lg:px-8">
            {{ $slot }}
        </main>
    </div>

    @include('partials.flash')
    @livewireScriptConfig
</body>
</html>
