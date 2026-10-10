<?php

namespace App\Modules\Sales\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Models\Organization;
use App\Modules\Sales\Services\SalesDashboardService;
use Illuminate\View\View;

class SalesDashboardController extends Controller
{
    public function __construct(
        private readonly SalesDashboardService $dashboardService
    ) {
    }

    public function index(
        Organization $organization
    ): View {
        $data = $this->dashboardService->build(
            $organization
        );

        return view(
            'modules.sales.dashboard',
            array_merge(
                [
                    'organization' => $organization,
                ],
                $data
            )
        );
    }
}
