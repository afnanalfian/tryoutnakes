<?php

namespace App\Http\Controllers;

use App\Models\Attempt;
use App\Models\Tryout;
use App\Services\ExamService;
use Illuminate\Http\Request;
use RuntimeException;

class ExamController extends Controller
{
    public function __construct(protected ExamService $exam) {}

    public function start(Request $request, Tryout $tryout)
    {
        abort_unless($tryout->is_published || $request->user()->isAdmin(), 404);
        try {
            $attempt = $this->exam->start($request->user(), $tryout);
        } catch (RuntimeException $e) {
            return back()->with('error', $e->getMessage());
        }

        return redirect()->route('exam.show', $attempt);
    }

    public function show(Request $request, Attempt $attempt)
    {
        $this->authorizeAttempt($request, $attempt);

        if ($attempt->isSubmitted()) {
            return redirect()->route('results.show', $attempt);
        }
        if (! $attempt->deadline_at->isFuture()) {
            $this->exam->submit($attempt, 'timeout');

            return redirect()->route('results.show', $attempt)->with('info', 'Waktu pengerjaan telah habis. Jawabanmu dikumpulkan otomatis.');
        }

        $attempt->load('tryout.category');
        $questions = $this->exam->payload($attempt);

        return view('exam.show', [
            'attempt' => $attempt,
            'tryout' => $attempt->tryout,
            'questions' => $questions,
            'config' => [
                'attemptId' => $attempt->id,
                'remaining' => $attempt->remainingSeconds(),
                'csrf' => csrf_token(),
                'answerUrl' => route('exam.answer', $attempt),
                'submitUrl' => route('exam.submit', $attempt),
                'syncUrl' => route('exam.sync', $attempt),
                'resultUrl' => route('results.show', $attempt),
                'questions' => collect($questions)->map(fn ($q) => [
                    'id' => $q['id'], 'no' => $q['no'], 'subtest' => $q['subtest'], 'answer' => $q['answer'], 'flagged' => $q['flagged'],
                ])->values(),
            ],
        ]);
    }

    public function answer(Request $request, Attempt $attempt)
    {
        $this->authorizeAttempt($request, $attempt);
        if ($attempt->isSubmitted()) {
            return response()->json(['message' => 'Tryout sudah dikumpulkan.'], 409);
        }

        $data = $request->validate([
            'question_id' => ['required', 'integer'],
            'option_id' => ['nullable', 'integer'],
            'flagged' => ['boolean'],
        ]);

        try {
            $this->exam->saveAnswer($attempt, (int) $data['question_id'], isset($data['option_id']) ? (int) $data['option_id'] : null, (bool) ($data['flagged'] ?? false));
        } catch (RuntimeException $e) {
            $expired = ! $attempt->deadline_at->copy()->addSeconds(config('tryout.answer_grace_seconds'))->isFuture();

            return response()->json(['message' => $e->getMessage()], $expired ? 410 : 422);
        }

        return response()->json(['ok' => true, 'remaining' => $attempt->remainingSeconds()]);
    }

    public function submit(Request $request, Attempt $attempt)
    {
        $this->authorizeAttempt($request, $attempt);

        if (! $attempt->isSubmitted()) {
            // Terapkan jawaban yang belum sempat tersinkron (best effort).
            foreach ((array) $request->input('pending', []) as $p) {
                try {
                    $this->exam->saveAnswer($attempt, (int) ($p['question_id'] ?? 0), isset($p['option_id']) ? (int) $p['option_id'] : null, (bool) ($p['flagged'] ?? false));
                } catch (RuntimeException) {
                    // abaikan
                }
            }
            $this->exam->submit($attempt, $attempt->deadline_at->isFuture() ? 'manual' : 'timeout');
        }

        return response()->json(['redirect' => route('results.show', $attempt)]);
    }

    public function sync(Request $request, Attempt $attempt)
    {
        $this->authorizeAttempt($request, $attempt);

        return response()->json([
            'remaining' => $attempt->remainingSeconds(),
            'submitted' => $attempt->isSubmitted(),
        ]);
    }

    protected function authorizeAttempt(Request $request, Attempt $attempt): void
    {
        abort_unless($attempt->user_id === $request->user()->id, 403);
    }
}
