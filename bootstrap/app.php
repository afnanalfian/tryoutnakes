<?php

use Illuminate\Console\Scheduling\Schedule;
use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        commands: __DIR__.'/../routes/console.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware) {
        // Percayai proxy lokal (Nginx) agar HTTPS & IP klien terbaca benar.
        $middleware->trustProxies(at: ['127.0.0.1', '::1']);

        $middleware->web(append: [
            \App\Http\Middleware\SecurityHeaders::class,
            \App\Http\Middleware\EnsureUserIsActive::class,
        ]);

        $middleware->alias([
            'admin' => \App\Http\Middleware\EnsureUserIsAdmin::class,
            'verified.optional' => \App\Http\Middleware\EnsureEmailVerifiedIfRequired::class,
        ]);

        $middleware->redirectGuestsTo(fn () => route('login'));
        $middleware->redirectUsersTo(fn () => route('dashboard'));
    })
    ->withSchedule(function (Schedule $schedule) {
        $schedule->command('attempts:finalize-expired')->everyMinute()->withoutOverlapping();
        $schedule->command('orders:expire')->everyFiveMinutes()->withoutOverlapping();
        $schedule->command('sitemap:warm')->hourly();
        $schedule->command('seo:indexnow')->dailyAt('03:15')->when(fn () => (bool) config('services.indexnow.key'));
        $schedule->command('queue:prune-failed --hours=168')->daily();
        $schedule->command('auth:clear-resets')->everyFifteenMinutes();
    })
    ->withExceptions(function (Exceptions $exceptions) {
        //
    })->create();
