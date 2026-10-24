<?php

namespace App\Services;

use App\Models\Attempt;
use App\Models\AttemptAnswer;
use App\Models\Question;
use App\Models\Tryout;
use App\Models\User;
use App\Notifications\AppNotification;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use RuntimeException;

class ExamService
{
    public function __construct(protected ScoringService $scoring) {}

    /** Mulai attempt baru atau lanjutkan yang sedang berjalan. */
    public function start(User $user, Tryout $tryout): Attempt
    {
        if (! $user->canAccessTryout($tryout)) {
            throw new RuntimeException('Kamu belum memiliki akses ke tryout ini.');
        }

        return DB::transaction(function () use ($user, $tryout) {
            // Kunci baris user untuk mencegah double-start dari dua tab.
            User::query()->whereKey($user->id)->lockForUpdate()->first();

            $running = Attempt::query()->where('user_id', $user->id)->where('tryout_id', $tryout->id)
                ->where('status', Attempt::STATUS_IN_PROGRESS)->latest('id')->first();

            if ($running) {
                if ($running->deadline_at->isFuture()) {
                    return $running;
                }
                $this->submit($running, 'timeout');
            }

            $left = $tryout->attemptsLeftFor($user);
            if ($left !== null && $left <= 0) {
                throw new RuntimeException('Kesempatan mengerjakan tryout ini sudah habis.');
            }

            $questions = $tryout->questions()->with('options:id,question_id,order')->get(['questions.id', 'questions.subtest']);
            if ($questions->isEmpty()) {
                throw new RuntimeException('Tryout ini belum memiliki soal.');
            }

            $order = $this->buildQuestionOrder($tryout, $questions);
            $optionOrder = [];
            foreach ($questions as $q) {
                $ids = $q->options->sortBy('order')->pluck('id')->all();
                if ($tryout->shuffle_options) {
                    shuffle($ids);
                }
                $optionOrder[$q->id] = $ids;
            }

            $now = now();

            return Attempt::query()->create([
                'user_id' => $user->id,
                'tryout_id' => $tryout->id,
                'attempt_no' => Attempt::query()->where('user_id', $user->id)->where('tryout_id', $tryout->id)->count() + 1,
                'status' => Attempt::STATUS_IN_PROGRESS,
                'started_at' => $now,
                'deadline_at' => $now->copy()->addMinutes($tryout->duration_minutes),
                'question_order' => $order,
                'option_order' => $optionOrder,
            ]);
        });
    }

    /** Urutan soal. SKD mengikuti urutan CAT resmi: TWK → TIU → TKP. */
    protected function buildQuestionOrder(Tryout $tryout, Collection $questions): array
    {
        $groups = $tryout->isSkd()
            ? collect(['twk', 'tiu', 'tkp'])->map(fn ($s) => $questions->where('subtest', $s)->values())
            : collect([$questions->values()]);

        return $groups->flatMap(function ($group) use ($tryout) {
            $ids = $group->pluck('id')->all();
            if ($tryout->shuffle_questions) {
                shuffle($ids);
            }

            return $ids;
        })->values()->all();
    }

    /** Data soal untuk ditampilkan di halaman ujian (TANPA kunci/bobot). */
    public function payload(Attempt $attempt): array
    {
        $questions = Question::withTrashed()->with('options')->whereIn('id', $attempt->question_order)->get()->keyBy('id');
        $answers = $attempt->answers()->get()->keyBy('question_id');
        $optionOrder = $attempt->option_order ?? [];

        return collect($attempt->question_order)->map(function ($qid, $i) use ($questions, $answers, $optionOrder) {
            $q = $questions->get($qid);
            if (! $q) {
                return null;
            }
            $opts = $q->options->keyBy('id');
            $ordered = collect($optionOrder[$qid] ?? $opts->keys()->all())->map(fn ($id) => $opts->get($id))->filter()->values();
            $a = $answers->get($qid);

            return [
                'id' => $q->id,
                'no' => $i + 1,
                'subtest' => $q->subtest,
                'text' => rich($q->question_text),
                'image' => media_url($q->image),
                'options' => $ordered->map(fn ($o, $k) => [
                    'id' => $o->id,
                    'label' => chr(65 + $k),
                    'text' => rich($o->option_text),
                    'image' => media_url($o->image),
                ])->all(),
                'answer' => $a?->option_id,
                'flagged' => (bool) $a?->is_flagged,
            ];
        })->filter()->values()->all();
    }

    public function saveAnswer(Attempt $attempt, int $questionId, ?int $optionId, bool $flagged): void
    {
        if ($attempt->isSubmitted()) {
            throw new RuntimeException('Tryout sudah dikumpulkan.');
        }
        $grace = (int) config('tryout.answer_grace_seconds', 20);
        if (now()->greaterThan($attempt->deadline_at->copy()->addSeconds($grace))) {
            throw new RuntimeException('Waktu pengerjaan sudah habis.');
        }
        if (! in_array($questionId, $attempt->question_order, true)) {
            throw new RuntimeException('Soal tidak valid.');
        }
        if ($optionId !== null && ! in_array($optionId, $attempt->option_order[$questionId] ?? [], true)) {
            throw new RuntimeException('Pilihan jawaban tidak valid.');
        }

        AttemptAnswer::query()->updateOrCreate(
            ['attempt_id' => $attempt->id, 'question_id' => $questionId],
            ['option_id' => $optionId, 'is_flagged' => $flagged, 'answered_at' => now()],
        );
    }

    public function submit(Attempt $attempt, string $reason = 'manual'): Attempt
    {
        $result = DB::transaction(function () use ($attempt, $reason) {
            $attempt = Attempt::query()->whereKey($attempt->id)->lockForUpdate()->first();
            if ($attempt->isSubmitted()) {
                return null;
            }
            $attempt->load('tryout.category');

            $questions = Question::withTrashed()->with('options')->whereIn('id', $attempt->question_order)->get();
            $answers = $attempt->answers()->get()->keyBy('question_id');
            $r = $this->scoring->score($attempt, $questions, $answers);

            $missing = [];
            foreach ($r['answers'] as $qid => $res) {
                $existing = $answers->get($qid);
                if ($existing) {
                    $existing->update(['is_correct' => $res['is_correct'], 'score' => $res['score']]);
                } else {
                    // Simpan soal kosong agar statistik & pembahasan lengkap.
                    $missing[] = ['attempt_id' => $attempt->id, 'question_id' => $qid, 'option_id' => null, 'is_flagged' => false, 'is_correct' => null, 'score' => 0];
                }
            }
            if ($missing) {
                AttemptAnswer::query()->insert($missing);
            }

            $submittedAt = now()->min($attempt->deadline_at);
            $attempt->update([
                'status' => Attempt::STATUS_SUBMITTED,
                'submitted_at' => $submittedAt,
                'duration_seconds' => max(0, (int) $attempt->started_at->diffInSeconds($submittedAt)),
                'score' => $r['score'],
                'max_score' => $r['max_score'],
                'percentage' => $r['percentage'],
                'score_twk' => $r['score_twk'],
                'score_tiu' => $r['score_tiu'],
                'score_tkp' => $r['score_tkp'],
                'breakdown' => $r['breakdown'],
                'correct_count' => $r['correct'],
                'wrong_count' => $r['wrong'],
                'empty_count' => $r['empty'],
                'is_passed' => $r['is_passed'],
                'submit_reason' => $reason,
            ]);

            return $attempt;
        });

        if ($result) {
            $result->user->notify(new AppNotification(
                'tryout_result',
                'Hasil '.$result->tryout->title,
                'Nilaimu '.score_format($result->score).' / '.score_format($result->max_score).' ('.score_format($result->percentage).'%). '.\App\Services\MotivationService::for($result->percentage)['message'],
                route('results.show', $result),
                'Lihat Hasil & Pembahasan',
            ));
            app(RankingService::class)->forget($result->tryout_id);

            return $result;
        }

        return $attempt->fresh();
    }

    public function finalizeExpired(): int
    {
        $count = 0;
        Attempt::query()->where('status', Attempt::STATUS_IN_PROGRESS)->where('deadline_at', '<', now()->subSeconds(30))
            ->chunkById(50, function ($attempts) use (&$count) {
                foreach ($attempts as $attempt) {
                    $this->submit($attempt, 'timeout');
                    $count++;
                }
            });

        return $count;
    }
}
