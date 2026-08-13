<?php

use App\Http\Middleware\AdminMiddleware;
use App\Http\Middleware\DoctorMiddleware;
use App\Http\Middleware\PatientMiddleware;
use App\Http\Middleware\ReceptionMiddleware;
use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        commands: __DIR__.'/../routes/console.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware): void {
        // Phase 1 - Route Protection.
        // These short names ("admin", "doctor", ...) are what we attach to routes
        // inside routes/web.php, e.g. Route::middleware(['auth', 'admin']).
        $middleware->alias([
            'admin'     => AdminMiddleware::class,
            'doctor'    => DoctorMiddleware::class,
            'reception' => ReceptionMiddleware::class,
            'patient'   => PatientMiddleware::class,
        ]);

        // Guests that hit a protected page are sent back to the login screen.
        $middleware->redirectGuestsTo(fn () => route('login'));
    })
    ->withExceptions(function (Exceptions $exceptions): void {
        //
    })->create();
