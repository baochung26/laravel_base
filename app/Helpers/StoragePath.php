<?php

namespace App\Helpers;

class StoragePath
{
    /**
     * Directory conventions for different file types.
     */
    public const AVATARS = 'avatars';
    public const DOCUMENTS = 'documents';
    public const UPLOADS = 'uploads';
    public const TEMP = 'temp';
    public const BACKUPS = 'backups';
    public const EXPORTS = 'exports';
    public const IMPORTS = 'imports';
    public const THUMBNAILS = 'thumbnails';
    public const IMAGES = 'images';
    public const VIDEOS = 'videos';
    public const AUDIOS = 'audios';
    public const FILES = 'files';

    /**
     * Get path for user avatar.
     */
    public static function avatar(int $userId, ?string $filename = null): string
    {
        $path = self::AVATARS . '/' . $userId;

        if ($filename !== null) {
            $path .= '/' . $filename;
        }

        return $path;
    }

    /**
     * Get path for user documents.
     */
    public static function userDocument(int $userId, ?string $filename = null): string
    {
        $path = self::DOCUMENTS . '/users/' . $userId;

        if ($filename !== null) {
            $path .= '/' . $filename;
        }

        return $path;
    }

    /**
     * Get path for general uploads.
     */
    public static function upload(string $category, ?string $filename = null): string
    {
        $path = self::UPLOADS . '/' . $category;

        if ($filename !== null) {
            $path .= '/' . $filename;
        }

        return $path;
    }

    /**
     * Get path for temporary files.
     */
    public static function temp(?string $filename = null): string
    {
        $path = self::TEMP;

        if ($filename !== null) {
            $path .= '/' . $filename;
        }

        return $path;
    }

    /**
     * Get path for exports.
     */
    public static function export(string $category, ?string $filename = null): string
    {
        $path = self::EXPORTS . '/' . $category;

        if ($filename !== null) {
            $path .= '/' . $filename;
        }

        return $path;
    }

    /**
     * Get path for imports.
     */
    public static function import(string $category, ?string $filename = null): string
    {
        $path = self::IMPORTS . '/' . $category;

        if ($filename !== null) {
            $path .= '/' . $filename;
        }

        return $path;
    }

    /**
     * Get path for thumbnails.
     */
    public static function thumbnail(string $originalPath, ?string $filename = null): string
    {
        $path = self::THUMBNAILS . '/' . dirname($originalPath);

        if ($filename !== null) {
            $path .= '/' . $filename;
        }

        return $path;
    }

    /**
     * Get path for images.
     */
    public static function image(string $category, ?string $filename = null): string
    {
        $path = self::IMAGES . '/' . $category;

        if ($filename !== null) {
            $path .= '/' . $filename;
        }

        return $path;
    }

    /**
     * Generate unique filename with extension.
     */
    public static function uniqueFilename(string $originalFilename, ?string $prefix = null): string
    {
        $extension = pathinfo($originalFilename, PATHINFO_EXTENSION);
        $name = pathinfo($originalFilename, PATHINFO_FILENAME);
        $filename = $prefix ? $prefix . '_' . $name : $name;
        $filename .= '_' . time() . '_' . \Illuminate\Support\Str::random(8);

        return $filename . '.' . $extension;
    }
}
