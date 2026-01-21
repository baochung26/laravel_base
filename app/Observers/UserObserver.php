<?php

namespace App\Observers;

use App\Helpers\CacheKey;
use App\Models\User;
use App\Services\Cache\CacheService;

class UserObserver
{
    public function __construct(
        protected CacheService $cacheService
    ) {
    }

    /**
     * Handle the User "created" event.
     */
    public function created(User $user): void
    {
        $this->invalidateUserCaches($user);
    }

    /**
     * Handle the User "updated" event.
     */
    public function updated(User $user): void
    {
        $this->invalidateUserCaches($user);
    }

    /**
     * Handle the User "deleted" event.
     */
    public function deleted(User $user): void
    {
        $this->invalidateUserCaches($user);
    }

    /**
     * Invalidate all user-related caches.
     */
    protected function invalidateUserCaches(User $user): void
    {
        // Invalidate individual user cache
        $this->cacheService->forget(CacheKey::user($user->id));
        $this->cacheService->forget(CacheKey::userProfile($user->id));
        $this->cacheService->forget(CacheKey::userRoles($user->id));
        $this->cacheService->forget(CacheKey::userPermissions($user->id));

        // Invalidate users list cache
        $this->cacheService->forgetPattern('users:list:*');

        // If using cache tags (Redis)
        if (config('cache.default') === 'redis') {
            $this->cacheService->forgetTags(CacheKey::tags(['users']));
        }
    }
}
