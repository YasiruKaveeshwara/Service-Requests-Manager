<?php

declare(strict_types=1);

namespace App\Http\Requests;

use App\Enums\RequestStatus;
use App\Models\ServiceRequest;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

final class ListServiceRequestsRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()->can('viewAny', ServiceRequest::class);
    }

    /** @return array<string, array<int, mixed>> */
    public function rules(): array
    {
        return [
            'status' => [Rule::enum(RequestStatus::class)],
            'assignee_id' => ['nullable', 'integer'],
            'search' => ['nullable', 'string', 'max:150'],
            'sort' => [Rule::in(ServiceRequest::SORTABLE)],
            'direction' => [Rule::in(['asc', 'desc'])],
            'per_page' => ['integer', 'min:1', 'max:100'],
        ];
    }
}
