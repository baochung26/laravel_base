# Hướng dẫn Cache

Tài liệu dùng cache trong project: quy ước key, service, xóa cache khi dữ liệu thay đổi, và ví dụ sử dụng.

---

## 1. Tổng quan

| Thành phần | Mô tả |
|------------|--------|
| **CacheKey** | Helper tạo key thống nhất, dễ đọc và tránh trùng. |
| **CacheService** | Lớp dùng để get/put/remember và xóa cache (theo key hoặc pattern). |
| **UserObserver** | Khi User đổi (create/update/delete) → tự động xóa cache liên quan user đó và cache danh sách users. |
| **HasCache** | Trait gắn vào Model để cache theo từng bản ghi (remember, forget theo model). |

**Cấu hình:** `.env` dùng `CACHE_DRIVER` (file / redis), `CACHE_PREFIX` (tiền tố key). Chi tiết ở [mục 7](#7-cấu-hình).

---

## 2. Quy ước đặt tên key (CacheKey)

Format chung: **`{prefix}:{module}:{identifier}:{suffix?}`**

- **prefix:** Mặc định `laravel` (theo config).
- **module:** Tên nhóm (user, users, role, permissions…).
- **identifier:** Id hoặc định danh (ví dụ: user id, số trang).
- **suffix:** (tùy chọn) Loại dữ liệu (profile, roles, list…).

### Ví dụ dùng CacheKey

File: `app/Helpers/CacheKey.php`

```php
use App\Helpers\CacheKey;

// Cache 1 user
CacheKey::user(1);                    // laravel:user:1
CacheKey::user(1, 'profile');         // laravel:user:1:profile
CacheKey::userProfile(1);             // laravel:user:1:profile
CacheKey::userRoles(1);               // laravel:user:1:roles
CacheKey::userPermissions(1);         // laravel:user:1:permissions

// Cache danh sách users (phân trang, tìm kiếm)
CacheKey::usersList(1);               // laravel:users:list:page:1
CacheKey::usersList(1, 'john');       // laravel:users:list:page:1:search:<md5>

// Role / permission
CacheKey::role('admin');              // laravel:role:admin
CacheKey::rolesList();                // laravel:roles:list
CacheKey::permissionsList();          // laravel:permissions:list

// Key tùy chỉnh (module:identifier:suffix)
CacheKey::generate('product', '123', 'reviews');  // laravel:product:123:reviews
```

**Lưu ý:** Nên luôn dùng `CacheKey` thay vì tự ghép chuỗi để thống nhất và dễ đổi prefix sau này.

---

## 3. CacheService – lấy, lưu, xóa cache

File: `app/Services/Cache/CacheService.php`

### 3.1 Remember (lấy từ cache hoặc tính rồi lưu)

Dùng khi: lần đầu lấy từ DB/tính toán, sau đó trả từ cache đến khi hết TTL.

```php
use App\Helpers\CacheKey;
use App\Services\Cache\CacheService;

$cache = app(CacheService::class);
$userId = 1;

// TTL mặc định 3600 (1 giây), có thể truyền tham số thứ 3
$profile = $cache->remember(
    CacheKey::userProfile($userId),
    function () use ($userId) {
        return User::find($userId)->load('profile')->profile;
    },
    3600  // 1 giờ
);
```

**Demo:** Gọi lần 1 → chạy closure, lưu cache. Gọi lần 2 trong vòng 1 giờ → trả từ cache, không chạy closure.

### 3.2 Get / Put

```php
// Lấy (không có thì null hoặc giá trị mặc định)
$value = $cache->get(CacheKey::user(1), 'default');

// Lưu (TTL giây)
$cache->put(CacheKey::user(1), $userData, 3600);
```

### 3.3 Forget (xóa cache)

```php
// Xóa 1 key
$cache->forget(CacheKey::userProfile($userId));

// Xóa theo pattern (chỉ Redis) – ví dụ mọi key danh sách users
$cache->forgetPattern('users:list:*');

// Xóa toàn bộ cache của module (Redis)
$cache->forgetModule('users');   // xóa laravel:users:*

// Xóa hết cache liên quan 1 user (user đó + danh sách users)
$cache->forgetUser($userId);

// Xóa toàn bộ cache (cẩn thận trên production)
$cache->flush();
```

### 3.4 Cache có tag (chỉ Redis)

Tag dùng để nhóm nhiều key, xóa cả nhóm một lúc.

```php
use App\Helpers\CacheKey;

// Lưu với tag
$users = $cache->rememberWithTags(
    ['users'],                      // tag
    CacheKey::usersList(1),         // key
    fn () => User::paginate(15),    // closure
    3600
);

// Xóa mọi key gắn tag 'users'
$cache->forgetTags(['users']);
```

**Lưu ý:** `forgetPattern` và cache tag chỉ hoạt động khi `CACHE_DRIVER=redis`. Driver `file` không hỗ trợ.

---

## 4. Demo: Cache profile user trong Service

Ví dụ trong một service (hoặc controller) – cache profile user 1 giờ.

```php
namespace App\Services;

use App\Helpers\CacheKey;
use App\Services\Cache\CacheService;

class UserService
{
    public function __construct(
        protected CacheService $cache,
        protected UserRepositoryInterface $userRepo
    ) {}

    /**
     * Lấy profile user, có cache 1 giờ.
     */
    public function getProfile(int $userId): array
    {
        return $this->cache->remember(
            CacheKey::userProfile($userId),
            function () use ($userId) {
                $user = $this->userRepo->withRolesAndPermissions($userId);
                return [
                    'id'   => $user->id,
                    'name' => $user->name,
                    'email'=> $user->email,
                    'roles'=> $user->roles->pluck('name')->toArray(),
                ];
            },
            3600
        );
    }
}
```

Khi user bị sửa (tên, email, role…), cần xóa cache để lần sau không trả dữ liệu cũ. Ở project này **UserObserver** đã làm việc đó (xem mục 5).

---

## 5. Demo: Cache danh sách users (có phân trang, tìm kiếm)

```php
use App\Helpers\CacheKey;
use App\Services\Cache\CacheService;

$cache = app(CacheService::class);
$page = request()->get('page', 1);
$search = request()->get('search');

$key = CacheKey::usersList($page, $search);

$users = $cache->remember($key, function () use ($page, $search) {
    $query = User::query()->with('roles');
    if ($search) {
        $query->where('name', 'like', "%{$search}%")
              ->orWhere('email', 'like', "%{$search}%");
    }
    return $query->paginate(15, ['*'], 'page', $page);
}, 1800);  // 30 phút
```

Khi có user mới/sửa/xóa, **UserObserver** sẽ xóa cache dạng `users:list:*`, nên lần gọi sau sẽ lấy lại dữ liệu mới từ DB và cache lại.

---

## 6. Xóa cache khi dữ liệu thay đổi

### 6.1 Tự động (UserObserver)

File: `app/Observers/UserObserver.php`  
Khi User **created / updated / deleted**:

- Xóa: `user:{id}`, `user:{id}:profile`, `user:{id}:roles`, `user:{id}:permissions`
- Xóa cache danh sách: pattern `users:list:*`
- Nếu dùng tag: xóa tag `users`

Bạn không cần gọi forget thủ công cho các key trên khi đổi user trong app (qua Eloquent).

### 6.2 Xóa thủ công (khi cần)

Ví dụ: sau khi gán role ngoài Eloquent, hoặc sửa dữ liệu trực tiếp trong DB.

```php
use App\Helpers\CacheKey;
use App\Services\Cache\CacheService;

$cache = app(CacheService::class);

// Chỉ xóa cache của 1 user
$cache->forgetUser($userId);

// Hoặc từng key
$cache->forget(CacheKey::userRoles($userId));
$cache->forget(CacheKey::userProfile($userId));

// Xóa toàn bộ cache danh sách users (Redis)
$cache->forgetPattern('users:list:*');
```

---

## 7. Trait HasCache (cache gắn với Model)

File: `app/Traits/HasCache.php`

Dùng khi bạn muốn cache theo từng bản ghi (ví dụ: roles, permissions của user) và tự xóa khi model update/delete.

**Cách dùng:** Khai báo trait trong model (ví dụ `User`), sau đó gọi `rememberCache`, `getCached`, `putCache`, `forgetCache` trên instance.

### Ví dụ (khi Model dùng HasCache)

```php
// Trong Model
use App\Traits\HasCache;

class User extends Model
{
    use HasCache;

    // Tùy chọn: đổi TTL mặc định (giây)
    protected function getCacheTtl(): int
    {
        return 7200; // 2 giờ
    }
}
```

```php
// Trong service hoặc controller
$user = User::find(1);

// Lấy từ cache hoặc tính rồi lưu (key sẽ là laravel:users:1:roles)
$roles = $user->rememberCache('roles', function () use ($user) {
    return $user->roles()->pluck('name')->toArray();
}, 3600);

// Chỉ lấy từ cache (không tính)
$cached = $user->getCached('roles');

// Lưu thủ công
$user->putCache('roles', ['admin', 'user'], 3600);

// Xóa cache của suffix 'roles' hoặc toàn bộ cache của user này
$user->forgetCache('roles');
$user->forgetCache();  // xóa mọi suffix của user 1
```

**Lưu ý:** Model `User` hiện tại trong project **chưa** dùng `HasCache`. Bạn có thể thêm `use HasCache` nếu muốn cache theo từng user như trên. UserObserver vẫn xóa cache khi user đổi (cache do Observer quản lý); HasCache thêm cách cache/forget gắn với từng instance model.

---

## 8. Cấu hình

### Biến môi trường (.env)

```env
# Driver: file (dev) hoặc redis (production khuyến nghị)
CACHE_DRIVER=file
CACHE_PREFIX=laravel_cache
```

**Redis (ví dụ Docker):**

```env
CACHE_DRIVER=redis
CACHE_PREFIX=laravel_cache
REDIS_HOST=redis
REDIS_PORT=6379
REDIS_PASSWORD=null
REDIS_CACHE_DB=1
```

### Gợi ý TTL (giây)

| Loại dữ liệu | TTL gợi ý |
|--------------|-----------|
| Hay đổi (ví dụ: số lượng tồn) | 300–600 (5–10 phút) |
| Đổi vừa (profile, danh sách) | 1800–3600 (30 phút – 1 giờ) |
| Ít đổi (roles, permissions) | 7200–86400 (2–24 giờ) |

---

## 9. Lưu ý và best practices

- **Key:** Luôn dùng `CacheKey`; không hardcode chuỗi key.
- **Xóa khi đổi dữ liệu:** Dựa vào Observer cho User; trường hợp khác (job, command, sửa DB tay) gọi `forget` / `forgetUser` / `forgetPattern` khi cần.
- **Redis vs File:** `forgetPattern`, cache tag chỉ có khi dùng Redis; driver file không hỗ trợ.
- **TTL:** Đặt TTL phù hợp với tần suất thay đổi dữ liệu; tránh TTL quá dài cho dữ liệu hay đổi.
- **Nhạy cảm:** Tránh cache mật khẩu, token đầy đủ; có thể cache thông tin hiển thị (tên, role, v.v.).

---

## 10. Tài liệu liên quan

- [CONFIG_ENVIRONMENT.md](CONFIG_ENVIRONMENT.md) – Cấu hình env, cache.
- [Laravel Cache](https://laravel.com/docs/cache) – Tài liệu chính thức Laravel.
