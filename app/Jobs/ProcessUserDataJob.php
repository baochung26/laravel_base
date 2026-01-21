<?php

namespace App\Jobs;

use App\Models\User;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;

class ProcessUserDataJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    /**
     * The number of times the job may be attempted.
     */
    public int $tries = 5;

    /**
     * The maximum number of seconds the job can run.
     */
    public int $timeout = 300;

    /**
     * Exponential backoff: [10s, 20s, 40s, 80s, 160s]
     */
    public array $backoff = [10, 20, 40, 80, 160];

    /**
     * Create a new job instance.
     */
    public function __construct(
        public int $userId,
        public array $data
    ) {
    }

    /**
     * Execute the job.
     */
    public function handle(): void
    {
        $user = User::findOrFail($this->userId);

        Log::info('Processing user data', [
            'user_id' => $user->id,
            'attempt' => $this->attempts(),
        ]);

        try {
            // Simulate data processing
            // In real implementation, you would process the data here
            sleep(2);

            Log::info('User data processed successfully', [
                'user_id' => $user->id,
            ]);
        } catch (\Exception $e) {
            Log::error('Failed to process user data', [
                'user_id' => $user->id,
                'attempt' => $this->attempts(),
                'error' => $e->getMessage(),
            ]);

            throw $e; // Re-throw to trigger retry
        }
    }

    /**
     * Determine the time at which the job should timeout.
     */
    public function retryUntil(): \DateTime
    {
        return now()->addMinutes(10);
    }

    /**
     * Handle a job failure.
     */
    public function failed(\Throwable $exception): void
    {
        Log::error('ProcessUserDataJob failed after all retries', [
            'user_id' => $this->userId,
            'exception' => $exception->getMessage(),
        ]);
    }
}
