<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Requests\File\FileListRequest;
use App\Http\Requests\File\FilePathRequest;
use App\Http\Requests\File\UploadFileRequest;
use App\Http\Requests\File\UploadMultipleFilesRequest;
use App\Services\Storage\FileManagerService;
use Illuminate\Http\JsonResponse;
use Symfony\Component\HttpFoundation\StreamedResponse;

class FileController extends ApiController
{
    public function __construct(
        protected FileManagerService $fileManagerService
    ) {
    }

    public function upload(UploadFileRequest $request): JsonResponse
    {
        $this->authorizeAbility($request->user(), 'upload files');

        $disk = (string) $request->input('disk', config('constants.files.default_disk', 'private'));
        $metadata = $this->fileManagerService->upload(
            $request->user(),
            $request->file('file'),
            $disk,
            $request->input('folder')
        );

        return $this->successResponse($metadata, __('messages.success.file_uploaded'), 201);
    }

    public function uploadMultiple(UploadMultipleFilesRequest $request): JsonResponse
    {
        $this->authorizeAbility($request->user(), 'upload files');

        $disk = (string) $request->input('disk', config('constants.files.default_disk', 'private'));
        $files = $request->file('files', []);

        $uploaded = $this->fileManagerService->uploadMany(
            $request->user(),
            $files,
            $disk,
            $request->input('folder')
        );

        return $this->successResponse([
            'count' => count($uploaded),
            'files' => $uploaded,
        ], __('messages.success.files_uploaded'), 201);
    }

    public function download(FilePathRequest $request): StreamedResponse
    {
        $this->authorizeAbility($request->user(), 'view files');

        $disk = (string) $request->input('disk', config('constants.files.default_disk', 'private'));

        return $this->fileManagerService->download(
            $request->user(),
            $disk,
            $request->string('path')->toString()
        );
    }

    public function delete(FilePathRequest $request): JsonResponse
    {
        $this->authorizeAbility($request->user(), 'delete files');

        $disk = (string) $request->input('disk', config('constants.files.default_disk', 'private'));
        $deleted = $this->fileManagerService->delete(
            $request->user(),
            $disk,
            $request->string('path')->toString()
        );

        if (! $deleted) {
            return $this->notFoundResponse(__('messages.errors.file_not_found'));
        }

        return $this->successResponse(null, __('messages.success.file_deleted'));
    }

    public function list(FileListRequest $request): JsonResponse
    {
        $this->authorizeAbility($request->user(), 'view files');

        $disk = (string) $request->input('disk', config('constants.files.default_disk', 'private'));
        $files = $this->fileManagerService->list(
            $request->user(),
            $disk,
            $request->input('folder'),
            (bool) $request->boolean('recursive', false),
            (int) $request->input('limit', 100)
        );

        return $this->successResponse([
            'count' => count($files),
            'files' => $files,
        ], __('messages.success.files_retrieved'));
    }

    public function stats(FileListRequest $request): JsonResponse
    {
        $this->authorizeAbility($request->user(), 'view files');

        $disk = (string) $request->input('disk', config('constants.files.default_disk', 'private'));
        $stats = $this->fileManagerService->stats(
            $request->user(),
            $disk,
            $request->input('folder'),
            (bool) $request->boolean('recursive', true)
        );

        return $this->successResponse($stats, __('messages.success.file_stats_retrieved'));
    }

    private function authorizeAbility($user, string $permission): void
    {
        if (
            ! $user->can($permission) &&
            ! $user->can('manage files') &&
            ! $user->can('manage users')
        ) {
            abort(403, __('messages.errors.unauthorized_action'));
        }
    }
}
