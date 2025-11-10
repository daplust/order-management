<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Response;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Auth\AuthenticationException;
use Illuminate\Database\QueryException;
use Illuminate\Validation\ValidationException;
use Symfony\Component\HttpKernel\Exception\HttpException;
use App\Exceptions\ApiException;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\App;

class ApiController extends Controller
{
    protected function success(mixed $data = null, ?string $message = null, int $code = 200): JsonResponse
    {
        $payload = ['status' => 'success'];

        if ($message !== null) {
            $payload['message'] = $message;
        }

        if ($data !== null) {
            $payload['data'] = $data;
        }

        return response()->json($payload, $code);
    }

    protected function error(?string $message = null, int $code = 400, $errors = null, ?string $errorId = null): JsonResponse
    {
        $payload = ['status' => 'error'];

        if ($message !== null) {
            $payload['message'] = $message;
        }

        if ($errors !== null) {
            $payload['errors'] = $errors;
        }

        if ($errorId !== null) {
            $payload['error_id'] = $errorId;
        }

        return response()->json($payload, $code);
    }

    protected function created(mixed $data = null, ?string $message = 'Created'): JsonResponse
    {
        return $this->success($data, $message, 201);
    }

    /**
     * Execute a callback and map common exceptions to standardized API responses.
     * Returns JsonResponse directly if the callback returns one.
     */
    protected function handle(callable $callback, int $successCode = 200): JsonResponse
    {
        try {
            $result = $callback();

            if ($result instanceof JsonResponse) {
                return $result;
            }

            return $this->success($result, null, $successCode);
        } catch (ValidationException $e) {
            return $this->error('Validation error', 422, $e->errors());
        } catch (ModelNotFoundException $e) {
            return $this->error('Resource not found', 404, null);
        } catch (AuthenticationException $e) {
            return $this->error('Unauthenticated', 401, null);
        } catch (AuthorizationException $e) {
            return $this->error('Unauthorized', 403, null);
        } catch (HttpException $e) {
            return $this->error($e->getMessage() ?: 'HTTP error', $e->getStatusCode(), null);
        } catch (QueryException $e) {
            $errorId = (string) Str::uuid();
            Log::error('Database query error', ['error_id' => $errorId, 'exception' => $e]);
            return $this->error('Database error', 500, null, $errorId);
        } catch (ApiException $e) {
            // ApiException carries its own status code and optional errors payload
            return $this->error($e->getMessage(), $e->getStatusCode(), $e->getErrors());
        } catch (\Throwable $e) {
            $errorId = (string) Str::uuid();
            Log::error('Unhandled exception', ['error_id' => $errorId, 'exception' => $e]);

            if (config('app.debug') || App::isLocal()) {
                $debug = [
                    'exception' => get_class($e),
                    'message' => $e->getMessage(),
                    'file' => $e->getFile(),
                    'line' => $e->getLine(),
                    'trace' => collect($e->getTrace())->map(function ($t) {
                        return isset($t['file']) ? ($t['file'] . ':' . ($t['line'] ?? '?')) : null;
                    })->filter()->values()->all(),
                ];

                return $this->error('An internal error occurred', 500, $debug, $errorId);
            }

            return $this->error('An internal error occurred', 500, null, $errorId);
        }
    }
}
