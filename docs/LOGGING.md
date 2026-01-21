# Logging & Monitoring Documentation

Tài liệu về hệ thống Logging & Monitoring với JSON logs, Request ID, và Slow Query logging.

## 📋 Tổng quan

Hệ thống Logging & Monitoring bao gồm:
- ✅ **JSON Logging** - Logs định dạng JSON (dễ đưa lên ELK stack)
- ✅ **Request ID / Correlation ID** - Track requests qua hệ thống
- ✅ **Slow Query Logging** - Tự động log các queries chậm
- ✅ **Structured Logging** - Logs có cấu trúc với metadata

## 📝 1. JSON Logging

### Cấu hình

Logging được cấu hình trong `config/logging.php`:

- **`json` channel** - Logs định dạng JSON
- **`daily` driver** - Rotate logs hàng ngày
- **30 days retention** - Giữ logs trong 30 ngày

### Log Files

```
storage/logs/
├── laravel.log          # Standard logs (text format)
├── laravel-json.log     # JSON format logs (ELK-ready)
└── query.log            # Query logs (JSON format)
```

### JSON Log Format

Tất cả logs được format theo JSON:

```json
{
    "message": "User logged in successfully",
    "context": {
        "user_id": 1,
        "email": "john@example.com"
    },
    "level": 200,
    "level_name": "INFO",
    "channel": "default",
    "datetime": "2024-01-01T12:00:00.000000+00:00",
    "extra": {
        "request_id": "laravel-20240101120000-abc12345",
        "correlation_id": "laravel-20240101120000-abc12345",
        "user_id": 1,
        "method": "POST",
        "url": "http://localhost:8000/api/v1/login",
        "ip": "127.0.0.1"
    }
}
```

### Sử dụng

```php
use Illuminate\Support\Facades\Log;

// Standard logging (sẽ được format JSON)
Log::info('User created', ['user_id' => 1]);
Log::warning('Slow query detected', ['query_time' => '1500ms']);
Log::error('Database connection failed', ['error' => $e->getMessage()]);

// Log với channel cụ thể
Log::channel('json')->info('Custom log message');
Log::channel('query')->warning('Slow query', ['query' => '...']);
```

## 🔍 2. Request ID / Correlation ID

### Mục đích

Request ID giúp:
- Track requests qua toàn bộ hệ thống
- Correlate logs với requests cụ thể
- Debug issues dễ dàng hơn
- Monitor request flow

### RequestId Middleware

**RequestIdMiddleware** tự động:
- Generate Request ID nếu chưa có
- Accept Request ID từ header `X-Request-ID` hoặc `X-Correlation-ID`
- Thêm Request ID vào response headers
- Inject Request ID vào application container

### Sử dụng Request ID

#### Trong Headers

Client có thể gửi Request ID:

```http
POST /api/v1/login
X-Request-ID: custom-request-id-12345
Content-Type: application/json

{
    "email": "john@example.com",
    "password": "password123"
}
```

Response sẽ bao gồm Request ID:

```http
HTTP/1.1 200 OK
X-Request-ID: custom-request-id-12345
X-Correlation-ID: custom-request-id-12345

{
    "success": true,
    "message": "Login successful",
    "data": {...}
}
```

#### Trong Code

```php
use App\Helpers\RequestId;

// Get current Request ID
$requestId = RequestId::get();

// Set Request ID (for correlation)
RequestId::set('custom-request-id');

// Request ID được tự động thêm vào tất cả logs
Log::info('Processing request', ['step' => 'validation']);
// Log sẽ tự động include request_id trong extra
```

### Request ID Format

```
{app-name}-{date}{time}-{uuid-prefix}

Example: laravel-20240101120000-abc12345
```

## 🔍 3. Slow Query Logging

### Cấu hình

Trong `.env`:

```env
LOG_ENABLE_QUERY_LOG=true          # Enable query logging
LOG_SLOW_QUERY_THRESHOLD=1000      # Threshold in milliseconds (default: 1000ms = 1 second)
```

Trong `config/logging.php`:

```php
'enable_query_log' => env('LOG_ENABLE_QUERY_LOG', false),
'slow_query_threshold' => env('LOG_SLOW_QUERY_THRESHOLD', 1000), // milliseconds
```

### LogQueryMiddleware

**LogQueryMiddleware** tự động:
- Enable query logging
- Track execution time
- Log slow queries (queries > threshold)
- Log all queries trong debug mode

### Slow Query Log Format

```json
{
    "message": "Slow query detected",
    "context": {
        "query": "select * from `users` where `email` = ?",
        "bindings": ["john@example.com"],
        "time": "1500ms",
        "threshold": "1000ms",
        "url": "http://localhost:8000/api/v1/users",
        "method": "GET",
        "ip": "127.0.0.1",
        "user_id": 1
    },
    "level": 300,
    "level_name": "WARNING",
    "channel": "query",
    "datetime": "2024-01-01T12:00:00.000000+00:00",
    "extra": {
        "request_id": "laravel-20240101120000-abc12345",
        "correlation_id": "laravel-20240101120000-abc12345",
        "user_id": 1,
        "method": "GET",
        "url": "http://localhost:8000/api/v1/users",
        "ip": "127.0.0.1"
    }
}
```

### Debug Mode Query Log

Trong debug mode, tất cả queries được log:

```json
{
    "message": "Query log",
    "context": {
        "queries": [
            {
                "query": "select * from `users` where `id` = ?",
                "bindings": [1],
                "time": 2.5
            }
        ],
        "total_queries": 1,
        "execution_time": "5.23ms",
        "url": "http://localhost:8000/api/v1/users/1",
        "method": "GET"
    },
    "level": 100,
    "level_name": "DEBUG",
    "channel": "query",
    "extra": {
        "request_id": "laravel-20240101120000-abc12345"
    }
}
```

## 🔧 4. Cấu hình

### Middleware Stack

Request ID middleware được đăng ký trong `bootstrap/app.php`:

```php
$middleware->append(\App\Http\Middleware\RequestIdMiddleware::class);
```

Query logging middleware (optional):

```php
if (config('logging.enable_query_log', false)) {
    $middleware->append(\App\Http\Middleware\LogQueryMiddleware::class);
}
```

### Environment Variables

```env
# Logging
LOG_CHANNEL=stack
LOG_LEVEL=debug
LOG_ENABLE_QUERY_LOG=false
LOG_SLOW_QUERY_THRESHOLD=1000
```

### Log Channels

- **`stack`** - Combine multiple channels (daily + json)
- **`json`** - JSON format logs (ELK-ready)
- **`query`** - Query logs (JSON format)
- **`daily`** - Daily rotated logs (text format)

## 📊 5. ELK Stack Integration

### Chuẩn bị

JSON logs đã sẵn sàng để đưa lên ELK stack:
- Logs format JSON
- Structured data với metadata
- Request ID để correlate
- Timestamps ISO format

### Log File Location

```
storage/logs/laravel-json.log    # JSON logs (ELK-ready)
storage/logs/query.log            # Query logs (JSON format)
```

### Log Format cho ELK

```json
{
    "message": "Log message",
    "context": {...},
    "level": 200,
    "level_name": "INFO",
    "channel": "default",
    "datetime": "2024-01-01T12:00:00.000000+00:00",
    "extra": {
        "request_id": "laravel-20240101120000-abc12345",
        "correlation_id": "laravel-20240101120000-abc12345",
        "user_id": 1,
        "method": "POST",
        "url": "http://localhost:8000/api/v1/login",
        "ip": "127.0.0.1"
    }
}
```

### Filebeat Configuration (Example)

```yaml
filebeat.inputs:
  - type: log
    enabled: true
    paths:
      - /var/www/storage/logs/laravel-json.log*
    json.keys_under_root: true
    json.add_error_key: true
    fields:
      log_type: application
      environment: production
    fields_under_root: true

output.logstash:
  hosts: ["logstash:5044"]
```

## 📝 6. Best Practices

### Logging Levels

- **DEBUG** - Detailed information for debugging
- **INFO** - General information
- **NOTICE** - Normal but significant events
- **WARNING** - Warning messages (e.g., slow queries)
- **ERROR** - Error messages
- **CRITICAL** - Critical conditions
- **ALERT** - Action must be taken immediately
- **EMERGENCY** - System is unusable

### Sử dụng Request ID

✅ **DO:**
- Luôn include Request ID trong API responses
- Sử dụng Request ID để correlate logs
- Forward Request ID trong internal API calls

❌ **DON'T:**
- Không generate new Request ID cho mỗi log entry
- Không expose sensitive data trong logs
- Không log passwords hoặc tokens

### Query Logging

✅ **DO:**
- Enable query logging trong development
- Set appropriate threshold cho slow queries
- Monitor và optimize slow queries

❌ **DON'T:**
- Không enable query logging trong production (trừ khi cần thiết)
- Không log queries quá thường xuyên
- Không log sensitive data (passwords, tokens)

## 🧪 7. Testing

### Test Request ID

```php
// Test Request ID middleware
$response = $this->get('/api/v1/me');
$response->assertHeader('X-Request-ID');

// Test Request ID trong logs
Log::info('Test log');
$logContent = file_get_contents(storage_path('logs/laravel-json.log'));
$this->assertStringContainsString('request_id', $logContent);
```

### Test Slow Query Logging

```php
// Create slow query (trong test)
DB::table('users')->whereRaw('SLEEP(2)')->get();

// Check log file
$logContent = file_get_contents(storage_path('logs/query.log'));
$this->assertStringContainsString('Slow query detected', $logContent);
```

## 📚 Tài liệu tham khảo

- [Laravel Logging](https://laravel.com/docs/logging)
- [Monolog Documentation](https://github.com/Seldaek/monolog)
- [ELK Stack Documentation](https://www.elastic.co/guide/index.html)
