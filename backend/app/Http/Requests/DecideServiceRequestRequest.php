<?php

declare(strict_types=1);

namespace App\Http\Requests;

use App\Enums\ManagerDecision;
use Illuminate\Validation\Rule;

final class DecideServiceRequestRequest extends ServiceRequestActionRequest
{
    protected function ability(): string
    {
        return 'decide';
    }

    public function rules(): array
    {
        return [
            'decision' => ['required', Rule::enum(ManagerDecision::class)],
            'comment' => ['required_if:decision,reject,return', 'nullable', 'string', 'max:1000'],
        ];
    }

    public function messages(): array
    {
        return ['comment.required_if' => 'A comment is required when rejecting or returning a request.'];
    }
}
