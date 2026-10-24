<div>
    <section class="relative overflow-hidden border-b border-slate-100 bg-gradient-to-b from-navy-50/70 to-white">
        <div class="grid-bg pointer-events-none absolute inset-0 opacity-60"></div>
        <div class="relative mx-auto max-w-7xl px-4 py-10 sm:px-6 sm:py-14 lg:px-8">
            <x-breadcrumbs :items="[['Beli Satuan', route('catalog.single')]]" />
            <h1 class="mt-4 font-display text-3xl font-semibold tracking-tight text-navy-900 sm:text-5xl">Pilih tryout <span class="italic text-brand-600">sesukamu.</span></h1>
            <p class="mt-3 max-w-2xl text-slate-600">Beli tryout satuan sesuai kebutuhan. Harga per tryout ditentukan berdasarkan kategori. Jika pilihanmu sama persis dengan isi sebuah paket, checkout otomatis diubah ke paket yang lebih hemat.</p>

            {{-- Stepper --}}
            @php($step = !$type ? 1 : ($type === 'skb' && !$current ? 2 : 3))
            <ol class="mt-8 flex flex-wrap items-center gap-2 text-sm font-semibold">
                @foreach([1 => 'Jenis Tes', 2 => 'Kategori', 3 => 'Pilih Tryout'] as $n => $label)
                    @continue($n === 2 && $type === 'skd')
                    <li class="flex items-center gap-2">
                        <span @class(['inline-flex h-7 w-7 items-center justify-center rounded-full text-xs', 'bg-navy-900 text-white' => $step >= $n, 'bg-white text-slate-400 ring-1 ring-slate-200' => $step < $n])>{{ $step > $n ? '✓' : ($n === 3 && $type === 'skd' ? 2 : $n) }}</span>
                        <span class="{{ $step >= $n ? 'text-navy-900' : 'text-slate-400' }}">{{ $label }}</span>
                        @if(!$loop->last)<x-icon name="chevron-right" class="h-4 w-4 text-slate-300" />@endif
                    </li>
                @endforeach
            </ol>
        </div>
    </section>

    <section class="mx-auto max-w-7xl px-4 py-10 sm:px-6 lg:px-8" wire:loading.class="opacity-60">
        @if($step > 1)
            <button type="button" wire:click="back" class="btn-ghost -ml-3 mb-4"><x-icon name="arrow-left" class="h-4 w-4" /> Kembali</button>
        @endif

        {{-- Step 1: SKD / SKB --}}
        @if($step === 1)
            <div class="grid gap-5 md:grid-cols-2">
                @foreach([
                    ['skd', 'SKD CPNS', 'Seleksi Kompetensi Dasar: TWK, TIU, dan TKP dengan penilaian resmi & passing grade.', 'skd', 'from-navy-900 to-brand-700', $skd->sum('tryouts_count')],
                    ['skb', 'SKB Tenaga Kesehatan', 'Seleksi Kompetensi Bidang untuk 8 profesi nakes. Skala nilai mengikuti tiap tryout.', 'perawat', 'from-mint-700 to-mint-500', $skb->sum('tryouts_count')],
                ] as [$key, $label, $desc, $icon, $grad, $count])
                    <button type="button" wire:click="chooseType('{{ $key }}')" class="group relative overflow-hidden rounded-3xl bg-gradient-to-br {{ $grad }} p-7 text-left text-white shadow-lift transition hover:-translate-y-1 sm:p-8">
                        <div class="absolute -right-10 -top-10 h-44 w-44 rounded-full bg-white/10"></div>
                        <span class="inline-flex h-14 w-14 items-center justify-center rounded-2xl bg-white/15 backdrop-blur"><x-icon :name="$icon" class="h-7 w-7" /></span>
                        <p class="mt-6 font-display text-3xl font-semibold">{{ $label }}</p>
                        <p class="mt-2 max-w-sm text-sm text-white/80">{{ $desc }}</p>
                        <p class="mt-6 inline-flex items-center gap-2 text-sm font-semibold">{{ $count }} tryout tersedia <x-icon name="arrow-right" class="h-4 w-4 transition group-hover:translate-x-1" /></p>
                    </button>
                @endforeach
            </div>
        @endif

        {{-- Step 2: kategori SKB --}}
        @if($step === 2)
            <h2 class="text-xl font-bold text-navy-900">Pilih profesi kamu</h2>
            <div class="mt-5 grid grid-cols-2 gap-3 sm:grid-cols-3 lg:grid-cols-4">
                @foreach($skb as $c)
                    <button type="button" wire:click="chooseCategory('{{ $c->slug }}')" class="card group flex items-center gap-3 p-4 text-left transition hover:-translate-y-0.5 hover:border-navy-200 hover:shadow-lift">
                        <span class="inline-flex h-12 w-12 shrink-0 items-center justify-center rounded-2xl" style="background: {{ $c->color }}1a; color: {{ $c->color }}"><x-icon :name="$c->icon" class="h-6 w-6" /></span>
                        <span class="min-w-0">
                            <span class="block truncate font-bold text-navy-900">{{ $c->name }}</span>
                            <span class="text-xs text-slate-500">{{ $c->tryouts_count }} tryout · {{ rupiah($c->price) }}</span>
                        </span>
                    </button>
                @endforeach
            </div>
        @endif

        {{-- Step 3: daftar tryout --}}
        @if($step === 3 && $current)
            <div class="flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">
                <div class="flex items-center gap-3">
                    <span class="inline-flex h-12 w-12 items-center justify-center rounded-2xl" style="background: {{ $current->color }}1a; color: {{ $current->color }}"><x-icon :name="$current->icon" class="h-6 w-6" /></span>
                    <div>
                        <h2 class="text-xl font-bold text-navy-900">{{ $current->label() }}</h2>
                        <p class="text-sm text-slate-500">Harga satuan <strong class="text-navy-900">{{ rupiah($current->price) }}</strong> / tryout</p>
                    </div>
                </div>
                @if($type === 'skb')
                    <div class="no-scrollbar -mx-4 flex gap-2 overflow-x-auto px-4 sm:mx-0 sm:px-0">
                        @foreach($skb as $c)
                            <button wire:click="chooseCategory('{{ $c->slug }}')" @class(['whitespace-nowrap rounded-full px-3.5 py-1.5 text-xs font-semibold', 'bg-navy-900 text-white' => $c->id === $current->id, 'bg-white text-slate-600 ring-1 ring-slate-200 hover:ring-navy-300' => $c->id !== $current->id])>{{ $c->name }}</button>
                        @endforeach
                    </div>
                @endif
            </div>

            @if($tryouts->isEmpty())
                <x-empty class="mt-8" icon="clipboard" title="Belum ada tryout di kategori ini" text="Tryout baru sedang disiapkan. Cek paket tryout atau kategori lain dulu, ya." />
            @else
                <div class="mt-6 grid gap-5 sm:grid-cols-2 lg:grid-cols-3">
                    @foreach($tryouts as $t)
                        <x-tryout-card :tryout="$t" :href="route('catalog.category', $current)" wire:key="t-{{ $t->id }}">
                            <div class="flex items-center justify-between gap-3">
                                <p class="text-lg font-extrabold text-navy-900">{{ rupiah($t->price()) }}</p>
                                @if($owned->contains($t->id))
                                    <span class="badge bg-mint-50 px-3 py-1.5 text-mint-700"><x-icon name="check" class="h-3.5 w-3.5" /> Dimiliki</span>
                                @elseif($inCart->contains($t->id))
                                    <button type="button" wire:click="remove({{ $t->id }})" class="btn btn-sm bg-mint-50 text-mint-700 hover:bg-rose-50 hover:text-rose-600" title="Klik untuk batal"><x-icon name="check" class="h-3.5 w-3.5" /> Dipilih</button>
                                @else
                                    <button type="button" wire:click="add({{ $t->id }})" wire:loading.attr="disabled" class="btn-primary btn-sm"><x-icon name="plus" class="h-3.5 w-3.5" /> Keranjang</button>
                                @endif
                            </div>
                        </x-tryout-card>
                    @endforeach
                </div>
            @endif
        @endif
    </section>

    {{-- Bar ringkasan keranjang --}}
    @if($summary && $summary['mode'] === 'tryout' && $summary['lines']->isNotEmpty())
        <div class="pb-safe sticky bottom-0 z-30 border-t border-slate-200 bg-white/95 backdrop-blur-md">
            <div class="mx-auto flex max-w-7xl flex-col gap-3 px-4 py-3 sm:flex-row sm:items-center sm:justify-between sm:px-6 lg:px-8">
                <div class="min-w-0 text-sm">
                    <p class="font-semibold text-navy-900">{{ $summary['lines']->count() }} tryout dipilih · <span class="text-brand-700">{{ rupiah($summary['total']) }}</span>
                        @if($summary['match'])<span class="text-xs font-medium text-slate-400 line-through">{{ rupiah($summary['subtotal']) }}</span>@endif
                    </p>
                    @if($summary['match'])
                        <p class="mt-0.5 flex items-center gap-1.5 text-xs font-semibold text-mint-700"><x-icon name="sparkles" class="h-4 w-4" /> Cocok dengan paket “{{ $summary['match']->name }}” — otomatis jadi paket, hemat {{ rupiah($summary['savings']) }}!</p>
                    @endif
                </div>
                <a href="{{ route('cart') }}" class="btn-brand">Lanjut ke Keranjang <x-icon name="arrow-right" class="h-4 w-4" /></a>
            </div>
        </div>
    @endif

    <x-modal name="single-conflict" title="Keranjang berisi paket" maxWidth="md">
        <p class="text-sm leading-relaxed text-slate-600">{{ $conflict }} Kosongkan keranjang lalu tambahkan tryout ini?</p>
        <div class="mt-6 flex flex-col-reverse gap-2 sm:flex-row sm:justify-end">
            <a href="{{ route('cart') }}" class="btn-outline">Lihat Keranjang</a>
            @if($pendingId)
                <button type="button" wire:click="add({{ $pendingId }}, true)" class="btn-primary">Kosongkan &amp; Tambahkan</button>
            @endif
        </div>
    </x-modal>
</div>
