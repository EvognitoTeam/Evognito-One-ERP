<?php

return [
    'clock_in_success' =>
        'Clock in completed successfully.',

    'clock_out_success' =>
        'Clock out completed successfully.',

    'errors' => [
        'employee_not_linked' =>
            'This account is not linked to an employee in this organization.',

        'employee_inactive' =>
            'The employee account is inactive.',

        'manual_disabled' =>
            'Manual attendance adjustment is disabled by the attendance policy.',

        'clock_in_required' =>
            'Clock-in time is required.',

        'clock_out_before_clock_in' =>
            'Clock-out time cannot be earlier than clock-in time.',

        'duplicate' =>
            'The employee already has attendance for this date.',

        'early_clock_out' =>
            'Clocking out before the scheduled end time is not allowed.',

        'no_open_attendance' =>
            'There is no active clock-in attendance that can be clocked out.',

        'on_annual_leave' =>
            'You are scheduled for annual leave on :date.',

        'on_leave' =>
            'You are on :type on :date.',

        'non_workday' =>
            'Attendance is not allowed on a non-working day.',

        'holiday' =>
            'Attendance is not allowed because :date is a holiday: :holiday.',

        'too_early' =>
            'Clock in is not available yet. Attendance opens at :time.',

        'too_late' =>
            'The clock-in window ended at :time.',

        'photo_in_required' =>
            'A clock-in photo is required.',

        'photo_out_required' =>
            'A clock-out photo is required.',

        'gps_accuracy' =>
            'GPS accuracy is too low. Current accuracy is :accuracy meters; maximum allowed is :maximum meters.',

        'location_in_required' =>
            'Device location is required when clocking in.',

        'location_out_required' =>
            'Device location is required when clocking out.',

        'no_work_location' =>
            'No active work location is available for this employee.',

        'outside_geofence' =>
            'You are outside :location. You are approximately :distance meters from the location point, while the allowed radius is :radius meters.',
    ],
];
