<?php

declare(strict_types=1);

namespace App\Http\Resources;

use Illuminate\Notifications\DatabaseNotification;

final class NotificationResource extends ApiResource
{
    /** @return array<string, mixed> */
    public function toArray($request): array
    {
        /** @var DatabaseNotification $notification */
        $notification = $this->resource;

        return [
            'id' => $notification->id,
            'type' => $notification->data['type'] ?? null,
            'message' => $notification->data['message'] ?? '',
            'request_id' => $notification->data['request_id'] ?? null,
            'read_at' => $notification->read_at?->toISOString(),
            'created_at' => $notification->created_at?->toISOString(),
        ];
    }
}
