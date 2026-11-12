<?php

declare(strict_types=1);

namespace App\Services;

use App\Enums\ActivityEvent;
use App\Exceptions\WorkflowException;
use App\Models\Comment;
use App\Models\ServiceRequest;
use App\Models\User;
use Illuminate\Support\Facades\DB;

final class CommentService
{
    public function __construct(private readonly ActivityLogger $activity) {}

    public function add(ServiceRequest $serviceRequest, User $actor, string $body): Comment
    {
        return DB::transaction(function () use ($serviceRequest, $actor, $body): Comment {
            $locked = ServiceRequest::query()->lockForUpdate()->findOrFail($serviceRequest->getKey());

            if ($locked->status->isFinal()) {
                throw WorkflowException::because('Comments are closed because this request is '.strtolower($locked->status->label()).'.');
            }

            $comment = Comment::query()->forceCreate([
                'service_request_id' => $locked->id,
                'user_id' => $actor->id,
                'body' => $body,
            ]);

            $this->activity->log($locked, $actor, ActivityEvent::Commented, comment: $body, metadata: ['comment_id' => $comment->id]);

            return $comment;
        });
    }
}
