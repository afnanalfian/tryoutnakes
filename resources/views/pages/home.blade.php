<x-layouts.public>
    {{-- ================= HERO ================= --}}
    <section class="relative overflow-hidden bg-gradient-to-b from-[#f4f7fc] via-white to-white">
        <div class="grid-bg pointer-events-none absolute inset-0 [mask-image:radial-gradient(ellipse_at_top_left,#000_20%,transparent_70%)]"></div>
        <div class="pointer-events-none absolute -left-40 top-20 h-96 w-96 rounded-full bg-brand-200/40 blur-3xl"></div>
        <div class="relative mx-auto grid max-w-7xl items-center gap-10 px-4 pb-14 pt-10 sm:px-6 lg:grid-cols-[1.02fr_1fr] lg:gap-6 lg:px-8 lg:pb-20 lg:pt-16">
            <div class="animate-fade-up">
                <span class="inline-flex items-center gap-2 rounded-full border border-brand-200 bg-white px-3 py-1.5 text-[11px] font-bold uppercase tracking-[.14em] text-brand-700 shadow-sm">
                    <span class="h-1.5 w-1.5 rounded-full bg-brand-600"></span> SKD × SKB CPNS Tenaga Kesehatan
                </span>
                <h1 class="mt-5 font-display text-[2.6rem] font-semibold leading-[1.04] tracking-tight text-navy-900 sm:text-6xl lg:text-[3.7rem] xl:text-[4.1rem]">
                    Siapkan SKD &amp; <span class="whitespace-nowrap">SKB-mu.</span><br>
                    <span class="relative whitespace-nowrap italic text-brand-600">Tryout Sesuai
                        <svg class="absolute -bottom-2 left-0 w-full text-brand-200" viewBox="0 0 300 12" fill="none" preserveAspectRatio="none" aria-hidden="true"><path d="M2 9c60-6 180-8 296-3" stroke="currentColor" stroke-width="5" stroke-linecap="round"/></svg>
                    </span><br>Profesimu.
                </h1>
                <p class="mt-6 max-w-xl text-base leading-relaxed text-slate-600 sm:text-lg">
                    Simulasi CAT SKD (TWK, TIU, TKP) dan SKB untuk 8 profesi nakes — lengkap dengan pembahasan, peringkat peserta, dan grafik progres supaya kamu tahu persis bagian mana yang perlu ditingkatkan.
                </p>
                <div class="mt-8 flex flex-col gap-3 sm:flex-row">
                    <a href="{{ route('catalog.packages') }}" class="btn-primary btn-lg rounded-full px-7">Lihat Paket Tryout <x-icon name="arrow-right" class="h-4 w-4" /></a>
                    <a href="{{ route('catalog.single') }}" class="btn-outline btn-lg rounded-full px-7">Beli Satuan</a>
                </div>
                <dl class="mt-10 grid max-w-lg grid-cols-3 gap-4 border-t border-slate-200 pt-6">
                    <div><dt class="text-xs font-medium text-slate-500">Profesi Nakes</dt><dd class="mt-1 text-2xl font-extrabold text-navy-900">8<span class="text-brand-600">+</span>SKD</dd></div>
                    <div><dt class="text-xs font-medium text-slate-500">{{ $stats['questions'] >= 100 ? 'Bank Soal' : 'Pembahasan' }}</dt><dd class="mt-1 text-2xl font-extrabold text-navy-900">{{ $stats['questions'] >= 100 ? number_format($stats['questions'], 0, ',', '.').'+' : '100%' }}</dd></div>
                    <div><dt class="text-xs font-medium text-slate-500">Sistem</dt><dd class="mt-1 text-2xl font-extrabold text-navy-900">CAT</dd></div>
                </dl>
            </div>

            <div class="relative mx-auto w-full max-w-2xl lg:max-w-none">
                <div class="relative overflow-hidden rounded-[2rem] border border-white bg-white shadow-lift ring-1 ring-navy-900/5">
                    <img src="{{ asset('images/hero/hero-light-960.webp') }}"
                         srcset="{{ asset('images/hero/hero-light-640.webp') }} 640w, {{ asset('images/hero/hero-light-960.webp') }} 960w, {{ asset('images/hero/hero-light-1536.webp') }} 1536w"
                         sizes="(min-width: 1024px) 50vw, 100vw" width="1536" height="1024" fetchpriority="high"
                         alt="Simulasi tryout SKD dan SKB CPNS tenaga kesehatan di laptop dan ponsel" class="h-full w-full object-cover">
                </div>
                {{-- Chip mengambang --}}
                <div class="absolute -left-3 top-8 hidden animate-float rounded-2xl border border-slate-100 bg-white/95 px-4 py-3 shadow-lift backdrop-blur sm:block">
                    <p class="text-[11px] font-semibold uppercase tracking-wider text-slate-500">Passing Grade</p>
                    <p class="mt-0.5 text-sm font-bold text-navy-900">TWK 65 · TIU 80 · TKP 166</p>
                </div>
                <div class="absolute -bottom-5 right-4 hidden animate-float rounded-2xl border border-slate-100 bg-white/95 px-4 py-3 shadow-lift backdrop-blur [animation-delay:1.5s] sm:flex sm:items-center sm:gap-3">
                    <span class="inline-flex h-10 w-10 items-center justify-center rounded-xl bg-mint-50 text-mint-600"><x-icon name="trophy" /></span>
                    <div><p class="text-[11px] font-semibold uppercase tracking-wider text-slate-500">Peringkat</p><p class="text-sm font-bold text-navy-900">Tahu posisimu di antara peserta</p></div>
                </div>
            </div>
        </div>
    </section>

    {{-- ================= BANNER ================= --}}
    @if($banners->isNotEmpty())
        <section class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8" x-data="{ i: 0, n: {{ $banners->count() }}, t: null }" x-init="if (n > 1) t = setInterval(() => i = (i + 1) % n, 6000)">
            <div class="relative overflow-hidden rounded-3xl bg-navy-900 shadow-soft">
                @foreach($banners as $k => $b)
                    <a href="{{ $b->link_url ?: '#' }}" x-show="i === {{ $k }}" @if($k > 0) x-cloak @endif x-transition.opacity.duration.500ms class="block" aria-label="{{ $b->title }}">
                        <picture>
                            @if($b->image_mobile)<source media="(max-width: 640px)" srcset="{{ media_url($b->image_mobile) }}">@endif
                            <img src="{{ media_url($b->image) }}" alt="{{ $b->title }}" class="aspect-[16/9] w-full object-cover sm:aspect-[3/1]" loading="{{ $k ? 'lazy' : 'eager' }}" width="1600" height="533">
                        </picture>
                        @if($b->subtitle || $b->button_text)
                            <div class="absolute inset-0 flex items-end bg-gradient-to-t from-navy-950/80 via-navy-950/10 to-transparent p-5 sm:items-center sm:bg-gradient-to-r sm:p-10">
                                <div class="max-w-md text-white">
                                    <p class="font-display text-2xl font-semibold sm:text-4xl">{{ $b->title }}</p>
                                    @if($b->subtitle)<p class="mt-2 text-sm text-white/80 sm:text-base">{{ $b->subtitle }}</p>@endif
                                    @if($b->button_text)<span class="btn mt-4 bg-white text-navy-900">{{ $b->button_text }} <x-icon name="arrow-right" class="h-4 w-4" /></span>@endif
                                </div>
                            </div>
                        @endif
                    </a>
                @endforeach
                @if($banners->count() > 1)
                    <div class="absolute bottom-3 left-1/2 flex -translate-x-1/2 gap-1.5">
                        @foreach($banners as $k => $b)
                            <button type="button" @click="i = {{ $k }}" class="h-1.5 rounded-full transition-all" :class="i === {{ $k }} ? 'w-6 bg-white' : 'w-1.5 bg-white/50'" aria-label="Banner {{ $k + 1 }}"></button>
                        @endforeach
                    </div>
                @endif
            </div>
        </section>
    @endif

    {{-- ================= PILIH PROFESI ================= --}}
    <section class="mx-auto max-w-7xl px-4 py-16 sm:px-6 lg:px-8 lg:py-20" id="profesi">
        <div class="flex flex-col gap-4 sm:flex-row sm:items-end sm:justify-between">
            <div>
                <p class="eyebrow">Pilih Profesi</p>
                <h2 class="section-title mt-2">Cari Tryout Sesuai Profesimu</h2>
                <p class="mt-2 max-w-2xl text-slate-600">Satu SKD untuk semua, ditambah SKB khusus untuk 8 rumpun tenaga kesehatan.</p>
            </div>
            <a href="{{ route('catalog.single') }}" class="inline-flex items-center gap-1.5 text-sm font-semibold text-navy-900 hover:text-brand-700">Beli per tryout <x-icon name="arrow-right" class="h-4 w-4" /></a>
        </div>
        <div class="mt-8 grid grid-cols-2 gap-3 sm:gap-4 md:grid-cols-3 lg:grid-cols-3 xl:grid-cols-3">
            @foreach($categories as $c)
                <a href="{{ route('catalog.category', $c) }}" @class(['group relative flex gap-3 overflow-hidden rounded-2xl border bg-white p-4 transition hover:-translate-y-0.5 hover:shadow-lift sm:flex-row sm:items-center sm:gap-4 sm:p-5', 'col-span-2 items-center border-navy-200 bg-gradient-to-br from-navy-900 to-navy-700 text-white md:col-span-1' => $c->isSkd(), 'flex-col items-start border-slate-200/80 shadow-soft' => ! $c->isSkd()])>
                    <span class="inline-flex h-12 w-12 shrink-0 items-center justify-center rounded-2xl sm:h-14 sm:w-14 {{ $c->isSkd() ? 'bg-white/15 text-white' : '' }}" @unless($c->isSkd()) style="background: {{ $c->color }}17; color: {{ $c->color }}" @endunless>
                        <x-icon :name="$c->icon" class="h-6 w-6 sm:h-7 sm:w-7" />
                    </span>
                    <span class="min-w-0">
                        <span class="block font-bold leading-tight {{ $c->isSkd() ? 'text-white' : 'text-navy-900' }}">{{ $c->isSkd() ? 'SKD CPNS' : $c->name }}</span>
                        <span class="mt-0.5 flex items-center gap-1 text-xs {{ $c->isSkd() ? 'text-navy-200' : 'text-slate-500' }}">{{ $c->isSkd() ? 'TWK · TIU · TKP' : 'Tryout SKB' }} <x-icon name="arrow-right" class="h-3.5 w-3.5 transition group-hover:translate-x-0.5" /></span>
                    </span>
                    @if($c->tryouts_count)<span class="absolute right-3 top-3 hidden rounded-full px-2 py-0.5 text-[10px] font-bold sm:inline {{ $c->isSkd() ? 'bg-white/15 text-white' : 'bg-slate-100 text-slate-600' }}">{{ $c->tryouts_count }} TO</span>@endif
                </a>
            @endforeach
        </div>
    </section>

    {{-- ================= PILIH CARA ================= --}}
    <section class="relative overflow-hidden bg-[#f3f6fb] py-16 lg:py-20">
        <div class="mx-auto grid max-w-7xl gap-6 px-4 sm:px-6 lg:grid-cols-[1fr_1fr_.9fr] lg:px-8">
            <div class="lg:col-span-3">
                <p class="eyebrow">Pilih Cara Belajarmu</p>
                <h2 class="section-title mt-2">Paket hemat atau pilih sendiri.</h2>
            </div>
            <div class="relative overflow-hidden rounded-3xl border border-brand-100 bg-white p-7 shadow-soft">
                <span class="inline-flex h-14 w-14 items-center justify-center rounded-2xl bg-brand-50 text-brand-600"><x-icon name="cube" class="h-7 w-7" /></span>
                <h3 class="mt-5 font-display text-2xl font-semibold text-navy-900">Paket Tryout</h3>
                <p class="mt-2 text-sm leading-relaxed text-slate-600">Kombinasi tryout pilihan — SKD saja, SKB per profesi, atau SKD + SKB sekaligus — dengan harga lebih hemat dibanding beli satuan.</p>
                <a href="{{ route('catalog.packages') }}" class="btn-brand mt-6 rounded-full">Lihat Paket <x-icon name="arrow-right" class="h-4 w-4" /></a>
            </div>
            <div class="relative overflow-hidden rounded-3xl border border-mint-100 bg-white p-7 shadow-soft">
                <span class="inline-flex h-14 w-14 items-center justify-center rounded-2xl bg-mint-50 text-mint-600"><x-icon name="tag" class="h-7 w-7" /></span>
                <h3 class="mt-5 font-display text-2xl font-semibold text-navy-900">Tryout Satuan</h3>
                <p class="mt-2 text-sm leading-relaxed text-slate-600">Pilih SKD atau SKB, tentukan profesimu, lalu masukkan tryout yang kamu mau ke keranjang. Fleksibel sesuai budget.</p>
                <a href="{{ route('catalog.single') }}" class="btn-mint mt-6 rounded-full">Pilih Tryout <x-icon name="arrow-right" class="h-4 w-4" /></a>
            </div>
            <div class="relative hidden overflow-hidden rounded-3xl lg:block">
                <img src="{{ asset('images/hero/hero-dark-640.webp') }}" alt="" loading="lazy" class="h-full w-full object-cover" width="640" height="427">
                <div class="absolute inset-0 bg-gradient-to-t from-navy-950/80 to-transparent"></div>
                <p class="absolute bottom-5 left-5 right-5 font-display text-xl font-semibold text-white">Rasakan sensasi CAT sebelum hari H.</p>
            </div>
        </div>
    </section>

    {{-- ================= PAKET POPULER ================= --}}
    @if($packages->isNotEmpty())
        <section class="mx-auto max-w-7xl px-4 py-16 sm:px-6 lg:px-8 lg:py-20">
            <div class="flex flex-col gap-4 sm:flex-row sm:items-end sm:justify-between">
                <div>
                    <p class="eyebrow">Produk Populer</p>
                    <h2 class="section-title mt-2">Paket Tryout Pilihan</h2>
                    <p class="mt-2 text-slate-600">Paket yang paling banyak dipilih calon ASN nakes.</p>
                </div>
                <a href="{{ route('catalog.packages') }}" class="inline-flex items-center gap-1.5 text-sm font-semibold text-navy-900 hover:text-brand-700">Lihat semua paket <x-icon name="arrow-right" class="h-4 w-4" /></a>
            </div>
            <div class="no-scrollbar -mx-4 mt-8 flex snap-x snap-mandatory gap-4 overflow-x-auto px-4 pb-4 sm:mx-0 sm:grid sm:grid-cols-2 sm:overflow-visible sm:px-0 lg:grid-cols-3">
                @foreach($packages as $p)
                    <x-package-card :package="$p" class="w-[82%] shrink-0 snap-start sm:w-auto" />
                @endforeach
            </div>
        </section>
    @endif

    {{-- ================= SIMULASI CAT (dark) ================= --}}
    <section class="relative overflow-hidden bg-navy-950 py-16 text-white lg:py-24">
        <img src="{{ asset('images/hero/hero-dark-1536.webp') }}" alt="" loading="lazy" class="absolute inset-y-0 right-0 hidden h-full w-[62%] object-cover opacity-90 lg:block" width="1536" height="1024">
        <div class="absolute inset-0 hidden bg-gradient-to-r from-navy-950 via-navy-950/95 to-navy-950/10 lg:block"></div>
        <div class="relative mx-auto max-w-7xl px-4 sm:px-6 lg:px-8">
            <div class="max-w-xl">
                <p class="text-[11px] font-bold uppercase tracking-[.18em] text-brand-300">Simulasi CAT</p>
                <h2 class="mt-2 font-display text-3xl font-semibold tracking-tight text-white sm:text-5xl">Latihan yang terasa seperti <span class="italic text-brand-300">ujian sungguhan.</span></h2>
                <p class="mt-4 text-navy-200">Tampilan bersih, nyaman di HP maupun laptop. Semua jawaban tersimpan otomatis — sinyal putus pun tetap aman.</p>
                <ul class="mt-8 grid gap-4 sm:grid-cols-2">
                    @foreach([
                        ['clock', 'Timer & auto-submit', 'Waktu berjalan di server, tidak bisa dicurangi.'],
                        ['flag', 'Tandai ragu-ragu', 'Navigasi nomor soal berwarna seperti CAT BKN.'],
                        ['book', 'Pembahasan lengkap', 'Kunci, bobot TKP, dan penjelasan setiap soal.'],
                        ['trophy', 'Peringkat peserta', 'Lihat posisimu di antara peserta tryout yang sama.'],
                        ['trending', 'Grafik progres', 'Pantau perkembangan per kategori & subtes.'],
                        ['shield', 'Auto-save offline', 'Jawaban dicadangkan di perangkat saat offline.'],
                    ] as [$icon, $t, $d])
                        <li class="flex gap-3">
                            <span class="inline-flex h-10 w-10 shrink-0 items-center justify-center rounded-xl bg-white/10 text-brand-300"><x-icon :name="$icon" /></span>
                            <span><span class="block font-semibold">{{ $t }}</span><span class="text-sm text-navy-300">{{ $d }}</span></span>
                        </li>
                    @endforeach
                </ul>
            </div>
            <img src="{{ asset('images/hero/hero-dark-960.webp') }}" alt="Tampilan tryout CAT di laptop dan ponsel" loading="lazy" class="mt-10 rounded-2xl lg:hidden" width="960" height="640">
        </div>
    </section>

    {{-- ================= TRYOUT GRATIS ================= --}}
    @if($freeTryouts->isNotEmpty())
        <section class="mx-auto max-w-7xl px-4 pt-16 sm:px-6 lg:px-8 lg:pt-20">
            <div class="rounded-3xl border border-mint-200 bg-gradient-to-br from-mint-50 to-white p-6 sm:p-10">
                <div class="flex flex-col gap-3 sm:flex-row sm:items-end sm:justify-between">
                    <div>
                        <p class="text-[11px] font-bold uppercase tracking-[.18em] text-mint-700">Coba Dulu</p>
                        <h2 class="mt-2 font-display text-3xl font-semibold text-navy-900">Tryout Gratis</h2>
                        <p class="mt-1 text-slate-600">Daftar akun, langsung kerjakan. Tanpa bayar.</p>
                    </div>
                </div>
                <div class="mt-6 grid gap-5 sm:grid-cols-2 lg:grid-cols-3">
                    @foreach($freeTryouts as $t)
                        <x-tryout-card :tryout="$t" :href="auth()->check() ? route('my-tryouts.show', $t) : route('register')">
                            <a href="{{ auth()->check() ? route('my-tryouts.show', $t) : route('register') }}" class="btn-mint w-full">Kerjakan Gratis <x-icon name="arrow-right" class="h-4 w-4" /></a>
                        </x-tryout-card>
                    @endforeach
                </div>
            </div>
        </section>
    @endif

    {{-- ================= TENTANG + LANGKAH ================= --}}
    <section class="mx-auto grid max-w-7xl gap-10 px-4 py-16 sm:px-6 lg:grid-cols-2 lg:px-8 lg:py-24">
        <div>
            <p class="eyebrow">Tentang Kami</p>
            <h2 class="section-title mt-2">Dibuat untuk Nakes.<br>Fokus untuk lulus.</h2>
            <p class="mt-4 leading-relaxed text-slate-600">{{ setting('site_name') }} menyediakan tryout SKD dan SKB CPNS yang dirancang khusus untuk tenaga kesehatan. Soal disusun mengikuti kisi-kisi terbaru, penilaian mengikuti aturan resmi (TWK & TIU 5 poin, TKP berbobot 1–5), dan setiap hasil dilengkapi analisis agar latihanmu terarah.</p>
            <a href="{{ route('pages.about') }}" class="btn-outline mt-6 rounded-full">Selengkapnya</a>
        </div>
        <div>
            <p class="eyebrow">Cara Menggunakan</p>
            <h2 class="section-title mt-2">Mulai dalam 4 langkah.</h2>
            <ol class="mt-6 space-y-4">
                @foreach([
                    ['Daftar akun', 'Gratis, cukup nama, email, dan nomor WhatsApp.'],
                    ['Pilih paket / satuan', 'Masukkan ke keranjang lalu checkout.'],
                    ['Bayar & unggah bukti', 'QRIS atau transfer. Verifikasi maks. '.setting('verification_sla').'.'],
                    ['Kerjakan & evaluasi', 'Lihat nilai, pembahasan, peringkat, dan progresmu.'],
                ] as $i => [$t, $d])
                    <li class="flex gap-4">
                        <span class="inline-flex h-11 w-11 shrink-0 items-center justify-center rounded-full bg-navy-900 font-display text-lg font-semibold text-white">0{{ $i + 1 }}</span>
                        <div class="pt-1"><p class="font-bold text-navy-900">{{ $t }}</p><p class="text-sm text-slate-600">{{ $d }}</p></div>
                    </li>
                @endforeach
            </ol>
        </div>
    </section>

    {{-- ================= PENILAIAN ================= --}}
    <section class="bg-[#f3f6fb] py-16 lg:py-20">
        <div class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8">
            <p class="eyebrow">Sistem Penilaian</p>
            <h2 class="section-title mt-2">Transparan, sesuai aturan.</h2>
            <div class="mt-8 grid gap-5 md:grid-cols-2">
                <div class="card card-pad">
                    <p class="font-bold text-navy-900">SKD — Nilai Ambang Batas</p>
                    <div class="mt-4 grid grid-cols-3 gap-3 text-center">
                        @foreach(['TWK' => [65, '30 soal · benar 5'], 'TIU' => [80, '35 soal · benar 5'], 'TKP' => [166, '45 soal · bobot 1–5']] as $k => [$pg, $info])
                            <div class="rounded-2xl bg-navy-50 p-3">
                                <p class="text-xs font-bold text-navy-600">{{ $k }}</p>
                                <p class="mt-1 text-2xl font-extrabold text-navy-900">{{ $pg }}</p>
                                <p class="mt-1 text-[11px] leading-tight text-slate-500">{{ $info }}</p>
                            </div>
                        @endforeach
                    </div>
                    <p class="mt-4 text-sm text-slate-600">Lulus jika ketiga subtes mencapai ambang batas. Nilai maksimal 550.</p>
                </div>
                <div class="card card-pad">
                    <p class="font-bold text-navy-900">SKB Nakes — Skala Fleksibel</p>
                    <p class="mt-3 text-sm leading-relaxed text-slate-600">Setiap tryout SKB punya skala nilai sendiri (mis. 0–100 atau 0–400). Nilai = jumlah benar ÷ jumlah soal × skala.</p>
                    <div class="mt-4 rounded-2xl bg-mint-50 p-4 font-mono text-sm text-mint-900">
                        Skala 400 · 50 benar dari 100 soal<br>→ 50 ÷ 100 × 400 = <strong>200</strong>
                    </div>
                </div>
            </div>
        </div>
    </section>

    {{-- ================= FAQ ================= --}}
    @if($faqs->isNotEmpty())
        <section class="mx-auto max-w-4xl px-4 py-16 sm:px-6 lg:px-8 lg:py-20">
            <div class="text-center">
                <p class="eyebrow">FAQ</p>
                <h2 class="section-title mt-2">Pertanyaan yang sering diajukan</h2>
            </div>
            <div class="mt-8 space-y-3" x-data="{ open: 0 }">
                @foreach($faqs as $i => $f)
                    <div class="card overflow-hidden">
                        <button type="button" @click="open = open === {{ $i }} ? null : {{ $i }}" class="flex w-full items-center justify-between gap-4 px-5 py-4 text-left font-semibold text-navy-900" :aria-expanded="open === {{ $i }}">
                            {{ $f->question }}
                            <x-icon name="chevron-down" class="h-5 w-5 shrink-0 text-slate-400 transition" ::class="open === {{ $i }} && 'rotate-180'" />
                        </button>
                        <div x-show="open === {{ $i }}" x-collapse.duration.200ms @if($i) x-cloak @endif class="px-5 pb-5 text-sm leading-relaxed text-slate-600">{!! nl2br(e($f->answer)) !!}</div>
                    </div>
                @endforeach
            </div>
        </section>
    @endif

    {{-- ================= CATATAN ================= --}}
    <section class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8">
        <div class="flex flex-col gap-3 rounded-2xl border border-slate-200 bg-slate-50 p-5 sm:flex-row sm:items-center sm:gap-6">
            <p class="flex shrink-0 items-center gap-2 font-semibold text-navy-900"><x-icon name="info" class="text-brand-600" /> Catatan Penting</p>
            <p class="text-sm text-slate-600">{{ setting('disclaimer') }}</p>
        </div>
    </section>

    {{-- ================= CTA ================= --}}
    <section class="mx-auto max-w-7xl px-4 py-16 sm:px-6 lg:px-8">
        <div class="relative overflow-hidden rounded-[2rem] bg-navy-900 px-6 py-12 text-center sm:px-12 sm:py-16">
            <svg class="absolute inset-0 h-full w-full opacity-[.07]" aria-hidden="true"><defs><pattern id="p" width="40" height="40" patternUnits="userSpaceOnUse"><path d="M20 8v24M8 20h24" stroke="#fff" stroke-width="3" stroke-linecap="round"/></pattern></defs><rect width="100%" height="100%" fill="url(#p)"/></svg>
            <div class="relative">
                <h2 class="font-display text-3xl font-semibold text-white sm:text-5xl">Siap menaklukkan seleksi CPNS?</h2>
                <p class="mx-auto mt-3 max-w-xl text-navy-200">Pilih profesimu dan mulai latihan sekarang. Kursi ASN nakes menunggumu.</p>
                <div class="mt-8 flex flex-col justify-center gap-3 sm:flex-row">
                    <a href="{{ route('catalog.packages') }}" class="btn btn-lg rounded-full bg-white px-7 text-navy-900 hover:bg-navy-50">Jelajahi Paket <x-icon name="arrow-right" class="h-4 w-4" /></a>
                    @guest<a href="{{ route('register') }}" class="btn btn-lg rounded-full border border-white/30 px-7 text-white hover:bg-white/10">Daftar Gratis</a>@endguest
                </div>
            </div>
        </div>
    </section>
</x-layouts.public>
