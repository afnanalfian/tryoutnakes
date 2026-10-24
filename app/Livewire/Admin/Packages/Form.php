<?php

namespace App\Livewire\Admin\Packages;

use App\Models\Category;
use App\Models\Package;
use App\Models\Tryout;
use App\Support\ImageService;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;
use Livewire\Attributes\Layout;
use Livewire\Component;
use Livewire\WithFileUploads;

#[Layout('components.layouts.admin')]
class Form extends Component
{
    use WithFileUploads;

    public ?Package $package = null;
    public string $name = '';
    public string $slug = '';
    public string $tagline = '';
    public string $description = '';
    public $price = '';
    public string $badge = '';
    public bool $is_featured = false;
    public bool $is_active = true;
    public int $sort_order = 0;
    public array $selected = [];
    public $image;

    public function mount(?Package $package = null): void
    {
        if ($package?->exists) {
            $this->package = $package;
            $this->fill($package->only(['name', 'slug', 'is_featured', 'is_active', 'sort_order']));
            $this->tagline = (string) $package->tagline;
            $this->description = (string) $package->description;
            $this->badge = (string) $package->badge;
            $this->price = $package->price;
            $this->selected = $package->tryouts()->pluck('tryouts.id')->map(fn ($i) => (string) $i)->all();
        }
    }

    public function updatedName(string $v): void
    {
        if (! $this->package) {
            $this->slug = Str::slug($v);
        }
    }

    public function selectCategory(int $categoryId, bool $on): void
    {
        $ids = Tryout::query()->where('category_id', $categoryId)->pluck('id')->map(fn ($i) => (string) $i);
        $this->selected = $on
            ? collect($this->selected)->merge($ids)->unique()->values()->all()
            : collect($this->selected)->diff($ids)->values()->all();
    }

    public function save()
    {
        $this->slug = Str::slug($this->slug ?: $this->name);
        $this->validate([
            'name' => 'required|string|max:120',
            'slug' => ['required', 'alpha_dash', 'max:140', Rule::unique('packages', 'slug')->ignore($this->package?->id)],
            'tagline' => 'nullable|string|max:200',
            'description' => 'nullable|string|max:5000',
            'price' => 'required|integer|min:0|max:100000000',
            'badge' => 'nullable|string|max:30',
            'selected' => 'required|array|min:1',
            'image' => 'nullable|image|max:3072',
        ], ['selected.required' => 'Pilih minimal satu tryout untuk paket ini.']);

        // Peringatan: kombinasi identik dengan paket lain
        $ids = collect($this->selected)->map(fn ($i) => (int) $i)->sort()->values();
        $dup = Package::query()->when($this->package, fn ($q) => $q->whereKeyNot($this->package->id))->with('tryouts:id')->get()
            ->first(fn ($p) => $p->tryouts->pluck('id')->sort()->values()->all() === $ids->all());

        $p = $this->package ?? new Package;
        $data = [
            'name' => $this->name, 'slug' => $this->slug, 'tagline' => $this->tagline ?: null, 'description' => $this->description ?: null,
            'price' => (int) $this->price, 'badge' => $this->badge ?: null, 'is_featured' => $this->is_featured, 'is_active' => $this->is_active,
            'sort_order' => $this->sort_order,
        ];
        if ($this->image) {
            ImageService::delete($p->image);
            $data['image'] = ImageService::store($this->image, 'packages', 1400);
        }
        $p->fill($data)->save();
        $p->tryouts()->sync($ids->all());

        session()->flash('success', 'Paket disimpan.'.($dup ? ' Catatan: isi paket identik dengan "'.$dup->name.'" — konversi otomatis akan memilih yang termurah.' : ''));

        return $this->redirect(route('admin.packages'), navigate: true);
    }

    public function removeImage(): void
    {
        if ($this->package) {
            ImageService::delete($this->package->image);
            $this->package->update(['image' => null]);
        }
    }

    public function render()
    {
        $categories = Category::query()->ordered()->with(['tryouts' => fn ($q) => $q->ordered()->with('category')->withCount('questions')])->get();
        $all = $categories->flatMap->tryouts->keyBy('id');
        $normal = collect($this->selected)->sum(fn ($id) => $all->get((int) $id)?->price() ?? 0);

        return view('livewire.admin.packages.form', compact('categories', 'normal'))->title($this->package ? 'Edit Paket' : 'Paket Baru');
    }
}
