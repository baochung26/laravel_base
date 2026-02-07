# Hướng Dẫn Sử Dụng File Storage Module (Laravel)

## 📋 Mục lục

- [Tổng quan](#tổng-quan)
- [Cấu hình](#cấu-hình)
- [Service & Kiến trúc](#service--kiến-trúc)
- [API Endpoints](#api-endpoints)
- [File Upload](#file-upload)
- [Security](#security)
- [Best Practices](#best-practices)
- [Troubleshooting](#troubleshooting)
- [Ví dụ sử dụng trong Laravel](#ví-dụ-sử-dụng-trong-laravel)

## 🎯 Tổng quan

Module xử lý file cho project Laravel hỗ trợ:

- ✅ Local file storage
- ✅ File upload (single & multiple)
- ✅ File download
- ✅ File deletion
- ✅ File validation (size, MIME type)
- ✅ Subfolder support
- ✅ File listing và statistics
- ✅ Security với authentication & authorization

Module đã được tích hợp theo API `v1` với `auth:sanctum` và permission.

## ⚙️ Cấu hình

### Environment Variables

Thêm/cập nhật trong `.env`:

```env
# Filesystem
FILESYSTEM_DISK=local

# File module
FILE_DEFAULT_DISK=private
FILE_MAX_SIZE_KB=10240
FILE_MAX_FILE_COUNT=10
FILE_ALLOWED_MIMES=jpg,jpeg,png,gif,webp,pdf,doc,docx,xls,xlsx,csv,txt,zip
```

### Filesystem Disks

Định nghĩa trong `config/filesystems.php`:

- `public`: `storage/app/public`
- `private`: `storage/app/private`
- `local`: `storage/app`
- `s3`, `s3_private` (nếu dùng object storage)

### Constants cho File Module

Định nghĩa trong `config/constants.php`:

- `constants.files.default_disk`
- `constants.files.max_size_kb`
- `constants.files.max_file_count`
- `constants.files.allowed_mimes`

## 📦 Service & Kiến trúc

### Service chính

- `app/Services/Storage/FileManagerService.php`

Các chức năng chính:

- Upload 1 file: `upload()`
- Upload nhiều file: `uploadMany()`
- Download: `download()`
- Delete: `delete()`
- List files: `list()`
- Statistics: `stats()`
- Path sanitize & anti-traversal: `normalizePath()`
- Authorization theo owner: `authorizePath()`

### Controller API

- `app/Http/Controllers/Api/V1/FileController.php`

Controller nhận request đã validate, gọi service, trả về chuẩn `ApiResponse`.

### Request Validation

- `app/Http/Requests/File/UploadFileRequest.php`
- `app/Http/Requests/File/UploadMultipleFilesRequest.php`
- `app/Http/Requests/File/FilePathRequest.php`
- `app/Http/Requests/File/FileListRequest.php`

## 🌐 API Endpoints

Base URL:

```text
http://localhost:8000/api/v1/files
```

Tất cả endpoint bên dưới yêu cầu:

- `Authorization: Bearer <SANCTUM_TOKEN>`
- `Accept: application/json`

### 1) Upload Single File

```http
POST /api/v1/files/upload
Content-Type: multipart/form-data
```

Fields:

- `file` (required)
- `disk` (`public|private`, optional)
- `folder` (optional)

Ví dụ:

```bash
curl -X POST "http://localhost:8000/api/v1/files/upload" \
  -H "Authorization: Bearer YOUR_TOKEN" \
  -H "Accept: application/json" \
  -F "disk=private" \
  -F "folder=docs/contracts" \
  -F "file=@/absolute/path/to/contract.pdf"
```

### 2) Upload Multiple Files

```http
POST /api/v1/files/upload-multiple
Content-Type: multipart/form-data
```

Fields:

- `files[]` (required)
- `disk` (`public|private`, optional)
- `folder` (optional)

Ví dụ:

```bash
curl -X POST "http://localhost:8000/api/v1/files/upload-multiple" \
  -H "Authorization: Bearer YOUR_TOKEN" \
  -H "Accept: application/json" \
  -F "disk=private" \
  -F "folder=docs/contracts" \
  -F "files[]=@/absolute/path/to/contract-1.pdf" \
  -F "files[]=@/absolute/path/to/contract-2.pdf"
```

### 3) Download File

```http
GET /api/v1/files/download?disk=private&path=users/1/docs/contracts/contract.pdf
```

Ví dụ:

```bash
curl -X GET "http://localhost:8000/api/v1/files/download?disk=private&path=users/1/docs/contracts/contract.pdf" \
  -H "Authorization: Bearer YOUR_TOKEN" \
  -o contract.pdf
```

### 4) Delete File

```http
DELETE /api/v1/files
Content-Type: application/json
```

Body:

```json
{
  "disk": "private",
  "path": "users/1/docs/contracts/contract.pdf"
}
```

Ví dụ:

```bash
curl -X DELETE "http://localhost:8000/api/v1/files" \
  -H "Authorization: Bearer YOUR_TOKEN" \
  -H "Accept: application/json" \
  -H "Content-Type: application/json" \
  -d "{\"disk\":\"private\",\"path\":\"users/1/docs/contracts/contract.pdf\"}"
```

### 5) List Files

```http
GET /api/v1/files/list?disk=private&folder=docs/contracts&recursive=true&limit=50
```

Ví dụ:

```bash
curl -X GET "http://localhost:8000/api/v1/files/list?disk=private&folder=docs/contracts&recursive=true&limit=50" \
  -H "Authorization: Bearer YOUR_TOKEN" \
  -H "Accept: application/json"
```

### 6) Get Storage Statistics

```http
GET /api/v1/files/stats?disk=private&folder=docs/contracts&recursive=true
```

Ví dụ:

```bash
curl -X GET "http://localhost:8000/api/v1/files/stats?disk=private&folder=docs/contracts&recursive=true" \
  -H "Authorization: Bearer YOUR_TOKEN" \
  -H "Accept: application/json"
```

## 📤 File Upload

### Quy tắc upload

- Module tự generate filename an toàn, unique.
- User thường sẽ được scope vào `users/{auth_user_id}/...`.
- User có `manage files` hoặc `manage users` có thể truyền folder tuyệt đối (ví dụ `users/2/contracts`).

### Validation mặc định

- Kích thước tối đa theo `FILE_MAX_SIZE_KB`
- Số lượng file tối đa theo `FILE_MAX_FILE_COUNT`
- MIME/extension theo `FILE_ALLOWED_MIMES`

## 🔐 Security

### Authentication

- Tất cả route `files/*` nằm trong group `auth:sanctum`
- Route định nghĩa tại `routes/api/v1/routes.php`

### Authorization

2 lớp bảo vệ:

1. Permission theo action trong `FileController`:
- `upload files`
- `view files`
- `delete files`
- fallback cho `manage files` và `manage users`

2. Ownership check theo path trong `FileManagerService::authorizePath()`:
- User thường chỉ được thao tác path thuộc `users/{id}/...`

### Permission seed

Trong `database/seeders/RolePermissionSeeder.php` đã có:

- `view files`
- `upload files`
- `delete files`
- `manage files`

Sau khi cập nhật permission:

```bash
php artisan db:seed --class=RolePermissionSeeder
```

## 💡 Best Practices

### 1) Tổ chức subfolder theo domain

```text
users/{userId}/documents
users/{userId}/avatars
users/{userId}/exports
```

### 2) Mặc định dùng private disk

- Chỉ dùng `public` khi file cần public URL.
- File nhạy cảm nên giữ `private`.

### 3) Không tin tưởng input path

- Module đã sanitize path và chặn `..`.
- Không tự ghép path thủ công ngoài service.

### 4) Upload bất đồng bộ cho file nặng

- Với file lớn hoặc xử lý hậu kỳ (resize, OCR), đẩy qua queue.

### 5) Bổ sung antivirus scanning

- Với file từ user/public, nên scan trước khi dùng.

### 6) Theo dõi dung lượng

- Dùng endpoint stats + monitoring định kỳ để tránh đầy disk.

## 🐛 Troubleshooting

### 1) Lỗi 422 khi upload

Nguyên nhân thường gặp:
- Vượt `FILE_MAX_SIZE_KB`
- MIME không nằm trong `FILE_ALLOWED_MIMES`
- Vượt `FILE_MAX_FILE_COUNT`

Khắc phục:
- Kiểm tra lại env và request payload
- Chạy `php artisan config:clear`

### 2) Lỗi 403

Nguyên nhân thường gặp:
- Thiếu permission (`upload files`, `view files`, `delete files`)
- Path không thuộc owner (`users/{auth_user_id}/...`)

Khắc phục:
- Reseed permission
- Kiểm tra role/permission của user hiện tại
- Kiểm tra path truyền lên

### 3) Lỗi 404 khi download/delete

Nguyên nhân:
- Sai `disk`
- Sai `path`
- File đã bị xóa

Khắc phục:
- Gọi endpoint list trước để lấy path chính xác

### 4) Không ghi được file local

Khắc phục:

```bash
chmod -R 775 storage bootstrap/cache
chown -R www-data:www-data storage bootstrap/cache
```

Trong Docker, có thể chạy từ container `app`.

## 🧪 Demo response mẫu

### Upload thành công (`201`)

```json
{
  "success": true,
  "message": "File uploaded successfully",
  "meta": {
    "request_id": "req_123",
    "timestamp": "2026-02-07T12:00:00Z"
  },
  "data": {
    "disk": "private",
    "path": "users/1/docs/contracts/contract_1700000000_abcd1234.pdf",
    "name": "contract_1700000000_abcd1234.pdf",
    "directory": "users/1/docs/contracts",
    "size_bytes": 231231,
    "size_human": "225.81 KB",
    "mime_type": "application/pdf",
    "last_modified": "2026-02-07T12:00:00+00:00",
    "url": null
  }
}
```

### Validation lỗi (`422`)

```json
{
  "success": false,
  "message": "The given data was invalid.",
  "errors": {
    "file": [
      "The file field is required."
    ]
  }
}
```

### Unauthorized (`403`)

```json
{
  "message": "You are not authorized to access this path."
}
```

## 🧭 End-to-end flow đề xuất

1. Login lấy token qua `/api/v1/login`
2. Upload file vào `folder=docs/contracts`
3. Lấy `data.path` từ response upload
4. Gọi `list` để kiểm tra file
5. Gọi `stats` để kiểm tra dung lượng
6. Download file bằng `path`
7. Delete file
8. Gọi lại `list` để confirm đã xóa

## 🔗 Tài liệu liên quan

- `docs/SECURITY.md`
- `docs/EMAIL_USAGE.md`
- `routes/api/v1/routes.php`
- `app/Services/Storage/FileManagerService.php`
