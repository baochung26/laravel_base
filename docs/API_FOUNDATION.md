# API Foundation Documentation

Tài liệu về API Foundation với versioning, response format chuẩn, pagination, và API Resources.

## 📋 Tổng quan

API Foundation bao gồm:
- ✅ **API Versioning** - `/api/v1` structure
- ✅ **Response Format** - Standardized success/error responses
- ✅ **Pagination** - Standardized pagination format
- ✅ **API Resources** - Transformers cho data formatting

## 🔢 1. API Versioning

### Cấu trúc

API được tổ chức theo version:
- **Version 1:** `/api/v1/*`
- **Legacy:** `/api/*` (backward compatibility)

### Routes structure

```
routes/
├── web.php
├── console.php
└── api/
    └── v1/
        └── routes.php   # All API v1 routes (loaded with prefix "api/v1")
```

Loaded in `RouteServiceProvider`: `Route::middleware('api')->prefix('api/v1')->group(base_path('routes/api/v1/routes.php'))`.

### Example URLs (base: `/api/v1`)

| Method | Path | Description |
|--------|------|-------------|
| POST | `/register` | Register |
| POST | `/login` | Login |
| POST | `/login/google` | Google login |
| GET | `/me` | Current user (auth) |
| GET | `/users` | List users, paginated (permission: manage users) |
| GET | `/users/{id}` | User by ID |
| GET | `/profile` | Own profile |
| GET | `/health` | Health check |
| GET | `/health/live` | Liveness probe |
| GET | `/health/ready` | Readiness probe (DB) |

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

### Example: UserResource (extends BaseResource)

Avatar URL uses disk config so it works with local storage or S3/CloudFront:

```php
class UserResource extends BaseResource
{
    public function toArray($request): array
    {
        return [
            'id' => $this->id,
            'name' => $this->name,
            'email' => $this->email,
            'avatar' => $this->storageUrl($this->avatar, config('constants.uploads.avatar_disk')),
            'roles' => $this->whenLoaded('roles', fn () => $this->roles->pluck('name')),
        ];
    }
}
```

`BaseResource::storageUrl($path, $disk)` uses `Storage::disk($disk)->url($path)`.

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

## 🔧 Cấu hình

### RouteServiceProvider

API v1 routes are registered in `app/Providers/RouteServiceProvider.php`:

```php
Route::middleware('api')
    ->prefix('api/v1')
    ->group(base_path('routes/api/v1/routes.php'));
```

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
- [Laravel Form Requests](https://laravel.com/docs/validation#form-request-validation)
