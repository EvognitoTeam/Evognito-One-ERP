<?php

namespace App\Modules\Sales\Services;

use App\Models\Organization;
use App\Modules\Sales\Models\SalesPolicy;

class SalesTrackingPolicyService
{
    public function resolve(Organization $organization): array
    {
        $policy = SalesPolicy::resolveFor($organization);

        return [
            'enabled' => (bool) $policy->tracking_enabled,
            'moving_interval_seconds' => (int) $policy->tracking_moving_interval_seconds,
            'active_interval_seconds' => (int) $policy->tracking_active_interval_seconds,
            'idle_interval_seconds' => (int) $policy->tracking_idle_interval_seconds,
            'min_distance_meters' => (int) $policy->tracking_min_distance_meters,
            'accuracy_limit_meters' => (int) $policy->tracking_accuracy_limit_meters,
            'work_start' => substr((string) $policy->tracking_work_start, 0, 5),
            'work_end' => substr((string) $policy->tracking_work_end, 0, 5),
            'retention_days' => (int) $policy->tracking_retention_days,
        ];
    }
}
