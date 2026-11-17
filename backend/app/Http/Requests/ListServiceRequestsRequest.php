<?php

declare(strict_types=1);

namespace App\Http\Requests;

use App\Enums\RequestStatus;
use App\Models\ServiceRequest;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

/**
 * Validates request-list filters, sorting, and pagination bounds.
 */
final class ListServiceRequestsRequest extends FormRequest
{
    public function authorize(): bool
    {
        // The policy currently allows every authenticated role to list; the
        // model scope still limits which records each role can see.
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
