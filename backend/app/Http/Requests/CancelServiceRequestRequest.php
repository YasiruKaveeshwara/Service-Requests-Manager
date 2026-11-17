<?php

declare(strict_types=1);

namespace App\Http\Requests;

/**
 * Validates cancellation comments for employee-owned requests.
 */
final class CancelServiceRequestRequest extends ServiceRequestActionRequest
{
    protected function ability(): string
    {
        return 'cancel';
    }

    public function rules(): array
    {
        return ['comment' => ['nullable', 'string', 'max:1000']];
    }
}
