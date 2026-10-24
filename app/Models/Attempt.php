<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Attempt extends Model
{
    public const STATUS_IN_PROGRESS = 'in_progress';
    public const STATUS_SUBMITTED = 'submitted';

    protected $fillable = [
        'user_id', 'tryout_id', 'attempt_no', 'status', 'started_at', 'deadline_at', 'submitted_at',
        'duration_seconds', 'question_order', 'option_order', 'score', 'max_score', 'percentage',
        'score_twk', 'score_tiu', 'score_tkp', 'breakdown', 'correct_count', 'wrong_count',
        'empty_count', 'is_passed', 'submit_reason',
    ];

    protected function casts(): array
    {
        return [
            'started_at' => 'datetime',
            'deadline_at' => 'datetime',
            'submitted_at' => 'datetime',
            'question_order' => 'array',
            'option_order' => 'array',
            'breakdown' => 'array',
            'score' => 'float',
            'max_score' => 'float',
            'percentage' => 'float',
            'is_passed' => 'boolean',
        ];
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function tryout(): BelongsTo
    {
        return $this->belongsTo(Tryout::class)->withTrashed();
    }

    public function answers(): HasMany
    {
        return $this->hasMany(AttemptAnswer::class);
    }

    public function isSubmitted(): bool
    {
        return $this->status === self::STATUS_SUBMITTED;
    }

    public function isRunning(): bool
    {
        return $this->status === self::STATUS_IN_PROGRESS && $this->deadline_at->isFuture();
    }

    public function remainingSeconds(): int
    {
        return max(0, (int) now()->diffInSeconds($this->deadline_at, false));
    }

    public function durationLabel(): string
    {
        $s = (int) $this->duration_seconds;

        return sprintf('%02d:%02d:%02d', intdiv($s, 3600), intdiv($s % 3600, 60), $s % 60);
    }
}
