<x-layouts.app :title="'Hasil '.$tryout->title">
    @php
        $isSkd = $tryout->isSkd();
        $bd = $attempt->breakdown ?? [];
        $toneColor = ['gold' => '#d97706', 'emerald' => '#059669', 'teal' => '#0d9488', 'sky' => '#0284c7', 'amber' => '#d97706', 'rose' => '#e11d48'][$motivation['tone']] ?? '#1d55f0';
        $counts = $items->countBy('status');
    @endphp

    <a href="{{ route('my-tryouts.show', $tryout) }}" wire:navigate class="btn-ghost -ml-3 mb-3"><x-icon name="arrow-left" class="h-4 w-4" /> {{ $tryout->title }}</a>

    {{-- ===== Ringkasan nilai ===== --}}
    <div class="grid gap-6 lg:grid-cols-[1.25fr_1fr]">
        <div class="card overflow-hidden">
            <div class="flex flex-col items-center gap-6 p-6 sm:flex-row sm:p-8">
                <x-score-ring :pct="$attempt->percentage" :label="score_format($attempt->score)" :sub="'dari '.score_format($attempt->max_score)" :size="176" :color="$toneColor" />
                <div class="min-w-0 flex-1 text-center sm:text-left">
                    <p class="text-sm font-semibold text-slate-500">Percobaan #{{ $attempt->attempt_no }} · {{ $attempt->submitted_at->translatedFormat('d M Y, H:i') }}</p>
                    <p class="mt-1 text-4xl font-extrabold tracking-tight text-navy-900">{{ score_format($attempt->percentage) }}<span class="text-2xl text-slate-400">%</span></p>
                    @if($attempt->is_passed !== null)
                        <span class="badge mt-2 px-3 py-1 text-sm {{ $attempt->is_passed ? 'bg-mint-100 text-mint-800' : 'bg-rose-100 text-rose-700' }}">{{ $attempt->is_passed ? '✓ Memenuhi passing grade' : '✗ Belum memenuhi passing grade' }}</span>
                    @endif
                    <div class="mt-4 grid grid-cols-3 gap-2 text-center">
                        <div class="rounded-xl bg-mint-50 p-2"><p class="text-lg font-extrabold text-mint-700">{{ $attempt->correct_count }}</p><p class="text-[11px] text-slate-500">{{ $isSkd ? 'Benar/Maks' : 'Benar' }}</p></div>
                        <div class="rounded-xl bg-rose-50 p-2"><p class="text-lg font-extrabold text-rose-600">{{ $attempt->wrong_count }}</p><p class="text-[11px] text-slate-500">{{ $isSkd ? 'Salah/Kurang' : 'Salah' }}</p></div>
                        <div class="rounded-xl bg-slate-100 p-2"><p class="text-lg font-extrabold text-slate-600">{{ $attempt->empty_count }}</p><p class="text-[11px] text-slate-500">Kosong</p></div>
                    </div>
                </div>
            </div>
            {{-- Motivasi --}}
            <div class="border-t border-slate-100 p-5 sm:px-8" style="background: {{ $toneColor }}0f">
                <p class="flex items-center gap-2 text-sm font-bold" style="color: {{ $toneColor }}">{{ $motivation['emoji'] }} {{ $motivation['label'] }}</p>
                <p class="mt-1 text-[15px] leading-relaxed text-navy-900">{{ $motivation['message'] }}</p>
            </div>
        </div>

        <div class="space-y-4">
            {{-- Peringkat --}}
            <div class="card card-pad">
                <div class="flex items-center gap-4">
                    <span class="inline-flex h-14 w-14 shrink-0 items-center justify-center rounded-2xl bg-amber-50 text-amber-600"><x-icon name="trophy" class="h-7 w-7" /></span>
                    <div>
                        @if($rank['eligible'])
                            <p class="text-sm text-slate-500">Peringkatmu</p>
                            <p class="text-2xl font-extrabold text-navy-900">#{{ $rank['rank'] }} <span class="text-base font-semibold text-slate-400">dari {{ $rank['participants'] }} peserta</span></p>
                            @if(! $rank['is_first'])<p class="text-xs text-slate-500">Berdasarkan percobaan pertamamu.</p>@endif
                        @else
                            <p class="font-bold text-navy-900">Peringkat belum tersedia</p>
                            <p class="text-sm text-slate-500">Peringkat muncul setelah minimal {{ $rank['min'] }} peserta mengerjakan ({{ $rank['participants'] }}/{{ $rank['min'] }}).</p>
                        @endif
                    </div>
                </div>
            </div>

            <div class="card card-pad">
                <dl class="grid grid-cols-2 gap-3 text-sm">
                    <div><dt class="text-slate-500">Durasi</dt><dd class="font-bold text-navy-900">{{ $attempt->durationLabel() }}</dd></div>
                    <div><dt class="text-slate-500">Dikumpulkan</dt><dd class="font-bold text-navy-900">{{ $attempt->submit_reason === 'timeout' ? 'Otomatis (waktu habis)' : 'Manual' }}</dd></div>
                    @unless($isSkd)
                        <div><dt class="text-slate-500">Skala nilai</dt><dd class="font-bold text-navy-900">0 – {{ score_format($bd['scale'] ?? $attempt->max_score) }}</dd></div>
                        <div><dt class="text-slate-500">Poin per soal</dt><dd class="font-bold text-navy-900">{{ score_format($bd['point_per_question'] ?? 0) }}</dd></div>
                    @endunless
                </dl>
                <div class="mt-5 flex flex-wrap gap-2">
                    @if($canRetake)
                        <form method="POST" action="{{ route('exam.start', $tryout) }}">@csrf<button class="btn-primary"><x-icon name="refresh" class="h-4 w-4" /> Kerjakan Ulang</button></form>
                    @endif
                    <a href="{{ route('progress', ['kategori' => $tryout->category->slug]) }}" wire:navigate class="btn-outline"><x-icon name="trending" class="h-4 w-4" /> Lihat Progres</a>
                </div>
            </div>
        </div>
    </div>

    {{-- ===== Rincian subtes SKD ===== --}}
    @if($isSkd && !empty($bd['subtests']))
        <div class="mt-6 grid gap-4 md:grid-cols-3">
            @foreach(['twk' => 'Tes Wawasan Kebangsaan', 'tiu' => 'Tes Intelegensia Umum', 'tkp' => 'Tes Karakteristik Pribadi'] as $s => $label)
                @php($st = $bd['subtests'][$s])
                @php($pct = $st['max'] ? min(100, $st['score'] / $st['max'] * 100) : 0)
                @php($pgPct = $st['max'] ? min(100, $st['passing'] / $st['max'] * 100) : 0)
                <div class="card card-pad">
                    <div class="flex items-start justify-between">
                        <div><p class="text-xs font-bold uppercase tracking-wider text-slate-400">{{ strtoupper($s) }}</p><p class="text-sm font-semibold text-navy-900">{{ $label }}</p></div>
                        <span class="badge {{ $st['passed'] ? 'bg-mint-50 text-mint-700' : 'bg-rose-50 text-rose-600' }}">{{ $st['passed'] ? 'Lulus' : 'Belum' }}</span>
                    </div>
                    <p class="mt-3 text-3xl font-extrabold text-navy-900">{{ $st['score'] }} <span class="text-base font-semibold text-slate-400">/ {{ $st['max'] }}</span></p>
                    <div class="relative mt-3 h-2.5 rounded-full bg-slate-100">
                        <div class="h-full rounded-full {{ $st['passed'] ? 'bg-mint-500' : 'bg-rose-400' }}" style="width: {{ $pct }}%"></div>
                        <div class="absolute -top-1 h-[18px] w-0.5 bg-navy-900" style="left: {{ $pgPct }}%" title="Passing grade {{ $st['passing'] }}"></div>
                    </div>
                    <p class="mt-2 text-xs text-slate-500">Passing grade {{ $st['passing'] }} · {{ $s === 'tkp' ? 'Opsi terbaik '.$st['correct'] : 'Benar '.$st['correct'] }} · Kosong {{ $st['empty'] }}</p>
                </div>
            @endforeach
        </div>
    @endif

    {{-- Riwayat percobaan --}}
    @if($history->count() > 1)
        <div class="mt-6 card card-pad">
            <p class="font-bold text-navy-900">Perkembangan di tryout ini</p>
            <x-line-chart class="mt-3" :height="170" :labels="$history->map(fn ($h) => '#'.$h->attempt_no)->all()" :max="$isSkd ? 550 : ($attempt->max_score ?: 100)" :series="[['name' => 'Nilai', 'color' => '#1d55f0', 'area' => true, 'values' => $history->pluck('score')->all()]]" />
        </div>
    @endif

    {{-- ===== Pembahasan ===== --}}
    <section class="mt-8" x-data="{ filter: 'all', open: null }">
        <div class="flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">
            <h2 class="text-xl font-bold text-navy-900">Pembahasan</h2>
            <div class="no-scrollbar -mx-4 flex gap-2 overflow-x-auto px-4 sm:mx-0 sm:px-0">
                @foreach(['all' => ['Semua', $items->count()], 'correct' => [$isSkd ? 'Benar/Maks' : 'Benar', $counts['correct'] ?? 0], 'wrong' => [$isSkd ? 'Salah/Kurang' : 'Salah', $counts['wrong'] ?? 0], 'empty' => ['Kosong', $counts['empty'] ?? 0]] as $k => [$l, $n])
                    <button type="button" @click="filter = '{{ $k }}'" class="whitespace-nowrap rounded-full px-3.5 py-1.5 text-xs font-bold" :class="filter === '{{ $k }}' ? 'bg-navy-900 text-white' : 'bg-white text-slate-600 ring-1 ring-slate-200'">{{ $l }} · {{ $n }}</button>
                @endforeach
            </div>
        </div>

        @if(! $tryout->show_explanation)
            <p class="mt-4 rounded-2xl bg-slate-50 p-4 text-sm text-slate-600">Pembahasan untuk tryout ini tidak ditampilkan oleh admin.</p>
        @else
            {{-- Peta nomor --}}
            <div class="mt-4 flex flex-wrap gap-1.5">
                @foreach($items as $it)
                    <a href="#soal-{{ $it['no'] }}" class="inline-flex h-8 w-8 items-center justify-center rounded-lg text-[11px] font-bold {{ ['correct' => 'bg-mint-500 text-white', 'wrong' => 'bg-rose-500 text-white', 'empty' => 'bg-slate-200 text-slate-600'][$it['status']] }}">{{ $it['no'] }}</a>
                @endforeach
            </div>

            <div class="mt-5 space-y-4">
                @foreach($items as $it)
                    @php($q = $it['q'])
                    @php($tkp = $q->subtest === 'tkp')
                    @php($maxW = $tkp ? $q->options->max('weight') : null)
                    <article id="soal-{{ $it['no'] }}" x-show="filter === 'all' || filter === '{{ $it['status'] }}'" class="card scroll-mt-24 overflow-hidden">
                        <div class="flex items-center justify-between gap-3 border-b border-slate-100 px-4 py-3 sm:px-6">
                            <div class="flex items-center gap-2">
                                <span class="inline-flex h-8 min-w-8 items-center justify-center rounded-lg px-2 text-sm font-bold text-white {{ ['correct' => 'bg-mint-500', 'wrong' => 'bg-rose-500', 'empty' => 'bg-slate-400'][$it['status']] }}">{{ $it['no'] }}</span>
                                @if($isSkd)<span class="badge bg-brand-50 uppercase text-brand-700">{{ $q->subtest }}</span>@endif
                                @if($q->topic)<span class="hidden text-xs text-slate-500 sm:inline">{{ $q->topic->name }}</span>@endif
                            </div>
                            <span class="text-xs font-bold {{ ['correct' => 'text-mint-600', 'wrong' => 'text-rose-600', 'empty' => 'text-slate-500'][$it['status']] }}">
                                @if($tkp) Poin {{ (int) ($it['answer']?->score ?? 0) }}/{{ $maxW }} @else {{ ['correct' => 'Benar', 'wrong' => 'Salah', 'empty' => 'Tidak dijawab'][$it['status']] }} @endif
                            </span>
                        </div>
                        <div class="px-4 py-5 sm:px-6">
                            <div class="rich" data-math>{!! rich($q->question_text) !!}</div>
                            @if($q->image)<img src="{{ media_url($q->image) }}" alt="" class="mt-3 max-h-80 rounded-xl border border-slate-200" loading="lazy">@endif
                            <div class="mt-4 space-y-2">
                                @foreach($it['options'] as $k => $o)
                                    @php($mine = $it['answer']?->option_id === $o->id)
                                    @php($best = $tkp ? $o->weight === $maxW : $o->is_correct)
                                    <div @class([
                                        'flex items-start gap-3 rounded-xl border p-3 text-sm',
                                        'border-mint-300 bg-mint-50' => $best,
                                        'border-rose-300 bg-rose-50' => $mine && ! $best,
                                        'border-slate-200' => ! $best && ! $mine,
                                    ])>
                                        <span @class(['inline-flex h-7 w-7 shrink-0 items-center justify-center rounded-lg text-xs font-bold', 'bg-mint-500 text-white' => $best, 'bg-rose-500 text-white' => $mine && ! $best, 'bg-slate-100 text-slate-600' => ! $best && ! $mine])>{{ chr(65 + $k) }}</span>
                                        <div class="min-w-0 flex-1 pt-0.5">
                                            <div class="rich" data-math>{!! rich($o->option_text) !!}</div>
                                            @if($o->image)<img src="{{ media_url($o->image) }}" alt="" class="mt-2 max-h-40 rounded-lg" loading="lazy">@endif
                                        </div>
                                        <div class="flex shrink-0 flex-col items-end gap-1 text-[11px] font-bold">
                                            @if($tkp)<span class="rounded bg-white px-1.5 py-0.5 text-slate-600 ring-1 ring-slate-200">Bobot {{ $o->weight }}</span>@endif
                                            @if($mine)<span class="text-navy-700">Jawabanmu</span>@endif
                                            @if($best && ! $tkp)<span class="text-mint-700">Kunci</span>@endif
                                        </div>
                                    </div>
                                @endforeach
                            </div>
                            @if($q->explanation)
                                <div class="mt-4 rounded-2xl border border-brand-100 bg-brand-50/50 p-4">
                                    <p class="mb-1 flex items-center gap-1.5 text-xs font-bold uppercase tracking-wider text-brand-700"><x-icon name="bulb" class="h-4 w-4" /> Pembahasan</p>
                                    <div class="rich text-sm" data-math>{!! rich($q->explanation) !!}</div>
                                </div>
                            @endif
                        </div>
                    </article>
                @endforeach
            </div>
        @endif
    </section>
</x-layouts.app>
