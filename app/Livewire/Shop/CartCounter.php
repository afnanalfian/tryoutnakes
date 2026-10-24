<?php

namespace App\Livewire\Shop;

use Livewire\Attributes\On;
use Livewire\Component;

class CartCounter extends Component
{
    public int $count = 0;

    public function mount(int $initial = 0): void
    {
        $this->count = $initial;
    }

    #[On('cart-updated')]
    public function refresh(): void
    {
        $this->count = auth()->user()?->cartItems()->count() ?? 0;
    }

    public function render()
    {
        return <<<'BLADE'
<span>
    @if($count > 0)
        <span class="absolute -right-0.5 -top-0.5 inline-flex h-[18px] min-w-[18px] items-center justify-center rounded-full bg-brand-600 px-1 text-[10px] font-bold text-white ring-2 ring-white">{{ $count }}</span>
    @endif
</span>
BLADE;
    }
}
