<div>
    <x-page-head title="Paket Tryout" subtitle="Buat paket bebas: beri nama, pilih kombinasi tryout dari kategori mana pun, dan tentukan harganya.">
        <a href="{{ route('admin.packages.create') }}" wire:navigate class="btn-primary"><x-icon name="plus" class="h-4 w-4" /> Paket Baru</a>
    </x-page-head>
    <div class="grid gap-4 lg:grid-cols-2">
        @forelse($packages as $p)
            <div class="card flex gap-4 p-4" wire:key="p-{{ $p->id }}">
                <img src="{{ $p->imageUrl() }}" alt="" class="hidden h-28 w-44 shrink-0 rounded-xl object-cover sm:block">
                <div class="min-w-0 flex-1">
                    <div class="flex flex-wrap items-center gap-2">
                        <p class="font-bold text-navy-900">{{ $p->name }}</p>
                        @if($p->badge)<span class="badge bg-amber-100 text-amber-800">{{ $p->badge }}</span>@endif
                        @unless($p->is_active)<span class="badge bg-slate-100 text-slate-500">Nonaktif</span>@endunless
                    </div>
                    <p class="mt-1 text-xs text-slate-500">{{ $p->tryouts->count() }} tryout · {{ $p->categories()->map->label()->implode(', ') }}</p>
                    <p class="mt-2 text-lg font-extrabold text-navy-900">{{ rupiah($p->price) }} @if($p->savings())<span class="text-xs font-medium text-slate-400 line-through">{{ rupiah($p->normalPrice()) }}</span>@endif</p>
                    <div class="mt-3 flex flex-wrap gap-2">
                        <a href="{{ route('admin.packages.edit', $p) }}" wire:navigate class="btn-outline btn-sm">Edit</a>
                        <button wire:click="toggle({{ $p->id }}, 'is_featured')" class="btn-ghost btn-sm">{{ $p->is_featured ? '★ Unggulan' : '☆ Jadikan unggulan' }}</button>
                        <button wire:click="toggle({{ $p->id }}, 'is_active')" class="btn-ghost btn-sm">{{ $p->is_active ? 'Nonaktifkan' : 'Aktifkan' }}</button>
                        <a href="{{ route('catalog.package', $p) }}" target="_blank" class="btn-ghost btn-sm">Lihat</a>
                        <button wire:click="delete({{ $p->id }})" wire:confirm="Hapus paket {{ $p->name }}? Akses pembeli lama tidak terpengaruh." class="btn-soft-danger btn-sm"><x-icon name="trash" class="h-3.5 w-3.5" /></button>
                    </div>
                </div>
            </div>
        @empty
            <x-empty class="lg:col-span-2" icon="cube" title="Belum ada paket" text="Buat paket pertama, misalnya Paket SKD Only (3 tryout)." />
        @endforelse
    </div>
</div>
