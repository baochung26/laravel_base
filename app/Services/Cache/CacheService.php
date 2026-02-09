<?php

namespace App\Services\Cache;

use App\Helpers\CacheKey;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;

class CacheService
{
    /**
     * Get or remember cache value.
     */
    public function remember(string $key, \Closure $callback, int $ttl = 3600)
    {
        return Cache::remember($key, $ttl, function () use ($callback, $key) {
            Log::debug('Cache miss', ['key' => $key]);
            return $callback();
        });
    }

    /**
     * Get cache value.
     */
    public function get(string $key, mixed $default = null)
    {
        $value = Cache::get($key, $default);

        if ($value !== $default) {
            Log::debug('Cache hit', ['key' => $key]);
        } else {
            Log::debug('Cache miss', ['key' => $key]);
        }

        return $value;
    }

    /**
     * Put value into cache.
     */
    public function put(string $key, mixed $value, int $ttl = 3600): bool
    {
        Log::debug('Cache put', ['key' => $key, 'ttl' => $ttl]);
        return Cache::put($key, $value, $ttl);
    }

    /**
     * Forget cache key.
     */
    public function forget(string $key): bool
    {
        Log::debug('Cache forget', ['key' => $key]);
        return Cache::forget($key);
    }

    /**
     * Forget cache keys by pattern (Redis only).
     */
    public function forgetPattern(string $pattern): int
    {
        $store = Cache::getStore();

        if (!method_exists($store, 'getRedis')) {
            Log::warning('Cache forget pattern skipped (store does not support getRedis)', [
                'pattern' => $pattern,
                'store' => get_class($store),
            ]);
            return 0;
        }

        $keys = $store->getRedis()->keys(CacheKey::generate('*') . $pattern);
        $count = 0;

        foreach ($keys as $key) {
            if (Cache::forget($key)) {
                $count++;
            }
        }

        Log::debug('Cache forget pattern', ['pattern' => $pattern, 'count' => $count]);
        return $count;
    }

    /**
     * Forget all cache keys for a module.
     */
    public function forgetModule(string $module): int
    {
        return $this->forgetPattern($module . ':*');
    }

    /**
     * Forget user-related cache.
     */
    public function forgetUser(int $userId): void
    {
        // Forget individual user cache
        $this->forget(CacheKey::user($userId));
        $this->forget(CacheKey::userProfile($userId));
        $this->forget(CacheKey::userRoles($userId));
        $this->forget(CacheKey::userPermissions($userId));

        // Forget users list cache (all pages)
        $this->forgetPattern('users:list:*');
    }

    /**
     * Clear all cache.
     */
    public function flush(): bool
    {
        Log::info('Cache flush');
        return Cache::flush();
    }

    /**
     * Get cache with tags (Redis only).
     */
    public function rememberWithTags(array $tags, string $key, \Closure $callback, int $ttl = 3600)
    {
        return Cache::tags($tags)->remember($key, $ttl, function () use ($callback, $key) {
            Log::debug('Cache miss (tagged)', ['key' => $key]);
            return $callback();
        });
    }

    /**
     * Forget cache by tags.
     */
    public function forgetTags(array $tags): bool
    {
        Log::debug('Cache forget tags', ['tags' => $tags]);
        return Cache::tags($tags)->flush();
    }
}
