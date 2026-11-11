<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api;

use App\Http\Requests\StoreCommentRequest;
use App\Http\Resources\CommentResource;
use App\Models\ServiceRequest;
use App\Services\CommentService;
use Illuminate\Http\JsonResponse;

final class CommentController
{
    public function store(StoreCommentRequest $request, ServiceRequest $serviceRequest, CommentService $comments): JsonResponse
    {
        $comment = $comments->add($serviceRequest, $request->user(), $request->validated('body'));

        return (new CommentResource($comment->load('user')))->response()->setStatusCode(201);
    }
}
