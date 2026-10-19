<?php

use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;
use Illuminate\Auth\AuthenticationException;
use Illuminate\Http\Request;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        commands: __DIR__.'/../routes/console.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware): void {
        $middleware->alias(['role' => \App\Http\Middleware\EnsureRole::class]);
    })
    ->withExceptions(function (Exceptions $exceptions): void {
        // Redirect unauthenticated users to login
        $exceptions->render(function (AuthenticationException $e, Request $request) {
            return redirect()->route('login');
        });

        // Redirect gracefully if accessing a deleted/non-existent logsheet record
        $exceptions->render(function (\Symfony\Component\HttpKernel\Exception\NotFoundHttpException $e, Request $request) {
            if ($request->is('logsheet/*')) {
                return redirect()->route('logsheet.index')->with('warning', 'Data logsheet tidak ditemukan atau telah dihapus sebelumnya.');
            }
            if ($request->is('daily-log/*')) {
                return redirect()->route('daily-log.index')->with('warning', 'Data Daily Log Book tidak ditemukan atau telah dihapus sebelumnya.');
            }
        });
    })->create();
