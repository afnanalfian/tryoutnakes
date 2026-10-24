<div>
    <x-page-head title="Kategori & Harga Satuan" subtitle="Harga satuan berlaku untuk semua tryout dalam kategori yang sama. Gambar kosong = memakai template bawaan." />
    <div class="grid gap-4 md:grid-cols-2 xl:grid-cols-3">
        @foreach($categories as $c)
            <div class="card overflow-hidden" wire:key="c-{{ $c->id }}">
                <div class="relative">
                    <img src="{{ $c->imageUrl() }}" alt="" class="aspect-[16/9] w-full object-cover">
                    @unless($c->is_active)<span class="badge absolute left-3 top-3 bg-slate-900/80 text-white">Nonaktif</span>@endunless
                </div>
                <div class="p-4">
                    <div class="flex items-center gap-2"><x-category-badge :category="$c" /></div>
                    <p class="mt-2 text-2xl font-extrabold text-navy-900">{{ rupiah($c->price) }} <span class="text-sm font-medium text-slate-400">/ tryout</span></p>
                    <p class="mt-1 text-xs text-slate-500">{{ $c->tryouts_count }} tryout · {{ $c->questions_count }} soal · /kategori/{{ $c->slug }}</p>
                    <div class="mt-4 flex gap-2">
                        <button wire:click="edit({{ $c->id }})" class="btn-primary btn-sm"><x-icon name="pencil" class="h-3.5 w-3.5" /> Edit</button>
                        @if($c->image)<button wire:click="removeImage({{ $c->id }})" wire:confirm="Hapus gambar dan kembali ke template?" class="btn-ghost btn-sm">Pakai template</button>@endif
                    </div>
                </div>
            </div>
        @endforeach
    </div>

    <x-modal name="category-form" title="Edit Kategori" maxWidth="2xl">
        <form wire:submit="save" class="grid gap-4 sm:grid-cols-2">
            <div><label class="label">Nama</label><input wire:model="form.name" class="input">@error('form.name')<p class="error">{{ $message }}</p>@enderror</div>
            <div><label class="label">Slug URL</label><input wire:model="form.slug" class="input">@error('form.slug')<p class="error">{{ $message }}</p>@enderror</div>
            <div><label class="label">Harga satuan (Rp)</label><input type="number" min="0" wire:model="form.price" class="input">@error('form.price')<p class="error">{{ $message }}</p>@enderror</div>
            <div><label class="label">Urutan</label><input type="number" min="0" wire:model="form.sort_order" class="input"></div>
            <div class="sm:col-span-2"><label class="label">Tagline</label><input wire:model="form.tagline" class="input"></div>
            <div class="sm:col-span-2"><label class="label">Deskripsi (tampil & dipakai untuk SEO halaman kategori)</label><textarea wire:model="form.description" rows="4" class="input"></textarea></div>
            <div>
                <label class="label">Ikon</label>
                <select wire:model.live="form.icon" class="input">@foreach($icons as $i)<option value="{{ $i }}">{{ $i }}</option>@endforeach</select>
                @error('form.icon')<p class="error">{{ $message }}</p>@enderror
            </div>
            <div>
                <label class="label">Warna</label>
                <div class="flex gap-2"><input type="color" wire:model.live="form.color" class="h-10 w-14 rounded-lg border border-slate-300"><input wire:model.live="form.color" class="input font-mono"></div>
                @error('form.color')<p class="error">{{ $message }}</p>@enderror
            </div>
            <div class="sm:col-span-2">
                <label class="label">Gambar kategori (opsional, rasio 16:9)</label>
                <input type="file" wire:model="image" accept="image/*" class="input p-2">
                @error('image')<p class="error">{{ $message }}</p>@enderror
                @if($image)<img src="{{ $image->temporaryUrl() }}" class="mt-2 h-28 rounded-lg object-cover">@endif
            </div>
            <label class="flex items-center gap-2 text-sm sm:col-span-2"><input type="checkbox" wire:model="form.is_active" class="checkbox"> Aktif (tampil di situs)</label>
            <div class="flex justify-end gap-2 sm:col-span-2"><button type="button" @click="$dispatch('close-modal', 'category-form')" class="btn-outline">Batal</button><button class="btn-primary">Simpan</button></div>
        </form>
    </x-modal>
</div>
