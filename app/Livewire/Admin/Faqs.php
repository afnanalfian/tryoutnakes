<?php

namespace App\Livewire\Admin;

use App\Models\Faq;
use Livewire\Attributes\Layout;
use Livewire\Component;

#[Layout('components.layouts.admin')]
class Faqs extends Component
{
    public ?int $editingId = null;
    public array $form = ['question' => '', 'answer' => '', 'is_active' => true, 'sort_order' => 0];

    public function create(): void
    {
        $this->editingId = null;
        $this->form = ['question' => '', 'answer' => '', 'is_active' => true, 'sort_order' => (int) Faq::query()->max('sort_order') + 1];
        $this->resetValidation();
        $this->dispatch('open-modal', 'faq-form');
    }

    public function edit(int $id): void
    {
        $f = Faq::query()->findOrFail($id);
        $this->editingId = $f->id;
        $this->form = $f->only(['question', 'answer', 'is_active', 'sort_order']);
        $this->dispatch('open-modal', 'faq-form');
    }

    public function save(): void
    {
        $this->validate(['form.question' => 'required|string|max:255', 'form.answer' => 'required|string|max:3000', 'form.sort_order' => 'nullable|integer']);
        $data = $this->form;
        $data['is_active'] = (bool) ($data['is_active'] ?? false);
        Faq::query()->updateOrCreate(['id' => $this->editingId], $data);
        $this->dispatch('close-modal', 'faq-form');
        $this->dispatch('toast', type: 'success', message: 'FAQ disimpan.');
    }

    public function reorder(array $ids): void
    {
        foreach ($ids as $i => $id) {
            Faq::query()->whereKey($id)->update(['sort_order' => $i]);
        }
        \App\Support\PublicCache::flush();
    }

    public function delete(int $id): void
    {
        Faq::query()->whereKey($id)->delete();
        \App\Support\PublicCache::flush();
    }

    public function render()
    {
        return view('livewire.admin.faqs', ['faqs' => Faq::query()->orderBy('sort_order')->get()])->title('FAQ');
    }
}
