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
        $middleware->trustProxies(at: '*');

        $middleware->trimStrings(except: [
            'current_password',
            'password',
            'password_confirmation',
        ]);

        $middleware->validateCsrfTokens(except: [
            'api/setup-intent',
            'api/cards',
            'api/charge-card',
            'api/cards/*',
            'api/auth/register',
            'api/auth/login',
            'api/auth/forgot-password',
            'api/auth/verify-otp',
            'api/auth/reset-password',
            'api/auth/google',
            'api/contact',
            'api/*',
        ]);

        $middleware->redirectGuestsTo(fn ($request) => $request->expectsJson() ? null : route('login'));

        $middleware->redirectTo('/home');

        $middleware->web(append: [
            \App\Http\Middleware\SetLocale::class,
        ]);

        $middleware->statefulApi();

        $middleware->api(append: [
            \App\Http\Middleware\SetLocale::class,
        ]);

        $middleware->alias([
            'role' => \App\Http\Middleware\EnsureUserHasRole::class,
        ]);
    })
    ->withExceptions(function (Exceptions $exceptions) {
        $exceptions->renderable(function (Throwable $e, $request) {
            if (! $request->is('api/*') && ! $request->wantsJson()) {
                return null;
            }

            return app(\App\Exceptions\ApiExceptionRenderer::class)->handle($e, $request);
        });
    })
    ->withSchedule(function ($schedule) {
        $schedule->call(function () {
            app(\App\Services\OtpService::class)->cleanupExpired();
        })->daily();

        $schedule->command('orders:enforce-delivery-sla')->everyMinute();
    })
    ->withProviders([
        App\Providers\AppServiceProvider::class,
        App\Providers\AuthServiceProvider::class,
        App\Providers\EventServiceProvider::class,
        App\Providers\Filament\AdminPanelProvider::class,
    ])
    ->create();
