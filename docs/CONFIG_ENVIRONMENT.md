# Configuration & Environment Documentation

Tài liệu về cấu hình và quản lý environment trong dự án.

## 📋 Tổng quan

Dự án sử dụng `.env` file để quản lý configuration theo environment. Tất cả các config files trong `config/` sử dụng `env()` helper để đọc từ `.env` file.

## 🔧 1. Environment Configuration

### .env.example

File `.env.example` chứa template với tất cả các biến môi trường cần thiết. 

**Lưu ý:** File `.env` không được commit vào git (đã được thêm vào `.gitignore`).

### Tạo .env từ .env.example

```bash
# Copy file .env.example thành .env
cp .env.example .env

# Hoặc trong Docker
docker-compose exec app cp .env.example .env

# Generate application key
php artisan key:generate
```

### Cấu trúc .env.example

File `.env.example` được chia thành các sections:

1. **Application Configuration** - Cấu hình ứng dụng
2. **Logging Configuration** - Cấu hình logging
3. **Database Configuration** - Cấu hình database
4. **Cache Configuration** - Cấu hình cache
5. **Session Configuration** - Cấu hình session
6. **Queue Configuration** - Cấu hình queue
7. **Mail Configuration** - Cấu hình mail
8. **AWS Configuration** - Cấu hình AWS (optional)
9. **Redis Configuration** - Cấu hình Redis (optional)
10. **Broadcasting Configuration** - Cấu hình broadcasting (optional)
11. **File System Configuration** - Cấu hình file system
12. **Sanctum Configuration** - Cấu hình Sanctum
13. **Swagger Configuration** - Cấu hình Swagger

## 📦 2. Configuration Files

### Cache Configuration (`config/cache.php`)

**Environment Variables:**
- `CACHE_DRIVER` - Cache driver (file, redis, database, etc.)
- `CACHE_PREFIX` - Cache key prefix

**Drivers:**
- `file` - File-based cache (default for local)
- `redis` - Redis cache (recommended for production)
- `database` - Database cache
- `memcached` - Memcached cache
- `array` - Array cache (testing only)

**Example:**
```env
# Local Development
CACHE_DRIVER=file

# Production
CACHE_DRIVER=redis
CACHE_PREFIX=laravel_cache
```

**Sử dụng:**
```php
use Illuminate\Support\Facades\Cache;

// Store
Cache::put('key', 'value', 3600);

// Retrieve
$value = Cache::get('key');

// Remember
$value = Cache::remember('key', 3600, function () {
    return 'value';
});
```

### Queue Configuration (`config/queue.php`)

**Environment Variables:**
- `QUEUE_CONNECTION` - Queue connection (sync, database, redis, etc.)
- `QUEUE_FAILED_DRIVER` - Failed jobs driver

**Connections:**
- `sync` - Synchronous (default for local, runs immediately)
- `database` - Database queue (requires migration)
- `redis` - Redis queue (recommended for production)
- `sqs` - AWS SQS
- `beanstalkd` - Beanstalkd

**Example:**
```env
# Local Development
QUEUE_CONNECTION=sync

# Production
QUEUE_CONNECTION=redis
QUEUE_FAILED_DRIVER=database-uuids
```

**Setup Database Queue:**
```bash
# Create jobs table
php artisan queue:table

# Create failed_jobs table
php artisan queue:failed-table

# Run migrations
php artisan migrate
```

**Sử dụng:**
```php
use Illuminate\Support\Facades\Queue;

// Dispatch job
dispatch(new ProcessOrder($order));

// With delay
dispatch(new ProcessOrder($order))->delay(now()->addMinutes(5));
```

### Mail Configuration (`config/mail.php`)

**Environment Variables:**
- `MAIL_MAILER` - Mail driver (smtp, sendmail, mailgun, ses, etc.)
- `MAIL_HOST` - SMTP host
- `MAIL_PORT` - SMTP port
- `MAIL_USERNAME` - SMTP username
- `MAIL_PASSWORD` - SMTP password
- `MAIL_ENCRYPTION` - Encryption (tls, ssl, null)
- `MAIL_FROM_ADDRESS` - From email address
- `MAIL_FROM_NAME` - From name

**Mailers:**
- `smtp` - SMTP (most common)
- `sendmail` - Sendmail
- `mailgun` - Mailgun
- `ses` - AWS SES
- `postmark` - Postmark
- `array` - Array (testing only, doesn't send)

**Example (Local - Mailtrap):**
```env
MAIL_MAILER=smtp
MAIL_HOST=smtp.mailtrap.io
MAIL_PORT=2525
MAIL_USERNAME=your-mailtrap-username
MAIL_PASSWORD=your-mailtrap-password
MAIL_ENCRYPTION=null
MAIL_FROM_ADDRESS="hello@example.com"
MAIL_FROM_NAME="${APP_NAME}"
```

**Example (Production - Gmail):**
```env
MAIL_MAILER=smtp
MAIL_HOST=smtp.gmail.com
MAIL_PORT=587
MAIL_USERNAME=your-email@gmail.com
MAIL_PASSWORD=your-app-password
MAIL_ENCRYPTION=tls
MAIL_FROM_ADDRESS="your-email@gmail.com"
MAIL_FROM_NAME="${APP_NAME}"
```

**Example (Production - SendGrid):**
```env
MAIL_MAILER=smtp
MAIL_HOST=smtp.sendgrid.net
MAIL_PORT=587
MAIL_USERNAME=apikey
MAIL_PASSWORD=your-sendgrid-api-key
MAIL_ENCRYPTION=tls
MAIL_FROM_ADDRESS="hello@yourdomain.com"
MAIL_FROM_NAME="${APP_NAME}"
```

**Sử dụng:**
```php
use Illuminate\Support\Facades\Mail;

// Send mail
Mail::to('user@example.com')->send(new OrderShipped($order));

// Queue mail
Mail::to('user@example.com')->queue(new OrderShipped($order));
```

### File System Configuration (`config/filesystems.php`)

**Environment Variables:**
- `FILESYSTEM_DISK` - Default disk (local, public, s3, etc.)

**Disks:**
- `local` - Local storage (storage/app/)
- `public` - Public storage (storage/app/public/)
- `s3` - AWS S3
- `sftp` - SFTP
- `ftp` - FTP

**Example:**
```env
# Local Development
FILESYSTEM_DISK=local

# Production (with S3)
FILESYSTEM_DISK=s3
AWS_ACCESS_KEY_ID=your-access-key
AWS_SECRET_ACCESS_KEY=your-secret-key
AWS_DEFAULT_REGION=us-east-1
AWS_BUCKET=your-bucket-name
```

**Sử dụng:**
```php
use Illuminate\Support\Facades\Storage;

// Store file
Storage::disk('public')->put('avatars/file.jpg', $contents);

// Get file
$url = Storage::disk('public')->url('avatars/file.jpg');

// Delete file
Storage::disk('public')->delete('avatars/file.jpg');
```

## 🌍 3. Environment-Specific Configuration

### Local Development

```env
APP_ENV=local
APP_DEBUG=true
LOG_LEVEL=debug
CACHE_DRIVER=file
QUEUE_CONNECTION=sync
SESSION_DRIVER=file
FILESYSTEM_DISK=local
```

### Staging

```env
APP_ENV=staging
APP_DEBUG=false
LOG_LEVEL=info
CACHE_DRIVER=redis
QUEUE_CONNECTION=redis
SESSION_DRIVER=redis
FILESYSTEM_DISK=s3
```

### Production

```env
APP_ENV=production
APP_DEBUG=false
LOG_LEVEL=error
CACHE_DRIVER=redis
QUEUE_CONNECTION=redis
SESSION_DRIVER=redis
FILESYSTEM_DISK=s3
```

## 🔐 4. Security Best Practices

### .env File Security

✅ **DO:**
- Never commit `.env` file to git
- Use `.env.example` as template
- Use different `.env` files for different environments
- Rotate secrets regularly
- Use strong passwords and keys

❌ **DON'T:**
- Never share `.env` file
- Never commit secrets to git
- Never use production secrets in development
- Never hardcode credentials in code

### Configuration Caching

Trong production, nên cache config:

```bash
# Cache configuration
php artisan config:cache

# Clear configuration cache
php artisan config:clear
```

## 📚 5. Configuration Best Practices

### Environment Variables

✅ **DO:**
- Use descriptive variable names
- Group related variables together
- Document variables in comments
- Use default values when appropriate
- Validate environment variables

❌ **DON'T:**
- Don't hardcode values in config files
- Don't use environment variables for constants
- Don't expose sensitive data in config files
- Don't skip validation

### Config Files

✅ **DO:**
- Keep config files simple and readable
- Use environment variables for all dynamic values
- Document complex configurations
- Validate configuration on startup

❌ **DON'T:**
- Don't put business logic in config files
- Don't duplicate configuration
- Don't hardcode environment-specific values

## 🔧 6. Configuration Commands

### Clear Configuration Cache

```bash
# Clear all config cache
php artisan config:clear

# Cache configuration (production only)
php artisan config:cache
```

### View Configuration

```bash
# View specific config
php artisan tinker
>>> config('app.name')
>>> config('cache.default')

# List all config
php artisan config:show
```

### Environment Check

```bash
# Check current environment
php artisan env

# Check environment variables
php artisan tinker
>>> env('APP_ENV')
>>> env('APP_DEBUG')
```

## 📝 7. Configuration Examples

### Complete .env Example

Xem file `docs/CONFIG_ENV.example` để xem đầy đủ template của `.env.example`.

### Docker Environment

Trong Docker, có thể sử dụng `.env` file hoặc environment variables trong `docker-compose.yml`:

```yaml
services:
  app:
    environment:
      - APP_ENV=${APP_ENV:-local}
      - APP_DEBUG=${APP_DEBUG:-true}
      - DB_HOST=${DB_HOST:-db}
```

## 🔗 8. Related Documentation

- [Laravel Configuration](https://laravel.com/docs/configuration)
- [Environment Configuration](https://laravel.com/docs/configuration#environment-configuration)
- [Cache Configuration](https://laravel.com/docs/cache)
- [Queue Configuration](https://laravel.com/docs/queues)
- [Mail Configuration](https://laravel.com/docs/mail)
- [File Storage](https://laravel.com/docs/filesystem)

## 📚 Tài liệu tham khảo

- [Laravel Configuration](https://laravel.com/docs/configuration)
- [Environment Variables](https://laravel.com/docs/configuration#environment-configuration)
- [Config Caching](https://laravel.com/docs/configuration#configuration-caching)
