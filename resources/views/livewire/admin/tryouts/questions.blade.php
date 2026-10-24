@php($isSkd = $tryout->category->isSkd())
<div>
    <a href="{{ route('admin.tryouts') }}" wire:navigate class="btn-ghost -ml-3 mb-3"><x-icon name="arrow-left" class="h-4 w-4" /> Tryout</a>
    <x-page-head :title="$tryout->title" :subtitle="$tryout->category->label().' · '.$selected->count().' soal · '.$tryout->duration_minutes.' menit'">
        <a href="{{ route('admin.tryouts.edit', $tryout) }}" wire:navigate class="btn-outline">Pengaturan</a>
        <a href="{{ route('admin.questions.create', ['kategori' => $tryout->category_id, 'subtest' => $subtest]) }}" wire:navigate class="btn-outline"><x-icon name="plus" class="h-4 w-4" /> Soal baru di bank</a>
    </x-page-head>

    {{-- Komposisi --}}
    <div class="mb-6 grid gap-3 {{ $isSkd ? 'sm:grid-cols-3' : '' }}">
        @foreach($tryout->category->subtests() as $s)
            @php($target = $isSkd ? config("tryout.skd.composition.$s") : null)
            @php($have = $counts[$s] ?? 0)
            <div class="card p-4">
                <div class="flex items-center justify-between"><p class="text-sm font-bold uppercase text-navy-900">{{ $s }}</p><p class="text-xs text-slate-500">bank: {{ $bankCounts[$s] ?? 0 }}</p></div>
                <p class="mt-1 text-2xl font-extrabold {{ $target && $have !== $target ? 'text-amber-600' : 'text-navy-900' }}">{{ $have }}@if($target)<span class="text-base text-slate-400"> / {{ $target }}</span>@endif</p>
                @if($target)<div class="mt-2 h-1.5 overflow-hidden rounded-full bg-slate-100"><div class="h-full rounded-full {{ $have >= $target ? 'bg-mint-500' : 'bg-amber-400' }}" style="width: {{ min(100, $have / $target * 100) }}%"></div></div>@endif
            </div>
        @endforeach
    </div>

    <div class="grid gap-6 2xl:grid-cols-2">
        {{-- Soal dalam tryout --}}
        <div class="card overflow-hidden">
            <div class="flex flex-wrap items-center justify-between gap-2 border-b border-slate-100 px-5 py-4">
                <p class="font-bold text-navy-900">Soal di tryout ini</p>
                @if($selected->isNotEmpty())<button wire:click="clearAll" wire:confirm="Keluarkan semua soal dari tryout ini?" class="btn-ghost btn-sm text-rose-600">Kosongkan</button>@endif
            </div>
            <p class="bg-slate-50 px-5 py-2 text-xs text-slate-500">Seret ⠿ untuk mengubah urutan.{{ $isSkd ? ' Peserta selalu mengerjakan berurutan TWK → TIU → TKP.' : '' }}</p>
            <ol class="max-h-[70vh] divide-y divide-slate-100 overflow-y-auto" x-sortable="(ids) => $wire.reorder(ids)">
                @forelse($selected as $i => $q)
                    <li class="flex items-start gap-3 px-4 py-3" data-id="{{ $q->id }}" wire:key="sel-{{ $q->id }}">
                        <button data-handle class="mt-0.5 cursor-grab text-slate-300 hover:text-slate-500" aria-label="Geser"><x-icon name="menu" class="h-4 w-4" /></button>
                        <span class="w-7 shrink-0 pt-0.5 text-xs font-bold text-slate-400">{{ $i + 1 }}</span>
                        <div class="min-w-0 flex-1">
                            <p class="line-clamp-2 text-sm text-navy-900">{{ $q->excerpt(160) }}</p>
                            <p class="mt-0.5 text-xs text-slate-500"><span class="font-bold uppercase">{{ $q->subtest }}</span> @if($q->topic)· {{ $q->topic->name }}@endif</p>
                        </div>
                        <button wire:click="preview({{ $q->id }})" class="text-xs font-semibold text-brand-700">Lihat</button>
                        <button wire:click="remove({{ $q->id }})" class="text-slate-300 hover:text-rose-600" aria-label="Keluarkan"><x-icon name="x" class="h-4 w-4" /></button>
                    </li>
                @empty
                    <li class="p-10 text-center text-sm text-slate-500">Belum ada soal. Pilih dari bank soal di samping atau gunakan isi otomatis.</li>
                @endforelse
            </ol>
        </div>

        {{-- Bank soal --}}
        <div class="space-y-4">
            <div class="card card-pad">
                <p class="font-bold text-navy-900">Isi otomatis (acak dari bank)</p>
                <p class="mt-1 text-xs text-slate-500">Sistem menambahkan soal acak yang belum dipakai di tryout ini hingga mencapai jumlah target.</p>
                <div class="mt-3 flex flex-wrap items-end gap-3">
                    @foreach($auto as $s => $n)
                        <div class="w-24"><label class="label uppercase">{{ $s }}</label><input type="number" min="0" wire:model="auto.{{ $s }}" class="input"></div>
                    @endforeach
                    <button wire:click="autoFill" class="btn-primary"><x-icon name="sparkles" class="h-4 w-4" /> Isi Otomatis</button>
                </div>
            </div>

            <div class="card overflow-hidden">
                <div class="grid gap-2 border-b border-slate-100 p-3 sm:grid-cols-3">
                    @if($isSkd)
                        <select wire:model.live="subtest" class="input">@foreach(['twk', 'tiu', 'tkp'] as $s)<option value="{{ $s }}">{{ strtoupper($s) }}</option>@endforeach</select>
                    @endif
                    <select wire:model.live="topic" class="input"><option value="">Semua topik</option>@foreach($topics as $t)<option value="{{ $t->id }}">{{ $t->name }}</option>@endforeach</select>
                    <input wire:model.live.debounce.400ms="search" class="input {{ $isSkd ? '' : 'sm:col-span-2' }}" placeholder="Cari teks soal">
                </div>
                <div class="flex items-center justify-between gap-2 bg-slate-50 px-4 py-2 text-xs">
                    <label class="flex items-center gap-2"><input type="checkbox" wire:model.live="hideUsed" class="checkbox"> Sembunyikan yang sudah dipilih</label>
                    <button wire:click="addPicked" @disabled(! $picked) class="btn-primary btn-sm">Tambahkan {{ count($picked) ?: '' }} terpilih</button>
                </div>
                <div class="divide-y divide-slate-100">
                    @forelse($bank as $q)
                        <div class="flex items-start gap-3 px-4 py-3" wire:key="bank-{{ $q->id }}">
                            @if($selectedIds->contains($q->id))
                                <x-icon name="check-circle" class="mt-0.5 h-4 w-4 text-mint-600" />
                            @else
                                <input type="checkbox" value="{{ $q->id }}" wire:model.live="picked" class="checkbox mt-0.5">
                            @endif
                            <div class="min-w-0 flex-1">
                                <p class="line-clamp-2 text-sm text-navy-900">{{ $q->excerpt(160) }}</p>
                                <p class="mt-0.5 text-xs text-slate-500">#{{ $q->id }} @if($q->topic)· {{ $q->topic->name }}@endif · dipakai {{ $q->tryouts_count }} tryout</p>
                            </div>
                            <button wire:click="preview({{ $q->id }})" class="text-xs font-semibold text-brand-700">Lihat</button>
                            @unless($selectedIds->contains($q->id))<button wire:click="addOne({{ $q->id }})" class="btn-outline btn-sm">+</button>@endunless
                        </div>
                    @empty
                        <p class="p-10 text-center text-sm text-slate-500">Tidak ada soal di bank untuk filter ini. <a href="{{ route('admin.questions.create', ['kategori' => $tryout->category_id, 'subtest' => $subtest]) }}" class="font-semibold text-brand-700 underline" wire:navigate>Buat soal</a> atau <a href="{{ route('admin.questions.import', ['kategori' => $tryout->category_id]) }}" class="font-semibold text-brand-700 underline" wire:navigate>impor CSV</a>.</p>
                    @endforelse
                </div>
                <div class="border-t border-slate-100 p-3">{{ $bank->links() }}</div>
            </div>
        </div>
    </div>

    <x-modal name="q-preview" title="Pratinjau soal" maxWidth="2xl">
        @include('partials.question-preview', ['q' => $preview])
    </x-modal>
</div>
