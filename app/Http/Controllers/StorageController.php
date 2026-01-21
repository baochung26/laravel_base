<?php

namespace App\Http\Controllers;

use App\Services\Storage\StorageService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Storage;

class StorageController extends Controller
{
    public function __construct(
        protected StorageService $storageService
    ) {
    }

    /**
     * Download private file.
     */
    public function download(Request $request, string $path): Response|JsonResponse
    {
        // Validate user has permission to access this file
        // Add your authorization logic here

        $disk = $this->getDiskForPath($path);

        if (! Storage::disk($disk)->exists($path)) {
            return response()->json([
                'success' => false,
                'message' => 'File not found',
            ], 404);
        }

        return Storage::disk($disk)->download($path);
    }

    /**
     * Get temporary URL for private file.
     */
    public function getTemporaryUrl(Request $request, string $path): JsonResponse
    {
        // Validate user has permission to access this file
        // Add your authorization logic here

        $expiration = (int) $request->get('expiration', 3600); // Default 1 hour
        $url = $this->storageService->getTemporaryUrl($path, $expiration);

        if (! $url) {
            return response()->json([
                'success' => false,
                'message' => 'File not found',
            ], 404);
        }

        return response()->json([
            'success' => true,
            'url' => $url,
            'expires_at' => now()->addSeconds($expiration)->toISOString(),
        ]);
    }

    /**
     * Determine disk based on path or request.
     */
    protected function getDiskForPath(string $path): string
    {
        // Check if path is in private directory
        if (str_starts_with($path, 'documents/') || str_starts_with($path, 'private/')) {
            return config('filesystems.default') === 's3' ? 's3_private' : 'private';
        }

        return config('filesystems.default') === 's3' ? 's3' : 'public';
    }
}
