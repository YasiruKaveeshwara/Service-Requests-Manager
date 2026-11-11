<?php

declare(strict_types=1);

namespace App\Http\Requests;

use Illuminate\Validation\Rule;

final class ChangeStatusRequest extends ServiceRequestActionRequest
{
    protected function ability(): string
    {
        return 'changeStatus';
    }

    public function rules(): array
    {
        return [
            'status' => ['required', Rule::in(['in_progress', 'completed'])],
            'comment' => ['nullable', 'string', 'max:1000'],
        ];
    }
}
