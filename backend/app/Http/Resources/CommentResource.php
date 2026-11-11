<?php

declare(strict_types=1);

namespace App\Http\Resources;

use App\Models\Comment;

final class CommentResource extends ApiResource
{
    /** @return array<string, mixed> */
    public function toArray($request): array
    {
        /** @var Comment $comment */
        $comment = $this->resource;

        return [
            'id' => $comment->id,
            'body' => $comment->body,
            'user' => $this->relation('user', UserResource::class),
            'created_at' => $comment->created_at?->toISOString(),
        ];
    }
}
