<div>
    <a href="{{ route('admin.orders') }}" wire:navigate class="btn-ghost -ml-3 mb-3"><x-icon name="arrow-left" class="h-4 w-4" /> Pesanan</a>
    <x-page-head :title="'Pesanan '.$order->code" :subtitle="'Dibuat '.$order->created_at->translatedFormat('d F Y H:i')">
        <span class="badge bg-{{ $order->statusColor() }}-100 px-3 py-1 text-sm text-{{ $order->statusColor() }}-800">{{ $order->statusLabel() }}</span>
    </x-page-head>
    <div class="grid gap-6 xl:grid-cols-[1fr_420px]">
        <div class="space-y-6">
            <div class="card card-pad">
                <p class="font-bold text-navy-900">Bukti pembayaran</p>
                @if($order->proof_path)
                    <a href="{{ route('orders.proof', $order) }}" target="_blank" class="mt-4 block overflow-hidden rounded-2xl border border-slate-200 bg-slate-50">
                        <img src="{{ route('orders.proof', $order) }}" alt="Bukti pembayaran" class="mx-auto max-h-[560px] object-contain">
                    </a>
                    <p class="mt-2 text-xs text-slate-500">Diunggah {{ $order->proof_uploaded_at->translatedFormat('d M Y H:i') }} · Metode: {{ $order->payment_method_name ?? '-' }} @if($order->payer_note)· Catatan: “{{ $order->payer_note }}”@endif</p>
                @else
                    <p class="mt-4 rounded-2xl bg-slate-50 p-8 text-center text-sm text-slate-500">Pembeli belum mengunggah bukti pembayaran.</p>
                @endif
            </div>
            <div class="card card-pad">
                <p class="font-bold text-navy-900">Tryout yang akan diberikan ({{ $tryouts->count() }})</p>
                <ul class="mt-3 grid gap-2 sm:grid-cols-2">
                    @foreach($tryouts as $t)<li class="flex items-center gap-2 rounded-xl bg-slate-50 px-3 py-2 text-sm"><x-icon name="check" class="h-4 w-4 text-mint-600" /> {{ $t->title }}</li>@endforeach
                </ul>
            </div>
        </div>
        <aside class="space-y-4">
            <div class="card card-pad">
                <p class="text-sm text-slate-500">Total tagihan</p>
                <p class="text-3xl font-extrabold text-navy-900">{{ rupiah($order->total) }}</p>
                <div class="mt-4 space-y-1.5 text-sm">
                    @foreach($order->items as $i)<div class="flex justify-between gap-3"><span class="text-slate-600">{{ $i->name }}</span><span>{{ rupiah($i->price) }}</span></div>@endforeach
                    @if($order->discount)<div class="flex justify-between text-mint-700"><span>Konversi paket</span><span>−{{ rupiah($order->discount) }}</span></div>@endif
                    @if($order->convertedPackage)<p class="text-xs text-mint-700">Dikonversi otomatis dari tryout satuan → {{ $order->convertedPackage->name }}</p>@endif
                </div>
                <div class="mt-5 rounded-xl bg-slate-50 p-3 text-sm">
                    <p class="font-semibold text-navy-900">{{ $order->user->name }}</p>
                    <p class="text-slate-500">{{ $order->user->email }}</p>
                    @if($waLink)<a href="{{ $waLink }}" target="_blank" class="mt-1 inline-flex items-center gap-1.5 font-semibold text-mint-700"><x-icon name="whatsapp" class="h-4 w-4" /> Hubungi via WhatsApp</a>@endif
                </div>
            </div>

            @if(in_array($order->status, ['waiting', 'pending', 'rejected']))
                <div class="card card-pad space-y-3">
                    <p class="font-bold text-navy-900">Verifikasi</p>
                    <input wire:model="note" class="input" placeholder="Catatan internal (opsional)">
                    <button wire:click="approve" wire:confirm="Setujui pembayaran {{ rupiah($order->total) }} dari {{ $order->user->name }}?" class="btn-mint btn-lg w-full"><x-icon name="check-circle" class="h-5 w-5" /> Setujui & Aktifkan Akses</button>
                    <button @click="$dispatch('open-modal', 'reject')" class="btn-soft-danger w-full">Tolak Bukti</button>
                </div>
            @elseif($order->status === 'paid')
                <div class="rounded-2xl border border-mint-200 bg-mint-50 p-4 text-sm text-mint-900">Disetujui {{ $order->paid_at?->translatedFormat('d M Y H:i') }} oleh {{ $order->verifier?->name ?? 'sistem' }}. @if($order->admin_note)<br>Catatan: {{ $order->admin_note }}@endif</div>
            @endif
            @if($order->status === 'rejected' && $order->admin_note)
                <div class="rounded-2xl border border-rose-200 bg-rose-50 p-4 text-sm text-rose-900">Alasan penolakan: {{ $order->admin_note }}</div>
            @endif
        </aside>
    </div>
    <x-modal name="reject" title="Tolak bukti pembayaran" maxWidth="md">
        <form wire:submit="reject" class="space-y-4">
            <div><label class="label">Alasan (dikirim ke pembeli)</label><textarea wire:model="reason" rows="3" class="input" placeholder="mis. Nominal transfer tidak sesuai / bukti tidak terbaca"></textarea>@error('reason')<p class="error">{{ $message }}</p>@enderror</div>
            <div class="flex justify-end gap-2"><button type="button" @click="$dispatch('close-modal', 'reject')" class="btn-outline">Batal</button><button class="btn-danger">Tolak</button></div>
        </form>
    </x-modal>
</div>
