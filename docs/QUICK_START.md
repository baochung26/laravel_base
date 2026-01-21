# Quick Start Guide

## Khởi động nhanh dự án

### 1. Cài đặt Laravel (nếu chưa có)

Nếu bạn chưa có Laravel installed, chạy:

```bash
docker-compose up -d
docker-compose exec app composer create-project laravel/laravel .
```

Hoặc sử dụng Laravel installer:

```bash
composer create-project laravel/laravel .
```

### 2. Setup môi trường

```bash
# Copy .env.example thành .env (sẽ được tạo tự động nếu dùng composer create-project)
cp .env.example .env

# Generate application key
docker-compose exec app php artisan key:generate
```

### 3. Chạy migrations

```bash
docker-compose exec app php artisan migrate
```

### 4. Truy cập ứng dụng

- Application: http://localhost:8000
- phpMyAdmin: http://localhost:8080

## Hoặc sử dụng Makefile (khuyến nghị)

```bash
make setup
```

Lệnh này sẽ tự động:
- Khởi động containers
- Cài đặt dependencies
- Copy .env.example thành .env
- Generate application key
- Chạy migrations

## Ghi chú

- File `.env.example` sẽ được tạo tự động khi bạn cài đặt Laravel bằng `composer create-project`
- Nếu bạn đã có Laravel project, chỉ cần copy file `.env.example` từ project Laravel khác hoặc tạo file `.env` với cấu hình phù hợp
- Database connection đã được cấu hình sẵn trong `docker-compose.yml`:
  - Host: db
  - Port: 3306
  - Database: laravel_db
  - Username: laravel_user
  - Password: root
