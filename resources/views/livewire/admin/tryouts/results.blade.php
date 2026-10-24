<div>
    <a href="{{ route('admin.tryouts') }}" wire:navigate class="btn-ghost -ml-3 mb-3"><x-icon name="arrow-left" class="h-4 w-4" /> Tryout</a>
    <x-page-head :title="'Hasil · '.$tryout->title" :subtitle="$running.' peserta sedang mengerjakan'">
        <label class="flex items-center gap-2 text-sm"><input type="checkbox" wire:model.live="firstOnly" class="checkbox"> Percobaan pertama saja (dasar peringkat)</label>
        <a href="{{ route('admin.tryouts.export', $tryout) }}" class="btn-outline"><x-icon name="download" class="h-4 w-4" /> CSV</a>
    </x-page-head>
    <div class="grid grid-cols-2 gap-3 lg:grid-cols-5">
        <x-stat label="Peserta" :value="$stats->n ?? 0" icon="users" />
        <x-stat label="Rata-rata" :value="score_format(round($stats->avg ?? 0, 2))" icon="chart" tone="brand" />
        <x-stat label="Tertinggi" :value="score_format($stats->max ?? 0)" icon="trophy" tone="amber" />
        <x-stat label="Terendah" :value="score_format($stats->min ?? 0)" icon="minus-circle" tone="rose" />
        <x-stat label="{{ $tryout->category->isSkd() ? 'Lulus PG' : 'Durasi rata2' }}" :value="$tryout->category->isSkd() ? (($stats->n ? round($stats->passed / $stats->n * 100) : 0).'%') : gmdate('H:i:s', (int) ($stats->dur ?? 0))" icon="check-circle" tone="mint" />
    </div>
    <div class="card mt-5 overflow-hidden">
        <div class="border-b border-slate-100 p-3"><input wire:model.live.debounce.400ms="search" class="input" placeholder="Cari peserta"></div>
        <div class="overflow-x-auto">
            <table class="table">
                <thead><tr><th>#</th><th>Peserta</th>@if($tryout->category->isSkd())<th>TWK</th><th>TIU</th><th>TKP</th>@endif<th>Nilai</th><th>%</th><th>Durasi</th><th>Status</th><th></th></tr></thead>
                <tbody>
                    @foreach($rows as $i => $a)
                        <tr>
                            <td class="font-bold text-slate-400">{{ $rows->firstItem() + $i }}</td>
                            <td><p class="font-semibold text-navy-900">{{ $a->user?->name }}</p><p class="text-xs text-slate-500">{{ $a->user?->email }} · #{{ $a->attempt_no }}</p></td>
                            @if($tryout->category->isSkd())<td>{{ $a->score_twk }}</td><td>{{ $a->score_tiu }}</td><td>{{ $a->score_tkp }}</td>@endif
                            <td class="font-extrabold text-navy-900">{{ score_format($a->score) }}</td>
                            <td>{{ score_format($a->percentage) }}</td>
                            <td class="font-mono text-xs">{{ $a->durationLabel() }}</td>
                            <td>@if($a->is_passed !== null)<span class="badge {{ $a->is_passed ? 'bg-mint-50 text-mint-700' : 'bg-rose-50 text-rose-600' }}">{{ $a->is_passed ? 'Lulus' : 'Tidak' }}</span>@endif</td>
                            <td><a href="{{ route('results.show', $a) }}" target="_blank" class="text-sm font-semibold text-brand-700">Lihat</a></td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
        @if($rows->isEmpty())<p class="p-10 text-center text-sm text-slate-500">Belum ada hasil.</p>@endif
    </div>
    <div class="mt-4">{{ $rows->links() }}</div>
</div>
