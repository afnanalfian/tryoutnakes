@php($st = $order->status)
<div class="mx-auto max-w-5xl" x-data="{ copied: null, copy(text, key) { navigator.clipboard?.writeText(text); this.copied = key; setTimeout(() => this.copied = null, 1600) } }">
    <x-page-head :title="'Pesanan '.$order->code" :subtitle="'Dibuat '.$order->created_at->translatedFormat('d F Y, H:i')">
        <span class="badge bg-{{ $order->statusColor() }}-100 px-3 py-1 text-sm text-{{ $order->statusColor() }}-800">{{ $order->statusLabel() }}</span>
        <a href="{{ route('orders.invoice', $order) }}" target="_blank" class="btn-outline btn-sm"><x-icon name="printer" class="h-4 w-4" /> Invoice</a>
    </x-page-head>

    {{-- Linimasa status --}}
    @php($steps = [['Pesanan dibuat', true], ['Bukti diunggah', $order->proof_uploaded_at !== null], ['Diverifikasi', $st === 'paid']])
    <ol class="card mb-6 grid grid-cols-3 gap-2 p-4 text-center text-xs font-semibold sm:text-sm">
        @foreach($steps as $i => [$label, $done])
            <li class="flex flex-col items-center gap-2">
                <span @class(['inline-flex h-9 w-9 items-center justify-center rounded-full', 'bg-mint-600 text-white' => $done, 'bg-slate-100 text-slate-400' => ! $done])>
                    @if($done)<x-icon name="check" class="h-4 w-4" stroke="2.4" />@else{{ $i + 1 }}@endif
                </span>
                <span class="{{ $done ? 'text-navy-900' : 'text-slate-400' }}">{{ $label }}</span>
            </li>
        @endforeach
    </ol>

    @if($st === 'paid')
        <div class="overflow-hidden rounded-3xl bg-gradient-to-br from-mint-600 to-mint-500 p-6 text-white sm:p-8">
            <p class="font-display text-3xl font-semibold">Pembayaran terverifikasi 🎉</p>
            <p class="mt-2 text-mint-50">Diverifikasi {{ $order->paid_at?->translatedFormat('d F Y, H:i') }}. Semua tryout di pesanan ini sudah bisa kamu kerjakan.</p>
            <a href="{{ route('my-tryouts.index') }}" class="btn btn-lg mt-6 bg-white text-mint-800 hover:bg-mint-50">Mulai Tryout <x-icon name="arrow-right" class="h-4 w-4" /></a>
        </div>
        <div class="mt-6 grid gap-3 sm:grid-cols-2">
            @foreach($tryouts as $t)
                <a href="{{ route('my-tryouts.show', $t) }}" class="card flex items-center gap-3 p-4 hover:border-navy-200">
                    <span class="inline-flex h-10 w-10 items-center justify-center rounded-xl" style="background: {{ $t->category->color }}1a; color: {{ $t->category->color }}"><x-icon :name="$t->category->icon" /></span>
                    <span class="min-w-0 flex-1"><span class="block truncate font-semibold text-navy-900">{{ $t->title }}</span><span class="text-xs text-slate-500">{{ $t->category->label() }}</span></span>
                    <x-icon name="chevron-right" class="h-4 w-4 text-slate-400" />
                </a>
            @endforeach
        </div>
    @elseif(in_array($st, ['expired', 'cancelled']))
        <x-empty icon="x-circle" :title="$st === 'expired' ? 'Pesanan kedaluwarsa' : 'Pesanan dibatalkan'" text="Kamu bisa membuat pesanan baru kapan saja dari katalog.">
            <a href="{{ route('catalog.packages') }}" class="btn-primary">Lihat Katalog</a>
        </x-empty>
    @else
        <div class="grid gap-6 lg:grid-cols-[1fr_380px]">
            <div class="space-y-5">
                @if($st === 'rejected')
                    <div class="flex gap-3 rounded-2xl border border-rose-200 bg-rose-50 p-4 text-sm text-rose-900">
                        <x-icon name="warning" class="h-5 w-5 shrink-0" />
                        <div><p class="font-bold">Bukti pembayaran ditolak</p><p class="mt-0.5">{{ $order->admin_note }}</p><p class="mt-1 text-rose-700">Silakan unggah ulang bukti yang benar di bawah.</p></div>
                    </div>
                @elseif($st === 'waiting')
                    <div class="flex gap-3 rounded-2xl border border-amber-200 bg-amber-50 p-4 text-sm text-amber-900">
                        <x-icon name="clock" class="h-5 w-5 shrink-0" />
                        <div><p class="font-bold">Menunggu verifikasi admin</p><p class="mt-0.5">Bukti terkirim {{ $order->proof_uploaded_at->diffForHumans() }}. Persetujuan paling lambat <strong>{{ setting('verification_sla') }}</strong>. Ingin lebih cepat? Konfirmasi via WhatsApp.</p></div>
                    </div>
                @endif

                {{-- Total --}}
                <div class="card card-pad">
                    <p class="text-sm font-semibold text-slate-500">Total yang harus dibayar</p>
                    <div class="mt-1 flex flex-wrap items-center gap-3">
                        <p class="text-3xl font-extrabold tracking-tight text-navy-900 sm:text-4xl">{{ rupiah($order->total) }}</p>
                        <button type="button" @click="copy('{{ $order->total }}', 'total')" class="btn-outline btn-sm"><x-icon name="copy" class="h-3.5 w-3.5" /> <span x-text="copied === 'total' ? 'Tersalin!' : 'Salin nominal'"></span></button>
                    </div>
                    @if($st === 'pending' && $order->expires_at)
                        <p class="mt-2 text-sm text-slate-500">Bayar sebelum <strong class="text-navy-900">{{ $order->expires_at->translatedFormat('d M Y, H:i') }}</strong></p>
                    @endif
                    <div class="mt-4 divide-y divide-slate-100 rounded-xl bg-slate-50 px-4 text-sm">
                        @foreach($order->items as $item)
                            <div class="flex justify-between gap-3 py-2.5"><span class="text-slate-600">{{ $item->name }}</span><span class="font-semibold">{{ rupiah($item->price) }}</span></div>
                        @endforeach
                        @if($order->discount > 0)<div class="flex justify-between py-2.5 text-mint-700"><span>Hemat (konversi paket)</span><span class="font-semibold">−{{ rupiah($order->discount) }}</span></div>@endif
                    </div>
                </div>

                {{-- Metode pembayaran --}}
                <div class="card card-pad">
                    <p class="font-bold text-navy-900">Metode Pembayaran</p>
                    <div class="mt-4 grid gap-4 md:grid-cols-[220px_1fr]">
                        @if(setting('qris_image'))
                            <div class="rounded-2xl border border-slate-200 p-3 text-center">
                                <p class="text-xs font-bold uppercase tracking-widest text-navy-900">QRIS</p>
                                <img src="{{ media_url(setting('qris_image')) }}" alt="QRIS pembayaran" class="mx-auto mt-2 w-full max-w-[220px] rounded-lg" loading="lazy">
                                @if(setting('qris_name'))<p class="mt-2 text-xs font-semibold text-slate-600">a.n. {{ setting('qris_name') }}</p>@endif
                                <a href="{{ media_url(setting('qris_image')) }}" download class="btn-outline btn-sm mt-3 w-full"><x-icon name="download" class="h-3.5 w-3.5" /> Unduh QRIS</a>
                            </div>
                        @endif
                        <div class="space-y-3 {{ setting('qris_image') ? '' : 'md:col-span-2' }}">
                            @if(setting('qris_image'))
                                <p class="rounded-xl bg-brand-50 p-3 text-sm text-brand-900">{{ setting('qris_note') }}</p>
                            @endif
                            @foreach($methods as $m)
                                <div class="rounded-2xl border border-slate-200 p-4">
                                    <div class="flex items-start justify-between gap-3">
                                        <div class="min-w-0">
                                            <p class="font-bold text-navy-900">{{ $m->name }}</p>
                                            <p class="mt-0.5 font-mono text-lg font-semibold tracking-wide text-navy-900">{{ $m->account_number }}</p>
                                            @if($m->account_holder)<p class="text-xs text-slate-500">a.n. {{ $m->account_holder }}</p>@endif
                                        </div>
                                        <button type="button" @click="copy('{{ preg_replace('/\s+/', '', $m->account_number) }}', 'm{{ $m->id }}')" class="btn-outline btn-sm shrink-0"><x-icon name="copy" class="h-3.5 w-3.5" /> <span x-text="copied === 'm{{ $m->id }}' ? 'Tersalin' : 'Salin'"></span></button>
                                    </div>
                                    @if($m->description)<p class="mt-2 text-sm text-slate-600">{{ $m->description }}</p>@endif
                                </div>
                            @endforeach
                            @if($methods->isEmpty() && ! setting('qris_image'))
                                <p class="text-sm text-slate-500">Metode pembayaran belum diatur. Silakan hubungi admin via WhatsApp.</p>
                            @endif
                        </div>
                    </div>
                </div>
            </div>

            {{-- Upload bukti --}}
            <aside class="space-y-4 lg:sticky lg:top-24 lg:self-start">
                <form wire:submit="submitProof" class="card card-pad">
                    <p class="font-bold text-navy-900">{{ $order->proof_path ? 'Ganti Bukti Pembayaran' : 'Unggah Bukti Pembayaran' }}</p>
                    <label for="proof-input" class="mt-4 flex cursor-pointer flex-col items-center justify-center rounded-2xl border-2 border-dashed border-slate-300 bg-slate-50 px-4 py-6 text-center transition hover:border-brand-400 hover:bg-brand-50/40">
                        @if($proof && method_exists($proof, 'temporaryUrl'))
                            <img src="{{ $proof->temporaryUrl() }}" class="mb-3 max-h-48 rounded-lg object-contain" alt="Pratinjau bukti">
                        @else
                            <span class="inline-flex h-12 w-12 items-center justify-center rounded-2xl bg-white text-brand-600 shadow-sm"><x-icon name="upload" /></span>
                        @endif
                        <span class="mt-2 text-sm font-semibold text-navy-900">{{ $proof ? 'Ganti gambar' : 'Pilih foto / screenshot' }}</span>
                        <span class="text-xs text-slate-500">JPG, PNG, WebP · maks 4 MB</span>
                    </label>
                    <input id="proof-input" type="file" wire:model="proof" accept="image/*" class="sr-only">
                    <div wire:loading wire:target="proof" class="mt-2 text-xs font-medium text-brand-700">Mengunggah gambar…</div>
                    @error('proof')<p class="error">{{ $message }}</p>@enderror

                    <label class="label mt-4">Dibayar melalui</label>
                    <select wire:model="method" class="input">
                        <option value="">— Pilih metode —</option>
                        @if(setting('qris_image'))<option value="qris">QRIS</option>@endif
                        @foreach($methods as $m)<option value="{{ $m->id }}">{{ $m->name }}</option>@endforeach
                    </select>

                    <label class="label mt-4">Catatan (opsional)</label>
                    <input type="text" wire:model="note" class="input" maxlength="300" placeholder="mis. Transfer a.n. Budi">

                    <button type="submit" class="btn-brand btn-lg mt-5 w-full" wire:loading.attr="disabled" wire:target="submitProof">
                        <span wire:loading.remove wire:target="submitProof">Kirim Bukti Pembayaran</span>
                        <span wire:loading wire:target="submitProof">Mengirim…</span>
                    </button>
                    <p class="mt-3 text-center text-xs text-slate-500">Persetujuan paling lambat <strong>{{ setting('verification_sla') }}</strong>.</p>
                </form>

                @if($waLink)
                    <a href="{{ $waLink }}" target="_blank" rel="noopener" class="btn btn-lg w-full bg-[#25D366] text-white hover:bg-[#1fb257]"><x-icon name="whatsapp" /> Konfirmasi Cepat via WhatsApp</a>
                @endif

                @if(in_array($st, ['pending', 'rejected']))
                    <button type="button" wire:click="cancel" wire:confirm="Batalkan pesanan ini?" class="btn-ghost w-full text-rose-600 hover:bg-rose-50">Batalkan pesanan</button>
                @endif
            </aside>
        </div>
    @endif
</div>
