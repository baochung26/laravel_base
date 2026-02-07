# API Response Format

Tai lieu mo ta format response JSON chuan dang duoc su dung trong API v1 cua project Laravel nay.

## Tong quan

Tat ca API responses nen theo mot format thong nhat de:
- de parse o frontend
- de trace log theo `request_id`
- de test va maintain

Nguon su that (source of truth):
- `app/Support/ApiResponse.php`
- `app/Traits/ApiResponseTrait.php`
- `app/Exceptions/Handler.php`

## Success Response Format

### Standard Success (200)

```json
{
  "success": true,
  "message": "Success",
  "meta": {
    "request_id": "laravel-20260207120000-abc12345",
    "timestamp": "2026-02-07T12:00:00+00:00"
  },
  "data": {
    "id": 1,
    "name": "John Doe"
  }
}
```

### Created Success (201)

```json
{
  "success": true,
  "message": "User created successfully",
  "meta": {
    "request_id": "laravel-20260207120000-abc12345",
    "timestamp": "2026-02-07T12:00:00+00:00"
  },
  "data": {
    "id": 101,
    "name": "New User"
  }
}
```

### Luu y field

- `data` chi co khi co du lieu tra ve (`$data !== null`).
- `meta.request_id` co khi request id da duoc bind (qua middleware).
- `meta.timestamp` luon co.
- Khi `APP_DEBUG=true`, he thong them `meta.status_code`.

## Error Response Format

### Standard Error

```json
{
  "success": false,
  "message": "Bad Request",
  "meta": {
    "request_id": "laravel-20260207120000-abc12345",
    "timestamp": "2026-02-07T12:00:00+00:00"
  }
}
```

### Validation Error (422)

```json
{
  "success": false,
  "message": "The email field is required.",
  "meta": {
    "request_id": "laravel-20260207120000-abc12345",
    "timestamp": "2026-02-07T12:00:00+00:00"
  },
  "errors": {
    "email": [
      "The email field is required."
    ],
    "password": [
      "The password field is required."
    ]
  }
}
```

### Error khi APP_DEBUG=true

```json
{
  "success": false,
  "message": "Resource not found",
  "meta": {
    "request_id": "laravel-20260207120000-abc12345",
    "timestamp": "2026-02-07T12:00:00+00:00",
    "status_code": 404
  }
}
```

Luu y:
- Khong co field `statusCode` top-level.
- Khong co field `path` top-level.
- Khong co field `error` top-level.
- Chi su dung `message`, `meta`, va tuy chon `errors`.

## Paginated Response Format

Khi dung `paginatedResponse()` hoac `resourcePaginatedResponse()`, response co them pagination metadata va `links`.

```json
{
  "success": true,
  "message": "Users retrieved successfully",
  "meta": {
    "request_id": "laravel-20260207120000-abc12345",
    "timestamp": "2026-02-07T12:00:00+00:00",
    "current_page": 1,
    "per_page": 15,
    "total": 100,
    "last_page": 7,
    "from": 1,
    "to": 15
  },
  "data": [
    {
      "id": 1,
      "name": "User 1"
    }
  ],
  "links": {
    "first": "http://localhost:8000/api/v1/users?page=1",
    "last": "http://localhost:8000/api/v1/users?page=7",
    "prev": null,
    "next": "http://localhost:8000/api/v1/users?page=2"
  }
}
```

## Auth Response Format

Auth APIs (`register`, `login`, `googleLogin`) tra ve:

```json
{
  "success": true,
  "message": "Login successful",
  "meta": {
    "request_id": "laravel-20260207120000-abc12345",
    "timestamp": "2026-02-07T12:00:00+00:00"
  },
  "data": {
    "user": {
      "id": 1,
      "name": "John Doe"
    },
    "token": "access_token_value",
    "access_token": "access_token_value",
    "refresh_token": "refresh_token_value",
    "permissions": [],
    "roles": [],
    "login_type": "password"
  }
}
```

Luu y:
- `token` duoc giu de tuong thich nguoc.
- `access_token` la field uu tien cho client moi.
- `login_type`: `password` hoac `google`.

## HTTP Status Code Mapping (Error)

Theo `app/Exceptions/Handler.php`:

| Code | Meaning | Nguon |
| --- | --- | --- |
| 400 | Bad Request | `BadRequestException` |
| 401 | Unauthorized | `UnauthorizedException`, `AuthenticationException`, `UnauthorizedHttpException` |
| 403 | Forbidden | `ForbiddenException`, `AccessDeniedHttpException` |
| 404 | Not Found | `ResourceNotFoundException`, `ModelNotFoundException`, `NotFoundHttpException` |
| 405 | Method Not Allowed | `MethodNotAllowedHttpException` |
| 422 | Validation Error | `ValidationException`, `LaravelValidationException` |
| 429 | Too Many Requests | `TooManyRequestsException`, `TooManyRequestsHttpException` |
| 500 | Internal Server Error | `InternalServerErrorException`, generic exceptions |

## Cach su dung trong code

### Trong API controller (khuyen nghi)

Controller API v1 nen extend `ApiController` de dung `ApiResponseTrait`.

```php
public function show(int $id): JsonResponse
{
    $user = $this->userService->getModelByIdWithRelations($id);

    return $this->successResponse(new UserResource($user), 'User retrieved successfully');
}
```

```php
public function store(StoreUserRequest $request): JsonResponse
{
    $user = $this->userService->create(...);

    return $this->successResponse(new UserResource($user), 'User created successfully', 201);
}
```

### Dung truc tiep `ApiResponse`

```php
use App\Support\ApiResponse;

return ApiResponse::success($data, 'OK');
return ApiResponse::error('Unauthorized', 401);
```

## Headers lien quan

Neu co request id, response se co:

```http
X-Request-ID: <request_id>
X-Correlation-ID: <request_id>
```

## Authorization Middleware Responses

`CheckRole` va `CheckPermission` da duoc chuan hoa theo cung format `ApiResponse`.

### Unauthenticated (401)

```json
{
  "success": false,
  "message": "Unauthenticated.",
  "meta": {
    "request_id": "laravel-20260207120000-abc12345",
    "timestamp": "2026-02-07T12:00:00+00:00"
  }
}
```

### Forbidden by role (403)

```json
{
  "success": false,
  "message": "Forbidden. You do not have the required role.",
  "meta": {
    "request_id": "laravel-20260207120000-abc12345",
    "timestamp": "2026-02-07T12:00:00+00:00"
  },
  "errors": {
    "required_roles": ["admin"],
    "user_roles": ["user"]
  }
}
```

### Forbidden by permission (403)

```json
{
  "success": false,
  "message": "Forbidden. You do not have the required permission.",
  "meta": {
    "request_id": "laravel-20260207120000-abc12345",
    "timestamp": "2026-02-07T12:00:00+00:00"
  },
  "errors": {
    "required_permission": ["users.delete"]
  }
}
```

## Best Practices

1. Luon kiem tra `success` truoc khi su dung `data`.
2. Validation errors doc tu field `errors`.
3. Khong hard-code format response o frontend theo `statusCode`/`path` top-level (project nay khong dung).
4. Middleware va API moi trong `api/v1` nen su dung `ApiResponse`/`ApiResponseTrait` thay vi `response()->json(...)` thu cong.
