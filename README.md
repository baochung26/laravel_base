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

### Bước 3: Cài đặt dependencies

```bash
# Cài đặt Composer packages
docker-compose exec app composer install

# Cài đặt NPM packages (tùy chọn)
docker-compose exec app npm install
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
- **Application:** http://localhost:8000
- **phpMyAdmin:** http://localhost:8080

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
make npm CMD="install"      # Chạy npm command
make fresh         # Fresh migration với seeding
make cache-clear   # Xóa tất cả cache
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

# Chạy npm commands
docker-compose exec app npm install

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
│   ├── views/            # Blade templates
│   ├── css/              # CSS files
│   └── js/               # JavaScript files
├── routes/               # Route definitions
│   ├── web.php           # Web routes
│   └── api.php           # API routes
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
    ├── ARCHITECTURE.md   # Architecture guide
    ├── AUTHENTICATION.md # Authentication guide
    └── QUICK_START.md    # Quick start guide
```

### Kiến trúc: Repository-Service-Controller Pattern

Dự án sử dụng **Repository-Service-Controller Pattern** để tách biệt các lớp logic:

- **Repository Layer**: Xử lý truy cập database (Data Access)
- **Service Layer**: Xử lý business logic (Business Logic)
- **Controller Layer**: Xử lý HTTP requests/responses (HTTP Layer)
- **DTOs**: Transfer data type-safe giữa các layers

Xem chi tiết trong file [ARCHITECTURE.md](docs/ARCHITECTURE.md)

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

Chỉnh sửa `docker-compose.yml`:
- Application port (8000): Thay đổi `"8000:80"` trong service `webserver`
- phpMyAdmin port (8080): Thay đổi `"8080:80"` trong service `phpmyadmin`
- MySQL port (3306): Thay đổi `"3306:3306"` trong service `db`

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

Các tài liệu chi tiết của dự án được lưu trong thư mục [`docs/`](docs/):

- [📐 ARCHITECTURE.md](docs/ARCHITECTURE.md) - Hướng dẫn về kiến trúc và Repository-Service-Controller Pattern
- [🔐 AUTHENTICATION.md](docs/AUTHENTICATION.md) - Hướng dẫn về Authentication & Authorization
- [👤 USER_MODULE.md](docs/USER_MODULE.md) - Hướng dẫn về User Module (CRUD, Profile, Password)
- [🔌 API_FOUNDATION.md](docs/API_FOUNDATION.md) - Hướng dẫn về API Foundation (Versioning, Response Format, Swagger)
- [📊 LOGGING.md](docs/LOGGING.md) - Hướng dẫn về Logging & Monitoring (JSON logs, Request ID, Slow Queries)
- [🚨 EXCEPTION_HANDLING.md](docs/EXCEPTION_HANDLING.md) - Hướng dẫn về Exception Handling chuẩn (Standardized Error Format)
- [⚙️ CONFIG_ENVIRONMENT.md](docs/CONFIG_ENVIRONMENT.md) - Hướng dẫn về Configuration & Environment (Cache, Queue, Mail, File System)
- [🔄 QUEUE_SCHEDULER.md](docs/QUEUE_SCHEDULER.md) - Hướng dẫn về Queue & Scheduler (Redis Queue, Jobs, Cron Tasks)
- [💾 CACHE_STRATEGY.md](docs/CACHE_STRATEGY.md) - Hướng dẫn về Cache Strategy (Key Convention, Invalidation)
- [🚀 QUICK_START.md](docs/QUICK_START.md) - Hướng dẫn khởi động nhanh dự án
- [✅ AUTH_CHECKLIST.md](docs/AUTH_CHECKLIST.md) - Checklist các tính năng Auth & Authorization

**Note:** File `.env.example` template có thể được tìm thấy trong `docs/.env.example` hoặc `docs/CONFIG_ENV.example`

## 🔌 API Foundation

Dự án sử dụng API Foundation với các tính năng:

- **API Versioning:** `/api/v1/*` structure
- **Response Format:** Standardized success/error responses
- **Pagination:** Standardized pagination format
- **API Resources:** Transformers cho data formatting
- **Swagger/OpenAPI:** API documentation với L5-Swagger

Xem chi tiết trong file [API_FOUNDATION.md](docs/API_FOUNDATION.md)

### Swagger Documentation

Sau khi cài đặt và generate:

```bash
php artisan l5-swagger:generate
```

Truy cập: `http://localhost:8000/api/documentation`

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
