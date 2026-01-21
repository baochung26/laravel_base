# API Versioning Strategy & Deprecation Policy

Tài liệu về chiến lược versioning API và deprecation policy để đảm bảo backward compatibility và smooth transitions.

## 📋 Tổng quan

API Versioning Strategy bao gồm:
- ✅ **Versioning Approach** - URL-based versioning (`/api/v1/`, `/api/v2/`)
- ✅ **Version Lifecycle** - Lifecycle của mỗi API version
- ✅ **Deprecation Policy** - Quy trình deprecate và remove API versions
- ✅ **Migration Guide** - Hướng dẫn migrate giữa các versions
- ✅ **Best Practices** - Best practices cho API versioning

## 🔢 1. Versioning Approach

### URL-Based Versioning

API sử dụng **URL-based versioning** với format:
```
/api/{version}/{resource}
```

**Examples:**
- `/api/v1/users` - Version 1
- `/api/v2/users` - Version 2 (future)
- `/api/v1/auth/login` - Version 1 authentication

### Version Format

- **Format:** `v{number}` (e.g., `v1`, `v2`, `v3`)
- **Current Version:** `v1`
- **Version Numbering:** Sequential integers starting from 1

### Why URL-Based Versioning?

✅ **Advantages:**
- Clear and explicit version in URL
- Easy to route to different controllers
- Simple to understand for developers
- Allows multiple versions to coexist
- Easy to deprecate old versions

❌ **Alternatives Considered:**
- Header-based versioning (less visible, harder to debug)
- Query parameter versioning (less RESTful)
- Subdomain versioning (more complex infrastructure)

## 📁 2. Project Structure

### Directory Structure

```
app/
├── Http/
│   └── Controllers/
│       └── Api/
│           ├── V1/
│           │   ├── Auth/
│           │   │   └── AuthController.php
│           │   ├── UserController.php
│           │   ├── ProfileController.php
│           │   └── ...
│           └── V2/  # Future version
│               └── ...
routes/
├── api.php          # Legacy routes (if needed)
└── api/
    ├── v1.php       # API V1 routes
    └── v2.php       # API V2 routes (future)
```

### Route Registration

**Current Implementation (Laravel 12):**

Routes được register trong `bootstrap/app.php` hoặc `routes/api/v1.php`:

```php
// routes/api/v1.php
Route::prefix('v1')->group(function () {
    Route::get('/users', [V1UserController::class, 'index']);
    // ...
});
```

**Route Prefix:**
- V1 routes: `/api/v1/*`
- V2 routes: `/api/v2/*` (future)

## 🔄 3. Version Lifecycle

### Version Lifecycle Stages

1. **Development** - Version đang được phát triển
2. **Beta** - Version đang được test với limited users
3. **Stable** - Version chính thức, production-ready
4. **Deprecated** - Version đang được deprecate, sẽ bị remove
5. **Removed** - Version đã bị remove, không còn available

### Lifecycle Timeline

```
┌─────────────┐
│ Development │ (1-3 months)
└──────┬──────┘
       │
       ▼
┌─────────────┐
│    Beta     │ (1-2 months)
└──────┬──────┘
       │
       ▼
┌─────────────┐
│   Stable    │ (12-24 months)
└──────┬──────┘
       │
       ▼
┌─────────────┐
│ Deprecated  │ (6-12 months notice)
└──────┬──────┘
       │
       ▼
┌─────────────┐
│   Removed   │ (Permanently removed)
└─────────────┘
```

### Current Version Status

| Version | Status | Release Date | Deprecation Date | Removal Date |
|---------|--------|--------------|-------------------|--------------|
| v1 | ✅ Stable | Current | TBD | TBD |

## ⚠️ 4. Deprecation Policy

### Deprecation Timeline

**Standard Deprecation Process:**

1. **Announcement Phase** (3 months before deprecation)
   - Announce deprecation in changelog
   - Add deprecation warnings in API responses
   - Update documentation with migration guide
   - Notify developers via email/blog

2. **Deprecation Phase** (6-12 months)
   - Version marked as deprecated
   - Deprecation headers in responses
   - Warnings in API documentation
   - Support for bug fixes only (no new features)

3. **Removal Phase** (After deprecation period)
   - Version removed from production
   - Routes return 410 Gone
   - Documentation archived

### Deprecation Headers

Deprecated versions will include headers in responses:

```http
HTTP/1.1 200 OK
API-Version: v1
API-Deprecated: true
API-Sunset: 2025-12-31
API-Successor: v2
Link: <https://api.example.com/v2/users>; rel="successor-version"
```

### Deprecation Notice in Response

```json
{
    "success": true,
    "message": "This API version is deprecated",
    "data": {
        // Response data
    },
    "deprecation": {
        "deprecated": true,
        "sunset_date": "2025-12-31",
        "successor_version": "v2",
        "migration_guide": "https://docs.example.com/migration/v1-to-v2"
    }
}
```

### Minimum Support Period

- **Stable Version:** Minimum 12 months support
- **Deprecated Version:** Minimum 6 months notice before removal
- **Total Lifecycle:** Minimum 18 months from stable to removal

## 📝 5. Creating New Versions

### When to Create a New Version?

Create a new version when:
- ✅ Breaking changes to request/response format
- ✅ Removing or renaming endpoints
- ✅ Changing authentication mechanism
- ✅ Major changes to data models
- ✅ Changes that break backward compatibility

**DO NOT create new version for:**
- ❌ Adding new endpoints (add to existing version)
- ❌ Adding optional fields to responses
- ❌ Bug fixes
- ❌ Performance improvements
- ❌ Minor enhancements

### Version Creation Process

1. **Planning Phase**
   - Document breaking changes
   - Create migration guide
   - Plan backward compatibility strategy

2. **Development Phase**
   - Create new version directory structure
   - Implement new version controllers
   - Write comprehensive tests
   - Update documentation

3. **Testing Phase**
   - Beta testing with limited users
   - Gather feedback
   - Fix issues

4. **Release Phase**
   - Announce new version
   - Provide migration guide
   - Support both versions during transition

### Example: Creating V2

**Step 1: Create Directory Structure**

```bash
mkdir -p app/Http/Controllers/Api/V2
mkdir -p routes/api
```

**Step 2: Create V2 Routes**

```php
// routes/api/v2.php
<?php

use App\Http\Controllers\Api\V2\UserController as V2UserController;
use Illuminate\Support\Facades\Route;

Route::prefix('v2')->group(function () {
    Route::get('/users', [V2UserController::class, 'index']);
    // ...
});
```

**Step 3: Register Routes**

```php
// bootstrap/app.php or RouteServiceProvider
Route::prefix('api')->group(function () {
    require __DIR__.'/../routes/api/v1.php';
    require __DIR__.'/../routes/api/v2.php';
});
```

**Step 4: Create V2 Controllers**

```php
// app/Http/Controllers/Api/V2/UserController.php
namespace App\Http\Controllers\Api\V2;

use App\Http\Controllers\Api\V1\ApiController;

class UserController extends ApiController
{
    // V2 implementation with breaking changes
}
```

## 🔀 6. Migration Guide Template

### Migration Guide Structure

When creating a new version, provide:

1. **Overview** - What changed and why
2. **Breaking Changes** - List of breaking changes
3. **Migration Steps** - Step-by-step migration guide
4. **Code Examples** - Before/after code examples
5. **FAQ** - Common questions and answers

### Example Migration Guide

```markdown
# Migrating from V1 to V2

## Overview
V2 introduces new response format and improved error handling.

## Breaking Changes

### 1. Response Format Change
**V1:**
```json
{
    "success": true,
    "data": {...}
}
```

**V2:**
```json
{
    "success": true,
    "data": {...},
    "meta": {...}
}
```

### 2. Endpoint Changes
- `GET /api/v1/users` → `GET /api/v2/users`
- `POST /api/v1/users` → `POST /api/v2/users` (new required fields)

## Migration Steps

1. Update base URL from `/api/v1/` to `/api/v2/`
2. Update response parsing to handle new format
3. Add new required fields to requests
4. Update error handling

## Code Examples

**Before (V1):**
```javascript
fetch('/api/v1/users')
    .then(res => res.json())
    .then(data => console.log(data.data));
```

**After (V2):**
```javascript
fetch('/api/v2/users')
    .then(res => res.json())
    .then(data => {
        console.log(data.data);
        console.log(data.meta); // New meta field
    });
```
```

## ✅ 7. Best Practices

### Versioning Best Practices

✅ **DO:**
- Keep versions stable for at least 12 months
- Provide clear migration guides
- Support multiple versions during transition
- Add deprecation warnings early
- Document all breaking changes
- Maintain backward compatibility when possible
- Use semantic versioning concepts (major.minor.patch)

❌ **DON'T:**
- Don't create new version for minor changes
- Don't remove versions without notice
- Don't break backward compatibility unnecessarily
- Don't skip deprecation phase
- Don't create too many versions (aim for 1-2 active versions)

### Backward Compatibility

**Maintain Compatibility When:**
- Adding new optional fields
- Adding new endpoints
- Extending existing functionality
- Performance improvements

**Breaking Changes Require New Version:**
- Removing fields
- Changing field types
- Removing endpoints
- Changing authentication
- Major structural changes

### Version Communication

**Channels:**
- 📧 Email notifications
- 📝 Changelog/Release notes
- 📚 Documentation updates
- 🔔 In-app notifications (if applicable)
- 💬 Developer community (Discord/Slack)

## 📊 8. Version Status Tracking

### Status Indicators

| Status | Icon | Description |
|--------|------|-------------|
| Stable | ✅ | Production-ready, fully supported |
| Beta | 🧪 | Testing phase, limited support |
| Deprecated | ⚠️ | Deprecated, will be removed |
| Removed | ❌ | No longer available |

### Version Status Endpoint

Consider adding a version status endpoint:

```http
GET /api/versions
```

**Response:**
```json
{
    "versions": [
        {
            "version": "v1",
            "status": "stable",
            "release_date": "2024-01-01",
            "deprecation_date": null,
            "removal_date": null
        },
        {
            "version": "v2",
            "status": "beta",
            "release_date": "2024-06-01",
            "deprecation_date": null,
            "removal_date": null
        }
    ]
}
```

## 🔍 9. Monitoring & Analytics

### Track Version Usage

Monitor:
- Request count per version
- Error rates per version
- Response times per version
- User adoption of new versions

### Metrics to Track

- **Adoption Rate:** % of requests using new version
- **Error Rate:** Error rate per version
- **Performance:** Response time per version
- **Deprecation Warnings:** Count of deprecated version requests

## 📚 10. Examples

### Current Implementation (V1)

**Routes:**
```php
// routes/api/v1.php
Route::prefix('v1')->group(function () {
    Route::get('/users', [V1UserController::class, 'index']);
    Route::post('/users', [V1UserController::class, 'store']);
});
```

**Controllers:**
```php
// app/Http/Controllers/Api/V1/UserController.php
namespace App\Http\Controllers\Api\V1;

class UserController extends ApiController
{
    public function index()
    {
        // V1 implementation
    }
}
```

### Future Implementation (V2)

**Routes:**
```php
// routes/api/v2.php
Route::prefix('v2')->group(function () {
    Route::get('/users', [V2UserController::class, 'index']);
    Route::post('/users', [V2UserController::class, 'store']);
});
```

**Controllers:**
```php
// app/Http/Controllers/Api/V2/UserController.php
namespace App\Http\Controllers\Api\V2;

class UserController extends ApiController
{
    public function index()
    {
        // V2 implementation with breaking changes
    }
}
```

## 🎯 11. Summary

### Key Principles

1. **URL-Based Versioning** - Clear and explicit
2. **Minimum 12 Months Support** - Stable versions supported for at least 12 months
3. **6-12 Months Deprecation Notice** - Adequate time for migration
4. **Clear Migration Guides** - Help developers migrate smoothly
5. **Backward Compatibility** - Maintain when possible
6. **Communication** - Keep developers informed

### Current Status

- ✅ **V1:** Stable, production-ready
- 🔮 **V2:** Not yet created (create when breaking changes needed)

### Next Steps

When creating V2:
1. Document breaking changes
2. Create migration guide
3. Implement V2 controllers
4. Beta test with limited users
5. Announce and support both versions
6. Deprecate V1 after 6-12 months notice

## 📖 Resources

- [REST API Versioning Best Practices](https://restfulapi.net/versioning/)
- [API Versioning Strategies](https://www.baeldung.com/rest-api-versioning)
- [Semantic Versioning](https://semver.org/)
