<?php

namespace App\Livewire\Admin\Users;

use App\Models\Attempt;
use App\Models\Package;
use App\Models\Tryout;
use App\Models\User;
use App\Models\UserTryout;
use App\Notifications\AppNotification;
use App\Services\OrderService;
use Livewire\Attributes\Layout;
use Livewire\Component;

#[Layout('components.layouts.admin')]
class Show extends Component
{
    public User $user;
    public array $grantTryouts = [];
    public ?int $grantPackage = null;
    public bool $notify = true;

    public function mount(User $user): void
    {
        $this->user = $user;
    }

    public function grant(OrderService $orders): void
    {
        $ids = collect($this->grantTryouts)->map(fn ($i) => (int) $i);
        if ($this->grantPackage) {
            $ids = $ids->merge(Package::query()->findOrFail($this->grantPackage)->tryouts()->pluck('tryouts.id'));
        }
        if ($ids->isEmpty()) {
            $this->addError('grantTryouts', 'Pilih minimal satu tryout atau paket.');

            return;
        }
        $n = $orders->grantTryouts($this->user, $ids->unique()->all(), null, 'grant', auth()->id());
        if ($n && $this->notify) {
            $this->user->notify(new AppNotification('grant', 'Kamu mendapat akses tryout baru 🎁', "Admin memberikan akses ke {$n} tryout. Selamat berlatih!", route('my-tryouts.index'), 'Lihat Tryout'));
        }
        $this->reset('grantTryouts', 'grantPackage');
        $this->dispatch('close-modal', 'grant');
        $this->dispatch('toast', type: 'success', message: "{$n} akses tryout ditambahkan.");
    }

    public function revoke(int $entitlementId): void
    {
        UserTryout::query()->where('user_id', $this->user->id)->whereKey($entitlementId)->delete();
        $this->dispatch('toast', type: 'success', message: 'Akses dicabut.');
    }

    public function render()
    {
        return view('livewire.admin.users.show', [
            'entitlements' => $this->user->entitlements()->with('tryout.category', 'order:id,code')->latest()->get(),
            'attempts' => Attempt::query()->where('user_id', $this->user->id)->with('tryout:id,title')->latest('id')->limit(20)->get(),
            'orders' => $this->user->orders()->latest()->limit(10)->get(),
            'tryouts' => Tryout::query()->with('category:id,name,type')->ordered()->get(['id', 'title', 'category_id']),
            'packages' => Package::query()->ordered()->get(['id', 'name']),
        ])->title($this->user->name);
    }
}
