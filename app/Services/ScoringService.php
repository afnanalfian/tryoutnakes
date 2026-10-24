<?php

namespace App\Services;

use App\Models\Attempt;
use App\Models\Question;
use Illuminate\Support\Collection;

/**
 * Penilaian:
 *  SKD  : TWK & TIU benar = 5, salah/kosong = 0. TKP = bobot opsi (1–5), kosong = 0.
 *         Lulus passing grade jika TWK, TIU, TKP masing-masing >= ambang.
 *  SKB  : nilai = (jumlah benar / jumlah soal) × skala (diatur admin per tryout, mis. 100 / 400).
 */
class ScoringService
{
    /**
     * @param  Collection<int, Question>  $questions  (dengan relasi options)
     * @param  Collection  $answers  keyed by question_id
     */
    public function score(Attempt $attempt, Collection $questions, Collection $answers): array
    {
        $tryout = $attempt->tryout;

        return $tryout->isSkd()
            ? $this->scoreSkd($tryout, $questions, $answers)
            : $this->scoreSkb($tryout, $questions, $answers);
    }

    protected function scoreSkd($tryout, Collection $questions, Collection $answers): array
    {
        $point = (int) config('tryout.skd.correct_point', 5);
        $subs = [];
        foreach (['twk', 'tiu', 'tkp'] as $s) {
            $subs[$s] = ['score' => 0, 'max' => 0, 'correct' => 0, 'wrong' => 0, 'empty' => 0, 'total' => 0];
        }
        $perAnswer = [];

        foreach ($questions as $q) {
            $sub = $q->subtest;
            if (! isset($subs[$sub])) {
                continue;
            }
            $subs[$sub]['total']++;
            $a = $answers->get($q->id);
            $optionId = $a?->option_id;

            if ($sub === 'tkp') {
                $max = (int) $q->options->max('weight');
                $subs[$sub]['max'] += $max;
                if (! $optionId) {
                    $subs[$sub]['empty']++;
                    $perAnswer[$q->id] = ['score' => 0, 'is_correct' => null];
                    continue;
                }
                $weight = (int) ($q->options->firstWhere('id', $optionId)?->weight ?? 0);
                $subs[$sub]['score'] += $weight;
                $weight === $max && $max > 0 ? $subs[$sub]['correct']++ : $subs[$sub]['wrong']++;
                $perAnswer[$q->id] = ['score' => $weight, 'is_correct' => $weight === $max];
                continue;
            }

            $subs[$sub]['max'] += $point;
            if (! $optionId) {
                $subs[$sub]['empty']++;
                $perAnswer[$q->id] = ['score' => 0, 'is_correct' => null];
                continue;
            }
            $isCorrect = (int) $q->options->firstWhere('is_correct', true)?->id === (int) $optionId;
            if ($isCorrect) {
                $subs[$sub]['score'] += $point;
                $subs[$sub]['correct']++;
            } else {
                $subs[$sub]['wrong']++;
            }
            $perAnswer[$q->id] = ['score' => $isCorrect ? $point : 0, 'is_correct' => $isCorrect];
        }

        $passing = [];
        $passed = true;
        foreach (['twk', 'tiu', 'tkp'] as $s) {
            $pg = $tryout->passingFor($s) ?? (int) config("tryout.skd.passing_grade.$s");
            $passing[$s] = $pg;
            $subs[$s]['passing'] = $pg;
            $subs[$s]['passed'] = $subs[$s]['score'] >= $pg;
            $passed = $passed && $subs[$s]['passed'];
        }

        $score = array_sum(array_column($subs, 'score'));
        $max = array_sum(array_column($subs, 'max'));

        return [
            'score' => $score,
            'max_score' => $max,
            'percentage' => $max > 0 ? round($score / $max * 100, 2) : 0,
            'score_twk' => $subs['twk']['score'],
            'score_tiu' => $subs['tiu']['score'],
            'score_tkp' => $subs['tkp']['score'],
            'correct' => array_sum(array_column($subs, 'correct')),
            'wrong' => array_sum(array_column($subs, 'wrong')),
            'empty' => array_sum(array_column($subs, 'empty')),
            'is_passed' => $passed,
            'breakdown' => ['type' => 'skd', 'subtests' => $subs, 'passing' => $passing],
            'answers' => $perAnswer,
        ];
    }

    protected function scoreSkb($tryout, Collection $questions, Collection $answers): array
    {
        $scale = (float) ($tryout->scale_max ?: 100);
        $total = $questions->count();
        $correct = $wrong = $empty = 0;
        $perAnswer = [];
        $pointPerQuestion = $total > 0 ? $scale / $total : 0;

        foreach ($questions as $q) {
            $a = $answers->get($q->id);
            if (! $a?->option_id) {
                $empty++;
                $perAnswer[$q->id] = ['score' => 0, 'is_correct' => null];
                continue;
            }
            $isCorrect = (int) $q->options->firstWhere('is_correct', true)?->id === (int) $a->option_id;
            $isCorrect ? $correct++ : $wrong++;
            $perAnswer[$q->id] = ['score' => $isCorrect ? round($pointPerQuestion, 2) : 0, 'is_correct' => $isCorrect];
        }

        $score = $total > 0 ? round($correct / $total * $scale, 2) : 0;
        $passingScore = $tryout->passingFor('score');

        return [
            'score' => $score,
            'max_score' => $scale,
            'percentage' => $total > 0 ? round($correct / $total * 100, 2) : 0,
            'score_twk' => null, 'score_tiu' => null, 'score_tkp' => null,
            'correct' => $correct,
            'wrong' => $wrong,
            'empty' => $empty,
            'is_passed' => $passingScore !== null ? $score >= $passingScore : null,
            'breakdown' => ['type' => 'skb', 'scale' => $scale, 'passing' => $passingScore, 'total' => $total, 'point_per_question' => round($pointPerQuestion, 4)],
            'answers' => $perAnswer,
        ];
    }
}
