<?php

namespace App\Livewire\Shop;

use App\Services\CartService;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Component;

#[Layout('components.layouts.app')]
#[Title('Keranjang')]
class Cart extends Component
{
    public function remove(int $id, CartService $cart): void
    {
        $cart->remove(auth()->user(), $id);
        $this->dispatch('cart-updated');
        $this->dispatch('toast', type: 'success', message: 'Item dihapus dari keranjang.');
    }

    public function clear(CartService $cart): void
    {
        $cart->clear(auth()->user());
        $this->dispatch('cart-updated');
    }

    public function render(CartService $cart)
    {
        return view('livewire.shop.cart', ['summary' => $cart->summary(auth()->user())])->title('Keranjang');
    }
}
