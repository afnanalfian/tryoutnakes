<?php

namespace App\Livewire\User;

use App\Livewire\Concerns\Paginates;
use Livewire\Attributes\Layout;
use Livewire\Component;

#[Layout('components.layouts.app')]
class Notifications extends Component
{
    use Paginates;

    public bool $onlyUnread = false;

    public function markAllRead(): void
    {
        auth()->user()->unreadNotifications()->update(['read_at' => now()]);
    }

    public function delete(string $id): void
    {
        auth()->user()->notifications()->whereKey($id)->delete();
    }

    public function clearRead(): void
    {
        auth()->user()->readNotifications()->delete();
    }

    public function render()
    {
        $q = auth()->user()->notifications()->latest();
        if ($this->onlyUnread) {
            $q->whereNull('read_at');
        }

        return view('livewire.user.notifications', ['items' => $q->paginate(15)])->title('Notifikasi');
    }
}
