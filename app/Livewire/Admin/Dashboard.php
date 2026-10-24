<?php

namespace App\Livewire\Admin;

use App\Models\Attempt;
use App\Models\Order;
use App\Models\Question;
use App\Models\Tryout;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Livewire\Attributes\Layout;
use Livewire\Component;

#[Layout('components.layouts.admin')]
class Dashboard extends Component
{
    public function render()
    {
        $monthStart = now()->startOfMonth();
        $paid = Order::query()->where('status', Order::STATUS_PAID);

        // Pendapatan 14 hari terakhir
        $days = collect(range(13, 0))->map(fn ($d) => now()->subDays($d)->toDateString());
        $rows = (clone $paid)->where('paid_at', '>=', now()->subDays(13)->startOfDay())
            ->get(['paid_at', 'total'])->groupBy(fn ($o) => $o->paid_at->toDateString())->map->sum('total');

        $topTryouts = Attempt::query()->select('tryout_id', DB::raw('COUNT(*) as total'))
            ->where('status', Attempt::STATUS_SUBMITTED)->groupBy('tryout_id')->orderByDesc('total')->limit(5)
            ->with('tryout:id,title,category_id', 'tryout.category:id,name,type,color,icon')->get();

        return view('livewire.admin.dashboard', [
            'stats' => [
                'revenue_month' => (int) (clone $paid)->where('paid_at', '>=', $monthStart)->sum('total'),
                'revenue_all' => (int) (clone $paid)->sum('total'),
                'waiting' => Order::query()->where('status', Order::STATUS_WAITING)->count(),
                'pending' => Order::query()->where('status', Order::STATUS_PENDING)->count(),
                'users' => User::query()->where('role', 'user')->count(),
                'users_new' => User::query()->where('role', 'user')->where('created_at', '>=', now()->subDays(7))->count(),
                'attempts_today' => Attempt::query()->whereDate('started_at', today())->count(),
                'running' => Attempt::query()->where('status', Attempt::STATUS_IN_PROGRESS)->where('deadline_at', '>', now())->count(),
                'questions' => Question::query()->count(),
                'tryouts' => Tryout::query()->published()->count(),
            ],
            'chartLabels' => $days->map(fn ($d) => \Carbon\Carbon::parse($d)->format('d/m'))->all(),
            'chartValues' => $days->map(fn ($d) => round(($rows[$d] ?? 0) / 1000))->all(),
            'waitingOrders' => Order::query()->where('status', Order::STATUS_WAITING)->with('user:id,name')->oldest('proof_uploaded_at')->limit(6)->get(),
            'recentUsers' => User::query()->where('role', 'user')->latest()->limit(5)->get(),
            'topTryouts' => $topTryouts,
        ])->title('Dashboard');
    }
}
