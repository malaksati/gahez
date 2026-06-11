<?php

use App\Http\Middleware\SetLocaleFromRequest;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;
use Illuminate\Http\Request;
use Illuminate\Routing\Middleware\ThrottleRequests;
use Illuminate\Support\Facades\Route;
use Spatie\Permission\Middleware\PermissionMiddleware;
use Spatie\Permission\Middleware\RoleMiddleware;
use Spatie\Permission\Middleware\RoleOrPermissionMiddleware;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        api: __DIR__.'/../routes/api.php',
        commands: __DIR__.'/../routes/console.php',
        channels: __DIR__.'/../routes/channels.php',
        health: '/up',
        apiPrefix: 'api/v1',
        then: function () {
            Route::middleware('web')
                ->name('v1.')
                ->group(base_path('routes/v1/web.php'));
        },
    )
    ->withMiddleware(function (Middleware $middleware): void {
        $middleware->alias([
            'locale' => SetLocaleFromRequest::class,
            'role' => RoleMiddleware::class,
            'permission' => PermissionMiddleware::class,
            'role_or_permission' => RoleOrPermissionMiddleware::class,
        ]);

        // Global API rate limiting (60 requests per minute)
        $middleware->api(prepend: [
            ThrottleRequests::class.':60,1',
        ]);

        $middleware->api(append: [
            SetLocaleFromRequest::class,
        ]);

        // After StartSession so session('locale') is available.
        $middleware->web(append: [
            SetLocaleFromRequest::class,
        ]);

        $middleware->redirectUsersTo(function () {
            $user = auth()->user();

            if ($user && $user->hasAnyRole(['super-admin', 'admin'])) {
                return route('v1.admin.dashboard');
            }

            return route('home');
        });
    })
    ->withExceptions(function (Exceptions $exceptions): void {
        $exceptions->render(function (
            ModelNotFoundException $e,
            Request $request
        ) {
            if ($request->expectsJson() || $request->is('api/v1/*') || $request->wantsJson()) {
                $model = class_basename($e->getModel());

                return response()->json([
                    'success' => false,
                    'message' => $model.' not found.',
                ], 404);
            }

            return null;
        });

        $exceptions->render(function (
            NotFoundHttpException $e,
            Request $request
        ) {
            $previous = $e->getPrevious();

            if ($previous instanceof ModelNotFoundException) {
                if ($request->expectsJson() || $request->is('api/v1/*') || $request->wantsJson()) {
                    $model = class_basename($previous->getModel());

                    return response()->json([
                        'success' => false,
                        'message' => $model.' not found.',
                    ], 404);
                }
            }

            return null;
        });
    })->create();
