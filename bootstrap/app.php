<?php

use Illuminate\Auth\AuthenticationException;
use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;
use Symfony\Component\HttpKernel\Exception\HttpException;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__ . '/../routes/web.php',
        api: __DIR__ . '/../routes/api.php',
        apiPrefix: 'api/v1',
        commands: __DIR__ . '/../routes/console.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware): void {
        // statefulApi() is intentionally NOT called here.
        // We use Sanctum token-based auth (Bearer tokens), not cookie/session-based SPA auth.
        // Calling statefulApi() would add CSRF verification to API routes, causing 419 errors.
    })
    ->withExceptions(function (Exceptions $exceptions): void {
        $exceptions->render(function (AuthenticationException $e, Request $request) {
            if ($request->expectsJson() || $request->is('api/*')) {
                return response()->json([
                    'data' => null,
                    'message' => 'Nuk jeni i autentikuar.',
                    'status' => 401,
                ], 401);
            }
        });

        $exceptions->render(function (ValidationException $e, Request $request) {
            if ($request->expectsJson() || $request->is('api/*')) {
                return response()->json([
                    'data' => $e->errors(),
                    'message' => 'Të dhënat nuk janë të sakta.',
                    'status' => 422,
                ], 422);
            }
        });

        $exceptions->render(function (HttpException $e, Request $request) {
            if ($request->expectsJson() || $request->is('api/*')) {
                return response()->json([
                    'data' => null,
                    'message' => $e->getMessage() ?: 'Ndodhi një gabim.',
                    'status' => $e->getStatusCode(),
                ], $e->getStatusCode());
            }
        });
    })->create();
