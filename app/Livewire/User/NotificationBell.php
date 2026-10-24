<?php

namespace App\Livewire\User;

use Livewire\Component;

class NotificationBell extends Component
{
    public function markAllRead(): void
    {
        auth()->user()->unreadNotifications()->update(['read_at' => now()]);
    }

    public function render()
    {
        $user = auth()->user();

        return view('livewire.user.notification-bell', [
            'items' => $user->notifications()->latest()->limit(6)->get(),
            'unread' => $user->unreadNotifications()->count(),
            'active' => $user->isAdmin() ? null : $user->activeAttempt(),
        ]);
    }
}
