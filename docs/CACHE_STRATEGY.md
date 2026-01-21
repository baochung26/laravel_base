# Cache Strategy Documentation

Tài liệu về Cache Strategy, Cache Key Convention, và Cache Invalidation.

## 📋 Tổng quan

Hệ thống Cache Strategy bao gồm:
- ✅ **Cache Key Convention** - Quy ước đặt tên cache key thống nhất
- ✅ **Cache Service** - Service layer để quản lý cache
- ✅ **Cache Invalidation** - Cơ chế làm mất hiệu lực cache tự động
- ✅ **Model Cache Trait** - Trait để cache model data
- ✅ **Observer Pattern** - Observer để tự động invalidate cache

## 🔑 1. Cache Key Convention

### Format

```
{prefix}:{module}:{identifier}:{suffix?}
```

**Components:**
- `prefix` - Application prefix (default: `laravel`)
- `module` - Module/table name (e.g., `user`, `users`)
- `identifier` - Resource identifier (e.g., user ID, role name)
- `suffix` - Optional suffix for specific data (e.g., `profile`, `roles`)

### Examples

```php
use App\Helpers\CacheKey;

// User cache keys
CacheKey::user(1)                    // laravel:user:1
CacheKey::user(1, 'profile')         // laravel:user:1:profile
CacheKey::userProfile(1)             // laravel:user:1:profile
CacheKey::userRoles(1)               // laravel:user:1:roles
CacheKey::userPermissions(1)         // laravel:user:1:permissions

// Users list cache keys
CacheKey::usersList(1)               // laravel:users:list:page:1
CacheKey::usersList(1, 'john')       // laravel:users:list:page:1:search:5d41402abc4b2a76b9719d911017c592

// Role cache keys
CacheKey::role('admin')              // laravel:role:admin
CacheKey::rolesList()                // laravel:roles:list

// Permission cache keys
CacheKey::permissionsList()          // laravel:permissions:list
```

### Custom Cache Keys

```php
use App\Helpers\CacheKey;

// Generate custom key
$key = CacheKey::generate('product', '123', 'reviews');
// Result: laravel:product:123:reviews

// With multiple identifiers
$key = CacheKey::generate('order', '123:item:456');
// Result: laravel:order:123:item:456
```

## 🔧 2. Cache Service

### CacheService

Service layer để quản lý cache với các methods:

**Location:** `app/Services/Cache/CacheService.php`

### Methods

#### Remember

```php
use App\Services\Cache\CacheService;

$cacheService = app(CacheService::class);

$value = $cacheService->remember($key, function () {
    return 'expensive computation';
}, 3600); // TTL: 1 hour
```

#### Get

```php
$value = $cacheService->get($key, 'default value');
```

#### Put

```php
$cacheService->put($key, $value, 3600);
```

#### Forget

```php
// Forget single key
$cacheService->forget($key);

// Forget by pattern (Redis only)
$cacheService->forgetPattern('users:list:*');

// Forget entire module
$cacheService->forgetModule('users');

// Forget user-related cache
$cacheService->forgetUser($userId);
```

#### Flush

```php
$cacheService->flush(); // Clear all cache
```

#### Tagged Cache (Redis only)

```php
// Remember with tags
$value = $cacheService->rememberWithTags(
    ['users', 'profiles'],
    $key,
    fn() => 'value',
    3600
);

// Forget by tags
$cacheService->forgetTags(['users', 'profiles']);
```

## 🚫 3. Cache Invalidation

### Automatic Invalidation

Cache được tự động invalidate khi:
- Model được updated
- Model được deleted
- Model được created (invalidates list cache)

### User Observer

UserObserver tự động invalidate cache khi User model thay đổi:

**Location:** `app/Observers/UserObserver.php`

**Registered in:** `app/Providers/AppServiceProvider.php`

```php
// Observer automatically invalidates:
// - User cache
// - User profile cache
// - User roles cache
// - User permissions cache
// - Users list cache
```

### Manual Invalidation

```php
use App\Helpers\CacheKey;
use App\Services\Cache\CacheService;

$cacheService = app(CacheService::class);

// Invalidate user cache
$cacheService->forgetUser($userId);

// Invalidate specific cache
$cacheService->forget(CacheKey::userProfile($userId));

// Invalidate module cache
$cacheService->forgetModule('users');
```

## 🎯 4. Model Cache Trait

### HasCache Trait

Trait để thêm cache functionality vào Models:

**Location:** `app/Traits/HasCache.php`

**Usage:**

```php
use App\Traits\HasCache;

class User extends Model
{
    use HasCache;

    // Override cache TTL if needed
    protected function getCacheTtl(): int
    {
        return 7200; // 2 hours
    }
}
```

### Methods

#### Remember Cache

```php
$user = User::find(1);

// Remember cache for user
$profile = $user->rememberCache('profile', function () use ($user) {
    return $user->load('profile')->profile;
}, 3600);
```

#### Get Cached Value

```php
$profile = $user->getCached('profile');
```

#### Put Cache

```php
$user->putCache('profile', $profileData, 3600);
```

#### Forget Cache

```php
// Forget specific cache
$user->forgetCache('profile');

// Forget all cache for this model
$user->forgetCache();

// Forget all cache for this model type
$user->forgetModelCache();
```

## 💡 5. Usage Examples

### Example 1: Cache User Profile

```php
use App\Helpers\CacheKey;
use App\Services\Cache\CacheService;

$cacheService = app(CacheService::class);
$userId = 1;

// Get user profile with cache
$profile = $cacheService->remember(
    CacheKey::userProfile($userId),
    function () use ($userId) {
        return User::find($userId)->load('profile')->profile;
    },
    3600 // 1 hour
);

// Cache is automatically invalidated when user is updated
```

### Example 2: Cache Users List

```php
use App\Helpers\CacheKey;
use App\Services\Cache\CacheService;

$cacheService = app(CacheService::class);
$page = 1;
$search = 'john';

// Get users list with cache
$users = $cacheService->remember(
    CacheKey::usersList($page, $search),
    function () use ($page, $search) {
        $query = User::query();
        
        if ($search) {
            $query->where('name', 'like', "%{$search}%");
        }
        
        return $query->paginate(15, ['*'], 'page', $page);
    },
    1800 // 30 minutes
);
```

### Example 3: Cache with Model Trait

```php
$user = User::find(1);

// Remember cache using trait
$roles = $user->rememberCache('roles', function () use ($user) {
    return $user->roles()->pluck('name')->toArray();
});

// Get cached value
$cachedRoles = $user->getCached('roles');

// Forget cache
$user->forgetCache('roles');
```

### Example 4: Cache Invalidation on Update

```php
$user = User::find(1);
$user->update(['name' => 'New Name']);

// Cache is automatically invalidated by UserObserver
// - user:1
// - user:1:profile
// - user:1:roles
// - user:1:permissions
// - users:list:*
```

### Example 5: Tagged Cache (Redis)

```php
use App\Helpers\CacheKey;
use App\Services\Cache\CacheService;

$cacheService = app(CacheService::class);

// Remember with tags
$users = $cacheService->rememberWithTags(
    CacheKey::tags(['users']),
    CacheKey::usersList(1),
    fn() => User::paginate(15),
    3600
);

// Invalidate all users cache by tag
$cacheService->forgetTags(['users']);
```

## ⚙️ 6. Cache Configuration

### Environment Variables

```env
CACHE_DRIVER=redis
CACHE_PREFIX=laravel_cache
```

### TTL Configuration

**Default TTL:** 3600 seconds (1 hour)

**Recommended TTL:**
- **Frequently changing data:** 300-600 seconds (5-10 minutes)
- **Moderately changing data:** 1800-3600 seconds (30-60 minutes)
- **Rarely changing data:** 7200-86400 seconds (2-24 hours)

### Cache Drivers

**File (Development):**
```env
CACHE_DRIVER=file
```

**Redis (Production):**
```env
CACHE_DRIVER=redis
REDIS_HOST=127.0.0.1
REDIS_PORT=6379
REDIS_CACHE_DB=1
```

## 📊 7. Cache Strategy Patterns

### Pattern 1: Cache-Aside (Lazy Loading)

```php
// Check cache first
$value = $cacheService->get($key);

if ($value === null) {
    // Cache miss - load from database
    $value = $this->loadFromDatabase();
    
    // Store in cache
    $cacheService->put($key, $value, 3600);
}

return $value;
```

**Or use remember:**
```php
$value = $cacheService->remember($key, function () {
    return $this->loadFromDatabase();
}, 3600);
```

### Pattern 2: Write-Through

```php
// Update database
$user->update($data);

// Update cache immediately
$cacheService->put(CacheKey::user($user->id), $user, 3600);
```

### Pattern 3: Write-Behind (Write-Back)

```php
// Update cache immediately
$cacheService->put($key, $value, 3600);

// Update database asynchronously (via queue)
UpdateUserJob::dispatch($userId, $data);
```

### Pattern 4: Invalidation on Update

```php
// Update model (triggers observer)
$user->update($data);

// Observer automatically invalidates cache
// - user:1
// - user:1:profile
// - users:list:*
```

## 🔒 8. Best Practices

### Cache Key Design

✅ **DO:**
- Use descriptive, consistent naming
- Include all relevant identifiers
- Use CacheKey helper for consistency
- Document cache keys in code

❌ **DON'T:**
- Don't use ambiguous keys
- Don't hardcode cache keys
- Don't use long, complex keys
- Don't forget to include identifiers

### Cache Invalidation

✅ **DO:**
- Invalidate cache on data changes
- Use observers for automatic invalidation
- Invalidate related caches together
- Test cache invalidation

❌ **DON'T:**
- Don't forget to invalidate cache
- Don't invalidate too aggressively
- Don't leave stale cache
- Don't invalidate unrelated caches

### Cache TTL

✅ **DO:**
- Use appropriate TTL for data type
- Consider data update frequency
- Monitor cache hit/miss rates
- Adjust TTL based on usage

❌ **DON'T:**
- Don't use too long TTL for changing data
- Don't use too short TTL for static data
- Don't ignore cache expiration
- Don't cache sensitive data

### Performance

✅ **DO:**
- Cache expensive operations
- Cache frequently accessed data
- Use cache tags for batch operations
- Monitor cache performance

❌ **DON'T:**
- Don't cache everything
- Don't cache rarely accessed data
- Don't cache large objects unnecessarily
- Don't ignore cache memory usage

## 🧪 9. Testing

### Test Cache

```php
use Illuminate\Support\Facades\Cache;
use App\Helpers\CacheKey;
use App\Services\Cache\CacheService;

Cache::fake();

$cacheService = app(CacheService::class);

// Put value
$cacheService->put($key, 'value', 3600);

// Assert cache
Cache::assertHas($key);

// Get value
$value = $cacheService->get($key);
$this->assertEquals('value', $value);
```

### Test Cache Invalidation

```php
$user = User::factory()->create();

// Cache user
$cacheService->put(CacheKey::user($user->id), $user, 3600);

// Update user (triggers observer)
$user->update(['name' => 'New Name']);

// Assert cache is invalidated
Cache::assertMissing(CacheKey::user($user->id));
```

## 📚 10. Related Documentation

- [Configuration & Environment](CONFIG_ENVIRONMENT.md#cache-configuration)
- [Laravel Cache](https://laravel.com/docs/cache)

## 🔗 Tài liệu tham khảo

- [Laravel Cache](https://laravel.com/docs/cache)
- [Redis Documentation](https://redis.io/documentation)
- [Cache Patterns](https://aws.amazon.com/caching/caching-patterns/)
