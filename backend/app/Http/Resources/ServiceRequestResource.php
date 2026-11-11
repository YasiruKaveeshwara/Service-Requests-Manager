<?php

declare(strict_types=1);

namespace App\Http\Resources;

use App\Models\ServiceRequest;
use App\Services\RequestAbilities;

final class ServiceRequestResource extends ApiResource
{
    /** @return array<string, mixed> */
    public function toArray($request): array
    {
        /** @var ServiceRequest $serviceRequest */
        $serviceRequest = $this->resource;

        return [
            'id' => $serviceRequest->id,
            'title' => $serviceRequest->title,
            'description' => $serviceRequest->description,
            'category' => $serviceRequest->category->value,
            'category_label' => $serviceRequest->category->label(),
            'priority' => $serviceRequest->priority->value,
            'priority_label' => $serviceRequest->priority->label(),
            'status' => $serviceRequest->status->value,
            'status_label' => $serviceRequest->status->label(),
            'requester' => $this->relation('requester', UserResource::class),
            'assignee' => $this->relation('assignee', UserResource::class),
            'submitted_at' => $serviceRequest->submitted_at?->toISOString(),
            'completed_at' => $serviceRequest->completed_at?->toISOString(),
            'created_at' => $serviceRequest->created_at?->toISOString(),
            'updated_at' => $serviceRequest->updated_at?->toISOString(),
            'comments' => CommentResource::collection($this->whenLoaded('comments')),
            'activity' => ActivityLogResource::collection($this->whenLoaded('activityLogs')),
            'abilities' => RequestAbilities::for($request->user(), $serviceRequest),
        ];
    }
}
