<?php

declare(strict_types=1);

namespace App\Services;

use App\Enums\RequestNotificationType;
use App\Enums\UserRole;
use App\Models\ServiceRequest;
use App\Models\User;
use Illuminate\Support\Collection;

final class NotificationRecipientResolver
{
    /** @return Collection<int, User> */
    public function resolve(ServiceRequest $serviceRequest, RequestNotificationType $type, User $actor): Collection
    {
        $serviceRequest->loadMissing(['requester.manager', 'assignee']);
        /** @var User $requester */
        $requester = $serviceRequest->requester;

        $recipients = match ($type) {
            RequestNotificationType::Submitted => collect([$requester->manager]),
            RequestNotificationType::Cancelled => collect($serviceRequest->submitted_at !== null ? [$requester->manager] : []),
            RequestNotificationType::Approved => collect([$requester])->merge(User::query()->withRole(UserRole::Operations)->get()),
            RequestNotificationType::Assigned => collect([$serviceRequest->assignee, $requester]),
            RequestNotificationType::Rejected,
            RequestNotificationType::Returned,
            RequestNotificationType::Started,
            RequestNotificationType::Completed => collect([$requester]),
        };

        return $recipients
            ->filter()
            ->reject(fn(User $user): bool => $user->id === $actor->id)
            ->unique('id')
            ->values();
    }
}
