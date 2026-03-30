# Báo cáo đánh giá Web Routes, Controllers, Templates, CSS & JS

> Tài liệu đánh giá cấu trúc và tối ưu hóa phần web của dự án Laravel.

## 1. Routes Web

### Điểm tốt
- Phân tách rõ theo middleware: `guest`, `auth`, `auth + verified`
- Tách routes dashboard vào file riêng `routes/web/dashboard.php`
- Đặt tên route nhất quán (`name()`)
- Có throttle cho verification và reset password
- Group prefix `dashboard` cho các route con

### Chưa tốt
- ~~Route `/dashboard` và `/dashboard/users` nằm trong cùng prefix nhưng cấu trúc hơi khác nhau~~ → Đã sửa: gộp vào `Route::prefix('dashboard')`
- ~~Một số POST route chưa có name~~ → Đã sửa: thêm `register.store`, `login.store`

### Đã áp dụng

```php
// routes/web.php
Route::post('/register', [RegisteredUserController::class, 'store'])->name('register.store');
Route::post('/login', [AuthenticatedSessionController::class, 'store'])->name('login.store');

// routes/web/dashboard.php - cấu trúc thống nhất
Route::prefix('dashboard')->group(function () {
    Route::get('/', DashboardController::class)->name('dashboard');
    Route::name('dashboard.')->group(function () {
        Route::get('/users', ...)->name('users.index');
        // ...
    });
});
```

---

## 2. Controllers

### Điểm tốt
- Namespace rõ ràng: `Web\Auth`, `Web.Dashboard`
- Dùng Form Request (`LoginRequest`, `GoogleLoginRequest`)
- Dependency injection (`GoogleTokenVerifier`)
- `DashboardUserController` có helper tái sử dụng (`ensureAdmin`, `redirectBackToUsersList`)
- Validation và xử lý lỗi chuẩn Laravel

### Chưa tốt
- ~~Logic trong `AuthenticatedSessionController::google` khá dài~~ → Đã sửa: tách sang `GoogleAuthService`
- ~~`DashboardUserController::index` gọi 3 query riêng cho stats~~ → Đã sửa: tính `inactive` từ `total - active`
- ~~`ensureAdmin` dùng `abort_unless`~~ → Đã sửa: dùng middleware `EnsureAdmin`

### Đã áp dụng

1. **GoogleAuthService** (`app/Services/GoogleAuthService.php`):
   - `authenticate(string $idToken)` – xác thực hoặc tạo user từ Google
   - `login(User $user)` – đăng nhập và regenerate session

2. **Stats tối ưu** trong `DashboardUserController::index`:

```php
$stats = [
    'total' => User::query()->count(),
    'active' => User::query()->whereNotNull('email_verified_at')->count(),
];
$stats['inactive'] = max($stats['total'] - $stats['active'], 0);
$stats['admin'] = User::query()->whereHas('roles', fn ($q) => $q->where('name', 'admin'))->count();
```

3. **Middleware `admin`** (`app/Http/Middleware/EnsureAdmin.php`):
   - Áp dụng cho group route `dashboard.users.*`
   - Redirect về dashboard với `session('error')` nếu user không phải admin

---

## 3. Templates (Blade)

### Điểm tốt
- Ba layout rõ ràng: `layouts.app`, `layouts.guest`, `layouts.landing`
- Dùng `@extends`, `@section`, `@stack`, `@push` đúng chuẩn
- Partial: `partials.landing.header`, `partials.landing.footer`
- Component: `x-dashboard.icon`
- Cấu hình sidebar từ `config/dashboard.php`
- Data attributes cho form: `data-validate`, `data-confirm`, `data-loading`
- `@error()`, `old()` cho validation

### Chưa tốt
- ~~**Logic PHP trong `app.blade.php`**~~ → Đã sửa: chuyển sang `DashboardSidebarComposer`
- ~~**Inline style** trong `login.blade.php`~~ → Đã sửa: dùng class `auth-remember-label`
- ~~**`profile.css`** không có trong Vite~~ → Đã sửa: thêm vào `vite.config.js` input

### Đã áp dụng

1. **View Composer** `app/View/Composers/DashboardSidebarComposer.php`:
   - Lọc menu theo roles/permissions
   - Tính sẵn `url` và `is_active` cho từng item
   - Đăng ký trong `AppServiceProvider` cho view `layouts.app`

2. **Class thay inline style** trong `auth.css`:
   - `.auth-remember-label` – flex, gap, color
   - `.auth-remember-checkbox` – kích thước 16x16

3. **`profile.css`** đã thêm vào `vite.config.js` input
```

---

## 4. CSS

### Điểm tốt
- Tách CSS theo context: `landing.css`, `auth.css`, `dashboard.css`, `profile.css`
- Dùng CSS variables (`:root`) cho theme
- Landing hỗ trợ dark/light (`data-theme`)
- Responsive với media queries
- BEM-style prefix rõ ràng (`.auth-`, `.dash-`, `.landing-`)

### Chưa tốt
- ~~**Trùng lặp code** giữa các file~~ → Đã sửa: tách sang `_shared.css`
- ~~**`dashboard.css` dùng `!important` nhiều**~~ → Đã sửa: dùng selector specificity
- `dashboard.css` ~620 dòng – đã giảm
- `landing.css` import toàn bộ Bootstrap – có thể purge/import chọn lọc (tùy chọn)

### Đã áp dụng

1. **`resources/css/_shared.css`** – chứa:
   - `.app-loading-overlay`, `.app-loading-box`, `.app-loading-spinner`
   - `.app-swal-popup`, `.app-swal-confirm`, `.app-swal-cancel`
   - `.app-toast-popup` (SweetAlert2 toast)
   - Dùng biến `--app-shared-*` để mỗi context override theme

2. **Auth, dashboard, landing** đều `@import '_shared.css'` và định nghĩa `--app-shared-*` trong `:root`

3. **Giảm `!important`** trong dashboard:
   - Typography: dùng `.dash-body .dash-content p` thay vì `.dash-body p { font-size: 14px !important }`
   - `.dash-users-action`: thêm selector `.dash-users-actions-dropdown .dash-users-action`
   - `.dash-users-empty`: thêm `.dash-users-table .dash-users-empty`
   - Giữ `!important` cho `-webkit-autofill` (override trình duyệt) và `.app-toast-popup` (override SweetAlert2)

---

## 5. JavaScript

### Điểm tốt
- `app.js` là entry chung, load trên mọi trang
- Page-specific: `auth.js` (login, register) chỉ load khi cần
- UI helpers: `showToast`, `showConfirm`, `showLoading`, `hideLoading`
- Form validation qua `data-validate`
- Confirm submit qua `data-confirm`
- Auto-submit cho filter form
- Theme toggle
- `AppUI` expose ra `window` để dùng global

### Chưa tốt
- ~~**`auth-login.js` và `auth-register.js` logic gần giống nhau**~~ → Đã sửa: gộp vào `auth-forms.js`
- `auth.js` không dùng cho forgot-password, reset-password (các form này dùng `data-validate` từ `app.js`)
- ~~**Không có lazy load cho Google GSI**~~ → Đã sửa: load động khi `g_id_onload` có trong DOM

### Đã áp dụng

1. **`resources/js/pages/auth-forms.js`** – module chung:
   - `initAuthFormSubmit(formId, btnId, loadingText)` – disable button, đổi text khi submit

2. **`auth.js`** – gộp logic login + register; init theo form có trong DOM; load Google GSI động khi `g_id_onload` tồn tại

3. **Login/Register blade** – cùng load `auth.js`; bỏ `<script src="...gsi/client">` tĩnh
```

---

## 6. Vite / Build

### Điểm tốt
- Dùng Vite với Laravel plugin
- Tách entry theo context
- Cấu hình CORS, HMR, load env

### Chưa tốt
- ~~**`profile.css` không có trong `vite.config.js`**~~ → Đã sửa (trước đó)
- ~~**`auth-login.js` và `auth-register.js` load riêng**~~ → Đã sửa: gộp vào `auth.js`

### Đã áp dụng

1. **`profile.css`** đã có trong `vite.config.js` input

2. **`resources/js/pages/auth.js`** – 1 entry thay cho 2:
   - Init login form nếu `login-form` tồn tại
   - Init register form nếu `register-form` tồn tại
   - Google callback + lazy load GSI nếu `g_id_onload` tồn tại
   - Login và register đều load `auth.js` → 1 chunk thay vì 2

---

## 7. Tổng kết

| Hạng mục    | Điểm tốt | Cần cải thiện               |
|-------------|----------|-----------------------------|
| Routes      | 8/10     | Naming, tổ chức nhóm        |
| Controllers | 8/10     | Tách Service, tối ưu query |
| Templates   | 7/10     | Logic trong view, inline CSS |
| CSS         | 7/10     | Trùng lặp, `!important`   |
| JS          | 8/10     | Trùng logic auth form      |
| Vite/Build  | 7/10     | Thiếu `profile.css`        |

### Ưu tiên thực hiện

1. **Cao:** Thêm `profile.css` vào `vite.config.js`
2. **Cao:** Bỏ inline style trong `login.blade.php`, dùng class
3. **Trung bình:** Tách logic sidebar trong `app.blade.php` sang View Composer
4. **Trung bình:** Tách CSS chung (loading, sweetalert) vào file shared
5. **Thấp:** Gộp logic auth form trong JS
6. **Thấp:** Tối ưu query stats trong `DashboardUserController`

---

## Xem thêm

- [WEB_TEMPLATE_STRUCTURE.md](WEB_TEMPLATE_STRUCTURE.md) – Cấu trúc template và stack
- [UI_LAYOUT_VITE_GUIDE.md](UI_LAYOUT_VITE_GUIDE.md) – Hướng dẫn layout và Vite
