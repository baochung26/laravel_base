# Security Module

This project includes a lightweight security module suitable for a base API project. It focuses on:
- Security headers
- API rate limiting (already configured)
- Email verification / password reset flow
- Recommended operational checks

## 1) Security Headers

Security headers are enabled globally via middleware:
- `App\Http\Middleware\SecurityHeaders`
- Registered in `app/Http/Kernel.php`

Configure headers in `config/security.php` and `.env`:

```
SECURITY_HEADERS_ENABLED=true
# SECURITY_CSP=default-src 'self';
SECURITY_HSTS_ENABLED=false
SECURITY_HSTS_MAX_AGE=31536000
SECURITY_HSTS_INCLUDE_SUBDOMAINS=true
SECURITY_HSTS_PRELOAD=false
SECURITY_X_FRAME_OPTIONS=SAMEORIGIN
SECURITY_X_CONTENT_TYPE_OPTIONS=nosniff
SECURITY_REFERRER_POLICY=strict-origin-when-cross-origin
# SECURITY_PERMISSIONS_POLICY=geolocation=(), microphone=()
```

Notes:
- HSTS is only applied on HTTPS responses.
- CSP is optional. Start in report-only mode in production before enforcing.

## 2) Rate Limiting

Rate limiters are configured in `app/Providers/RouteServiceProvider.php`:
- `api` (auth vs guest)
- `login`
- `register`
- `password-reset`
- `api-public`

Use the `throttle` middleware in routes when needed.

## 3) Email Verification & Password Reset

Implemented with custom notifications:
- `App\Notifications\VerifyEmailNotification`
- `App\Notifications\ResetPasswordNotification`

Templates:
- `resources/views/emails/auth/verify-email.blade.php`
- `resources/views/emails/auth/reset-password.blade.php`

## 4) Operational Checklist

- Always run `php artisan config:clear` after changing `.env`.
- Keep `APP_KEY` set in production.
- Use HTTPS and enable HSTS in production.
- Use a real mail provider (SES/Mailgun/Postmark).
- Keep `SECURITY_CSP` minimal and tighten gradually.

## 5) Production Best-Practice Checklist

- `APP_ENV=production` and `APP_DEBUG=false`
- `APP_KEY` is set and never rotated without a key-rotation plan
- Enforce HTTPS at LB/reverse proxy and set `SECURITY_HSTS_ENABLED=true`
- Set a minimal CSP first, validate, then tighten (`SECURITY_CSP=...`)
- Keep `SECURITY_X_FRAME_OPTIONS=SAMEORIGIN` (or stricter `DENY` if UI allows)
- Keep `SECURITY_X_CONTENT_TYPE_OPTIONS=nosniff`
- Keep `SECURITY_REFERRER_POLICY=strict-origin-when-cross-origin` or stricter
- Configure rate limits per traffic profile in `app/Providers/RouteServiceProvider.php`
- Ensure auth endpoints (`login`, `register`, `password-reset`) are throttled
- Configure logging/monitoring for 401/403/429 spikes and failed auth bursts
- Use managed secrets for `.env` values (do not commit production secrets)
- Run `php artisan config:cache` on deploy after validating configuration
- Run `php artisan route:cache` in production if routes are cache-safe
- Review CORS allowlist (no wildcard origins when credentials are enabled)
- Verify backup/restore and incident response runbook exist

## 6) Testing

Clear config cache:
```
php artisan config:clear
```

Inspect headers (example):
```
curl -I http://localhost:8000/api/v1/health
```
