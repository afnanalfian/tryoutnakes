<?php

namespace App\Livewire\Admin\Tryouts;

use App\Livewire\Concerns\Paginates;
use App\Models\Question;
use App\Models\QuestionTopic;
use App\Models\Tryout;
use Livewire\Attributes\Layout;
use Livewire\Component;

#[Layout('components.layouts.admin')]
class Questions extends Component
{
    use Paginates;

    public Tryout $tryout;
    public string $subtest = '';
    public string $topic = '';
    public string $search = '';
    public bool $hideUsed = true;
    public array $picked = [];
    public array $auto = [];
    public ?int $previewId = null;

    public function mount(Tryout $tryout): void
    {
        $this->tryout = $tryout->load('category');
        $this->subtest = $tryout->category->subtests()[0];
        $this->auto = $tryout->category->isSkd() ? config('tryout.skd.composition') : ['skb' => 100];
    }

    public function updating($name): void
    {
        if (in_array($name, ['subtest', 'topic', 'search', 'hideUsed'], true)) {
            $this->resetPage();
        }
        if ($name === 'subtest') {
            $this->topic = '';
        }
    }

    protected function syncWith(array $ids): void
    {
        $this->tryout->questions()->sync(collect($ids)->unique()->values()->mapWithKeys(fn ($id, $i) => [(int) $id => ['order' => $i + 1]])->all());
        if (empty($ids) && $this->tryout->is_published) {
            $this->tryout->update(['is_published' => false]);
        }
        \App\Support\PublicCache::flush();
    }

    protected function currentIds(): array
    {
        return $this->tryout->questions()->pluck('questions.id')->all();
    }

    /** Urutan SKD: TWK → TIU → TKP agar penomoran rapi. */
    protected function sortBySubtest(array $ids): array
    {
        if (! $this->tryout->category->isSkd()) {
            return $ids;
        }
        $subs = Question::query()->whereIn('id', $ids)->pluck('subtest', 'id');
        $rank = ['twk' => 0, 'tiu' => 1, 'tkp' => 2];

        return collect($ids)->sortBy(fn ($id, $pos) => [$rank[$subs[$id] ?? 'tkp'] ?? 3, $pos])->values()->all();
    }

    public function addPicked(): void
    {
        if (! $this->picked) {
            return;
        }
        $ids = $this->sortBySubtest(array_merge($this->currentIds(), array_map('intval', $this->picked)));
        $this->syncWith($ids);
        $n = count($this->picked);
        $this->picked = [];
        $this->dispatch('toast', type: 'success', message: "{$n} soal ditambahkan.");
    }

    public function addOne(int $id): void
    {
        $this->syncWith($this->sortBySubtest(array_merge($this->currentIds(), [$id])));
    }

    public function remove(int $id): void
    {
        $this->syncWith(array_values(array_diff($this->currentIds(), [$id])));
    }

    public function clearAll(): void
    {
        $this->syncWith([]);
        $this->dispatch('toast', type: 'success', message: 'Semua soal dikeluarkan dari tryout.');
    }

    public function reorder(array $ids): void
    {
        $current = $this->currentIds();
        $ids = array_values(array_intersect(array_map('intval', $ids), $current));
        if (count($ids) === count($current)) {
            $this->syncWith($ids);
        }
    }

    /** Isi otomatis secara acak dari bank soal sesuai jumlah per subtes. */
    public function autoFill(): void
    {
        $this->validate(['auto.*' => 'nullable|integer|min:0|max:500']);
        $current = collect($this->currentIds());
        $existingBySub = Question::query()->whereIn('id', $current)->pluck('subtest', 'id')->countBy();
        $added = 0;
        $ids = $current->all();

        foreach ($this->auto as $sub => $target) {
            $need = max(0, (int) $target - (int) ($existingBySub[$sub] ?? 0));
            if (! $need) {
                continue;
            }
            $new = Question::query()->where('category_id', $this->tryout->category_id)->where('subtest', $sub)
                ->whereNotIn('id', $current)->inRandomOrder()->limit($need)->pluck('id')->all();
            $ids = array_merge($ids, $new);
            $added += count($new);
        }
        $this->syncWith($this->sortBySubtest($ids));
        $this->dispatch('toast', type: $added ? 'success' : 'info', message: $added ? "{$added} soal acak ditambahkan." : 'Tidak ada soal tambahan (komposisi terpenuhi atau bank soal kurang).');
    }

    public function preview(int $id): void
    {
        $this->previewId = $id;
        $this->dispatch('open-modal', 'q-preview');
    }

    public function render()
    {
        $selected = $this->tryout->questions()->with('topic:id,name', 'options')->get();
        $selectedIds = $selected->pluck('id');

        $bank = Question::query()->with('topic:id,name', 'options')
            ->where('category_id', $this->tryout->category_id)
            ->where('subtest', $this->subtest)
            ->when($this->topic, fn ($q) => $q->where('topic_id', $this->topic))
            ->when($this->search, fn ($q) => $q->where('question_text', 'like', "%{$this->search}%"))
            ->when($this->hideUsed, fn ($q) => $q->whereNotIn('id', $selectedIds))
            ->withCount('tryouts')
            ->latest('id')->paginate(15);

        $counts = $selected->countBy('subtest');
        $bankCounts = Question::query()->where('category_id', $this->tryout->category_id)->selectRaw('subtest, COUNT(*) as c')->groupBy('subtest')->pluck('c', 'subtest');

        return view('livewire.admin.tryouts.questions', [
            'selected' => $selected,
            'selectedIds' => $selectedIds,
            'bank' => $bank,
            'counts' => $counts,
            'bankCounts' => $bankCounts,
            'topics' => QuestionTopic::query()->where('category_id', $this->tryout->category_id)->where('subtest', $this->subtest)->orderBy('name')->get(),
            'preview' => $this->previewId ? Question::query()->with('options', 'topic')->find($this->previewId) : null,
        ])->title('Soal: '.$this->tryout->title);
    }
}
