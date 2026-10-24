<div>
    <x-page-head title="Halo, {{ auth()->user()->firstName() }} 👋" subtitle="Ringkasan aktivitas {{ setting('site_name') }} hari ini.">
        <a href="{{ route('admin.questions.create') }}" wire:navigate class="btn-outline"><x-icon name="plus" class="h-4 w-4" /> Soal</a>
        <a href="{{ route('admin.tryouts.create') }}" wire:navigate class="btn-primary"><x-icon name="plus" class="h-4 w-4" /> Tryout</a>
    </x-page-head>

    @if($stats['waiting'])
        <a href="{{ route('admin.orders', ['status' => 'waiting']) }}" wire:navigate class="mb-6 flex items-center gap-4 rounded-2xl border border-amber-200 bg-amber-50 p-4 hover:bg-amber-100/60">
            <span class="inline-flex h-11 w-11 items-center justify-center rounded-xl bg-amber-400 text-amber-950"><x-icon name="bell" /></span>
            <p class="flex-1 text-sm text-amber-900"><strong>{{ $stats['waiting'] }} pembayaran</strong> menunggu verifikasi. Target maksimal {{ setting('verification_sla') }}.</p>
            <x-icon name="chevron-right" class="h-4 w-4 text-amber-700" />
        </a>
    @endif

    <div class="grid grid-cols-2 gap-3 lg:grid-cols-4">
        <x-stat label="Pendapatan bulan ini" :value="rupiah($stats['revenue_month'])" icon="banknotes" tone="mint" :hint="'Total: '.rupiah($stats['revenue_all'])" />
        <x-stat label="Menunggu verifikasi" :value="$stats['waiting']" icon="clock" tone="amber" :hint="$stats['pending'].' belum bayar'" />
        <x-stat label="Peserta" :value="number_format($stats['users'], 0, ',', '.')" icon="users" tone="brand" :hint="'+'.$stats['users_new'].' minggu ini'" />
        <x-stat label="Ujian hari ini" :value="$stats['attempts_today']" icon="clipboard" :hint="$stats['running'].' sedang berlangsung'" />
    </div>

    <div class="mt-6 grid gap-6 xl:grid-cols-[1.4fr_1fr]">
        <div class="card card-pad">
            <div class="flex items-center justify-between"><p class="font-bold text-navy-900">Pendapatan 14 hari (ribu Rp)</p></div>
            <x-line-chart class="mt-4" :labels="$chartLabels" :series="[['name' => 'Pendapatan', 'color' => '#1d55f0', 'area' => true, 'values' => $chartValues]]" />
        </div>
        <div class="card">
            <div class="flex items-center justify-between border-b border-slate-100 px-5 py-4">
                <p class="font-bold text-navy-900">Antrean verifikasi</p>
                <a href="{{ route('admin.orders') }}" wire:navigate class="text-sm font-semibold text-brand-700">Semua</a>
            </div>
            @forelse($waitingOrders as $o)
                <a href="{{ route('admin.orders.show', $o) }}" wire:navigate class="flex items-center justify-between gap-3 px-5 py-3 hover:bg-slate-50">
                    <span class="min-w-0"><span class="block truncate text-sm font-semibold text-navy-900">{{ $o->user->name }}</span><span class="font-mono text-xs text-slate-500">{{ $o->code }} · {{ $o->proof_uploaded_at?->diffForHumans() }}</span></span>
                    <span class="text-sm font-bold text-navy-900">{{ rupiah($o->total) }}</span>
                </a>
            @empty
                <p class="px-5 py-10 text-center text-sm text-slate-500">Tidak ada antrean. 🎉</p>
            @endforelse
        </div>
    </div>

    <div class="mt-6 grid gap-6 lg:grid-cols-2">
        <div class="card">
            <p class="border-b border-slate-100 px-5 py-4 font-bold text-navy-900">Tryout terpopuler</p>
            @forelse($topTryouts as $t)
                <div class="flex items-center justify-between gap-3 px-5 py-3 text-sm">
                    <span class="min-w-0 truncate font-medium text-navy-900">{{ $t->tryout?->title ?? '—' }}</span>
                    <span class="shrink-0 font-bold text-slate-600">{{ $t->total }}× dikerjakan</span>
                </div>
            @empty
                <p class="px-5 py-10 text-center text-sm text-slate-500">Belum ada pengerjaan.</p>
            @endforelse
        </div>
        <div class="card">
            <p class="border-b border-slate-100 px-5 py-4 font-bold text-navy-900">Pendaftar terbaru</p>
            @foreach($recentUsers as $u)
                <a href="{{ route('admin.users.show', $u) }}" wire:navigate class="flex items-center justify-between gap-3 px-5 py-3 text-sm hover:bg-slate-50">
                    <span class="min-w-0"><span class="block truncate font-medium text-navy-900">{{ $u->name }}</span><span class="text-xs text-slate-500">{{ $u->email }}</span></span>
                    <span class="shrink-0 text-xs text-slate-400">{{ $u->created_at->diffForHumans() }}</span>
                </a>
            @endforeach
        </div>
    </div>
    <p class="mt-6 text-xs text-slate-400">Bank soal: {{ number_format($stats['questions'], 0, ',', '.') }} soal · Tryout terbit: {{ $stats['tryouts'] }}</p>
</div>
