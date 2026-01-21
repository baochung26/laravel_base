# Auth & Authorization Checklist

Bảng kiểm tra đầy đủ các tính năng Authentication & Authorization trong dự án.

## ✅ 1. Login/Register (JWT hoặc Session)

### JWT Authentication với Laravel Sanctum

- [x] **Laravel Sanctum đã được cài đặt** (`composer.json`)
  - Version: `^4.0` (compatible với Laravel 12)

- [x] **User Model có HasApiTokens trait**
  - File: `app/Models/User.php`
  - Trait: `HasApiTokens` từ Laravel Sanctum

- [x] **Authentication Controller**
  - File: `app/Http/Controllers/Auth/AuthController.php`
  - Methods:
    - `register()` - Đăng ký user mới
    - `login()` - Đăng nhập và tạo token
    - `logout()` - Đăng xuất (revoke token)
    - `me()` - Lấy thông tin user hiện tại
    - `refresh()` - Refresh token

- [x] **Authentication Service**
  - File: `app/Services/AuthService.php`
  - Business logic cho authentication
  - Xử lý rate limiting và lockout

- [x] **API Routes**
  - File: `routes/api.php`
  - Endpoints:
    - `POST /api/register` - Đăng ký
    - `POST /api/login` - Đăng nhập
    - `POST /api/logout` - Đăng xuất (protected)
    - `GET /api/me` - Lấy user info (protected)
    - `POST /api/refresh` - Refresh token (protected)

- [x] **Request Validation**
  - File: `app/Http/Requests/Auth/LoginRequest.php`
  - File: `app/Http/Requests/Auth/RegisterRequest.php`
  - Validation rules cho login và register

- [x] **JWT Token Generation**
  - Sử dụng Laravel Sanctum
  - Tokens được tạo trong AuthService
  - Tokens có thể được revoked

**Status: ✅ HOÀN TẤT**

## ✅ 2. RBAC (Role/Permission) hoặc Policy/Gate

### Spatie Laravel Permission (RBAC)

- [x] **Spatie Permission Package**
  - Version: `^6.0` (compatible với Laravel 12)
  - File: `composer.json`

- [x] **User Model có HasRoles trait**
  - File: `app/Models/User.php`
  - Trait: `HasRoles` từ Spatie Permission

- [x] **Role & Permission Seeder**
  - File: `database/seeders/RolePermissionSeeder.php`
  - Tạo 3 roles: `admin`, `moderator`, `user`
  - Tạo 17 permissions cho quản lý users, roles, content, settings
  - Tạo demo users (admin@example.com, user@example.com)

- [x] **Repository & Service cho Role Management**
  - File: `app/Repositories/Contracts/UserRepositoryInterface.php`
  - File: `app/Repositories/Eloquent/UserRepository.php`
  - File: `app/Services/UserService.php`
  - Methods: `assignRole()`, `removeRole()`, `syncRoles()`

- [x] **RolePermissionController**
  - File: `app/Http/Controllers/RolePermissionController.php`
  - API endpoints để quản lý roles và permissions

- [x] **Middleware cho Role/Permission**
  - File: `app/Http/Middleware/CheckRole.php`
  - File: `app/Http/Middleware/CheckPermission.php`
  - Đã đăng ký trong `bootstrap/app.php`:
    - `role` middleware
    - `permission` middleware

- [x] **Cách sử dụng trong Routes**
  ```php
  // Kiểm tra role
  Route::middleware(['auth:sanctum', 'role:admin'])->group(function () {
      // Routes chỉ dành cho admin
  });
  
  // Kiểm tra permission
  Route::middleware(['auth:sanctum', 'permission:manage roles'])->group(function () {
      // Routes yêu cầu permission
  });
  ```

- [x] **Cách sử dụng trong Code**
  ```php
  // Kiểm tra role
  if ($user->hasRole('admin')) { }
  
  // Kiểm tra permission
  if ($user->can('edit users')) { }
  
  // Gate/Policy (Laravel built-in)
  $this->authorize('edit', $user);
  ```

### Laravel Policies & Gates (Optional)

- [x] **Laravel built-in Policy/Gate support**
  - Có thể sử dụng `$this->authorize()` trong controllers
  - Có thể sử dụng `Gate::allows()` hoặc `Gate::denies()`
  - Có thể tạo Policy classes với `php artisan make:policy`

**Status: ✅ HOÀN TẤT**

**Note:** Dự án đang sử dụng **Spatie Permission** cho RBAC. Nếu cần, có thể bổ sung Policy examples để demo Laravel built-in Policy/Gate.

## ✅ 3. Rate Limit + Lockout

### Rate Limiting

- [x] **Rate Limiter Configuration**
  - File: `app/Providers/RouteServiceProvider.php`
  - Các rate limiters đã được định nghĩa:
    - `api` - 60 requests/minute
    - `login` - 5 attempts per 5 minutes
    - `register` - 3 attempts per 10 minutes
    - `api-auth` - 100 requests/minute (authenticated users)

- [x] **Login Rate Limiting**
  - File: `app/Services/AuthService.php`
  - Method: `login()`
  - Rate limit: 5 attempts per 5 minutes
  - Key: `login.{email}|{ip}`
  - Lockout: 5 minutes sau 5 lần thử sai
  - Đã được áp dụng trong route: `middleware('throttle:login')`

- [x] **Register Rate Limiting**
  - File: `app/Services/AuthService.php`
  - Method: `register()`
  - Rate limit: 3 attempts per 10 minutes
  - Key: `register.{ip}`
  - Lockout: 10 minutes sau 3 lần thử sai
  - Đã được áp dụng trong route: `middleware('throttle:register')`

- [x] **API Rate Limiting**
  - Áp dụng cho tất cả API routes
  - Rate limit: 60 requests/minute (guest)
  - Rate limit: 100 requests/minute (authenticated users)
  - Middleware: `throttle:api` (tự động áp dụng)

### Lockout Mechanism

- [x] **Login Lockout**
  - Sau 5 lần đăng nhập sai → Lockout 5 phút
  - Key dựa trên email + IP address
  - RateLimiter::hit() được gọi khi login thất bại
  - RateLimiter::clear() được gọi khi login thành công

- [x] **Register Lockout**
  - Sau 3 lần đăng ký thất bại → Lockout 10 phút
  - Key dựa trên IP address
  - RateLimiter::hit() được gọi khi registration thất bại
  - RateLimiter::clear() được gọi khi registration thành công

- [x] **Error Messages**
  - Thông báo rõ ràng về lockout
  - Hiển thị thời gian còn lại trước khi có thể thử lại

**Status: ✅ HOÀN TẤT**

## 📊 Tổng kết

| Tính năng | Status | Ghi chú |
|-----------|--------|---------|
| Login/Register với JWT (Sanctum) | ✅ | Hoàn tất đầy đủ |
| RBAC với Spatie Permission | ✅ | Hoàn tất đầy đủ |
| Rate Limiting | ✅ | Hoàn tất đầy đủ |
| Lockout Mechanism | ✅ | Hoàn tất đầy đủ |
| Policy/Gate Support | ✅ | Laravel built-in, có thể sử dụng |

## 🎯 Kết luận

**Tất cả các tính năng Auth & Authorization đã được implement đầy đủ:**

1. ✅ **Login/Register với JWT** - Sử dụng Laravel Sanctum
2. ✅ **RBAC (Role/Permission)** - Sử dụng Spatie Permission
3. ✅ **Rate Limiting + Lockout** - Đầy đủ cho login, register, và API

Dự án đã sẵn sàng để sử dụng các tính năng Authentication & Authorization!

## 📚 Tài liệu tham khảo

- Xem chi tiết trong file [AUTHENTICATION.md](AUTHENTICATION.md)
- Xem cách sử dụng trong file [ARCHITECTURE.md](ARCHITECTURE.md)
