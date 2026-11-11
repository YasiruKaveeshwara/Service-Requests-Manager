<?php

declare(strict_types=1);

namespace App\Http\Requests;

use App\Enums\UserRole;
use Illuminate\Validation\Rule;

final class AssignServiceRequestRequest extends ServiceRequestActionRequest
{
    protected function ability(): string
    {
        return 'assign';
    }

    public function rules(): array
    {
        return [
            'assignee_id' => [
                'required',
                'integer',
                Rule::exists('users', 'id')->where('role', UserRole::Operations->value),
            ],
            'comment' => ['nullable', 'string', 'max:1000'],
        ];
    }

    public function messages(): array
    {
        return ['assignee_id.exists' => 'The assignee must be an operations user.'];
    }
}
