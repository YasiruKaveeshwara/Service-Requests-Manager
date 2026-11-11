<?php

declare(strict_types=1);

namespace App\Http\Requests;

final class StoreCommentRequest extends ServiceRequestActionRequest
{
    protected function ability(): string
    {
        return 'comment';
    }

    public function rules(): array
    {
        return ['body' => ['required', 'string', 'max:2000']];
    }
}
