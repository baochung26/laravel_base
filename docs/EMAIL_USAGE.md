# Email Usage Guide

Cấu hình và sử dụng email trong project: biến môi trường, template, xác thực email, reset mật khẩu, và gửi email tùy chỉnh.

---

## 1. Tổng quan nhanh

| Thành phần | Mô tả |
|------------|--------|
| **Cấu hình** | `config/mail.php` + biến env (xem bảng dưới). |
| **Template** | Markdown trong `resources/views/emails/` (reset-password, verify-email). |
| **Xác thực email** | User implements `MustVerifyEmail`; sau khi đăng ký gửi `VerifyEmailNotification`. |
| **Reset mật khẩu** | API `POST /api/v1/password/forgot` → `PasswordResetService` → `ResetPasswordNotification` (link dùng `FRONTEND_URL`). |
| **Link reset/verify** | Reset: `FRONTEND_URL/reset-password?token=...&email=...` (config: `config('app.frontend_url')` từ `FRONTEND_URL`). |

---

## 2. Biến môi trường (.env)

### Bắt buộc / thường dùng

| Biến | Ví dụ | Ghi chú |
|------|--------|--------|
| `MAIL_MAILER` | `smtp`, `log`, `ses`, `mailgun`, `postmark`, `sendmail`, `failover`, `array` | Mặc định: `log`. |
| `MAIL_HOST` | `smtp.mailtrap.io` | SMTP host. |
| `MAIL_PORT` | `2525`, `587` | SMTP port. |
| `MAIL_USERNAME` | — | SMTP user (có thể null). |
| `MAIL_PASSWORD` | — | SMTP password (có thể null). |
| `MAIL_ENCRYPTION` | `tls`, `ssl`, `null` | Mã hóa SMTP. |
| `MAIL_FROM_ADDRESS` | `hello@example.com` | Địa chỉ người gửi. |
| `MAIL_FROM_NAME` | `${APP_NAME}` | Tên người gửi. |
| `FRONTEND_URL` | `http://localhost:3000` | Dùng cho link reset mật khẩu (đọc qua `config('app.frontend_url')`). |

### Tùy chọn

| Biến | Khi nào dùng |
|------|------------------|
| `MAIL_EHLO_DOMAIN` | SMTP server yêu cầu EHLO domain. |
| `MAIL_LOG_CHANNEL` | Khi `MAIL_MAILER=log` (channel ghi log). |
| `MAIL_SENDMAIL_PATH` | Khi `MAIL_MAILER=sendmail`. **Phải quote** nếu có khoảng trắng: `MAIL_SENDMAIL_PATH="/usr/sbin/sendmail -t -i"`. |
| `MAIL_FAILOVER_MAILERS` | Khi `MAIL_MAILER=failover` (ví dụ: `smtp,log`). |
| `POSTMARK_TOKEN` | Khi dùng Postmark. |
| `MAILGUN_DOMAIN`, `MAILGUN_SECRET`, `MAILGUN_ENDPOINT` | Khi dùng Mailgun. |
| `AWS_ACCESS_KEY_ID`, `AWS_SECRET_ACCESS_KEY`, `AWS_DEFAULT_REGION` | Khi dùng SES. |

---

## 3. Cấu hình mail (config/mail.php)

- **Default mailer:** `env('MAIL_MAILER', 'log')`.
- **From:** `MAIL_FROM_ADDRESS`, `MAIL_FROM_NAME`.
- **Markdown:** theme `default`, path `resource_path('views/emails')` (tức `resources/views/emails/`).

Các mailer: `smtp`, `ses`, `mailgun`, `postmark`, `log`, `sendmail`, `failover`, `array` — đều map từ env như trên.

---

## 4. Ví dụ cấu hình theo mailer

### Log (development, không gửi thật)

```env
MAIL_MAILER=log
```

Nội dung email được ghi vào log (channel mặc định hoặc `MAIL_LOG_CHANNEL`).

### SMTP (Mailtrap / Gmail / server SMTP)

```env
MAIL_MAILER=smtp
MAIL_HOST=smtp.mailtrap.io
MAIL_PORT=2525
MAIL_USERNAME=your_user
MAIL_PASSWORD=your_pass
MAIL_ENCRYPTION=tls
MAIL_FROM_ADDRESS="hello@example.com"
MAIL_FROM_NAME="${APP_NAME}"
```

### Sendmail

```env
MAIL_MAILER=sendmail
MAIL_SENDMAIL_PATH="/usr/sbin/sendmail -t -i"
```

Giá trị có khoảng trắng **bắt buộc** đặt trong dấu ngoặc kép.

### Amazon SES

```env
MAIL_MAILER=ses
AWS_ACCESS_KEY_ID=your_key
AWS_SECRET_ACCESS_KEY=your_secret
AWS_DEFAULT_REGION=us-east-1
```

### Mailgun

```env
MAIL_MAILER=mailgun
MAILGUN_DOMAIN=your-domain.com
MAILGUN_SECRET=your-secret
MAILGUN_ENDPOINT=api.mailgun.net
```

### Postmark

```env
MAIL_MAILER=postmark
POSTMARK_TOKEN=your-token
```

### Failover (primary + fallback)

```env
MAIL_MAILER=failover
MAIL_FAILOVER_MAILERS=smtp,log
```

---

## 5. Template email trong project

| File | Dùng cho |
|------|----------|
| `resources/views/emails/auth/reset-password.blade.php` | Link reset mật khẩu. |
| `resources/views/emails/auth/verify-email.blade.php` | Link xác thực email. |

Cả hai dùng Laravel Markdown (`@component('mail::message')`, `mail::button`). Biến truyền vào:

- **reset-password:** `$url`, `$expire` (phút), `$appName`.
- **verify-email:** `$url`, `$appName`.

Có thể chỉnh nội dung hoặc branding trực tiếp trong các file blade này.

---

## 6. Luồng xác thực email (Verify Email)

- **Model:** `App\Models\User` implements `Illuminate\Contracts\Auth\MustVerifyEmail`.
- **Notification:** `App\Notifications\VerifyEmailNotification` (extends Laravel `VerifyEmail`), dùng template `emails.auth.verify-email`.
- **Kích hoạt:** Trong `App\Services\AuthService::register()`, sau khi tạo user gọi `$user->sendEmailVerificationNotification()`.

Nếu muốn bắt buộc verify trước khi login, thêm kiểm tra trong luồng login và trả lỗi API phù hợp.

---

## 7. Luồng reset mật khẩu

### API

| Method | Endpoint | Mô tả |
|--------|----------|--------|
| POST | `/api/v1/password/forgot` | Gửi link reset vào email (body: `email`). |
| POST | `/api/v1/password/reset` | Đổi mật khẩu bằng token (body: `email`, `token`, `password`, `password_confirmation`). |

### Code path

1. **PasswordController::forgotPassword()** nhận `email` → gọi **PasswordResetService::sendResetLink($email)**.
2. **PasswordResetService** dùng `Password::sendResetLink(['email' => $email])` (Laravel).
3. Laravel tìm user theo email, gọi **User::sendPasswordResetNotification($token)**.
4. **User** gửi **App\Notifications\ResetPasswordNotification** với token.
5. **ResetPasswordNotification::resetUrl()** tạo link:  
   `rtrim(config('app.frontend_url'), '/') . "/reset-password?token={$token}&email={$email}"`  
   → Frontend nhận `token` + `email`, gọi `POST /api/v1/password/reset` để đổi mật khẩu.

`config('app.frontend_url')` lấy từ env `FRONTEND_URL` (trong `config/app.php`: `'frontend_url' => env('FRONTEND_URL', env('APP_URL', 'http://localhost'))`).

---

## 8. Gửi email tùy chỉnh

### Cách 1: Mailable (email đơn, không bắt buộc gắn User)

**Bước 1 – Tạo Mailable và view:**

```bash
php artisan make:mail WelcomeMail --markdown=emails.custom.welcome
```

**Bước 2 – Sửa Mailable (ví dụ):**

```php
namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Queue\SerializesModels;

class WelcomeMail extends Mailable
{
    use Queueable, SerializesModels;

    public function __construct(public string $userName) {}

    public function content(): Content
    {
        return new Content(
            markdown: 'emails.custom.welcome',
            with: [
                'userName' => $this->userName,
                'appName' => config('app.name'),
            ]
        );
    }
}
```

**Bước 3 – Template `resources/views/emails/custom/welcome.blade.php`:**

```blade
@component('mail::message')
# Welcome, {{ $userName }}!

Thanks for joining **{{ $appName }}**.

@component('mail::button', ['url' => config('app.frontend_url')])
Go to App
@endcomponent

Thanks,<br>{{ $appName }}
@endcomponent
```

**Bước 4 – Gửi (trong service/controller):**

```php
use App\Mail\WelcomeMail;
use Illuminate\Support\Facades\Mail;

Mail::to($user->email)->send(new WelcomeMail($user->name));

// Hoặc queue (khuyến nghị cho production):
Mail::to($user->email)->queue(new WelcomeMail($user->name));
```

### Cách 2: Notification (gắn User, nhiều kênh)

**Tạo notification:**

```bash
php artisan make:notification OrderShipped
```

Trong `toMail($notifiable)` trả về `MailMessage` (hoặc dùng markdown tùy chỉnh). Gửi: `$user->notify(new OrderShipped());`.

**Khi nào dùng:** Mailable cho email đơn, không bắt buộc có User. Notification khi gắn với user hoặc sau này cần thêm kênh (SMS, database).

### Job SendWelcomeEmailJob (stub)

`App\Jobs\SendWelcomeEmailJob` hiện chỉ log, chưa gửi email. Để gửi welcome thật:

1. Tạo Mailable (ví dụ `WelcomeMail` như trên).
2. Trong `SendWelcomeEmailJob::handle()`:

```php
use App\Mail\WelcomeMail;

Mail::to($this->user->email)->send(new WelcomeMail($this->user->name));
// hoặc ->queue(...) nếu Mailable implement ShouldQueue
```

---

## 9. Test email local

- **Chỉ xem nội dung:** `MAIL_MAILER=log` → xem log (storage/logs hoặc channel trong `MAIL_LOG_CHANNEL`).
- **Gửi thật qua SMTP:** Dùng Mailtrap hoặc SMTP local, cấu hình đủ `MAIL_*` trong `.env`.

Sau khi đổi `.env` hoặc template, nên chạy:

```bash
php artisan config:clear
php artisan view:clear
```

---

## 10. Xử lý lỗi thường gặp

| Triệu chứng | Kiểm tra |
|-------------|----------|
| Không gửi được mail | `MAIL_MAILER`, credential (SMTP/SES/Mailgun/Postmark), `config:clear`. |
| Link reset sai | `FRONTEND_URL` và `config('app.frontend_url')` (sau config:clear). |
| Thay đổi template không hiện | `php artisan view:clear`. |
| Lỗi parse .env | `MAIL_SENDMAIL_PATH` có khoảng trắng phải để trong dấu ngoặc kép. |

---

## 11. Production – checklist nhanh

- Đặt `MAIL_MAILER` thành provider production (`ses`, `mailgun`, `postmark` hoặc SMTP bảo mật).
- `MAIL_FROM_ADDRESS` thuộc domain đã verify; cấu hình SPF/DKIM/DMARC.
- Dùng `MAIL_ENCRYPTION=tls` (hoặc theo hướng dẫn provider).
- Gửi email qua queue (`ShouldQueue`) cho đường hot; chạy queue worker có retry/backoff.
- Dùng `MAIL_FAILOVER_MAILERS` nếu mailer là `failover`.
- Không commit secret vào git; dùng secret manager.
- Set `FRONTEND_URL` ổn định cho link reset/verify.
- Giám sát bounce/reject; tránh gửi trùng (idempotency) khi có thể.

---

## 12. Lệnh hữu ích

```bash
# Xóa cache cấu hình
php artisan config:clear

# Xóa cache view (template)
php artisan view:clear

# Test gửi reset link (Tinker)
php artisan tinker
>>> Password::sendResetLink(['email' => 'user@example.com']);
```

---

**Tài liệu liên quan:** [CONFIG_ENVIRONMENT.md](CONFIG_ENVIRONMENT.md), [CONFIG_ENV.example](CONFIG_ENV.example), `.env.example` ở root.
