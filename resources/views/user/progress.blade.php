<x-layouts.app title="Progres">
    <x-page-head title="Progres Kemampuan" subtitle="Pantau perkembangan nilai dan akurasi per topik di setiap kategori." />
    @if(! $current)
        <x-empty icon="trending" title="Belum ada data progres" text="Kerjakan minimal satu tryout untuk melihat grafik perkembanganmu.">
            <a href="{{ route('my-tryouts.index') }}" class="btn-primary">Ke Tryout Saya</a>
        </x-empty>
    @else
        <div class="no-scrollbar -mx-4 mb-6 flex gap-2 overflow-x-auto px-4 sm:mx-0 sm:flex-wrap sm:px-0">
            @foreach($categories as $c)
                @php($on = $c->id === $current->id)
                <a href="{{ route('progress', ['kategori' => $c->slug]) }}" wire:navigate class="flex shrink-0 items-center gap-2 rounded-full px-4 py-2 text-sm font-semibold {{ $on ? 'bg-navy-900 text-white' : 'bg-white text-slate-600 ring-1 ring-slate-200' }}"><x-icon :name="$c->icon" class="h-4 w-4" /> {{ $c->label() }}</a>
            @endforeach
        </div>

        <div class="grid grid-cols-2 gap-3 lg:grid-cols-4">
            <x-stat label="Tryout dikerjakan" :value="$data['count']" icon="clipboard" />
            <x-stat label="Rata-rata" :value="$data['avg_pct'].'%'" icon="chart" tone="brand" />
            <x-stat label="Tertinggi" :value="score_format($data['best_pct']).'%'" icon="trophy" tone="amber" />
            <x-stat label="Tren" :value="$data['delta'] === null ? '–' : (($data['delta'] >= 0 ? '+' : '').$data['delta'].'%')" icon="trending" :tone="($data['delta'] ?? 0) >= 0 ? 'mint' : 'rose'" hint="Dari tryout pertama ke terakhir" />
        </div>

        <div class="mt-6 grid gap-6 xl:grid-cols-[1.5fr_1fr]">
            <div class="card card-pad">
                <div class="flex items-center justify-between">
                    <p class="font-bold text-navy-900">{{ $current->isSkd() ? 'Nilai total SKD' : 'Persentase benar' }}</p>
                    @if($data['pass_rate'] !== null)<span class="badge bg-mint-50 text-mint-700">Lulus PG {{ $data['pass_rate'] }}%</span>@endif
                </div>
                @php($labels = $data['points']->pluck('date')->all())
                <x-line-chart class="mt-4" :labels="$labels" :max="$current->isSkd() ? 550 : 100" :threshold="$current->isSkd() ? 311 : null"
                    :series="[['name' => $current->isSkd() ? 'Total' : '% Benar', 'color' => '#1d55f0', 'area' => true, 'values' => $current->isSkd() ? $data['points']->pluck('score')->all() : $data['points']->pluck('pct')->all()]]" :suffix="$current->isSkd() ? '' : '%'" />
                @if($current->isSkd())<p class="mt-2 text-xs text-slate-500">Garis putus-putus = jumlah ambang batas minimal (65 + 80 + 166 = 311).</p>@endif
            </div>

            @if($current->isSkd())
                <div class="card card-pad">
                    <p class="font-bold text-navy-900">Per subtes</p>
                    <x-line-chart class="mt-4" :labels="$labels" :max="225" :series="[
                        ['name' => 'TWK', 'color' => '#3374fb', 'values' => $data['points']->pluck('twk')->all()],
                        ['name' => 'TIU', 'color' => '#27ab93', 'values' => $data['points']->pluck('tiu')->all()],
                        ['name' => 'TKP', 'color' => '#f59e0b', 'values' => $data['points']->pluck('tkp')->all()],
                    ]" />
                    <div class="mt-4 grid grid-cols-3 gap-2 text-center">
                        @foreach($data['subtests'] as $s => $st)
                            <div class="rounded-xl bg-slate-50 p-2.5">
                                <p class="text-[11px] font-bold uppercase text-slate-500">{{ $s }}</p>
                                <p class="text-lg font-extrabold text-navy-900">{{ $st['avg'] ?? '–' }}</p>
                                <p class="text-[11px] {{ ($st['avg'] ?? 0) >= $st['passing'] ? 'text-mint-600' : 'text-rose-500' }}">PG {{ $st['passing'] }} · lulus {{ $st['pass_rate'] }}%</p>
                            </div>
                        @endforeach
                    </div>
                </div>
            @endif
        </div>

        <div class="mt-6 card card-pad">
            <div class="flex flex-col gap-1 sm:flex-row sm:items-end sm:justify-between">
                <div>
                    <p class="font-bold text-navy-900">Akurasi per topik</p>
                    <p class="text-sm text-slate-500">Diurutkan dari yang paling perlu ditingkatkan.</p>
                </div>
            </div>
            @if($data['topics']->isEmpty())
                <p class="mt-6 text-sm text-slate-500">Belum ada data topik.</p>
            @else
                <div class="mt-5 grid gap-x-8 gap-y-4 md:grid-cols-2">
                    @foreach($data['topics']->take(16) as $t)
                        @php($c = $t['accuracy'] >= 80 ? 'bg-mint-500' : ($t['accuracy'] >= 60 ? 'bg-brand-500' : ($t['accuracy'] >= 40 ? 'bg-amber-400' : 'bg-rose-500')))
                        <div>
                            <div class="flex items-center justify-between gap-3 text-sm">
                                <p class="min-w-0 truncate font-medium text-navy-900"><span class="mr-1.5 rounded bg-slate-100 px-1.5 py-0.5 text-[10px] font-bold uppercase text-slate-500">{{ $t['subtest'] }}</span>{{ $t['topic'] }}</p>
                                <p class="shrink-0 font-bold text-navy-900">{{ $t['accuracy'] }}% <span class="text-xs font-normal text-slate-400">({{ $t['total'] }} soal)</span></p>
                            </div>
                            <div class="mt-1.5 h-2 overflow-hidden rounded-full bg-slate-100"><div class="h-full rounded-full {{ $c }}" style="width: {{ max(2, $t['accuracy']) }}%"></div></div>
                        </div>
                    @endforeach
                </div>
            @endif
        </div>

        <div class="mt-6 card overflow-hidden">
            <p class="border-b border-slate-100 px-5 py-4 font-bold text-navy-900">Detail per tryout</p>
            <div class="overflow-x-auto">
                <table class="table">
                    <thead><tr><th>Tanggal</th><th>Tryout</th>@if($current->isSkd())<th>TWK</th><th>TIU</th><th>TKP</th>@endif<th>Nilai</th><th>%</th><th></th></tr></thead>
                    <tbody>
                        @foreach($data['points']->reverse() as $p)
                            <tr>
                                <td class="whitespace-nowrap">{{ $p['date'] }}</td><td class="min-w-[180px] font-medium text-navy-900">{{ $p['label'] }}</td>
                                @if($current->isSkd())<td>{{ $p['twk'] }}</td><td>{{ $p['tiu'] }}</td><td>{{ $p['tkp'] }}</td>@endif
                                <td class="font-bold">{{ score_format($p['score']) }}</td><td>{{ score_format($p['pct']) }}%</td>
                                <td><a href="{{ route('results.show', $p['id']) }}" class="text-sm font-semibold text-brand-700 hover:underline">Detail</a></td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>
    @endif
</x-layouts.app>
