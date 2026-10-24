<div>
    <x-page-head title="FAQ" subtitle="Tampil di landing page & halaman FAQ (juga sebagai rich result FAQ di Google). Seret untuk mengurutkan.">
        <button wire:click="create" class="btn-primary"><x-icon name="plus" class="h-4 w-4" /> FAQ Baru</button>
    </x-page-head>
    <div class="card divide-y divide-slate-100" x-sortable="(ids) => $wire.reorder(ids)">
        @forelse($faqs as $f)
            <div class="flex items-start gap-3 p-4" data-id="{{ $f->id }}" wire:key="f-{{ $f->id }}">
                <button data-handle class="mt-0.5 cursor-grab text-slate-300 hover:text-slate-500" aria-label="Geser"><x-icon name="menu" class="h-5 w-5" /></button>
                <div class="min-w-0 flex-1">
                    <p class="font-semibold text-navy-900">{{ $f->question }} @unless($f->is_active)<span class="badge bg-slate-100 text-slate-500">Nonaktif</span>@endunless</p>
                    <p class="mt-1 line-clamp-2 text-sm text-slate-500">{{ $f->answer }}</p>
                </div>
                <button wire:click="edit({{ $f->id }})" class="btn-outline btn-sm">Edit</button>
                <button wire:click="delete({{ $f->id }})" wire:confirm="Hapus FAQ ini?" class="btn-soft-danger btn-sm"><x-icon name="trash" class="h-3.5 w-3.5" /></button>
            </div>
        @empty
            <p class="p-10 text-center text-sm text-slate-500">Belum ada FAQ.</p>
        @endforelse
    </div>
    <x-modal name="faq-form" :title="$editingId ? 'Edit FAQ' : 'FAQ Baru'" maxWidth="xl">
        <form wire:submit="save" class="space-y-4">
            <div><label class="label">Pertanyaan</label><input wire:model="form.question" class="input">@error('form.question')<p class="error">{{ $message }}</p>@enderror</div>
            <div><label class="label">Jawaban</label><textarea wire:model="form.answer" rows="6" class="input"></textarea>@error('form.answer')<p class="error">{{ $message }}</p>@enderror</div>
            <label class="flex items-center gap-2 text-sm"><input type="checkbox" wire:model="form.is_active" class="checkbox"> Aktif</label>
            <div class="flex justify-end gap-2"><button type="button" @click="$dispatch('close-modal', 'faq-form')" class="btn-outline">Batal</button><button class="btn-primary">Simpan</button></div>
        </form>
    </x-modal>
</div>
