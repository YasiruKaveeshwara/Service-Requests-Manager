<?php

declare(strict_types=1);

namespace App\Jobs;

use App\Enums\RequestNotificationType;
use App\Models\ServiceRequest;
use App\Models\User;
use App\Notifications\RequestStatusNotification;
use App\Services\NotificationRecipientResolver;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Notification;

/**
 * Resolves and delivers notifications outside the request-response cycle.
 *
 * Database notifications are created by the notification channels; the same
 * event also supports email delivery through the configured mail transport.
 */
class SendRequestNotificationsJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public bool $deleteWhenMissingModels = true;

    public int $tries = 3;

    public function __construct(
        public ServiceRequest $serviceRequest,
        public RequestNotificationType $type,
        public int $actorId,
    ) {}

    public function handle(NotificationRecipientResolver $resolver): void
    {
        // The actor may have been removed after dispatch; in that case there is
        // no trustworthy event context, so the job can be safely discarded.
        $actor = User::query()->find($this->actorId);

        if ($actor === null) {
            return;
        }

        $recipients = $resolver->resolve($this->serviceRequest, $this->type, $actor);

        if ($recipients->isNotEmpty()) {
            Notification::sendNow($recipients, new RequestStatusNotification($this->serviceRequest, $this->type, $actor));
        }
    }
}
