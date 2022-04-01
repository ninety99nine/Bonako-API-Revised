<?php

namespace App\Exceptions;

use Throwable;
use App\Services\Logging\SlackLogError;
use Illuminate\Auth\AuthenticationException;
use Illuminate\Validation\ValidationException;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Illuminate\Foundation\Exceptions\Handler as ExceptionHandler;
use Symfony\Component\HttpKernel\Exception\AccessDeniedHttpException;
use Symfony\Component\HttpKernel\Exception\MethodNotAllowedHttpException;
use Symfony\Component\Routing\Exception\RouteNotFoundException;

class Handler extends ExceptionHandler
{
    /**
     * A list of the exception types that are not reported.
     *
     * @var array<int, class-string<Throwable>>
     */
    protected $dontReport = [
        RepositoryQueryFailedException::class,
        AcceptingTermsAndConditionsFailedException::class
    ];

    /**
     * A list of the inputs that are never flashed for validation exceptions.
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
     *
     * @return void
     */
    public function register()
    {
        $this->reportable(function (Throwable $e) {
            //  Send every error to our Slack error channel
            //  resolve(SlackLogError::class)->logError($e);

        });

        //  Route not found Error
        $this->renderable(function (RouteNotFoundException $e, $request) {
            return response(['message' => 'Route not found Make sure you are using the correct url'], 404);
        });

        //  Resource not found Error
        $this->renderable(function (NotFoundHttpException $e, $request) {
            return response(['message' => 'This resource does not exist'], 404);
        });

        //  Method not allowed Error
        $this->renderable(function (MethodNotAllowedHttpException $e, $request) {
            return response(['message' => 'The '.$request->method().' method not allowed for this endpoint'], 404);
        });

        //  Unauthenticated Error
        $this->renderable(function (AuthenticationException $e, $request) {
            return response(['message' => 'Please sign in to continue'], 401);
        });

        //  Unauthorized Error
        $this->renderable(function (AccessDeniedHttpException $e, $request) {
            return response(['message' => 'You do not have permissions to perform this action'], 403);
        });

        //  Validation Error
        $this->renderable(function (ValidationException $e, $request) {
            return response(['message' => $e->getMessage(), 'errors' => $e->errors()], 422);
        });

        //  Any other Error
        $this->renderable(function (Throwable $e, $request) {

            //  Render any other error
            return response([
                'message' => 'Something went wrong',
                'error' => $e->getMessage(),
                'file' => $e->getFile(),
                'line' => $e->getLine(),
            ], 500);

        });

    }
}
