<?php

declare(strict_types=1);

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

/**
 * Shared authorization adapter for request actions.
 *
 * Subclasses provide the policy ability name while this base class resolves
 * the route-bound request consistently.
 */
abstract class ServiceRequestActionRequest extends FormRequest
{
    abstract protected function ability(): string;

    public function authorize(): bool
    {
        return $this->user()->can($this->ability(), $this->route('serviceRequest'));
    }
}
