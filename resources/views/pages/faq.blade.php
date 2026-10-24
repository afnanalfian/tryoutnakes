<x-layouts.public>
    <section class="mx-auto max-w-3xl px-4 py-12 sm:px-6 lg:py-16">
        <x-breadcrumbs :items="[['FAQ', route('pages.faq')]]" />
        <h1 class="mt-4 font-display text-4xl font-semibold tracking-tight text-navy-900 sm:text-5xl">Pertanyaan Umum</h1>
        <div class="mt-8 space-y-3" x-data="{ open: 0 }">
            @forelse($faqs as $i => $f)
                <div class="card overflow-hidden">
                    <button type="button" @click="open = open === {{ $i }} ? null : {{ $i }}" class="flex w-full items-center justify-between gap-4 px-5 py-4 text-left font-semibold text-navy-900">
                        {{ $f->question }} <x-icon name="chevron-down" class="h-5 w-5 shrink-0 text-slate-400 transition" ::class="open === {{ $i }} && 'rotate-180'" />
                    </button>
                    <div x-show="open === {{ $i }}" x-collapse @if($i) x-cloak @endif class="px-5 pb-5 text-sm leading-relaxed text-slate-600">{!! nl2br(e($f->answer)) !!}</div>
                </div>
            @empty
                <x-empty icon="question" title="FAQ belum tersedia" />
            @endforelse
        </div>
        @if($wa = wa_link(setting('contact_whatsapp'), 'Halo Admin, saya mau bertanya.'))
            <div class="mt-10 rounded-3xl bg-navy-900 p-6 text-center text-white">
                <p class="font-semibold">Masih ada pertanyaan?</p>
                <a href="{{ $wa }}" target="_blank" rel="noopener" class="btn mt-4 bg-[#25D366] text-white hover:bg-[#1fb257]"><x-icon name="whatsapp" /> Tanya Admin via WhatsApp</a>
            </div>
        @endif
    </section>
</x-layouts.public>
