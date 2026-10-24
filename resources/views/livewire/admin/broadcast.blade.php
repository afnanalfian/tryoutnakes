<div class="mx-auto max-w-3xl">
    <x-page-head title="Broadcast Notifikasi" subtitle="Kirim pengumuman ke lonceng notifikasi peserta, opsional juga via email." />
    <form wire:submit="send" class="card card-pad space-y-4">
        <div>
            <label class="label">Penerima</label>
            <select wire:model.live="audience" class="input">
                <option value="all">Semua peserta aktif</option>
                <option value="buyers">Peserta yang punya tryout</option>
                @foreach($categories as $c)<option value="category:{{ $c->id }}">Pemilik tryout {{ $c->label() }}</option>@endforeach
            </select>
            <p class="help">{{ number_format($recipients, 0, ',', '.') }} penerima.</p>
        </div>
        <div><label class="label">Judul</label><input wire:model="title" class="input" placeholder="mis. Tryout Akbar SKB Bidan dibuka!">@error('title')<p class="error">{{ $message }}</p>@enderror</div>
        <div><label class="label">Pesan</label><textarea wire:model="body" rows="5" class="input"></textarea>@error('body')<p class="error">{{ $message }}</p>@enderror</div>
        <div class="grid gap-4 sm:grid-cols-2">
            <div><label class="label">Tautan (opsional)</label><input wire:model="url" class="input" placeholder="{{ url('/paket') }}">@error('url')<p class="error">{{ $message }}</p>@enderror</div>
            <div><label class="label">Teks tombol</label><input wire:model="action" class="input" placeholder="Lihat Sekarang"></div>
        </div>
        <label class="flex items-start gap-2 text-sm text-slate-600"><input type="checkbox" wire:model="email" class="checkbox mt-0.5"> <span>Kirim juga lewat email. <span class="text-amber-700">Perhatikan kuota Brevo gratis (300 email/hari); email dikirim bertahap lewat antrean.</span></span></label>
        <div class="flex justify-end"><button class="btn-primary" wire:confirm="Kirim pengumuman ke {{ $recipients }} peserta?" wire:loading.attr="disabled"><x-icon name="megaphone" class="h-4 w-4" /> Kirim</button></div>
    </form>
</div>
