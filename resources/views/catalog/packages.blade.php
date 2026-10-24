<x-layouts.public>
    <section class="relative overflow-hidden border-b border-slate-100 bg-gradient-to-b from-navy-50/70 to-white">
        <div class="grid-bg pointer-events-none absolute inset-0 opacity-60"></div>
        <div class="relative mx-auto max-w-7xl px-4 py-10 sm:px-6 sm:py-14 lg:px-8">
            <x-breadcrumbs :items="[['Paket Tryout', route('catalog.packages')]]" />
            <h1 class="mt-4 font-display text-3xl font-semibold tracking-tight text-navy-900 sm:text-5xl">Paket Tryout <span class="italic text-brand-600">CPNS Nakes</span></h1>
            <p class="mt-3 max-w-2xl text-slate-600">Kombinasi tryout yang disusun untuk persiapan menyeluruh — lebih hemat dibanding membeli satuan. Ingin memilih sendiri? <a href="{{ route('catalog.single') }}" class="font-semibold text-brand-700 underline">Beli satuan</a>.</p>
            <div class="no-scrollbar -mx-4 mt-6 flex gap-2 overflow-x-auto px-4 sm:mx-0 sm:flex-wrap sm:px-0">
                <a href="{{ route('catalog.packages') }}" @class(['whitespace-nowrap rounded-full px-4 py-2 text-sm font-semibold', 'bg-navy-900 text-white' => ! $filter, 'bg-white text-slate-600 ring-1 ring-slate-200 hover:ring-navy-300' => $filter])>Semua</a>
                @foreach($categories as $c)
                    <a href="{{ route('catalog.packages', ['kategori' => $c->slug]) }}" @class(['whitespace-nowrap rounded-full px-4 py-2 text-sm font-semibold', 'bg-navy-900 text-white' => $filter === $c->slug, 'bg-white text-slate-600 ring-1 ring-slate-200 hover:ring-navy-300' => $filter !== $c->slug])>{{ $c->isSkd() ? 'SKD' : $c->name }}</a>
                @endforeach
            </div>
        </div>
    </section>
    <section class="mx-auto max-w-7xl px-4 py-10 sm:px-6 lg:px-8">
        @if($packages->isEmpty())
            <x-empty icon="cube" title="Belum ada paket untuk filter ini" text="Coba kategori lain atau beli tryout satuan.">
                <a href="{{ route('catalog.single') }}" class="btn-primary">Beli Satuan</a>
            </x-empty>
        @else
            <div class="grid gap-6 sm:grid-cols-2 lg:grid-cols-3">
                @foreach($packages as $p)
                    <x-package-card :package="$p" />
                @endforeach
            </div>
        @endif
    </section>
</x-layouts.public>
