<?php

namespace App\Livewire\Admin\Tryouts;

use App\Models\Category;
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

    public ?Tryout $tryout = null;
    public $category_id = '';
    public string $title = '';
    public string $slug = '';
    public string $description = '';
    public $duration_minutes = 100;
    public $scale_max = 100;
    public array $pg = ['twk' => 65, 'tiu' => 80, 'tkp' => 166, 'score' => ''];
    public bool $is_free = false;
    public bool $is_published = false;
    public bool $shuffle_questions = false;
    public bool $shuffle_options = false;
    public bool $show_explanation = true;
    public $max_attempts = 3;
    public $sort_order = 0;
    public $image;

    public function mount(?Tryout $tryout = null): void
    {
        $this->category_id = (string) (request('kategori') ?: Category::query()->ordered()->value('id'));
        if ($tryout?->exists) {
            $this->tryout = $tryout;
            $this->fill($tryout->only(['title', 'slug', 'duration_minutes', 'is_free', 'is_published', 'shuffle_questions', 'shuffle_options', 'show_explanation', 'max_attempts', 'sort_order']));
            $this->category_id = (string) $tryout->category_id;
            $this->description = (string) $tryout->description;
            $this->scale_max = $tryout->scale_max ?? 100;
            $this->pg = array_merge($this->pg, array_map('strval', $tryout->passing_grade ?? []));
        }
    }

    public function updatedTitle(string $v): void
    {
        if (! $this->tryout) {
            $this->slug = Str::slug($v);
        }
    }

    public function updatedCategoryId(): void
    {
        $cat = Category::query()->find($this->category_id);
        if (! $this->tryout && $cat) {
            $this->duration_minutes = $cat->isSkd() ? 100 : 120;
        }
    }

    public function save(bool $toQuestions = false)
    {
        $this->slug = Str::slug($this->slug ?: $this->title);
        $cat = Category::query()->findOrFail($this->category_id);

        $rules = [
            'category_id' => 'required|exists:categories,id',
            'title' => 'required|string|max:150',
            'slug' => ['required', 'alpha_dash', 'max:170', Rule::unique('tryouts', 'slug')->ignore($this->tryout?->id)],
            'description' => 'nullable|string|max:2000',
            'duration_minutes' => 'required|integer|min:1|max:600',
            'max_attempts' => 'required|integer|min:0|max:100',
            'sort_order' => 'nullable|integer|min:0',
            'image' => 'nullable|image|max:3072',
        ];
        if ($cat->isSkd()) {
            $rules += ['pg.twk' => 'required|integer|min:0', 'pg.tiu' => 'required|integer|min:0', 'pg.tkp' => 'required|integer|min:0'];
        } else {
            $rules += ['scale_max' => 'required|numeric|min:1|max:100000', 'pg.score' => 'nullable|numeric|min:0'];
        }
        $this->validate($rules, [], ['scale_max' => 'skala nilai', 'pg.twk' => 'passing TWK', 'pg.tiu' => 'passing TIU', 'pg.tkp' => 'passing TKP']);

        $t = $this->tryout ?? new Tryout;
        $data = [
            'category_id' => $cat->id, 'title' => $this->title, 'slug' => $this->slug, 'description' => $this->description ?: null,
            'duration_minutes' => (int) $this->duration_minutes,
            'scale_max' => $cat->isSkd() ? null : (float) $this->scale_max,
            'passing_grade' => $cat->isSkd()
                ? ['twk' => (int) $this->pg['twk'], 'tiu' => (int) $this->pg['tiu'], 'tkp' => (int) $this->pg['tkp']]
                : ($this->pg['score'] !== '' && $this->pg['score'] !== null ? ['score' => (float) $this->pg['score']] : null),
            'is_free' => $this->is_free, 'shuffle_questions' => $this->shuffle_questions, 'shuffle_options' => $this->shuffle_options,
            'show_explanation' => $this->show_explanation, 'max_attempts' => (int) $this->max_attempts, 'sort_order' => (int) $this->sort_order,
        ];
        $hasQuestions = $t->exists && $t->questions()->exists();
        $data['is_published'] = $this->is_published && $hasQuestions;
        if ($data['is_published'] && ! $t->published_at) {
            $data['published_at'] = now();
        }
        if ($this->image) {
            ImageService::delete($t->image);
            $data['image'] = ImageService::store($this->image, 'tryouts', 1400);
        }
        $t->fill($data)->save();

        $msg = 'Tryout disimpan.';
        if ($this->is_published && ! $hasQuestions) {
            $msg .= ' Tryout belum diterbitkan karena belum ada soal.';
        }
        session()->flash('success', $msg);

        return $this->redirect($toQuestions || ! $hasQuestions ? route('admin.tryouts.questions', $t) : route('admin.tryouts'), navigate: true);
    }

    public function removeImage(): void
    {
        if ($this->tryout) {
            ImageService::delete($this->tryout->image);
            $this->tryout->update(['image' => null]);
        }
    }

    public function render()
    {
        $categories = Category::query()->ordered()->get();

        return view('livewire.admin.tryouts.form', [
            'categories' => $categories,
            'cat' => $categories->firstWhere('id', (int) $this->category_id),
        ])->title($this->tryout ? 'Edit Tryout' : 'Tryout Baru');
    }
}
