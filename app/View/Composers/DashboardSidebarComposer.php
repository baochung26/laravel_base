<?php

namespace App\View\Composers;

use Illuminate\Support\Facades\Route;
use Illuminate\View\View;

class DashboardSidebarComposer
{
    /**
     * Bind data to the view.
     */
    public function compose(View $view): void
    {
        $sidebarMenu = config('dashboard.menu', []);
        $sidebarFooter = config('dashboard.footer', []);
        $currentUser = auth()->user();

        $view->with([
            'sidebarMenu' => $this->enrichItems($this->filterVisibleItems($sidebarMenu, $currentUser)),
            'sidebarFooter' => $this->enrichItems($this->filterVisibleItems($sidebarFooter, $currentUser)),
        ]);
    }

    /**
     * Add url and is_active to each menu item.
     *
     * @param  array<int, array<string, mixed>>  $items
     * @return array<int, array<string, mixed>>
     */
    protected function enrichItems(array $items): array
    {
        return array_map(function (array $item) {
            $item['url'] = self::itemUrl($item);
            $item['is_active'] = self::isMenuItemActive($item);

            return $item;
        }, $items);
    }

    /**
     * Filter menu items by user roles/permissions.
     *
     * @param  array<int, array<string, mixed>>  $items
     * @return array<int, array<string, mixed>>
     */
    protected function filterVisibleItems(array $items, $currentUser): array
    {
        return array_filter($items, function (array $item) use ($currentUser) {
            return $this->canViewMenuItem($item, $currentUser);
        });
    }

    protected function canViewMenuItem(array $item, $currentUser): bool
    {
        if (! $currentUser) {
            return false;
        }

        $roles = $item['roles'] ?? [];
        $permissions = $item['permissions'] ?? [];

        if (! empty($roles) && ! empty($permissions)) {
            return $currentUser->hasAnyRole($roles) || $currentUser->hasAnyPermission($permissions);
        }

        if (! empty($roles)) {
            return $currentUser->hasAnyRole($roles);
        }

        if (! empty($permissions)) {
            return $currentUser->hasAnyPermission($permissions);
        }

        return true;
    }

    /**
     * Get URL for a menu item.
     */
    public static function itemUrl(array $item): string
    {
        if (! empty($item['route']) && Route::has($item['route'])) {
            return route($item['route']);
        }

        return (string) ($item['url'] ?? '#');
    }

    /**
     * Check if menu item is active for current route.
     */
    public static function isMenuItemActive(array $item): bool
    {
        $patterns = $item['active'] ?? [];
        if (empty($patterns) && ! empty($item['route'])) {
            $patterns = [$item['route']];
        }

        return ! empty($patterns) && request()->routeIs($patterns);
    }
}
