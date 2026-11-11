<?php

declare(strict_types=1);

namespace App\Exceptions;

use Illuminate\Auth\AuthenticationException;
use Illuminate\Http\JsonResponse;
use Illuminate\Validation\ValidationException;
use Symfony\Component\HttpKernel\Exception\HttpExceptionInterface;
use Throwable;

final class ApiExceptionRenderer
{
    public function render(Throwable $e): JsonResponse
    {
        return match (true) {
            $e instanceof ValidationException => $this->error('Validation failed.', 'validation_failed', 422, $e->errors()),
            $e instanceof WorkflowException => $this->error($e->getMessage(), 'invalid_transition', 422),
            $e instanceof AuthenticationException => $this->error($e->getMessage() ?: 'Unauthenticated.', 'unauthenticated', 401),
            $e instanceof HttpExceptionInterface => $this->fromHttp($e),
            default => $this->error(config('app.debug') ? $e->getMessage() : 'Server error.', 'server_error', 500),
        };
    }

    private function fromHttp(HttpExceptionInterface $e): JsonResponse
    {
        $status = $e->getStatusCode();

        [$code, $message] = match ($status) {
            401 => ['unauthenticated', 'Unauthenticated.'],
            403 => ['forbidden', $e->getMessage() ?: 'This action is unauthorized.'],
            404 => ['not_found', 'Resource not found.'],
            405 => ['method_not_allowed', 'Method not allowed.'],
            429 => ['too_many_requests', 'Too many requests. Please slow down.'],
            default => ['http_error', $e->getMessage() ?: 'Request failed.'],
        };

        return $this->error($message, $code, $status, null, $e->getHeaders());
    }

    /**
     * @param  array<string, array<int, string>>|null  $errors
     * @param  array<string, string>  $headers
     */
    private function error(string $message, string $code, int $status, ?array $errors = null, array $headers = []): JsonResponse
    {
        return response()->json(['message' => $message, 'code' => $code, 'errors' => $errors], $status, $headers);
    }
}
