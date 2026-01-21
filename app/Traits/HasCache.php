<?php

namespace App\Traits;

use App\Helpers\CacheKey;
use App\Services\Cache\CacheService;
use Illuminate\Support\Facades\Cache;

trait HasCache
{
    /**
     * Get cache service instance.
     */
    protected function getCacheService(): CacheService
    {
        return app(CacheService::class);
    }

    /**
     * Get cache key for this model.
     */
    public function getCacheKey(?string $suffix = null): string
    {
        $identifier = $this->getCacheIdentifier();
        $module = $this->getCacheModule();

        return CacheKey::generate($module, $identifier, $suffix);
    }

    /**
     * Get cache identifier (usually model ID).
     */
    protected function getCacheIdentifier(): string
    {
        return (string) $this->id;
    }

    /**
     * Get cache module name (usually table name).
     */
    protected function getCacheModule(): string
    {
        return $this->getTable();
    }

    /**
     * Get cache TTL in seconds.
     */
    protected function getCacheTtl(): int
    {
        return 3600; // 1 hour default
    }

    /**
     * Remember cache for this model.
     */
    public function rememberCache(?string $suffix, \Closure $callback, ?int $ttl = null)
    {
        $key = $this->getCacheKey($suffix);
        $ttl = $ttl ?? $this->getCacheTtl();

        return $this->getCacheService()->remember($key, $callback, $ttl);
    }

    /**
     * Get cached value for this model.
     */
    public function getCached(?string $suffix, mixed $default = null)
    {
        $key = $this->getCacheKey($suffix);
        return $this->getCacheService()->get($key, $default);
    }

    /**
     * Put value into cache for this model.
     */
    public function putCache(?string $suffix, mixed $value, ?int $ttl = null): bool
    {
        $key = $this->getCacheKey($suffix);
        $ttl = $ttl ?? $this->getCacheTtl();

        return $this->getCacheService()->put($key, $value, $ttl);
    }

    /**
     * Forget cache for this model.
     */
    public function forgetCache(?string $suffix = null): bool
    {
        if ($suffix !== null) {
            $key = $this->getCacheKey($suffix);
            return $this->getCacheService()->forget($key);
        }

        // Forget all cache for this model
        return $this->getCacheService()->forgetPattern($this->getCacheModule() . ':' . $this->getCacheIdentifier() . ':*');
    }

    /**
     * Forget all cache for this model type.
     */
    public function forgetModelCache(): void
    {
        $this->getCacheService()->forgetModule($this->getCacheModule());
    }

    /**
     * Boot trait and set up cache event listeners.
     */
    public static function bootHasCache(): void
    {
        // Clear cache when model is updated
        static::updated(function ($model) {
            $model->forgetCache();
        });

        // Clear cache when model is deleted
        static::deleted(function ($model) {
            $model->forgetCache();
        });

        // Clear model list cache when model is created/updated/deleted
        static::saved(function ($model) {
            $cacheService = app(\App\Services\Cache\CacheService::class);
            $cacheService->forgetPattern($model->getCacheModule() . ':list:*');
        });

        static::deleted(function ($model) {
            $cacheService = app(\App\Services\Cache\CacheService::class);
            $cacheService->forgetPattern($model->getCacheModule() . ':list:*');
        });
    }
}
