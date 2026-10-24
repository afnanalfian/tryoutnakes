<?php

namespace App\Livewire\Admin\Tryouts;

use App\Livewire\Concerns\Paginates;
use App\Models\Category;
use App\Models\Tryout;
use Illuminate\Support\Str;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Url;
use Livewire\Component;

#[Layout('components.layouts.admin')]
class Index extends Component
{
    use Paginates;

    #[Url]
    public string $category = '';
    #[Url]
    public string $status = '';
    #[Url(as: 'q')]
    public string $search = '';

    public function updating($name): void
    {
        $this->resetPage();
    }

    public function toggle(int $id, string $field): void
    {
        abort_unless(in_array($field, ['is_published', 'is_free'], true), 400);
        $t = Tryout::query()->withCount('questions')->findOrFail($id);
        if ($field === 'is_published' && ! $t->is_published && $t->questions_count === 0) {
            $this->dispatch('toast', type: 'error', message: 'Tambahkan soal terlebih dahulu sebelum menerbitkan tryout.');

            return;
        }
        $t->update([$field => ! $t->{$field}, 'published_at' => $field === 'is_published' && ! $t->is_published ? ($t->published_at ?? now()) : $t->published_at]);
    }

    public function duplicate(int $id)
    {
        $t = Tryout::query()->with('questions')->findOrFail($id);
        $copy = $t->replicate(['published_at']);
        $copy->title = $t->title.' (Salinan)';
        $copy->slug = Str::slug($copy->title).'-'.Str::lower(Str::random(4));
        $copy->is_published = false;
        $copy->image = null; // hindari berbagi file gambar antar tryout
        $copy->save();
        $copy->questions()->sync($t->questions->mapWithKeys(fn ($q) => [$q->id => ['order' => $q->pivot->order]])->all());
        session()->flash('success', 'Tryout diduplikasi.');

        return $this->redirect(route('admin.tryouts.edit', $copy), navigate: true);
    }

    public function delete(int $id): void
    {
        Tryout::query()->findOrFail($id)->delete();
        $this->dispatch('toast', type: 'success', message: 'Tryout dihapus (riwayat nilai peserta tetap tersimpan).');
    }

    public function render()
    {
        $tryouts = Tryout::query()->with('category')->withCount(['questions', 'attempts'])
            ->when($this->category, fn ($q) => $q->where('category_id', $this->category))
            ->when($this->status === 'published', fn ($q) => $q->where('is_published', true))
            ->when($this->status === 'draft', fn ($q) => $q->where('is_published', false))
            ->when($this->status === 'free', fn ($q) => $q->where('is_free', true))
            ->when($this->search, fn ($q) => $q->where('title', 'like', "%{$this->search}%"))
            ->orderBy('category_id')->ordered()->paginate(20);

        return view('livewire.admin.tryouts.index', [
            'tryouts' => $tryouts,
            'categories' => Category::query()->ordered()->get(),
        ])->title('Tryout');
    }
}
