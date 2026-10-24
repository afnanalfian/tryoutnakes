<x-layouts.public>
    @php($savings = $package->savings())
    <section class="mx-auto max-w-7xl px-4 py-8 sm:px-6 lg:px-8 lg:py-12">
        <x-breadcrumbs :items="[['Paket Tryout', route('catalog.packages')], [$package->name, route('catalog.package', $package)]]" />
        <div class="mt-6 grid gap-8 lg:grid-cols-[1.2fr_.8fr]">
            <div>
                <div class="overflow-hidden rounded-3xl border border-slate-200 bg-navy-50">
                    <img src="{{ $package->imageUrl() }}" alt="{{ $package->name }}" class="aspect-[16/9] w-full object-cover" width="1200" height="675">
                </div>
                <div class="mt-6 flex flex-wrap gap-2">
                    @foreach($package->categories() as $c)<x-category-badge :category="$c" class="px-3 py-1 text-xs" />@endforeach
                    @if($package->badge)<span class="badge bg-amber-100 px-3 py-1 text-xs text-amber-800">{{ $package->badge }}</span>@endif
                </div>
                <h1 class="mt-4 font-display text-3xl font-semibold tracking-tight text-navy-900 sm:text-4xl">{{ $package->name }}</h1>
                @if($package->tagline)<p class="mt-2 text-lg text-slate-600">{{ $package->tagline }}</p>@endif
                @if($package->description)
                    <div class="rich mt-6 text-slate-700">{!! rich($package->description) !!}</div>
                @endif

                <h2 class="mt-10 text-xl font-bold text-navy-900">Isi paket ({{ $package->tryouts->count() }} tryout)</h2>
                <div class="mt-4 space-y-3">
                    @foreach($package->tryouts as $i => $t)
                        <div class="card flex items-center gap-4 p-4">
                            <span class="inline-flex h-11 w-11 shrink-0 items-center justify-center rounded-xl" style="background: {{ $t->category->color }}17; color: {{ $t->category->color }}"><x-icon :name="$t->category->icon" /></span>
                            <div class="min-w-0 flex-1">
                                <p class="font-semibold text-navy-900">{{ $t->title }}</p>
                                <p class="text-xs text-slate-500">{{ $t->category->label() }} · {{ $t->questions_count }} soal · {{ $t->duration_minutes }} menit</p>
                            </div>
                            @if($owned->contains($t->id))<span class="badge bg-mint-50 text-mint-700">Dimiliki</span>@endif
                        </div>
                    @endforeach
                </div>
            </div>

            <aside class="lg:sticky lg:top-24 lg:self-start">
                <div class="card overflow-hidden">
                    <div class="bg-gradient-to-br from-navy-900 to-navy-700 p-6 text-white">
                        <p class="text-sm text-navy-200">Harga paket</p>
                        <div class="mt-1 flex items-end gap-3">
                            <p class="text-4xl font-extrabold tracking-tight">{{ rupiah($package->price) }}</p>
                            @if($savings > 0)<p class="pb-1 text-sm text-navy-300 line-through">{{ rupiah($package->normalPrice()) }}</p>@endif
                        </div>
                        @if($savings > 0)<p class="mt-2 inline-flex rounded-full bg-mint-400/20 px-3 py-1 text-xs font-semibold text-mint-200">Hemat {{ rupiah($savings) }} dibanding beli satuan</p>@endif
                    </div>
                    <div class="p-6">
                        <ul class="space-y-2.5 text-sm text-slate-600">
                            @foreach([$package->tryouts->count().' tryout sistem CAT', 'Pembahasan lengkap setiap soal', 'Peringkat & analisis per subtes', 'Grafik progres kemampuan', 'Akses selamanya setelah verifikasi'] as $f)
                                <li class="flex items-center gap-2.5"><x-icon name="check-circle" class="h-5 w-5 text-mint-600" /> {{ $f }}</li>
                            @endforeach
                        </ul>
                        <div class="mt-6"><livewire:shop.add-to-cart type="package" :item-id="$package->id" :block="true" /></div>
                        <p class="mt-4 text-center text-xs text-slate-500">Pembayaran QRIS / transfer · verifikasi maks. {{ setting('verification_sla') }}</p>
                    </div>
                </div>
            </aside>
        </div>
    </section>

    @if($related->isNotEmpty())
        <section class="mx-auto max-w-7xl px-4 pb-16 sm:px-6 lg:px-8">
            <h2 class="text-xl font-bold text-navy-900">Paket lainnya</h2>
            <div class="mt-5 grid gap-6 sm:grid-cols-2 lg:grid-cols-3">
                @foreach($related as $p)<x-package-card :package="$p" />@endforeach
            </div>
        </section>
    @endif
</x-layouts.public>
