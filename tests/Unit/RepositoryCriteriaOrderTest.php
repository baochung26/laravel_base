<?php

namespace Tests\Unit;

use App\Repositories\Eloquent\UserRepository;
use Tests\TestCase;

class RepositoryCriteriaOrderTest extends TestCase
{
    public function test_it_applies_where_and_or_where_in_order(): void
    {
        $repo = $this->app->make(UserRepository::class);

        $query = $repo
            ->where('name', 'John')
            ->orWhere('email', 'john@example.com')
            ->query();

        $this->assertSame(
            'select * from "users" where "name" = ? or "email" = ?',
            $query->toSql()
        );
    }

    public function test_it_keeps_nested_where_order(): void
    {
        $repo = $this->app->make(UserRepository::class);

        $query = $repo
            ->where('status', 'active')
            ->whereNested(function ($q) {
                $q->where('name', 'like', '%john%')
                    ->orWhere('email', 'like', '%john%');
            })
            ->orWhere('id', 10)
            ->query();

        $this->assertSame(
            'select * from "users" where "status" = ? and ("name" like ? or "email" like ?) or "id" = ?',
            $query->toSql()
        );
    }

    public function test_it_keeps_where_has_binding_order(): void
    {
        $repo = $this->app->make(UserRepository::class);

        $query = $repo
            ->where('email', 'john@example.com')
            ->whereHas('roles', function ($q) {
                $q->where('name', 'admin');
            })
            ->orWhere('id', 10)
            ->query();

        $this->assertSame(
            ['john@example.com', 'admin', 10],
            $query->getBindings()
        );
    }

    public function test_it_supports_or_where_has_with_bindings(): void
    {
        $repo = $this->app->make(UserRepository::class);

        $query = $repo
            ->where('status', 'active')
            ->orWhereHas('roles', function ($q) {
                $q->where('name', 'editor');
            })
            ->query();

        $this->assertSame(
            ['active', 'editor'],
            $query->getBindings()
        );
    }

    public function test_it_supports_where_between_and_where_date_bindings(): void
    {
        $repo = $this->app->make(UserRepository::class);

        $query = $repo
            ->whereBetween('id', [5, 10])
            ->whereDate('created_at', '2026-02-01')
            ->query();

        $this->assertSame(
            [5, 10, '2026-02-01'],
            $query->getBindings()
        );
    }

    public function test_it_supports_or_where_between_and_or_where_date_bindings(): void
    {
        $repo = $this->app->make(UserRepository::class);

        $query = $repo
            ->where('status', 'active')
            ->orWhereBetween('id', [1, 3])
            ->orWhereDate('created_at', '<', '2026-01-01')
            ->query();

        $this->assertSame(
            ['active', 1, 3, '2026-01-01'],
            $query->getBindings()
        );
    }
}
