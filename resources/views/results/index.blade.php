<x-layouts.app title="Riwayat & Hasil">
    <x-page-head title="Riwayat & Hasil" subtitle="Semua tryout yang pernah kamu kerjakan." />
    @if($attempts->isEmpty())
        <x-empty icon="document" title="Belum ada riwayat" text="Hasil tryout akan muncul di sini setelah kamu mengerjakan.">
            <a href="{{ route('my-tryouts.index') }}" class="btn-primary">Ke Tryout Saya</a>
        </x-empty>
    @else
        <div class="grid gap-3 md:grid-cols-2">
            @foreach($attempts as $a)
                @php($c = $a->tryout->category)
                <a href="{{ $a->isSubmitted() ? route('results.show', $a) : route('exam.show', $a) }}" class="card flex items-center gap-4 p-4 hover:border-navy-200">
                    <span class="inline-flex h-12 w-12 shrink-0 items-center justify-center rounded-2xl" style="background: {{ $c->color }}17; color: {{ $c->color }}"><x-icon :name="$c->icon" /></span>
                    <div class="min-w-0 flex-1">
                        <p class="truncate font-semibold text-navy-900">{{ $a->tryout->title }}</p>
                        <p class="text-xs text-slate-500">#{{ $a->attempt_no }} · {{ $a->started_at->translatedFormat('d M Y, H:i') }}</p>
                    </div>
                    @if($a->isSubmitted())
                        <div class="text-right">
                            <p class="text-lg font-extrabold text-navy-900">{{ score_format($a->score) }}</p>
                            @if($a->is_passed !== null)
                                <span class="badge {{ $a->is_passed ? 'bg-mint-50 text-mint-700' : 'bg-rose-50 text-rose-600' }}">{{ $a->is_passed ? 'Lulus PG' : 'Belum lulus' }}</span>
                            @else
                                <span class="text-xs text-slate-500">{{ score_format($a->percentage) }}%</span>
                            @endif
                        </div>
                    @else
                        <span class="badge bg-amber-100 text-amber-800">Berjalan</span>
                    @endif
                </a>
            @endforeach
        </div>
        <div class="mt-6">{{ $attempts->links() }}</div>
    @endif
</x-layouts.app>
