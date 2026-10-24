@props(['dark' => false, 'compact' => false])
@php($logo = setting('logo'))
<span {{ $attributes->merge(['class' => 'inline-flex items-center gap-2.5']) }}>
    @if($logo)
        <img src="{{ media_url($logo) }}" alt="{{ setting('site_name') }}" class="h-9 w-auto" width="36" height="36">
    @else
        <svg class="h-9 w-9 shrink-0" viewBox="0 0 40 40" fill="none" aria-hidden="true">
            <rect x="14" y="3" width="12" height="34" rx="3.5" fill="{{ $dark ? '#8ebdff' : '#1d55f0' }}"/>
            <rect x="3" y="14" width="34" height="12" rx="3.5" fill="{{ $dark ? '#ffffff' : '#172a4d' }}"/>
            <path d="M7.5 20h6.2l2.3-4.2 3.6 8.6 2.6-6.2 1.6 1.8H32.5" stroke="{{ $dark ? '#172a4d' : '#ffffff' }}" stroke-width="2.1" stroke-linecap="round" stroke-linejoin="round"/>
        </svg>
    @endif
    @unless($compact)
        <span class="leading-none">
            <span class="block font-display text-[1.35rem] font-semibold tracking-tight {{ $dark ? 'text-white' : 'text-navy-900' }}">{{ setting('site_name') }}</span>
            <span class="mt-0.5 block text-[10.5px] font-medium {{ $dark ? 'text-navy-200' : 'text-slate-500' }}">Tryout SKD &amp; SKB CPNS Nakes</span>
        </span>
    @endunless
</span>
