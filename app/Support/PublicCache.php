<?php

namespace App\Support;

use Closure;
use Illuminate\Support\Facades\Cache;

/**
 * Cache untuk data publik (landing, katalog, sitemap).
 * Memakai "versi" agar seluruh cache publik bisa di-invalidate sekaligus
 * tanpa butuh cache tags (aman untuk driver file/database/redis).
 */
class PublicCache
{
    public static function version(): int
    {
        return (int) Cache::rememberForever('public-cache:version', fn () => 1);
    }

    public static function remember(string $key, int $seconds, Closure $callback): mixed
    {
        return Cache::remember('public:'.static::version().':'.$key, $seconds, $callback);
    }

    public static function flush(): void
    {
        Cache::forever('public-cache:version', static::version() + 1);
    }
}
