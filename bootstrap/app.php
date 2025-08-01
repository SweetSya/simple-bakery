<?php

use App\Http\Middleware\CheckAuthentication;
use App\Http\Middleware\DebugRequest;
use App\Http\Middleware\HandleAppearance;
use App\Http\Middleware\HandleInertiaRequests;
use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;
use Illuminate\Foundation\Http\Middleware\VerifyCsrfToken;
use Illuminate\Http\Middleware\AddLinkHeadersForPreloadedAssets;
use Illuminate\Http\Middleware\HandleCors;
use Symfony\Component\ErrorHandler\Debug;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__ . '/../routes/web.php',
        commands: __DIR__ . '/../routes/console.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware) {
        $middleware->encryptCookies(except: ['appearance', 'sidebar_state']);
        $middleware->web(append: [
            VerifyCsrfToken::class,
            HandleInertiaRequests::class,
            HandleAppearance::class,
            AddLinkHeadersForPreloadedAssets::class,
        ]);
        $middleware->alias([
            'checkAuth' => CheckAuthentication::class
        ]);
    })
    ->withExceptions(function (Exceptions $exceptions) {
        //
    })->create();
