<?php

declare(strict_types=1);

namespace App\Http\Requests\Concerns;

use App\Enums\RequestCategory;
use App\Enums\RequestPriority;
use Illuminate\Validation\Rule;

trait HasServiceRequestFields
{
    /** @return array<string, array<int, mixed>> */
    protected function fieldRules(): array
    {
        return [
            'title' => ['required', 'string', 'max:150'],
            'description' => ['required', 'string', 'max:5000'],
            'category' => ['required', Rule::enum(RequestCategory::class)],
            'priority' => ['nullable', Rule::enum(RequestPriority::class)],
        ];
    }
}
