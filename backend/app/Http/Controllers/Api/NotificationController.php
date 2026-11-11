<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api;

use App\Http\Resources\NotificationResource;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;

final class NotificationController
{
    public function index(Request $request): AnonymousResourceCollection
    {
        return NotificationResource::collection($request->user()->notifications()->paginate(15))
            ->additional(['meta' => ['unread_count' => $request->user()->unreadNotifications()->count()]]);
    }

    public function read(Request $request, string $id): NotificationResource
    {
        $notification = $request->user()->notifications()->findOrFail($id);
        $notification->markAsRead();

        return new NotificationResource($notification);
    }

    /** @return array<string, array<string, int>> */
    public function readAll(Request $request): array
    {
        $count = $request->user()->unreadNotifications()->count();
        $request->user()->unreadNotifications()->update(['read_at' => now()]);

        return ['data' => ['marked' => $count]];
    }
}
