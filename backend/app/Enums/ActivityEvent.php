<?php

declare(strict_types=1);

namespace App\Enums;

enum ActivityEvent: string
{
    case Created = 'created';
    case Updated = 'updated';
    case Transitioned = 'transitioned';
    case Assigned = 'assigned';
    case Reassigned = 'reassigned';
    case Commented = 'commented';
}
