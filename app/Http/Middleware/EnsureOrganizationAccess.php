<?php

namespace App\Http\Middleware;

use App\Models\Organization;
use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\View;
use Symfony\Component\HttpFoundation\Response;

class EnsureOrganizationAccess
{
    public function handle(
        Request $request,
        Closure $next
    ): Response {
        /*
        |--------------------------------------------------------------------------
        | Authentication
        |--------------------------------------------------------------------------
        */

        $user = $request->user();

        if (!$user) {
            return redirect()
                ->route('login');
        }


        /*
        |--------------------------------------------------------------------------
        | Current Organization
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
        | Super Admin
        |--------------------------------------------------------------------------
        |
        | Superadmin boleh masuk ke seluruh organization.
        |
        */

        if (
            (bool) $user->is_super_admin
        ) {
            $availableOrganizations =
                Organization::query()
                    ->orderBy('name')
                    ->get();

            /*
            |--------------------------------------------------------------------------
            | Share Workspace Context
            |--------------------------------------------------------------------------
            */

            app()->instance(
                'currentOrganization',
                $organization
            );

            $request
                ->attributes
                ->set(
                    'currentOrganization',
                    $organization
                );

            View::share(
                'currentOrganization',
                $organization
            );

            View::share(
                'availableOrganizations',
                $availableOrganizations
            );

            return $next($request);
        }


        /*
        |--------------------------------------------------------------------------
        | Organization Status
        |--------------------------------------------------------------------------
        */

        if (
            $organization->status
            !== 'active'
        ) {
            abort(
                403,
                'Organization is not active.'
            );
        }


        /*
        |--------------------------------------------------------------------------
        | Validate Membership
        |--------------------------------------------------------------------------
        */

        $membership =
            $user
                ->organizations()
                ->where(
                    'organizations.id',
                    $organization->id
                )
                ->wherePivot(
                    'status',
                    'active'
                )
                ->first();

        if (!$membership) {
            abort(
                403,
                'You do not have access to this organization.'
            );
        }


        /*
        |--------------------------------------------------------------------------
        | Available Organizations
        |--------------------------------------------------------------------------
        |
        | Organization switcher di workspace memakai data ini.
        |
        */

        $availableOrganizations =
            $user
                ->organizations()
                ->wherePivot(
                    'status',
                    'active'
                )
                ->where(
                    'organizations.status',
                    'active'
                )
                ->orderBy(
                    'organizations.name'
                )
                ->get();


        /*
        |--------------------------------------------------------------------------
        | Current Organization Context
        |--------------------------------------------------------------------------
        */

        app()->instance(
            'currentOrganization',
            $organization
        );

        $request
            ->attributes
            ->set(
                'currentOrganization',
                $organization
            );


        /*
        |--------------------------------------------------------------------------
        | Share With All Workspace Views
        |--------------------------------------------------------------------------
        |
        | Dengan ini:
        |
        | /{organization}
        | /{organization}/hris
        | /{organization}/performance
        | /{organization}/sales
        | /{organization}/lms
        |
        | semuanya otomatis mendapatkan:
        |
        | $currentOrganization
        | $availableOrganizations
        |
        */

        View::share(
            'currentOrganization',
            $organization
        );

        View::share(
            'availableOrganizations',
            $availableOrganizations
        );


        return $next($request);
    }
}