<?php

use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;
use Illuminate\Validation\ValidationException;
use Illuminate\Http\Request;

use Symfony\Component\HttpKernel\Exception\HttpExceptionInterface;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\HttpKernel\Exception\MethodNotAllowedHttpException;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Auth\AuthenticationException;
use Illuminate\Http\Exceptions\ThrottleRequestsException;
use Illuminate\Support\Facades\Log;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        api: __DIR__.'/../routes/api.php',
        commands: __DIR__.'/../routes/console.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware) {
        //
    })
->withExceptions(function (Exceptions $exceptions) {
    $exceptions->render(function (Throwable $e, Request $request)  {
        if ($e instanceof ValidationException) {
            return response()->json([
                'status' => false,
                'message' => 'Validation Error',
                'errors' => $e->errors()
            ], 422);
        }

        if ($e instanceof AuthenticationException) {
            // لو الطلب جاي من API أو JavaScript → رجّع JSON
        if ($request->expectsJson() || $request->is('api/*')) {
        return response()->json([
            'status' => false,
            'message' => $e->getMessage() ?? 'Unauthenticated'
        ], 401);
    }

    // غير كده (يعني متصفح داخل Filament) → رجّعه على صفحة تسجيل الدخول
     return redirect()->guest(route('filament.admin.auth.login'));
    }


        if ($e instanceof ModelNotFoundException) {
            $model = strtolower(class_basename($e->getModel()));
            return response()->json([
                'status' => false,
                'message' => $e->getMessage() ?? "{$model} not found"
            ], 404);
        }

        if ($e instanceof NotFoundHttpException) {
            return response()->json([
                'status' => false,
                'message' => $e->getMessage() ?? 'The specified URL cannot be found'
            ], 404);
        }

        if ($e instanceof \InvalidArgumentException) {
            return response()->json([
                'status' => false,
                'message' => $e->getMessage()
            ], 400);
        }

        if ($e instanceof MethodNotAllowedHttpException) {
            return response()->json([
                'status' => false,
                'message' => $e->getMessage() ?? 'The specified method for the request is invalid'
            ], 405);
        }

        if ($e instanceof ThrottleRequestsException) {
            return response()->json([
                'status' => false,
                'message' => $e->getMessage() ?? 'Too many requests'
            ], 429);
        }

        if ($e instanceof HttpExceptionInterface) {
            return response()->json([
                'status' => false,
                'message' => $e->getMessage() ?: 'HTTP error'
            ], $e->getStatusCode());
        }

        if (config('app.debug')) {
            return response()->json([
                'status' => false,
                'message' => $e->getMessage(),
                'debug' => [
                    'exception' => get_class($e),
                    'file' => $e->getFile(),
                    'line' => $e->getLine(),
                    'trace' => collect($e->getTrace())->take(5),
                ]
            ], 500);
        }

        Log::error('Unexpected exception caught', [
            'exception' => get_class($e),
            'message' => $e->getMessage(),
            'file' => $e->getFile(),
            'line' => $e->getLine(),
            'trace' => collect($e->getTrace())->take(5)->toArray()
        ]);

        return response()->json([
            'status' => false,
            'message' => 'Unexpected error. Try later'
        ], 500);
    });
})->create();

