<?php

namespace App\Services;

use App\Models\Attempt;
use App\Models\AttemptAnswer;
use App\Models\Category;
use App\Models\User;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;

/** Data monitoring kemampuan & peningkatan user per kategori. */
class ProgressService
{
    public function categoriesWithAttempts(User $user): Collection
    {
        $ids = Attempt::query()->where('attempts.user_id', $user->id)->where('status', Attempt::STATUS_SUBMITTED)
            ->join('tryouts', 'tryouts.id', '=', 'attempts.tryout_id')
            ->distinct()->pluck('tryouts.category_id');

        return Category::query()->whereIn('id', $ids)->ordered()->get();
    }

    public function forCategory(User $user, Category $category): array
    {
        $attempts = Attempt::query()->with('tryout:id,title,slug,category_id')
            ->where('user_id', $user->id)->where('status', Attempt::STATUS_SUBMITTED)
            ->whereHas('tryout', fn ($q) => $q->withTrashed()->where('category_id', $category->id))
            ->orderBy('submitted_at')->get();

        $points = $attempts->map(fn (Attempt $a) => [
            'label' => \Illuminate\Support\Str::limit($a->tryout->title, 22).($a->attempt_no > 1 ? ' (#'.$a->attempt_no.')' : ''),
            'date' => $a->submitted_at->translatedFormat('d M'),
            'score' => (float) $a->score,
            'pct' => (float) $a->percentage,
            'twk' => $a->score_twk, 'tiu' => $a->score_tiu, 'tkp' => $a->score_tkp,
            'passed' => $a->is_passed,
            'sub_passed' => collect($a->breakdown['subtests'] ?? [])->map(fn ($x) => (bool) ($x['passed'] ?? false))->all(),
            'id' => $a->id,
        ])->values();

        $pcts = $points->pluck('pct');
        $first = $pcts->first();
        $last = $pcts->last();

        $subtestStats = [];
        if ($category->isSkd()) {
            foreach (['twk', 'tiu', 'tkp'] as $s) {
                $vals = $points->pluck($s)->filter(fn ($v) => $v !== null);
                $subtestStats[$s] = [
                    'avg' => $vals->count() ? round($vals->avg(), 1) : null,
                    'best' => $vals->max(),
                    'last' => $vals->last(),
                    'passing' => (int) config("tryout.skd.passing_grade.$s"),
                    'pass_rate' => $vals->count() ? round($points->filter(fn ($p) => $p['sub_passed'][$s] ?? false)->count() / $vals->count() * 100) : 0,
                ];
            }
        }

        return [
            'points' => $points,
            'count' => $points->count(),
            'avg_pct' => $pcts->count() ? round($pcts->avg(), 1) : 0,
            'best_pct' => $pcts->max() ?? 0,
            'last_pct' => $last ?? 0,
            'delta' => $pcts->count() > 1 ? round($last - $first, 1) : null,
            'pass_rate' => $points->count() && $category->isSkd() ? round($points->where('passed', true)->count() / $points->count() * 100) : null,
            'subtests' => $subtestStats,
            'topics' => $this->topicAccuracy($user, $category),
        ];
    }

    /** Akurasi per topik/materi (berdasarkan semua jawaban). */
    public function topicAccuracy(User $user, Category $category): Collection
    {
        return AttemptAnswer::query()
            ->join('attempts', 'attempts.id', '=', 'attempt_answers.attempt_id')
            ->join('questions', 'questions.id', '=', 'attempt_answers.question_id')
            ->leftJoin('question_topics', 'question_topics.id', '=', 'questions.topic_id')
            ->where('attempts.user_id', $user->id)
            ->where('attempts.status', Attempt::STATUS_SUBMITTED)
            ->where('questions.category_id', $category->id)
            ->groupBy('questions.subtest', 'question_topics.name')
            ->select([
                'questions.subtest',
                DB::raw("COALESCE(question_topics.name, 'Umum') as topic"),
                DB::raw('COUNT(*) as total'),
                DB::raw('SUM(CASE WHEN attempt_answers.is_correct = 1 THEN 1 ELSE 0 END) as correct'),
            ])
            ->get()
            ->map(fn ($r) => [
                'subtest' => $r->subtest,
                'topic' => $r->topic,
                'total' => (int) $r->total,
                'accuracy' => $r->total ? round($r->correct / $r->total * 100) : 0,
            ])
            ->sortBy('accuracy')->values();
    }
}
