<?php

declare(strict_types=1);

namespace App\Http\Requests;

use App\Enums\RequestStatus;
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
            'status' => ['required', Rule::in(array_map(
                static fn (RequestStatus $status): string => $status->value,
                [RequestStatus::InProgress, RequestStatus::Completed],
            ))],
            'comment' => ['nullable', 'string', 'max:1000'],
        ];
    }
}
