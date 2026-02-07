# Email Usage Guide

This document describes how email is configured and used in the project, including templates, verification, password reset, and production operations.

## 1) Environment Configuration

All mail settings are driven by environment variables (see `.env` and `.env.example`):

Required:
- `MAIL_MAILER` (e.g. `smtp`, `ses`, `mailgun`, `postmark`, `sendmail`, `log`, `array`, `failover`)
- `MAIL_HOST`
- `MAIL_PORT`
- `MAIL_USERNAME`
- `MAIL_PASSWORD`
- `MAIL_ENCRYPTION` (`tls` or `ssl`)
- `MAIL_FROM_ADDRESS`
- `MAIL_FROM_NAME`

Optional:
- `MAIL_EHLO_DOMAIN` (set if your SMTP server requires a specific EHLO)
- `MAIL_LOG_CHANNEL` (only used when `MAIL_MAILER=log`)
- `MAIL_SENDMAIL_PATH` (only used when `MAIL_MAILER=sendmail`)
- `MAIL_FAILOVER_MAILERS` (comma list when `MAIL_MAILER=failover`)
- `POSTMARK_TOKEN` (Postmark)
- `MAILGUN_DOMAIN`, `MAILGUN_SECRET`, `MAILGUN_ENDPOINT` (Mailgun)
- `AWS_ACCESS_KEY_ID`, `AWS_SECRET_ACCESS_KEY`, `AWS_DEFAULT_REGION` (SES)

Frontend URL for email actions:
- `FRONTEND_URL` (used to generate reset-password URLs)

## 2) Mail Configuration File

`config/mail.php` is included in the project and uses the env values above.

Default mailer:
- `MAIL_MAILER` defaults to `log` if not set.

## 3) Supported Mailers (Common)

### SMTP (Mailtrap / Gmail / SMTP Server)
```
MAIL_MAILER=smtp
MAIL_HOST=smtp.mailtrap.io
MAIL_PORT=2525
MAIL_USERNAME=your_user
MAIL_PASSWORD=your_pass
MAIL_ENCRYPTION=tls
```

### Amazon SES
```
MAIL_MAILER=ses
AWS_ACCESS_KEY_ID=your_key
AWS_SECRET_ACCESS_KEY=your_secret
AWS_DEFAULT_REGION=us-east-1
```

### Mailgun
```
MAIL_MAILER=mailgun
MAILGUN_DOMAIN=your-domain.com
MAILGUN_SECRET=your-secret
MAILGUN_ENDPOINT=api.mailgun.net
```

### Postmark
```
MAIL_MAILER=postmark
POSTMARK_TOKEN=your-token
```

### Sendmail
```
MAIL_MAILER=sendmail
MAIL_SENDMAIL_PATH=/usr/sbin/sendmail -t -i
```

### Failover (primary + fallback)
```
MAIL_MAILER=failover
MAIL_FAILOVER_MAILERS=smtp,log
```

Markdown templates:
- Paths are configured to `resources/views/emails`.

## 4) Email Templates

Markdown templates are used for standard flows:
- Password reset: `resources/views/emails/auth/reset-password.blade.php`
- Email verification: `resources/views/emails/auth/verify-email.blade.php`

You can customize the copy or branding directly in these templates.

## 5) Email Verification Flow

Implementation:
- User model implements `MustVerifyEmail`
- Notification: `app/Notifications/VerifyEmailNotification.php`
- Trigger: after successful register (see `App\Services\AuthService::register`)

If you want to force verification before login, add a check in the login flow and return a proper error response.

## 6) Password Reset Flow

Implementation:
- Notification: `app/Notifications/ResetPasswordNotification.php`
- Service: `App\Services\PasswordResetService`
- Controller: `App\Http\Controllers\Api\V1\PasswordController`

Reset URL:
- Built using `FRONTEND_URL` with `/reset-password?token=...&email=...`

## 7) Testing Email Locally

Use a safe local strategy during development:

Option A: Log driver
- Set `MAIL_MAILER=log`
- Check logs to see the email payload

Option B: Mailtrap (SMTP)
- Set SMTP credentials in `.env`

## 8) Sending Custom Emails

You can send custom email using either **Mailable** (recommended for simple, standalone emails) or **Notification** (recommended when tied to a User or when you need multiple channels).

### Option A: Mailable (simple, direct)

1) Create a mailable:
```
php artisan make:mail WelcomeMail --markdown=emails.custom.welcome
```

2) Edit the class (example):
```
public function __construct(public string $userName) {}

public function build(): self
{
    return $this->subject('Welcome to ' . config('app.name'))
        ->markdown('emails.custom.welcome', [
            'userName' => $this->userName,
            'appName' => config('app.name'),
        ]);
}
```

3) Create the template:
`resources/views/emails/custom/welcome.blade.php`
```
@component('mail::message')
# Welcome, {{ $userName }}!

Thanks for joining **{{ $appName }}**.

@component('mail::button', ['url' => config('app.frontend_url')])
Go to App
@endcomponent

Thanks,
{{ $appName }}
@endcomponent
```

4) Send the email (example in a service/controller):
```
use App\Mail\WelcomeMail;
use Illuminate\Support\Facades\Mail;

Mail::to($user->email)->send(new WelcomeMail($user->name));
```

### Option B: Notification (user-centric, flexible)

1) Create a notification:
```
php artisan make:notification OrderShipped
```

2) Use the `toMail` method:
```
public function toMail($notifiable)
{
    return (new MailMessage)
        ->subject('Your order has shipped')
        ->markdown('emails.custom.order-shipped', [
            'name' => $notifiable->name,
        ]);
}
```

3) Send via user:
```
$user->notify(new OrderShipped());
```

### When to use which?
- **Mailable**: one-off emails not tied to a User model.
- **Notification**: user-centric emails, or when you may add SMS/DB channels later.

## 9) Common Troubleshooting

- If emails are not sending, check `MAIL_MAILER` and provider credentials.
- If emails are not sending, run `php artisan config:clear` and retry.
- If reset links are wrong, verify `FRONTEND_URL`.
- If template changes are not visible, run `php artisan view:clear`.

## 10) Production Best-Practice Checklist

- Set `MAIL_MAILER` to a production provider (`ses`, `mailgun`, `postmark`, or hardened `smtp`)
- Keep `MAIL_FROM_ADDRESS` on a verified domain
- Configure SPF, DKIM, and DMARC for sender domain
- Keep `MAIL_ENCRYPTION=tls` (or provider-secured transport)
- Do not send emails synchronously for high traffic paths
- Queue mail jobs (`ShouldQueue`) and run workers with retry/backoff policy
- Configure `MAIL_FAILOVER_MAILERS` when using `failover` transport
- Store provider secrets in a secret manager, not in git
- Set a stable `FRONTEND_URL` for reset/verify links
- Add monitoring for bounce, reject, and deferred events
- Add idempotency for repeated send triggers (avoid duplicate emails)
- Validate templates after every release (links, placeholders, branding)

## 11) Useful Commands

Clear config cache:
```
php artisan config:clear
```

Clear view cache:
```
php artisan view:clear
```

Test password reset flow:
```
php artisan tinker
Password::sendResetLink(['email' => 'user@example.com']);
```
