<?php

namespace App\Livewire\Admin\Packages;

use App\Models\Package;
use Livewire\Attributes\Layout;
use Livewire\Component;

#[Layout('components.layouts.admin')]
class Index extends Component
{
    public function toggle(int $id, string $field): void
    {
        abort_unless(in_array($field, ['is_active', 'is_featured'], true), 400);
        $p = Package::query()->findOrFail($id);
        $p->update([$field => ! $p->{$field}]);
    }

    public function delete(int $id): void
    {
        Package::query()->findOrFail($id)->delete();
        $this->dispatch('toast', type: 'success', message: 'Paket dihapus.');
    }

    public function render()
    {
        return view('livewire.admin.packages.index', [
            'packages' => Package::query()->with('tryouts.category')->ordered()->get(),
        ])->title('Paket');
    }
}
