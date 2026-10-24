<?php

namespace App\Livewire\Admin;

use App\Models\Banner;
use App\Support\ImageService;
use Livewire\Attributes\Layout;
use Livewire\Component;
use Livewire\WithFileUploads;

#[Layout('components.layouts.admin')]
class Banners extends Component
{
    use WithFileUploads;

    public ?int $editingId = null;
    public array $form = [];
    public $image;
    public $imageMobile;

    protected function blank(): array
    {
        return ['title' => '', 'subtitle' => '', 'link_url' => '', 'button_text' => '', 'is_active' => true, 'starts_at' => '', 'ends_at' => '', 'sort_order' => 0];
    }

    public function mount(): void
    {
        $this->form = $this->blank();
    }

    public function create(): void
    {
        $this->editingId = null;
        $this->form = $this->blank();
        $this->reset('image', 'imageMobile');
        $this->resetValidation();
        $this->dispatch('open-modal', 'banner-form');
    }

    public function edit(int $id): void
    {
        $b = Banner::query()->findOrFail($id);
        $this->editingId = $b->id;
        $this->form = [
            'title' => $b->title, 'subtitle' => $b->subtitle, 'link_url' => $b->link_url, 'button_text' => $b->button_text,
            'is_active' => $b->is_active, 'sort_order' => $b->sort_order,
            'starts_at' => $b->starts_at?->format('Y-m-d\TH:i'), 'ends_at' => $b->ends_at?->format('Y-m-d\TH:i'),
        ];
        $this->reset('image', 'imageMobile');
        $this->resetValidation();
        $this->dispatch('open-modal', 'banner-form');
    }

    public function save(): void
    {
        $this->validate([
            'form.title' => 'required|string|max:120',
            'form.subtitle' => 'nullable|string|max:200',
            'form.link_url' => 'nullable|url|max:255',
            'form.button_text' => 'nullable|string|max:40',
            'form.starts_at' => 'nullable|date',
            'form.ends_at' => 'nullable|date|after_or_equal:form.starts_at',
            'image' => ($this->editingId ? 'nullable' : 'required').'|image|max:4096',
            'imageMobile' => 'nullable|image|max:3072',
        ], [], ['image' => 'gambar banner']);

        $b = $this->editingId ? Banner::query()->findOrFail($this->editingId) : new Banner;
        $data = $this->form;
        $data['is_active'] = (bool) ($data['is_active'] ?? false);
        $data['starts_at'] = $data['starts_at'] ?: null;
        $data['ends_at'] = $data['ends_at'] ?: null;
        if ($this->image) {
            ImageService::delete($b->image);
            $data['image'] = ImageService::store($this->image, 'banners', 1920);
        }
        if ($this->imageMobile) {
            ImageService::delete($b->image_mobile);
            $data['image_mobile'] = ImageService::store($this->imageMobile, 'banners', 900);
        }
        $b->fill($data)->save();
        $this->dispatch('close-modal', 'banner-form');
        $this->dispatch('toast', type: 'success', message: 'Banner disimpan.');
    }

    public function toggle(int $id): void
    {
        $b = Banner::query()->findOrFail($id);
        $b->update(['is_active' => ! $b->is_active]);
    }

    public function delete(int $id): void
    {
        $b = Banner::query()->findOrFail($id);
        ImageService::delete($b->image);
        ImageService::delete($b->image_mobile);
        $b->delete();
        $this->dispatch('toast', type: 'success', message: 'Banner dihapus.');
    }

    public function render()
    {
        return view('livewire.admin.banners', ['banners' => Banner::query()->orderBy('sort_order')->latest()->get()])->title('Banner Landing');
    }
}
