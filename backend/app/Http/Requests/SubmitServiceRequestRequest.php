<?php

declare(strict_types=1);

namespace App\Http\Requests;

final class SubmitServiceRequestRequest extends ServiceRequestActionRequest
{
    protected function ability(): string
    {
        return 'submit';
    }

    public function rules(): array
    {
        return ['comment' => ['nullable', 'string', 'max:1000']];
    }
}
