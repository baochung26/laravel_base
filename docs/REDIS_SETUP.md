# Redis Setup Documentation

Hướng dẫn về Redis setup trong Docker và cấu hình Laravel.

## 📋 Tổng quan

Redis đã được thêm vào `docker-compose.yml` để hỗ trợ:
- ✅ **Cache** - Redis cache driver
- ✅ **Queue** - Redis queue driver
- ✅ **Session** - Redis session driver (optional)

## 🐳 1. Docker Setup

### Redis Service

Redis service đã được thêm vào `docker-compose.yml`:

```yaml
redis:
  image: redis:7-alpine
  container_name: laravel_redis
  restart: unless-stopped
  ports:
    - "6379:6379"
  command: redis-server --appendonly yes
  volumes:
    - redisdata:/data
  networks:
    - laravel
  healthcheck:
    test: ["CMD", "redis-cli", "ping"]
    interval: 10s
    timeout: 3s
    retries: 3
```

### PHP Redis Extension

Redis extension đã được thêm vào `Dockerfile`:

```dockerfile
# Install Redis extension
RUN pecl install redis && docker-php-ext-enable redis
```

## 🔧 2. Configuration

### Environment Variables

**For Docker:**
```env
REDIS_HOST=redis
REDIS_PASSWORD=null
REDIS_PORT=6379
REDIS_DB=0
REDIS_CACHE_DB=1
```

**For Local Development (outside Docker):**
```env
REDIS_HOST=127.0.0.1
REDIS_PASSWORD=null
REDIS_PORT=6379
REDIS_DB=0
REDIS_CACHE_DB=1
```

### Cache Configuration

```env
CACHE_DRIVER=redis
```

### Queue Configuration

```env
QUEUE_CONNECTION=redis
```

### Session Configuration (Optional)

```env
SESSION_DRIVER=redis
```

## 🚀 3. Usage

### Start Redis

```bash
# Start all services including Redis
docker-compose up -d

# Or start only Redis
docker-compose up -d redis
```

### Test Redis Connection

```bash
# Test from app container
docker-compose exec app php artisan tinker
>>> Cache::put('test', 'value', 60);
>>> Cache::get('test');
```

### Redis CLI

```bash
# Connect to Redis CLI
docker-compose exec redis redis-cli

# Test connection
PING

# List keys
KEYS *

# Get value
GET laravel_cache:test
```

## 📚 4. Redis Databases

Laravel sử dụng các Redis databases khác nhau:

- **Database 0** - Default (queue, sessions)
- **Database 1** - Cache (`REDIS_CACHE_DB=1`)

## 🔗 Related Documentation

- [Cache Strategy](CACHE_STRATEGY.md)
- [Queue & Scheduler](QUEUE_SCHEDULER.md)
- [Configuration & Environment](CONFIG_ENVIRONMENT.md)
