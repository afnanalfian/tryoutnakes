<div class="mx-auto max-w-5xl">
    <x-page-head title="Checkout" subtitle="Periksa kembali pesananmu sebelum membayar." />

    <div class="grid gap-6 lg:grid-cols-[1fr_360px]">
        <div class="space-y-4">
            @if($summary['match'])
                @php($p = $summary['match'])
                <div class="overflow-hidden rounded-3xl border border-mint-200 bg-gradient-to-br from-mint-50 to-white">
                    <div class="flex items-start gap-3 p-5">
                        <span class="inline-flex h-11 w-11 shrink-0 items-center justify-center rounded-2xl bg-mint-600 text-white"><x-icon name="sparkles" /></span>
                        <div>
                            <p class="font-bold text-navy-900">Keranjang otomatis diubah menjadi paket</p>
                            <p class="mt-1 text-sm text-slate-600">Tryout satuan yang kamu pilih sama persis dengan isi <strong>{{ $p->name }}</strong>. Kamu hemat <strong class="text-mint-700">{{ rupiah($summary['savings']) }}</strong>.</p>
                        </div>
                    </div>
                    <div class="border-t border-mint-100 bg-white/70 px-5 py-4">
                        <p class="font-bold text-navy-900">{{ $p->name }}</p>
                        <ul class="mt-2 space-y-1 text-sm text-slate-600">
                            @foreach($p->tryouts as $t)<li class="flex items-center gap-2"><x-icon name="check" class="h-4 w-4 text-mint-600" /> {{ $t->title }}</li>@endforeach
                        </ul>
                    </div>
                </div>
            @else
                <div class="card divide-y divide-slate-100">
                    @foreach($summary['lines'] as $line)
                        <div class="flex items-center justify-between gap-4 p-4">
                            <div class="min-w-0">
                                <p class="truncate font-semibold text-navy-900">{{ $line['name'] }}</p>
                                <p class="text-xs text-slate-500">{{ $line['type'] === 'package' ? 'Paket · '.$line['model']->tryouts->count().' tryout' : $line['model']->category->label() }}</p>
                            </div>
                            <p class="shrink-0 font-bold text-navy-900">{{ rupiah($line['price']) }}</p>
                        </div>
                    @endforeach
                </div>
            @endif

            <div class="card card-pad">
                <p class="font-bold text-navy-900">Cara pembayaran</p>
                <ol class="mt-3 space-y-2 text-sm text-slate-600">
                    <li class="flex gap-2"><span class="font-bold text-brand-600">1.</span> Buat pesanan, lalu bayar via QRIS / transfer sesuai nominal.</li>
                    <li class="flex gap-2"><span class="font-bold text-brand-600">2.</span> Unggah bukti pembayaran di halaman pesanan.</li>
                    <li class="flex gap-2"><span class="font-bold text-brand-600">3.</span> Admin memverifikasi paling lambat {{ setting('verification_sla') }} — tryout langsung aktif.</li>
                </ol>
            </div>
        </div>

        <aside class="lg:sticky lg:top-24 lg:self-start">
            <form wire:submit="place" class="card card-pad">
                <p class="font-bold text-navy-900">Total Pembayaran</p>
                <p class="mt-2 text-3xl font-extrabold tracking-tight text-navy-900">{{ rupiah($summary['total']) }}</p>
                @if($summary['savings'] > 0)<p class="text-sm text-slate-400 line-through">{{ rupiah($summary['subtotal']) }}</p>@endif

                <label class="mt-5 flex items-start gap-2.5 text-sm text-slate-600">
                    <input type="checkbox" wire:model="agree" class="checkbox mt-0.5">
                    <span>Saya menyetujui <a href="{{ route('pages.terms') }}" target="_blank" class="font-semibold text-brand-700 underline">Syarat &amp; Ketentuan</a> dan memahami bahwa soal merupakan latihan, bukan soal resmi.</span>
                </label>
                @error('agree')<p class="error">{{ $message }}</p>@enderror

                <button type="submit" class="btn-brand btn-lg mt-5 w-full" wire:loading.attr="disabled">
                    <span wire:loading.remove wire:target="place">Buat Pesanan</span>
                    <span wire:loading wire:target="place">Memproses…</span>
                </button>
                <a href="{{ route('cart') }}" wire:navigate class="btn-ghost mt-2 w-full">Kembali ke keranjang</a>
            </form>
        </aside>
    </div>
</div>
