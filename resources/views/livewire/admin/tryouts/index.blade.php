<div>
    <x-page-head title="Tryout" subtitle="Kelola tryout SKD & SKB per kategori.">
        <a href="{{ route('admin.tryouts.create', ['kategori' => $category ?: null]) }}" wire:navigate class="btn-primary"><x-icon name="plus" class="h-4 w-4" /> Tryout Baru</a>
    </x-page-head>
    <div class="card mb-4 grid gap-3 p-3 sm:grid-cols-[1fr_220px_180px]">
        <div class="relative"><x-icon name="search" class="absolute left-3 top-1/2 h-4 w-4 -translate-y-1/2 text-slate-400" /><input wire:model.live.debounce.400ms="search" class="input pl-9" placeholder="Cari judul tryout"></div>
        <select wire:model.live="category" class="input"><option value="">Semua kategori</option>@foreach($categories as $c)<option value="{{ $c->id }}">{{ $c->label() }}</option>@endforeach</select>
        <select wire:model.live="status" class="input"><option value="">Semua status</option><option value="published">Terbit</option><option value="draft">Draft</option><option value="free">Gratis</option></select>
    </div>
    <div class="card overflow-hidden">
        <div class="overflow-x-auto">
            <table class="table">
                <thead><tr><th>Tryout</th><th>Soal</th><th>Durasi</th><th>Nilai</th><th>Dikerjakan</th><th>Status</th><th></th></tr></thead>
                <tbody>
                    @foreach($tryouts as $t)
                        <tr wire:key="t-{{ $t->id }}">
                            <td class="min-w-[240px]"><p class="font-semibold text-navy-900">{{ $t->title }}</p><x-category-badge :category="$t->category" class="mt-1" /></td>
                            <td><span class="font-semibold {{ $t->questions_count ? '' : 'text-rose-600' }}">{{ $t->questions_count }}</span></td>
                            <td class="whitespace-nowrap">{{ $t->duration_minutes }} mnt</td>
                            <td class="whitespace-nowrap text-xs">{{ $t->category->isSkd() ? 'SKD (maks 550)' : 'Skala '.score_format($t->scale_max) }}</td>
                            <td>{{ $t->attempts_count }}</td>
                            <td class="whitespace-nowrap">
                                <button wire:click="toggle({{ $t->id }}, 'is_published')" class="badge {{ $t->is_published ? 'bg-mint-100 text-mint-800' : 'bg-slate-100 text-slate-500' }}">{{ $t->is_published ? 'Terbit' : 'Draft' }}</button>
                                <button wire:click="toggle({{ $t->id }}, 'is_free')" class="badge {{ $t->is_free ? 'bg-sky-100 text-sky-800' : 'bg-white text-slate-400 ring-1 ring-slate-200' }}">{{ $t->is_free ? 'Gratis' : 'Berbayar' }}</button>
                            </td>
                            <td class="whitespace-nowrap text-right">
                                <a href="{{ route('admin.tryouts.questions', $t) }}" wire:navigate class="btn-primary btn-sm">Soal</a>
                                <a href="{{ route('admin.tryouts.edit', $t) }}" wire:navigate class="btn-outline btn-sm">Edit</a>
                                <a href="{{ route('admin.tryouts.results', $t) }}" wire:navigate class="btn-ghost btn-sm">Hasil</a>
                                <button wire:click="duplicate({{ $t->id }})" class="btn-ghost btn-sm" title="Duplikasi"><x-icon name="copy" class="h-3.5 w-3.5" /></button>
                                <button wire:click="delete({{ $t->id }})" wire:confirm="Hapus tryout {{ $t->title }}?" class="btn-soft-danger btn-sm"><x-icon name="trash" class="h-3.5 w-3.5" /></button>
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
        @if($tryouts->isEmpty())<p class="p-10 text-center text-sm text-slate-500">Belum ada tryout.</p>@endif
    </div>
    <div class="mt-4">{{ $tryouts->links() }}</div>
</div>
