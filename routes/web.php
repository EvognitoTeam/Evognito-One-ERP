<?php

use App\Http\Controllers\Auth\LoginController;
use App\Http\Controllers\PublicRegistrationController;
use App\Http\Controllers\SuperAdmin\AuditLogController;
use App\Http\Controllers\SuperAdmin\DashboardController as SuperAdminDashboardController;
use App\Http\Controllers\SuperAdmin\IntegrationController;
use App\Http\Controllers\SuperAdmin\InvoiceController;
use App\Http\Controllers\SuperAdmin\ModuleController;
use App\Http\Controllers\SuperAdmin\OrganizationAppController;
use App\Http\Controllers\SuperAdmin\OrganizationController;
use App\Http\Controllers\SuperAdmin\OrganizationSubscriptionController;
use App\Http\Controllers\SuperAdmin\PaymentController;
use App\Http\Controllers\SuperAdmin\PlatformSettingController;
use App\Http\Controllers\SuperAdmin\RegistrationInviteController;
use App\Http\Controllers\SuperAdmin\RegistrationRequestController;
use App\Http\Controllers\SuperAdmin\SubscriptionPlanController;
use App\Http\Controllers\SuperAdmin\UserController;
use App\Http\Controllers\Workspace\DashboardController as WorkspaceDashboardController;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\LanguageController;

/*
|--------------------------------------------------------------------------
| Public Landing
|--------------------------------------------------------------------------
*/

Route::get('/', function () {
    return view('landing');
})->name('home');

/*
|--------------------------------------------------------------------------
| Public Registration
|--------------------------------------------------------------------------
*/

Route::get(
    '/register',
    [
        PublicRegistrationController::class,
        'showSelf',
    ]
)->name('registration.self');

Route::post(
    '/register',
    [
        PublicRegistrationController::class,
        'storeSelf',
    ]
)->name('registration.self.store');

Route::get(
    '/join/{token}',
    [
        PublicRegistrationController::class,
        'showInvite',
    ]
)->name('registration.invite.show');

Route::post(
    '/join/{token}',
    [
        PublicRegistrationController::class,
        'acceptInvite',
    ]
)->name('registration.invite.accept');

/*
|--------------------------------------------------------------------------
| Authentication
|--------------------------------------------------------------------------
*/

Route::middleware('guest')
    ->group(function () {
        Route::get(
            '/login',
            [
                LoginController::class,
                'create',
            ]
        )->name('login');

        Route::post(
            '/login',
            [
                LoginController::class,
                'store',
            ]
        )->name('login.store');
    });

Route::post(
    '/logout',
    [
        LoginController::class,
        'destroy',
    ]
)
    ->middleware('auth')
    ->name('logout');

/*
|--------------------------------------------------------------------------
| Language
|--------------------------------------------------------------------------
*/

require __DIR__ . '/language.php';

/*
|--------------------------------------------------------------------------
| Platform Superadmin
|--------------------------------------------------------------------------
*/

Route::prefix('superadmin')
    ->name('superadmin.')
    ->middleware([
        'auth',
        'superadmin',
        'audit.superadmin',
    ])
    ->group(function () {
        Route::get(
            '/',
            [
                SuperAdminDashboardController::class,
                'index',
            ]
        )->name('dashboard');

        Route::resource(
            'organizations',
            OrganizationController::class
        );

        Route::resource(
            'modules',
            ModuleController::class
        )->except([
            'destroy',
        ]);

        Route::post(
            '/modules/{module}/toggle-status',
            [
                ModuleController::class,
                'toggleStatus',
            ]
        )->name(
            'modules.toggle-status'
        );

        Route::resource(
            'users',
            UserController::class
        )->except([
            'destroy',
        ]);

        Route::post(
            '/users/{user}/toggle-status',
            [
                UserController::class,
                'toggleStatus',
            ]
        )->name('users.toggle-status');

        Route::post(
            '/users/{user}/memberships',
            [
                UserController::class,
                'addMembership',
            ]
        )->name('users.memberships.store');

        Route::post(
            '/users/{user}/organizations/{organization}/default',
            [
                UserController::class,
                'setDefaultOrganization',
            ]
        )->name('users.organizations.default');

        Route::delete(
            '/users/{user}/organizations/{organization}',
            [
                UserController::class,
                'removeMembership',
            ]
        )->name('users.organizations.destroy');

        Route::resource(
            'plans',
            SubscriptionPlanController::class
        )->except([
            'destroy',
        ]);

        Route::post(
            '/plans/{plan}/toggle-status',
            [
                SubscriptionPlanController::class,
                'toggleStatus',
            ]
        )->name(
            'plans.toggle-status'
        );

        Route::get(
            '/subscriptions',
            [
                OrganizationSubscriptionController::class,
                'index',
            ]
        )->name(
            'subscriptions.index'
        );

        Route::get(
            '/subscriptions/create',
            [
                OrganizationSubscriptionController::class,
                'create',
            ]
        )->name(
            'subscriptions.create'
        );

        Route::post(
            '/subscriptions',
            [
                OrganizationSubscriptionController::class,
                'store',
            ]
        )->name(
            'subscriptions.store'
        );

        Route::get(
            '/subscriptions/{subscription}',
            [
                OrganizationSubscriptionController::class,
                'show',
            ]
        )->name(
            'subscriptions.show'
        );

        Route::post(
            '/subscriptions/{subscription}/activate',
            [
                OrganizationSubscriptionController::class,
                'activate',
            ]
        )->name(
            'subscriptions.activate'
        );

        Route::post(
            '/subscriptions/{subscription}/suspend',
            [
                OrganizationSubscriptionController::class,
                'suspend',
            ]
        )->name(
            'subscriptions.suspend'
        );

        Route::post(
            '/subscriptions/{subscription}/cancel',
            [
                OrganizationSubscriptionController::class,
                'cancel',
            ]
        )->name(
            'subscriptions.cancel'
        );

        /*
|--------------------------------------------------------------------------
| Invoices
|--------------------------------------------------------------------------
*/

        Route::get(
            '/invoices',
            [
                InvoiceController::class,
                'index',
            ]
        )->name('invoices.index');

        Route::get(
            '/invoices/create',
            [
                InvoiceController::class,
                'create',
            ]
        )->name('invoices.create');

        Route::post(
            '/invoices',
            [
                InvoiceController::class,
                'store',
            ]
        )->name('invoices.store');

        Route::get(
            '/invoices/create/manual',
            [
                InvoiceController::class,
                'createManual',
            ]
        )->name(
            'invoices.manual.create'
        );

        Route::post(
            '/invoices/manual',
            [
                InvoiceController::class,
                'storeManual',
            ]
        )->name(
            'invoices.manual.store'
        );

        Route::get(
            '/invoices/{invoice}',
            [
                InvoiceController::class,
                'show',
            ]
        )->name('invoices.show');

        Route::post(
            '/invoices/{invoice}/issue',
            [
                InvoiceController::class,
                'issue',
            ]
        )->name('invoices.issue');

        Route::post(
            '/invoices/{invoice}/cancel',
            [
                InvoiceController::class,
                'cancel',
            ]
        )->name('invoices.cancel');

        /*
|--------------------------------------------------------------------------
| Organization Apps
|--------------------------------------------------------------------------
*/

        Route::get(
            '/organizations/{organization}/apps',
            [
                OrganizationAppController::class,
                'index',
            ]
        )->name(
            'organizations.apps.index'
        );

        Route::put(
            '/organizations/{organization}/apps',
            [
                OrganizationAppController::class,
                'update',
            ]
        )->name(
            'organizations.apps.update'
        );


        /*
|--------------------------------------------------------------------------
| Payments
|--------------------------------------------------------------------------
*/

        Route::get(
            '/payments',
            [
                PaymentController::class,
                'index',
            ]
        )->name('payments.index');

        Route::get(
            '/payments/create',
            [
                PaymentController::class,
                'create',
            ]
        )->name('payments.create');

        Route::post(
            '/payments',
            [
                PaymentController::class,
                'store',
            ]
        )->name('payments.store');

        Route::get(
            '/payments/{payment}',
            [
                PaymentController::class,
                'show',
            ]
        )->name('payments.show');

        Route::post(
            '/payments/{payment}/cancel',
            [
                PaymentController::class,
                'cancel',
            ]
        )->name('payments.cancel');

        /*
|--------------------------------------------------------------------------
| Integrations
|--------------------------------------------------------------------------
*/

        Route::resource(
            'integrations',
            IntegrationController::class
        )->except([
            'destroy',
        ]);

        Route::post(
            '/integrations/{integration}/toggle-status',
            [
                IntegrationController::class,
                'toggleStatus',
            ]
        )->name(
            'integrations.toggle-status'
        );


        /*
|--------------------------------------------------------------------------
| Audit Logs
|--------------------------------------------------------------------------
*/

        Route::get(
            '/audit-logs',
            [
                AuditLogController::class,
                'index',
            ]
        )->name(
            'audit-logs.index'
        );

        Route::get(
            '/audit-logs/{auditLog}',
            [
                AuditLogController::class,
                'show',
            ]
        )->name(
            'audit-logs.show'
        );


        /*
|--------------------------------------------------------------------------
| Platform Settings
|--------------------------------------------------------------------------
*/

        Route::get(
            '/platform-settings',
            [
                PlatformSettingController::class,
                'index',
            ]
        )->name(
            'platform-settings.index'
        );

        Route::put(
            '/platform-settings',
            [
                PlatformSettingController::class,
                'update',
            ]
        )->name(
            'platform-settings.update'
        );

        /*
        |--------------------------------------------------------------------------
        | Registration Invites
        |--------------------------------------------------------------------------
        */

        Route::get(
            '/registration-invites',
            [
                RegistrationInviteController::class,
                'index',
            ]
        )->name(
            'registration-invites.index'
        );

        Route::get(
            '/registration-invites/create',
            [
                RegistrationInviteController::class,
                'create',
            ]
        )->name(
            'registration-invites.create'
        );

        Route::post(
            '/registration-invites',
            [
                RegistrationInviteController::class,
                'store',
            ]
        )->name(
            'registration-invites.store'
        );

        Route::post(
            '/registration-invites/{invite}/revoke',
            [
                RegistrationInviteController::class,
                'revoke',
            ]
        )->name(
            'registration-invites.revoke'
        );

        Route::post(
            '/registration-invites/{invite}/regenerate',
            [
                RegistrationInviteController::class,
                'regenerate',
            ]
        )->name(
            'registration-invites.regenerate'
        );

        /*
        |--------------------------------------------------------------------------
        | Registration Requests
        |--------------------------------------------------------------------------
        */

        Route::get(
            '/registration-requests',
            [
                RegistrationRequestController::class,
                'index',
            ]
        )->name(
            'registration-requests.index'
        );

        Route::post(
            '/registration-requests/{registration}/approve',
            [
                RegistrationRequestController::class,
                'approve',
            ]
        )->name(
            'registration-requests.approve'
        );

        Route::post(
            '/registration-requests/{registration}/reject',
            [
                RegistrationRequestController::class,
                'reject',
            ]
        )->name(
            'registration-requests.reject'
        );
    });

require __DIR__ . '/hris.php';
require __DIR__ . '/hris-attendance-map.php';
require __DIR__ . '/sales.php';
require __DIR__ . '/performance.php';

/*
|--------------------------------------------------------------------------
| Organization Workspace
|--------------------------------------------------------------------------
|
| WAJIB berada di bawah static route seperti:
|
| /login
| /register
| /join/*
| /superadmin/*
|
| karena slug organization berada langsung di root.
|
*/

/*
|--------------------------------------------------------------------------
| Organization Workspace
|--------------------------------------------------------------------------
|
| WAJIB diletakkan setelah route statis.
|
*/

Route::middleware([
    'auth',
    'organization.access',
])
    ->group(
        function () {
            Route::get(
                '/{organization:slug}',
                [
                    \App\Http\Controllers\Workspace\DashboardController::class,
                    'index',
                ]
            )->name(
                'workspace.dashboard'
            );
        }
    );
