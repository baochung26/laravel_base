<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Support\Carbon;

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
     * Storage URL helper.
     */
    protected function storageUrl(?string $path): ?string
    {
        return $path ? url('storage/' . ltrim($path, '/')) : null;
    }
}
