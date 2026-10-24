<?php

namespace App\Livewire\Admin;

use App\Models\PaymentMethod;
use App\Support\ImageService;
use App\Support\Settings;
use Livewire\Attributes\Layout;
use Livewire\Component;
use Livewire\WithFileUploads;

#[Layout('components.layouts.admin')]
class PaymentMethods extends Component
{
    use WithFileUploads;

    public $qris;
    public string $qrisName = '';
    public string $qrisNote = '';
    public ?int $editingId = null;
    public array $form = ['name' => '', 'account_number' => '', 'account_holder' => '', 'description' => '', 'is_active' => true, 'sort_order' => 0];

    public function mount(): void
    {
        $this->qrisName = (string) setting('qris_name');
        $this->qrisNote = (string) setting('qris_note');
    }

    public function saveQris(Settings $settings): void
    {
        $this->validate(['qris' => 'nullable|image|max:4096', 'qrisName' => 'nullable|string|max:120', 'qrisNote' => 'nullable|string|max:500']);
        $values = ['qris_name' => $this->qrisName, 'qris_note' => $this->qrisNote];
        if ($this->qris) {
            ImageService::delete(setting('qris_image'));
            // QR disimpan PNG tanpa kompresi lossy agar tetap mudah dipindai.
            $values['qris_image'] = ImageService::store($this->qris, 'payments', 1200, 'png');
        }
        $settings->set($values);
        $this->reset('qris');
        $this->dispatch('toast', type: 'success', message: 'QRIS disimpan.');
    }

    public function create(): void
    {
        $this->editingId = null;
        $this->form = ['name' => '', 'account_number' => '', 'account_holder' => '', 'description' => '', 'is_active' => true, 'sort_order' => PaymentMethod::query()->max('sort_order') + 1];
        $this->resetValidation();
        $this->dispatch('open-modal', 'method-form');
    }

    public function edit(int $id): void
    {
        $m = PaymentMethod::query()->findOrFail($id);
        $this->editingId = $m->id;
        $this->form = $m->only(['name', 'account_number', 'account_holder', 'description', 'is_active', 'sort_order']);
        $this->resetValidation();
        $this->dispatch('open-modal', 'method-form');
    }

    public function save(): void
    {
        $this->validate([
            'form.name' => 'required|string|max:60',
            'form.account_number' => 'required|string|max:60',
            'form.account_holder' => 'nullable|string|max:100',
            'form.description' => 'nullable|string|max:500',
            'form.sort_order' => 'nullable|integer|min:0',
        ], [], ['form.name' => 'metode', 'form.account_number' => 'no. rekening / no. HP']);
        $data = $this->form;
        $data['is_active'] = (bool) ($data['is_active'] ?? false);
        PaymentMethod::query()->updateOrCreate(['id' => $this->editingId], $data);
        $this->dispatch('close-modal', 'method-form');
        $this->dispatch('toast', type: 'success', message: 'Metode pembayaran disimpan.');
    }

    public function toggle(int $id): void
    {
        $m = PaymentMethod::query()->findOrFail($id);
        $m->update(['is_active' => ! $m->is_active]);
    }

    public function delete(int $id): void
    {
        PaymentMethod::query()->whereKey($id)->delete();
        $this->dispatch('toast', type: 'success', message: 'Metode dihapus.');
    }

    public function render()
    {
        return view('livewire.admin.payment-methods', [
            'methods' => PaymentMethod::query()->orderBy('sort_order')->get(),
        ])->title('Metode Pembayaran');
    }
}
