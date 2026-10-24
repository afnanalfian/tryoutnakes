<div class="mx-auto max-w-5xl">
    <x-page-head title="Keranjang" :subtitle="$summary['mode'] ? ($summary['mode'] === 'package' ? 'Mode: Paket Tryout' : 'Mode: Tryout Satuan') : null">
        @if($summary['lines']->isNotEmpty())
            <button type="button" wire:click="clear" wire:confirm="Kosongkan semua isi keranjang?" class="btn-ghost text-rose-600 hover:bg-rose-50"><x-icon name="trash" class="h-4 w-4" /> Kosongkan</button>
        @endif
    </x-page-head>

    @if($summary['lines']->isEmpty())
        <x-empty icon="cart" title="Keranjangmu masih kosong" text="Pilih paket tryout yang hemat atau beli tryout satuan sesuai kebutuhanmu.">
            <div class="flex flex-wrap justify-center gap-2">
                <a href="{{ route('catalog.packages') }}" class="btn-primary">Lihat Paket</a>
                <a href="{{ route('catalog.single') }}" class="btn-outline">Beli Satuan</a>
            </div>
        </x-empty>
    @else
        <div class="grid gap-6 lg:grid-cols-[1fr_340px]">
            <div class="space-y-3">
                <div class="flex items-start gap-3 rounded-2xl border border-sky-200 bg-sky-50 px-4 py-3 text-sm text-sky-900">
                    <x-icon name="info" class="mt-0.5 h-5 w-5 shrink-0" />
                    <p>Satu checkout hanya boleh berisi <strong>paket saja</strong> atau <strong>tryout satuan saja</strong>.</p>
                </div>
                @foreach($summary['lines'] as $line)
                    @php($m = $line['model'])
                    <div class="card flex gap-4 p-3 sm:p-4" wire:key="line-{{ $line['cart_id'] }}">
                        <img src="{{ $m->imageUrl() }}" alt="" class="h-20 w-28 shrink-0 rounded-xl object-cover sm:h-24 sm:w-36" loading="lazy">
                        <div class="flex min-w-0 flex-1 flex-col">
                            <div class="flex items-start justify-between gap-3">
                                <div class="min-w-0">
                                    <p class="text-[11px] font-bold uppercase tracking-wider text-brand-600">{{ $line['type'] === 'package' ? 'Paket · '.$m->tryouts->count().' tryout' : $m->category->label() }}</p>
                                    <p class="mt-0.5 font-bold leading-snug text-navy-900">{{ $line['name'] }}</p>
                                </div>
                                <button type="button" wire:click="remove({{ $line['cart_id'] }})" class="rounded-lg p-1.5 text-slate-400 hover:bg-rose-50 hover:text-rose-600" aria-label="Hapus"><x-icon name="trash" class="h-4 w-4" /></button>
                            </div>
                            @if($line['owned_count'] > 0)
                                <p class="mt-1 text-xs font-medium text-amber-700">{{ $line['owned_count'] }} tryout di paket ini sudah kamu miliki.</p>
                            @endif
                            <div class="mt-auto flex items-end gap-2 pt-2">
                                <p class="font-extrabold text-navy-900">{{ rupiah($line['price']) }}</p>
                                @if($line['normal_price'] > $line['price'])<p class="text-xs text-slate-400 line-through">{{ rupiah($line['normal_price']) }}</p>@endif
                            </div>
                        </div>
                    </div>
                @endforeach
                <a href="{{ $summary['mode'] === 'package' ? route('catalog.packages') : route('catalog.single') }}" class="btn-ghost"><x-icon name="plus" class="h-4 w-4" /> Tambah {{ $summary['mode'] === 'package' ? 'paket' : 'tryout' }} lain</a>
            </div>

            <aside class="lg:sticky lg:top-24 lg:self-start">
                <div class="card card-pad">
                    <p class="font-bold text-navy-900">Ringkasan</p>
                    <dl class="mt-4 space-y-2 text-sm">
                        <div class="flex justify-between"><dt class="text-slate-500">Subtotal ({{ $summary['lines']->count() }} item)</dt><dd class="font-semibold">{{ rupiah($summary['subtotal']) }}</dd></div>
                        @if($summary['match'])
                            <div class="flex justify-between text-mint-700"><dt>Konversi ke paket</dt><dd class="font-semibold">−{{ rupiah($summary['savings']) }}</dd></div>
                        @endif
                        <div class="flex justify-between border-t border-dashed border-slate-200 pt-3 text-base"><dt class="font-bold text-navy-900">Total</dt><dd class="font-extrabold text-navy-900">{{ rupiah($summary['total']) }}</dd></div>
                    </dl>
                    @if($summary['match'])
                        <div class="mt-4 rounded-xl bg-mint-50 p-3 text-xs leading-relaxed text-mint-800">
                            <p class="flex items-center gap-1.5 font-bold"><x-icon name="sparkles" class="h-4 w-4" /> Pilihanmu sama dengan paket!</p>
                            <p class="mt-1">Saat checkout, keranjang otomatis diubah menjadi <strong>{{ $summary['match']->name }}</strong>.</p>
                        </div>
                    @endif
                    <a href="{{ route('checkout') }}" wire:navigate class="btn-brand btn-lg mt-5 w-full">Lanjut Checkout <x-icon name="arrow-right" class="h-4 w-4" /></a>
                </div>
            </aside>
        </div>
    @endif
</div>
