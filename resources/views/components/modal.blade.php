@props(['name', 'title' => null, 'maxWidth' => 'lg'])
@php($w = ['sm' => 'sm:max-w-sm', 'md' => 'sm:max-w-md', 'lg' => 'sm:max-w-lg', 'xl' => 'sm:max-w-xl', '2xl' => 'sm:max-w-2xl', '3xl' => 'sm:max-w-3xl', '4xl' => 'sm:max-w-4xl'][$maxWidth])
<div x-data="{ show: false }"
     x-on:open-modal.window="if ($event.detail === '{{ $name }}' || $event.detail?.name === '{{ $name }}') show = true"
     x-on:close-modal.window="if ($event.detail === '{{ $name }}' || $event.detail?.name === '{{ $name }}' || !$event.detail) show = false"
     x-on:keydown.escape.window="show = false"
     x-show="show" x-cloak class="fixed inset-0 z-[60] flex items-end justify-center sm:items-center sm:p-6" role="dialog" aria-modal="true">
    <div x-show="show" x-transition.opacity class="absolute inset-0 bg-navy-950/50 backdrop-blur-[2px]" @click="show = false"></div>
    <div x-show="show" x-transition:enter="transition ease-out duration-200" x-transition:enter-start="opacity-0 translate-y-6 sm:translate-y-0 sm:scale-95" x-transition:enter-end="opacity-100 translate-y-0 sm:scale-100"
         class="relative max-h-[92vh] w-full overflow-y-auto rounded-t-3xl bg-white shadow-2xl sm:rounded-3xl {{ $w }}">
        @if($title)
            <div class="sticky top-0 z-10 flex items-center justify-between border-b border-slate-100 bg-white px-5 py-4 sm:px-6">
                <h3 class="text-base font-bold text-navy-900">{{ $title }}</h3>
                <button type="button" @click="show = false" class="rounded-lg p-1 text-slate-400 hover:bg-slate-100 hover:text-slate-600" aria-label="Tutup"><x-icon name="x" /></button>
            </div>
        @endif
        <div class="p-5 sm:p-6">{{ $slot }}</div>
    </div>
</div>
