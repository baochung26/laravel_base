<?php

namespace App\Jobs;

use App\Models\User;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;

class SendWelcomeEmailJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    /**
     * The number of times the job may be attempted.
     */
    public int $tries = 3;

    /**
     * The maximum number of seconds the job can run.
     */
    public int $timeout = 120;

    /**
     * The number of seconds to wait before retrying the job.
     *
     * @var array<int>
     */
    public array $backoff = [60, 180, 600]; // 1 minute, 3 minutes, 10 minutes

    /**
     * Create a new job instance.
     */
    public function __construct(
        public User $user
    ) {
    }

    /**
     * Execute the job.
     */
    public function handle(): void
    {
        try {
            Log::info('Sending welcome email', [
                'user_id' => $this->user->id,
                'email' => $this->user->email,
            ]);

            // Simulate sending welcome email
            // In real implementation, you would use Mail facade
            // Mail::to($this->user->email)->send(new WelcomeEmail($this->user));

            Log::info('Welcome email sent successfully', [
                'user_id' => $this->user->id,
            ]);
        } catch (\Exception $e) {
            Log::error('Failed to send welcome email', [
                'user_id' => $this->user->id,
                'error' => $e->getMessage(),
            ]);

            throw $e; // Re-throw to trigger retry
        }
    }

    /**
     * Handle a job failure.
     */
    public function failed(\Throwable $exception): void
    {
        Log::error('SendWelcomeEmailJob failed after all retries', [
            'user_id' => $this->user->id,
            'exception' => $exception->getMessage(),
        ]);
    }
}
