<?php

namespace App\Livewire\Admin;

use App\Models\Category;
use App\Models\User;
use App\Notifications\AppNotification;
use Illuminate\Support\Facades\Notification;
use Livewire\Attributes\Layout;
use Livewire\Component;

#[Layout('components.layouts.admin')]
class Broadcast extends Component
{
    public string $title = '';
    public string $body = '';
    public string $url = '';
    public string $action = '';
    public string $audience = 'all';  // all | buyers | category:{id}
    public bool $email = false;

    public function audienceQuery()
    {
        $q = User::query()->where('role', 'user')->where('is_active', true);
        if ($this->audience === 'buyers') {
            $q->whereHas('entitlements');
        } elseif (str_starts_with($this->audience, 'category:')) {
            $cid = (int) substr($this->audience, 9);
            $q->whereHas('entitlements.tryout', fn ($t) => $t->where('category_id', $cid));
        }

        return $q;
    }

    public function send(): void
    {
        $this->validate([
            'title' => 'required|string|max:120',
            'body' => 'required|string|max:1000',
            'url' => 'nullable|url|max:255',
            'action' => 'nullable|string|max:40',
        ]);

        $count = 0;
        $this->audienceQuery()->select('id', 'name', 'email')->chunkById(200, function ($users) use (&$count) {
            Notification::send($users, new AppNotification('broadcast', $this->title, $this->body, $this->url ?: null, $this->action ?: null, $this->email));
            $count += $users->count();
        });

        $this->reset('title', 'body', 'url', 'action');
        $this->dispatch('toast', type: 'success', message: "Pengumuman dikirim ke {$count} peserta.");
    }

    public function render()
    {
        return view('livewire.admin.broadcast', [
            'categories' => Category::query()->ordered()->get(),
            'recipients' => $this->audienceQuery()->count(),
        ])->title('Broadcast Notifikasi');
    }
}
