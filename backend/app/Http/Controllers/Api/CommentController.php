<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api;

use App\Http\Requests\StoreCommentRequest;
use App\Http\Resources\CommentResource;
use App\Models\ServiceRequest;
use App\Services\CommentService;
use Illuminate\Http\JsonResponse;

/**
 * Exposes request comment creation after policy and input validation.
 */
final class CommentController
{
    /** Add a comment and append the corresponding audit event. */
    public function store(StoreCommentRequest $request, ServiceRequest $serviceRequest, CommentService $comments): JsonResponse
    {
        $comment = $comments->add($serviceRequest, $request->user(), $request->validated('body'));

        return (new CommentResource($comment->load('user')))->response()->setStatusCode(201);
    }
}
