# Health Endpoints

Health routes are available under `/api/v1`.

## Endpoints

- `GET /health` - general application health
- `GET /health/live` - liveness probe
- `GET /health/ready` - readiness probe (dependency checks)

## Use cases

- Kubernetes/containers probe configuration
- Load balancer health verification
- Basic uptime monitoring

## Local checks

```bash
curl http://localhost:8000/api/v1/health
curl http://localhost:8000/api/v1/health/live
curl http://localhost:8000/api/v1/health/ready
```
