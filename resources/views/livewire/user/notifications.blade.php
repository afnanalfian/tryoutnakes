<div class="mx-auto max-w-3xl">
    <x-page-head title="Notifikasi" subtitle="Semua kabar penting tentang pesanan dan tryout-mu. Salinan juga dikirim ke email.">
        <label class="flex items-center gap-2 text-sm text-slate-600"><input type="checkbox" wire:model.live="onlyUnread" class="checkbox"> Belum dibaca</label>
        <button wire:click="markAllRead" class="btn-outline btn-sm">Tandai semua dibaca</button>
        <button wire:click="clearRead" wire:confirm="Hapus semua notifikasi yang sudah dibaca?" class="btn-ghost btn-sm text-rose-600">Hapus yang dibaca</button>
    </x-page-head>
    <div class="card divide-y divide-slate-100 overflow-hidden">
        @forelse($items as $n)
            <div class="flex gap-3 p-4 {{ $n->read_at ? '' : 'bg-brand-50/40' }}" wire:key="n-{{ $n->id }}">
                <span class="mt-1.5 h-2 w-2 shrink-0 rounded-full {{ $n->read_at ? 'bg-slate-200' : 'bg-brand-600' }}"></span>
                <div class="min-w-0 flex-1">
                    <p class="font-semibold text-navy-900">{{ $n->data['title'] ?? 'Notifikasi' }}</p>
                    <p class="mt-0.5 text-sm text-slate-600">{{ $n->data['body'] ?? '' }}</p>
                    <div class="mt-2 flex items-center gap-3 text-xs">
                        <span class="text-slate-400">{{ $n->created_at->translatedFormat('d M Y, H:i') }}</span>
                        @if(!empty($n->data['url']))<a href="{{ route('notifications.open', $n->id) }}" class="font-semibold text-brand-700 hover:underline">{{ $n->data['action'] ?? 'Buka' }} →</a>@endif
                    </div>
                </div>
                <button wire:click="delete('{{ $n->id }}')" class="self-start rounded-lg p-1.5 text-slate-300 hover:bg-rose-50 hover:text-rose-600" aria-label="Hapus"><x-icon name="x" class="h-4 w-4" /></button>
            </div>
        @empty
            <x-empty class="m-4 border-0" icon="bell" title="Tidak ada notifikasi" />
        @endforelse
    </div>
    <div class="mt-4">{{ $items->links() }}</div>
</div>
