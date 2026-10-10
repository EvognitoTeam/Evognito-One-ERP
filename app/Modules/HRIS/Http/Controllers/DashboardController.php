<?php

namespace App\Modules\HRIS\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Models\Organization;
use Illuminate\View\View;

class DashboardController extends Controller
{
    public function index(
        Organization $organization
    ): View {
        /*
        |--------------------------------------------------------------------------
        | Core Organization Data
        |--------------------------------------------------------------------------
        |
        | HRIS tidak membuat Employee,
        | Branch, Department, atau Position
        | miliknya sendiri.
        |
        */

        $organization->loadCount([
            'employees',
            'branches',
            'departments',
            'positions',
        ]);

        /*
        |--------------------------------------------------------------------------
        | Employee Counts
        |--------------------------------------------------------------------------
        */

        $totalEmployees =
            $organization
            ->employees()
            ->count();

        $activeEmployees =
            $organization
            ->employees()
            ->where(
                'status',
                'active'
            )
            ->count();

        $inactiveEmployees =
            max(
                0,
                $totalEmployees
                    - $activeEmployees
            );

        /*
        |--------------------------------------------------------------------------
        | Current Module
        |--------------------------------------------------------------------------
        */

        $module =
            request()
            ->attributes
            ->get(
                'currentModule'
            );

        return view(
            'modules.hris.dashboard',
            compact(
                'organization',
                'module',
                'totalEmployees',
                'activeEmployees',
                'inactiveEmployees'
            )
        );
    }
}
