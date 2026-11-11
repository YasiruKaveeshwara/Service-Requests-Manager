<?php

declare(strict_types=1);

namespace App\Http\Requests;

use App\Enums\ActivityEvent;
use Illuminate\Validation\Rule;

final class ListActivityRequest extends ServiceRequestActionRequest
{
    protected function ability(): string
    {
        return 'view';
    }

    public function rules(): array
    {
        return [
            'event' => ['nullable', Rule::enum(ActivityEvent::class)],
            'per_page' => ['nullable', 'integer', 'min:1', 'max:100'],
        ];
    }
}
