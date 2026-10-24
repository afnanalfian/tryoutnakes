{{-- Toast global: session flash + event Livewire "toast" --}}
<div x-data="{ items: [], push(e) { const id = Date.now() + Math.random(); this.items.push({ id, ...e }); setTimeout(() => this.items = this.items.filter(i => i.id !== id), e.timeout || 4500); } }"
     x-init="
        @if(session('success')) push({ type: 'success', message: @js(session('success')) }); @endif
        @if(session('error')) push({ type: 'error', message: @js(session('error')) }); @endif
        @if(session('info')) push({ type: 'info', message: @js(session('info')) }); @endif
     "
     @toast.window="push($event.detail)"
     class="pointer-events-none fixed inset-x-0 bottom-20 z-[70] flex flex-col items-center gap-2 px-4 sm:bottom-auto sm:left-auto sm:right-4 sm:top-4 sm:items-end"
     aria-live="polite">
    <template x-for="item in items" :key="item.id">
        <div x-transition.opacity.duration.200ms
             class="pointer-events-auto flex w-full max-w-sm items-start gap-3 rounded-2xl border bg-white px-4 py-3 text-sm shadow-lift"
             :class="{ 'border-emerald-200': item.type === 'success', 'border-rose-200': item.type === 'error', 'border-sky-200': !['success','error'].includes(item.type) }">
            <span class="mt-0.5 inline-flex h-5 w-5 shrink-0 items-center justify-center rounded-full text-[11px] font-bold text-white"
                  :class="{ 'bg-emerald-500': item.type === 'success', 'bg-rose-500': item.type === 'error', 'bg-sky-500': !['success','error'].includes(item.type) }"
                  x-text="item.type === 'success' ? '✓' : (item.type === 'error' ? '!' : 'i')"></span>
            <p class="flex-1 font-medium text-slate-700" x-text="item.message"></p>
            <button type="button" class="text-slate-400 hover:text-slate-600" @click="items = items.filter(i => i.id !== item.id)" aria-label="Tutup">✕</button>
        </div>
    </template>
</div>
