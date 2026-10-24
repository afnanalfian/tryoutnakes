<div>
    <x-page-head title="Bank Soal" subtitle="Semua soal pilihan ganda. TWK/TIU/SKB memakai kunci jawaban, TKP memakai bobot 1–5.">
        <a href="{{ route('admin.questions.import', ['kategori' => $category]) }}" wire:navigate class="btn-outline"><x-icon name="upload" class="h-4 w-4" /> Impor CSV</a>
        <a href="{{ route('admin.questions.create', ['kategori' => $category, 'subtest' => $subtest, 'topik' => is_numeric($topic) ? $topic : null]) }}" wire:navigate class="btn-primary"><x-icon name="plus" class="h-4 w-4" /> Soal Baru</a>
    </x-page-head>

    {{-- Kategori --}}
    <div class="no-scrollbar -mx-4 mb-4 flex gap-2 overflow-x-auto px-4 sm:mx-0 sm:flex-wrap sm:px-0">
        @foreach($categories as $c)
            @php($n = collect($counts[$c->id] ?? [])->sum())
            <button wire:click="$set('category', '{{ $c->id }}')" class="flex shrink-0 items-center gap-2 rounded-2xl border px-3.5 py-2 text-sm font-semibold transition {{ (string) $category === (string) $c->id ? 'border-navy-900 bg-navy-900 text-white' : 'border-slate-200 bg-white text-slate-700 hover:border-navy-300' }}">
                <x-icon :name="$c->icon" class="h-4 w-4" /> {{ $c->isSkd() ? 'SKD' : $c->name }} <span class="text-[11px] opacity-70">{{ $n }}</span>
            </button>
        @endforeach
    </div>

    <div class="grid gap-6 xl:grid-cols-[1fr_300px]">
        <div class="min-w-0">
            @if($cat?->isSkd())
                <div class="mb-4 inline-flex rounded-2xl bg-white p-1 ring-1 ring-slate-200">
                    @foreach(['twk' => 'TWK', 'tiu' => 'TIU', 'tkp' => 'TKP'] as $s => $l)
                        <button wire:click="$set('subtest', '{{ $s }}')" class="rounded-xl px-4 py-1.5 text-sm font-bold {{ $subtest === $s ? 'bg-navy-900 text-white' : 'text-slate-500' }}">{{ $l }} <span class="text-[11px] font-medium opacity-70">{{ $counts[$cat->id][$s] ?? 0 }}</span></button>
                    @endforeach
                </div>
            @endif
            <div class="card mb-4 grid gap-3 p-3 sm:grid-cols-[1fr_200px_150px]">
                <div class="relative"><x-icon name="search" class="absolute left-3 top-1/2 h-4 w-4 -translate-y-1/2 text-slate-400" /><input wire:model.live.debounce.400ms="search" class="input pl-9" placeholder="Cari teks soal / pembahasan"></div>
                <select wire:model.live="topic" class="input"><option value="">Semua topik</option><option value="none">Tanpa topik</option>@foreach($topics as $t)<option value="{{ $t->id }}">{{ $t->name }} ({{ $t->questions_count }})</option>@endforeach</select>
                <select wire:model.live="difficulty" class="input"><option value="">Semua tingkat</option><option value="1">Mudah</option><option value="2">Sedang</option><option value="3">Sulit</option></select>
            </div>

            @if($selected)
                <div class="mb-3 flex items-center justify-between rounded-2xl bg-navy-900 px-4 py-2.5 text-sm text-white">
                    <span>{{ count($selected) }} soal dipilih</span>
                    <button wire:click="deleteSelected" wire:confirm="Hapus {{ count($selected) }} soal terpilih? Soal juga dikeluarkan dari tryout." class="btn btn-sm bg-rose-500 text-white hover:bg-rose-600">Hapus</button>
                </div>
            @endif

            <div class="space-y-3">
                @forelse($questions as $q)
                    @php($tkp = $q->subtest === 'tkp')
                    <div class="card p-4" wire:key="q-{{ $q->id }}">
                        <div class="flex items-start gap-3">
                            <input type="checkbox" value="{{ $q->id }}" wire:model.live="selected" class="checkbox mt-1">
                            <div class="min-w-0 flex-1">
                                <p class="line-clamp-2 text-[15px] text-navy-900">{{ $q->excerpt(220) }}</p>
                                <div class="mt-2 flex flex-wrap items-center gap-1.5 text-xs text-slate-500">
                                    <span class="font-mono text-slate-400">#{{ $q->id }}</span>
                                    @if($q->topic)<span class="badge bg-slate-100 text-slate-600">{{ $q->topic->name }}</span>@endif
                                    @if($q->difficulty)<span class="badge bg-slate-100 text-slate-600">{{ [1 => 'Mudah', 2 => 'Sedang', 3 => 'Sulit'][$q->difficulty] }}</span>@endif
                                    @if($tkp)<span class="badge bg-amber-50 text-amber-700">Bobot {{ $q->options->pluck('weight')->implode('-') }}</span>@else<span class="badge bg-mint-50 text-mint-700">Kunci {{ $q->correctOption()?->label() ?? '?' }}</span>@endif
                                    @if($q->image)<span class="badge bg-sky-50 text-sky-700">Gambar</span>@endif
                                    <span>· dipakai {{ $q->tryouts_count }} tryout</span>
                                </div>
                            </div>
                            <div class="flex shrink-0 gap-1">
                                <button wire:click="preview({{ $q->id }})" class="btn-ghost btn-sm" title="Pratinjau"><x-icon name="eye" class="h-4 w-4" /></button>
                                <a href="{{ route('admin.questions.edit', $q) }}" wire:navigate class="btn-outline btn-sm" title="Edit"><x-icon name="pencil" class="h-4 w-4" /></a>
                                <button wire:click="duplicate({{ $q->id }})" class="btn-ghost btn-sm" title="Duplikasi"><x-icon name="copy" class="h-4 w-4" /></button>
                                <button wire:click="delete({{ $q->id }})" wire:confirm="Hapus soal #{{ $q->id }}?{{ $q->tryouts_count ? ' Soal ini dipakai di '.$q->tryouts_count.' tryout dan akan dikeluarkan.' : '' }}" class="btn-soft-danger btn-sm" title="Hapus"><x-icon name="trash" class="h-4 w-4" /></button>
                            </div>
                        </div>
                    </div>
                @empty
                    <x-empty icon="book" title="Belum ada soal" text="Mulai buat soal atau impor dari CSV.">
                        <a href="{{ route('admin.questions.create', ['kategori' => $category, 'subtest' => $subtest]) }}" wire:navigate class="btn-primary">Buat Soal</a>
                    </x-empty>
                @endforelse
            </div>
            <div class="mt-4">{{ $questions->links() }}</div>
        </div>

        {{-- Topik --}}
        <aside class="card h-fit p-4 xl:sticky xl:top-24">
            <p class="font-bold text-navy-900">Topik / Materi {{ strtoupper($subtest) }}</p>
            <p class="text-xs text-slate-500">Dipakai untuk filter & analisis akurasi per topik di halaman progres peserta.</p>
            <form wire:submit="addTopic" class="mt-3 flex gap-2"><input wire:model="newTopic" class="input" placeholder="Topik baru"><button class="btn-primary btn-sm px-3"><x-icon name="plus" class="h-4 w-4" /></button></form>
            @error('newTopic')<p class="error">{{ $message }}</p>@enderror
            <ul class="mt-3 max-h-[50vh] space-y-1 overflow-y-auto">
                @foreach($topics as $t)
                    <li class="group flex items-center gap-2 rounded-lg px-2 py-1.5 text-sm hover:bg-slate-50" wire:key="tp-{{ $t->id }}">
                        @if($renamingTopic === $t->id)
                            <input wire:model="renameValue" wire:keydown.enter="saveRename" class="input py-1"><button wire:click="saveRename" class="text-xs font-semibold text-brand-700">OK</button>
                        @else
                            <button wire:click="$set('topic', '{{ $t->id }}')" class="min-w-0 flex-1 truncate text-left {{ (string) $topic === (string) $t->id ? 'font-bold text-navy-900' : 'text-slate-600' }}">{{ $t->name }}</button>
                            <span class="text-xs text-slate-400">{{ $t->questions_count }}</span>
                            <button wire:click="startRename({{ $t->id }})" class="hidden text-slate-400 hover:text-navy-900 group-hover:block"><x-icon name="pencil" class="h-3.5 w-3.5" /></button>
                            <button wire:click="deleteTopic({{ $t->id }})" wire:confirm="Hapus topik? Soal tidak ikut terhapus." class="hidden text-slate-400 hover:text-rose-600 group-hover:block"><x-icon name="x" class="h-3.5 w-3.5" /></button>
                        @endif
                    </li>
                @endforeach
            </ul>
        </aside>
    </div>

    <x-modal name="q-preview" title="Pratinjau soal" maxWidth="2xl">
        @include('partials.question-preview', ['q' => $preview])
        @if($preview)<div class="mt-5 flex justify-end"><a href="{{ route('admin.questions.edit', $preview) }}" wire:navigate class="btn-primary">Edit Soal</a></div>@endif
    </x-modal>
</div>
