<?php

namespace App\Exceptions;

use App\Traits\ApiResponse;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Auth\AuthenticationException;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Database\QueryException;
use Illuminate\Foundation\Exceptions\Handler as ExceptionHandler;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;
use Symfony\Component\HttpKernel\Exception\AccessDeniedHttpException;
use Symfony\Component\HttpKernel\Exception\MethodNotAllowedHttpException;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\HttpKernel\Exception\TooManyRequestsHttpException;
use Throwable;

class Handler extends ExceptionHandler
{
    use ApiResponse;

    /**
     * The list of the inputs that are never flashed to the session on validation exceptions.
     *
     * @var array<int, string>
     */
    protected $dontFlash = [
        'current_password',
        'password',
        'password_confirmation',
    ];

    /**
     * Register the exception handling callbacks for the application.
     */
    public function register(): void
    {
        $this->renderable(function (Throwable $e, Request $request) {
            if (! $request->is('api/*') && ! $request->wantsJson()) {
                return null;
            }

            return $this->handleApiException($e, $request);
        });
    }

    private function handleApiException(Throwable $e, Request $request)
    {
        if (method_exists($e, 'render')) {
            return $e->render($request);
        }

        return match (true) {
            $e instanceof ValidationException =>
            $this->error(
                'Validation Failed',
                422,
                $e->errors()
            ),

            $e instanceof TooManyRequestsHttpException =>
            $this->error(
                'Too many attempts. Please try again later',
                429
            ),

            $e instanceof ModelNotFoundException =>
            $this->error(
                'Not Found',
                404,
                [
                    class_basename($e->getModel()) => [
                        'This ' . class_basename($e->getModel()) . ' is not found.'
                    ]
                ]
            ),

            $e instanceof NotFoundHttpException =>
            $this->error(
                'The requested endpoint does not exist',
                404
            ),

            $e instanceof MethodNotAllowedHttpException =>
            $this->error(
                'The HTTP method used for this request is not supported',
                405
            ),

            $e instanceof AuthenticationException =>
            $this->error(
                'Unauthenticated',
                401
            ),

            $e instanceof AuthorizationException,
            $e instanceof AccessDeniedHttpException =>
            $this->error(
                'Forbidden',
                403
            ),

            $e instanceof QueryException =>
            $this->handleQueryException($e),

            default =>
            $this->handleGeneralException($e),
        };
    }

    private function handleQueryException(QueryException $e)
    {
        $errorCode = $e->errorInfo[1] ?? null;

        return match ($errorCode) {
            1062 => $this->error(
                'This data already exists (Duplicate entry).',
                409
            ),

            1451 => $this->error(
                'Cannot delete or update this record because it is referenced by other records.',
                409
            ),

            1452 => $this->error(
                'Cannot create or update this record because the referenced record does not exist.',
                422
            ),

            default => $this->handleDatabaseError($e),
        };
    }

    private function handleDatabaseError(QueryException $e)
    {
        $debugData = config('app.debug') ? [
            'exception' => class_basename($e),
            'sql' => $e->getSql(),
            'bindings' => $e->getBindings(),
            'message' => $e->getMessage(),
        ] : [];

        return $this->error(
            'A database error occurred.',
            500,
            $debugData
        );
    }

    private function handleGeneralException(Throwable $e)
    {
        $debugData = config('app.debug') ? [
            'exception' => class_basename($e),
            'file' => $e->getFile(),
            'line' => $e->getLine(),
            'trace' => array_slice($e->getTrace(), 0, 5),
        ] : [];

        return $this->error(
            config('app.debug')
                ? ($e->getMessage() ?: 'Something went wrong.')
                : 'Something went wrong.',
            500,
            $debugData
        );
    }
}
