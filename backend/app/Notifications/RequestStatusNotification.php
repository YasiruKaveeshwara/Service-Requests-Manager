<?php

declare(strict_types=1);

namespace App\Notifications;

use App\Enums\RequestNotificationType;
use App\Models\ServiceRequest;
use App\Models\User;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class RequestStatusNotification extends Notification
{
    use Queueable;

    public function __construct(
        private readonly ServiceRequest $serviceRequest,
        private readonly RequestNotificationType $type,
        private readonly User $actor,
    ) {}

    /** @return list<string> */
    public function via(object $notifiable): array
    {
        return ['database', 'mail'];
    }

    public function message(): string
    {
        return sprintf('Request #%d "%s" %s.', $this->serviceRequest->id, $this->serviceRequest->title, $this->type->phrase());
    }

    public function toMail(object $notifiable): MailMessage
    {
        return (new MailMessage)
            ->subject(sprintf('[Request #%d] %s', $this->serviceRequest->id, $this->type->label()))
            ->line($this->message())
            ->line('Action by: '.$this->actor->name)
            ->action('View request', rtrim((string) config('app.frontend_url'), '/').'/requests/'.$this->serviceRequest->id);
    }

    /** @return array<string, mixed> */
    public function toArray(object $notifiable): array
    {
        return [
            'request_id' => $this->serviceRequest->id,
            'title' => $this->serviceRequest->title,
            'type' => $this->type->value,
            'message' => $this->message(),
            'actor' => [
                'id' => $this->actor->id,
                'name' => $this->actor->name,
            ],
        ];
    }
}
