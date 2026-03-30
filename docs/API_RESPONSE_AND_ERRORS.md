# API Response and Error Format

All API responses follow a consistent envelope.

## Success response

```json
{
  "success": true,
  "message": "User retrieved successfully",
  "meta": {
    "request_id": "...",
    "timestamp": "2026-03-30T00:00:00+00:00"
  },
  "data": {}
}
```

Notes:

- `data` is present when the endpoint returns payload.
- `meta.timestamp` is ISO-8601.
- `meta.request_id` supports traceability in logs.

## Error response

```json
{
  "success": false,
  "message": "Validation failed",
  "meta": {
    "request_id": "...",
    "timestamp": "2026-03-30T00:00:00+00:00"
  },
  "errors": {
    "email": ["The email field is required."]
  }
}
```

Notes:

- `errors` is included when structured details are available (for example, validation).
- HTTP status code is communicated by the HTTP response itself.

## Pagination response

List endpoints can include pagination fields in `meta` and optional `links`.

## Source of truth in code

- `app/Support/ApiResponse.php`
- `app/Traits/ApiResponseTrait.php`
- `app/Exceptions/Handler.php`
