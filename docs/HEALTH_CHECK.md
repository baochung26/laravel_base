# Health Check (Liveness/Readiness)

Tài liệu này mô tả các endpoint health check dùng cho monitoring và Kubernetes probes.

## Endpoints

### 1) Combined health

- **URL**: `GET /api/v1/health`
- **Mục đích**: Trả về trạng thái tổng quan + chi tiết check DB/Redis.
- **HTTP code**: Luôn `200`, nhưng có trường `overall_status`:
  - `ok`: tất cả dependency OK
  - `degraded`: có dependency lỗi (xem `data.checks`)

Response mẫu:

```json
{
  "success": true,
  "message": "OK",
  "data": {
    "overall_status": "ok",
    "timestamp": "2026-01-21T10:00:00Z",
    "checks": {
      "db": { "ok": true, "duration_ms": 3 },
      "redis": { "ok": true, "message": "PONG", "duration_ms": 1 }
    }
  }
}
```

### 2) Liveness probe

- **URL**: `GET /api/v1/health/live`
- **Mục đích**: Kiểm tra process/app còn sống (không phụ thuộc DB/Redis).
- **HTTP code**: `200` nếu app chạy.

### 3) Readiness probe

- **URL**: `GET /api/v1/health/ready`
- **Mục đích**: Kiểm tra app đã “ready” để nhận traffic (DB + Redis phải OK).
- **HTTP code**:
  - `200` nếu ready
  - `503` nếu không ready (payload theo chuẩn error format, có `errors.checks`)

## Kubernetes (gợi ý cấu hình)

Ví dụ cấu hình probes:

```yaml
livenessProbe:
  httpGet:
    path: /api/v1/health/live
    port: 80
  initialDelaySeconds: 10
  periodSeconds: 10

readinessProbe:
  httpGet:
    path: /api/v1/health/ready
    port: 80
  initialDelaySeconds: 10
  periodSeconds: 10
```

## Testing

Chạy test:

```bash
docker compose exec app php artisan test --filter=HealthApiV1Test
```

