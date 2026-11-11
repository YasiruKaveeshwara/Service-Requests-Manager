<?php

declare(strict_types=1);

namespace App\Enums;

enum RequestNotificationType: string
{
    case Submitted = 'submitted';
    case Approved = 'approved';
    case Rejected = 'rejected';
    case Returned = 'returned';
    case Cancelled = 'cancelled';
    case Assigned = 'assigned';
    case Started = 'started';
    case Completed = 'completed';

    public function label(): string
    {
        return match ($this) {
            self::Submitted => 'Submitted for review',
            self::Approved => 'Approved',
            self::Rejected => 'Rejected',
            self::Returned => 'Returned for changes',
            self::Cancelled => 'Cancelled',
            self::Assigned => 'Owner assigned',
            self::Started => 'Work started',
            self::Completed => 'Completed',
        };
    }

    public function phrase(): string
    {
        return match ($this) {
            self::Submitted => 'was submitted for review',
            self::Approved => 'was approved',
            self::Rejected => 'was rejected',
            self::Returned => 'was returned for changes',
            self::Cancelled => 'was cancelled',
            self::Assigned => 'has a new owner',
            self::Started => 'is now in progress',
            self::Completed => 'was completed',
        };
    }
}
