<?php

namespace App\Modules\HRIS\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Models\Organization;
use App\Models\Position;
use App\Modules\HRIS\Models\HrisPositionSetting;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class PositionScheduleController extends Controller
{
    /*
    |--------------------------------------------------------------------------
    | Index
    |--------------------------------------------------------------------------
    */

    public function index(
        Organization $organization
    ): View {
        /*
        |--------------------------------------------------------------------------
        | Core Positions
        |--------------------------------------------------------------------------
        */

        $positions =
            $organization
            ->positions()
            ->orderBy('name')
            ->get();

        /*
        |--------------------------------------------------------------------------
        | Existing HRIS Settings
        |--------------------------------------------------------------------------
        */

        $settings =
            HrisPositionSetting::query()
            ->where(
                'organization_id',
                $organization->id
            )
            ->whereIn(
                'position_id',
                $positions->pluck('id')
            )
            ->get()
            ->keyBy('position_id');

        /*
        |--------------------------------------------------------------------------
        | Summary
        |--------------------------------------------------------------------------
        */

        $totalPositions =
            $positions->count();

        $configuredPositions =
            $settings->count();

        $mobilePositions =
            $settings
            ->where(
                'is_mobile',
                true
            )
            ->count();

        $unconfiguredPositions =
            max(
                0,
                $totalPositions
                    -
                    $configuredPositions
            );

        return view(
            'modules.hris.configuration.position-schedules',
            compact(
                'organization',
                'positions',
                'settings',

                'totalPositions',
                'configuredPositions',
                'mobilePositions',
                'unconfiguredPositions'
            )
        );
    }


    /*
    |--------------------------------------------------------------------------
    | Update Single Position
    |--------------------------------------------------------------------------
    */

    public function update(
        Request $request,
        Organization $organization,
        Position $position
    ): RedirectResponse {
        /*
        |--------------------------------------------------------------------------
        | Organization Guard
        |--------------------------------------------------------------------------
        */

        if (
            (int) $position->organization_id
            !==
            (int) $organization->id
        ) {
            abort(404);
        }

        /*
        |--------------------------------------------------------------------------
        | Validation
        |--------------------------------------------------------------------------
        */

        $validated =
            $request->validate([
                'clock_in' => [
                    'required',
                    'date_format:H:i',
                ],

                'clock_out' => [
                    'required',
                    'date_format:H:i',
                ],

                'late_tolerance_minutes' => [
                    'required',
                    'integer',
                    'min:0',
                    'max:240',
                ],

                'is_mobile' => [
                    'nullable',
                    'boolean',
                ],
            ]);

        /*
        |--------------------------------------------------------------------------
        | Save
        |--------------------------------------------------------------------------
        */

        HrisPositionSetting::query()
            ->updateOrCreate(
                [
                    'organization_id' =>
                    $organization->id,

                    'position_id' =>
                    $position->id,
                ],
                [
                    'clock_in' =>
                    $validated['clock_in'],

                    'clock_out' =>
                    $validated['clock_out'],

                    'late_tolerance_minutes' =>
                    $validated['late_tolerance_minutes'],

                    'is_mobile' =>
                    $request->boolean(
                        'is_mobile'
                    ),
                ]
            );

        return back()
            ->with(
                'success',
                'Attendance schedule untuk '
                    . $position->name
                    . ' berhasil diperbarui.'
            );
    }


    /*
    |--------------------------------------------------------------------------
    | Apply Default Schedule
    |--------------------------------------------------------------------------
    */

    public function applyDefault(
        Request $request,
        Organization $organization
    ): RedirectResponse {
        $validated =
            $request->validate([
                'clock_in' => [
                    'required',
                    'date_format:H:i',
                ],

                'clock_out' => [
                    'required',
                    'date_format:H:i',
                ],

                'late_tolerance_minutes' => [
                    'required',
                    'integer',
                    'min:0',
                    'max:240',
                ],

                'apply_mode' => [
                    'required',

                    Rule::in([
                        'unconfigured',
                        'all',
                    ]),
                ],
            ]);

        $positions =
            $organization
            ->positions()
            ->select([
                'id',
                'organization_id',
            ])
            ->get();

        DB::transaction(
            function () use (
                $positions,
                $validated,
                $organization
            ) {
                foreach (
                    $positions
                    as $position
                ) {
                    $query =
                        HrisPositionSetting::query()
                        ->where(
                            'organization_id',
                            $organization->id
                        )
                        ->where(
                            'position_id',
                            $position->id
                        );

                    /*
                    |--------------------------------------------------------------------------
                    | Unconfigured Only
                    |--------------------------------------------------------------------------
                    */

                    if (
                        $validated['apply_mode']
                        === 'unconfigured'
                        &&
                        $query->exists()
                    ) {
                        continue;
                    }

                    /*
                    |--------------------------------------------------------------------------
                    | Preserve Mobile Flag
                    |--------------------------------------------------------------------------
                    |
                    | Bulk default tidak mengubah is_mobile.
                    |
                    */

                    $existing =
                        $query->first();

                    HrisPositionSetting::query()
                        ->updateOrCreate(
                            [
                                'organization_id' =>
                                $organization->id,

                                'position_id' =>
                                $position->id,
                            ],
                            [
                                'clock_in' =>
                                $validated['clock_in'],

                                'clock_out' =>
                                $validated['clock_out'],

                                'late_tolerance_minutes' =>
                                $validated['late_tolerance_minutes'],

                                'is_mobile' =>
                                $existing
                                    ?->is_mobile
                                    ?? false,
                            ]
                        );
                }
            }
        );

        return back()
            ->with(
                'success',
                $validated['apply_mode']
                    === 'all'
                    ? 'Default schedule berhasil diterapkan ke semua position.'
                    : 'Default schedule berhasil diterapkan ke position yang belum dikonfigurasi.'
            );
    }
}
