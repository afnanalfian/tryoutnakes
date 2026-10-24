<div>
    <a href="{{ route('admin.users') }}" wire:navigate class="btn-ghost -ml-3 mb-3"><x-icon name="arrow-left" class="h-4 w-4" /> Pengguna</a>
    <div class="grid gap-6 xl:grid-cols-[320px_1fr]">
        <div class="card card-pad h-fit">
            <span class="inline-flex h-14 w-14 items-center justify-center rounded-2xl bg-gradient-to-br from-brand-500 to-navy-800 text-lg font-bold text-white">{{ $user->initials() }}</span>
            <p class="mt-3 text-lg font-bold text-navy-900">{{ $user->name }}</p>
            <p class="text-sm text-slate-500">{{ $user->email }}</p>
            @if($user->phone)<a href="{{ wa_link($user->phone) }}" target="_blank" class="mt-1 inline-flex items-center gap-1.5 text-sm font-semibold text-mint-700"><x-icon name="whatsapp" class="h-4 w-4" /> +{{ $user->phone }}</a>@endif
            <dl class="mt-5 space-y-2 text-sm">
                <div class="flex justify-between"><dt class="text-slate-500">Peran</dt><dd class="font-semibold">{{ $user->isAdmin() ? 'Admin' : 'Peserta' }}</dd></div>
                <div class="flex justify-between"><dt class="text-slate-500">Status</dt><dd class="font-semibold">{{ $user->is_active ? 'Aktif' : 'Nonaktif' }}</dd></div>
                <div class="flex justify-between"><dt class="text-slate-500">Email terverifikasi</dt><dd class="font-semibold">{{ $user->email_verified_at ? 'Ya' : 'Belum' }}</dd></div>
                <div class="flex justify-between"><dt class="text-slate-500">Terdaftar</dt><dd class="font-semibold">{{ $user->created_at->format('d/m/Y') }}</dd></div>
                <div class="flex justify-between"><dt class="text-slate-500">Login terakhir</dt><dd class="font-semibold">{{ $user->last_login_at?->diffForHumans() ?? '–' }}</dd></div>
            </dl>
            <button @click="$dispatch('open-modal', 'grant')" class="btn-primary mt-5 w-full"><x-icon name="sparkles" class="h-4 w-4" /> Beri Akses Tryout</button>
        </div>

        <div class="space-y-6">
            <div class="card">
                <p class="border-b border-slate-100 px-5 py-4 font-bold text-navy-900">Akses tryout ({{ $entitlements->count() }})</p>
                <div class="divide-y divide-slate-100">
                    @forelse($entitlements as $e)
                        <div class="flex items-center gap-3 px-5 py-3 text-sm">
                            <div class="min-w-0 flex-1"><p class="truncate font-semibold text-navy-900">{{ $e->tryout?->title ?? '(tryout dihapus)' }}</p><p class="text-xs text-slate-500">{{ $e->tryout?->category?->label() }} · {{ $e->source === 'grant' ? 'Diberikan admin' : 'Pembelian '.($e->order?->code ?? '') }} · {{ $e->created_at->format('d/m/Y') }}</p></div>
                            <button wire:click="revoke({{ $e->id }})" wire:confirm="Cabut akses tryout ini?" class="btn-soft-danger btn-sm">Cabut</button>
                        </div>
                    @empty
                        <p class="px-5 py-8 text-center text-sm text-slate-500">Belum memiliki tryout.</p>
                    @endforelse
                </div>
            </div>
            <div class="grid gap-6 lg:grid-cols-2">
                <div class="card">
                    <p class="border-b border-slate-100 px-5 py-4 font-bold text-navy-900">Pengerjaan terakhir</p>
                    @forelse($attempts as $a)
                        <a href="{{ $a->isSubmitted() ? route('results.show', $a) : '#' }}" class="flex items-center justify-between gap-3 px-5 py-2.5 text-sm hover:bg-slate-50">
                            <span class="min-w-0 truncate">{{ $a->tryout?->title }} <span class="text-xs text-slate-400">#{{ $a->attempt_no }}</span></span>
                            <span class="shrink-0 font-bold">{{ $a->isSubmitted() ? score_format($a->score) : 'berjalan' }}</span>
                        </a>
                    @empty
                        <p class="px-5 py-8 text-center text-sm text-slate-500">Belum ada.</p>
                    @endforelse
                </div>
                <div class="card">
                    <p class="border-b border-slate-100 px-5 py-4 font-bold text-navy-900">Pesanan</p>
                    @forelse($orders as $o)
                        <a href="{{ route('admin.orders.show', $o) }}" wire:navigate class="flex items-center justify-between gap-3 px-5 py-2.5 text-sm hover:bg-slate-50">
                            <span><span class="font-mono font-semibold">{{ $o->code }}</span> <span class="badge bg-{{ $o->statusColor() }}-100 text-{{ $o->statusColor() }}-800">{{ $o->statusLabel() }}</span></span>
                            <span class="font-bold">{{ rupiah($o->total) }}</span>
                        </a>
                    @empty
                        <p class="px-5 py-8 text-center text-sm text-slate-500">Belum ada.</p>
                    @endforelse
                </div>
            </div>
        </div>
    </div>

    <x-modal name="grant" title="Beri akses tryout" maxWidth="2xl">
        <form wire:submit="grant" class="space-y-4">
            <div><label class="label">Dari paket (opsional)</label><select wire:model="grantPackage" class="input"><option value="">— Tidak —</option>@foreach($packages as $p)<option value="{{ $p->id }}">{{ $p->name }}</option>@endforeach</select></div>
            <div>
                <label class="label">Atau pilih tryout</label>
                <div class="max-h-72 space-y-1 overflow-y-auto rounded-xl border border-slate-200 p-2">
                    @foreach($tryouts->groupBy(fn ($t) => $t->category->label()) as $cat => $list)
                        <p class="px-2 pb-1 pt-2 text-[11px] font-bold uppercase tracking-wider text-slate-400">{{ $cat }}</p>
                        @foreach($list as $t)
                            <label class="flex items-center gap-2 rounded-lg px-2 py-1.5 text-sm hover:bg-slate-50"><input type="checkbox" value="{{ $t->id }}" wire:model="grantTryouts" class="checkbox"> {{ $t->title }}</label>
                        @endforeach
                    @endforeach
                </div>
                @error('grantTryouts')<p class="error">{{ $message }}</p>@enderror
            </div>
            <label class="flex items-center gap-2 text-sm"><input type="checkbox" wire:model="notify" class="checkbox"> Kirim notifikasi ke peserta</label>
            <div class="flex justify-end gap-2"><button type="button" @click="$dispatch('close-modal', 'grant')" class="btn-outline">Batal</button><button class="btn-primary">Berikan Akses</button></div>
        </form>
    </x-modal>
</div>
