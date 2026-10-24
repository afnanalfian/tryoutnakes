<x-layouts.app title="Tryout Saya">
    <x-page-head title="Tryout Saya" subtitle="Hanya kategori yang kamu miliki yang ditampilkan.">
        <a href="{{ route('catalog.packages') }}" class="btn-outline"><x-icon name="plus" class="h-4 w-4" /> Tambah Tryout</a>
    </x-page-head>

    @if($categories->isEmpty())
        <x-empty icon="clipboard" title="Kamu belum punya tryout" text="Pilih paket hemat atau beli tryout satuan. Setelah pembayaran diverifikasi, tryout langsung muncul di sini.">
            <div class="flex flex-wrap justify-center gap-2">
                <a href="{{ route('catalog.packages') }}" class="btn-primary">Lihat Paket</a>
                <a href="{{ route('catalog.single') }}" class="btn-outline">Beli Satuan</a>
            </div>
        </x-empty>
    @else
        <div class="no-scrollbar -mx-4 mb-6 flex gap-2 overflow-x-auto px-4 sm:mx-0 sm:flex-wrap sm:px-0">
            @foreach($categories as $c)
                @php($on = $current && $current->id === $c->id)
                <a href="{{ route('my-tryouts.index', ['kategori' => $c->slug]) }}" wire:navigate
                   class="flex shrink-0 items-center gap-2.5 rounded-2xl border px-4 py-2.5 text-sm font-semibold transition {{ $on ? 'border-navy-900 bg-navy-900 text-white shadow' : 'border-slate-200 bg-white text-slate-700 hover:border-navy-300' }}">
                    <span class="inline-flex h-7 w-7 items-center justify-center rounded-lg {{ $on ? 'bg-white/15' : '' }}" @unless($on) style="background: {{ $c->color }}17; color: {{ $c->color }}" @endunless><x-icon :name="$c->icon" class="h-4 w-4" /></span>
                    {{ $c->label() }}
                    <span class="rounded-full px-2 py-0.5 text-[11px] {{ $on ? 'bg-white/15' : 'bg-slate-100 text-slate-500' }}">{{ $counts[$c->id] ?? 0 }}</span>
                </a>
            @endforeach
        </div>

        <div class="grid gap-5 sm:grid-cols-2 xl:grid-cols-3">
            @foreach($tryouts as $t)
                @php($last = $latest->get($t->id))
                @php($run = $running->get($t->id))
                <x-tryout-card :tryout="$t" :href="route('my-tryouts.show', $t)" :attempt="$last">
                    @if($run)
                        <a href="{{ route('exam.show', $run) }}" class="btn w-full bg-amber-500 text-white hover:bg-amber-600"><x-icon name="play" class="h-4 w-4" /> Lanjutkan</a>
                    @elseif($last)
                        <div class="grid grid-cols-2 gap-2">
                            <a href="{{ route('results.show', $last) }}" class="btn-outline">Pembahasan</a>
                            <a href="{{ route('my-tryouts.show', $t) }}" class="btn-primary">Ulangi</a>
                        </div>
                    @else
                        <a href="{{ route('my-tryouts.show', $t) }}" class="btn-primary w-full">Mulai Tryout <x-icon name="arrow-right" class="h-4 w-4" /></a>
                    @endif
                </x-tryout-card>
            @endforeach
        </div>
    @endif
</x-layouts.app>
