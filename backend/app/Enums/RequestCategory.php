<?php

declare(strict_types=1);

namespace App\Enums;

enum RequestCategory: string
{
    case It = 'it';
    case Equipment = 'equipment';
    case Maintenance = 'maintenance';
    case Access = 'access';

    public function label(): string
    {
        return $this === self::It ? 'IT' : ucfirst($this->value);
    }
}
