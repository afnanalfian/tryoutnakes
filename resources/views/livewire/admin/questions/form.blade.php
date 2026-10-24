@php($isTkp = $subtest === 'tkp')
@php($labels = ['A', 'B', 'C', 'D', 'E', 'F'])
<div x-data="questionEditor()" class="relative">
    <a href="{{ route('admin.questions', ['kategori' => $category_id, 'subtest' => $subtest]) }}" wire:navigate class="btn-ghost -ml-3 mb-3"><x-icon name="arrow-left" class="h-4 w-4" /> Bank Soal</a>

    <form wire:submit="save" class="grid gap-6 xl:grid-cols-[1fr_340px]">
        <div class="min-w-0 space-y-6">
            {{-- Teks soal --}}
            <div class="card card-pad">
                <div class="flex flex-wrap items-center justify-between gap-2">
                    <label class="label mb-0" for="q-text">Teks soal <span class="text-rose-500">*</span></label>
                    @include('partials.editor-toolbar', ['target' => 'q-text'])
                </div>
                <textarea id="q-text" wire:model="question_text" @input.debounce.250ms="preview('q-text')" rows="7" class="input mt-2 font-mono text-[13px] leading-relaxed" placeholder="Tulis soal di sini… Gunakan tombol ∑ Rumus untuk menyisipkan persamaan."></textarea>
                @error('question_text')<p class="error">{{ $message }}</p>@enderror
                <p class="mb-1.5 mt-4 text-xs font-bold uppercase tracking-wider text-slate-400">Pratinjau</p>
                <div wire:ignore class="rich min-h-[56px] rounded-xl border border-dashed border-slate-200 bg-slate-50/60 p-4" data-math data-preview-for="q-text"></div>

                <div class="mt-5">
                    <label class="label">Gambar soal (opsional)</label>
                    <div class="flex flex-wrap items-center gap-4">
                        @if($imageUpload)
                            <img src="{{ $imageUpload->temporaryUrl() }}" class="h-24 rounded-lg border">
                        @elseif($image && ! $removeImage)
                            <img src="{{ media_url($image) }}" class="h-24 rounded-lg border">
                            <button type="button" wire:click="$set('removeImage', true)" class="btn-ghost btn-sm text-rose-600">Hapus gambar</button>
                        @endif
                        <input type="file" wire:model="imageUpload" accept="image/*" class="input max-w-sm p-2">
                    </div>
                    @error('imageUpload')<p class="error">{{ $message }}</p>@enderror
                </div>
            </div>

            {{-- Opsi --}}
            <div class="card card-pad">
                <div class="flex flex-wrap items-center justify-between gap-2">
                    <div>
                        <p class="font-bold text-navy-900">Pilihan jawaban</p>
                        <p class="text-xs text-slate-500">{{ $isTkp ? 'TKP: isi bobot 1–5 untuk setiap opsi (tidak ada jawaban salah).' : 'Pilih satu kunci jawaban yang benar.' }}</p>
                    </div>
                    @if(count($options) < 6)<button type="button" wire:click="addOption" class="btn-outline btn-sm"><x-icon name="plus" class="h-3.5 w-3.5" /> Opsi</button>@endif
                </div>
                @error('correct')<p class="error">{{ $message }}</p>@enderror
                <div class="mt-4 space-y-3">
                    @foreach($options as $i => $o)
                        @php($id = 'opt-'.$o['key'])
                        <div wire:key="{{ $id }}" @class(['rounded-2xl border p-3 sm:p-4', 'border-mint-300 bg-mint-50/40' => ! $isTkp && (int) $correct === $i, 'border-slate-200' => $isTkp || (int) $correct !== $i])>
                            <div class="flex flex-wrap items-center gap-2">
                                <span class="inline-flex h-8 w-8 items-center justify-center rounded-lg bg-navy-900 text-sm font-bold text-white">{{ $labels[$i] }}</span>
                                @if($isTkp)
                                    <div class="flex items-center gap-1">
                                        <span class="text-xs font-semibold text-slate-500">Bobot</span>
                                        @foreach([1, 2, 3, 4, 5] as $w)
                                            <button type="button" wire:click="$set('options.{{ $i }}.weight', {{ $w }})" class="h-8 w-8 rounded-lg text-sm font-bold {{ (int) $o['weight'] === $w ? 'bg-amber-400 text-amber-950' : 'bg-white text-slate-500 ring-1 ring-slate-200 hover:ring-amber-300' }}">{{ $w }}</button>
                                        @endforeach
                                    </div>
                                @else
                                    <label class="flex cursor-pointer items-center gap-2 rounded-lg px-2 py-1 text-xs font-semibold {{ (int) $correct === $i ? 'text-mint-700' : 'text-slate-500' }}">
                                        <input type="radio" wire:model.live="correct" value="{{ $i }}" class="h-4 w-4 border-slate-300 text-mint-600 focus:ring-mint-500"> Kunci jawaban
                                    </label>
                                @endif
                                <div class="ml-auto flex items-center gap-1">
                                    @include('partials.editor-toolbar', ['target' => $id, 'compact' => true])
                                    @if(count($options) > 2)<button type="button" wire:click="removeOption({{ $i }})" class="rounded-lg p-1.5 text-slate-400 hover:bg-rose-50 hover:text-rose-600" title="Hapus opsi"><x-icon name="trash" class="h-4 w-4" /></button>@endif
                                </div>
                            </div>
                            @error("options.$i.weight")<p class="error">{{ $message }}</p>@enderror
                            <div class="mt-2 grid gap-3 lg:grid-cols-2">
                                <div>
                                    <textarea id="{{ $id }}" wire:model="options.{{ $i }}.text" @input.debounce.250ms="preview('{{ $id }}')" rows="2" class="input font-mono text-[13px]" placeholder="Teks opsi {{ $labels[$i] }}"></textarea>
                                    @error("options.$i.text")<p class="error">{{ $message }}</p>@enderror
                                </div>
                                <div wire:ignore class="rich min-h-[44px] rounded-xl border border-dashed border-slate-200 bg-white/70 px-3 py-2 text-sm" data-math data-preview-for="{{ $id }}" x-init="$nextTick(() => preview('{{ $id }}'))"></div>
                            </div>
                            <div class="mt-2 flex flex-wrap items-center gap-3 text-xs">
                                @if(! empty($optionUploads[$i]))
                                    <img src="{{ $optionUploads[$i]->temporaryUrl() }}" class="h-14 rounded border">
                                @elseif($o['image'] && ! $o['remove_image'])
                                    <img src="{{ media_url($o['image']) }}" class="h-14 rounded border">
                                    <button type="button" wire:click="$set('options.{{ $i }}.remove_image', true)" class="font-semibold text-rose-600">Hapus gambar</button>
                                @endif
                                <label class="inline-flex cursor-pointer items-center gap-1.5 font-semibold text-slate-500 hover:text-navy-900"><x-icon name="photo" class="h-4 w-4" /> Gambar opsi<input type="file" wire:model="optionUploads.{{ $i }}" accept="image/*" class="sr-only"></label>
                                @error("optionUploads.$i")<span class="error">{{ $message }}</span>@enderror
                            </div>
                        </div>
                    @endforeach
                </div>
            </div>

            {{-- Pembahasan --}}
            <div class="card card-pad">
                <div class="flex flex-wrap items-center justify-between gap-2">
                    <label class="label mb-0" for="q-expl">Pembahasan</label>
                    @include('partials.editor-toolbar', ['target' => 'q-expl'])
                </div>
                <textarea id="q-expl" wire:model="explanation" @input.debounce.250ms="preview('q-expl')" rows="5" class="input mt-2 font-mono text-[13px]" placeholder="Jelaskan mengapa jawaban tersebut benar…"></textarea>
                <p class="mb-1.5 mt-4 text-xs font-bold uppercase tracking-wider text-slate-400">Pratinjau</p>
                <div wire:ignore class="rich min-h-[48px] rounded-xl border border-dashed border-slate-200 bg-slate-50/60 p-4 text-sm" data-math data-preview-for="q-expl"></div>
            </div>
        </div>

        {{-- Sidebar --}}
        <aside class="space-y-4 xl:sticky xl:top-24 xl:self-start">
            <div class="card card-pad space-y-4">
                <div>
                    <label class="label">Kategori</label>
                    <select wire:model.live="category_id" class="input">@foreach($categories as $c)<option value="{{ $c->id }}">{{ $c->label() }}</option>@endforeach</select>
                </div>
                @if($cat?->isSkd())
                    <div>
                        <label class="label">Subtes</label>
                        <div class="grid grid-cols-3 gap-1 rounded-xl bg-slate-100 p-1">
                            @foreach(['twk', 'tiu', 'tkp'] as $s)
                                <button type="button" wire:click="$set('subtest', '{{ $s }}')" class="rounded-lg py-1.5 text-sm font-bold {{ $subtest === $s ? 'bg-white text-navy-900 shadow' : 'text-slate-500' }}">{{ strtoupper($s) }}</button>
                            @endforeach
                        </div>
                    </div>
                @endif
                <div>
                    <label class="label">Topik / materi</label>
                    <select wire:model="topic_id" class="input"><option value="">— Tanpa topik —</option>@foreach($topics as $t)<option value="{{ $t->id }}">{{ $t->name }}</option>@endforeach</select>
                    <input wire:model="newTopic" class="input mt-2" placeholder="…atau ketik topik baru">
                </div>
                <div>
                    <label class="label">Tingkat kesulitan</label>
                    <div class="grid grid-cols-3 gap-1 rounded-xl bg-slate-100 p-1">
                        @foreach([1 => 'Mudah', 2 => 'Sedang', 3 => 'Sulit'] as $v => $l)
                            <button type="button" wire:click="$set('difficulty', {{ $v }})" class="rounded-lg py-1.5 text-xs font-bold {{ (int) $difficulty === $v ? 'bg-white text-navy-900 shadow' : 'text-slate-500' }}">{{ $l }}</button>
                        @endforeach
                    </div>
                </div>
                @if($usedIn->isNotEmpty())
                    <p class="rounded-xl bg-amber-50 p-3 text-xs text-amber-800">Dipakai di: {{ $usedIn->implode(', ') }}. Perubahan langsung berlaku untuk tryout tersebut.</p>
                @endif
                <button class="btn-primary btn-lg w-full" wire:loading.attr="disabled" wire:target="save">Simpan Soal</button>
                <button type="button" wire:click="save(true)" class="btn-outline w-full" wire:loading.attr="disabled" wire:target="save">Simpan & Buat Lagi</button>
            </div>
            <button type="button" @click="docsOpen = true" class="card flex w-full items-center gap-3 p-4 text-left hover:border-brand-200">
                <span class="inline-flex h-10 w-10 items-center justify-center rounded-xl bg-brand-50 text-brand-600"><x-icon name="book" /></span>
                <span><span class="block text-sm font-bold text-navy-900">Dokumentasi rumus</span><span class="text-xs text-slate-500">Cheat sheet MathQuill & LaTeX</span></span>
            </button>
        </aside>
    </form>

    {{-- ===== Modal editor rumus (MathQuill) ===== --}}
    <div wire:ignore>
        <div x-show="mqOpen" x-cloak class="fixed inset-0 z-[60] flex items-end justify-center sm:items-center sm:p-6" @keydown.escape.window="closeMath()">
            <div class="absolute inset-0 bg-navy-950/50" @click="closeMath()"></div>
            <div class="relative w-full rounded-t-3xl bg-white p-5 shadow-2xl sm:max-w-2xl sm:rounded-3xl sm:p-6">
                <div class="flex items-center justify-between">
                    <p class="flex items-center gap-2 font-bold text-navy-900"><x-icon name="bolt" class="h-5 w-5 text-brand-600" /> Editor Rumus</p>
                    <button type="button" @click="closeMath()" class="rounded-lg p-1 text-slate-400 hover:bg-slate-100"><x-icon name="x" /></button>
                </div>
                <div class="mt-4 flex flex-wrap gap-1.5">
                    @foreach([['a/b', "cmd('\\\\frac')"], ['√', "cmd('\\\\sqrt')"], ['ⁿ√', "write('\\\\sqrt[n]{}')"], ['x²', "cmd('^')"], ['xₙ', "cmd('_')"], ['( )', "cmd('(')"], ['|x|', "cmd('|')"], ['×', "cmd('\\\\times')"], ['÷', "cmd('\\\\div')"], ['±', "cmd('\\\\pm')"], ['≤', "cmd('\\\\le')"], ['≥', "cmd('\\\\ge')"], ['≠', "cmd('\\\\ne')"], ['π', "cmd('\\\\pi')"], ['°', "write('^{\\\\circ}')"], ['∑', "cmd('\\\\sum')"], ['∫', "cmd('\\\\int')"], ['log', "write('\\\\log')"], ['θ', "cmd('\\\\theta')"]] as [$l, $js])
                        <button type="button" @click="{{ $js }}" class="h-9 min-w-9 rounded-lg border border-slate-200 bg-slate-50 px-2 text-sm font-semibold text-navy-900 hover:border-brand-300 hover:bg-brand-50">{{ $l }}</button>
                    @endforeach
                </div>
                <div class="mt-3">
                    <span x-ref="mqField" class="block w-full"></span>
                    <p x-show="!mqReady" class="mt-2 text-xs text-slate-400">Memuat editor rumus…</p>
                </div>
                <div class="mt-4 flex flex-wrap items-center justify-between gap-3 border-t border-slate-100 pt-4">
                    <div class="flex items-center gap-4">
                        <label class="flex items-center gap-2 text-xs font-semibold text-slate-600"><input type="checkbox" x-model="displayMode" class="checkbox"> Baris sendiri (display)</label>
                        <button type="button" @click="docsOpen = true" class="text-xs font-semibold text-brand-700 underline">📘 Dokumentasi</button>
                    </div>
                    <div class="flex gap-2">
                        <button type="button" @click="closeMath()" class="btn-outline">Batal</button>
                        <button type="button" @click="insertMath()" class="btn-primary">Sisipkan</button>
                    </div>
                </div>
            </div>
        </div>

        {{-- Drawer dokumentasi --}}
        <div x-show="docsOpen" x-cloak class="fixed inset-0 z-[70]">
            <div class="absolute inset-0 bg-navy-950/30" @click="docsOpen = false"></div>
            <div class="absolute inset-y-0 right-0 w-full max-w-sm bg-white shadow-2xl" x-transition:enter="transition duration-200" x-transition:enter-start="translate-x-full" x-transition:enter-end="translate-x-0">
                @include('partials.math-docs')
            </div>
        </div>
    </div>
</div>
