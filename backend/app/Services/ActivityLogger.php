<?php

declare(strict_types=1);

namespace App\Services;

use App\Enums\ActivityEvent;
use App\Enums\RequestStatus;
use App\Models\ActivityLog;
use App\Models\ServiceRequest;
use App\Models\User;

final class ActivityLogger
{
    /** @param array<string, mixed> $metadata */
    public function log(
        ServiceRequest $serviceRequest,
        ?User $actor,
        ActivityEvent $event,
        ?RequestStatus $from = null,
        ?RequestStatus $to = null,
        ?string $comment = null,
        array $metadata = [],
    ): ActivityLog {
        return ActivityLog::query()->create([
            'service_request_id' => $serviceRequest->id,
            'actor_id' => $actor?->id,
            'event' => $event,
            'from_status' => $from,
            'to_status' => $to,
            'comment' => $comment,
            'metadata' => $metadata === [] ? null : $metadata,
        ]);
    }
}
