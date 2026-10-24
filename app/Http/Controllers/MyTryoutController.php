<?php

namespace App\Http\Controllers;

use App\Models\Attempt;
use App\Models\Category;
use App\Models\Tryout;
use App\Services\RankingService;
use Illuminate\Http\Request;

class MyTryoutController extends Controller
{
    /** Hanya kategori yang user punya aksesnya yang ditampilkan. */
    public function index(Request $request)
    {
        $user = $request->user();
        $owned = $user->ownedTryoutIds();

        $tryouts = Tryout::query()->published()
            ->when(! $user->isAdmin(), fn ($q) => $q->where(fn ($w) => $w->whereIn('id', $owned)->orWhere('is_free', true)))
            ->with('category')->withCount('questions')->ordered()->get();

        $categories = Category::query()->whereIn('id', $tryouts->pluck('category_id')->unique())->ordered()->get();
        $active = $request->string('kategori')->toString() ?: $categories->first()?->slug;
        $current = $categories->firstWhere('slug', $active) ?? $categories->first();

        $latest = Attempt::query()->where('user_id', $user->id)->where('status', Attempt::STATUS_SUBMITTED)
            ->whereIn('tryout_id', $tryouts->pluck('id'))->orderByDesc('id')->get()->unique('tryout_id')->keyBy('tryout_id');
        $running = Attempt::query()->where('user_id', $user->id)->where('status', Attempt::STATUS_IN_PROGRESS)
            ->where('deadline_at', '>', now())->get()->keyBy('tryout_id');

        return view('user.my-tryouts', [
            'categories' => $categories,
            'current' => $current,
            'tryouts' => $current ? $tryouts->where('category_id', $current->id)->values() : collect(),
            'counts' => $tryouts->countBy('category_id'),
            'latest' => $latest,
            'running' => $running,
        ]);
    }

    public function show(Request $request, Tryout $tryout, RankingService $ranking)
    {
        abort_unless($tryout->is_published || $request->user()->isAdmin(), 404);
        $user = $request->user();
        $tryout->load('category')->loadCount('questions');
        $hasAccess = $user->canAccessTryout($tryout);

        $history = Attempt::query()->where('user_id', $user->id)->where('tryout_id', $tryout->id)->orderByDesc('id')->get();
        $running = $history->first(fn ($a) => $a->isRunning());
        $composition = \Illuminate\Support\Facades\DB::table('question_tryout')
            ->join('questions', 'questions.id', '=', 'question_tryout.question_id')
            ->where('question_tryout.tryout_id', $tryout->id)
            ->groupBy('questions.subtest')
            ->selectRaw('questions.subtest as subtest, COUNT(*) as total')
            ->pluck('total', 'subtest');

        return view('user.tryout-show', [
            'tryout' => $tryout,
            'hasAccess' => $hasAccess,
            'history' => $history,
            'running' => $running,
            'left' => $tryout->attemptsLeftFor($user),
            'composition' => $composition,
            'participants' => $ranking->participants($tryout->id),
        ]);
    }
}
