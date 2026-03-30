# Authentication and Authorization

The project uses:

- Laravel Sanctum for API token authentication
- Spatie Permission for role/permission-based authorization
- Route-level throttling on sensitive endpoints

## Base path

All API auth routes below use `/api/v1`.

## Public auth endpoints

- `POST /register`
- `POST /login`
- `POST /login/google`
- `POST /password/forgot`
- `POST /password/reset`

## Protected auth endpoints (`auth:sanctum`)

- `POST /logout`
- `POST /refresh`
- `GET /me`
- `POST /password/change`

## Authorization model

- Roles and permissions are managed via Spatie Permission.
- User-management endpoints are protected by `permission:manage users`.
- Role/permission-management endpoints are protected by `permission:manage roles`.

## Seeded sample accounts

After running `RolePermissionSeeder`:

- `admin@example.com` / `password`
- `user@example.com` / `password`

## Related docs

- [API_RESPONSE_AND_ERRORS.md](API_RESPONSE_AND_ERRORS.md)
- [WEB_GOOGLE_LOGIN.md](WEB_GOOGLE_LOGIN.md)
