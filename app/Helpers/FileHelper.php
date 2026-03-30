<?php

namespace App\Helpers;

class FileHelper
{
    /**
     * Chuyển số byte sang chuỗi dễ đọc (B, KB, MB, GB, TB).
     */
    public static function formatBytes(int $bytes): string
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

    /**
     * Làm sạch tên file để tránh path traversal và ký tự đặc biệt.
     * Chỉ giữ chữ, số, gạch ngang, gạch dưới, dấu chấm.
     */
    public static function sanitizeFilename(string $filename): string
    {
        $name = pathinfo($filename, PATHINFO_FILENAME);
        $ext = pathinfo($filename, PATHINFO_EXTENSION);
        $name = preg_replace('/[^A-Za-z0-9_\-]/', '_', $name ?? 'file');
        $name = trim($name, '_') ?: 'file';
        $ext = preg_replace('/[^A-Za-z0-9]/', '', $ext ?? '');

        return $ext !== '' ? $name . '.' . $ext : $name;
    }

    /**
     * Lấy extension an toàn từ tên file (chữ thường, chỉ chữ/số).
     */
    public static function getSafeExtension(string $filename): string
    {
        $ext = strtolower(pathinfo($filename, PATHINFO_EXTENSION));

        return preg_replace('/[^a-z0-9]/', '', $ext ?? '') ?: '';
    }
}
