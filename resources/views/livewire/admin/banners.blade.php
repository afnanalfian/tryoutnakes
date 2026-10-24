<div>
    <x-page-head title="Banner Landing Page" subtitle="Banner aktif tampil sebagai slider di bawah hero halaman utama. Ukuran ideal 1600×533 (desktop) & 900×506 (mobile).">
        <button wire:click="create" class="btn-primary"><x-icon name="plus" class="h-4 w-4" /> Banner Baru</button>
    </x-page-head>
    <div class="grid gap-4 md:grid-cols-2">
        @forelse($banners as $b)
            <div class="card overflow-hidden" wire:key="b-{{ $b->id }}">
                <img src="{{ media_url($b->image) }}" alt="" class="aspect-[3/1] w-full object-cover">
                <div class="flex items-start justify-between gap-3 p-4">
                    <div class="min-w-0">
                        <p class="font-bold text-navy-900">{{ $b->title }}</p>
                        <p class="text-xs text-slate-500">{{ $b->starts_at?->format('d/m/Y') ?? 'Mulai sekarang' }} → {{ $b->ends_at?->format('d/m/Y') ?? 'tanpa batas' }} · urutan {{ $b->sort_order }}</p>
                    </div>
                    <span class="badge {{ $b->is_active ? 'bg-mint-50 text-mint-700' : 'bg-slate-100 text-slate-500' }}">{{ $b->is_active ? 'Aktif' : 'Nonaktif' }}</span>
                </div>
                <div class="flex gap-2 border-t border-slate-100 px-4 py-3">
                    <button wire:click="toggle({{ $b->id }})" class="btn-ghost btn-sm">{{ $b->is_active ? 'Nonaktifkan' : 'Aktifkan' }}</button>
                    <button wire:click="edit({{ $b->id }})" class="btn-outline btn-sm">Edit</button>
                    <button wire:click="delete({{ $b->id }})" wire:confirm="Hapus banner?" class="btn-soft-danger btn-sm ml-auto"><x-icon name="trash" class="h-3.5 w-3.5" /></button>
                </div>
            </div>
        @empty
            <x-empty class="md:col-span-2" icon="photo" title="Belum ada banner" text="Tambahkan banner promo untuk ditampilkan di landing page." />
        @endforelse
    </div>

    <x-modal name="banner-form" :title="$editingId ? 'Edit Banner' : 'Banner Baru'" maxWidth="2xl">
        <form wire:submit="save" class="grid gap-4 sm:grid-cols-2">
            <div class="sm:col-span-2"><label class="label">Judul</label><input wire:model="form.title" class="input">@error('form.title')<p class="error">{{ $message }}</p>@enderror</div>
            <div class="sm:col-span-2"><label class="label">Subjudul (opsional, tampil di atas gambar)</label><input wire:model="form.subtitle" class="input"></div>
            <div><label class="label">Tautan</label><input wire:model="form.link_url" class="input" placeholder="https://...">@error('form.link_url')<p class="error">{{ $message }}</p>@enderror</div>
            <div><label class="label">Teks tombol</label><input wire:model="form.button_text" class="input" placeholder="Lihat Promo"></div>
            <div><label class="label">Mulai tayang</label><input type="datetime-local" wire:model="form.starts_at" class="input"></div>
            <div><label class="label">Selesai tayang</label><input type="datetime-local" wire:model="form.ends_at" class="input">@error('form.ends_at')<p class="error">{{ $message }}</p>@enderror</div>
            <div><label class="label">Gambar desktop</label><input type="file" wire:model="image" accept="image/*" class="input p-2">@error('image')<p class="error">{{ $message }}</p>@enderror</div>
            <div><label class="label">Gambar mobile (opsional)</label><input type="file" wire:model="imageMobile" accept="image/*" class="input p-2"></div>
            <div><label class="label">Urutan</label><input type="number" wire:model="form.sort_order" class="input"></div>
            <label class="mt-7 flex items-center gap-2 text-sm"><input type="checkbox" wire:model="form.is_active" class="checkbox"> Aktif</label>
            <div class="flex justify-end gap-2 sm:col-span-2"><button type="button" @click="$dispatch('close-modal', 'banner-form')" class="btn-outline">Batal</button><button class="btn-primary" wire:loading.attr="disabled">Simpan</button></div>
        </form>
    </x-modal>
</div>
