<?php

namespace App\Services\Storage;

use App\Models\User;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Symfony\Component\HttpFoundation\StreamedResponse;

class FileManagerService
{
    public function upload(User $user, UploadedFile $file, string $disk, ?string $folder = null): array
    {
        $folder = $this->resolveFolder($user, $folder);
        $this->authorizePath($user, $folder);
        $filename = $this->generateFilename($file);
        $path = trim($folder . '/' . $filename, '/');

        Storage::disk($disk)->putFileAs(
            dirname($path),
            $file,
            basename($path),
            $disk === 'public' ? 'public' : 'private'
        );

        return $this->fileMetadata($disk, $path);
    }

    public function uploadMany(User $user, array $files, string $disk, ?string $folder = null): array
    {
        $uploaded = [];

        foreach ($files as $file) {
            $uploaded[] = $this->upload($user, $file, $disk, $folder);
        }

        return $uploaded;
    }

    public function download(User $user, string $disk, string $path): StreamedResponse
    {
        $path = $this->normalizePath($path);
        $this->authorizePath($user, $path);

        if (! Storage::disk($disk)->exists($path)) {
            abort(404, 'File not found.');
        }

        return Storage::disk($disk)->download($path);
    }

    public function delete(User $user, string $disk, string $path): bool
    {
        $path = $this->normalizePath($path);
        $this->authorizePath($user, $path);

        if (! Storage::disk($disk)->exists($path)) {
            return false;
        }

        return Storage::disk($disk)->delete($path);
    }

    public function list(User $user, string $disk, ?string $folder = null, bool $recursive = false, int $limit = 100): array
    {
        $folder = $this->resolveFolder($user, $folder);
        $this->authorizePath($user, $folder);

        $fs = Storage::disk($disk);
        $files = $recursive ? $fs->allFiles($folder) : $fs->files($folder);
        $files = array_slice($files, 0, max(1, min($limit, 500)));

        return array_map(fn (string $path) => $this->fileMetadata($disk, $path), $files);
    }

    public function stats(User $user, string $disk, ?string $folder = null, bool $recursive = true): array
    {
        $folder = $this->resolveFolder($user, $folder);
        $this->authorizePath($user, $folder);

        $fs = Storage::disk($disk);
        $files = $recursive ? $fs->allFiles($folder) : $fs->files($folder);

        $totalSize = 0;
        foreach ($files as $path) {
            $totalSize += (int) $fs->size($path);
        }

        return [
            'disk' => $disk,
            'folder' => $folder,
            'total_files' => count($files),
            'total_size_bytes' => $totalSize,
            'total_size_human' => $this->formatBytes($totalSize),
        ];
    }

    public function authorizePath(User $user, string $path): void
    {
        if ($user->can('manage files') || $user->can('manage users')) {
            return;
        }

        $ownerPrefix = 'users/' . $user->id . '/';
        if (str_starts_with($path . '/', $ownerPrefix)) {
            return;
        }

        abort(403, 'You are not authorized to access this path.');
    }

    public function normalizePath(string $path): string
    {
        $path = str_replace('\\', '/', trim($path));
        $path = trim($path, '/');

        if ($path === '') {
            abort(422, 'Path cannot be empty.');
        }

        $segments = array_filter(explode('/', $path), fn ($segment) => $segment !== '');
        foreach ($segments as $segment) {
            if ($segment === '.' || $segment === '..') {
                abort(422, 'Invalid path.');
            }
        }

        return implode('/', $segments);
    }

    private function resolveFolder(User $user, ?string $folder): string
    {
        $base = 'users/' . $user->id;

        if ($folder === null || trim($folder) === '') {
            return $base;
        }

        $sub = $this->normalizePath($folder);

        if ($user->can('manage files') || $user->can('manage users')) {
            return $sub;
        }

        return $base . '/' . $sub;
    }

    private function fileMetadata(string $disk, string $path): array
    {
        $fs = Storage::disk($disk);

        return [
            'disk' => $disk,
            'path' => $path,
            'name' => basename($path),
            'directory' => dirname($path) === '.' ? '' : dirname($path),
            'size_bytes' => (int) $fs->size($path),
            'size_human' => $this->formatBytes((int) $fs->size($path)),
            'mime_type' => $fs->mimeType($path),
            'last_modified' => date(DATE_ATOM, (int) $fs->lastModified($path)),
            'url' => $disk === 'public' ? $fs->url($path) : null,
        ];
    }

    private function generateFilename(UploadedFile $file): string
    {
        $original = pathinfo($file->getClientOriginalName(), PATHINFO_FILENAME);
        $original = preg_replace('/[^A-Za-z0-9_\-]/', '_', $original ?? 'file');
        $original = trim($original, '_');
        $original = $original !== '' ? $original : 'file';

        return $original . '_' . time() . '_' . substr(bin2hex(random_bytes(4)), 0, 8) . '.' . $file->getClientOriginalExtension();
    }

    private function formatBytes(int $bytes): string
    {
        if ($bytes < 1024) {
            return $bytes . ' B';
        }

        $units = ['KB', 'MB', 'GB', 'TB'];
        $value = $bytes / 1024;
        $index = 0;

        while ($value >= 1024 && $index < count($units) - 1) {
            $value /= 1024;
            $index++;
        }

        return number_format($value, 2) . ' ' . $units[$index];
    }
}
