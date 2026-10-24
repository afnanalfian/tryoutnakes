@php($cats = \App\Support\PublicCache::remember('footer:categories', 3600, fn () => \App\Models\Category::query()->active()->ordered()->get(['id', 'name', 'slug', 'type'])))
<footer class="relative overflow-hidden bg-navy-950 text-navy-100">
    <div class="pointer-events-none absolute -right-24 -top-24 h-80 w-80 rounded-full bg-brand-600/20 blur-3xl"></div>
    <div class="mx-auto grid max-w-7xl gap-10 px-4 pb-10 pt-14 sm:px-6 md:grid-cols-12 lg:px-8">
        <div class="md:col-span-4">
            <x-logo dark />
            <p class="mt-4 max-w-sm text-sm leading-relaxed text-navy-200">{{ setting('site_tagline') }}. Latihan dengan sistem CAT, pembahasan lengkap, dan pantau progresmu sampai hari H.</p>
            <div class="mt-5 flex gap-2">
                @if($wa = wa_link(setting('contact_whatsapp'), 'Halo Admin '.setting('site_name').', saya mau bertanya.'))
                    <a href="{{ $wa }}" target="_blank" rel="noopener" class="inline-flex h-10 w-10 items-center justify-center rounded-xl bg-white/10 hover:bg-white/20" aria-label="WhatsApp"><x-icon name="whatsapp" /></a>
                @endif
                @if(setting('contact_instagram'))
                    <a href="https://instagram.com/{{ ltrim(setting('contact_instagram'), '@') }}" target="_blank" rel="noopener" class="inline-flex h-10 w-10 items-center justify-center rounded-xl bg-white/10 hover:bg-white/20" aria-label="Instagram"><x-icon name="instagram" /></a>
                @endif
                @if(setting('contact_email'))
                    <a href="mailto:{{ setting('contact_email') }}" class="inline-flex h-10 w-10 items-center justify-center rounded-xl bg-white/10 hover:bg-white/20" aria-label="Email"><x-icon name="mail" /></a>
                @endif
            </div>
        </div>
        <div class="md:col-span-2">
            <p class="text-xs font-bold uppercase tracking-[.16em] text-white">Menu</p>
            <ul class="mt-4 space-y-2.5 text-sm">
                <li><a class="hover:text-white" href="{{ route('home') }}">Beranda</a></li>
                <li><a class="hover:text-white" href="{{ route('catalog.packages') }}">Paket Tryout</a></li>
                <li><a class="hover:text-white" href="{{ route('catalog.single') }}">Beli Satuan</a></li>
                <li><a class="hover:text-white" href="{{ route('pages.faq') }}">FAQ</a></li>
                <li><a class="hover:text-white" href="{{ route('pages.about') }}">Tentang Kami</a></li>
            </ul>
        </div>
        <div class="md:col-span-3">
            <p class="text-xs font-bold uppercase tracking-[.16em] text-white">Kategori Tryout</p>
            <ul class="mt-4 grid grid-cols-2 gap-x-4 gap-y-2.5 text-sm">
                @foreach($cats as $c)
                    <li><a class="hover:text-white" href="{{ route('catalog.category', $c) }}">{{ $c->type === 'skd' ? 'SKD CPNS' : $c->name }}</a></li>
                @endforeach
            </ul>
        </div>
        <div class="md:col-span-3">
            <p class="text-xs font-bold uppercase tracking-[.16em] text-white">Kontak</p>
            <ul class="mt-4 space-y-3 text-sm">
                @if(setting('contact_whatsapp'))
                    <li class="flex items-center gap-2.5"><x-icon name="phone" class="h-4 w-4 text-brand-300" /> +{{ normalize_phone(setting('contact_whatsapp')) }}</li>
                @endif
                @if(setting('contact_email'))
                    <li class="flex items-center gap-2.5"><x-icon name="mail" class="h-4 w-4 text-brand-300" /> {{ setting('contact_email') }}</li>
                @endif
                @if(setting('contact_instagram'))
                    <li class="flex items-center gap-2.5"><x-icon name="instagram" class="h-4 w-4 text-brand-300" /> {{ '@'.ltrim(setting('contact_instagram'), '@') }}</li>
                @endif
            </ul>
        </div>
    </div>
    <div class="border-t border-white/10">
        <div class="mx-auto flex max-w-7xl flex-col gap-3 px-4 py-5 text-xs text-navy-300 sm:flex-row sm:items-center sm:justify-between sm:px-6 lg:px-8">
            <p>© {{ date('Y') }} {{ setting('site_name') }}. Seluruh hak cipta dilindungi.</p>
            <div class="flex gap-4">
                <a class="hover:text-white" href="{{ route('pages.terms') }}">Syarat &amp; Ketentuan</a>
                <a class="hover:text-white" href="{{ route('pages.privacy') }}">Kebijakan Privasi</a>
            </div>
        </div>
    </div>
</footer>
