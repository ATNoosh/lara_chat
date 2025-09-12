<?php

use App\Http\Middleware\DevelopmentCsp;
use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        api: __DIR__.'/../routes/api.php',
        commands: __DIR__.'/../routes/console.php',
        channels: __DIR__.'/../routes/channels.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware) {
        // Temporarily disable CSP to debug blank page
        // $middleware->web([DevelopmentCsp::class]);
    })
    ->withExceptions(function (Exceptions $exceptions) {
        //
    })->create();
