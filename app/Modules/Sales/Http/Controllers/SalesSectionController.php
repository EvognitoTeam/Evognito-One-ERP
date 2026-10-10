<?php

namespace App\Modules\Sales\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Models\Organization;
use Illuminate\View\View;

class SalesSectionController extends Controller
{
    public function team(
        Organization $organization
    ): View {
        return $this->section(
            $organization,
            'team'
        );
    }

    public function customers(
        Organization $organization
    ): View {
        return $this->section(
            $organization,
            'customers'
        );
    }

    public function products(
        Organization $organization
    ): View {
        return $this->section(
            $organization,
            'products'
        );
    }

    public function visits(
        Organization $organization
    ): View {
        return $this->section(
            $organization,
            'visits'
        );
    }

    public function monitoring(
        Organization $organization
    ): View {
        return $this->section(
            $organization,
            'monitoring'
        );
    }

    public function records(
        Organization $organization
    ): View {
        return $this->section(
            $organization,
            'records'
        );
    }

    public function reports(
        Organization $organization
    ): View {
        return $this->section(
            $organization,
            'reports'
        );
    }

    public function configuration(
        Organization $organization
    ): View {
        return $this->section(
            $organization,
            'configuration'
        );
    }

    private function section(
        Organization $organization,
        string $section
    ): View {
        $allowed = [
            'team',
            'customers',
            'products',
            'visits',
            'monitoring',
            'records',
            'reports',
            'configuration',
        ];

        abort_unless(
            in_array(
                $section,
                $allowed,
                true
            ),
            404
        );

        return view(
            'modules.sales.section',
            [
                'organization' => $organization,
                'section' => $section,
            ]
        );
    }
}
