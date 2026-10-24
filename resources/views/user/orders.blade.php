<x-layouts.app title="Pesanan Saya">
    <x-page-head title="Pesanan Saya" subtitle="Riwayat pembelian paket dan tryout." />
    @if($orders->isEmpty())
        <x-empty icon="card" title="Belum ada pesanan" text="Pesanan yang kamu buat akan muncul di sini.">
            <a href="{{ route('catalog.packages') }}" class="btn-primary">Mulai Belanja</a>
        </x-empty>
    @else
        <div class="space-y-3">
            @foreach($orders as $o)
                <a href="{{ route('orders.show', $o) }}" wire:navigate class="card flex flex-col gap-3 p-4 hover:border-navy-200 sm:flex-row sm:items-center sm:justify-between">
                    <div class="min-w-0">
                        <div class="flex flex-wrap items-center gap-2">
                            <p class="font-mono text-sm font-bold text-navy-900">{{ $o->code }}</p>
                            <span class="badge bg-{{ $o->statusColor() }}-100 text-{{ $o->statusColor() }}-800">{{ $o->statusLabel() }}</span>
                        </div>
                        <p class="mt-1 truncate text-sm text-slate-600">{{ $o->items->pluck('name')->implode(', ') }}</p>
                        <p class="text-xs text-slate-400">{{ $o->created_at->translatedFormat('d M Y, H:i') }}</p>
                    </div>
                    <div class="flex items-center justify-between gap-4 sm:justify-end">
                        <p class="text-lg font-extrabold text-navy-900">{{ rupiah($o->total) }}</p>
                        <x-icon name="chevron-right" class="h-4 w-4 text-slate-400" />
                    </div>
                </a>
            @endforeach
        </div>
        <div class="mt-6">{{ $orders->links() }}</div>
    @endif
</x-layouts.app>
