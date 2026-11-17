<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api;

use App\Http\Resources\NotificationResource;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;

/**
 * Reads and acknowledges the authenticated user's database notifications.
 */
final class NotificationController
{
    /** Return paginated notifications and the unread badge count. */
    public function index(Request $request): AnonymousResourceCollection
    {
        return NotificationResource::collection($request->user()->notifications()->paginate(15))
            ->additional(['meta' => ['unread_count' => $request->user()->unreadNotifications()->count()]]);
    }

    /** Mark one notification as read after verifying it belongs to the user. */
    public function read(Request $request, string $id): NotificationResource
    {
        $notification = $request->user()->notifications()->findOrFail($id);
        $notification->markAsRead();

        return new NotificationResource($notification);
    }

    /** @return array<string, array<string, int>> */
    /** Mark all notifications belonging to the current user as read. */
    public function readAll(Request $request): array
    {
        $count = $request->user()->unreadNotifications()->count();
        $request->user()->unreadNotifications()->update(['read_at' => now()]);

        return ['data' => ['marked' => $count]];
    }
}
