<?php

namespace App\Livewire\Shop;

use App\Services\CartService;
use App\Services\OrderService;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Validate;
use Livewire\Component;
use RuntimeException;

#[Layout('components.layouts.app')]
class Checkout extends Component
{
    #[Validate('accepted', message: 'Centang persetujuan syarat & ketentuan terlebih dahulu.')]
    public bool $agree = false;

    public function mount(CartService $cart)
    {
        if ($cart->count(auth()->user()) === 0) {
            return $this->redirect(route('cart'));
        }

        return null;
    }

    public function place(OrderService $orders)
    {
        $this->validate();
        try {
            $order = $orders->checkout(auth()->user());
        } catch (RuntimeException $e) {
            $this->dispatch('toast', type: 'error', message: $e->getMessage());

            return null;
        }
        $this->dispatch('cart-updated');
        session()->flash('success', 'Pesanan dibuat! Silakan selesaikan pembayaran.');

        return $this->redirect(route('orders.show', $order));
    }

    public function render(CartService $cart)
    {
        return view('livewire.shop.checkout', ['summary' => $cart->summary(auth()->user())])->title('Checkout');
    }
}
