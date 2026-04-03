<?php

use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;
use Symfony\Component\HttpFoundation\Response;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        commands: __DIR__.'/../routes/console.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware): void {
        $middleware->alias([
            'user.database' => SetUserDatabase::class,
        ]);
    })
    ->withExceptions(function (Exceptions $exceptions): void {
        $exceptions->render(function (\Illuminate\Auth\AuthenticationException $e, $request) {
            if ($request->expectsJson()) {
                return response()->json(['message' => 'Unauthenticated.'], 401);
            }
            
            if ($request->is('admin*') || $request->is('admin/*')) {
                return redirect()->guest(route('admin.login'));
            }
            
            return redirect()->guest(route('masters.login'));
        });
        
        $exceptions->respond(function (Response $response) {
            if ($response->getStatusCode() === 419) {
                if (request()->expectsJson()) {
                    return response()->json([
                        'message' => 'csrf_token_expired',
                        'error' => 'csrf_token_expired'
                    ], 419);
                }
                
                return back()->with([
                    'message' => 'refresh please.',
                ]);
            }
            return $response;
        });
    })->create();