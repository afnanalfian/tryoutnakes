<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class QuestionOption extends Model
{
    protected $fillable = ['question_id', 'order', 'option_text', 'image', 'is_correct', 'weight'];

    protected function casts(): array
    {
        return ['is_correct' => 'boolean', 'weight' => 'integer', 'order' => 'integer'];
    }

    public function question(): BelongsTo
    {
        return $this->belongsTo(Question::class);
    }

    public function label(): string
    {
        return chr(64 + max(1, $this->order));
    }
}
