<x-layouts.public>
    <section class="relative overflow-hidden border-b border-slate-100" style="background: linear-gradient(180deg, {{ $category->color }}12, #fff)">
        <div class="grid-bg pointer-events-none absolute inset-0 opacity-50"></div>
        <div class="relative mx-auto grid max-w-7xl items-center gap-8 px-4 py-10 sm:px-6 sm:py-14 lg:grid-cols-[1.1fr_.9fr] lg:px-8">
            <div>
                <x-breadcrumbs :items="[['Kategori', route('catalog.packages')], [$category->label(), route('catalog.category', $category)]]" />
                <span class="mt-5 inline-flex h-14 w-14 items-center justify-center rounded-2xl bg-white shadow-soft" style="color: {{ $category->color }}"><x-icon :name="$category->icon" class="h-7 w-7" /></span>
                <h1 class="mt-4 font-display text-3xl font-semibold tracking-tight text-navy-900 sm:text-5xl">Tryout {{ $name }}</h1>
                @if($category->tagline)<p class="mt-3 text-lg font-medium text-navy-700">{{ $category->tagline }}</p>@endif
                <p class="mt-3 max-w-2xl leading-relaxed text-slate-600">{{ $category->description }}</p>
                <div class="mt-6 flex flex-wrap gap-3">
                    <a href="{{ route('catalog.single', ['jenis' => $category->type, 'kategori' => $category->slug]) }}" class="btn-primary rounded-full px-6">Beli Satuan · {{ rupiah($category->price) }}/tryout</a>
                    @if($packages->isNotEmpty())<a href="#paket" class="btn-outline rounded-full px-6">Lihat Paket</a>@endif
                </div>
            </div>
            <img src="{{ $category->imageUrl() }}" alt="Tryout {{ $name }}" class="hidden w-full rounded-3xl shadow-lift lg:block" width="1200" height="675">
        </div>
    </section>

    @if($packages->isNotEmpty())
        <section id="paket" class="mx-auto max-w-7xl scroll-mt-24 px-4 py-12 sm:px-6 lg:px-8">
            <h2 class="text-2xl font-bold text-navy-900">Paket yang memuat {{ $category->label() }}</h2>
            <div class="mt-6 grid gap-6 sm:grid-cols-2 lg:grid-cols-3">
                @foreach($packages as $p)<x-package-card :package="$p" />@endforeach
            </div>
        </section>
    @endif

    <section class="mx-auto max-w-7xl px-4 pb-16 sm:px-6 lg:px-8 {{ $packages->isEmpty() ? 'pt-12' : '' }}">
        <h2 class="text-2xl font-bold text-navy-900">Daftar Tryout {{ $category->label() }}</h2>
        @if($tryouts->isEmpty())
            <x-empty class="mt-6" icon="clipboard" title="Tryout sedang disiapkan" text="Pantau terus, tryout baru untuk kategori ini segera hadir." />
        @else
            <div class="mt-6 grid gap-5 sm:grid-cols-2 lg:grid-cols-3">
                @foreach($tryouts as $t)
                    <x-tryout-card :tryout="$t" :href="route('catalog.single', ['jenis' => $category->type, 'kategori' => $category->slug])" :owned="$owned->contains($t->id)">
                        @if($t->description)<p class="mb-3 line-clamp-2 text-sm text-slate-500">{{ $t->description }}</p>@endif
                        <div class="flex items-center justify-between gap-2">
                            <p class="text-lg font-extrabold text-navy-900">{{ $t->is_free ? 'Gratis' : rupiah($t->price()) }}</p>
                            @if($t->is_free)
                                <a href="{{ auth()->check() ? route('my-tryouts.show', $t) : route('register') }}" class="btn-mint btn-sm">Kerjakan</a>
                            @elseif($owned->contains($t->id))
                                <a href="{{ route('my-tryouts.show', $t) }}" class="btn-outline btn-sm">Kerjakan</a>
                            @else
                                <a href="{{ route('catalog.single', ['jenis' => $category->type, 'kategori' => $category->slug]) }}" class="btn-primary btn-sm">Pilih</a>
                            @endif
                        </div>
                    </x-tryout-card>
                @endforeach
            </div>
        @endif
    </section>
</x-layouts.public>
