# Queue & Scheduler Documentation

Tài liệu về Queue và Scheduler (Cron Jobs) trong dự án.

## 📋 Tổng quan

Hệ thống Queue & Scheduler bao gồm:
- ✅ **Queue Driver (Redis)** - Queue system với Redis backend
- ✅ **Job Examples** - Ví dụ các Job với retry/backoff
- ✅ **Scheduler (Cron)** - Task scheduler với Laravel Scheduler
- ✅ **Failed Jobs Table** - Quản lý failed jobs
- ✅ **Retry/Backoff** - Cấu hình retry và backoff cho jobs

## 🔄 1. Queue Configuration

### Queue Driver Setup

**Environment Variables:**
```env
QUEUE_CONNECTION=redis
QUEUE_FAILED_DRIVER=database-uuids
```

**Redis Configuration:**
```env
REDIS_HOST=redis
REDIS_PASSWORD=null
REDIS_PORT=6379
REDIS_DB=0
```

## 🐳 Docker Runtime (Queue Worker + Scheduler)

Dự án đã cấu hình sẵn 2 container chạy nền:

- **`queue`**: chạy `php artisan queue:work ...`
- **`scheduler`**: chạy `php artisan schedule:work`

### Khởi động

```bash
docker compose up -d
```

### Kiểm tra logs

```bash
docker compose logs -f queue
docker compose logs -f scheduler
```

### Restart worker/scheduler (khi deploy code mới)

```bash
docker compose restart queue scheduler
```

### Queue Connections

**Local Development:**
```env
QUEUE_CONNECTION=sync  # Runs immediately
```

**Production:**
```env
QUEUE_CONNECTION=redis  # Redis queue
```

### Database Setup

**Run Migrations:**
```bash
# Jobs table (if using database queue)
php artisan queue:table
php artisan migrate

# Failed jobs table (always needed)
php artisan queue:failed-table
php artisan migrate
```

Migrations đã được tạo sẵn:
- `database/migrations/2024_01_01_000003_create_jobs_table.php`
- `database/migrations/2024_01_01_000004_create_failed_jobs_table.php`

## 📦 2. Job Examples

### SendWelcomeEmailJob

Job gửi welcome email cho user mới với retry và backoff.

**Features:**
- Retry: 3 attempts
- Backoff: [60s, 180s, 600s]
- Timeout: 120 seconds

**Location:** `app/Jobs/SendWelcomeEmailJob.php`

**Usage:**
```php
use App\Jobs\SendWelcomeEmailJob;
use App\Models\User;

// Dispatch job
$user = User::find(1);
SendWelcomeEmailJob::dispatch($user);

// With delay
SendWelcomeEmailJob::dispatch($user)->delay(now()->addMinutes(5));

// On specific queue
SendWelcomeEmailJob::dispatch($user)->onQueue('emails');
```

### ProcessUserDataJob

Job xử lý data của user với exponential backoff.

**Features:**
- Retry: 5 attempts
- Backoff: [10s, 20s, 40s, 80s, 160s] (exponential)
- Timeout: 300 seconds

**Location:** `app/Jobs/ProcessUserDataJob.php`

**Usage:**
```php
use App\Jobs\ProcessUserDataJob;

ProcessUserDataJob::dispatch($userId, $data);
```

### CleanupExpiredTokensJob

Job cleanup expired tokens, chạy định kỳ qua scheduler.

**Features:**
- Retry: 3 attempts
- Timeout: 300 seconds

**Location:** `app/Jobs/CleanupExpiredTokensJob.php`

## 🔄 3. Job Configuration

### Retry Configuration

**Property:**
```php
public int $tries = 3; // Number of attempts
```

**Method:**
```php
public function retryUntil(): \DateTime
{
    return now()->addMinutes(10); // Retry within 10 minutes
}
```

### Backoff Configuration

**Fixed Backoff:**
```php
public array $backoff = [60, 180, 600]; // 1min, 3min, 10min
```

**Exponential Backoff:**
```php
public array $backoff = [10, 20, 40, 80, 160]; // Exponential
```

**Dynamic Backoff:**
```php
public function backoff(): array
{
    return [60, 120, 240];
}
```

### Timeout Configuration

```php
public int $timeout = 120; // 120 seconds
```

### Queue Configuration

```php
// On specific queue
public $queue = 'emails';

// On connection
public $connection = 'redis';
```

## 📅 4. Scheduler Configuration

### Kernel Schedule

Scheduler được cấu hình trong `app/Console/Kernel.php`.

**Example Tasks:**

```php
// Run daily at 2 AM
$schedule->job(new \App\Jobs\CleanupExpiredTokensJob())
    ->dailyAt('02:00')
    ->name('cleanup-expired-tokens')
    ->withoutOverlapping()
    ->onOneServer();

// Run daily at 3 AM
$schedule->command('log:clear')
    ->dailyAt('03:00')
    ->name('cleanup-old-logs')
    ->withoutOverlapping();

// Run hourly
$schedule->command('your:command')->hourly();

// Run every 5 minutes
$schedule->command('your:command')->everyFiveMinutes();

// Run on specific days
$schedule->command('your:command')->mondays()->at('09:00');

// Run between times
$schedule->command('your:command')->hourly()->between('8:00', '17:00');
```

### Schedule Frequency

- `->everyMinute()` - Every minute
- `->everyFiveMinutes()` - Every 5 minutes
- `->everyTenMinutes()` - Every 10 minutes
- `->everyFifteenMinutes()` - Every 15 minutes
- `->everyThirtyMinutes()` - Every 30 minutes
- `->hourly()` - Every hour
- `->daily()` - Daily at midnight
- `->dailyAt('13:00')` - Daily at specific time
- `->weekly()` - Weekly
- `->monthly()` - Monthly
- `->yearly()` - Yearly

### Schedule Constraints

- `->weekdays()` - Only weekdays
- `->weekends()` - Only weekends
- `->mondays()` - Only Mondays
- `->between('8:00', '17:00')` - Between times
- `->unlessBetween('8:00', '17:00')` - Unless between times
- `->when(function () { return true; })` - Conditional

### Schedule Options

- `->withoutOverlapping()` - Prevent overlapping
- `->onOneServer()` - Run on single server only
- `->runInBackground()` - Run in background
- `->emailOutputTo('admin@example.com')` - Email output
- `->appendOutputTo('path/to/file.log')` - Append to file

## ⏰ 5. Cron Setup

### Add Cron Entry

Thêm entry sau vào crontab:

```bash
* * * * * cd /path-to-your-project && php artisan schedule:run >> /dev/null 2>&1
```

**With Logging:**
```bash
* * * * * cd /path-to-your-project && php artisan schedule:run >> /var/log/laravel-scheduler.log 2>&1
```

**Docker:**
```yaml
# In docker-compose.yml, add cron service or run in existing container
services:
  cron:
    image: your-php-image
    command: >
      sh -c "echo '* * * * * cd /var/www && php artisan schedule:run >> /dev/null 2>&1' | crontab - && crond -f"
```

### Verify Cron

```bash
# Test scheduler
php artisan schedule:list

# Run scheduler manually
php artisan schedule:run
```

## 🚫 6. Failed Jobs Management

### Failed Jobs Table

Failed jobs được lưu trong `failed_jobs` table.

**View Failed Jobs:**
```bash
php artisan queue:failed
```

**Retry Failed Job:**
```bash
# Retry specific job
php artisan queue:retry {id}

# Retry all failed jobs
php artisan queue:retry all
```

**Delete Failed Job:**
```bash
# Delete specific job
php artisan queue:forget {id}

# Delete all failed jobs
php artisan queue:flush
```

### Job Failed Handler

```php
public function failed(\Throwable $exception): void
{
    Log::error('Job failed after all retries', [
        'exception' => $exception->getMessage(),
    ]);
    
    // Send notification, update database, etc.
}
```

## 🎯 7. Queue Worker

### Start Queue Worker

```bash
# Basic
php artisan queue:work

# With specific connection
php artisan queue:work redis

# With specific queue
php artisan queue:work --queue=emails,default

# With max tries
php artisan queue:work --tries=3

# With timeout
php artisan queue:work --timeout=60

# Daemon mode (keep running)
php artisan queue:work --daemon
```

### Supervisor Configuration

**Install Supervisor:**
```bash
sudo apt-get install supervisor
```

**Configuration:** `/etc/supervisor/conf.d/laravel-worker.conf`

```ini
[program:laravel-worker]
process_name=%(program_name)s_%(process_num)02d
command=php /path-to-your-project/artisan queue:work redis --sleep=3 --tries=3 --max-time=3600
autostart=true
autorestart=true
stopasgroup=true
killasgroup=true
user=www-data
numprocs=2
redirect_stderr=true
stdout_logfile=/path-to-your-project/storage/logs/worker.log
stopwaitsecs=3600
```

**Start Supervisor:**
```bash
sudo supervisorctl reread
sudo supervisorctl update
sudo supervisorctl start laravel-worker:*
```

## 📝 8. Usage Examples

### Dispatch Job

```php
use App\Jobs\SendWelcomeEmailJob;

// Immediate dispatch
SendWelcomeEmailJob::dispatch($user);

// With delay
SendWelcomeEmailJob::dispatch($user)->delay(now()->addMinutes(5));

// On specific queue
SendWelcomeEmailJob::dispatch($user)->onQueue('emails');

// On specific connection
SendWelcomeEmailJob::dispatch($user)->onConnection('redis');
```

### Chain Jobs

```php
use Illuminate\Support\Facades\Bus;

Bus::chain([
    new ProcessOrder($order),
    new SendOrderConfirmation($order),
    new UpdateInventory($order),
])->dispatch();
```

### Batch Jobs

```php
use Illuminate\Support\Facades\Bus;

$batch = Bus::batch([
    new ProcessOrder($order1),
    new ProcessOrder($order2),
    new ProcessOrder($order3),
])->then(function (Batch $batch) {
    // All jobs completed successfully
})->catch(function (Batch $batch, \Throwable $e) {
    // First batch job failure detected
})->finally(function (Batch $batch) {
    // The batch has finished executing
})->dispatch();
```

### Check Job Status

```php
use App\Jobs\SendWelcomeEmailJob;

$job = SendWelcomeEmailJob::dispatch($user);

$jobId = $job->getJobId();

// Check if job is processed
$job->getJobId(); // Returns job ID
```

## 🧪 9. Testing

### Test Jobs

```php
use Illuminate\Support\Facades\Queue;

// Assert job was dispatched
Queue::fake();

dispatch(new SendWelcomeEmailJob($user));

Queue::assertPushed(SendWelcomeEmailJob::class);

// Assert job was dispatched to specific queue
Queue::assertPushedOn('emails', SendWelcomeEmailJob::class);
```

### Test Scheduler

```php
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Schedule;

// Run scheduled commands
Artisan::call('schedule:run');
```

## 📊 10. Monitoring

### Queue Monitoring

```bash
# Check queue status
php artisan queue:monitor redis:default,redis:emails

# Check failed jobs
php artisan queue:failed
```

### Log Monitoring

Check logs for job execution:
```bash
tail -f storage/logs/laravel.log | grep "Job"
```

## 🔧 11. Best Practices

### Job Design

✅ **DO:**
- Keep jobs focused on single responsibility
- Use retry and backoff appropriately
- Log job execution and failures
- Handle exceptions properly
- Use appropriate timeouts

❌ **DON'T:**
- Don't put too much logic in jobs
- Don't forget to handle failures
- Don't use infinite retries
- Don't ignore job timeouts

### Scheduler Design

✅ **DO:**
- Use descriptive names for scheduled tasks
- Use `withoutOverlapping()` for long-running tasks
- Use `onOneServer()` for multi-server deployments
- Group related tasks together
- Document scheduled tasks

❌ **DON'T:**
- Don't schedule too many tasks at once
- Don't forget to test scheduled tasks
- Don't ignore task failures
- Don't overlap long-running tasks

## 📚 12. Related Documentation

- [Laravel Queues](https://laravel.com/docs/queues)
- [Laravel Task Scheduling](https://laravel.com/docs/scheduling)
- [Redis Configuration](CONFIG_ENVIRONMENT.md#redis-configuration)

## 🔗 Tài liệu tham khảo

- [Laravel Queues](https://laravel.com/docs/queues)
- [Laravel Task Scheduling](https://laravel.com/docs/scheduling)
- [Supervisor Configuration](http://supervisord.org/)
