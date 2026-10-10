<?php

use App\Http\Middleware\AuditSuperAdminActivity;
use App\Http\Middleware\EnsureModuleAccess;
use App\Http\Middleware\EnsureOrganizationAccess;
use App\Http\Middleware\EnsureSuperAdmin;
use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;
use App\Http\Middleware\SetLocale;

return Application::configure(
    basePath: dirname(__DIR__)
)
    ->withRouting(
        web: __DIR__ . '/../routes/web.php',
        api: __DIR__ . '/../routes/api.php',
        commands: __DIR__ . '/../routes/console.php',
        health: '/up',
    )
    ->withMiddleware(
        function (
            Middleware $middleware
        ): void {
            $middleware->web(
                append: [
                    SetLocale::class,
                ]
            );
            $middleware->alias([
                'superadmin' =>
                EnsureSuperAdmin::class,

                'organization.access' =>
                EnsureOrganizationAccess::class,

                'audit.superadmin' =>
                AuditSuperAdminActivity::class,

                'module' =>
                EnsureModuleAccess::class,
            ]);
        }
    )
    ->withExceptions(
        function (
            Exceptions $exceptions
        ): void {
            //
        }
    )
    ->create();
