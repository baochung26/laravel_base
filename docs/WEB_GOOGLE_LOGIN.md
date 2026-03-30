# Web Google Login (Session-Based)

This document describes the web login flow using Google Identity Services (GIS).

## Flow summary

1. User opens `/login`.
2. Frontend loads GIS and receives an `id_token`.
3. Frontend submits token to `POST /login/google`.
4. Backend verifies token with Google (`oauth2.googleapis.com/tokeninfo`).
5. Backend finds/creates user, then signs in with `web` guard.
6. Session is regenerated and user is redirected to dashboard.

## Main files

- `resources/views/auth/login.blade.php`
- `resources/js/pages/auth.js`
- `app/Http/Controllers/Web/Auth/AuthenticatedSessionController.php`
- `app/Http/Requests/Web/Auth/GoogleLoginRequest.php`
- `app/Services/GoogleAuthService.php`
- `app/Services/GoogleTokenVerifier.php`
- `config/services.php`

## Setup

1. Create a Google OAuth Client ID (Web application).
2. Add your login origin to Google authorized JavaScript origins.
3. Set `GOOGLE_CLIENT_ID` in `.env`.
4. Clear config cache if needed:

```bash
php artisan config:clear
```

## Notes

- If `GOOGLE_CLIENT_ID` is empty, Google login UI is hidden/disabled.
- Backend must have outbound access to Google token verification endpoint.
