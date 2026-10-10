<?php

namespace App\Http\Controllers\SuperAdmin;

use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;

class DashboardController extends Controller
{
    public function index(): View
    {
        $stats = [
            'organizations' => DB::table('organizations')
                ->whereNull('deleted_at')
                ->count(),

            'users' => DB::table('users')
                ->whereNull('deleted_at')
                ->count(),

            'employees' => DB::table('employees')
                ->whereNull('deleted_at')
                ->count(),

            'modules' => DB::table('modules')
                ->where('status', 'active')
                ->count(),

            'active_subscriptions' => DB::table(
                'organization_subscriptions'
            )
                ->where('status', 'active')
                ->count(),

            'unpaid_invoices' => DB::table('invoices')
                ->whereIn('status', [
                    'issued',
                    'pending',
                    'overdue',
                ])
                ->whereNull('deleted_at')
                ->count(),
        ];

        return view(
            'superadmin.dashboard',
            compact('stats')
        );
    }
}
