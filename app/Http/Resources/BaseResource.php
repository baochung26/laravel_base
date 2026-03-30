<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Storage;

abstract class BaseResource extends JsonResource
{
    /**
     * ISO-8601 time formatting helper.
     */
    protected function iso(?Carbon $value): ?string
    {
        return $value?->toISOString();
    }

    /**
     * Storage URL helper. Uses the given disk so URLs work with local, S3, CloudFront, etc.
     *
     * @param  string|null  $path  Path relative to the disk (e.g. "avatars/abc.jpg")
     * @param  string|null  $disk  Disk name from config (e.g. config('constants.uploads.avatar_disk')). Defaults to "public".
     */
    protected function storageUrl(?string $path, ?string $disk = null): ?string
    {
        if (! $path) {
            return null;
        }

        $disk = $disk ?? 'public';

        return Storage::disk($disk)->url(ltrim($path, '/'));
    }
}
