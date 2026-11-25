<?php

use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;
use Illuminate\Http\Request;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        commands: __DIR__.'/../routes/console.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware): void {
        // Coolify's Traefik terminates HTTPS; trust its X-Forwarded-* headers so URLs are https://.
        $middleware->trustProxies(at: '*');
        $middleware->redirectGuestsTo('/admin/login');

        // Coolify's proxy (Caddy) terminates https in front of the app. Trust its
        // X-Forwarded-* headers so links, assets and signed URLs use https.
        $middleware->trustProxies(at: '*');
    })
    ->withExceptions(function (Exceptions $exceptions): void {
        $exceptions->shouldRenderJsonWhen(
            fn (Request $request) => $request->is('api/*') || $request->expectsJson(),
        );
    })->create();
