# Swagger and OpenAPI Usage

## Endpoints

- Swagger UI: `GET /api/v1/docs`
- OpenAPI spec: `GET /api/v1/openapi.yaml`

Local examples:

- `http://localhost:8000/api/v1/docs`
- `http://localhost:8000/api/v1/openapi.yaml`

## Quick testing flow

1. Call `POST /api/v1/login` and copy `access_token`.
2. Open Swagger UI and click **Authorize**.
3. Enter: `Bearer <access_token>`.
4. Run protected endpoints (for example `GET /api/v1/me`).

## Updating API documentation

When API routes or payloads change, update `docs/openapi.yaml`:

- Add or modify `paths`
- Keep request/response schemas current
- Keep examples aligned with real API behavior

This project keeps OpenAPI spec manually versioned in Git for clear review history.
