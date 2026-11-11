<?php

declare(strict_types=1);

namespace App\Http\Requests;

use App\Http\Requests\Concerns\HasServiceRequestFields;
use App\Models\ServiceRequest;

final class StoreServiceRequestRequest extends ServiceRequestActionRequest
{
    use HasServiceRequestFields;

    protected function ability(): string
    {
        return 'create';
    }

    public function authorize(): bool
    {
        return $this->user()->can('create', ServiceRequest::class);
    }

    /** @return array<string, array<int, mixed>> */
    public function rules(): array
    {
        return [...$this->fieldRules(), 'submit' => ['sometimes', 'boolean']];
    }
}
