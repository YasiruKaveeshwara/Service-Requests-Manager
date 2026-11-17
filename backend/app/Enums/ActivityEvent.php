<?php

declare(strict_types=1);

namespace App\Enums;

/**
 * Immutable audit events recorded against a service request.
 */
enum ActivityEvent: string
{
    case Created = 'created';
    case Updated = 'updated';
    case Transitioned = 'transitioned';
    case Assigned = 'assigned';
    case Reassigned = 'reassigned';
    case Commented = 'commented';
}
