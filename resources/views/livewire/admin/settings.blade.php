<div>
    <x-page-head title="Pengaturan Situs" subtitle="Semua perubahan langsung berlaku (cache otomatis diperbarui)." />
    <div class="no-scrollbar -mx-4 mb-5 flex gap-2 overflow-x-auto px-4 sm:mx-0 sm:px-0">
        @foreach(['umum' => 'Umum & Brand', 'kontak' => 'Kontak & WhatsApp', 'pembayaran' => 'Pembayaran & Akun', 'seo' => 'SEO & Analitik', 'motivasi' => 'Kata Motivasi', 'email' => 'Email'] as $k => $l)
            <button type="button" wire:click="$set('tab', '{{ $k }}')" class="whitespace-nowrap rounded-full px-4 py-2 text-sm font-semibold {{ $tab === $k ? 'bg-navy-900 text-white' : 'bg-white text-slate-600 ring-1 ring-slate-200' }}">{{ $l }}</button>
        @endforeach
    </div>

    <form wire:submit="save" class="card card-pad">
        @if($tab === 'umum')
            <div class="grid gap-4 sm:grid-cols-2">
                <div><label class="label">Nama situs / brand</label><input wire:model="s.site_name" class="input">@error('s.site_name')<p class="error">{{ $message }}</p>@enderror</div>
                <div><label class="label">Tagline</label><input wire:model="s.site_tagline" class="input">@error('s.site_tagline')<p class="error">{{ $message }}</p>@enderror</div>
                <div class="sm:col-span-2">
                    <label class="label">Logo (opsional, PNG transparan)</label>
                    <div class="flex items-center gap-4">
                        @if($logo)<img src="{{ $logo->temporaryUrl() }}" class="h-12">@elseif(setting('logo'))<img src="{{ media_url(setting('logo')) }}" class="h-12"><button type="button" wire:click="removeLogo" class="btn-ghost btn-sm text-rose-600">Hapus</button>@else<x-logo />@endif
                        <input type="file" wire:model="logo" accept="image/*" class="input max-w-xs p-2">
                    </div>
                </div>
                <div class="sm:col-span-2"><label class="label">Teks berjalan (topbar) — satu baris per item</label><textarea wire:model="s.topbar_items" rows="5" class="input"></textarea></div>
                <div class="sm:col-span-2"><label class="label">Catatan penting / disclaimer</label><textarea wire:model="s.disclaimer" rows="3" class="input"></textarea></div>
            </div>
        @elseif($tab === 'kontak')
            <div class="grid gap-4 sm:grid-cols-2">
                <div><label class="label">Nomor WhatsApp admin (contact person)</label><input wire:model="s.contact_whatsapp" class="input" placeholder="081234567890">@error('s.contact_whatsapp')<p class="error">{{ $message }}</p>@enderror<p class="help">Dipakai untuk tombol konfirmasi pembayaran & bantuan.</p></div>
                <div><label class="label">Email kontak</label><input wire:model="s.contact_email" class="input">@error('s.contact_email')<p class="error">{{ $message }}</p>@enderror</div>
                <div><label class="label">Instagram</label><input wire:model="s.contact_instagram" class="input" placeholder="@akun"></div>
                <div><label class="label">TikTok</label><input wire:model="s.contact_tiktok" class="input" placeholder="@akun"></div>
                <div class="sm:col-span-2"><label class="label">Template pesan WA konfirmasi pembayaran</label><textarea wire:model="s.whatsapp_message" rows="3" class="input"></textarea><p class="help">Variabel: <code>{nama}</code>, <code>{kode}</code>, <code>{total}</code></p></div>
            </div>
        @elseif($tab === 'pembayaran')
            <div class="grid gap-4 sm:grid-cols-2">
                <div><label class="label">Batas waktu verifikasi (ditampilkan ke pembeli)</label><input wire:model="s.verification_sla" class="input" placeholder="1 × 3 jam">@error('s.verification_sla')<p class="error">{{ $message }}</p>@enderror</div>
                <div><label class="label">Pesanan kedaluwarsa jika belum dibayar (jam)</label><input type="number" min="1" wire:model="s.order_expiry_hours" class="input">@error('s.order_expiry_hours')<p class="error">{{ $message }}</p>@enderror</div>
                <label class="flex items-start gap-2 text-sm text-slate-600 sm:col-span-2">
                    <input type="checkbox" class="checkbox mt-0.5" @checked($s['require_email_verification'] === '1') wire:click="$set('s.require_email_verification', '{{ $s['require_email_verification'] === '1' ? '0' : '1' }}')">
                    <span><strong class="text-navy-900">Wajib verifikasi email</strong> sebelum checkout & mengerjakan tryout (mengurangi akun palsu, tetapi memakai kuota email).</span>
                </label>
                <p class="text-sm text-slate-500 sm:col-span-2">Metode pembayaran & QRIS diatur di menu <a href="{{ route('admin.payments') }}" class="font-semibold text-brand-700 underline" wire:navigate>Metode Pembayaran</a>. Harga satuan di <a href="{{ route('admin.categories') }}" class="font-semibold text-brand-700 underline" wire:navigate>Kategori & Harga</a>.</p>
            </div>
        @elseif($tab === 'seo')
            <div class="grid gap-4">
                <div><label class="label">Meta description utama (maks ±160 karakter)</label><textarea wire:model="s.site_description" rows="3" class="input"></textarea>@error('s.site_description')<p class="error">{{ $message }}</p>@enderror</div>
                <div><label class="label">Kata kunci utama (catatan internal)</label><input wire:model="s.site_keywords" class="input"></div>
                <div class="grid gap-4 sm:grid-cols-3">
                    <div><label class="label">Google Search Console (kode verifikasi)</label><input wire:model="s.google_site_verification" class="input" placeholder="isi content=..."></div>
                    <div><label class="label">Bing Webmaster (msvalidate.01)</label><input wire:model="s.bing_site_verification" class="input"></div>
                    <div><label class="label">Google Analytics 4 (G-XXXX)</label><input wire:model="s.ga_measurement_id" class="input">@error('s.ga_measurement_id')<p class="error">{{ $message }}</p>@enderror</div>
                </div>
                <p class="text-sm text-slate-500">Sitemap otomatis: <a href="{{ route('sitemap') }}" target="_blank" class="font-mono text-brand-700 underline">{{ route('sitemap') }}</a></p>
            </div>
        @elseif($tab === 'motivasi')
            <p class="mb-4 text-sm text-slate-500">Kata penyemangat yang muncul di halaman hasil sesuai persentase nilai peserta.</p>
            <div class="grid gap-4">
                @foreach($ranges as $key => $label)
                    <div><label class="label">{{ $label }}</label><textarea wire:model="s.{{ $key }}" rows="2" class="input"></textarea></div>
                @endforeach
            </div>
        @elseif($tab === 'email')
            <div class="space-y-4">
                <label class="flex items-start gap-2 text-sm text-slate-600">
                    <input type="checkbox" class="checkbox mt-0.5" @checked($s['mail_enabled'] === '1') wire:click="$set('s.mail_enabled', '{{ $s['mail_enabled'] === '1' ? '0' : '1' }}')">
                    <span><strong class="text-navy-900">Kirim salinan notifikasi ke email peserta</strong> (pesanan, verifikasi, hasil tryout, pengumuman).</span>
                </label>
                <div class="rounded-2xl bg-slate-50 p-4 text-sm text-slate-600">
                    <p>Mailer: <strong>{{ config('mail.default') }}</strong> · Host: <strong>{{ config('mail.mailers.smtp.host') }}</strong> · Pengirim: <strong>{{ config('mail.from.address') }}</strong></p>
                    <p class="mt-1">Kredensial SMTP Brevo diatur di file <code>.env</code> server (lihat docs/INSTALL-VPS.md).</p>
                </div>
                <button type="button" wire:click="sendTestMail" class="btn-outline"><x-icon name="mail" class="h-4 w-4" /> Kirim email tes ke {{ auth()->user()->email }}</button>
            </div>
        @endif
        <div class="mt-6 flex justify-end border-t border-slate-100 pt-5"><button class="btn-primary" wire:loading.attr="disabled">Simpan Pengaturan</button></div>
    </form>
</div>
