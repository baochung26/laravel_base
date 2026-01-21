<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Log;

class LogClearCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'log:clear {--days=7 : Number of days to keep logs}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Clear old log files';

    /**
     * Execute the console command.
     */
    public function handle(): int
    {
        $days = (int) $this->option('days');
        $logPath = storage_path('logs');
        $cutoffDate = now()->subDays($days);

        $this->info("Clearing logs older than {$days} days...");

        $deletedCount = 0;
        $logFiles = File::files($logPath);

        foreach ($logFiles as $file) {
            if (File::lastModified($file) < $cutoffDate->timestamp) {
                File::delete($file);
                $deletedCount++;
                $this->line("Deleted: {$file->getFilename()}");
            }
        }

        $this->info("Deleted {$deletedCount} log file(s).");

        Log::info('Log cleanup completed', [
            'deleted_count' => $deletedCount,
            'days' => $days,
        ]);

        return Command::SUCCESS;
    }
}
