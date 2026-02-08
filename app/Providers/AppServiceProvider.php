<?php

namespace App\Providers;

use App\Repositories\Contracts\RepositoryInterface;
use App\Repositories\Contracts\UserRepositoryInterface;
use App\Repositories\Eloquent\UserRepository;
use App\View\Composers\DashboardSidebarComposer;
use Illuminate\Support\Facades\View;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        // Bind Repository interfaces to implementations
        $this->app->bind(UserRepositoryInterface::class, UserRepository::class);

        // Register other repositories here when needed
        // $this->app->bind(ProductRepositoryInterface::class, ProductRepository::class);
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        // Spatie Permission: cache is used by default. Clear only when roles/permissions
        // change (e.g. in seeders or admin UI); do not clear on every request.

        // Register User Observer for cache invalidation
        \App\Models\User::observe(\App\Observers\UserObserver::class);

        View::composer('layouts.app', DashboardSidebarComposer::class);
    }
}
