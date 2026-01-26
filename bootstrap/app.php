<?php

use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        api: __DIR__.'/../routes/api.php',
        apiPrefix: 'api',
        commands: __DIR__.'/../routes/console.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware): void {
        $middleware->validateCsrfTokens(except: [
            'iclock/*',
        ]);
        
        $middleware->alias([
            'role' => \App\Http\Middleware\RoleMiddleware::class,
            'permission' => \App\Http\Middleware\PermissionMiddleware::class,
            'subdomain' => \App\Http\Middleware\SubdomainMiddleware::class,
        ]);
    })
    ->withExceptions(function (Exceptions $exceptions): void {
        // Handle 419 Page Expired (CSRF Token Mismatch) - Redirect to login
        $exceptions->render(function (\Illuminate\Session\TokenMismatchException $e, \Illuminate\Http\Request $request) {
            if ($request->expectsJson()) {
                return response()->json([
                    'message' => 'Your session has expired. Please refresh the page and try again.',
                    'redirect' => route('login')
                ], 419);
            }
            
            // Redirect to login page with a message
            return redirect()->route('login')
                ->with('error', 'Your session has expired. Please login again.');
        });
        
        // Handle 419 HTTP status code
        $exceptions->render(function (\Symfony\Component\HttpKernel\Exception\HttpException $e, \Illuminate\Http\Request $request) {
            if ($e->getStatusCode() === 419) {
                if ($request->expectsJson()) {
                    return response()->json([
                        'message' => 'Your session has expired. Please refresh the page and try again.',
                        'redirect' => route('login')
                    ], 419);
                }
                
                // Redirect to login page with a message
                return redirect()->route('login')
                    ->with('error', 'Your session has expired. Please login again.');
            }
        });
    })->create();
