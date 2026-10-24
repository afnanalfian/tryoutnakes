<?php

namespace App\Livewire\Shop;

use App\Models\Order;
use App\Models\PaymentMethod;
use App\Models\Tryout;
use App\Services\OrderService;
use Livewire\Attributes\Layout;
use Livewire\Component;
use Livewire\WithFileUploads;
use RuntimeException;

#[Layout('components.layouts.app')]
class OrderPayment extends Component
{
    use WithFileUploads;

    public Order $order;
    public $proof;
    public ?string $method = null;
    public string $note = '';

    public function mount(Order $order): void
    {
        abort_unless($order->user_id === auth()->id() || auth()->user()->isAdmin(), 403);
        $this->order = $order;
        $this->method = $order->payment_method_id ? (string) $order->payment_method_id : null;
    }

    public function submitProof(OrderService $orders): void
    {
        abort_unless($this->order->user_id === auth()->id(), 403);
        $this->validate([
            'proof' => ['required', 'image', 'mimes:jpg,jpeg,png,webp', 'max:4096'],
            'method' => ['nullable', 'string'],
            'note' => ['nullable', 'string', 'max:300'],
        ], [
            'proof.required' => 'Pilih foto/screenshot bukti pembayaran.',
            'proof.image' => 'Bukti pembayaran harus berupa gambar.',
            'proof.max' => 'Ukuran gambar maksimal 4 MB.',
        ]);

        $key = 'proof-upload:'.auth()->id();
        if (\Illuminate\Support\Facades\RateLimiter::tooManyAttempts($key, 6)) {
            $this->addError('proof', 'Terlalu sering mengunggah. Coba lagi beberapa saat.');

            return;
        }
        \Illuminate\Support\Facades\RateLimiter::hit($key, 300);

        try {
            $orders->uploadProof($this->order, $this->proof, $this->method === 'qris' ? 0 : ($this->method ? (int) $this->method : null), $this->note);
        } catch (RuntimeException $e) {
            $this->addError('proof', $e->getMessage());

            return;
        }

        $this->reset('proof', 'note');
        $this->order->refresh();
        $this->dispatch('toast', type: 'success', message: 'Bukti pembayaran terkirim! Kami akan memverifikasi secepatnya.');
    }

    public function cancel(OrderService $orders): void
    {
        abort_unless($this->order->user_id === auth()->id(), 403);
        $orders->cancel($this->order);
        $this->order->refresh();
        $this->dispatch('toast', type: 'info', message: 'Pesanan dibatalkan.');
    }

    public function render()
    {
        $this->order->load('items');
        $tryouts = $this->order->status === Order::STATUS_PAID
            ? Tryout::query()->whereIn('id', $this->order->tryoutIds())->with('category')->get()
            : collect();
        $total = rupiah($this->order->total);
        $waText = strtr((string) setting('whatsapp_message'), [
            '{nama}' => auth()->user()->name,
            '{kode}' => $this->order->code,
            '{total}' => $total,
        ]);

        return view('livewire.shop.order-payment', [
            'methods' => PaymentMethod::query()->active()->get(),
            'tryouts' => $tryouts,
            'waLink' => wa_link(setting('contact_whatsapp'), $waText),
        ])->title('Pesanan '.$this->order->code);
    }
}
