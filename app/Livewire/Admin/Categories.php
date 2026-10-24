<?php

namespace App\Livewire\Admin;

use App\Models\Category;
use App\Support\ImageService;
use App\View\Components\Icon;
use Livewire\Attributes\Layout;
use Livewire\Component;
use Livewire\WithFileUploads;

#[Layout('components.layouts.admin')]
class Categories extends Component
{
    use WithFileUploads;

    public ?int $editingId = null;
    public array $form = [];
    public $image;

    public function edit(int $id): void
    {
        $c = Category::query()->findOrFail($id);
        $this->editingId = $c->id;
        $this->form = $c->only(['name', 'slug', 'tagline', 'description', 'icon', 'color', 'price', 'sort_order', 'is_active']);
        $this->image = null;
        $this->resetValidation();
        $this->dispatch('open-modal', 'category-form');
    }

    public function save(): void
    {
        $c = Category::query()->findOrFail($this->editingId);
        $this->validate([
            'form.name' => 'required|string|max:80',
            'form.slug' => 'required|alpha_dash|max:80|unique:categories,slug,'.$c->id,
            'form.tagline' => 'nullable|string|max:150',
            'form.description' => 'nullable|string|max:2000',
            'form.icon' => 'required|string|in:'.implode(',', array_keys(Icon::PATHS)),
            'form.color' => ['required', 'regex:/^#[0-9a-fA-F]{6}$/'],
            'form.price' => 'required|integer|min:0|max:100000000',
            'form.sort_order' => 'nullable|integer|min:0',
            'image' => 'nullable|image|max:3072',
        ]);
        $data = $this->form;
        $data['is_active'] = (bool) ($data['is_active'] ?? false);
        if ($this->image) {
            ImageService::delete($c->image);
            $data['image'] = ImageService::store($this->image, 'categories', 1400);
        }
        $c->update($data);
        $this->dispatch('close-modal', 'category-form');
        $this->dispatch('toast', type: 'success', message: 'Kategori diperbarui.');
    }

    public function removeImage(int $id): void
    {
        $c = Category::query()->findOrFail($id);
        ImageService::delete($c->image);
        $c->update(['image' => null]);
        $this->dispatch('toast', type: 'success', message: 'Gambar dihapus, kembali ke template bawaan.');
    }

    public function render()
    {
        return view('livewire.admin.categories', [
            'categories' => Category::query()->ordered()->withCount(['tryouts', 'questions'])->get(),
            'icons' => array_keys(Icon::PATHS),
        ])->title('Kategori & Harga');
    }
}
