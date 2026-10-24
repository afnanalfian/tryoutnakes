<?php

namespace App\Http\Controllers;

use App\Models\Attempt;
use App\Models\Order;
use App\Models\Tryout;
use App\Services\ProgressService;
use Illuminate\Http\Request;

class DashboardController extends Controller
{
    public function index(Request $request, ProgressService $progress)
    {
        $user = $request->user();
        if ($user->isAdmin() && ! $request->boolean('peserta')) {
            return redirect()->route('admin.dashboard');
        }

        $owned = $user->ownedTryoutIds();
        $attempts = Attempt::query()->where('user_id', $user->id)->where('status', Attempt::STATUS_SUBMITTED);

        $recent = (clone $attempts)->with('tryout.category')->latest('submitted_at')->limit(5)->get();
        $doneIds = (clone $attempts)->distinct()->pluck('tryout_id');

        $todo = Tryout::query()->published()
            ->where(fn ($q) => $q->whereIn('id', $owned)->orWhere('is_free', true))
            ->whereNotIn('id', $doneIds)
            ->with('category')->withCount('questions')->ordered()->limit(4)->get();

        $stats = [
            'owned' => $owned->count(),
            'done' => $doneIds->count(),
            'attempts' => (clone $attempts)->count(),
            'avg' => round((float) (clone $attempts)->avg('percentage'), 1),
            'best' => (float) (clone $attempts)->max('percentage'),
        ];

        $openOrder = Order::query()->where('user_id', $user->id)->whereIn('status', [Order::STATUS_PENDING, Order::STATUS_REJECTED])->latest()->first();

        return view('user.dashboard', [
            'user' => $user,
            'active' => $user->activeAttempt(),
            'recent' => $recent,
            'todo' => $todo,
            'stats' => $stats,
            'openOrder' => $openOrder,
            'progressCats' => $progress->categoriesWithAttempts($user),
        ]);
    }
}
