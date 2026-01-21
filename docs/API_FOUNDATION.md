# API Foundation Documentation

Tài liệu về API Foundation với versioning, response format chuẩn, pagination, API Resources, và Swagger/OpenAPI.

## 📋 Tổng quan

API Foundation bao gồm:
- ✅ **API Versioning** - `/api/v1` structure
- ✅ **Response Format** - Standardized success/error responses
- ✅ **Pagination** - Standardized pagination format
- ✅ **API Resources** - Transformers cho data formatting
- ✅ **Swagger/OpenAPI** - API documentation với L5-Swagger

## 🔢 1. API Versioning

### Cấu trúc

API được tổ chức theo version:
- **Version 1:** `/api/v1/*`
- **Legacy:** `/api/*` (backward compatibility)

### Routes Structure

```
routes/
├── api.php        # Legacy routes (backward compatibility)
└── api/
    └── v1.php     # API V1 routes
```

### Ví dụ URLs

- `POST /api/v1/register` - Đăng ký
- `POST /api/v1/login` - Đăng nhập
- `GET /api/v1/users` - Danh sách users
- `GET /api/v1/profile` - Profile của user

### Versioning Strategy

Để biết thêm chi tiết về API Versioning Strategy và Deprecation Policy, xem:
- 📖 [API_VERSIONING_STRATEGY.md](API_VERSIONING_STRATEGY.md) - Chiến lược versioning và deprecation policy đầy đủ

## 📦 2. Response Format Chuẩn

### Success Response

```json
{
    "success": true,
    "message": "Success message",
    "data": {
        // Response data
    }
}
```

### Error Response

```json
{
    "success": false,
    "message": "Error message",
    "errors": {
        // Validation errors (optional)
    }
}
```

### Sử dụng trong Controllers

Tất cả API V1 controllers extend `ApiController` và sử dụng `ApiResponseTrait`:

```php
class UserController extends ApiController
{
    // Success response
    return $this->successResponse($data, 'User created successfully', 201);
    
    // Error response
    return $this->errorResponse('User not found', 404);
    
    // Validation error
    return $this->validationErrorResponse($errors, 'Validation failed');
    
    // Not found
    return $this->notFoundResponse('Resource not found');
    
    // Unauthorized
    return $this->unauthorizedResponse('Unauthorized');
    
    // Forbidden
    return $this->forbiddenResponse('Forbidden');
}
```

### ApiResponseTrait Methods

- `successResponse($data, $message, $code)` - Success response
- `errorResponse($message, $code, $errors)` - Error response
- `validationErrorResponse($errors, $message)` - Validation error
- `notFoundResponse($message)` - 404 Not Found
- `unauthorizedResponse($message)` - 401 Unauthorized
- `forbiddenResponse($message)` - 403 Forbidden
- `paginatedResponse($paginator, $message)` - Paginated response
- `resourcePaginatedResponse($resourceCollection, $paginator, $message)` - Paginated resource collection

## 📄 3. Pagination Chuẩn

### Pagination Response Format

```json
{
    "success": true,
    "message": "Success",
    "data": [
        // Items array
    ],
    "meta": {
        "current_page": 1,
        "per_page": 15,
        "total": 100,
        "last_page": 7,
        "from": 1,
        "to": 15
    },
    "links": {
        "first": "http://localhost:8000/api/v1/users?page=1",
        "last": "http://localhost:8000/api/v1/users?page=7",
        "prev": null,
        "next": "http://localhost:8000/api/v1/users?page=2"
    }
}
```

### Sử dụng trong Controllers

```php
// Paginated response với resource collection
$users = $this->userService->search($keyword, $perPage);
return $this->resourcePaginatedResponse(
    UserResource::collection($users->items()),
    $users,
    'Users retrieved successfully'
);

// Hoặc paginated response đơn giản
return $this->paginatedResponse($paginator, 'Users retrieved successfully');
```

### Query Parameters

- `per_page` - Số lượng items mỗi trang (default: 15)
- `page` - Trang hiện tại (default: 1)
- `search` - Tìm kiếm (tùy chọn)

## 🎨 4. API Resources (Transformers)

### Mục đích

API Resources giúp:
- Format dữ liệu một cách nhất quán
- Ẩn/hiện fields dựa trên điều kiện
- Transform data structure
- Tách biệt data presentation khỏi model

### Ví dụ: UserResource

```php
class UserResource extends JsonResource
{
    public function toArray($request): array
    {
        return [
            'id' => $this->id,
            'name' => $this->name,
            'email' => $this->email,
            'avatar' => $this->avatar ? url('storage/' . $this->avatar) : null,
            'roles' => $this->whenLoaded('roles', function () {
                return $this->roles->pluck('name');
            }),
        ];
    }
}
```

### Sử dụng trong Controllers

```php
// Single resource
return $this->successResponse(
    new UserResource($user->load('roles', 'permissions')),
    'User retrieved successfully'
);

// Resource collection
return $this->successResponse(
    UserResource::collection($users),
    'Users retrieved successfully'
);

// Paginated resource collection
return $this->resourcePaginatedResponse(
    UserResource::collection($users->items()),
    $users,
    'Users retrieved successfully'
);
```

### Available Resources

- `UserResource` - User data transformer
- `UserCollection` - User collection transformer
- `RoleResource` - Role data transformer
- `PermissionResource` - Permission data transformer

## 📚 5. Swagger/OpenAPI Documentation

### Cài đặt

Swagger được cài đặt thông qua `darkaonline/l5-swagger` package.

### Truy cập Documentation

Sau khi cài đặt và generate docs:

```bash
# Generate Swagger documentation (sử dụng Makefile - khuyến nghị)
make swagger-generate

# Hoặc sử dụng artisan trực tiếp
php artisan l5-swagger:generate
```

Truy cập: `http://localhost:8000/api/documentation`

**Lưu ý:** Sau mỗi lần thay đổi Swagger annotations trong controllers, cần chạy lại `make swagger-generate` để cập nhật documentation.

### Swagger Annotations

Các controllers đã được annotate với Swagger/OpenAPI annotations:

```php
/**
 * @OA\Post(
 *     path="/api/v1/register",
 *     summary="Register a new user",
 *     tags={"Authentication"},
 *     @OA\RequestBody(...),
 *     @OA\Response(...)
 * )
 */
public function register(RegisterRequest $request): JsonResponse
{
    // Implementation
}
```

### Security Definitions

API sử dụng Laravel Sanctum với Bearer token:

```php
/**
 * @OA\SecurityScheme(
 *     securityScheme="sanctum",
 *     type="apiKey",
 *     in="header",
 *     name="Authorization",
 *     description="Enter token in format: Bearer {token}"
 * )
 */
```

### Components/Schemas

Định nghĩa schemas cho Swagger:

- `UserResource` - User schema
- `RoleResource` - Role schema
- `PermissionResource` - Permission schema
- `PaginationMeta` - Pagination metadata schema

## 🔧 Cấu hình

### RouteServiceProvider

Routes được đăng ký trong `RouteServiceProvider`:

```php
Route::middleware('api')
    ->prefix('api/v1')
    ->group(base_path('routes/api/v1.php'));
```

### Bootstrap App

API routes không còn được đăng ký trong `bootstrap/app.php`, thay vào đó được đăng ký trong `RouteServiceProvider`.

## 📝 Ví dụ sử dụng

### 1. Create User (V1)

```http
POST /api/v1/users
Authorization: Bearer {token}
Content-Type: multipart/form-data

{
    "name": "John Doe",
    "email": "john@example.com",
    "password": "password123",
    "password_confirmation": "password123",
    "avatar": <file>
}
```

**Response:**
```json
{
    "success": true,
    "message": "User created successfully",
    "data": {
        "id": 1,
        "name": "John Doe",
        "email": "john@example.com",
        "avatar": "http://localhost:8000/storage/avatars/abc123.jpg",
        "roles": ["user"],
        "permissions": []
    }
}
```

### 2. Get Users with Pagination (V1)

```http
GET /api/v1/users?per_page=10&page=1&search=john
Authorization: Bearer {token}
```

**Response:**
```json
{
    "success": true,
    "message": "Users retrieved successfully",
    "data": [
        {
            "id": 1,
            "name": "John Doe",
            "email": "john@example.com",
            "avatar": "http://localhost:8000/storage/avatars/abc123.jpg"
        }
    ],
    "meta": {
        "current_page": 1,
        "per_page": 10,
        "total": 50,
        "last_page": 5,
        "from": 1,
        "to": 10
    },
    "links": {
        "first": "http://localhost:8000/api/v1/users?page=1",
        "last": "http://localhost:8000/api/v1/users?page=5",
        "prev": null,
        "next": "http://localhost:8000/api/v1/users?page=2"
    }
}
```

### 3. Error Response Example

```http
GET /api/v1/users/999
Authorization: Bearer {token}
```

**Response:**
```json
{
    "success": false,
    "message": "Resource not found"
}
```

## 🎯 Best Practices

### 1. Response Format

✅ **DO:**
- Luôn sử dụng `ApiResponseTrait` methods
- Luôn trả về consistent response format
- Include message trong response

❌ **DON'T:**
- Không trả về raw data without wrapper
- Không mix different response formats
- Không quên include success flag

### 2. API Resources

✅ **DO:**
- Sử dụng Resources để transform data
- Conditional loading với `whenLoaded()`
- Format URLs properly (avatar, etc.)

❌ **DON'T:**
- Không return Model trực tiếp
- Không expose sensitive data
- Không hardcode URLs

### 3. Pagination

✅ **DO:**
- Luôn sử dụng `paginatedResponse()` hoặc `resourcePaginatedResponse()`
- Include metadata và links
- Allow per_page customization

❌ **DON'T:**
- Không return raw paginator
- Không quên metadata
- Không hardcode per_page value

### 4. Versioning

✅ **DO:**
- Sử dụng version trong URL (`/api/v1/`)
- Follow versioning strategy (xem [API_VERSIONING_STRATEGY.md](API_VERSIONING_STRATEGY.md))
- Maintain backward compatibility when possible
- Provide migration guides for new versions
- Keep backward compatibility với legacy routes
- Document breaking changes

❌ **DON'T:**
- Không break existing APIs without versioning
- Không mix versions trong same endpoint
- Không forget to update documentation

## 📚 Tài liệu tham khảo

- [Laravel API Resources](https://laravel.com/docs/eloquent-resources)
- [L5-Swagger Documentation](https://github.com/DarkaOnLine/L5-Swagger)
- [OpenAPI Specification](https://swagger.io/specification/)
