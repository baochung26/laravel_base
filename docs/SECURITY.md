# Security Overview

This base project includes practical security defaults for API-first Laravel apps.

## Included protections

- Security headers middleware
- Rate limiting for public and auth-sensitive routes
- Email verification and password reset flows
- Request ID support for traceability

## Security headers

Configured through `config/security.php` and environment variables.

Common settings include:

- `SECURITY_HEADERS_ENABLED`
- `SECURITY_HSTS_ENABLED`
- `SECURITY_X_FRAME_OPTIONS`
- `SECURITY_X_CONTENT_TYPE_OPTIONS`
- `SECURITY_REFERRER_POLICY`
- `SECURITY_CSP` (optional)

## Production checklist

- Set `APP_ENV=production` and `APP_DEBUG=false`
- Enforce HTTPS and enable HSTS
- Keep CORS allowlist strict
- Apply appropriate throttling to auth endpoints
- Use managed secrets, never commit real `.env` values
- Enable log monitoring for auth failures and 429 spikes

## Key files

- `app/Http/Middleware/SecurityHeaders.php`
- `app/Providers/RouteServiceProvider.php`
- `config/security.php`
