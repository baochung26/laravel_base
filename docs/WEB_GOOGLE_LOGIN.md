# Web Google Login (Session) - Flow & Setup

Tài liệu mô tả luồng đăng nhập Google cho web (session-based) và hướng dẫn cấu hình `GOOGLE_CLIENT_ID`.

## Tổng quan

Luồng hiện tại dùng Google Identity Services (GIS) để lấy `id_token` trên frontend, sau đó gửi về server qua `POST /login/google`.
Server xác thực `id_token` với Google, tạo hoặc cập nhật user, rồi đăng nhập bằng guard `web`.

## Luồng hiện tại

1. Người dùng mở trang `/login`
   - View: `resources/views/auth/login.blade.php`
   - Nếu `GOOGLE_CLIENT_ID` có giá trị, hiển thị Google Sign-In button + form ẩn `google-login-form` (có CSRF).
2. Frontend lấy `id_token`
   - Script: `resources/js/pages/auth.js`
   - Tải `https://accounts.google.com/gsi/client`.
   - Callback `onGoogleSignInCallback` nhận `response.credential`, gán vào input `google-id-token` rồi submit form ẩn.
3. Server nhận `POST /login/google`
   - Route: `routes/web.php` -> `AuthenticatedSessionController@google`.
   - Request: `app/Http/Requests/Web/Auth/GoogleLoginRequest.php` yêu cầu `id_token`.
4. Xác thực token và xử lý user
   - `GoogleAuthService::authenticate` gọi `GoogleTokenVerifier::verifyIdToken`.
   - `GoogleTokenVerifier` gọi `https://oauth2.googleapis.com/tokeninfo` và kiểm tra:
     - issuer hợp lệ
     - audience khớp `config('services.google.client_id')`
     - token chưa hết hạn
   - Tìm user theo `google_id`, nếu không có thì tìm theo email.
   - Tạo mới nếu chưa tồn tại (set `google_id`, `avatar`, `email_verified_at`, random password).
   - Nếu đã có user, cập nhật `google_id`, `avatar`, `email_verified_at` nếu thiếu.
5. Đăng nhập và redirect
   - `GoogleAuthService::login` -> `Auth::guard('web')->login` + `session()->regenerate()`.
   - Controller redirect về `route('dashboard')` (intended) kèm flash message.

## Files liên quan

- `routes/web.php`
- `resources/views/auth/login.blade.php`
- `resources/js/pages/auth.js`
- `app/Http/Controllers/Web/Auth/AuthenticatedSessionController.php`
- `app/Http/Requests/Web/Auth/GoogleLoginRequest.php`
- `app/Services/GoogleAuthService.php`
- `app/Services/GoogleTokenVerifier.php`
- `config/services.php`
- `database/migrations/2026_02_05_000006_add_google_id_to_users_table.php`

## Hướng dẫn setup

1. Tạo OAuth Client ID (Web Application)
   - Vào Google Cloud Console -> APIs & Services -> Credentials -> Create Credentials -> OAuth client ID.
   - Loại: Web application.
   - Authorized JavaScript origins: domain chạy trang `/login` (ví dụ `http://localhost:8000`).
   - Lưu `Client ID`.
2. Cập nhật `.env`
   - Thêm `GOOGLE_CLIENT_ID=...` vào `.env`.
   - Nên dùng 1 Client ID duy nhất (frontend dùng trực tiếp giá trị này).
3. Clear config cache (nếu đã cache)
   - `php artisan config:clear`
4. Migrate DB (nếu chưa có cột `google_id`)
   - `php artisan migrate`
5. Kiểm thử
   - Truy cập `/login` -> thấy nút Google.
   - Đăng nhập -> redirect về `dashboard`.

## Ghi chú quan trọng

- Server cần outbound network tới `oauth2.googleapis.com` để verify token.
- Nếu `GOOGLE_CLIENT_ID` rỗng, nút Google sẽ bị ẩn/disabled.
- Khi đổi domain/port, cần cập nhật Authorized JavaScript origins tương ứng.

## Troubleshooting nhanh

- `Google token audience mismatch`: `GOOGLE_CLIENT_ID` không khớp hoặc origin sai.
- `Invalid Google token` / `issuer`: token không hợp lệ.
- `Google token has expired`: token hết hạn hoặc máy client lệch giờ.
- Không thấy nút Google: chưa set `GOOGLE_CLIENT_ID` hoặc config cache chưa clear.
