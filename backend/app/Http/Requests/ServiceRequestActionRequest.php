<?php

declare(strict_types=1);

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

abstract class ServiceRequestActionRequest extends FormRequest
{
    abstract protected function ability(): string;

    public function authorize(): bool
    {
        return $this->user()->can($this->ability(), $this->route('serviceRequest'));
    }
}
