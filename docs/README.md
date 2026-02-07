# Project Documentation

Documentation for the Laravel API base project. All API examples use the **`/api/v1`** prefix.

---

## How to use this docs folder

| Goal | Start here |
|------|------------|
| Run the project locally | [QUICK_START.md](QUICK_START.md) |
| Call the API (auth, users, files) | [API_FOUNDATION.md](API_FOUNDATION.md), [AUTHENTICATION.md](AUTHENTICATION.md), [USER_MODULE.md](USER_MODULE.md) |
| Response/error format | [API_RESPONSE_FORMAT.md](API_RESPONSE_FORMAT.md) |
| API versioning & breaking changes | [API_VERSIONING_STRATEGY.md](API_VERSIONING_STRATEGY.md) |
| Health checks (K8s, monitoring) | [HEALTH_CHECK.md](HEALTH_CHECK.md) |
| Security, CORS, rate limits | [SECURITY.md](SECURITY.md), [AUTHENTICATION.md](AUTHENTICATION.md) |
| Config, env, cache, queue | [CONFIG_ENVIRONMENT.md](CONFIG_ENVIRONMENT.md), [CACHE_STRATEGY.md](CACHE_STRATEGY.md), [QUEUE_SCHEDULER.md](QUEUE_SCHEDULER.md) |
| Database, migrations, queries | [DATABASE_CONVENTIONS.md](DATABASE_CONVENTIONS.md), [QUERY_OPTIMIZATION.md](QUERY_OPTIMIZATION.md) |
| Files, storage, S3 | [FILE_STORAGE.md](FILE_STORAGE.md) |
| Logging, exceptions | [LOGGING.md](LOGGING.md), [EXCEPTION_HANDLING.md](EXCEPTION_HANDLING.md) |
| Project evaluation & roadmap | [PROJECT_EVALUATION.md](PROJECT_EVALUATION.md) |

---

## Document index (by topic)

### Getting started

- **[QUICK_START.md](QUICK_START.md)** – Run with Docker, Makefile, first migrations and seeders.

### API

- **[API_FOUNDATION.md](API_FOUNDATION.md)** – Versioning (`/api/v1`), response format, pagination, resources.
- **[API_RESPONSE_FORMAT.md](API_RESPONSE_FORMAT.md)** – Exact JSON shape for success/error/paginated.
- **[API_VERSIONING_STRATEGY.md](API_VERSIONING_STRATEGY.md)** – Version lifecycle, deprecation policy.

### Auth & users

- **[AUTHENTICATION.md](AUTHENTICATION.md)** – Register, login, logout, refresh token, Google login, RBAC.
- **[AUTH_CHECKLIST.md](AUTH_CHECKLIST.md)** – Checklist of auth features.
- **[USER_MODULE.md](USER_MODULE.md)** – User CRUD, profile, password, avatar (all under `/api/v1`).

### Infrastructure & config

- **[CONFIG_ENVIRONMENT.md](CONFIG_ENVIRONMENT.md)** – Environment variables, cache, queue, mail, files.
- **[CONFIG_ENV.example](CONFIG_ENV.example)** – Extended env template (optional).
- **[REDIS_SETUP.md](REDIS_SETUP.md)** – Redis in Docker.
- **[HEALTH_CHECK.md](HEALTH_CHECK.md)** – `/api/v1/health`, liveness, readiness.

### Data & storage

- **[DATABASE_CONVENTIONS.md](DATABASE_CONVENTIONS.md)** – Migrations, soft deletes, indexes.
- **[QUERY_OPTIMIZATION.md](QUERY_OPTIMIZATION.md)** – Eager loading, indexing, performance.
- **[CACHE_STRATEGY.md](CACHE_STRATEGY.md)** – Cache keys, invalidation.
- **[FILE_STORAGE.md](FILE_STORAGE.md)** – Local, S3, public/private files.
- **[EMAIL_USAGE.md](EMAIL_USAGE.md)** – Mail config and usage.

### Reliability & operations

- **[LOGGING.md](LOGGING.md)** – JSON logs, request ID, slow queries.
- **[EXCEPTION_HANDLING.md](EXCEPTION_HANDLING.md)** – Standardized error responses.
- **[QUEUE_SCHEDULER.md](QUEUE_SCHEDULER.md)** – Queues, jobs, scheduler.
- **[SECURITY.md](SECURITY.md)** – Headers, CORS, best practices.

### Reference & evaluation

- **[PROJECT_AUDIT.md](PROJECT_AUDIT.md)** – Legacy audit report.
- **[PROJECT_EVALUATION.md](PROJECT_EVALUATION.md)** – Current evaluation, strengths, issues, roadmap.

---

## Quick API examples (base URL: `http://localhost:8000/api/v1`)

```bash
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

All responses follow the format in [API_RESPONSE_FORMAT.md](API_RESPONSE_FORMAT.md) (`success`, `message`, `data`, `meta`, `errors`).
