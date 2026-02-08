# Hướng dẫn làm việc với File Storage

Tài liệu tiếng Việt: cấu hình, service/helper, API và demo đầy đủ.

## Mục lục

- [Tổng quan và đánh giá](#tổng-quan-và-đánh-giá)
- [Khi nào dùng Service / Helper nào](#khi-nào-dùng-service--helper-nào)
- [Cấu hình](#cấu-hình)
- [Service và Helper](#service-và-helper)
- [API Endpoints](#api-endpoints)
- [Demo: gọi từ code Laravel](#demo-gọi-từ-code-laravel)
- [Bảo mật](#bảo-mật)
- [Khuyến nghị](#khuyến-nghị)
- [Xử lý lỗi thường gặp](#xử-lý-lỗi-thường-gặp)
- [Tài liệu liên quan](#tài-liệu-liên-quan)

---

## Tổng quan và đánh giá

### Module hiện hỗ trợ

- **Local / public / private disk**: lưu file local hoặc (khi cấu hình) S3.
- **Upload**: một file hoặc nhiều file qua API, có validation (size, MIME, số lượng).
- **Download / xóa / liệt kê / thống kê** qua API, có phân quyền theo user và path.
- **Avatar user**: upload/xóa avatar qua `StorageService` và path chuẩn `avatars/{userId}/...`.
- **Helper**: tạo path chuẩn (`StoragePath`), format byte và tên file an toàn (`FileHelper`).

### Đánh giá: đã đủ dùng chưa?

| Nội dung | Trạng thái |
|----------|------------|
| Service tập trung (upload, download, delete, list, stats) | ✅ Có `FileManagerService` (API, user-scoped) |
| Service lưu file public/private, URL, metadata | ✅ Có `StorageService` (storePublic, storePrivate, getPublicUrl, getTemporaryUrl, getMetadata, delete, ...) |
| Helper path (avatar, document, temp, export...) | ✅ Có `StoragePath` |
| Helper format byte, tên file an toàn | ✅ Có `FileHelper` |
| Avatar thống nhất qua service | ✅ ProfileController / UserController dùng `StorageService::storeAvatar` |
| Khi cần chỉ cần gọi service/helper | ✅ Có thể gọi trực tiếp trong controller, command, job |

**Kết luận:** Phần làm việc với file đã có service và helper rõ ràng; khi cần xử lý file chỉ cần gọi đúng service/helper theo bảng bên dưới.

---

## Khi nào dùng Service / Helper nào

| Nhu cầu | Dùng gì | Ghi chú |
|--------|---------|--------|
| Upload/download/delete/list/stats **qua API**, có user + permission + path scope | `FileManagerService` | Controller API file đã dùng; path dạng `users/{id}/...` (trừ khi user có `manage files`) |
| Lưu file **public** (ảnh, tài liệu có URL) | `StorageService::storePublic()` | Trả về path đã lưu |
| Lưu file **private** (chỉ xem qua signed URL hoặc download) | `StorageService::storePrivate()` | Trả về path đã lưu |
| Avatar user | `StorageService::storeAvatar()`, `StorageService::delete()` | Path: `avatars/{userId}/tên_unique.jpg` |
| Tài liệu theo user | `StorageService::storeUserDocument()` | Path: `documents/users/{userId}/...` |
| Lấy URL public file | `StorageService::getPublicUrl($path, $disk)` | Disk public |
| Lấy URL tạm (private) | `StorageService::getTemporaryUrl($path, $expiration, $disk)` | S3 có signed URL; local trả route download |
| Xóa file / thư mục | `StorageService::delete()`, `StorageService::deleteDirectory()` | Không check user; dùng khi đã biết path an toàn |
| Metadata file (size, mime, last_modified...) | `StorageService::getMetadata($path, $disk)` | Dùng trong command, cron, báo cáo |
| Tạo path chuẩn (avatar, document, temp, export...) | `StoragePath::avatar()`, `StoragePath::userDocument()`, `StoragePath::temp()`, ... | Chỉ tạo chuỗi path, không ghi disk |
| Tên file unique | `StoragePath::uniqueFilename($originalName, $prefix)` | Dùng trước khi lưu |
| Format dung lượng (1024 → "1 KB") | `FileHelper::formatBytes($bytes)` | Hiển thị UI hoặc log |
| Tên file an toàn (bỏ ký tự lạ) | `FileHelper::sanitizeFilename($filename)` | Trước khi lưu nếu không dùng uniqueFilename |

---

## Cấu hình

### Biến môi trường (.env)

```env
# Filesystem mặc định
FILESYSTEM_DISK=local

# Module file (API upload/list/download)
FILE_DEFAULT_DISK=private
FILE_MAX_SIZE_KB=10240
FILE_MAX_FILE_COUNT=10
FILE_ALLOWED_MIMES=jpg,jpeg,png,gif,webp,pdf,doc,docx,xls,xlsx,csv,txt,zip
```

### Disk (config/filesystems.php)

- `public`: `storage/app/public`, URL qua `php artisan storage:link`.
- `private`: `storage/app/private`, không có URL công khai.
- `local`: `storage/app`.

### Constants (config/constants.php)

- **Avatar**: `uploads.avatar_disk`, `uploads.avatar_dir`, `uploads.max_avatar_kb`, `uploads.allowed_avatar_mimes`.
- **File API**: `files.default_disk`, `files.max_size_kb`, `files.max_file_count`, `files.allowed_mimes`.

---

## Service và Helper

### FileManagerService (`app/Services/Storage/FileManagerService.php`)

Dùng cho **API file**: upload/list/download/delete/stats theo user và path.

| Method | Mô tả |
|--------|--------|
| `upload(User $user, UploadedFile $file, string $disk, ?string $folder)` | Upload 1 file; folder mặc định `users/{id}`; trả về mảng metadata. |
| `uploadMany(User $user, array $files, string $disk, ?string $folder)` | Upload nhiều file. |
| `download(User $user, string $disk, string $path)` | Stream download; có kiểm tra path thuộc user (hoặc manage files). |
| `delete(User $user, string $disk, string $path)` | Xóa file; trả về `true/false`. |
| `list(User $user, string $disk, ?string $folder, bool $recursive, int $limit)` | Liệt kê file trong folder; trả về mảng metadata. |
| `stats(User $user, string $disk, ?string $folder, bool $recursive)` | Thống kê số file và tổng dung lượng. |
| `normalizePath(string $path)` | Chuẩn hóa path, chặn `..` và path rỗng. |
| `authorizePath(User $user, string $path)` | Kiểm tra user được truy cập path (owner hoặc manage files/users). |

### StorageService (`app/Services/Storage/StorageService.php`)

Dùng khi **đã có path/disk** (controller, command, job): lưu file, lấy URL, metadata, xóa.

| Method | Mô tả |
|--------|--------|
| `storePublic(UploadedFile $file, string $path, ?string $disk)` | Lưu file public; trả về full path. |
| `storePrivate(UploadedFile $file, string $path, ?string $disk)` | Lưu file private. |
| `storeAvatar(UploadedFile $file, int $userId, ?string $disk)` | Lưu avatar vào `avatars/{userId}/unique.jpg`. |
| `storeUserDocument(UploadedFile $file, int $userId, ?string $disk)` | Lưu tài liệu vào `documents/users/{userId}/...`. |
| `getPublicUrl(string $path, ?string $disk)` | URL public (disk public). |
| `getTemporaryUrl(string $path, int $expiration, ?string $disk)` | URL tạm (S3 signed; local trả route download). |
| `getMetadata(string $path, ?string $disk)` | Mảng metadata (path, size, mime, last_modified, ...). |
| `delete(string $path, ?string $disk)` | Xóa file. |
| `deleteDirectory(string $path, ?string $disk)` | Xóa thư mục đệ quy. |
| `exists`, `size`, `mimeType`, `copy`, `move` | Thao tác cơ bản trên file. |

### FileHelper (`app/Helpers/FileHelper.php`)

| Method | Mô tả |
|--------|--------|
| `formatBytes(int $bytes)` | Ví dụ: `1024` → `"1.00 KB"`. |
| `sanitizeFilename(string $filename)` | Chỉ giữ chữ, số, `_`, `-`, dấu chấm. |
| `getSafeExtension(string $filename)` | Extension chữ thường, chỉ [a-z0-9]. |

### StoragePath (`app/Helpers/StoragePath.php`)

Chỉ **tạo chuỗi path**, không ghi disk.

| Method | Ví dụ |
|--------|--------|
| `StoragePath::avatar($userId)` | `avatars/1` |
| `StoragePath::avatar($userId, 'x.jpg')` | `avatars/1/x.jpg` |
| `StoragePath::userDocument($userId)` | `documents/users/1` |
| `StoragePath::upload($category)` | `uploads/{category}` |
| `StoragePath::temp()` | `temp` |
| `StoragePath::export($category)` | `exports/{category}` |
| `StoragePath::import($category)` | `imports/{category}` |
| `StoragePath::uniqueFilename('file.pdf', 'doc')` | `doc_file_1234567890_abc12def.pdf` |

---

## API Endpoints

Base: `http://localhost:8000/api/v1/files`. Tất cả cần `Authorization: Bearer <token>` và `Accept: application/json`.

| Method | Endpoint | Mô tả |
|--------|----------|--------|
| POST | `/files/upload` | Upload 1 file (form: `file`, `disk`, `folder`) |
| POST | `/files/upload-multiple` | Upload nhiều file (`files[]`, `disk`, `folder`) |
| GET | `/files/download?disk=...&path=...` | Download file (stream) |
| DELETE | `/files` | Xóa file (body JSON: `disk`, `path`) |
| GET | `/files/list?disk=...&folder=...&recursive=&limit=` | Liệt kê file |
| GET | `/files/stats?disk=...&folder=...&recursive=` | Thống kê dung lượng |

### Demo cURL nhanh

**Upload một file:**

```bash
curl -X POST "http://localhost:8000/api/v1/files/upload" \
  -H "Authorization: Bearer YOUR_TOKEN" \
  -H "Accept: application/json" \
  -F "disk=private" \
  -F "folder=docs" \
  -F "file=@/path/to/document.pdf"
```

**Liệt kê file:**

```bash
curl -X GET "http://localhost:8000/api/v1/files/list?disk=private&folder=docs&limit=50" \
  -H "Authorization: Bearer YOUR_TOKEN" \
  -H "Accept: application/json"
```

**Download:**

```bash
curl -X GET "http://localhost:8000/api/v1/files/download?disk=private&path=users/1/docs/file_123.pdf" \
  -H "Authorization: Bearer YOUR_TOKEN" \
  -o file_123.pdf
```

**Xóa file:**

```bash
curl -X DELETE "http://localhost:8000/api/v1/files" \
  -H "Authorization: Bearer YOUR_TOKEN" \
  -H "Content-Type: application/json" \
  -d '{"disk":"private","path":"users/1/docs/file_123.pdf"}'
```

---

## Demo: gọi từ code Laravel

### 1. Upload avatar (trong controller)

Đã dùng trong `ProfileController` và `UserController`:

```php
use App\Services\Storage\StorageService;

// Trong controller, inject StorageService
public function __construct(
    protected StorageService $storageService
) {}

// Upload avatar (ghi đè avatar cũ nếu có)
if ($currentUser->avatar) {
    $this->storageService->delete($currentUser->avatar, config('constants.uploads.avatar_disk'));
}
$avatarPath = $this->storageService->storeAvatar($request->file('avatar'), $user->id);
$this->userService->updateAvatar($user->id, $avatarPath);
```

### 2. Lưu tài liệu private theo user

```php
use App\Services\Storage\StorageService;

$path = $this->storageService->storeUserDocument($request->file('document'), $user->id);
// Lưu $path vào DB nếu cần (vd: bảng user_documents)
```

### 3. Lưu file public với path tùy chọn

```php
use App\Helpers\StoragePath;
use App\Services\Storage\StorageService;

$folder = StoragePath::upload('contracts');
$fullPath = $this->storageService->storePublic($request->file('file'), $folder);
// $fullPath = uploads/contracts/name_1234567890_abc12.pdf
$url = $this->storageService->getPublicUrl($fullPath);
```

### 4. Lấy URL tạm cho file private (vd. S3)

```php
$url = $this->storageService->getTemporaryUrl($path, 3600); // 1 giờ
// Local disk: trả route download thay vì signed URL
```

### 5. Lấy metadata file (command, cron, báo cáo)

```php
use App\Services\Storage\StorageService;

$meta = $this->storageService->getMetadata('users/1/docs/file.pdf', 'private');
if ($meta) {
    echo $meta['size_human'];   // "125.50 KB"
    echo $meta['mime_type'];   // "application/pdf"
    echo $meta['last_modified'];
}
```

### 6. Upload/list/download qua FileManagerService (có user + permission)

```php
use App\Services\Storage\FileManagerService;

// Upload (user được scope vào users/{id}/... nếu không có manage files)
$metadata = $this->fileManagerService->upload(
    $request->user(),
    $request->file('file'),
    'private',
    'docs/contracts'
);

// List
$files = $this->fileManagerService->list($request->user(), 'private', 'docs', true, 100);

// Download (đã check authorizePath)
return $this->fileManagerService->download($request->user(), 'private', $path);
```

### 7. Dùng Helper path và format

```php
use App\Helpers\StoragePath;
use App\Helpers\FileHelper;

$avatarDir = StoragePath::avatar($userId);           // avatars/1
$exportPath = StoragePath::export('reports');        // exports/reports
$filename = StoragePath::uniqueFilename('báo cáo.xlsx', 'report');
$humanSize = FileHelper::formatBytes(1024 * 1024);    // "1.00 MB"
$safeName = FileHelper::sanitizeFilename('Tệp có dấu & ký tự.docx'); // T_p_c_d_u___k_t_.docx
```

### 8. Export file tạm rồi xóa (vd. queue job)

```php
use App\Helpers\StoragePath;
use App\Services\Storage\StorageService;

$path = StoragePath::export('orders') . '/' . StoragePath::uniqueFilename('orders.csv');
// Ghi nội dung vào disk (hoặc dùng storePublic/storePrivate với nội dung)
Storage::disk('private')->put($path, $csvContent);
// Gửi link tạm hoặc đính kèm email...
$this->storageService->delete($path, 'private');
```

---

## Bảo mật

- **API file**: Mọi route trong group `auth:sanctum`; permission `upload files`, `view files`, `delete files` (hoặc `manage files` / `manage users`).
- **Path**: `FileManagerService::authorizePath()` giới hạn user thường chỉ truy cập `users/{id}/...`.
- **Path traversal**: `normalizePath()` chặn `..` và path rỗng.
- **Validation**: Kích thước, MIME, số lượng file theo config; avatar theo `constants.uploads`.

Sau khi thêm/sửa permission, chạy:

```bash
php artisan db:seed --class=RolePermissionSeeder
```

---

## Khuyến nghị

1. **Tổ chức folder**: `users/{id}/documents`, `users/{id}/exports`, v.v.
2. **Mặc định private**: Chỉ dùng disk public khi cần URL công khai.
3. **Không ghép path thủ công**: Dùng `StoragePath` và service đã chuẩn hóa path.
4. **File lớn / xử lý hậu kỳ**: Đẩy upload hoặc xử lý vào queue.
5. **File từ user**: Cân nhắc quét virus trước khi lưu hoặc mở.
6. **Dung lượng**: Dùng API stats + monitoring để tránh đầy disk.

---

## Xử lý lỗi thường gặp

| Lỗi | Nguyên nhân thường gặp | Cách xử lý |
|-----|------------------------|------------|
| 422 khi upload | Vượt size, MIME không cho phép, vượt số file | Kiểm tra `.env` và `config/constants.php`; `php artisan config:clear` |
| 403 | Thiếu permission hoặc path không thuộc user | Gán role/permission; kiểm tra path `users/{auth_id}/...` |
| 404 download/delete | Sai disk/path hoặc file đã xóa | Gọi list trước để lấy path chính xác |
| Không ghi được local | Quyền thư mục | `chmod -R 775 storage bootstrap/cache`; với Docker chạy trong container |

---

## Tài liệu liên quan

- [API Response & Errors](API_RESPONSE_AND_ERRORS.md)
- [Security](SECURITY.md)
- [Swagger / OpenAPI](SWAGGER_USAGE.md)
- Route API: `routes/api/v1/routes.php`
- Service: `app/Services/Storage/FileManagerService.php`, `app/Services/Storage/StorageService.php`
- Helper: `app/Helpers/FileHelper.php`, `app/Helpers/StoragePath.php`
