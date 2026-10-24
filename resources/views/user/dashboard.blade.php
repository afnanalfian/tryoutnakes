<x-layouts.app title="Dashboard">
    @php($hour = now()->hour)
    @php($greet = $hour < 11 ? 'Selamat pagi' : ($hour < 15 ? 'Selamat siang' : ($hour < 19 ? 'Selamat sore' : 'Selamat malam')))

    <div class="relative overflow-hidden rounded-3xl bg-gradient-to-br from-navy-900 via-navy-800 to-brand-800 p-6 text-white sm:p-8">
        <div class="pointer-events-none absolute -right-16 -top-16 h-64 w-64 rounded-full bg-brand-500/30 blur-3xl"></div>
        <div class="relative flex flex-col gap-6 lg:flex-row lg:items-center lg:justify-between">
            <div>
                <p class="text-sm text-navy-200">{{ $greet }},</p>
                <h1 class="font-display text-3xl font-semibold sm:text-4xl">{{ $user->firstName() }} 👋</h1>
                <p class="mt-2 max-w-lg text-sm text-navy-200">Konsistensi kecil setiap hari mengalahkan belajar kebut semalam. Yuk lanjutkan latihanmu.</p>
            </div>
            <div class="grid grid-cols-3 gap-3 sm:gap-4">
                @foreach([['Tryout dimiliki', $stats['owned']], ['Dikerjakan', $stats['attempts']], ['Rata-rata', $stats['attempts'] ? $stats['avg'].'%' : '–']] as [$l, $v])
                    <div class="rounded-2xl bg-white/10 px-4 py-3 backdrop-blur"><p class="text-[11px] text-navy-200">{{ $l }}</p><p class="mt-0.5 text-2xl font-extrabold">{{ $v }}</p></div>
                @endforeach
            </div>
        </div>
    </div>

    @if($active)
        <div class="mt-5 flex flex-col gap-4 rounded-3xl border-2 border-amber-300 bg-amber-50 p-5 sm:flex-row sm:items-center sm:justify-between">
            <div class="flex items-center gap-4">
                <span class="inline-flex h-12 w-12 shrink-0 items-center justify-center rounded-2xl bg-amber-500 text-white"><x-icon name="play" /></span>
                <div>
                    <p class="font-bold text-amber-950">Kamu punya tryout yang belum selesai</p>
                    <p class="text-sm text-amber-800">{{ $active->tryout->title }} · sisa waktu ±{{ (int) ceil($active->remainingSeconds() / 60) }} menit</p>
                </div>
            </div>
            <a href="{{ route('exam.show', $active) }}" class="btn btn-lg bg-amber-500 text-white hover:bg-amber-600">Lanjutkan Sekarang <x-icon name="arrow-right" class="h-4 w-4" /></a>
        </div>
    @endif

    @if($openOrder)
        <a href="{{ route('orders.show', $openOrder) }}" class="mt-5 flex items-center gap-4 rounded-2xl border border-sky-200 bg-sky-50 p-4 hover:bg-sky-100/70">
            <span class="inline-flex h-10 w-10 shrink-0 items-center justify-center rounded-xl bg-sky-500 text-white"><x-icon name="card" /></span>
            <p class="flex-1 text-sm text-sky-900"><strong>Pesanan {{ $openOrder->code }}</strong> {{ $openOrder->status === 'rejected' ? 'perlu unggah ulang bukti pembayaran.' : 'menunggu pembayaranmu ('.rupiah($openOrder->total).').' }}</p>
            <x-icon name="chevron-right" class="h-4 w-4 text-sky-700" />
        </a>
    @endif

    <div class="mt-8 grid gap-6 xl:grid-cols-[1.4fr_1fr]">
        <section>
            <div class="mb-4 flex items-center justify-between">
                <h2 class="text-lg font-bold text-navy-900">Siap dikerjakan</h2>
                <a href="{{ route('my-tryouts.index') }}" wire:navigate class="text-sm font-semibold text-brand-700 hover:underline">Semua tryout</a>
            </div>
            @if($todo->isEmpty())
                <x-empty icon="clipboard" title="{{ $stats['owned'] ? 'Semua tryout sudah kamu kerjakan 🎉' : 'Belum ada tryout' }}" text="{{ $stats['owned'] ? 'Ulangi tryout untuk memperbaiki nilai, atau tambah paket baru.' : 'Pilih paket atau tryout satuan untuk mulai berlatih.' }}">
                    <a href="{{ route('catalog.packages') }}" class="btn-primary">Lihat Paket</a>
                </x-empty>
            @else
                <div class="grid gap-4 sm:grid-cols-2">
                    @foreach($todo as $t)
                        <x-tryout-card :tryout="$t" :href="route('my-tryouts.show', $t)">
                            <a href="{{ route('my-tryouts.show', $t) }}" class="btn-primary w-full">Mulai <x-icon name="arrow-right" class="h-4 w-4" /></a>
                        </x-tryout-card>
                    @endforeach
                </div>
            @endif
        </section>

        <section class="space-y-6">
            <div class="card">
                <div class="flex items-center justify-between border-b border-slate-100 px-5 py-4">
                    <h2 class="font-bold text-navy-900">Hasil terakhir</h2>
                    <a href="{{ route('results.index') }}" wire:navigate class="text-sm font-semibold text-brand-700 hover:underline">Riwayat</a>
                </div>
                @forelse($recent as $a)
                    <a href="{{ route('results.show', $a) }}" class="flex items-center gap-3 px-5 py-3.5 hover:bg-slate-50">
                        <span class="inline-flex h-10 w-10 shrink-0 items-center justify-center rounded-xl" style="background: {{ $a->tryout->category->color }}17; color: {{ $a->tryout->category->color }}"><x-icon :name="$a->tryout->category->icon" /></span>
                        <span class="min-w-0 flex-1">
                            <span class="block truncate text-sm font-semibold text-navy-900">{{ $a->tryout->title }}</span>
                            <span class="text-xs text-slate-500">{{ $a->submitted_at->diffForHumans() }}</span>
                        </span>
                        <span class="text-right">
                            <span class="block font-extrabold text-navy-900">{{ score_format($a->score) }}</span>
                            @if($a->is_passed !== null)<span class="text-[11px] font-semibold {{ $a->is_passed ? 'text-mint-600' : 'text-rose-500' }}">{{ $a->is_passed ? 'Lulus PG' : 'Belum lulus' }}</span>@else<span class="text-[11px] text-slate-500">{{ score_format($a->percentage) }}%</span>@endif
                        </span>
                    </a>
                @empty
                    <p class="px-5 py-8 text-center text-sm text-slate-500">Belum ada hasil. Kerjakan tryout pertamamu!</p>
                @endforelse
            </div>

            @if($progressCats->isNotEmpty())
                <a href="{{ route('progress') }}" wire:navigate class="card group flex items-center gap-4 p-5 hover:border-navy-200">
                    <span class="inline-flex h-12 w-12 items-center justify-center rounded-2xl bg-mint-50 text-mint-600"><x-icon name="trending" /></span>
                    <span class="flex-1"><span class="block font-bold text-navy-900">Lihat progresmu</span><span class="text-sm text-slate-500">Grafik nilai & akurasi per topik di {{ $progressCats->count() }} kategori.</span></span>
                    <x-icon name="arrow-right" class="h-4 w-4 text-slate-400 transition group-hover:translate-x-1" />
                </a>
            @endif
        </section>
    </div>
</x-layouts.app>
