# Exception Handling Documentation

Tài liệu về hệ thống Exception Handling chuẩn với format response thống nhất.

## 📋 Tổng quan

Hệ thống Exception Handling bao gồm:
- ✅ **Standardized Response Format** - Format response thống nhất cho tất cả errors
- ✅ **Exception Classes** - Custom exception classes cho các loại lỗi phổ biến
- ✅ **Automatic Error Mapping** - Tự động map tất cả HTTP errors thành format chuẩn
- ✅ **Request ID Integration** - Tự động include Request ID trong error responses

## 🎯 1. Standardized Response Format

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
        "field_name": ["Error message"]
    }
}
```

### Error Response với Debug (khi `APP_DEBUG=true`)

```json
{
    "success": false,
    "message": "Error message",
    "errors": {
        "field_name": ["Error message"]
    },
    "debug": {
        "status_code": 422,
        "request_id": "laravel-20240101120000-abc12345"
    }
}
```

## 🔴 2. Exception Classes

### Custom Exception Classes

Tất cả custom exceptions đều extend `Exception` và có HTTP status code mặc định:

- **`ValidationException`** (422) - Validation errors
- **`ResourceNotFoundException`** (404) - Resource not found
- **`UnauthorizedException`** (401) - Unauthorized
- **`ForbiddenException`** (403) - Forbidden
- **`BadRequestException`** (400) - Bad request
- **`TooManyRequestsException`** (429) - Too many requests
- **`InternalServerErrorException`** (500) - Internal server error

### Sử dụng Exception Classes

```php
use App\Exceptions\ValidationException;
use App\Exceptions\ResourceNotFoundException;
use App\Exceptions\UnauthorizedException;
use App\Exceptions\ForbiddenException;

// Validation error
throw new ValidationException('Email already exists');

// Resource not found
throw new ResourceNotFoundException('User not found');

// Unauthorized
throw new UnauthorizedException('Authentication required');

// Forbidden
throw new ForbiddenException('You do not have permission to perform this action');
```

## 🔧 3. Automatic Error Mapping

Exception Handler tự động map các loại exceptions sau:

### Laravel Exceptions

- **`LaravelValidationException`** → 422 Validation Error
- **`AuthenticationException`** → 401 Unauthorized
- **`ModelNotFoundException`** → 404 Resource Not Found
- **`NotFoundHttpException`** → 404 Resource Not Found
- **`UnauthorizedHttpException`** → 401 Unauthorized
- **`AccessDeniedHttpException`** → 403 Forbidden
- **`MethodNotAllowedHttpException`** → 405 Method Not Allowed
- **`TooManyRequestsHttpException`** → 429 Too Many Requests

### Custom Exceptions

- **`ValidationException`** → 422 Validation Error
- **`ResourceNotFoundException`** → 404 Resource Not Found
- **`UnauthorizedException`** → 401 Unauthorized
- **`ForbiddenException`** → 403 Forbidden
- **`BadRequestException`** → 400 Bad Request
- **`TooManyRequestsException`** → 429 Too Many Requests
- **`InternalServerErrorException`** → 500 Internal Server Error

### Generic Exceptions

Tất cả các exceptions khác → 500 Internal Server Error

## 📝 4. Error Response Examples

### Validation Error (422)

**Request:**
```http
POST /api/v1/users
Content-Type: application/json

{
    "email": "invalid-email"
}
```

**Response:**
```json
{
    "success": false,
    "message": "Validation failed",
    "errors": {
        "email": [
            "The email must be a valid email address."
        ],
        "name": [
            "The name field is required."
        ]
    }
}
```

**Headers:**
```http
HTTP/1.1 422 Unprocessable Entity
X-Request-ID: laravel-20240101120000-abc12345
X-Correlation-ID: laravel-20240101120000-abc12345
```

### Unauthorized (401)

**Request:**
```http
GET /api/v1/profile
```

**Response:**
```json
{
    "success": false,
    "message": "Unauthenticated"
}
```

**Headers:**
```http
HTTP/1.1 401 Unauthorized
X-Request-ID: laravel-20240101120000-abc12345
X-Correlation-ID: laravel-20240101120000-abc12345
```

### Forbidden (403)

**Request:**
```http
GET /api/v1/users
Authorization: Bearer {token}  // User không có permission
```

**Response:**
```json
{
    "success": false,
    "message": "Forbidden"
}
```

**Headers:**
```http
HTTP/1.1 403 Forbidden
X-Request-ID: laravel-20240101120000-abc12345
X-Correlation-ID: laravel-20240101120000-abc12345
```

### Resource Not Found (404)

**Request:**
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

**Headers:**
```http
HTTP/1.1 404 Not Found
X-Request-ID: laravel-20240101120000-abc12345
X-Correlation-ID: laravel-20240101120000-abc12345
```

### Method Not Allowed (405)

**Request:**
```http
PATCH /api/v1/users/1
Authorization: Bearer {token}
```

**Response:**
```json
{
    "success": false,
    "message": "Method not allowed. Allowed methods: GET, PUT, DELETE"
}
```

### Too Many Requests (429)

**Request:**
```http
POST /api/v1/login
// Quá nhiều attempts
```

**Response:**
```json
{
    "success": false,
    "message": "Too Many Requests"
}
```

### Internal Server Error (500)

**Request:**
```http
GET /api/v1/users
Authorization: Bearer {token}
```

**Response (Production):**
```json
{
    "success": false,
    "message": "An error occurred. Please try again later."
}
```

**Response (Debug Mode):**
```json
{
    "success": false,
    "message": "SQLSTATE[HY000] [2002] Connection refused",
    "debug": {
        "status_code": 500,
        "request_id": "laravel-20240101120000-abc12345"
    }
}
```

## 🔧 5. Exception Handler

### Handler Location

`app/Exceptions/Handler.php`

### Features

- ✅ Tự động detect API requests (`expectsJson()` hoặc `is('api/*')`)
- ✅ Map tất cả exceptions thành format chuẩn
- ✅ Include Request ID trong response headers
- ✅ Debug mode support (hiển thị thêm thông tin khi `APP_DEBUG=true`)
- ✅ Log exceptions với Request ID

### Exception Reporting

Một số exceptions không được report (log):
- `ValidationException`
- `ResourceNotFoundException`
- `UnauthorizedException`
- `ForbiddenException`
- `BadRequestException`
- `TooManyRequestsException`

Các exceptions khác sẽ được log với đầy đủ thông tin.

## 📚 6. Best Practices

### Throw Exceptions trong Services

✅ **DO:**
```php
use App\Exceptions\ResourceNotFoundException;
use App\Exceptions\ValidationException;

public function getById(int $id): UserDTO
{
    $user = $this->userRepository->find($id);
    
    if (!$user) {
        throw new ResourceNotFoundException("User with ID {$id} not found");
    }
    
    return UserDTO::fromModel($user);
}

public function create(UserDTO $userDTO): UserDTO
{
    if ($this->userRepository->existsByEmail($userDTO->email)) {
        throw new ValidationException('Email already exists');
    }
    
    // Create user...
}
```

❌ **DON'T:**
```php
// Don't return null or false
public function getById(int $id): ?UserDTO
{
    $user = $this->userRepository->find($id);
    return $user ? UserDTO::fromModel($user) : null; // Bad
}

// Don't return error arrays
public function create(UserDTO $userDTO): array
{
    if ($this->userRepository->existsByEmail($userDTO->email)) {
        return ['error' => 'Email exists']; // Bad
    }
}
```

### Handle Exceptions trong Controllers

✅ **DO:**
```php
public function show(int $id): JsonResponse
{
    try {
        $user = $this->userService->getById($id);
        return $this->successResponse(new UserResource($user), 'User retrieved successfully');
    } catch (ResourceNotFoundException $e) {
        // Exception sẽ tự động được handle bởi Handler
        throw $e; // Just re-throw
    }
}
```

Hoặc đơn giản hơn:
```php
public function show(int $id): JsonResponse
{
    // Exception sẽ tự động được handle
    $user = $this->userService->getById($id);
    return $this->successResponse(new UserResource($user), 'User retrieved successfully');
}
```

### Validation Exceptions

Laravel Form Requests tự động throw `LaravelValidationException` khi validation fails:

```php
public function store(StoreUserRequest $request): JsonResponse
{
    // Nếu validation fails, LaravelValidationException sẽ được throw
    // và tự động được handle bởi Handler
    
    $user = $this->userService->create(...);
    return $this->successResponse(new UserResource($user), 'User created successfully');
}
```

## 🧪 7. Testing

### Test Exception Responses

```php
// Test validation error
$response = $this->postJson('/api/v1/users', []);
$response->assertStatus(422);
$response->assertJson([
    'success' => false,
    'message' => 'Validation failed',
    'errors' => [...]
]);

// Test not found
$response = $this->getJson('/api/v1/users/999');
$response->assertStatus(404);
$response->assertJson([
    'success' => false,
    'message' => 'Resource not found'
]);

// Test unauthorized
$response = $this->getJson('/api/v1/profile');
$response->assertStatus(401);
$response->assertJson([
    'success' => false,
    'message' => 'Unauthenticated'
]);
```

## 📊 8. HTTP Status Codes

| Status Code | Meaning | Exception Class |
|------------|---------|----------------|
| 400 | Bad Request | `BadRequestException` |
| 401 | Unauthorized | `UnauthorizedException`, `AuthenticationException` |
| 403 | Forbidden | `ForbiddenException`, `AccessDeniedHttpException` |
| 404 | Not Found | `ResourceNotFoundException`, `NotFoundHttpException`, `ModelNotFoundException` |
| 405 | Method Not Allowed | `MethodNotAllowedHttpException` |
| 422 | Validation Error | `ValidationException`, `LaravelValidationException` |
| 429 | Too Many Requests | `TooManyRequestsException`, `TooManyRequestsHttpException` |
| 500 | Internal Server Error | `InternalServerErrorException`, Generic exceptions |

## 🔗 9. Related Documentation

- [API Foundation](API_FOUNDATION.md) - Standardized response format
- [Logging](LOGGING.md) - Exception logging với Request ID

## 📚 Tài liệu tham khảo

- [Laravel Exception Handling](https://laravel.com/docs/errors)
- [HTTP Status Codes](https://developer.mozilla.org/en-US/docs/Web/HTTP/Status)
