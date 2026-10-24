<?php

namespace App\Models;

use App\Support\PublicCache;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Category extends Model
{
    public const TYPE_SKD = 'skd';
    public const TYPE_SKB = 'skb';

    protected $fillable = ['type', 'code', 'name', 'slug', 'tagline', 'description', 'icon', 'color', 'image', 'price', 'sort_order', 'is_active'];

    protected function casts(): array
    {
        return ['is_active' => 'boolean', 'price' => 'integer'];
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

    public function tryouts(): HasMany
    {
        return $this->hasMany(Tryout::class);
    }

    public function questions(): HasMany
    {
        return $this->hasMany(Question::class);
    }

    public function topics(): HasMany
    {
        return $this->hasMany(QuestionTopic::class);
    }

    public function scopeActive(Builder $q): Builder
    {
        return $q->where('is_active', true);
    }

    public function scopeOrdered(Builder $q): Builder
    {
        return $q->orderBy('sort_order')->orderBy('id');
    }

    public function isSkd(): bool
    {
        return $this->type === self::TYPE_SKD;
    }

    /** Subtes yang tersedia untuk kategori ini. */
    public function subtests(): array
    {
        return $this->isSkd() ? ['twk', 'tiu', 'tkp'] : ['skb'];
    }

    public function imageUrl(): string
    {
        return $this->image ? media_url($this->image) : asset("images/templates/{$this->code}.svg");
    }

    public function label(): string
    {
        return $this->isSkd() ? 'SKD CPNS' : 'SKB '.$this->name;
    }
}
