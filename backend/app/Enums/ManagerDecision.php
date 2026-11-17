<?php

declare(strict_types=1);

namespace App\Enums;

/**
 * Decisions available to a manager reviewing a submitted request.
 *
 * Each decision maps to a workflow status and notification type so the
 * workflow service can apply one consistent interpretation of the action.
 */
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
