# Quick Start Guide

Chạy dự án Laravel API base (Docker) và gọi API lần đầu.

---

## 1. Yêu cầu

- Docker Desktop (hoặc Docker Engine + Docker Compose)
- Git

---

## 2. Khởi động với Docker

```bash
# Clone / vào thư mục dự án
cd laravel_base_cursor

# Khởi động containers
docker-compose up -d

# Cài đặt dependencies
docker-compose exec app composer install

# Copy env và tạo key
docker-compose exec app cp .env.example .env
docker-compose exec app php artisan key:generate
```

---

## 3. Database và RBAC

```bash
# Publish Spatie Permission migrations (nếu chưa có bảng permission)
docker-compose exec app php artisan vendor:publish --provider="Spatie\Permission\PermissionServiceProvider"

# Chạy migrations
docker-compose exec app php artisan migrate

# Seed roles & permissions (admin, user, permissions)
docker-compose exec app php artisan db:seed --class=RolePermissionSeeder

# (Tùy chọn) Seed dữ liệu demo
docker-compose exec app php artisan db:seed --class=DemoSeeder
```

---

## 4. Truy cập ứng dụng

| Dịch vụ | URL |
|--------|-----|
| **API / App** | http://localhost:8000 |
| **phpMyAdmin** | http://localhost:8080 |

API base path: **`http://localhost:8000/api/v1`**.

---

## 5. Demo: Gọi API nhanh

### 5.1 Health check (không cần auth)

```bash
curl http://localhost:8000/api/v1/health
```

Ví dụ response:

```json
{
  "success": true,
  "message": "OK",
  "meta": { "request_id": "...", "timestamp": "..." },
  "data": { "status": "healthy" }
}
```

### 5.2 Đăng ký

```bash
curl -X POST http://localhost:8000/api/v1/register \
  -H "Content-Type: application/json" \
  -d '{
    "name": "Demo User",
    "email": "demo@example.com",
    "password": "password123",
    "password_confirmation": "password123"
  }'
```

Response chứa `data.access_token` và `data.refresh_token`. Lưu `access_token` cho bước sau.

### 5.3 Đăng nhập (nếu đã có tài khoản)

```bash
curl -X POST http://localhost:8000/api/v1/login \
  -H "Content-Type: application/json" \
  -d '{"email":"demo@example.com","password":"password123"}'
```

Lấy `data.access_token` từ response.

### 5.4 Lấy thông tin user (cần token)

```bash
export TOKEN="<dán_access_token_vào_đây>"
curl http://localhost:8000/api/v1/me \
  -H "Authorization: Bearer $TOKEN"
```

### 5.5 Danh sách users (cần permission `manage users` – admin)

```bash
curl "http://localhost:8000/api/v1/users?per_page=10" \
  -H "Authorization: Bearer $TOKEN"
```

Response có dạng paginated: `data` (mảng user), `meta` (current_page, per_page, total, ...), `links` (first, next, ...).

---

## 6. Dùng Makefile (khuyến nghị)

```bash
make help      # Xem lệnh
make setup     # Cài đặt lần đầu (containers, composer, .env, key, migrate)
make up        # Bật containers
make down      # Tắt containers
make shell     # Shell trong container app
make artisan CMD="migrate"   # Chạy migrate
make fresh     # migrate:fresh + seed
make cache-clear
```

Sau `make setup`, nhớ chạy seed RBAC và (tuỳ chọn) demo:

```bash
docker-compose exec app php artisan db:seed --class=RolePermissionSeeder
docker-compose exec app php artisan db:seed --class=DemoSeeder
```

---

## 7. Tài khoản demo (sau khi chạy DemoSeeder / RolePermissionSeeder)

| Email | Password | Ghi chú |
|-------|----------|--------|
| admin@example.com | password | Admin (manage users, manage roles) |
| user@example.com | password | User thường |

---

## 8. Cấu trúc routes API v1

Routes được định nghĩa trong `routes/api/v1/routes.php`, prefix `api/v1`:

- **Public:** `POST /register`, `POST /login`, `POST /login/google`, `POST /password/forgot`, `POST /password/reset`, `GET /health`, `GET /health/live`, `GET /health/ready`
- **Auth (Bearer):** `POST /logout`, `POST /refresh`, `GET /me`, `GET|PUT /profile/*`, `POST /password/change`, `GET|POST|PUT|DELETE /users/*`, `GET|POST .../roles-permissions/*`, `POST|GET|DELETE .../files/*`

Chi tiết từng nhóm: [AUTHENTICATION.md](AUTHENTICATION.md), [USER_MODULE.md](USER_MODULE.md), [API_FOUNDATION.md](API_FOUNDATION.md).
