<?php

declare(strict_types=1);

namespace App\Enums;

enum ManagerDecision: string
{
    case Approve = 'approve';
    case Reject = 'reject';
    case SendBack = 'return';

    public function toStatus(): RequestStatus
    {
        return match ($this) {
            self::Approve => RequestStatus::Approved,
            self::Reject => RequestStatus::Rejected,
            self::SendBack => RequestStatus::Returned,
        };
    }

    public function requiresComment(): bool
    {
        return $this !== self::Approve;
    }

    public function notificationType(): RequestNotificationType
    {
        return match ($this) {
            self::Approve => RequestNotificationType::Approved,
            self::Reject => RequestNotificationType::Rejected,
            self::SendBack => RequestNotificationType::Returned,
        };
    }
}
