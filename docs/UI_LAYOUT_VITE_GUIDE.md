# UI Layout & Vite Guide

## Mục tiêu

Tài liệu này mô tả chuẩn frontend hiện tại của project:

- Cấu trúc Blade layout/partial/component
- Cách tổ chức CSS/JS
- Cách chạy Vite trong Docker cho môi trường dev
- Cách build assets cho flow deploy/start

---

## 1. Frontend Stack

- Template engine: Blade
- Bundler: Vite (`laravel-vite-plugin`)
- CSS framework: Bootstrap 5 (npm package)
- JS utility: Axios + Vanilla JS modules
- Third-party script: Google Identity Services (login Google)

---

## 2. Cấu trúc UI Layout

### 2.1 Layout chính

- `resources/views/layouts/landing.blade.php`
- `resources/views/layouts/guest.blade.php`
- `resources/views/layouts/app.blade.php` (dashboard shell + sidebar)

### 2.2 Partial/Component

- Landing header/footer:
  - `resources/views/partials/landing/header.blade.php`
  - `resources/views/partials/landing/footer.blade.php`
- Dashboard icon component:
  - `resources/views/components/dashboard/icon.blade.php`

### 2.3 Dashboard menu động

- File config: `config/dashboard.php`
- Hỗ trợ:
  - `route` hoặc `url`
  - `active` route patterns
  - `roles` / `permissions` (Spatie)

---

## 3. Cấu trúc CSS/JS (chuẩn Vite)

### 3.1 CSS

- `resources/css/landing.css`
- `resources/css/auth.css`
- `resources/css/dashboard.css`

Mỗi layout dùng entry CSS riêng qua `@vite(...)` để tránh CSS bleed.

### 3.2 JS

- `resources/js/bootstrap.js` (axios global)
- `resources/js/app.js` (bootstrap js bundle)
- `resources/js/pages/auth-login.js` (logic submit/login Google cho trang login)

### 3.3 Vite config

- `vite.config.js`
- `package.json`

---

## 4. Blade wiring với `@vite`

Ví dụ layout:

```blade
@vite(['resources/css/dashboard.css', 'resources/js/app.js'])
```

Page script riêng:

```blade
@push('scripts')
    @vite('resources/js/pages/auth-login.js')
@endpush
```

Và trong layout cần:

```blade
@stack('scripts')
```

---

## 5. Docker Workflow cho Vite

## 5.1 Service trong `docker-compose.yml`

- `node`: chạy `npm run dev` (HMR), tự start khi `docker-compose up -d`
- `node_build`: chạy `npm run build` (one-shot), dùng cho build/deploy flow

Port mặc định Vite:

- `${VITE_PORT:-5173}`

### 5.2 Commands thường dùng

- Start full local stack (bao gồm Vite dev):
  - `docker-compose up -d`
- Xem logs Vite:
  - `docker-compose logs -f node`
- Install frontend deps:
  - `make npm-install`
- Build assets:
  - `make npm-build`
- Build assets rồi start backend:
  - `make up-build`

---

## 6. Quy ước code frontend

- Không để CSS dài inline trong Blade.
- Không để JS nghiệp vụ inline trong Blade (trừ snippet cực ngắn).
- Mỗi page có logic riêng => tạo file trong `resources/js/pages/`.
- Mỗi layout lớn có CSS entry riêng để dễ maintain.
- Menu dashboard không hard-code trong layout, luôn đi qua `config/dashboard.php`.

---

## 7. Flow deploy/start khuyến nghị

1. Build assets:
   - `docker-compose --profile build run --rm node_build`
2. Start app stack:
   - `docker-compose up -d app queue scheduler webserver db redis`

Nếu chạy local dev thông thường:

- Chỉ cần `docker-compose up -d` (service `node` sẽ tự chạy Vite dev).

---

## 8. Triển khai Vite theo môi trường

### 8.1 Development

Mục tiêu: hot reload nhanh, không cần build static mỗi lần sửa.

1. Start stack:
   - `docker-compose up -d`
2. Đảm bảo Vite đang chạy:
   - `docker-compose ps node`
   - `docker-compose logs -f node`
3. Truy cập:
   - App: `http://localhost:${WEB_PORT}`
   - Vite: `http://localhost:${VITE_PORT}`

Ghi chú:

- Ở môi trường dev, `@vite(...)` sẽ dùng dev server (HMR).
- Không cần chạy `npm run build` sau mỗi lần sửa CSS/JS.
- Nếu thay đổi không phản ánh, kiểm tra log service `node`.

### 8.2 Production

Mục tiêu: serve static assets đã build từ `public/build`.

1. Build frontend assets:
   - `docker-compose --profile build run --rm node_build`
2. Start backend stack (không cần Vite dev server):
   - `docker-compose up -d app queue scheduler webserver db redis`
3. Verify build output:
   - `public/build/manifest.json` phải tồn tại
4. Clear/rebuild cache Laravel (khuyến nghị):
   - `docker-compose exec app php artisan optimize:clear`
   - `docker-compose exec app php artisan config:cache`
   - `docker-compose exec app php artisan route:cache`
   - `docker-compose exec app php artisan view:cache`

Checklist production:

- `APP_ENV=production`
- `APP_DEBUG=false`
- Đã build assets trước khi deploy container web/app
- Không chạy service `node` dev trong môi trường production

### 8.3 Cập nhật frontend khi đã deploy

Khi có thay đổi ở `resources/css` hoặc `resources/js`:

1. Build lại:
   - `docker-compose --profile build run --rm node_build`
2. Reload app/web containers:
   - `docker-compose restart app webserver`

