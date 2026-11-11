<?php

declare(strict_types=1);

namespace App\Http\Resources;

use App\Models\ActivityLog;

final class ActivityLogResource extends ApiResource
{
    /** @return array<string, mixed> */
    public function toArray($request): array
    {
        /** @var ActivityLog $activity */
        $activity = $this->resource;

        return [
            'id' => $activity->id,
            'event' => $activity->event->value,
            'from_status' => $activity->from_status?->value,
            'to_status' => $activity->to_status?->value,
            'comment' => $activity->comment,
            'metadata' => $activity->metadata,
            'actor' => $this->relation('actor', UserResource::class),
            'created_at' => $activity->created_at->toISOString(),
        ];
    }
}
