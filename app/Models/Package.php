<?php

namespace App\Models;

use App\Support\PublicCache;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class Package extends Model
{
    use SoftDeletes;

    protected $fillable = ['name', 'slug', 'tagline', 'description', 'image', 'price', 'badge', 'is_featured', 'is_active', 'sort_order'];

    protected function casts(): array
    {
        return ['is_featured' => 'boolean', 'is_active' => 'boolean', 'price' => 'integer'];
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

    public function tryouts(): BelongsToMany
    {
        return $this->belongsToMany(Tryout::class)->orderBy('category_id')->orderBy('sort_order');
    }

    public function scopeActive(Builder $q): Builder
    {
        return $q->where('is_active', true);
    }

    public function scopeOrdered(Builder $q): Builder
    {
        return $q->orderByDesc('is_featured')->orderBy('sort_order')->orderBy('id');
    }

    /** Total harga jika tryout di dalamnya dibeli satuan. */
    public function normalPrice(): int
    {
        return (int) $this->tryouts->sum(fn (Tryout $t) => $t->price());
    }

    public function savings(): int
    {
        return max(0, $this->normalPrice() - $this->price);
    }

    public function categories()
    {
        return $this->tryouts->pluck('category')->filter()->unique('id')->sortBy('sort_order')->values();
    }

    public function imageUrl(): string
    {
        if ($this->image) {
            return media_url($this->image);
        }
        $cats = $this->categories();
        if ($cats->count() > 1) {
            return asset('images/templates/paket.svg');
        }

        return $cats->first()?->imageUrl() ?? asset('images/templates/paket.svg');
    }
}
