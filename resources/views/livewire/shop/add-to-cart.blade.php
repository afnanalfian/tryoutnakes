<div class="{{ $block ? 'w-full' : 'inline-flex' }}">
    @if($owned)
        <a href="{{ route('my-tryouts.index') }}" class="btn-outline {{ $block ? 'w-full' : '' }}"><x-icon name="check-circle" class="h-4 w-4 text-mint-600" /> Sudah dimiliki</a>
    @elseif($inCart)
        <a href="{{ route('cart') }}" class="btn {{ $block ? 'w-full' : '' }} bg-mint-50 text-mint-700 hover:bg-mint-100"><x-icon name="check" class="h-4 w-4" /> Di keranjang · Lihat</a>
    @else
        <div class="flex {{ $block ? 'w-full flex-col sm:flex-row' : '' }} gap-2">
            <button type="button" wire:click="add(false, true)" wire:loading.attr="disabled" class="btn-brand {{ $block ? 'btn-lg flex-1' : '' }}">
                <span wire:loading.remove wire:target="add"><x-icon name="bolt" class="h-4 w-4" /></span>
                <svg wire:loading wire:target="add" class="h-4 w-4 animate-spin" viewBox="0 0 24 24" fill="none"><circle cx="12" cy="12" r="9" stroke="currentColor" stroke-width="3" opacity=".25"/><path d="M21 12a9 9 0 0 0-9-9" stroke="currentColor" stroke-width="3"/></svg>
                Beli Sekarang
            </button>
            <button type="button" wire:click="add" wire:loading.attr="disabled" class="btn-outline {{ $block ? 'btn-lg flex-1' : '' }}">
                <x-icon name="cart" class="h-4 w-4" /> + Keranjang
            </button>
        </div>
    @endif

    <x-modal :name="'cart-conflict-'.$this->getId()" title="Tidak bisa digabung" maxWidth="md">
        <div class="flex gap-3">
            <span class="inline-flex h-10 w-10 shrink-0 items-center justify-center rounded-xl bg-amber-50 text-amber-600"><x-icon name="warning" /></span>
            <p class="text-sm leading-relaxed text-slate-600">{{ $conflict }} Kosongkan keranjang dan tambahkan item ini?</p>
        </div>
        <div class="mt-6 flex flex-col-reverse gap-2 sm:flex-row sm:justify-end">
            <a href="{{ route('cart') }}" class="btn-outline">Lihat Keranjang</a>
            <button type="button" wire:click="add(true)" class="btn-primary">Kosongkan &amp; Tambahkan</button>
        </div>
    </x-modal>
</div>
