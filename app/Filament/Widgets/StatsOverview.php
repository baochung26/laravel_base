<?php

namespace App\Filament\Widgets;

use App\Models\User;
use Filament\Widgets\StatsOverviewWidget as BaseWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;

class StatsOverview extends BaseWidget
{
    protected function getStats(): array
    {
        return [
            Stat::make('Total Users', User::count())
                ->description('All registered users')
                ->descriptionIcon('heroicon-m-users')
                ->color('success')
                ->chart([7, 3, 4, 5, 6, 3, 5]),
            Stat::make('Total Roles', Role::count())
                ->description('Active roles')
                ->descriptionIcon('heroicon-m-shield-check')
                ->color('info'),
            Stat::make('Total Permissions', Permission::count())
                ->description('Available permissions')
                ->descriptionIcon('heroicon-m-key')
                ->color('warning'),
        ];
    }
}
