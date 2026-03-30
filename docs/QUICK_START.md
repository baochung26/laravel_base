# Quick Start

This guide runs the project with Docker and verifies the API is working.

## Requirements

- Docker Desktop (or Docker Engine + Docker Compose)
- Git

## 1. Boot containers

```bash
docker compose up -d
```

Services started by default include `app`, `webserver`, `db`, `redis`, `queue`, `scheduler`, `node`, and `phpmyadmin`.

## 2. Install dependencies and app key

```bash
docker compose exec app composer install
docker compose exec app cp .env.example .env
docker compose exec app php artisan key:generate
```

## 3. Run migrations and seed baseline data

```bash
docker compose exec app php artisan migrate
docker compose exec app php artisan db:seed --class=RolePermissionSeeder
```

## 4. Access local services

- App/API: `http://localhost:8000` (or `${WEB_PORT}`)
- Swagger UI: `http://localhost:8000/api/v1/docs`
- OpenAPI YAML: `http://localhost:8000/api/v1/openapi.yaml`
- phpMyAdmin: `http://localhost:8080` (or `${PHPMYADMIN_PORT}`)
- Vite dev server: `http://localhost:5173` (or `${VITE_PORT}`)

## 5. First API checks

```bash
# Health
curl http://localhost:8000/api/v1/health

# Register
curl -X POST http://localhost:8000/api/v1/register \
  -H "Content-Type: application/json" \
  -d '{
    "name":"Demo User",
    "email":"demo@example.com",
    "password":"password123",
    "password_confirmation":"password123"
  }'
```

## 6. Common commands

```bash
# See logs
docker compose logs -f app webserver

# Run tests
docker compose exec app php artisan test

# Stop all
docker compose down
```
