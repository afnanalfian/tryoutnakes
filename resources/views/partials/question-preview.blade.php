{{-- Pratinjau satu soal (admin) --}}
@if($q)
    @php($tkp = $q->subtest === 'tkp')
    <div data-math>
        <div class="mb-3 flex flex-wrap gap-2 text-xs">
            <span class="badge bg-brand-50 uppercase text-brand-700">{{ $q->subtest }}</span>
            @if($q->topic)<span class="badge bg-slate-100 text-slate-600">{{ $q->topic->name }}</span>@endif
            @if($q->difficulty)<span class="badge bg-slate-100 text-slate-600">{{ [1 => 'Mudah', 2 => 'Sedang', 3 => 'Sulit'][$q->difficulty] }}</span>@endif
        </div>
        <div class="rich">{!! rich($q->question_text) !!}</div>
        @if($q->image)<img src="{{ media_url($q->image) }}" class="mt-3 max-h-72 rounded-xl border" alt="">@endif
        <div class="mt-4 space-y-2">
            @foreach($q->options as $k => $o)
                <div @class(['flex items-start gap-3 rounded-xl border p-3 text-sm', 'border-mint-300 bg-mint-50' => ! $tkp && $o->is_correct, 'border-slate-200' => $tkp || ! $o->is_correct])>
                    <span class="inline-flex h-7 w-7 shrink-0 items-center justify-center rounded-lg bg-slate-100 text-xs font-bold">{{ chr(65 + $k) }}</span>
                    <div class="min-w-0 flex-1"><div class="rich">{!! rich($o->option_text) !!}</div>@if($o->image)<img src="{{ media_url($o->image) }}" class="mt-2 max-h-32 rounded" alt="">@endif</div>
                    @if($tkp)<span class="badge bg-navy-900 text-white">Bobot {{ $o->weight }}</span>@elseif($o->is_correct)<span class="badge bg-mint-500 text-white">Kunci</span>@endif
                </div>
            @endforeach
        </div>
        @if($q->explanation)
            <div class="mt-4 rounded-2xl bg-brand-50/60 p-4 text-sm"><p class="mb-1 text-xs font-bold uppercase tracking-wider text-brand-700">Pembahasan</p><div class="rich">{!! rich($q->explanation) !!}</div></div>
        @endif
    </div>
@endif
