# API Response Format (Chuẩn hoá)

Tài liệu mô tả chuẩn response/error response đang dùng trong hệ thống.

## 📋 Tổng quan

Mọi API response đều theo format chuẩn, đảm bảo:
- ✅ Dễ parse ở client
- ✅ Đồng nhất giữa success và error
- ✅ Có `request_id` để trace
- ✅ Có `timestamp` cho logging/debug

Nguồn chuẩn:
- `app/Support/ApiResponse.php`
- `app/Traits/ApiResponseTrait.php`
- `app/Exceptions/Handler.php`

## ✅ Success Response (Chuẩn)

```json
{
    "success": true,
    "message": "Success message",
    "meta": {
        "request_id": "laravel-20240101120000-abc12345",
        "timestamp": "2026-02-07T12:00:00Z"
    },
    "data": {
        // Response data
    }
}
```

### Ghi chú
- Trường `data` chỉ xuất hiện khi có dữ liệu trả về.
- `meta.request_id` luôn có nếu middleware `RequestIdMiddleware` chạy.
- `meta.timestamp` luôn có.
- Khi `APP_DEBUG=true`, sẽ có thêm `meta.status_code`.

## ❌ Error Response (Chuẩn)

```json
{
    "success": false,
    "message": "Error message",
    "meta": {
        "request_id": "laravel-20240101120000-abc12345",
        "timestamp": "2026-02-07T12:00:00Z"
    },
    "errors": {
        // Validation errors (optional)
        "field_name": ["Error message"]
    }
}
```

### Ghi chú
- `errors` chỉ có khi là validation lỗi hoặc có error details.
- Nếu là validation lỗi, `message` sẽ lấy lỗi đầu tiên (để client dễ show toast).

## 🧪 Error Response khi APP_DEBUG=true

```json
{
    "success": false,
    "message": "Validation failed",
    "meta": {
        "request_id": "laravel-20240101120000-abc12345",
        "timestamp": "2026-02-07T12:00:00Z",
        "status_code": 422
    },
    "errors": {
        "email": ["The email field is required."]
    }
}
```

## 📄 Pagination Response

Pagination sẽ trả thêm `meta` và `links` (ngoài `meta` chuẩn).

```json
{
    "success": true,
    "message": "Users retrieved successfully",
    "meta": {
        "request_id": "laravel-20240101120000-abc12345",
        "timestamp": "2026-02-07T12:00:00Z",
        "current_page": 1,
        "per_page": 15,
        "total": 100,
        "last_page": 7,
        "from": 1,
        "to": 15
    },
    "data": [
        // Items array
    ],
    "links": {
        "first": "http://localhost:8000/api/v1/users?page=1",
        "last": "http://localhost:8000/api/v1/users?page=7",
        "prev": null,
        "next": "http://localhost:8000/api/v1/users?page=2"
    }
}
```

## 🔐 Auth Response (Login/Register)

```json
{
    "success": true,
    "message": "Login successful",
    "meta": {
        "request_id": "laravel-20240101120000-abc12345",
        "timestamp": "2026-02-07T12:00:00Z"
    },
    "data": {
        "user": { /* UserResource */ },
        "token": "access_token_value",
        "access_token": "access_token_value",
        "refresh_token": "refresh_token_value",
        "permissions": [],
        "roles": [],
        "login_type": "password"
    }
}
```

Ghi chú:
- `token` = `access_token` để tương thích ngược.
- `login_type`: `password` hoặc `google`.

## ✅ Cách dùng trong Controller

### Dùng `ApiResponseTrait`

```php
return $this->successResponse($data, 'Created', 201);
return $this->errorResponse('Bad Request', 400);
return $this->validationErrorResponse($errors, 'Validation failed');
return $this->notFoundResponse('Not found');
return $this->unauthorizedResponse('Unauthorized');
```

### Dùng trực tiếp `ApiResponse`

```php
use App\Support\ApiResponse;

return ApiResponse::success($data, 'OK');
return ApiResponse::error('Unauthorized', 401);
```

## 🧷 Headers mặc định

Nếu có `request_id`, hệ thống sẽ tự thêm:

```http
X-Request-ID: <request_id>
X-Correlation-ID: <request_id>
```

## 📌 Lưu ý chung

- Tránh trả về `response()->json(...)` thủ công trong API V1.
- Ưu tiên `ApiResponseTrait` hoặc `ApiResponse`.
- Khi thêm API mới, luôn giữ format chuẩn để dễ maintain và log.
