<div class="flex items-center justify-between">
    <p class="font-bold text-navy-900">Navigasi Soal</p>
    <p class="text-xs font-semibold text-slate-500"><span x-text="answeredCount"></span>/<span x-text="questions.length"></span> dijawab</p>
</div>
<div class="mt-3 flex flex-wrap gap-x-3 gap-y-1 text-[11px] text-slate-500">
    <span class="inline-flex items-center gap-1"><span class="h-3 w-3 rounded bg-navy-900"></span>Dijawab</span>
    <span class="inline-flex items-center gap-1"><span class="h-3 w-3 rounded bg-amber-400"></span>Ragu</span>
    <span class="inline-flex items-center gap-1"><span class="h-3 w-3 rounded border border-slate-300 bg-white"></span>Kosong</span>
</div>
<div class="mt-3 max-h-[50vh] overflow-y-auto p-1.5 scrollbar-thin lg:max-h-[calc(100vh-330px)]">
    @if($tryout->isSkd())
        @foreach(['twk' => 'TWK', 'tiu' => 'TIU', 'tkp' => 'TKP'] as $s => $label)
            <p class="mb-2 mt-3 text-[11px] font-bold uppercase tracking-wider text-slate-400 first:mt-0">{{ $label }}</p>
            <div class="grid grid-cols-6 gap-1.5 sm:grid-cols-8 lg:grid-cols-6">
                <template x-for="(item, i) in questions" :key="item.id">
                    <button type="button" x-show="item.subtest === '{{ $s }}'" @click="go(i)" class="h-9 rounded-lg border text-xs font-bold transition" :class="cellClass(i)" x-text="item.no"></button>
                </template>
            </div>
        @endforeach
    @else
        <div class="grid grid-cols-6 gap-1.5 sm:grid-cols-8 lg:grid-cols-6">
            <template x-for="(item, i) in questions" :key="item.id">
                <button type="button" @click="go(i)" class="h-9 rounded-lg border text-xs font-bold transition" :class="cellClass(i)" x-text="item.no"></button>
            </template>
        </div>
    @endif
</div>
<div class="mt-4 flex items-center gap-1.5 text-[11px] text-slate-500 md:hidden">
    <span class="h-2 w-2 rounded-full" :class="!online ? 'bg-rose-400' : ((saving || queue.length) ? 'bg-amber-400' : 'bg-mint-400')"></span><span x-text="saveLabel"></span>
</div>
<button type="button" @click="navOpen = false; confirmOpen = true" class="btn-brand mt-4 w-full">Selesai &amp; Kumpulkan</button>
