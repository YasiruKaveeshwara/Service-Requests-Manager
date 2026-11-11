<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use Illuminate\Http\Response;
use Symfony\Component\HttpFoundation\BinaryFileResponse;

final class DocsController
{
    public function ui(): Response
    {
        return response()->view('scalar');
    }

    public function spec(): BinaryFileResponse
    {
        return response()->file(base_path('docs/openapi.yaml'), ['Content-Type' => 'application/yaml']);
    }
}
