# Swagger / OpenAPI Guide

## 1. Endpoints tài liệu

- Swagger UI: `GET /api/v1/docs`
- OpenAPI spec (YAML): `GET /api/v1/openapi.yaml`

Ví dụ local:

```text
http://localhost:8000/api/v1/docs
http://localhost:8000/api/v1/openapi.yaml
```

## 2. Cách sử dụng Swagger UI

1. Mở `http://localhost:8000/api/v1/docs`.
2. Với API protected, bấm **Authorize**.
3. Nhập token theo format:
   `Bearer YOUR_ACCESS_TOKEN`
4. Chọn endpoint và bấm **Try it out** để test trực tiếp.

## 3. Luồng test nhanh

1. Gọi `POST /api/v1/login` để lấy `access_token`.
2. Authorize trong Swagger bằng token vừa lấy.
3. Test các API cần auth như:
   - `GET /api/v1/me`
   - `GET /api/v1/users`
   - `POST /api/v1/files/upload`

## 4. Cập nhật tài liệu khi thêm API mới

OpenAPI spec nằm tại: `docs/openapi.yaml`.

Khi thêm/sửa route ở `routes/api/v1/routes.php`, cần cập nhật:

1. `paths` trong `docs/openapi.yaml`
2. `requestBody` và `responses` tương ứng
3. `components/schemas` nếu có payload mới

## 5. Response example đồng bộ với API thực tế

Các schema response trong `docs/openapi.yaml` đã được thêm **example** khớp với response thực tế:

- **Chuẩn chung:** Mọi response có `success`, `message`, `meta` (request_id, timestamp); success có `data`, error có `errors` khi cần. Chi tiết: `docs/API_RESPONSE_AND_ERRORS.md`.
- **Success không data** (logout, delete, forgot password…): chỉ `success`, `message`, `meta`; không có key `data`.
- **Auth:** Register/Login trả `data.user`, `data.token`, `data.access_token`, `data.refresh_token`, `data.permissions`, `data.roles`, `data.login_type`.
- **Profile:** `GET /profile` trả `data` là object User (UserResource), không bọc `data.user`.
- **Me:** `GET /me` trả `data.user`, `data.permissions`, `data.roles`.
- **Users index:** Phân trang với `meta` (current_page, per_page, total, last_page, from, to) và `links` (first, last, prev, next).
- **Health ready 503:** ErrorEnvelope với `errors.checks` (db, redis) và `errors.timestamp`.

Khi thêm endpoint mới, nên thêm/cập nhật example trong schema tương ứng để Swagger UI hiển thị đúng.

## 6. Ghi chú triển khai

- Swagger UI đang dùng CDN `swagger-ui-dist` (unpkg) để render giao diện.
- Project không phụ thuộc package Swagger PHP, nên không cần bước generate docs.
- Spec được quản lý thủ công để bám sát contract API hiện tại.
