# Kiến trúc Controller → Service → Repository

Tài liệu mô tả cách dự án tổ chức theo mô hình **Controller → Service → Repository**: controller chỉ nhận request, gọi service, trả response; business logic nằm ở service; truy cập dữ liệu qua repository.

## 1. Nguyên tắc

| Layer | Trách nhiệm | Không nên |
|-------|-------------|-----------|
| **Controller** | Nhận request, validate (Form Request), gọi service, format response (Resource/JSON) | Gọi Eloquent trực tiếp, xử lý file/avatar, logic nghiệp vụ |
| **Service** | Logic nghiệp vụ, điều phối repository + storage/queue, throw exception chuẩn | Render response, biết về HTTP/Request |
| **Repository** | Truy vấn DB (Eloquent), trả model/collection/paginator | Logic nghiệp vụ, hash password, gửi mail |

## 2. Cấu trúc thư mục

```
app/
├── Http/
│   ├── Controllers/Api/V1/   # Chỉ delegate → service, format response
│   └── Requests/             # Validation (Form Request)
├── Services/                 # Business logic, orchestration
│   ├── UserService.php
│   ├── AuthService.php
│   ├── RolePermissionService.php
│   ├── PasswordResetService.php
│   └── Storage/
│       ├── StorageService.php
│       └── FileManagerService.php
└── Repositories/
    ├── Contracts/           # Interface repository
    │   ├── UserRepositoryInterface.php
    │   └── RolePermissionRepositoryInterface.php
    └── Eloquent/             # Implementation
        ├── UserRepository.php
        └── RolePermissionRepository.php
```

## 3. Luồng điển hình

### User CRUD + Avatar

- **Controller** (`UserController`): nhận `StoreUserRequest`/`UpdateUserRequest`, lấy `validated()` + `file('avatar')`, gọi `UserService::createWithAvatar()` / `updateWithAvatar()` / `delete()`, trả `UserResource` + HTTP status.
- **Service** (`UserService`): nhận DTO + optional `UploadedFile`; gọi repository để tạo/cập nhật user; gọi `StorageService` để lưu/xóa avatar; dùng `config('constants.uploads.avatar_disk')`; throw `ValidationException` / `ResourceNotFoundException`.
- **Repository** (`UserRepository`): `create()`, `update()`, `findOrFail()`, `withRolesAndPermissions()`, `paginateWithRelations()`, `search()`.

### Role & Permission API

- **Controller** (`RolePermissionController`): dùng Form Request (`AssignRoleRequest`, `SyncRolesRequest`, …) để validate; gọi `RolePermissionService::getRoles()` / `getPermissions()` / `assignRoleToUser()` / …; trả `RoleResource`, `PermissionResource`, `UserResource`.
- **Service** (`RolePermissionService`): `getRoles()` / `getPermissions()` đọc qua `RolePermissionRepository`; assign/sync/revoke ủy quyền cho `UserService`.
- **Repository** (`RolePermissionRepository`): `getRolesWithPermissions()`, `getAllPermissions()` (Spatie Role/Permission).

### Auth

- **Controller** (`AuthController`): nhận `RegisterRequest`/`LoginRequest`, tạo DTO, gọi `AuthService::register()`/`login()`; dùng `UserService::getModelByIdWithRelations()` (inject) để trả `UserResource` trong response.
- **Service** (`AuthService`): đăng ký/đăng nhập, tạo token; không gọi repository trực tiếp mà qua `UserService` nếu cần user đầy đủ.

## 4. Binding (Service Provider)

Trong `AppServiceProvider::register()`:

```php
$this->app->bind(UserRepositoryInterface::class, UserRepository::class);
$this->app->bind(RolePermissionRepositoryInterface::class, RolePermissionRepository::class);
```

Controller và Service nhận interface qua constructor (DI); khi cần test có thể mock interface.

## 5. Form Request

Validation được đưa ra Form Request thay vì viết trong controller:

- User: `StoreUserRequest`, `UpdateUserRequest`
- Profile: `UpdateProfileRequest`
- Role/Permission: `AssignRoleRequest`, `RemoveRoleRequest`, `SyncRolesRequest`, `GivePermissionRequest`, `RevokePermissionRequest`
- Auth: `RegisterRequest`, `LoginRequest`, `GoogleLoginRequest`
- Password: `ChangePasswordRequest`, `ForgotPasswordRequest`, `ResetPasswordRequest`

Controller chỉ gọi `$request->validated()` và truyền xuống service.

## 6. Lợi ích

- **Controller mỏng**: dễ đọc, dễ viết test API (chỉ cần assert service được gọi đúng và response format).
- **Service tái sử dụng**: logic user/role/permission/avatar có thể dùng từ API, Artisan, Job, Filament, …
- **Repository thay thế được**: có thể đổi Eloquent sang implementation khác (cache, external API) mà không đổi service.
- **Test**: mock `UserRepositoryInterface` / `RolePermissionRepositoryInterface` / `UserService` trong unit test; feature test vẫn gọi HTTP và kiểm tra response.

## 7. Tài liệu liên quan

- [API_FOUNDATION.md](API_FOUNDATION.md) – Response format, versioning, resources
- [EXCEPTION_HANDLING.md](EXCEPTION_HANDLING.md) – Chuẩn exception và map sang JSON
- [CACHE_STRATEGY.md](CACHE_STRATEGY.md) – Cache có thể dùng trong service/repository
