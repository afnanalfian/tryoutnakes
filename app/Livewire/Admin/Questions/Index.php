<?php

namespace App\Livewire\Admin\Questions;

use App\Livewire\Concerns\Paginates;
use App\Models\Category;
use App\Models\Question;
use App\Models\QuestionTopic;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Url;
use Livewire\Component;

#[Layout('components.layouts.admin')]
class Index extends Component
{
    use Paginates;

    #[Url(as: 'kategori')]
    public $category = '';
    #[Url]
    public string $subtest = '';
    #[Url]
    public string $topic = '';
    #[Url(as: 'q')]
    public string $search = '';
    #[Url]
    public string $difficulty = '';

    public ?int $previewId = null;
    public string $newTopic = '';
    public ?int $renamingTopic = null;
    public string $renameValue = '';
    public array $selected = [];

    public function mount(): void
    {
        if (! $this->category) {
            $this->category = (string) Category::query()->ordered()->value('id');
        }
        $this->ensureSubtest();
    }

    protected function cat(): ?Category
    {
        return Category::query()->find($this->category);
    }

    protected function ensureSubtest(): void
    {
        $subs = $this->cat()?->subtests() ?? ['skb'];
        if (! in_array($this->subtest, $subs, true)) {
            $this->subtest = $subs[0];
        }
    }

    public function updated($name): void
    {
        if ($name === 'category') {
            $this->subtest = '';
            $this->topic = '';
            $this->ensureSubtest();
        }
        if ($name === 'subtest') {
            $this->topic = '';
        }
        if (in_array($name, ['category', 'subtest', 'topic', 'search', 'difficulty'], true)) {
            $this->resetPage();
            $this->selected = [];
        }
    }

    public function preview(int $id): void
    {
        $this->previewId = $id;
        $this->dispatch('open-modal', 'q-preview');
    }

    public function duplicate(int $id): void
    {
        $q = Question::query()->with('options')->findOrFail($id);
        $copy = $q->replicate();
        $copy->created_by = auth()->id();
        $copy->image = \App\Support\ImageService::duplicate($q->image);
        $copy->save();
        foreach ($q->options as $o) {
            $copy->options()->create(['image' => \App\Support\ImageService::duplicate($o->image)] + $o->only(['order', 'option_text', 'is_correct', 'weight']));
        }
        $this->dispatch('toast', type: 'success', message: 'Soal diduplikasi.');
    }

    public function delete(int $id): void
    {
        $q = Question::query()->withCount('tryouts')->findOrFail($id);
        $q->tryouts()->detach();
        $q->delete();
        $this->dispatch('toast', type: 'success', message: 'Soal dihapus'.($q->tryouts_count ? " dan dikeluarkan dari {$q->tryouts_count} tryout." : '.'));
    }

    public function deleteSelected(): void
    {
        $qs = Question::query()->whereIn('id', $this->selected)->get();
        foreach ($qs as $q) {
            $q->tryouts()->detach();
            $q->delete();
        }
        $this->selected = [];
        $this->dispatch('toast', type: 'success', message: $qs->count().' soal dihapus.');
    }

    public function addTopic(): void
    {
        $this->validate(['newTopic' => 'required|string|max:100']);
        QuestionTopic::query()->firstOrCreate(['category_id' => $this->category, 'subtest' => $this->subtest, 'name' => trim($this->newTopic)]);
        $this->newTopic = '';
    }

    public function startRename(int $id): void
    {
        $this->renamingTopic = $id;
        $this->renameValue = (string) QuestionTopic::query()->whereKey($id)->value('name');
    }

    public function saveRename(): void
    {
        $this->validate(['renameValue' => 'required|string|max:100']);
        QuestionTopic::query()->whereKey($this->renamingTopic)->update(['name' => trim($this->renameValue)]);
        $this->renamingTopic = null;
    }

    public function deleteTopic(int $id): void
    {
        QuestionTopic::query()->whereKey($id)->delete();
        if ((string) $this->topic === (string) $id) {
            $this->topic = '';
        }
    }

    public function render()
    {
        $questions = Question::query()->with('topic:id,name', 'options')->withCount('tryouts')
            ->where('category_id', $this->category)->where('subtest', $this->subtest)
            ->when($this->topic === 'none', fn ($q) => $q->whereNull('topic_id'))
            ->when($this->topic && $this->topic !== 'none', fn ($q) => $q->where('topic_id', $this->topic))
            ->when($this->difficulty, fn ($q) => $q->where('difficulty', $this->difficulty))
            ->when($this->search, fn ($q) => $q->where(fn ($w) => $w->where('question_text', 'like', "%{$this->search}%")->orWhere('explanation', 'like', "%{$this->search}%")))
            ->latest('id')->paginate(20);

        $counts = Question::query()->selectRaw('category_id, subtest, COUNT(*) as c')->groupBy('category_id', 'subtest')->get()
            ->groupBy('category_id')->map(fn ($g) => $g->pluck('c', 'subtest'));

        return view('livewire.admin.questions.index', [
            'categories' => Category::query()->ordered()->get(),
            'cat' => $this->cat(),
            'questions' => $questions,
            'topics' => QuestionTopic::query()->where('category_id', $this->category)->where('subtest', $this->subtest)->withCount('questions')->orderBy('name')->get(),
            'counts' => $counts,
            'preview' => $this->previewId ? Question::query()->with('options', 'topic')->find($this->previewId) : null,
        ])->title('Bank Soal');
    }
}
