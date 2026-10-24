@php($user = auth()->user())
<div class="relative" x-data="{ open: false }" @click.outside="open = false" @keydown.escape.window="open = false">
    <button type="button" @click="open = !open" class="flex items-center gap-2 rounded-xl p-1 pr-2 hover:bg-navy-50" :aria-expanded="open" aria-label="Menu akun">
        <span class="inline-flex h-9 w-9 items-center justify-center rounded-xl bg-gradient-to-br from-brand-500 to-navy-800 text-xs font-bold text-white">{{ $user->initials() }}</span>
        <span class="hidden text-left sm:block">
            <span class="block max-w-[140px] truncate text-sm font-semibold text-navy-900">{{ $user->firstName() }}</span>
            <span class="block text-[11px] text-slate-500">{{ $user->isAdmin() ? 'Administrator' : 'Peserta' }}</span>
        </span>
        <x-icon name="chevron-down" class="hidden h-4 w-4 text-slate-400 sm:block" />
    </button>
    <div x-show="open" x-cloak x-transition.origin.top.right class="absolute right-0 mt-2 w-60 overflow-hidden rounded-2xl border border-slate-200 bg-white p-1.5 shadow-lift">
        <div class="px-3 py-2.5">
            <p class="truncate text-sm font-semibold text-navy-900">{{ $user->name }}</p>
            <p class="truncate text-xs text-slate-500">{{ $user->email }}</p>
        </div>
        <div class="my-1 h-px bg-slate-100"></div>
        @if($user->isAdmin())
            <a href="{{ route('admin.dashboard') }}" class="flex items-center gap-2.5 rounded-xl px-3 py-2 text-sm text-slate-700 hover:bg-slate-50"><x-icon name="grid" class="h-4 w-4" /> Panel Admin</a>
        @endif
        <a href="{{ route('dashboard') }}" class="flex items-center gap-2.5 rounded-xl px-3 py-2 text-sm text-slate-700 hover:bg-slate-50"><x-icon name="home" class="h-4 w-4" /> Dashboard Peserta</a>
        <a href="{{ route('profile') }}" class="flex items-center gap-2.5 rounded-xl px-3 py-2 text-sm text-slate-700 hover:bg-slate-50"><x-icon name="user" class="h-4 w-4" /> Profil &amp; Keamanan</a>
        <a href="{{ route('orders.index') }}" class="flex items-center gap-2.5 rounded-xl px-3 py-2 text-sm text-slate-700 hover:bg-slate-50"><x-icon name="card" class="h-4 w-4" /> Pesanan Saya</a>
        <a href="{{ route('home') }}" class="flex items-center gap-2.5 rounded-xl px-3 py-2 text-sm text-slate-700 hover:bg-slate-50"><x-icon name="sparkles" class="h-4 w-4" /> Halaman Utama</a>
        <div class="my-1 h-px bg-slate-100"></div>
        <form method="POST" action="{{ route('logout') }}">
            @csrf
            <button class="flex w-full items-center gap-2.5 rounded-xl px-3 py-2 text-sm font-medium text-rose-600 hover:bg-rose-50"><x-icon name="logout" class="h-4 w-4" /> Keluar</button>
        </form>
    </div>
</div>
