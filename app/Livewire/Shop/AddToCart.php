<?php

namespace App\Livewire\Shop;

use App\Services\CartService;
use Livewire\Component;

/** Tombol tambah ke keranjang (paket / tryout satuan). Tamu diarahkan login dulu. */
class AddToCart extends Component
{
    public string $type = 'package';
    public int $itemId;
    public string $returnUrl = '';
    public bool $block = false;
    public ?string $conflict = null;

    public function mount(string $type, int $itemId, bool $block = false): void
    {
        $this->type = $type === 'package' ? 'package' : 'tryout';
        $this->itemId = $itemId;
        $this->block = $block;
        $this->returnUrl = url()->current();
    }

    public function add(CartService $cart, bool $replace = false, bool $checkout = false)
    {
        if (! auth()->check()) {
            session()->put('url.intended', $this->returnUrl);
            session()->flash('info', 'Masuk atau daftar akun dulu ya, lalu lanjutkan pilihanmu.');

            return $this->redirect(route('login'));
        }

        $res = $cart->add(auth()->user(), $this->type, $this->itemId, $replace);

        if ($res['code'] === 'conflict') {
            $this->conflict = $res['message'];
            $this->dispatch('open-modal', 'cart-conflict-'.$this->getId());

            return null;
        }

        $this->conflict = null;
        $this->dispatch('close-modal', 'cart-conflict-'.$this->getId());
        $this->dispatch('cart-updated');
        $this->dispatch('toast', type: $res['ok'] ? 'success' : 'error', message: $res['message']);

        if ($res['ok'] && $checkout) {
            return $this->redirect(route('cart'));
        }

        return null;
    }

    public function render(CartService $cart)
    {
        $user = auth()->user();
        $inCart = $user ? $cart->has($user, $this->type, $this->itemId) : false;
        $owned = $user && $this->type === 'tryout' ? $user->ownedTryoutIds()->contains($this->itemId) : false;

        return view('livewire.shop.add-to-cart', compact('inCart', 'owned'));
    }
}
