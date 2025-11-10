<?php

namespace App\Exceptions;

use Throwable;
use App\Exceptions\ApiException;
use Illuminate\Auth\AuthenticationException;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Foundation\Exceptions\Handler as ExceptionHandler;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\HttpKernel\Exception\HttpException;

class Handler extends ExceptionHandler
{
    protected $dontReport = [
        ValidationException::class,
        AuthenticationException::class,
    ];

    protected $dontFlash = [
        'current_password',
        'password',
        'password_confirmation',
    ];

    public function register(): void
    {
        $this->reportable(function (Throwable $e) {
            if ($this->shouldReport($e)) {
                // You can add logging here if needed
            }
        });

        // Register a custom response for all exceptions in API routes
        $this->renderable(function (Throwable $e, $request) {
            if ($request->is('api/*')) {
                return $this->handleApiException($request, $e);
            }
        });
    }

    private function handleApiException($request, Throwable $exception): JsonResponse
    {
        if ($exception instanceof ApiException) {
            return new JsonResponse([
                'status' => 'error',
                'message' => $exception->getMessage(),
                'errors' => $exception->getErrors(),
            ], $exception->getStatusCode());
        }

        if ($exception instanceof ValidationException) {
            return new JsonResponse([
                'message' => 'Validation error',
                'errors' => $exception->errors(),
            ], 422);
        }

        if ($exception instanceof ModelNotFoundException) {
            return new JsonResponse([
                'message' => 'Resource not found',
                'error' => 'ModelNotFoundException'
            ], 404);
        }

        if ($exception instanceof NotFoundHttpException) {
            return new JsonResponse([
                'message' => 'Route not found',
                'error' => 'NotFoundHttpException'
            ], 404);
        }

        if ($exception instanceof AuthenticationException) {
            return new JsonResponse([
                'message' => 'Unauthenticated',
                'error' => 'AuthenticationException'
            ], 401);
        }

        if ($exception instanceof HttpException) {
            return new JsonResponse([
                'message' => $exception->getMessage(),
                'error' => get_class($exception)
            ], $exception->getStatusCode());
        }

        // Log unexpected errors
        if ($this->shouldReport($exception)) {
            logger()->error($exception->getMessage(), [
                'exception' => get_class($exception),
                'file' => $exception->getFile(),
                'line' => $exception->getLine()
            ]);
        }

        return new JsonResponse([
            'message' => $exception->getMessage(),
            'error' => get_class($exception)
        ], 500);
    }

    /**
     * Convert an authentication exception into an unauthenticated response.
     * Ensure API routes return JSON 401 when unauthenticated.
     */
    protected function unauthenticated($request, AuthenticationException $exception)
    {
        if ($request->is('api/*') || $request->expectsJson()) {
            return response()->json([
                'status' => 'error',
                'message' => 'Unauthenticated'
            ], 401);
        }

        return redirect()->guest(route('login'));
    }
}