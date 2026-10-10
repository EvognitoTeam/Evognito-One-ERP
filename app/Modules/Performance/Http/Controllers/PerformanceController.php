<?php

namespace App\Modules\Performance\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Models\Organization;

abstract class PerformanceController extends Controller
{
    protected function guardOrganization(Organization $organization, object $model): void
    {
        abort_unless(
            isset($model->organization_id) && (int) $model->organization_id === (int) $organization->id,
            404
        );
    }
}
