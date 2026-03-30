# Project Documentation

Documentation for the Laravel API base project. All API examples use the **`/api/v1`** prefix.

---

## How to use this docs folder

| Goal | Start here |
|------|------------|
| Run the project locally | [QUICK_START.md](QUICK_START.md) |
| Swagger / OpenAPI docs | [SWAGGER_USAGE.md](SWAGGER_USAGE.md) |
| Call the API (auth, users, files) | [API_FOUNDATION.md](API_FOUNDATION.md), [AUTHENTICATION.md](AUTHENTICATION.md) |
| Response/error format | [API_RESPONSE_AND_ERRORS.md](API_RESPONSE_AND_ERRORS.md) |
| API versioning & breaking changes | [API_VERSIONING_STRATEGY.md](API_VERSIONING_STRATEGY.md) |
| Health checks (K8s, monitoring) | [HEALTH_CHECK.md](HEALTH_CHECK.md) |
| Security, CORS, rate limits | [SECURITY.md](SECURITY.md), [AUTHENTICATION.md](AUTHENTICATION.md) |
| Google login web (session) | [WEB_GOOGLE_LOGIN.md](WEB_GOOGLE_LOGIN.md) |
| Config, env, cache, queue | [CONFIG_ENVIRONMENT.md](CONFIG_ENVIRONMENT.md), [CACHE_STRATEGY.md](CACHE_STRATEGY.md), [QUEUE_SCHEDULER.md](QUEUE_SCHEDULER.md) |
| Database, migrations, queries | [DATABASE_CONVENTIONS.md](DATABASE_CONVENTIONS.md), [QUERY_OPTIMIZATION.md](QUERY_OPTIMIZATION.md) |
| Files, storage, S3 | [FILE_STORAGE.md](FILE_STORAGE.md) |
| Logging, exceptions | [LOGGING.md](LOGGING.md), [API_RESPONSE_AND_ERRORS.md](API_RESPONSE_AND_ERRORS.md) |
| Web template/layout | [WEB_TEMPLATE_STRUCTURE.md](WEB_TEMPLATE_STRUCTURE.md), [UI_LAYOUT_VITE_GUIDE.md](UI_LAYOUT_VITE_GUIDE.md) |

---

## Document index (by topic)

### Getting started

- **[QUICK_START.md](QUICK_START.md)** – Run with Docker, Makefile, first migrations and seeders.

### API

- **[SWAGGER_USAGE.md](SWAGGER_USAGE.md)** – Cách dùng Swagger UI (`/api/v1/docs`) và OpenAPI spec (`/api/v1/openapi.yaml`).
- **[API_FOUNDATION.md](API_FOUNDATION.md)** – Versioning (`/api/v1`), response format, pagination, resources.
- **[API_RESPONSE_AND_ERRORS.md](API_RESPONSE_AND_ERRORS.md)** – Response format (success/error/paginated) and exception handling.
- **[API_VERSIONING_STRATEGY.md](API_VERSIONING_STRATEGY.md)** – Version lifecycle, deprecation policy.

### Auth & users

- **[AUTHENTICATION.md](AUTHENTICATION.md)** – Register, login, logout, refresh token, Google login, RBAC.
- **[AUTH_CHECKLIST.md](AUTH_CHECKLIST.md)** – Checklist of auth features.
- **[WEB_GOOGLE_LOGIN.md](WEB_GOOGLE_LOGIN.md)** – Luồng đăng nhập Google cho web (session) và hướng dẫn setup.

### Infrastructure & config

- **[CONFIG_ENVIRONMENT.md](CONFIG_ENVIRONMENT.md)** – Environment variables, cache, queue, mail, files.
- **[CONFIG_ENV.example](CONFIG_ENV.example)** – Extended env template (optional).
- **[REDIS_SETUP.md](REDIS_SETUP.md)** – Redis in Docker.
- **[HEALTH_CHECK.md](HEALTH_CHECK.md)** – `/api/v1/health`, liveness, readiness.

### Data & storage

- **[DATABASE_CONVENTIONS.md](DATABASE_CONVENTIONS.md)** – Migrations, soft deletes, indexes.
- **[QUERY_OPTIMIZATION.md](QUERY_OPTIMIZATION.md)** – Eager loading, indexing, performance.
- **[CACHE_STRATEGY.md](CACHE_STRATEGY.md)** – Cache keys, invalidation.
- **[FILE_STORAGE.md](FILE_STORAGE.md)** – Làm việc với file: service/helper (FileManagerService, StorageService, FileHelper, StoragePath), API, demo đầy đủ (tiếng Việt).
- **[EMAIL_USAGE.md](EMAIL_USAGE.md)** – Mail config and usage.

### Reliability & operations

- **[LOGGING.md](LOGGING.md)** – JSON logs, request ID, slow queries.
- **[QUEUE_SCHEDULER.md](QUEUE_SCHEDULER.md)** – Queues, jobs, scheduler.
- **[SECURITY.md](SECURITY.md)** – Headers, CORS, best practices.

### Web UI

- **[WEB_TEMPLATE_STRUCTURE.md](WEB_TEMPLATE_STRUCTURE.md)** – Cấu trúc layout/partials và cách quản lý CSS cho landing page.
- **[UI_LAYOUT_VITE_GUIDE.md](UI_LAYOUT_VITE_GUIDE.md)** – Hướng dẫn chi tiết UI layout + Vite + Docker workflow.

---

## Quick API examples (base URL: `http://localhost:8000/api/v1`)

```bash
# Swagger UI: http://localhost:8000/api/v1/docs

# Health
curl http://localhost:8000/api/v1/health

# Register
curl -X POST http://localhost:8000/api/v1/register \
  -H "Content-Type: application/json" \
  -d '{"name":"Demo","email":"demo@example.com","password":"password123","password_confirmation":"password123"}'

# Login
curl -X POST http://localhost:8000/api/v1/login \
  -H "Content-Type: application/json" \
  -d '{"email":"demo@example.com","password":"password123"}'

# Me (use token from login)
curl http://localhost:8000/api/v1/me -H "Authorization: Bearer YOUR_TOKEN"
```

All responses follow the format in [API_RESPONSE_AND_ERRORS.md](API_RESPONSE_AND_ERRORS.md) (`success`, `message`, `data`, `meta`, `errors`).
