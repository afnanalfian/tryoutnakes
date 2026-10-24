<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class Question extends Model
{
    use SoftDeletes;

    protected $fillable = ['category_id', 'subtest', 'topic_id', 'question_text', 'image', 'explanation', 'difficulty', 'created_by'];

    public function category(): BelongsTo
    {
        return $this->belongsTo(Category::class);
    }

    public function topic(): BelongsTo
    {
        return $this->belongsTo(QuestionTopic::class, 'topic_id');
    }

    public function options(): HasMany
    {
        return $this->hasMany(QuestionOption::class)->orderBy('order');
    }

    public function tryouts(): BelongsToMany
    {
        return $this->belongsToMany(Tryout::class)->withPivot('order');
    }

    public function isTkp(): bool
    {
        return $this->subtest === 'tkp';
    }

    /** Poin maksimum soal: TKP = bobot terbesar, lainnya = poin benar. */
    public function maxPoint(int $correctPoint = 1): int
    {
        return $this->isTkp() ? (int) $this->options->max('weight') : $correctPoint;
    }

    public function correctOption(): ?QuestionOption
    {
        return $this->options->firstWhere('is_correct', true);
    }

    public function excerpt(int $len = 110): string
    {
        $text = preg_replace('/\\\\\(|\\\\\)/', ' ', strip_tags($this->question_text));

        return \Illuminate\Support\Str::limit(trim(preg_replace('/\s+/', ' ', html_entity_decode($text))), $len);
    }
}
