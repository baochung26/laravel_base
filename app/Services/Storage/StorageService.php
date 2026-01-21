<?php

namespace App\Services\Storage;

use App\Helpers\StoragePath;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class StorageService
{
    /**
     * Store public file.
     */
    public function storePublic(UploadedFile $file, string $path, ?string $disk = null): string
    {
        $disk = $disk ?? $this->getPublicDisk();
        $filename = StoragePath::uniqueFilename($file->getClientOriginalName());
        $fullPath = rtrim($path, '/') . '/' . $filename;

        Storage::disk($disk)->putFileAs(
            dirname($fullPath),
            $file,
            basename($fullPath),
            'public'
        );

        return $fullPath;
    }

    /**
     * Store private file.
     */
    public function storePrivate(UploadedFile $file, string $path, ?string $disk = null): string
    {
        $disk = $disk ?? $this->getPrivateDisk();
        $filename = StoragePath::uniqueFilename($file->getClientOriginalName());
        $fullPath = rtrim($path, '/') . '/' . $filename;

        Storage::disk($disk)->putFileAs(
            dirname($fullPath),
            $file,
            basename($fullPath),
            'private'
        );

        return $fullPath;
    }

    /**
     * Get public URL for file.
     */
    public function getPublicUrl(string $path, ?string $disk = null): ?string
    {
        $disk = $disk ?? $this->getPublicDisk();

        if (! Storage::disk($disk)->exists($path)) {
            return null;
        }

        return Storage::disk($disk)->url($path);
    }

    /**
     * Get temporary signed URL for private file.
     */
    public function getTemporaryUrl(string $path, int $expiration = 3600, ?string $disk = null): ?string
    {
        $disk = $disk ?? $this->getPrivateDisk();

        if (! Storage::disk($disk)->exists($path)) {
            return null;
        }

        try {
            return Storage::disk($disk)->temporaryUrl($path, now()->addSeconds($expiration));
        } catch (\Exception $e) {
            // For local storage, return a download route instead
            if ($disk === 'private' || $disk === 'local') {
                return route('storage.download', ['path' => $path]);
            }

            throw $e;
        }
    }

    /**
     * Delete file.
     */
    public function delete(string $path, ?string $disk = null): bool
    {
        $disk = $disk ?? config('filesystems.default');

        if (! Storage::disk($disk)->exists($path)) {
            return false;
        }

        return Storage::disk($disk)->delete($path);
    }

    /**
     * Delete directory recursively.
     */
    public function deleteDirectory(string $path, ?string $disk = null): bool
    {
        $disk = $disk ?? config('filesystems.default');

        if (! Storage::disk($disk)->exists($path)) {
            return false;
        }

        return Storage::disk($disk)->deleteDirectory($path);
    }

    /**
     * Check if file exists.
     */
    public function exists(string $path, ?string $disk = null): bool
    {
        $disk = $disk ?? config('filesystems.default');
        return Storage::disk($disk)->exists($path);
    }

    /**
     * Get file size.
     */
    public function size(string $path, ?string $disk = null): int
    {
        $disk = $disk ?? config('filesystems.default');

        if (! Storage::disk($disk)->exists($path)) {
            return 0;
        }

        return Storage::disk($disk)->size($path);
    }

    /**
     * Get file MIME type.
     */
    public function mimeType(string $path, ?string $disk = null): ?string
    {
        $disk = $disk ?? config('filesystems.default');

        if (! Storage::disk($disk)->exists($path)) {
            return null;
        }

        return Storage::disk($disk)->mimeType($path);
    }

    /**
     * Copy file.
     */
    public function copy(string $from, string $to, ?string $disk = null): bool
    {
        $disk = $disk ?? config('filesystems.default');

        if (! Storage::disk($disk)->exists($from)) {
            return false;
        }

        return Storage::disk($disk)->copy($from, $to);
    }

    /**
     * Move file.
     */
    public function move(string $from, string $to, ?string $disk = null): bool
    {
        $disk = $disk ?? config('filesystems.default');

        if (! Storage::disk($disk)->exists($from)) {
            return false;
        }

        return Storage::disk($disk)->move($from, $to);
    }

    /**
     * Store avatar (public).
     */
    public function storeAvatar(UploadedFile $file, int $userId, ?string $disk = null): string
    {
        $path = StoragePath::avatar($userId);
        return $this->storePublic($file, $path, $disk);
    }

    /**
     * Store user document (private).
     */
    public function storeUserDocument(UploadedFile $file, int $userId, ?string $disk = null): string
    {
        $path = StoragePath::userDocument($userId);
        return $this->storePrivate($file, $path, $disk);
    }

    /**
     * Get public disk.
     */
    protected function getPublicDisk(): string
    {
        $defaultDisk = config('filesystems.default');

        // Use S3 public or local public disk
        if ($defaultDisk === 's3') {
            return 's3';
        }

        return 'public';
    }

    /**
     * Get private disk.
     */
    protected function getPrivateDisk(): string
    {
        $defaultDisk = config('filesystems.default');

        // Use S3 private or local private disk
        if ($defaultDisk === 's3') {
            return 's3_private';
        }

        return 'private';
    }
}
