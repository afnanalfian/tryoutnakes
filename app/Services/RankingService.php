<?php

namespace App\Services;

use App\Models\Attempt;
use Illuminate\Support\Facades\Cache;

/**
 * Peringkat dihitung dari PERCOBAAN PERTAMA tiap peserta (adil, tidak bisa "farming").
 * Urutan: nilai total ↓, (SKD: TKP ↓, TIU ↓, TWK ↓ sesuai aturan BKN), durasi ↑.
 * Hanya ditampilkan bila peserta >= rank_min_participants (default 5).
 */
class RankingService
{
    public function participants(int $tryoutId): int
    {
        return (int) Cache::remember("rank:participants:$tryoutId", 300, fn () => Attempt::query()
            ->where('tryout_id', $tryoutId)->where('attempt_no', 1)->where('status', Attempt::STATUS_SUBMITTED)
            ->whereHas('user', fn ($u) => $u->where('role', 'user'))->count());
    }

    /** @return array{rank:int|null, participants:int, eligible:bool, is_first:bool} */
    public function rankFor(Attempt $attempt): array
    {
        $participants = $this->participants($attempt->tryout_id);
        $min = (int) config('tryout.rank_min_participants', 5);

        $first = $attempt->attempt_no === 1
            ? $attempt
            : Attempt::query()->where('tryout_id', $attempt->tryout_id)->where('user_id', $attempt->user_id)
                ->where('attempt_no', 1)->where('status', Attempt::STATUS_SUBMITTED)->first();

        if (! $first || $participants < $min) {
            return ['rank' => null, 'participants' => $participants, 'eligible' => false, 'is_first' => $attempt->attempt_no === 1, 'min' => $min];
        }

        $better = Attempt::query()
            ->where('tryout_id', $first->tryout_id)->where('attempt_no', 1)->where('status', Attempt::STATUS_SUBMITTED)
            ->where('id', '!=', $first->id)
            ->whereHas('user', fn ($u) => $u->where('role', 'user'))
            ->where(function ($q) use ($first) {
                $q->where('score', '>', $first->score)
                    ->orWhere(function ($q) use ($first) {
                        $q->where('score', $first->score);
                        $this->tieBreak($q, $first, ['score_tkp', 'score_tiu', 'score_twk']);
                    });
            })->count();

        return ['rank' => $better + 1, 'participants' => $participants, 'eligible' => true, 'is_first' => $attempt->attempt_no === 1, 'min' => $min];
    }

    protected function tieBreak($q, Attempt $a, array $cols): void
    {
        if (empty($cols)) {
            $q->where('duration_seconds', '<', $a->duration_seconds);

            return;
        }
        $col = array_shift($cols);
        $mine = $a->{$col};
        if ($mine === null) {
            $this->tieBreak($q, $a, $cols);

            return;
        }
        $q->where(function ($w) use ($col, $mine, $a, $cols) {
            $w->where($col, '>', $mine)->orWhere(function ($w2) use ($col, $mine, $a, $cols) {
                $w2->where($col, $mine);
                $this->tieBreak($w2, $a, $cols);
            });
        });
    }

    public function forget(int $tryoutId): void
    {
        Cache::forget("rank:participants:$tryoutId");
    }
}
