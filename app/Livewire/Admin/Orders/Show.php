<?php

namespace App\Livewire\Admin\Orders;

use App\Models\Order;
use App\Models\Tryout;
use App\Services\OrderService;
use Livewire\Attributes\Layout;
use Livewire\Component;

#[Layout('components.layouts.admin')]
class Show extends Component
{
    public Order $order;
    public string $note = '';
    public string $reason = '';

    public function mount(Order $order): void
    {
        $this->order = $order;
    }

    public function approve(OrderService $orders): void
    {
        $orders->approve($this->order, auth()->user(), $this->note ?: null);
        $this->order->refresh();
        $this->dispatch('toast', type: 'success', message: 'Pesanan disetujui. Akses tryout aktif & peserta telah diberi notifikasi.');
    }

    public function reject(OrderService $orders): void
    {
        $this->validate(['reason' => 'required|string|min:5|max:400'], ['reason.required' => 'Tuliskan alasan penolakan agar peserta tahu apa yang harus diperbaiki.']);
        $orders->reject($this->order, auth()->user(), $this->reason);
        $this->order->refresh();
        $this->reset('reason');
        $this->dispatch('close-modal', 'reject');
        $this->dispatch('toast', type: 'success', message: 'Pesanan ditolak dan peserta diberi tahu.');
    }

    public function render()
    {
        $this->order->load('items', 'user', 'verifier:id,name', 'convertedPackage:id,name');

        return view('livewire.admin.orders.show', [
            'tryouts' => Tryout::query()->withTrashed()->whereIn('id', $this->order->tryoutIds())->with('category:id,name,type')->get(),
            'waLink' => wa_link($this->order->user->phone, 'Halo '.$this->order->user->name.', terkait pesanan '.$this->order->code.' di '.setting('site_name').' ...'),
        ])->title('Pesanan '.$this->order->code);
    }
}
