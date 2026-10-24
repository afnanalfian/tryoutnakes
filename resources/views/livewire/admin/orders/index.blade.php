<div>
    <x-page-head title="Pesanan" subtitle="Verifikasi bukti pembayaran. Antrean diurutkan dari yang paling lama menunggu." />
    <div class="no-scrollbar -mx-4 mb-4 flex gap-2 overflow-x-auto px-4 sm:mx-0 sm:px-0">
        @foreach(['waiting' => 'Menunggu Verifikasi', 'pending' => 'Belum Bayar', 'paid' => 'Lunas', 'rejected' => 'Ditolak', 'expired' => 'Kedaluwarsa', 'cancelled' => 'Dibatalkan', '' => 'Semua'] as $k => $l)
            <button wire:click="$set('status', '{{ $k }}')" class="flex shrink-0 items-center gap-2 rounded-full px-4 py-2 text-sm font-semibold {{ $status === $k ? 'bg-navy-900 text-white' : 'bg-white text-slate-600 ring-1 ring-slate-200' }}">{{ $l }}@if($k !== '' && ($counts[$k] ?? 0))<span class="rounded-full px-1.5 text-[11px] {{ $status === $k ? 'bg-white/20' : 'bg-slate-100' }}">{{ $counts[$k] }}</span>@endif</button>
        @endforeach
    </div>
    <div class="card mb-4 p-3"><div class="relative"><x-icon name="search" class="absolute left-3 top-1/2 h-4 w-4 -translate-y-1/2 text-slate-400" /><input wire:model.live.debounce.400ms="search" class="input pl-9" placeholder="Cari kode pesanan, nama, atau email"></div></div>
    <div class="card overflow-hidden">
        <div class="overflow-x-auto">
            <table class="table">
                <thead><tr><th>Kode</th><th>Pembeli</th><th>Item</th><th>Total</th><th>Status</th><th>Waktu</th><th></th></tr></thead>
                <tbody>
                    @foreach($orders as $o)
                        <tr wire:key="o-{{ $o->id }}">
                            <td class="font-mono text-xs font-bold">{{ $o->code }}</td>
                            <td><p class="font-semibold text-navy-900">{{ $o->user->name }}</p><p class="text-xs text-slate-500">{{ $o->user->email }}</p></td>
                            <td class="max-w-[260px] truncate text-xs">{{ $o->items->pluck('name')->implode(', ') }}</td>
                            <td class="whitespace-nowrap font-bold">{{ rupiah($o->total) }}</td>
                            <td><span class="badge bg-{{ $o->statusColor() }}-100 text-{{ $o->statusColor() }}-800">{{ $o->statusLabel() }}</span></td>
                            <td class="whitespace-nowrap text-xs text-slate-500">{{ ($o->proof_uploaded_at ?? $o->created_at)->diffForHumans() }}</td>
                            <td class="text-right"><a href="{{ route('admin.orders.show', $o) }}" wire:navigate class="btn-primary btn-sm">{{ $o->status === 'waiting' ? 'Verifikasi' : 'Detail' }}</a></td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
        @if($orders->isEmpty())<p class="p-10 text-center text-sm text-slate-500">Tidak ada pesanan.</p>@endif
    </div>
    <div class="mt-4">{{ $orders->links() }}</div>
</div>
