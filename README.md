# Laravel 12 Base Project (Docker)

A Laravel 12 starter project with Docker-based local development, MySQL, Redis, Nginx, API v1 foundation, and web authentication pages.

## Highlights

- Laravel `12.x` with PHP `8.2+`
- Dockerized stack: `app`, `webserver`, `db`, `redis`, `queue`, `scheduler`, `node`, `phpmyadmin`
- API v1 with OpenAPI + Swagger UI
- API auth with Sanctum
- RBAC with Spatie Laravel Permission
- Web auth flows (register/login/email verification/password reset)
- Layered architecture (Controller -> Service -> Repository)

## Requirements

- Docker Desktop (or Docker Engine + Docker Compose)
- Git

## Quick Start

```bash
# 1) Start containers
docker compose up -d

# 2) Install backend dependencies
docker compose exec app composer install

# 3) Prepare environment
docker compose exec app cp .env.example .env
docker compose exec app php artisan key:generate

# 4) Run migrations + seed baseline roles/permissions
docker compose exec app php artisan migrate
docker compose exec app php artisan db:seed --class=RolePermissionSeeder
```

If your machine still uses the old CLI, replace `docker compose` with `docker-compose`.

## Local URLs

- App: `http://localhost:8000` (or `${WEB_PORT}`)
- Swagger UI: `http://localhost:8000/api/v1/docs`
- OpenAPI YAML: `http://localhost:8000/api/v1/openapi.yaml`
- Vite dev server: `http://localhost:5173` (or `${VITE_PORT}`)
- phpMyAdmin: `http://localhost:8080` (or `${PHPMYADMIN_PORT}`)

## Helpful Commands

### Makefile shortcuts (recommended)

```bash
make help
make setup
make up
make down
make restart
make logs
make ps
make shell
make db-shell
make artisan CMD="migrate"
make composer CMD="install"
make test
make fresh
make cache-clear
make npm-install
make npm-build
make npm-dev
make up-build
```

### Docker Compose directly

```bash
docker compose up -d
docker compose down
docker compose logs -f
docker compose exec app php artisan test
docker compose exec app bash
```

## Authentication

### API (`/api/v1`)

- Public endpoints: register, login, google login, forgot/reset password, health
- Protected endpoints (Sanctum): profile, password change, files, users, roles/permissions

### Web

- Guest flows: register, login, forgot/reset password
- Authenticated flows: email verification, profile update, password update, dashboard

## Default Database Settings (Docker)

- Host: `db` (inside Docker network)
- Port: `3306`
- Database: `laravel_db`
- Username: `laravel_user`
- Password: `root`
- Root password: `root`

You can change these values in `.env`.

## Testing

```bash
docker compose exec app php artisan test
```

## Troubleshooting

```bash
# Rebuild/restart
docker compose up -d --force-recreate

# Check service status
docker compose ps

# View logs
docker compose logs -f
```

If file permission issues appear in Linux containers:

```bash
docker compose exec app chmod -R 775 storage bootstrap/cache
```

## Documentation

Project docs are in [`docs/`](docs/). Start with:

- [Documentation Index](docs/README.md)
- [Quick Start](docs/QUICK_START.md)
- [API Foundation](docs/API_FOUNDATION.md)
- [Authentication](docs/AUTHENTICATION.md)
- [Security](docs/SECURITY.md)

## Tech Stack

- Laravel Framework `12.x`
- Laravel Sanctum `4.x`
- Spatie Laravel Permission `6.x`
- MySQL `8.0`
- Redis `7`
- Nginx (Alpine)
- Node `20` + Vite

## License

This project is open-sourced software licensed under the [MIT license](https://opensource.org/licenses/MIT).
