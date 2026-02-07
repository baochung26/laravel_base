# Project Evaluation

**Đánh giá toàn diện Project Base Laravel**

**Date:** 2026-02-07  
**Scope:** Full Laravel API base codebase (backend for Next.js frontend).  
**Based on:** routes, middleware, auth flow, config, docs, tests, security, performance.

---

## 1. Overview

| Criteria       | Score   | Notes |
|----------------|--------|--------|
| **Overall**    | **7.9/10** | Solid architecture; a few items to address before scale/production. |
| Architecture   | 9/10   | Repository-Service-Controller, DTO, clear layering. |
| API & Response | 9/10   | Standard response/error, correlation/request id, versioning. |
| Auth & RBAC    | 8/10   | Sanctum + Spatie, refresh token correct; CORS/env improved. |
| Security       | 7/10   | Good rate limiting; CORS and env parsing fixed. |
| Performance    | 7/10   | Permission cache and pagination fixed; see remaining items. |
| Testing        | 6/10   | Auth + Health covered; missing User/Role/File/Password edge cases. |
| Documentation  | 9/10   | Docs comprehensive; fix README link to removed ARCHITECTURE.md. |

**Summary:** Base is in good shape for development and staging. Before production, address remaining P1/P2 items and follow the improvement roadmap below.

---

## 2. Strengths

### 2.1 Architecture and layering

- **Repository-Service-Controller** clearly applied:
  - `app/Services/UserService.php`, `app/Repositories/Eloquent/UserRepository.php`, `app/DTOs/UserDTO.php`
- **DTOs** for type-safe data transfer between layers.
- **Custom exceptions** and unified handling in `app/Exceptions/Handler.php`.

### 2.2 API response standardisation

- `app/Support/ApiResponse.php`: consistent success/error format, `request_id`, `timestamp`.
- Headers `X-Request-ID`, `X-Correlation-ID` for tracing.
- Doc: `docs/API_RESPONSE_FORMAT.md`.

### 2.3 Rate limiting

- `app/Providers/RouteServiceProvider.php`: limits per group (api, login, register, password-reset, api-public).
- Config via env: `API_RATE_LIMIT_AUTHENTICATED`, `API_RATE_LIMIT_GUEST`.

### 2.4 Auth and RBAC

- **Sanctum** for API tokens; **Spatie Permission** for roles/permissions.
- Refresh token: only `refresh_token` name accepted; rotate (revoke old, issue new). Logout revokes all tokens.
- Routes: `routes/api/v1/routes.php`; middleware: `CheckRole`, `CheckPermission`.

### 2.5 Health check

- `app/Http/Controllers/Api/V1/HealthController.php`: separate **liveness** and **readiness** (DB), suitable for K8s/deploy.

### 2.6 Basic tests

- Feature tests: `AuthApiV1Test.php`, `HealthApiV1Test.php`.
- Auth: register, login, me, logout, refresh (refresh token required), Google login.

### 2.7 Infrastructure and docs

- Docker (Nginx, PHP, MySQL), Makefile, Redis/Queue config documented.
- Many docs in `docs/`: API, versioning, cache, queue, logging, file storage, database, security.

---

## 3. Issues (by priority)

### P0 – Address soon

#### 3.1 Parse `.env` / `.env.example` (dotenv) ✅ Fixed

- **Issue:** `MAIL_SENDMAIL_PATH=/usr/sbin/sendmail -t -i` had spaces without quotes → dotenv parse errors.
- **Fix:** Value quoted in `.env` and `.env.example`: `MAIL_SENDMAIL_PATH="/usr/sbin/sendmail -t -i"`.

#### 3.2 Duplicate / conflicting Redis in `.env.example` ✅ Fixed

- **Issue:** Two Redis blocks (host vs Docker) with same keys; second overwrote first.
- **Fix:** Single Redis block; comment: Docker use `REDIS_HOST=redis`, local use `REDIS_HOST=127.0.0.1`.

### P1 – Before higher traffic / production

#### 3.3 Permission cache cleared every request ✅ Fixed

- **Issue:** `AppServiceProvider` called `forgetCachedPermissions()` on every web request → cache useless, extra load.
- **Fix:** Removed that call; Spatie clears cache when roles/permissions change via its API.

#### 3.4 CORS: `allowed_origins` and `supports_credentials` ✅ Fixed

- **Issue:** Default `*` with credentials invalid in browsers; production needed explicit origins.
- **Fix:** In `config/cors.php`: no default `*`; production empty origins if unset; local default localhost list; when origin is `*`, `supports_credentials = false`.

#### 3.5 Inconsistent pagination – GET /users ✅ Fixed

- **Issue:** With `search` → paginated; without → full collection (memory/response size risk).
- **Fix:** `GET /users` always paginated (`meta`, `links`); `per_page`/`page`; uses `UserService::paginate()` or `search()`.

### P2 – Quality / scale

#### 3.6 Avatar/file URL in Resource ✅ Fixed

- **Issue:** `BaseResource::storageUrl()` used hardcoded `url('storage/...')`, not disk-aware (S3/CloudFront).
- **Fix:** `storageUrl($path, $disk = null)` uses `Storage::disk($disk)->url($path)`; `UserResource` passes `config('constants.uploads.avatar_disk')`.

#### 3.7 Middleware: Kernel vs bootstrap

- **Issue:** Both `app/Http/Kernel.php` and `bootstrap/app.php` configure middleware → confusing.
- **Suggestion:** Use one place (Laravel 11+ typically `bootstrap/app.php`); document in docs or comments.

#### 3.8 Test coverage gaps

- **Missing:** User CRUD/avatar, role/permission endpoints, file upload/list/delete, password reset edge cases and rate limit.
- **Suggestion:** Add feature tests for these; optionally unit tests for key services/repositories.

#### 3.9 Broken doc link ✅ Fixed

- **Issue:** `README.md` linked to removed `docs/ARCHITECTURE.md` and had duplicate doc links.
- **Fixed:** README doc section rewritten: single table by topic, link to [docs/README.md](README.md) as index. No reference to ARCHITECTURE.md. Docs folder has [docs/README.md](README.md) as entry point with “How to use” and full index.

---

## 4. Improvement roadmap

### Phase 1 (1–2 days) – Immediate ✅ Done

1. ~~Parse .env~~ – Quote `MAIL_SENDMAIL_PATH` in `.env` and `.env.example`.
2. ~~Permission cache~~ – Do not call `forgetCachedPermissions()` on every request.
3. ~~.env.example Redis~~ – Single Redis block; comment Docker vs local.

### Phase 2 (2–4 days) – Before scale/production ✅ Done

1. ~~Pagination~~ – `GET /users` always paginated.
2. ~~CORS~~ – Explicit origins for production; `supports_credentials` when using `*`.
3. ~~Storage URL~~ – `BaseResource::storageUrl()` uses disk config and `Storage::disk()->url()`.

### Phase 3 (ongoing)

1. **Tests** – Feature tests: User, Role/Permission, File, Password reset (+ rate limit). Optional unit tests.
2. **Static analysis & CI** – PHPStan/Psalm, Pint, tests in pipeline (e.g. GitHub Actions).
3. **Docs** – Fix README link to ARCHITECTURE.md; check links in `docs/`.

---

## 5. Backend API + Next.js notes

1. **Auth** – Different domains → prefer Bearer token. If cookie/Sanctum stateful: CSRF, same-site, secure cookies, CORS credentials.
2. **Contract** – Keep response/error schema stable (`ApiResponse`). Use `/api/v1` versioning; document breaking changes.

---

## 6. Checklist

- [x] `.env` / `.env.example`: Quote `MAIL_SENDMAIL_PATH`, single Redis block.
- [x] AppServiceProvider: No `forgetCachedPermissions()` every request.
- [x] CORS: Explicit origins; `supports_credentials` when `*`.
- [x] GET /users: Always paginated, `meta` + `links`.
- [x] BaseResource::storageUrl(): Disk config + `Storage::disk()->url()`.
- [x] README / docs: Fix ARCHITECTURE.md link; add docs/README.md index; tidy doc list.
- [ ] Tests: Feature (User, Role/Permission, File, Password); optional Unit + CI.

---

## 7. Related docs

- API response: `docs/API_RESPONSE_FORMAT.md`
- Auth: `docs/AUTHENTICATION.md`, `docs/AUTH_CHECKLIST.md`
- Previous audits: `docs/PROJECT_AUDIT_2026-02-07.md`, `docs/PROJECT_AUDIT.md`

This file (`docs/PROJECT_EVALUATION.md`) is the consolidated evaluation; work through items in priority order above.
