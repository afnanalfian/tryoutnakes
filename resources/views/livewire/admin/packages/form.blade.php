<div>
    <a href="{{ route('admin.packages') }}" wire:navigate class="btn-ghost -ml-3 mb-3"><x-icon name="arrow-left" class="h-4 w-4" /> Paket</a>
    <form wire:submit="save" class="grid gap-6 xl:grid-cols-[1fr_380px]">
        <div class="space-y-6">
            <div class="card card-pad grid gap-4 sm:grid-cols-2">
                <div class="sm:col-span-2"><label class="label">Nama paket</label><input wire:model.live.debounce.400ms="name" class="input" placeholder="mis. Paket SKD + SKB Bidan">@error('name')<p class="error">{{ $message }}</p>@enderror</div>
                <div><label class="label">Slug URL</label><input wire:model="slug" class="input">@error('slug')<p class="error">{{ $message }}</p>@enderror</div>
                <div><label class="label">Label (opsional)</label><input wire:model="badge" class="input" placeholder="Terlaris / Paling Hemat"></div>
                <div class="sm:col-span-2"><label class="label">Tagline singkat</label><input wire:model="tagline" class="input"></div>
                <div class="sm:col-span-2"><label class="label">Deskripsi</label><textarea wire:model="description" rows="4" class="input"></textarea></div>
                <div class="sm:col-span-2">
                    <label class="label">Gambar paket (opsional — kosong = template otomatis)</label>
                    <div class="flex items-center gap-4">
                        @if($image)<img src="{{ $image->temporaryUrl() }}" class="h-16 rounded-lg">@elseif($package?->image)<img src="{{ $package->imageUrl() }}" class="h-16 rounded-lg"><button type="button" wire:click="removeImage" class="btn-ghost btn-sm text-rose-600">Hapus</button>@endif
                        <input type="file" wire:model="image" accept="image/*" class="input p-2">
                    </div>
                    @error('image')<p class="error">{{ $message }}</p>@enderror
                </div>
            </div>

            <div class="card card-pad">
                <div class="flex items-center justify-between"><p class="font-bold text-navy-900">Pilih tryout ({{ count($selected) }} dipilih)</p></div>
                @error('selected')<p class="error">{{ $message }}</p>@enderror
                <div class="mt-4 space-y-4">
                    @foreach($categories as $c)
                        @continue($c->tryouts->isEmpty())
                        @php($allOn = $c->tryouts->every(fn ($t) => in_array((string) $t->id, $selected, true)))
                        <div class="rounded-2xl border border-slate-200">
                            <div class="flex items-center justify-between gap-3 border-b border-slate-100 px-4 py-2.5">
                                <x-category-badge :category="$c" />
                                <button type="button" wire:click="selectCategory({{ $c->id }}, {{ $allOn ? 'false' : 'true' }})" class="text-xs font-semibold text-brand-700">{{ $allOn ? 'Batalkan semua' : 'Pilih semua' }}</button>
                            </div>
                            <div class="grid gap-1 p-2 sm:grid-cols-2">
                                @foreach($c->tryouts as $t)
                                    <label class="flex items-center gap-2.5 rounded-xl px-2.5 py-2 text-sm hover:bg-slate-50">
                                        <input type="checkbox" value="{{ $t->id }}" wire:model.live="selected" class="checkbox">
                                        <span class="min-w-0 flex-1"><span class="block truncate font-medium text-navy-900">{{ $t->title }}</span><span class="text-xs text-slate-500">{{ $t->questions_count }} soal · {{ rupiah($t->price()) }} @unless($t->is_published)· <span class="text-amber-600">draft</span>@endunless</span></span>
                                    </label>
                                @endforeach
                            </div>
                        </div>
                    @endforeach
                </div>
            </div>
        </div>

        <aside class="space-y-4 xl:sticky xl:top-24 xl:self-start">
            <div class="card card-pad space-y-4">
                <div><label class="label">Harga paket (Rp)</label><input type="number" min="0" wire:model.live.debounce.400ms="price" class="input text-lg font-bold">@error('price')<p class="error">{{ $message }}</p>@enderror</div>
                <div class="rounded-xl bg-slate-50 p-3 text-sm">
                    <div class="flex justify-between"><span class="text-slate-500">Harga normal (satuan)</span><span class="font-semibold">{{ rupiah($normal) }}</span></div>
                    @if(is_numeric($price) && $normal > (int) $price)<div class="mt-1 flex justify-between text-mint-700"><span>Hemat pembeli</span><span class="font-bold">{{ rupiah($normal - (int) $price) }} ({{ round(($normal - (int) $price) / max(1, $normal) * 100) }}%)</span></div>@endif
                    @if(is_numeric($price) && $normal && (int) $price > $normal)<p class="mt-1 text-xs text-amber-700">Harga paket lebih mahal dari total satuan — konversi otomatis keranjang tidak akan memakai paket ini.</p>@endif
                </div>
                <div><label class="label">Urutan</label><input type="number" min="0" wire:model="sort_order" class="input"></div>
                <label class="flex items-center gap-2 text-sm"><input type="checkbox" wire:model="is_featured" class="checkbox"> Tampilkan di landing (unggulan)</label>
                <label class="flex items-center gap-2 text-sm"><input type="checkbox" wire:model="is_active" class="checkbox"> Aktif / dijual</label>
                <button class="btn-primary btn-lg w-full" wire:loading.attr="disabled">Simpan Paket</button>
            </div>
        </aside>
    </form>
</div>
