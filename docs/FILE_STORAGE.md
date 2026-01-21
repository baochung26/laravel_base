# File Storage Documentation

Tài liệu về File Storage với Local và S3, quy ước thư mục, và xử lý public/private files.

## 📋 Tổng quan

Hệ thống File Storage bao gồm:
- ✅ **Local Storage** - File storage trên server
- ✅ **S3 Storage** - AWS S3 storage (chỉ cần config)
- ✅ **Directory Conventions** - Quy ước thư mục thống nhất
- ✅ **Public/Private Files** - Xử lý public và private files
- ✅ **Storage Service** - Service layer để quản lý file storage

## 💾 1. Storage Configuration

### Filesystem Disks

**Location:** `config/filesystems.php`

**Disks:**
- `local` - Private local storage (`storage/app/`)
- `public` - Public local storage (`storage/app/public/`)
- `private` - Private local storage (`storage/app/private/`)
- `s3` - Public S3 storage
- `s3_private` - Private S3 storage

### Environment Variables

**Local Development:**
```env
FILESYSTEM_DISK=local
```

**Production with S3:**
```env
FILESYSTEM_DISK=s3
AWS_ACCESS_KEY_ID=your-access-key
AWS_SECRET_ACCESS_KEY=your-secret-key
AWS_DEFAULT_REGION=us-east-1
AWS_BUCKET=your-bucket-name
AWS_BUCKET_PRIVATE=your-private-bucket-name  # Optional, defaults to AWS_BUCKET
AWS_URL=https://your-bucket.s3.amazonaws.com  # Optional
AWS_ENDPOINT=  # Optional, for S3-compatible services
AWS_USE_PATH_STYLE_ENDPOINT=false
```

## 📁 2. Directory Conventions

### StoragePath Helper

**Location:** `app/Helpers/StoragePath.php`

### Directory Structure

```
storage/app/
├── avatars/              # User avatars (public)
│   └── {userId}/
│       └── {filename}
├── documents/            # Documents (private)
│   └── users/
│       └── {userId}/
│           └── {filename}
├── uploads/              # General uploads (public/private)
│   └── {category}/
│       └── {filename}
├── temp/                 # Temporary files
│   └── {filename}
├── backups/              # Backup files (private)
│   └── {category}/
├── exports/              # Exported files (private)
│   └── {category}/
├── imports/              # Import files (private)
│   └── {category}/
├── thumbnails/           # Thumbnail images (public)
│   └── {originalPath}/
├── images/               # General images (public)
│   └── {category}/
├── videos/               # Videos (public/private)
│   └── {category}/
├── audios/               # Audio files (public/private)
│   └── {category}/
└── files/                # General files (public/private)
    └── {category}/
```

### Usage Examples

```php
use App\Helpers\StoragePath;

// User avatar path
$avatarPath = StoragePath::avatar(1);  // avatars/1
$avatarPath = StoragePath::avatar(1, 'photo.jpg');  // avatars/1/photo.jpg

// User document path
$docPath = StoragePath::userDocument(1);  // documents/users/1
$docPath = StoragePath::userDocument(1, 'contract.pdf');  // documents/users/1/contract.pdf

// General upload
$uploadPath = StoragePath::upload('products', 'image.jpg');  // uploads/products/image.jpg

// Temporary file
$tempPath = StoragePath::temp('temp_file.txt');  // temp/temp_file.txt

// Export file
$exportPath = StoragePath::export('reports', 'report.xlsx');  // exports/reports/report.xlsx

// Generate unique filename
$filename = StoragePath::uniqueFilename('photo.jpg');  // photo_1234567890_abc12345.jpg
$filename = StoragePath::uniqueFilename('photo.jpg', 'user');  // user_photo_1234567890_abc12345.jpg
```

## 🔧 3. Storage Service

### StorageService

**Location:** `app/Services/Storage/StorageService.php`

### Methods

#### Store Public File

```php
use App\Services\Storage\StorageService;
use Illuminate\Http\UploadedFile;

$storageService = app(StorageService::class);

$path = $storageService->storePublic(
    $file,
    'uploads/products',
    's3'  // optional disk
);
```

#### Store Private File

```php
$path = $storageService->storePrivate(
    $file,
    'documents/users/1',
    's3_private'  // optional disk
);
```

#### Get Public URL

```php
$url = $storageService->getPublicUrl('avatars/1/photo.jpg');
// Returns: https://bucket.s3.amazonaws.com/avatars/1/photo.jpg
// Or: http://localhost:8000/storage/avatars/1/photo.jpg (local)
```

#### Get Temporary URL (Private Files)

```php
$url = $storageService->getTemporaryUrl(
    'documents/users/1/contract.pdf',
    3600  // expiration in seconds (1 hour)
);
```

#### Delete File

```php
$storageService->delete('avatars/1/old_photo.jpg');
```

#### Delete Directory

```php
$storageService->deleteDirectory('avatars/1');
```

#### Check File Exists

```php
if ($storageService->exists('avatars/1/photo.jpg')) {
    // File exists
}
```

#### Get File Size

```php
$size = $storageService->size('avatars/1/photo.jpg');  // bytes
```

#### Get MIME Type

```php
$mimeType = $storageService->mimeType('avatars/1/photo.jpg');  // image/jpeg
```

### Convenience Methods

#### Store Avatar

```php
$path = $storageService->storeAvatar($file, $userId);
// Stores in: avatars/{userId}/{unique_filename}
```

#### Store User Document

```php
$path = $storageService->storeUserDocument($file, $userId);
// Stores in: documents/users/{userId}/{unique_filename}
```

## 🌐 4. Public vs Private Files

### Public Files

**Characteristics:**
- Accessible via direct URL
- No authentication required
- Suitable for: avatars, public images, static assets

**Storage:**
- Local: `storage/app/public/`
- S3: Public S3 bucket

**Usage:**
```php
// Store public file
$path = $storageService->storePublic($file, StoragePath::avatar(1));

// Get public URL
$url = $storageService->getPublicUrl($path);
// Use $url directly in frontend
```

**URL Examples:**
- Local: `http://localhost:8000/storage/avatars/1/photo.jpg`
- S3: `https://bucket.s3.amazonaws.com/avatars/1/photo.jpg`

### Private Files

**Characteristics:**
- Require authentication
- Use temporary signed URLs or download routes
- Suitable for: documents, sensitive files, user data

**Storage:**
- Local: `storage/app/private/`
- S3: Private S3 bucket

**Usage:**
```php
// Store private file
$path = $storageService->storePrivate($file, StoragePath::userDocument(1));

// Get temporary URL (valid for 1 hour)
$url = $storageService->getTemporaryUrl($path, 3600);

// Or use download route
$url = route('storage.download', ['path' => $path]);
```

**Access:**
- API endpoint: `GET /api/v1/storage/temporary-url/{path}`
- Download route: `GET /storage/download/{path}` (requires auth)

## 🚀 5. Usage Examples

### Example 1: Upload Avatar (Public)

```php
use App\Services\Storage\StorageService;
use App\Helpers\StoragePath;

class UserController extends Controller
{
    public function uploadAvatar(Request $request, int $userId)
    {
        $request->validate([
            'avatar' => 'required|image|max:2048',
        ]);

        $storageService = app(StorageService::class);

        // Delete old avatar if exists
        $user = User::find($userId);
        if ($user->avatar) {
            $storageService->delete($user->avatar);
        }

        // Store new avatar
        $path = $storageService->storeAvatar($request->file('avatar'), $userId);

        // Update user record
        $user->update(['avatar' => $path]);

        // Get public URL
        $url = $storageService->getPublicUrl($path);

        return response()->json([
            'success' => true,
            'path' => $path,
            'url' => $url,
        ]);
    }
}
```

### Example 2: Upload Document (Private)

```php
use App\Services\Storage\StorageService;
use App\Helpers\StoragePath;

class DocumentController extends Controller
{
    public function uploadDocument(Request $request, int $userId)
    {
        $request->validate([
            'document' => 'required|file|mimes:pdf,doc,docx|max:5120',
        ]);

        $storageService = app(StorageService::class);

        // Store private document
        $path = $storageService->storeUserDocument(
            $request->file('document'),
            $userId
        );

        // Get temporary URL (valid for 24 hours)
        $url = $storageService->getTemporaryUrl($path, 86400);

        return response()->json([
            'success' => true,
            'path' => $path,
            'temporary_url' => $url,
            'expires_at' => now()->addHours(24)->toISOString(),
        ]);
    }
}
```

### Example 3: Delete File

```php
$storageService = app(StorageService::class);

// Delete single file
if ($storageService->delete($path)) {
    // File deleted successfully
}

// Delete directory
$storageService->deleteDirectory('avatars/1');
```

### Example 4: Get File Info

```php
$storageService = app(StorageService::class);

if ($storageService->exists($path)) {
    $size = $storageService->size($path);        // bytes
    $mimeType = $storageService->mimeType($path); // MIME type
    
    // Convert bytes to human-readable
    $sizeFormatted = number_format($size / 1024, 2) . ' KB';
}
```

## 🔐 6. Security & Access Control

### Private File Access

Private files require authentication. Access control can be implemented in:

1. **StorageController** (`app/Http/Controllers/StorageController.php`)
2. **Middleware** - Add authorization middleware
3. **Route** - Protect routes with `auth:sanctum`

**Example Authorization:**
```php
public function download(Request $request, string $path): Response
{
    // Check if user has permission
    if (!$this->canAccessFile($request->user(), $path)) {
        abort(403, 'Unauthorized');
    }

    // ... download logic
}

protected function canAccessFile($user, string $path): bool
{
    // Check if path belongs to user
    if (str_contains($path, "users/{$user->id}")) {
        return true;
    }

    // Add more authorization logic
    return false;
}
```

### File Validation

Always validate uploaded files:

```php
$request->validate([
    'avatar' => 'required|image|mimes:jpeg,png,jpg,gif|max:2048',
    'document' => 'required|file|mimes:pdf,doc,docx|max:5120',
]);
```

## 📝 7. Best Practices

### File Naming

✅ **DO:**
- Use unique filenames to prevent conflicts
- Use `StoragePath::uniqueFilename()` helper
- Include timestamp and random string
- Preserve original extension

❌ **DON'T:**
- Don't use original filenames directly
- Don't use predictable filenames
- Don't allow user-controlled filenames

### Directory Structure

✅ **DO:**
- Organize files by category/user
- Use consistent directory structure
- Separate public and private files
- Clean up temporary files regularly

❌ **DON'T:**
- Don't store all files in root
- Don't mix public and private files
- Don't create deep nested structures
- Don't forget to organize by date/user

### Storage Selection

✅ **DO:**
- Use local storage for development
- Use S3 for production
- Consider file size and access patterns
- Use CDN for public assets

❌ **DON'T:**
- Don't store sensitive data in public storage
- Don't forget to configure S3 properly
- Don't ignore storage costs
- Don't skip backup strategy

### Performance

✅ **DO:**
- Use appropriate storage driver
- Compress images when possible
- Generate thumbnails for large images
- Use CDN for public assets
- Cache file URLs when possible

❌ **DON'T:**
- Don't store oversized files
- Don't serve files directly from Laravel
- Don't ignore storage limits
- Don't forget to optimize images

## 🔧 8. Storage Link (Local)

For local public storage, create symbolic link:

```bash
php artisan storage:link
```

This creates a symbolic link from `public/storage` to `storage/app/public`.

## 🧪 9. Testing

### Test File Storage

```php
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use App\Services\Storage\StorageService;

Storage::fake('public');

$file = UploadedFile::fake()->image('avatar.jpg');
$storageService = app(StorageService::class);

$path = $storageService->storePublic($file, 'avatars/1');

Storage::disk('public')->assertExists($path);
```

## 📚 10. Related Documentation

- [Configuration & Environment](CONFIG_ENVIRONMENT.md#file-system-configuration)
- [Laravel Filesystem](https://laravel.com/docs/filesystem)

## 🔗 Tài liệu tham khảo

- [Laravel Filesystem](https://laravel.com/docs/filesystem)
- [AWS S3](https://aws.amazon.com/s3/)
- [S3 Best Practices](https://docs.aws.amazon.com/AmazonS3/latest/userguide/best-practices.html)
