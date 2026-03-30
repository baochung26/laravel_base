<?php

namespace App\Http\Controllers\Api\V1;

use Illuminate\Http\Response;
use Illuminate\View\View;
use Symfony\Component\HttpFoundation\BinaryFileResponse;

class DocsController extends ApiController
{
    public function openapi(): BinaryFileResponse
    {
        $path = base_path('docs/openapi.yaml');

        if (! file_exists($path)) {
            abort(404, 'OpenAPI specification not found.');
        }

        return response()->file($path, [
            'Content-Type' => 'application/x-yaml; charset=UTF-8',
        ]);
    }

    public function docs(): View
    {
        return view('docs.swagger', [
            'specUrl' => route('v1.openapi'),
        ]);
    }
}
