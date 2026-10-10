<?php

namespace App\Modules\HRIS\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Models\Organization;
use App\Modules\HRIS\Models\HrisAttendancePolicy;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class AttendancePolicyController extends Controller
{
    /*
    |--------------------------------------------------------------------------
    | Index
    |--------------------------------------------------------------------------
    */

    public function index(
        Organization $organization
    ): View {
        $policy =
            HrisAttendancePolicy::resolveFor(
                $organization
            );

        $days = [
            1 => 'Monday',
            2 => 'Tuesday',
            3 => 'Wednesday',
            4 => 'Thursday',
            5 => 'Friday',
            6 => 'Saturday',
            7 => 'Sunday',
        ];

        return view(
            'modules.hris.configuration.attendance-policy',
            compact(
                'organization',
                'policy',
                'days'
            )
        );
    }


    /*
    |--------------------------------------------------------------------------
    | Update
    |--------------------------------------------------------------------------
    */

    public function update(
        Request $request,
        Organization $organization
    ): RedirectResponse {
        $validated =
            $request->validate([
                'work_days' => [
                    'required',
                    'array',
                    'min:1',
                ],

                'work_days.*' => [
                    'required',
                    'integer',

                    Rule::in([
                        1,
                        2,
                        3,
                        4,
                        5,
                        6,
                        7,
                    ]),
                ],

                'geofence_mode' => [
                    'required',

                    Rule::in([
                        'strict',
                        'mobile_remote',
                        'record_only',
                    ]),
                ],

                'maximum_gps_accuracy_meters' => [
                    'required',
                    'integer',
                    'min:5',
                    'max:5000',
                ],

                'clock_in_before_minutes' => [
                    'required',
                    'integer',
                    'min:0',
                    'max:1440',
                ],

                'clock_in_after_minutes' => [
                    'required',
                    'integer',
                    'min:0',
                    'max:1440',
                ],

                'require_location' => [
                    'nullable',
                    'boolean',
                ],

                'require_photo_clock_in' => [
                    'nullable',
                    'boolean',
                ],

                'require_photo_clock_out' => [
                    'nullable',
                    'boolean',
                ],

                'allow_early_clock_out' => [
                    'nullable',
                    'boolean',
                ],

                'allow_attendance_on_holidays' => [
                    'nullable',
                    'boolean',
                ],

                'allow_attendance_on_non_workdays' => [
                    'nullable',
                    'boolean',
                ],

                'allow_manual_adjustment' => [
                    'nullable',
                    'boolean',
                ],
            ]);

        $workDays =
            collect(
                $validated['work_days']
            )
            ->map(
                fn($day) =>
                (int) $day
            )
            ->unique()
            ->sort()
            ->values()
            ->all();

        HrisAttendancePolicy::query()
            ->updateOrCreate(
                [
                    'organization_id' =>
                    $organization->id,
                ],
                [
                    'work_days' =>
                    $workDays,

                    'require_location' =>
                    $request->boolean(
                        'require_location'
                    ),

                    'require_photo_clock_in' =>
                    $request->boolean(
                        'require_photo_clock_in'
                    ),

                    'require_photo_clock_out' =>
                    $request->boolean(
                        'require_photo_clock_out'
                    ),

                    'maximum_gps_accuracy_meters' =>
                    $validated['maximum_gps_accuracy_meters'],

                    'geofence_mode' =>
                    $validated['geofence_mode'],

                    'clock_in_before_minutes' =>
                    $validated['clock_in_before_minutes'],

                    'clock_in_after_minutes' =>
                    $validated['clock_in_after_minutes'],

                    'allow_early_clock_out' =>
                    $request->boolean(
                        'allow_early_clock_out'
                    ),

                    'allow_attendance_on_holidays' =>
                    $request->boolean(
                        'allow_attendance_on_holidays'
                    ),

                    'allow_attendance_on_non_workdays' =>
                    $request->boolean(
                        'allow_attendance_on_non_workdays'
                    ),

                    'allow_manual_adjustment' =>
                    $request->boolean(
                        'allow_manual_adjustment'
                    ),
                ]
            );

        return back()
            ->with(
                'success',
                'Attendance Policy berhasil diperbarui.'
            );
    }
}
