@props(['icon' => 'sparkles', 'title' => 'Belum ada data', 'text' => null])
<div {{ $attributes->merge(['class' => 'flex flex-col items-center justify-center rounded-2xl border border-dashed border-slate-300 bg-white/60 px-6 py-12 text-center']) }}>
    <span class="inline-flex h-14 w-14 items-center justify-center rounded-2xl bg-navy-50 text-navy-700"><x-icon :name="$icon" class="h-7 w-7" /></span>
    <p class="mt-4 font-semibold text-navy-900">{{ $title }}</p>
    @if($text)<p class="mt-1 max-w-sm text-sm text-slate-500">{{ $text }}</p>@endif
    @if(trim($slot))<div class="mt-5">{{ $slot }}</div>@endif
</div>
