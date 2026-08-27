<?php

use Illuminate\Auth\AuthenticationException;
use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;
use Symfony\Component\HttpKernel\Exception\HttpException;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        api: __DIR__.'/../routes/api.php',
        commands: __DIR__.'/../routes/console.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware): void {
        //
    })
    ->withExceptions(function (Exceptions $exceptions): void {
        $exceptions->shouldRenderJsonWhen(
            fn (Request $request, Throwable $e): bool => $request->is('api/*') || $request->expectsJson()
        );

        $exceptions->render(function (Throwable $e, Request $request): ?JsonResponse {
            if (! $request->is('api/*')) {
                return null;
            }

            return match (true) {
                $e instanceof ValidationException => response()->json([
                    'message' => 'Validation failed.',
                    'errors' => $e->errors(),
                ], 422),

                $e instanceof AuthenticationException => response()->json([
                    'message' => 'Unauthenticated.',
                ], 401),

                $e instanceof HttpException && $e->getStatusCode() === 403 => response()->json([
                    'message' => 'You are not authorized to perform this action.',
                ], 403),

                $e instanceof NotFoundHttpException => response()->json([
                    'message' => 'Resource not found.',
                ], 404),

                $e instanceof HttpException => response()->json([
                    'message' => $e->getStatusCode() >= 500 ? 'An unexpected error occurred.' : $e->getMessage(),
                ], $e->getStatusCode()),

                default => response()->json([
                    'message' => 'An unexpected error occurred.',
                ], 500),
            };
        });
    })->create();
