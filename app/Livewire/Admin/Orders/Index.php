<?php

namespace App\Livewire\Admin\Orders;

use App\Livewire\Concerns\Paginates;
use App\Models\Order;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Url;
use Livewire\Component;

#[Layout('components.layouts.admin')]
class Index extends Component
{
    use Paginates;

    #[Url]
    public string $status = 'waiting';
    #[Url(as: 'q')]
    public string $search = '';

    public function updating($name): void
    {
        if (in_array($name, ['status', 'search'], true)) {
            $this->resetPage();
        }
    }

    public function render()
    {
        $orders = Order::query()->with('user:id,name,email,phone', 'items')
            ->when($this->status, fn ($q) => $q->where('status', $this->status))
            ->when($this->search, fn ($q) => $q->where(fn ($w) => $w->where('code', 'like', "%{$this->search}%")
                ->orWhereHas('user', fn ($u) => $u->where('name', 'like', "%{$this->search}%")->orWhere('email', 'like', "%{$this->search}%"))))
            ->orderByRaw($this->status === 'waiting' ? 'proof_uploaded_at asc' : 'created_at desc')
            ->paginate(15);

        $counts = Order::query()->selectRaw('status, COUNT(*) as c')->groupBy('status')->pluck('c', 'status');

        return view('livewire.admin.orders.index', compact('orders', 'counts'))->title('Pesanan');
    }
}
