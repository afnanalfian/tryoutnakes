<div>
    <x-page-head title="Metode Pembayaran" subtitle="QRIS wajib diisi. Metode lain (bank/e-wallet) bebas ditambahkan dan semuanya tampil di halaman pembayaran." />
    <div class="grid gap-6 xl:grid-cols-[380px_1fr]">
        <form wire:submit="saveQris" class="card card-pad space-y-4">
            <div class="flex items-center justify-between"><p class="font-bold text-navy-900">QRIS</p>@unless(setting('qris_image'))<span class="badge bg-rose-100 text-rose-700">Wajib</span>@endunless</div>
            <div class="flex justify-center rounded-2xl bg-slate-50 p-4">
                @if($qris)
                    <img src="{{ $qris->temporaryUrl() }}" class="max-h-64 rounded-lg">
                @elseif(setting('qris_image'))
                    <img src="{{ media_url(setting('qris_image')) }}" class="max-h-64 rounded-lg">
                @else
                    <p class="py-16 text-sm text-slate-400">Belum ada gambar QRIS</p>
                @endif
            </div>
            <div><label class="label">Unggah gambar QRIS</label><input type="file" wire:model="qris" accept="image/*" class="input p-2">@error('qris')<p class="error">{{ $message }}</p>@enderror</div>
            <div><label class="label">Nama merchant (opsional)</label><input wire:model="qrisName" class="input" placeholder="mis. NAKESPREP ID"></div>
            <div><label class="label">Keterangan</label><textarea wire:model="qrisNote" rows="3" class="input"></textarea></div>
            <button class="btn-primary w-full">Simpan QRIS</button>
        </form>

        <div class="card">
            <div class="flex items-center justify-between border-b border-slate-100 px-5 py-4">
                <p class="font-bold text-navy-900">Metode lain</p>
                <button wire:click="create" class="btn-primary btn-sm"><x-icon name="plus" class="h-3.5 w-3.5" /> Tambah</button>
            </div>
            <div class="divide-y divide-slate-100">
                @forelse($methods as $m)
                    <div class="flex flex-col gap-3 px-5 py-4 sm:flex-row sm:items-center" wire:key="m-{{ $m->id }}">
                        <div class="min-w-0 flex-1">
                            <div class="flex items-center gap-2"><p class="font-bold text-navy-900">{{ $m->name }}</p>@unless($m->is_active)<span class="badge bg-slate-100 text-slate-500">Nonaktif</span>@endunless</div>
                            <p class="font-mono text-sm text-navy-900">{{ $m->account_number }} @if($m->account_holder)<span class="font-sans text-slate-500">· a.n. {{ $m->account_holder }}</span>@endif</p>
                            @if($m->description)<p class="mt-0.5 text-sm text-slate-500">{{ $m->description }}</p>@endif
                        </div>
                        <div class="flex gap-1.5">
                            <button wire:click="toggle({{ $m->id }})" class="btn-ghost btn-sm">{{ $m->is_active ? 'Nonaktifkan' : 'Aktifkan' }}</button>
                            <button wire:click="edit({{ $m->id }})" class="btn-outline btn-sm">Edit</button>
                            <button wire:click="delete({{ $m->id }})" wire:confirm="Hapus metode ini?" class="btn-soft-danger btn-sm"><x-icon name="trash" class="h-3.5 w-3.5" /></button>
                        </div>
                    </div>
                @empty
                    <p class="px-5 py-10 text-center text-sm text-slate-500">Belum ada metode tambahan.</p>
                @endforelse
            </div>
        </div>
    </div>

    <x-modal name="method-form" :title="$editingId ? 'Edit Metode' : 'Tambah Metode'">
        <form wire:submit="save" class="space-y-4">
            <div><label class="label">Metode</label><input wire:model="form.name" class="input" placeholder="mis. BRI, BCA, GoPay, DANA">@error('form.name')<p class="error">{{ $message }}</p>@enderror</div>
            <div><label class="label">No. Rekening / No. HP</label><input wire:model="form.account_number" class="input font-mono">@error('form.account_number')<p class="error">{{ $message }}</p>@enderror</div>
            <div><label class="label">Atas nama (opsional)</label><input wire:model="form.account_holder" class="input"></div>
            <div><label class="label">Keterangan</label><textarea wire:model="form.description" rows="3" class="input" placeholder="Silakan transfer melalui rekening BRI berikut ..."></textarea></div>
            <div class="grid grid-cols-2 gap-4"><div><label class="label">Urutan</label><input type="number" wire:model="form.sort_order" class="input"></div><label class="mt-7 flex items-center gap-2 text-sm"><input type="checkbox" wire:model="form.is_active" class="checkbox"> Aktif</label></div>
            <div class="flex justify-end gap-2"><button type="button" @click="$dispatch('close-modal', 'method-form')" class="btn-outline">Batal</button><button class="btn-primary">Simpan</button></div>
        </form>
    </x-modal>
</div>
