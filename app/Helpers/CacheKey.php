<?php

namespace App\Helpers;

class CacheKey
{
    /**
     * Cache key prefix.
     */
    protected static string $prefix = 'laravel';

    /**
     * Generate cache key with convention.
     *
     * Format: {prefix}:{module}:{identifier}:{suffix?}
     *
     * Examples:
     * - user:1
     * - user:1:profile
     * - users:list:page:1
     * - users:search:john:page:1
     */
    public static function generate(string $module, ?string $identifier = null, ?string $suffix = null): string
    {
        $key = self::$prefix . ':' . $module;

        if ($identifier !== null) {
            $key .= ':' . $identifier;
        }

        if ($suffix !== null) {
            $key .= ':' . $suffix;
        }

        return $key;
    }

    /**
     * User cache keys.
     */
    public static function user(int $userId, ?string $suffix = null): string
    {
        return self::generate('user', (string) $userId, $suffix);
    }

    public static function userProfile(int $userId): string
    {
        return self::user($userId, 'profile');
    }

    public static function userRoles(int $userId): string
    {
        return self::user($userId, 'roles');
    }

    public static function userPermissions(int $userId): string
    {
        return self::user($userId, 'permissions');
    }

    /**
     * Users list cache keys.
     */
    public static function usersList(int $page = 1, ?string $search = null): string
    {
        $key = 'users:list:page:' . $page;

        if ($search !== null) {
            $key .= ':search:' . md5($search);
        }

        return self::$prefix . ':' . $key;
    }

    /**
     * Role cache keys.
     */
    public static function role(string $roleName, ?string $suffix = null): string
    {
        return self::generate('role', $roleName, $suffix);
    }

    public static function rolesList(): string
    {
        return self::generate('roles', 'list');
    }

    /**
     * Permission cache keys.
     */
    public static function permissionsList(): string
    {
        return self::generate('permissions', 'list');
    }

    /**
     * Generate cache tag for a module.
     */
    public static function tag(string $module): string
    {
        return self::$prefix . ':' . $module;
    }

    /**
     * Generate cache tags array.
     */
    public static function tags(array $modules): array
    {
        return array_map(fn ($module) => self::tag($module), $modules);
    }
}
