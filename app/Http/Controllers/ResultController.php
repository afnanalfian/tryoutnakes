<?php

namespace App\Http\Controllers;

use App\Models\Attempt;
use App\Models\Question;
use App\Services\MotivationService;
use App\Services\RankingService;
use Illuminate\Http\Request;

class ResultController extends Controller
{
    public function index(Request $request)
    {
        $attempts = Attempt::query()->where('user_id', $request->user()->id)
            ->with('tryout.category')->orderByDesc('id')->paginate(12);

        return view('results.index', compact('attempts'));
    }

    public function show(Request $request, Attempt $attempt, RankingService $ranking)
    {
        $user = $request->user();
        abort_unless($attempt->user_id === $user->id || $user->isAdmin(), 403);

        if (! $attempt->isSubmitted()) {
            return redirect()->route('exam.show', $attempt);
        }

        $attempt->load('tryout.category');
        $tryout = $attempt->tryout;
        $questions = Question::withTrashed()->with(['options', 'topic'])->whereIn('id', $attempt->question_order)->get()->keyBy('id');
        $answers = $attempt->answers()->get()->keyBy('question_id');
        $optionOrder = $attempt->option_order ?? [];

        $items = collect($attempt->question_order)->map(function ($qid, $i) use ($questions, $answers, $optionOrder) {
            $q = $questions->get($qid);
            if (! $q) {
                return null;
            }
            $a = $answers->get($qid);
            $opts = $q->options->keyBy('id');
            $ordered = collect($optionOrder[$qid] ?? $opts->keys())->map(fn ($id) => $opts->get($id))->filter()->values();
            $status = ! $a?->option_id ? 'empty' : ($a->is_correct ? 'correct' : 'wrong');

            return ['no' => $i + 1, 'q' => $q, 'options' => $ordered, 'answer' => $a, 'status' => $status];
        })->filter()->values();

        return view('results.show', [
            'attempt' => $attempt,
            'tryout' => $tryout,
            'items' => $items,
            'rank' => $ranking->rankFor($attempt),
            'motivation' => MotivationService::for((float) $attempt->percentage),
            'history' => Attempt::query()->where('user_id', $attempt->user_id)->where('tryout_id', $tryout->id)
                ->where('status', Attempt::STATUS_SUBMITTED)->orderBy('attempt_no')->get(['id', 'attempt_no', 'score', 'percentage']),
            'canRetake' => $user->id === $attempt->user_id && ($tryout->attemptsLeftFor($user) === null || $tryout->attemptsLeftFor($user) > 0),
        ]);
    }
}
