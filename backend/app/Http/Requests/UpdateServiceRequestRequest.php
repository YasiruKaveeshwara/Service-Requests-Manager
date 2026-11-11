<?php

declare(strict_types=1);

namespace App\Http\Requests;

use App\Http\Requests\Concerns\HasServiceRequestFields;

final class UpdateServiceRequestRequest extends ServiceRequestActionRequest
{
    use HasServiceRequestFields;

    protected function ability(): string
    {
        return 'update';
    }

    /** @return array<string, array<int, mixed>> */
    public function rules(): array
    {
        return $this->fieldRules();
    }
}
