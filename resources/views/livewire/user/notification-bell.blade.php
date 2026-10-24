<div class="relative" x-data="{ open: false }" @click.outside="open = false" @keydown.escape.window="open = false">
    <button type="button" @click="open = !open" class="relative inline-flex h-10 w-10 items-center justify-center rounded-xl text-navy-900 hover:bg-navy-50" aria-label="Notifikasi">
        <x-icon name="bell" class="h-[22px] w-[22px]" />
        @if($unread + ($active ? 1 : 0) > 0)
            <span class="absolute right-1.5 top-1.5 inline-flex h-[18px] min-w-[18px] items-center justify-center rounded-full bg-rose-500 px-1 text-[10px] font-bold text-white ring-2 ring-white">{{ min(99, $unread + ($active ? 1 : 0)) }}</span>
        @endif
    </button>
    <div x-show="open" x-cloak x-transition.origin.top.right class="fixed inset-x-3 top-16 z-50 overflow-hidden rounded-2xl border border-slate-200 bg-white shadow-lift sm:absolute sm:inset-x-auto sm:right-0 sm:top-auto sm:mt-2 sm:w-96">
        <div class="flex items-center justify-between border-b border-slate-100 px-4 py-3">
            <p class="font-bold text-navy-900">Notifikasi</p>
            @if($unread)<button wire:click="markAllRead" class="text-xs font-semibold text-brand-700 hover:underline">Tandai semua dibaca</button>@endif
        </div>
        <div class="max-h-[60vh] overflow-y-auto">
            @if($active)
                <a href="{{ route('exam.show', $active) }}" class="flex gap-3 border-b border-amber-100 bg-amber-50 px-4 py-3 hover:bg-amber-100/70">
                    <span class="mt-0.5 inline-flex h-9 w-9 shrink-0 items-center justify-center rounded-xl bg-amber-500 text-white"><x-icon name="play" class="h-4 w-4" /></span>
                    <span class="min-w-0">
                        <span class="block text-sm font-bold text-amber-900">Ujian sedang berlangsung</span>
                        <span class="block text-xs text-amber-800">{{ $active->tryout->title }} · sisa {{ (int) ceil($active->remainingSeconds() / 60) }} menit. Ketuk untuk melanjutkan.</span>
                    </span>
                </a>
            @endif
            @forelse($items as $n)
                <a href="{{ route('notifications.open', $n->id) }}" class="flex gap-3 px-4 py-3 hover:bg-slate-50 {{ $n->read_at ? '' : 'bg-brand-50/50' }}">
                    <span class="mt-1.5 h-2 w-2 shrink-0 rounded-full {{ $n->read_at ? 'bg-transparent' : 'bg-brand-600' }}"></span>
                    <span class="min-w-0">
                        <span class="block text-sm font-semibold text-navy-900">{{ $n->data['title'] ?? 'Notifikasi' }}</span>
                        <span class="line-clamp-2 block text-xs text-slate-500">{{ $n->data['body'] ?? '' }}</span>
                        <span class="mt-1 block text-[11px] text-slate-400">{{ $n->created_at->diffForHumans() }}</span>
                    </span>
                </a>
            @empty
                @unless($active)<p class="px-4 py-10 text-center text-sm text-slate-500">Belum ada notifikasi.</p>@endunless
            @endforelse
        </div>
        <a href="{{ route('notifications') }}" class="block border-t border-slate-100 py-3 text-center text-sm font-semibold text-navy-900 hover:bg-slate-50">Lihat semua</a>
    </div>
</div>
