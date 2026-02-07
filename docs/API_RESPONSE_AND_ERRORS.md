# API Response & Error Handling

Chuẩn chung cho mọi response và xử lý lỗi của API: format JSON thống nhất, exception mapping, cách dùng trong code.

**Nguồn thật (source of truth):** `app/Support/ApiResponse.php`, `app/Traits/ApiResponseTrait.php`, `app/Exceptions/Handler.php`.

---

## 1. Tổng quan

| Mục tiêu | Cách làm |
|----------|----------|
| Response thống nhất | Mọi API trả về cùng cấu trúc: `success`, `message`, `meta`, (optional) `data`, (optional) `errors`. |
| Trace request | Mỗi response có `meta.request_id`, header `X-Request-ID` / `X-Correlation-ID`. |
| Lỗi chuẩn | Mọi exception (custom, Laravel, Symfony) đều được Handler chuyển thành JSON cùng format. |

**Quy ước:**

- **Success:** `success === true`, có thể có `data`. Không có `errors`.
- **Error:** `success === false`, có `message`; có thể có `errors` (validation hoặc chi tiết). Không dùng top-level `statusCode`, `path`, `error` — chỉ `meta.status_code` khi `APP_DEBUG=true`.

---

## 2. Format response chuẩn

### 2.1 Success (200 / 201)

```json
{
  "success": true,
  "message": "User retrieved successfully",
  "meta": {
    "request_id": "laravel-20260207120000-abc12345",
    "timestamp": "2026-02-07T12:00:00+00:00"
  },
  "data": {
    "id": 1,
    "name": "John Doe",
    "email": "john@example.com"
  }
}
```

- `data` chỉ có khi có dữ liệu trả về (`$data !== null`).
- `meta.request_id` có khi đã bind request id (middleware).
- `meta.timestamp` luôn có (ISO 8601).
- Khi `APP_DEBUG=true`: thêm `meta.status_code` (số HTTP).

### 2.2 Error (4xx / 5xx)

```json
{
  "success": false,
  "message": "Resource not found",
  "meta": {
    "request_id": "laravel-20260207120000-abc12345",
    "timestamp": "2026-02-07T12:00:00+00:00"
  }
}
```

Có thể kèm `errors` (object, thường dùng cho validation):

```json
{
  "success": false,
  "message": "The email field is required.",
  "meta": {
    "request_id": "...",
    "timestamp": "..."
  },
  "errors": {
    "email": ["The email field is required."],
    "password": ["The password field is required."]
  }
}
```

Khi `APP_DEBUG=true`, response error có thêm `meta.status_code` (không dùng object `debug` riêng).

### 2.3 Paginated

```json
{
  "success": true,
  "message": "Users retrieved successfully",
  "meta": {
    "request_id": "...",
    "timestamp": "...",
    "current_page": 1,
    "per_page": 15,
    "total": 100,
    "last_page": 7,
    "from": 1,
    "to": 15
  },
  "data": [
    { "id": 1, "name": "User 1" }
  ],
  "links": {
    "first": "http://localhost:8000/api/v1/users?page=1",
    "last": "http://localhost:8000/api/v1/users?page=7",
    "prev": null,
    "next": "http://localhost:8000/api/v1/users?page=2"
  }
}
```

### 2.4 Auth (register / login)

Data nằm trong `data`:

```json
{
  "success": true,
  "message": "Login successful",
  "meta": { "request_id": "...", "timestamp": "..." },
  "data": {
    "user": { "id": 1, "name": "John Doe", ... },
    "token": "1|...",
    "access_token": "1|...",
    "refresh_token": "2|...",
    "permissions": [],
    "roles": ["user"],
    "login_type": "password"
  }
}
```

- `token` giữ cho tương thích ngược; client mới nên dùng `access_token`.
- `login_type`: `password` hoặc `google`.

---

## 3. HTTP status code và nguồn lỗi

| Code | Ý nghĩa | Nguồn (Exception / Laravel) |
|------|--------|-----------------------------|
| 400 | Bad Request | `BadRequestException` |
| 401 | Unauthorized | `UnauthorizedException`, `AuthenticationException`, `UnauthorizedHttpException` |
| 403 | Forbidden | `ForbiddenException`, `AccessDeniedHttpException` |
| 404 | Not Found | `ResourceNotFoundException`, `ModelNotFoundException`, `NotFoundHttpException` |
| 405 | Method Not Allowed | `MethodNotAllowedHttpException` |
| 422 | Validation Error | `ValidationException`, `LaravelValidationException` |
| 429 | Too Many Requests | `TooManyRequestsException`, `TooManyRequestsHttpException` |
| 500 | Internal Server Error | `InternalServerErrorException`, mọi exception còn lại |

---

## 4. Exception handling

### 4.1 Custom exceptions (app/Exceptions)

| Class | HTTP code | Dùng khi |
|-------|-----------|----------|
| `ValidationException` | 422 | Lỗi nghiệp vụ/validation (vd: email đã tồn tại). |
| `ResourceNotFoundException` | 404 | Không tìm thấy resource. |
| `UnauthorizedException` | 401 | Chưa đăng nhập / token không hợp lệ. |
| `ForbiddenException` | 403 | Không đủ quyền. |
| `BadRequestException` | 400 | Request sai (bad request). |
| `TooManyRequestsException` | 429 | Vượt rate limit. |
| `InternalServerErrorException` | 500 | Lỗi server (chủ động). |

Tất cả đều extend `Exception`, có `$code` tương ứng. Handler chỉ cần gọi `ApiResponse::error($message, $code, $errors)`.

### 4.2 Mapping trong Handler

- **API:** Chỉ khi `$request->expectsJson()` hoặc `$request->is('api/*')` mới render JSON; còn lại dùng render mặc định Laravel.
- **Thứ tự xử lý:** Custom exceptions → Laravel validation → Authentication → AccessDenied/Unauthorized HTTP → NotFound → MethodNotAllowed → TooManyRequests → ModelNotFound → generic (500).
- **Format:** Mọi case đều qua `standardizedErrorResponse()` → `ApiResponse::error()`, nên luôn có `success`, `message`, `meta`; có thể thêm `errors`.

### 4.3 Exceptions không report (dontReport)

Các exception sau không ghi log (coi là lỗi nghiệp vụ / client):

- `ValidationException`, `ResourceNotFoundException`, `UnauthorizedException`, `ForbiddenException`, `BadRequestException`, `TooManyRequestsException`

Exception khác vẫn được log (kèm request_id, message, file, line; trace khi debug).

---

## 5. Dùng trong code

### 5.1 Controller (khuyến nghị)

API controller extend `ApiController` (có `ApiResponseTrait`):

```php
// Success
return $this->successResponse(new UserResource($user), 'User retrieved successfully');
return $this->successResponse($user, 'User created successfully', 201);

// Error (thường để Handler xử lý bằng cách throw)
return $this->notFoundResponse('User not found');
return $this->errorResponse('Bad request', 400, ['field' => ['Detail']]);

// Paginated
return $this->resourcePaginatedResponse(
    UserResource::collection($users->items()),
    $users,
    'Users retrieved successfully'
);
```

Trait cung cấp: `successResponse`, `errorResponse`, `validationErrorResponse`, `notFoundResponse`, `unauthorizedResponse`, `forbiddenResponse`, `paginatedResponse`, `resourcePaginatedResponse`.

### 5.2 Gọi trực tiếp ApiResponse

```php
use App\Support\ApiResponse;

return ApiResponse::success($data, 'OK');
return ApiResponse::error('Unauthorized', 401);
return ApiResponse::error('Validation failed', 422, ['email' => ['Invalid.']]);
```

### 5.3 Throw trong Service

Handler sẽ bắt và trả JSON chuẩn, không cần catch trong controller (trừ khi cần xử lý đặc biệt):

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

public function create(UserDTO $dto): UserDTO
{
    if ($this->userRepository->existsByEmail($dto->email)) {
        throw new ValidationException('Email already exists');
    }
    // ...
}
```

Controller chỉ cần:

```php
public function show(int $id): JsonResponse
{
    $user = $this->userService->getModelByIdWithRelations($id);
    return $this->successResponse(new UserResource($user), 'User retrieved successfully');
}
```

### 5.4 Validation (Form Request)

Form Request fail → Laravel throw `LaravelValidationException` → Handler trả 422 với `errors` đúng format. Không cần catch trong controller.

---

## 6. Middleware (CheckRole / CheckPermission)

Hai middleware trả về cùng format `ApiResponse::error()`:

- **401 Unauthenticated:** `message`: "Unauthenticated."
- **403 Forbidden (role):** `message`: "Forbidden. You do not have the required role.", `errors`: `required_roles`, `user_roles`.
- **403 Forbidden (permission):** `message`: "Forbidden. You do not have the required permission.", `errors`: `required_permission`.

---

## 7. Headers

Khi có request id, response kèm:

```http
X-Request-ID: <request_id>
X-Correlation-ID: <request_id>
```

---

## 8. Ví dụ response lỗi theo từng mã

### 422 Validation

```json
{
  "success": false,
  "message": "The email must be a valid email address.",
  "meta": { "request_id": "...", "timestamp": "..." },
  "errors": {
    "email": ["The email must be a valid email address."],
    "name": ["The name field is required."]
  }
}
```

### 401 Unauthorized

```json
{
  "success": false,
  "message": "Unauthenticated",
  "meta": { "request_id": "...", "timestamp": "..." }
}
```

### 403 Forbidden

```json
{
  "success": false,
  "message": "Forbidden. You do not have the required permission.",
  "meta": { "request_id": "...", "timestamp": "..." },
  "errors": { "required_permission": ["manage users"] }
}
```

### 404 Not Found

```json
{
  "success": false,
  "message": "User with ID 999 not found",
  "meta": { "request_id": "...", "timestamp": "..." }
}
```

### 429 Too Many Requests

```json
{
  "success": false,
  "message": "Too Many Requests",
  "meta": { "request_id": "...", "timestamp": "..." }
}
```

### 500 (production vs debug)

Production (`APP_DEBUG=false`):

```json
{
  "success": false,
  "message": "An error occurred. Please try again later.",
  "meta": { "request_id": "...", "timestamp": "..." }
}
```

Debug (`APP_DEBUG=true`): `message` có thể là nội dung exception; `meta.status_code`: 500.

---

## 9. Best practices

1. Luôn kiểm tra `success` trước khi dùng `data` ở frontend.
2. Validation: đọc từ `errors` (object theo field).
3. Không dựa vào top-level `statusCode` hay `path` — project chỉ dùng `message`, `meta`, `errors`.
4. Controller API v1: dùng `ApiResponse` / `ApiResponseTrait`, tránh `response()->json(...)` tùy ý.
5. Service: throw custom exception; Handler thống nhất format.
6. Không return `null` hoặc array lỗi thay vì throw exception khi có lỗi nghiệp vụ.

---

## 10. Test nhanh

```php
$response = $this->getJson('/api/v1/users/999');
$response->assertStatus(404);
$response->assertJson([
    'success' => false,
    'message' => 'User with ID 999 not found',
]);
$response->assertJsonStructure(['success', 'message', 'meta']);
```

---

## 11. Tài liệu liên quan

- [API_FOUNDATION.md](API_FOUNDATION.md) – Versioning, pagination, resources.
- [LOGGING.md](LOGGING.md) – Log exception kèm request_id.
