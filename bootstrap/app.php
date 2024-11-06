<?php

use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;

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
        // Set when to render as JSON
        $exceptions->shouldRenderJsonWhen(function (Request $request, Throwable $e) {
            return $request->is('api/*') || $request->expectsJson();
        });

        // Customize the JSON response format
        $exceptions->renderUsing(function (Request $request, Throwable $e): Response {
            $status = $e instanceof HttpException ? $e->getStatusCode() : 500;
            $message = $e->getMessage() ?: 'An unexpected error occurred';

            return new JsonResponse([
                'error' => true,
                'status' => $status,
                'message' => $message,
            ], $status);
        });
    })->create();
