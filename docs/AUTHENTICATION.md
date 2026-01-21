# Authentication & Authorization Guide

Hướng dẫn sử dụng hệ thống Authentication và Authorization trong dự án Laravel.

## 📋 Tổng quan

Dự án sử dụng:
- **Laravel Sanctum** cho API authentication (JWT tokens)
- **Spatie Laravel Permission** cho RBAC (Role-Based Access Control)
- **Rate Limiting** và **Lockout** để bảo mật

## 🔐 Authentication

### API Endpoints

#### 1. Đăng ký (Register)

```http
POST /api/register
Content-Type: application/json

{
    "name": "John Doe",
    "email": "john@example.com",
    "password": "password123",
    "password_confirmation": "password123"
}
```

**Response:**
```json
{
    "message": "User registered successfully",
    "user": {
        "id": 1,
        "name": "John Doe",
        "email": "john@example.com"
    },
    "token": "1|xxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxx"
}
```

**Rate Limit:** 3 requests per 10 minutes per IP

#### 2. Đăng nhập (Login)

```http
POST /api/login
Content-Type: application/json

{
    "email": "john@example.com",
    "password": "password123"
}
```

**Response:**
```json
{
    "message": "Login successful",
    "user": {
        "id": 1,
        "name": "John Doe",
        "email": "john@example.com"
    },
    "token": "1|xxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxx",
    "permissions": ["view content", "create content"],
    "roles": ["user"]
}
```

**Rate Limit:** 5 attempts per 5 minutes per email+IP
**Lockout:** 5 minutes sau 5 lần thử sai

#### 3. Lấy thông tin user (Get Me)

```http
GET /api/me
Authorization: Bearer {token}
```

**Response:**
```json
{
    "user": {
        "id": 1,
        "name": "John Doe",
        "email": "john@example.com"
    },
    "permissions": ["view content", "create content"],
    "roles": ["user"]
}
```

#### 4. Đăng xuất (Logout)

```http
POST /api/logout
Authorization: Bearer {token}
```

**Response:**
```json
{
    "message": "Logged out successfully"
}
```

#### 5. Refresh Token

```http
POST /api/refresh
Authorization: Bearer {token}
```

**Response:**
```json
{
    "message": "Token refreshed successfully",
    "token": "2|xxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxx"
}
```

## 🛡️ Authorization (RBAC)

### Roles & Permissions

Dự án sử dụng Spatie Laravel Permission để quản lý roles và permissions.

#### Default Roles

1. **admin** - Toàn quyền
2. **moderator** - Quản lý nội dung
3. **user** - Người dùng thông thường

#### Default Permissions

- `view users` - Xem danh sách users
- `create users` - Tạo user mới
- `edit users` - Chỉnh sửa user
- `delete users` - Xóa user
- `view roles` - Xem danh sách roles
- `create roles` - Tạo role mới
- `edit roles` - Chỉnh sửa role
- `delete roles` - Xóa role
- `assign roles` - Gán role cho user
- `manage roles` - Quản lý roles (permission tổng hợp)
- `view content` - Xem nội dung
- `create content` - Tạo nội dung
- `edit content` - Chỉnh sửa nội dung
- `delete content` - Xóa nội dung
- `publish content` - Xuất bản nội dung
- `access admin panel` - Truy cập admin panel
- `manage settings` - Quản lý cài đặt

### Sử dụng trong Code

#### Kiểm tra Role

```php
// Trong Controller hoặc Blade
if ($user->hasRole('admin')) {
    // User có role admin
}

// Kiểm tra nhiều roles
if ($user->hasAnyRole(['admin', 'moderator'])) {
    // User có ít nhất một trong các roles
}

if ($user->hasAllRoles(['admin', 'moderator'])) {
    // User có tất cả các roles
}
```

#### Kiểm tra Permission

```php
// Trong Controller hoặc Blade
if ($user->can('edit users')) {
    // User có permission edit users
}

// Kiểm tra nhiều permissions
if ($user->hasAnyPermission(['edit users', 'delete users'])) {
    // User có ít nhất một trong các permissions
}

if ($user->hasAllPermissions(['edit users', 'delete users'])) {
    // User có tất cả các permissions
}
```

### Sử dụng Middleware

#### Trong Routes

```php
// Yêu cầu role cụ thể
Route::middleware(['auth:sanctum', 'role:admin'])->group(function () {
    // Routes chỉ dành cho admin
});

// Yêu cầu một trong các roles
Route::middleware(['auth:sanctum', 'role:admin|moderator'])->group(function () {
    // Routes cho admin hoặc moderator
});

// Yêu cầu permission cụ thể
Route::middleware(['auth:sanctum', 'permission:edit users'])->group(function () {
    // Routes yêu cầu permission edit users
});
```

#### Trong Controller

```php
public function edit(Request $request, $id)
{
    // Kiểm tra permission trong controller
    $this->authorize('edit users');
    
    // Hoặc kiểm tra role
    abort_unless(auth()->user()->hasRole('admin'), 403);
    
    // Code xử lý...
}
```

### API Endpoints cho Role & Permission Management

**Yêu cầu:** User phải có permission `manage roles`

#### Get All Roles

```http
GET /api/roles-permissions/roles
Authorization: Bearer {token}
```

#### Get All Permissions

```http
GET /api/roles-permissions/permissions
Authorization: Bearer {token}
```

#### Assign Role to User

```http
POST /api/roles-permissions/assign-role
Authorization: Bearer {token}
Content-Type: application/json

{
    "user_id": 1,
    "role": "admin"
}
```

#### Remove Role from User

```http
POST /api/roles-permissions/remove-role
Authorization: Bearer {token}
Content-Type: application/json

{
    "user_id": 1,
    "role": "admin"
}
```

#### Sync User Roles

```http
POST /api/roles-permissions/sync-roles
Authorization: Bearer {token}
Content-Type: application/json

{
    "user_id": 1,
    "roles": ["admin", "moderator"]
}
```

#### Give Permission to User

```http
POST /api/roles-permissions/give-permission
Authorization: Bearer {token}
Content-Type: application/json

{
    "user_id": 1,
    "permission": "edit users"
}
```

#### Revoke Permission from User

```http
POST /api/roles-permissions/revoke-permission
Authorization: Bearer {token}
Content-Type: application/json

{
    "user_id": 1,
    "permission": "edit users"
}
```

## 🚦 Rate Limiting

### Cấu hình Rate Limits

1. **API General:** 60 requests/minute per user or IP
2. **Login:** 5 attempts per 5 minutes per email+IP
3. **Register:** 3 attempts per 10 minutes per IP
4. **API Auth:** 100 requests/minute for authenticated users, 60 for guests

### Lockout

- Sau 5 lần đăng nhập sai, user sẽ bị lockout trong **5 phút**
- Lockout được tính theo email + IP address
- Sau khi lockout, user phải đợi 5 phút mới có thể thử lại

### Custom Rate Limiting

Bạn có thể thêm rate limiting tùy chỉnh trong `app/Providers/RouteServiceProvider.php`:

```php
RateLimiter::for('custom-limit', function (Request $request) {
    return Limit::perMinute(10)->by($request->user()?->id ?: $request->ip());
});
```

Sử dụng trong routes:

```php
Route::middleware(['throttle:custom-limit'])->group(function () {
    // Routes với custom rate limit
});
```

## 📝 Database Seeder

Chạy seeder để tạo roles và permissions mặc định:

```bash
docker-compose exec app php artisan db:seed --class=RolePermissionSeeder
```

Hoặc:

```bash
make artisan CMD="db:seed --class=RolePermissionSeeder"
```

Seeder sẽ tạo:
- 3 roles: admin, moderator, user
- Các permissions mặc định
- 2 demo users:
  - **Admin:** admin@example.com / password
  - **User:** user@example.com / password

## 🔧 Cấu hình

### Publish Spatie Permission Config

```bash
docker-compose exec app php artisan vendor:publish --provider="Spatie\Permission\PermissionServiceProvider"
```

### Run Migrations

```bash
docker-compose exec app php artisan migrate
```

Migrations sẽ tạo các bảng:
- `roles`
- `permissions`
- `model_has_roles`
- `model_has_permissions`
- `role_has_permissions`

## 📚 Tài liệu tham khảo

- [Laravel Sanctum Documentation](https://laravel.com/docs/sanctum)
- [Spatie Laravel Permission Documentation](https://spatie.be/docs/laravel-permission)
- [Laravel Rate Limiting](https://laravel.com/docs/routing#rate-limiting)
