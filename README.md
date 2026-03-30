# Laravel 12 Base Project với Docker

Dự án Laravel 12 cơ bản sử dụng Docker với MySQL, PHP 8.2+, và Nginx.

## 📋 Yêu cầu hệ thống

- Docker Desktop (hoặc Docker Engine + Docker Compose)
- Git
- PHP 8.2 trở lên (trong container)

## 🚀 Cài đặt và Khởi chạy

### Bước 1: Clone hoặc tải dự án

```bash
git clone <repository-url>
cd laravel_base_cursor
```

### Bước 2: Khởi động Docker containers

```bash
docker-compose up -d
```

Lệnh trên sẽ khởi động luôn service `node` chạy Vite dev server tại `http://localhost:5173`.

### Bước 3: Cài đặt dependencies

```bash
# Cài đặt Composer packages
docker-compose exec app composer install
```

### Bước 4: Cấu hình môi trường

```bash
# Copy file .env.example thành .env
docker-compose exec app cp .env.example .env

# Generate application key
docker-compose exec app php artisan key:generate
```

### Bước 5: Publish Spatie Permission migrations (cho RBAC)

```bash
docker-compose exec app php artisan vendor:publish --provider="Spatie\Permission\PermissionServiceProvider"
```

### Bước 6: Chạy migrations và seeders

```bash
docker-compose exec app php artisan migrate
docker-compose exec app php artisan db:seed --class=RolePermissionSeeder
```

### Bước 7: Truy cập ứng dụng

Mở trình duyệt và truy cập:
- **Application:** http://localhost:${WEB_PORT} (mặc định `8000`)
- **Vite Dev Server:** http://localhost:${VITE_PORT} (mặc định `5173`)
- **phpMyAdmin:** http://localhost:${PHPMYADMIN_PORT} (mặc định `8080`)

## 🛠️ Các lệnh hữu ích

### Sử dụng Makefile (khuyến nghị)

```bash
make help          # Xem danh sách các lệnh
make setup         # Cài đặt toàn bộ dự án (khuyến nghị lần đầu)
make up            # Khởi động containers
make down          # Dừng containers
make restart       # Khởi động lại containers
make logs          # Xem logs
make shell         # Mở shell trong container app
make db-shell      # Mở MySQL shell
make artisan CMD="migrate"  # Chạy artisan command
make composer CMD="install" # Chạy composer command
make fresh         # Fresh migration với seeding
make cache-clear   # Xóa tất cả cache
make npm-install   # Cài dependencies frontend (Vite)
make npm-build     # Build assets frontend
make npm-dev       # Chạy Vite dev server (port 5173)
make up-build      # Build frontend trước rồi start backend containers
```

### Hoặc sử dụng Docker Compose trực tiếp

```bash
# Khởi động containers
docker-compose up -d

# Dừng containers
docker-compose down

# Xem logs
docker-compose logs -f

# Chạy artisan commands
docker-compose exec app php artisan migrate

# Chạy composer commands
docker-compose exec app composer install

# Truy cập shell trong container
docker-compose exec app bash

# Truy cập MySQL shell
docker-compose exec app mysql -u laravel_user -proot laravel_db
```

## 📁 Cấu trúc dự án

```
laravel_base_cursor/
├── app/                  # Application code
│   ├── Http/
│   │   ├── Controllers/  # HTTP Layer - Controllers
│   │   │   ├── Auth/     # Authentication controllers
│   │   │   └── RolePermissionController.php
│   │   ├── Middleware/    # Custom middleware
│   │   ├── Requests/     # Form request validation
│   │   ├── Resources/    # API Resources
│   │   └── Kernel.php    # HTTP Kernel
│   ├── Services/         # Business Logic Layer
│   │   ├── AuthService.php
│   │   └── UserService.php
│   ├── Repositories/     # Data Access Layer
│   │   ├── Contracts/    # Repository interfaces
│   │   │   ├── RepositoryInterface.php
│   │   │   └── UserRepositoryInterface.php
│   │   └── Eloquent/     # Repository implementations
│   │       ├── BaseRepository.php
│   │       └── UserRepository.php
│   ├── DTOs/             # Data Transfer Objects
│   │   ├── UserDTO.php
│   │   └── LoginDTO.php
│   ├── Models/           # Eloquent Models
│   ├── Providers/        # Service providers
│   └── Exceptions/       # Custom exceptions
├── bootstrap/
│   └── app.php           # Application bootstrap (Laravel 12 structure)
├── config/               # Configuration files
├── database/
│   ├── migrations/       # Database migrations
│   └── seeders/          # Database seeders
├── public/               # Public assets
├── resources/
│   └── views/            # Email templates
├── routes/               # Route definitions
│   ├── console.php       # Artisan commands
│   └── api/v1/           # API v1 (prefix: /api/v1)
│       └── routes.php    # Auth, users, files, health, etc.
├── storage/              # Storage files
├── tests/                # Tests
├── docker/               # Docker configuration
│   ├── nginx/            # Nginx config
│   ├── php/              # PHP config
│   └── mysql/            # MySQL config
├── docker-compose.yml    # Docker Compose configuration
├── Dockerfile            # PHP Dockerfile
├── Makefile              # Helper commands
└── docs/                 # Documentation
    ├── API_FOUNDATION.md # API & architecture
    ├── AUTHENTICATION.md # Authentication guide
    └── QUICK_START.md    # Quick start guide
```

### Kiến trúc: Repository-Service-Controller Pattern

Dự án sử dụng **Repository-Service-Controller Pattern** để tách biệt các lớp logic:

- **Repository Layer**: Xử lý truy cập database (Data Access)
- **Service Layer**: Xử lý business logic (Business Logic)
- **Controller Layer**: Xử lý HTTP requests/responses (HTTP Layer)
- **DTOs**: Transfer data type-safe giữa các layers

Xem chi tiết trong [API_FOUNDATION.md](docs/API_FOUNDATION.md) và [QUICK_START.md](docs/QUICK_START.md).

## 🗄️ Cấu hình Database

Database đã được cấu hình sẵn với MySQL:

- **Host:** db (hoặc localhost khi chạy từ host)
- **Port:** 3306
- **Database:** laravel_db
- **Username:** laravel_user
- **Password:** root
- **Root Password:** root

Thông tin này có thể được thay đổi trong file `.env` hoặc `docker-compose.yml`.

## 📦 Packages đã cài đặt

### Production Packages
- Laravel Framework 12.x
- Laravel Sanctum 4.x (API Authentication)
- Laravel Tinker 2.9 (REPL)
- Spatie Laravel Permission 6.x (RBAC)

### Development Packages
- Laravel Debugbar (Debug toolbar)
- Laravel IDE Helper (IDE support)
- Laravel Pint (Code style fixer)
- PHPUnit 11.x (Testing)
- Spatie Laravel Ignition (Error pages)

## 🔐 Authentication & Authorization

Dự án đã được tích hợp sẵn:

- **Authentication:** Login/Register với JWT (Laravel Sanctum)
- **RBAC:** Role-Based Access Control với Spatie Permission
- **Rate Limiting:** Đã cấu hình sẵn cho login, register, API
- **Lockout:** Tự động lockout sau 5 lần đăng nhập sai

Xem chi tiết trong file [AUTHENTICATION.md](docs/AUTHENTICATION.md)

### Demo Accounts (sau khi chạy seeder)

- **Admin:** admin@example.com / password
- **User:** user@example.com / password

## 🔧 Tùy chỉnh

### Thay đổi PHP version

Dockerfile đang sử dụng PHP 8.2 (yêu cầu tối thiểu cho Laravel 12). 
Để nâng cấp lên PHP 8.3, chỉnh sửa `Dockerfile`:
```dockerfile
FROM php:8.3-fpm
```

### Thay đổi port

Chỉnh sửa file `.env` (không cần sửa `docker-compose.yml`):
- `WEB_PORT=8000` (port host cho web app)
- `PHPMYADMIN_PORT=8080` (port host cho phpMyAdmin)
- `DB_FORWARD_PORT=3306` (port host forward tới MySQL container)

Sau khi đổi port, chạy lại:
```bash
docker-compose up -d --force-recreate
```

### Thay đổi cấu hình PHP

Chỉnh sửa file `docker/php/local.ini` để thay đổi các cấu hình PHP như `upload_max_filesize`, `post_max_size`, etc.

### Thay đổi cấu hình Nginx

Chỉnh sửa file `docker/nginx/default.conf` để tùy chỉnh Nginx.

## 🧪 Testing

```bash
# Chạy tests
docker-compose exec app php artisan test

# Hoặc với PHPUnit
docker-compose exec app vendor/bin/phpunit
```

## 🐛 Troubleshooting

### Container không khởi động

```bash
# Kiểm tra logs
docker-compose logs

# Khởi động lại containers
docker-compose restart
```

### Database connection error

- Đảm bảo service `db` đã chạy: `docker-compose ps`
- Kiểm tra `.env` có đúng cấu hình database không
- Đợi một vài giây sau khi start container để MySQL khởi động xong

### Permission issues

```bash
# Fix storage permissions
docker-compose exec app chmod -R 775 storage bootstrap/cache
docker-compose exec app chown -R www:www storage bootstrap/cache
```

### Clear all caches

```bash
docker-compose exec app php artisan cache:clear
docker-compose exec app php artisan config:clear
docker-compose exec app php artisan route:clear
docker-compose exec app php artisan view:clear
```

Hoặc sử dụng: `make cache-clear`

## 📝 Notes

- Tất cả files sẽ được đồng bộ giữa host và container, bạn có thể chỉnh sửa code trực tiếp
- Database data sẽ được lưu trong Docker volume `dbdata` và sẽ persist ngay cả khi containers bị xóa
- Để xóa database data, chạy: `docker-compose down -v`
- Laravel 12 yêu cầu PHP 8.2 trở lên
- Cấu trúc Laravel 12 đã được cập nhật với `bootstrap/app.php` mới

## 📚 Tài liệu dự án

Tài liệu chi tiết nằm trong [`docs/`](docs/). **Mục lục:** [docs/README.md](docs/README.md).

| Nhóm | Tài liệu chính |
|------|----------------|
| **Bắt đầu** | [QUICK_START.md](docs/QUICK_START.md) |
| **API Contract** | [API_FOUNDATION.md](docs/API_FOUNDATION.md), [API_RESPONSE_AND_ERRORS.md](docs/API_RESPONSE_AND_ERRORS.md), [SWAGGER_USAGE.md](docs/SWAGGER_USAGE.md), [openapi.yaml](docs/openapi.yaml) |
| **Architecture & Security** | [ARCHITECTURE_CONTROLLER_SERVICE_REPOSITORY.md](docs/ARCHITECTURE_CONTROLLER_SERVICE_REPOSITORY.md), [SECURITY.md](docs/SECURITY.md) |
| **Auth** | [AUTHENTICATION.md](docs/AUTHENTICATION.md), [WEB_GOOGLE_LOGIN.md](docs/WEB_GOOGLE_LOGIN.md) |
| **Operations** | [HEALTH_CHECK.md](docs/HEALTH_CHECK.md), [QUEUE_SCHEDULER.md](docs/QUEUE_SCHEDULER.md) |

**Ghi chú:** `.env.example` có sẵn ở root.

## 🔌 API Foundation

Dự án sử dụng API Foundation với các tính năng:

- **API Versioning:** `/api/v1/*` structure
- **Response Format:** Standardized success/error responses
- **Pagination:** Standardized pagination format
- **API Resources:** Transformers cho data formatting
- **Swagger UI:** `GET /api/v1/docs`
- **OpenAPI Spec:** `GET /api/v1/openapi.yaml`

Xem chi tiết trong file [API_FOUNDATION.md](docs/API_FOUNDATION.md)

## 🔗 Tài liệu tham khảo

- [Laravel 12 Documentation](https://laravel.com/docs/12.x)
- [Laravel Sanctum Documentation](https://laravel.com/docs/sanctum)
- [Spatie Laravel Permission Documentation](https://spatie.be/docs/laravel-permission)
- [Docker Documentation](https://docs.docker.com/)

## 🤝 Contributing

1. Fork the project
2. Create your feature branch (`git checkout -b feature/AmazingFeature`)
3. Commit your changes (`git commit -m 'Add some AmazingFeature'`)
4. Push to the branch (`git push origin feature/AmazingFeature`)
5. Open a Pull Request

## 📄 License

This project is open-sourced software licensed under the [MIT license](https://opensource.org/licenses/MIT).
