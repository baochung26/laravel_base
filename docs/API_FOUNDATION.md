# API Foundation

This project exposes a versioned REST API under `/api/v1`.

## Base URL

- Local: `http://localhost:8000/api/v1`

## Public endpoints

- `GET /docs` - Swagger UI
- `GET /openapi.yaml` - OpenAPI spec
- `GET /health` - Basic health check
- `GET /health/live` - Liveness probe
- `GET /health/ready` - Readiness probe (dependencies)
- `POST /register`
- `POST /login`
- `POST /login/google`
- `POST /password/forgot`
- `POST /password/reset`

## Protected endpoints (`auth:sanctum`)

- `POST /logout`
- `POST /refresh`
- `GET /me`
- `GET|PUT /profile`
- `POST /profile/avatar`
- `DELETE /profile/avatar`
- `POST /password/change`

Admin-oriented groups:

- `/users/*` with `permission:manage users`
- `/roles-permissions/*` with `permission:manage roles`

File operations:

- `/files/upload`
- `/files/upload-multiple`
- `/files/download`
- `/files` (DELETE)
- `/files/list`
- `/files/stats`

## Versioning strategy

- Current version: `v1`
- API routes are loaded from `routes/api/v1/routes.php`
- New major contract changes should be introduced in a new version namespace (for example, `v2`) instead of breaking `v1`

## Related docs

- [API_RESPONSE_AND_ERRORS.md](API_RESPONSE_AND_ERRORS.md)
- [AUTHENTICATION.md](AUTHENTICATION.md)
- [SWAGGER_USAGE.md](SWAGGER_USAGE.md)
