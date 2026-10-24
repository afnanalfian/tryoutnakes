<?php

namespace App\Livewire\Admin\Tryouts;

use App\Livewire\Concerns\Paginates;
use App\Models\Attempt;
use App\Models\Tryout;
use Livewire\Attributes\Layout;
use Livewire\Component;

#[Layout('components.layouts.admin')]
class Results extends Component
{
    use Paginates;

    public Tryout $tryout;
    public bool $firstOnly = true;
    public string $search = '';

    public function mount(Tryout $tryout): void
    {
        $this->tryout = $tryout->load('category');
    }

    public function updating(): void
    {
        $this->resetPage();
    }

    public function render()
    {
        $base = Attempt::query()->where('tryout_id', $this->tryout->id)->where('status', Attempt::STATUS_SUBMITTED)
            ->when($this->firstOnly, fn ($q) => $q->where('attempt_no', 1));

        $stats = (clone $base)->selectRaw('COUNT(*) as n, AVG(score) as avg, MAX(score) as max, MIN(score) as min, AVG(duration_seconds) as dur, SUM(CASE WHEN is_passed = 1 THEN 1 ELSE 0 END) as passed')->first();

        $rows = (clone $base)->with('user:id,name,email')
            ->when($this->search, fn ($q) => $q->whereHas('user', fn ($u) => $u->where('name', 'like', "%{$this->search}%")->orWhere('email', 'like', "%{$this->search}%")))
            ->orderByDesc('score')->orderByDesc('score_tkp')->orderByDesc('score_tiu')->orderBy('duration_seconds')
            ->paginate(25);

        return view('livewire.admin.tryouts.results', [
            'rows' => $rows,
            'stats' => $stats,
            'running' => Attempt::query()->where('tryout_id', $this->tryout->id)->where('status', Attempt::STATUS_IN_PROGRESS)->where('deadline_at', '>', now())->count(),
        ])->title('Hasil: '.$this->tryout->title);
    }
}
