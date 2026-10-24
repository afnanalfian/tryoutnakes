<?php

namespace App\Models;

use App\Support\PublicCache;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class Tryout extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'category_id', 'title', 'slug', 'description', 'image', 'duration_minutes', 'scale_max',
        'passing_grade', 'is_free', 'is_published', 'shuffle_questions', 'shuffle_options',
        'show_explanation', 'max_attempts', 'sort_order', 'published_at',
    ];

    protected function casts(): array
    {
        return [
            'passing_grade' => 'array',
            'is_free' => 'boolean',
            'is_published' => 'boolean',
            'shuffle_questions' => 'boolean',
            'shuffle_options' => 'boolean',
            'show_explanation' => 'boolean',
            'scale_max' => 'float',
            'published_at' => 'datetime',
        ];
    }

    protected static function booted(): void
    {
        static::saved(fn () => PublicCache::flush());
        static::deleted(fn () => PublicCache::flush());
    }

    public function getRouteKeyName(): string
    {
        return 'slug';
    }

    public function category(): BelongsTo
    {
        return $this->belongsTo(Category::class);
    }

    public function questions(): BelongsToMany
    {
        return $this->belongsToMany(Question::class)->withPivot('order')->orderByPivot('order');
    }

    public function packages(): BelongsToMany
    {
        return $this->belongsToMany(Package::class);
    }

    public function attempts(): HasMany
    {
        return $this->hasMany(Attempt::class);
    }

    public function scopePublished(Builder $q): Builder
    {
        return $q->where('is_published', true);
    }

    public function scopeOrdered(Builder $q): Builder
    {
        return $q->orderBy('sort_order')->orderBy('id');
    }

    public function isSkd(): bool
    {
        return $this->category?->type === Category::TYPE_SKD;
    }

    public function price(): int
    {
        return $this->is_free ? 0 : (int) $this->category?->price;
    }

    public function imageUrl(): string
    {
        return $this->image ? media_url($this->image) : $this->category->imageUrl();
    }

    public function passingFor(string $key): ?int
    {
        $pg = $this->passing_grade ?? [];

        return isset($pg[$key]) && $pg[$key] !== '' ? (int) $pg[$key] : null;
    }

    public function questionCount(): int
    {
        return (int) ($this->questions_count ?? $this->questions()->count());
    }

    public function attemptsLeftFor(User $user): ?int
    {
        if ((int) $this->max_attempts === 0) {
            return null; // tanpa batas
        }
        $used = $this->attempts()->where('user_id', $user->id)->count();

        return max(0, $this->max_attempts - $used);
    }
}
