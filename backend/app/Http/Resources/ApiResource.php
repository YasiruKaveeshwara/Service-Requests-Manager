<?php

declare(strict_types=1);

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

abstract class ApiResource extends JsonResource
{
    protected function relation(string $name, string $resourceClass): mixed
    {
        return $this->whenLoaded($name, fn() => $this->{$name} === null ? null : new $resourceClass($this->{$name}));
    }
}
