<?php

namespace App\Http\Middleware;

use App\Models\Module;
use App\Models\Organization;
use Carbon\Carbon;
use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Symfony\Component\HttpFoundation\Response;

class EnsureModuleAccess
{
    public function handle(
        Request $request,
        Closure $next,
        string $moduleCode
    ): Response {
        /*
        |--------------------------------------------------------------------------
        | Authentication
        |--------------------------------------------------------------------------
        */

        if (!$request->user()) {
            return redirect()
                ->route('login');
        }

        /*
        |--------------------------------------------------------------------------
        | Organization
        |--------------------------------------------------------------------------
        */

        $organization =
            $request->route('organization');

        if (!$organization instanceof Organization) {
            abort(
                404,
                'Organization not found.'
            );
        }

        /*
        |--------------------------------------------------------------------------
        | Module
        |--------------------------------------------------------------------------
        */

        $module = Module::query()
            ->where(
                'code',
                $moduleCode
            )
            ->where(
                'status',
                'active'
            )
            ->first();

        if (!$module) {
            abort(
                404,
                'Application module not found.'
            );
        }

        /*
        |--------------------------------------------------------------------------
        | Organization Entitlement
        |--------------------------------------------------------------------------
        |
        | organization_modules menjadi sumber akses aplikasi.
        |
        */

        $entitlement =
            DB::table(
                'organization_modules'
            )
            ->where(
                'organization_id',
                $organization->id
            )
            ->where(
                'module_id',
                $module->id
            )
            ->first();

        $enabled =
            $entitlement
            && (bool) $entitlement->enabled
            && $entitlement->status
            === 'active';

        /*
        |--------------------------------------------------------------------------
        | Expiration
        |--------------------------------------------------------------------------
        */

        if (
            $enabled
            && !empty($entitlement->expires_at)
        ) {
            $expiresAt =
                Carbon::parse(
                    $entitlement->expires_at
                );

            if ($expiresAt->isPast()) {
                $enabled = false;
            }
        }

        /*
        |--------------------------------------------------------------------------
        | Locked
        |--------------------------------------------------------------------------
        */

        if (!$enabled) {
            return redirect()
                ->route(
                    'workspace.dashboard',
                    [
                        'organization' =>
                        $organization->slug,
                    ]
                )
                ->with(
                    'warning',
                    $module->name
                        . ' belum aktif untuk organization ini. Aktivasi atau pembayaran diperlukan.'
                );
        }

        /*
        |--------------------------------------------------------------------------
        | Current Module
        |--------------------------------------------------------------------------
        */

        app()->instance(
            'currentModule',
            $module
        );

        $request
            ->attributes
            ->set(
                'currentModule',
                $module
            );

        return $next($request);
    }
}
