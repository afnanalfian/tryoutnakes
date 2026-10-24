<x-layouts.exam :title="$tryout->title">
<div x-data="exam(@js($config))" @keydown.window="key($event)" class="flex min-h-screen flex-col" :style="`--fs:${fontScale}`">

    {{-- ===== Header ===== --}}
    <header class="sticky top-0 z-30 bg-navy-950 text-white shadow-lg">
        <div class="mx-auto flex h-14 max-w-7xl items-center justify-between gap-3 px-3 sm:h-16 sm:px-6">
            <div class="min-w-0 flex-1">
                <p class="truncate text-sm font-bold sm:text-base">{{ $tryout->title }}</p>
                <p class="hidden truncate text-[11px] text-navy-300 sm:block">{{ $tryout->category->label() }} · {{ count($questions) }} soal</p>
            </div>
            <div class="flex items-center gap-2 rounded-2xl px-3 py-1.5 font-mono text-base font-bold tabular-nums sm:text-lg" :class="danger ? 'bg-rose-500 animate-pulse' : 'bg-white/10'">
                <x-icon name="clock" class="h-4 w-4" /> <span x-text="clock">--:--:--</span>
            </div>
            <div class="flex flex-1 items-center justify-end gap-1.5">
                <div class="mr-1 hidden items-center gap-1.5 text-[11px] text-navy-300 md:flex">
                    <span class="h-2 w-2 rounded-full" :class="!online ? 'bg-rose-400' : ((saving || queue.length) ? 'bg-amber-400 animate-pulse' : 'bg-mint-400')"></span>
                    <span x-text="saveLabel"></span>
                </div>
                <button type="button" @click="zoom(-0.1)" class="hidden h-9 w-9 items-center justify-center rounded-xl hover:bg-white/10 sm:inline-flex" title="Perkecil teks"><x-icon name="zoom-out" class="h-5 w-5" /></button>
                <button type="button" @click="zoom(0.1)" class="hidden h-9 w-9 items-center justify-center rounded-xl hover:bg-white/10 sm:inline-flex" title="Perbesar teks"><x-icon name="zoom-in" class="h-5 w-5" /></button>
                <button type="button" @click="fullscreen()" class="hidden h-9 w-9 items-center justify-center rounded-xl hover:bg-white/10 sm:inline-flex" title="Layar penuh"><x-icon name="expand" class="h-5 w-5" /></button>
                <button type="button" @click="navOpen = true" class="inline-flex h-9 items-center gap-1.5 rounded-xl bg-white/10 px-3 text-xs font-semibold lg:hidden"><x-icon name="grid" class="h-4 w-4" /> <span x-text="`${answeredCount}/${questions.length}`"></span></button>
            </div>
        </div>
        <div class="h-1 bg-white/10"><div class="h-full bg-gradient-to-r from-brand-400 to-mint-400 transition-all duration-300" :style="`width:${progress}%`"></div></div>
    </header>

    {{-- Banner offline --}}
    <div x-show="!online" x-cloak class="bg-rose-600 px-4 py-2 text-center text-xs font-semibold text-white">Koneksi terputus. Jawabanmu disimpan di perangkat dan akan dikirim otomatis saat online.</div>

    <div class="mx-auto flex w-full max-w-7xl flex-1 gap-6 px-3 py-4 sm:px-6 sm:py-6">
        {{-- ===== Soal ===== --}}
        <main class="min-w-0 flex-1 pb-24 lg:pb-6" x-ref="stage">
            @if($tryout->isSkd())
                <div class="no-scrollbar mb-4 flex gap-2 overflow-x-auto">
                    @foreach(['twk' => 'TWK', 'tiu' => 'TIU', 'tkp' => 'TKP'] as $s => $label)
                        <button type="button" @click="go(sectionStart('{{ $s }}'))" class="flex shrink-0 items-center gap-2 rounded-full px-4 py-1.5 text-xs font-bold transition"
                                :class="q.subtest === '{{ $s }}' ? 'bg-navy-900 text-white' : 'bg-white text-slate-600 ring-1 ring-slate-200'">
                            {{ $label }} <span class="font-medium opacity-70" x-text="sectionStats('{{ $s }}')"></span>
                        </button>
                    @endforeach
                </div>
            @endif

            @foreach($questions as $i => $q)
                <article x-show="current === {{ $i }}" @if($i) x-cloak @endif data-q="{{ $q['id'] }}" class="card overflow-hidden">
                    <div class="flex items-center justify-between gap-3 border-b border-slate-100 bg-slate-50/60 px-4 py-3 sm:px-6">
                        <div class="flex items-center gap-2.5">
                            <span class="inline-flex h-9 min-w-9 items-center justify-center rounded-xl bg-navy-900 px-2 text-sm font-bold text-white">{{ $q['no'] }}</span>
                            <span class="text-sm font-semibold text-slate-500">dari {{ count($questions) }}</span>
                            @if($tryout->isSkd())<span class="badge bg-brand-50 uppercase text-brand-700">{{ $q['subtest'] }}</span>@endif
                        </div>
                        <button type="button" @click="toggleFlag()" class="inline-flex items-center gap-1.5 rounded-xl px-3 py-1.5 text-xs font-bold transition"
                                :class="questions[{{ $i }}].flagged ? 'bg-amber-400 text-amber-950' : 'bg-white text-slate-600 ring-1 ring-slate-200 hover:ring-amber-300'">
                            <x-icon name="flag" class="h-4 w-4" /> <span x-text="questions[{{ $i }}].flagged ? 'Ditandai ragu' : 'Ragu-ragu'"></span>
                        </button>
                    </div>
                    <div class="px-4 py-5 sm:px-6 sm:py-6">
                        <div class="rich text-[calc(1rem*var(--fs,1))] sm:text-[calc(1.0625rem*var(--fs,1))]" data-math>{!! $q['text'] !!}</div>
                        @if($q['image'])
                            <img src="{{ $q['image'] }}" alt="Gambar soal {{ $q['no'] }}" class="mt-4 max-h-[420px] w-auto max-w-full rounded-xl border border-slate-200" loading="lazy">
                        @endif

                        <div class="mt-6 space-y-2.5" role="radiogroup">
                            @foreach($q['options'] as $o)
                                <button type="button" data-label="{{ $o['label'] }}" role="radio" :aria-checked="questions[{{ $i }}].answer === {{ $o['id'] }}"
                                        @click="choose({{ $o['id'] }})"
                                        class="group flex w-full items-start gap-3 rounded-2xl border-2 p-3 text-left transition sm:p-4"
                                        :class="questions[{{ $i }}].answer === {{ $o['id'] }} ? 'border-brand-500 bg-brand-50/70 shadow-sm' : 'border-slate-200 bg-white hover:border-navy-200 hover:bg-slate-50'">
                                    <span class="inline-flex h-8 w-8 shrink-0 items-center justify-center rounded-xl text-sm font-bold transition"
                                          :class="questions[{{ $i }}].answer === {{ $o['id'] }} ? 'bg-brand-600 text-white' : 'bg-slate-100 text-slate-600 group-hover:bg-navy-100'">{{ $o['label'] }}</span>
                                    <span class="min-w-0 flex-1 pt-1">
                                        <span class="rich block text-[calc(.95rem*var(--fs,1))]" data-math>{!! $o['text'] !!}</span>
                                        @if($o['image'])<img src="{{ $o['image'] }}" alt="Opsi {{ $o['label'] }}" class="mt-2 max-h-48 rounded-lg border border-slate-200" loading="lazy">@endif
                                    </span>
                                </button>
                            @endforeach
                        </div>
                        <div class="mt-4 flex justify-end" x-show="questions[{{ $i }}].answer" x-cloak>
                            <button type="button" @click="clearAnswer()" class="text-xs font-semibold text-slate-400 hover:text-rose-600">Hapus jawaban</button>
                        </div>
                    </div>
                </article>
            @endforeach

            {{-- Navigasi bawah desktop --}}
            <div class="mt-5 hidden items-center justify-between lg:flex">
                <button type="button" @click="prev()" :disabled="current === 0" class="btn-outline btn-lg"><x-icon name="chevron-left" class="h-4 w-4" /> Sebelumnya</button>
                <button type="button" x-show="current < questions.length - 1" @click="next()" class="btn-primary btn-lg">Selanjutnya <x-icon name="chevron-right" class="h-4 w-4" /></button>
                <button type="button" x-show="current === questions.length - 1" x-cloak @click="confirmOpen = true" class="btn-brand btn-lg">Selesai &amp; Kumpulkan <x-icon name="check" class="h-4 w-4" /></button>
            </div>
        </main>

        {{-- ===== Panel navigasi nomor (desktop) ===== --}}
        <aside class="hidden w-[300px] shrink-0 lg:block">
            <div class="card sticky top-24 p-4">
                @include('exam.partials.nav-grid')
            </div>
        </aside>
    </div>

    {{-- ===== Bar bawah mobile ===== --}}
    <div class="pb-safe fixed inset-x-0 bottom-0 z-30 border-t border-slate-200 bg-white/95 px-3 pt-2 backdrop-blur lg:hidden">
        <div class="mx-auto grid max-w-xl grid-cols-[1fr_auto_1fr] items-center gap-2">
            <button type="button" @click="prev()" :disabled="current === 0" class="btn-outline h-12"><x-icon name="chevron-left" class="h-4 w-4" /> Prev</button>
            <button type="button" @click="toggleFlag()" class="inline-flex h-12 w-12 items-center justify-center rounded-xl" :class="q.flagged ? 'bg-amber-400 text-amber-950' : 'bg-amber-50 text-amber-600'" aria-label="Ragu-ragu"><x-icon name="flag" /></button>
            <button type="button" x-show="current < questions.length - 1" @click="next()" class="btn-primary h-12">Next <x-icon name="chevron-right" class="h-4 w-4" /></button>
            <button type="button" x-show="current === questions.length - 1" x-cloak @click="confirmOpen = true" class="btn-brand h-12">Selesai</button>
        </div>
    </div>

    {{-- ===== Drawer navigasi mobile ===== --}}
    <div x-show="navOpen" x-cloak class="fixed inset-0 z-40 lg:hidden">
        <div class="absolute inset-0 bg-navy-950/50" @click="navOpen = false" x-transition.opacity></div>
        <div class="absolute inset-x-0 bottom-0 max-h-[82vh] overflow-y-auto rounded-t-3xl bg-white p-5 pb-8" x-transition:enter="transition duration-200" x-transition:enter-start="translate-y-full" x-transition:enter-end="translate-y-0">
            <div class="mx-auto mb-4 h-1.5 w-12 rounded-full bg-slate-200"></div>
            @include('exam.partials.nav-grid')
        </div>
    </div>

    {{-- ===== Konfirmasi selesai ===== --}}
    <div x-show="confirmOpen" x-cloak class="fixed inset-0 z-50 flex items-end justify-center sm:items-center sm:p-6">
        <div class="absolute inset-0 bg-navy-950/60" @click="confirmOpen = false"></div>
        <div class="relative w-full rounded-t-3xl bg-white p-6 shadow-2xl sm:max-w-md sm:rounded-3xl" x-transition>
            <span class="inline-flex h-12 w-12 items-center justify-center rounded-2xl bg-brand-50 text-brand-600"><x-icon name="check-circle" class="h-6 w-6" /></span>
            <h3 class="mt-4 text-xl font-bold text-navy-900">Kumpulkan jawaban?</h3>
            <p class="mt-1 text-sm text-slate-500">Setelah dikumpulkan, jawaban tidak bisa diubah lagi.</p>
            <div class="mt-5 grid grid-cols-3 gap-2 text-center">
                <div class="rounded-2xl bg-navy-50 p-3"><p class="text-2xl font-extrabold text-navy-900" x-text="answeredCount"></p><p class="text-[11px] font-semibold text-slate-500">Dijawab</p></div>
                <div class="rounded-2xl bg-amber-50 p-3"><p class="text-2xl font-extrabold text-amber-700" x-text="flaggedCount"></p><p class="text-[11px] font-semibold text-slate-500">Ragu-ragu</p></div>
                <div class="rounded-2xl bg-rose-50 p-3"><p class="text-2xl font-extrabold text-rose-600" x-text="emptyCount"></p><p class="text-[11px] font-semibold text-slate-500">Kosong</p></div>
            </div>
            <p x-show="emptyCount > 0" class="mt-4 rounded-xl bg-rose-50 p-3 text-xs font-medium text-rose-700">Masih ada <span x-text="emptyCount"></span> soal kosong. Sisa waktu <span x-text="clock"></span>.</p>
            <div class="mt-6 grid grid-cols-2 gap-2">
                <button type="button" @click="confirmOpen = false" class="btn-outline btn-lg">Periksa Lagi</button>
                <button type="button" @click="submit()" :disabled="submitting" class="btn-brand btn-lg"><span x-text="submitting ? 'Mengumpulkan…' : 'Ya, Kumpulkan'"></span></button>
            </div>
        </div>
    </div>

    {{-- Overlay saat mengumpulkan --}}
    <div x-show="submitting" x-cloak class="fixed inset-0 z-[60] flex flex-col items-center justify-center bg-white/90 backdrop-blur">
        <svg class="h-10 w-10 animate-spin text-brand-600" viewBox="0 0 24 24" fill="none"><circle cx="12" cy="12" r="9" stroke="currentColor" stroke-width="3" opacity=".2"/><path d="M21 12a9 9 0 0 0-9-9" stroke="currentColor" stroke-width="3" stroke-linecap="round"/></svg>
        <p class="mt-4 font-semibold text-navy-900">Menghitung nilaimu…</p>
    </div>

    {{-- Toast --}}
    <div x-show="toast" x-cloak x-transition class="fixed inset-x-3 top-20 z-[55] mx-auto max-w-md rounded-2xl bg-navy-900 px-4 py-3 text-center text-sm font-semibold text-white shadow-lift" x-text="toast"></div>
</div>
</x-layouts.exam>
