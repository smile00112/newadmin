<?php

use App\Http\Middleware\EncryptCookies;
use Illuminate\Auth\AuthenticationException;
use Illuminate\Console\Scheduling\Schedule;
use Illuminate\Cookie\Middleware\EncryptCookies as BaseEncryptCookies;
use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;
use Illuminate\Foundation\Http\Middleware\ConvertEmptyStringsToNull;
use Illuminate\Foundation\Http\Middleware\PreventRequestsDuringMaintenance;
use Illuminate\Http\Request;
use Webkul\Core\Http\Middleware\SecureHeaders;
use Webkul\Installer\Http\Middleware\CanInstall;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        commands: __DIR__.'/../routes/console.php',
        channels: __DIR__.'/../routes/channels.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware) {
        /**
         * Remove the default Laravel middleware that prevents requests during maintenance mode. There are three
         * middlewares in the shop that need to be loaded before this middleware. Therefore, we need to remove this
         * middleware from the list and add the overridden middleware at the end of the list.
         *
         * As of now, this has been added in the Admin and Shop providers. I will look for a better approach in Laravel 11 for this.
         */
        $middleware->remove(PreventRequestsDuringMaintenance::class);

        /**
         * Remove the default Laravel middleware that converts empty strings to null. First, handle all nullable cases,
         * then remove this line.
         */
        $middleware->remove(ConvertEmptyStringsToNull::class);

        $middleware->append(SecureHeaders::class);
        $middleware->append(CanInstall::class);

        /**
         * Add the overridden middleware at the end of the list.
         */
        $middleware->replaceInGroup('web', BaseEncryptCookies::class, EncryptCookies::class);

        /**
         * Configure authentication redirect for API requests.
         * For API requests, return null to prevent redirect and let exception handler deal with it.
         */
        $middleware->redirectGuestsTo(function (Request $request) {
            // For API requests, return null to prevent redirect
            // This will cause AuthenticationException to be thrown, which will be handled by exception handler
            if ($request->is('api/*') || $request->expectsJson() || $request->wantsJson()) {
                return null;
            }
            
            // For web requests, let the default handler deal with it
            // Return null to prevent redirect attempt to non-existent 'login' route
            return null;
        });
    })
    ->withSchedule(function (Schedule $schedule) {
        //
    })
    ->withExceptions(function (Exceptions $exceptions) {
        /**
         * Handle authentication exceptions for API requests.
         * Return 403 instead of redirecting to login route.
         */
        $exceptions->render(function (AuthenticationException $e, Request $request) {
            // Check if this is an API request
            if ($request->is('api/*') || $request->expectsJson() || $request->wantsJson()) {
                return response()->json([
                    'message' => 'Unauthenticated.',
                ], 403);
            }
        });
    })->create();
