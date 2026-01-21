<?php

namespace App\Console;

use Illuminate\Console\Scheduling\Schedule;
use Illuminate\Foundation\Console\Kernel as ConsoleKernel;

class Kernel extends ConsoleKernel
{
    /**
     * Define the application's command schedule.
     */
    protected function schedule(Schedule $schedule): void
    {
        // Cleanup expired tokens - Run daily at 2 AM
        $schedule->job(new \App\Jobs\CleanupExpiredTokensJob())
            ->dailyAt('02:00')
            ->name('cleanup-expired-tokens')
            ->withoutOverlapping()
            ->onOneServer();

        // Cleanup old logs - Run daily at 3 AM
        $schedule->command('log:clear')
            ->dailyAt('03:00')
            ->name('cleanup-old-logs')
            ->withoutOverlapping();

        // Run queue worker restart - Every hour (if using supervisor)
        // $schedule->command('queue:restart')->hourly();

        // Flush expired cache - Run daily at 4 AM
        $schedule->command('cache:prune-stale-tags')->dailyAt('04:00');

        // Database backup (if you have backup command) - Run daily at 1 AM
        // $schedule->command('backup:run')->dailyAt('01:00');

        // Example: Run task every minute (testing only)
        // $schedule->call(function () {
        //     \Log::info('Scheduled task executed');
        // })->everyMinute();

        // Example: Run task every 5 minutes
        // $schedule->command('your:command')->everyFiveMinutes();

        // Example: Run task every hour
        // $schedule->command('your:command')->hourly();

        // Example: Run task daily
        // $schedule->command('your:command')->daily();

        // Example: Run task weekly
        // $schedule->command('your:command')->weekly();

        // Example: Run task monthly
        // $schedule->command('your:command')->monthly();

        // Example: Run task on specific days
        // $schedule->command('your:command')->mondays()->at('09:00');
        // $schedule->command('your:command')->weekdays()->at('09:00');

        // Example: Run task between specific times
        // $schedule->command('your:command')->hourly()->between('8:00', '17:00');

        // Example: Run task only in production
        // if (app()->environment('production')) {
        //     $schedule->command('your:command')->daily();
        // }
    }

    /**
     * Register the commands for the application.
     */
    protected function commands(): void
    {
        $this->load(__DIR__.'/Commands');

        require base_path('routes/console.php');
    }
}
