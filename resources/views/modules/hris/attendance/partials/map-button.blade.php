@php
    $workLocation =
        $attendance->workLocation;

    $payload = [
        'employee' => [
            'name' =>
                $attendance->employee?->name
                ?? $attendance->employee?->user?->name
                ?? '-',

            'employee_number' =>
                $attendance->employee?->employee_number
                ?? null,
        ],

        'attendance' => [
            'date' =>
                $attendance->attendance_date
                    ?->format('Y-m-d'),

            'clock_in' =>
                $attendance->clock_in
                    ?->format('Y-m-d H:i:s'),

            'clock_out' =>
                $attendance->clock_out
                    ?->format('Y-m-d H:i:s'),

            'method' =>
                $attendance->method,

            'status' =>
                $attendance->status,
        ],

        'clock_in' => [
            'latitude' =>
                $attendance->clock_in_latitude !== null
                    ? (float) $attendance->clock_in_latitude
                    : null,

            'longitude' =>
                $attendance->clock_in_longitude !== null
                    ? (float) $attendance->clock_in_longitude
                    : null,

            'accuracy_meters' =>
                $attendance->clock_in_accuracy_meters !== null
                    ? (float) $attendance->clock_in_accuracy_meters
                    : null,

            'distance_meters' =>
                $attendance->clock_in_distance_meters !== null
                    ? (float) $attendance->clock_in_distance_meters
                    : null,

            'within_geofence' =>
                $attendance->clock_in_within_geofence,
        ],

        'clock_out' => [
            'latitude' =>
                $attendance->clock_out_latitude !== null
                    ? (float) $attendance->clock_out_latitude
                    : null,

            'longitude' =>
                $attendance->clock_out_longitude !== null
                    ? (float) $attendance->clock_out_longitude
                    : null,

            'accuracy_meters' =>
                $attendance->clock_out_accuracy_meters !== null
                    ? (float) $attendance->clock_out_accuracy_meters
                    : null,

            'distance_meters' =>
                $attendance->clock_out_distance_meters !== null
                    ? (float) $attendance->clock_out_distance_meters
                    : null,

            'within_geofence' =>
                $attendance->clock_out_within_geofence,
        ],

        'work_location' =>
            $workLocation
                ? [
                    'name' =>
                        $workLocation->name,

                    'address' =>
                        $workLocation->address,

                    'latitude' =>
                        $workLocation->latitude !== null
                            ? (float) $workLocation->latitude
                            : null,

                    'longitude' =>
                        $workLocation->longitude !== null
                            ? (float) $workLocation->longitude
                            : null,

                    'radius_meters' =>
                        (int) $workLocation->radius_meters,

                    'geofence_enabled' =>
                        (bool) $workLocation->geofence_enabled,
                ]
                : null,
    ];

    $hasMapPoint =
        $payload['clock_in']['latitude'] !== null
        ||
        $payload['clock_out']['latitude'] !== null
        ||
        (
            $payload['work_location']
            &&
            $payload['work_location']['latitude'] !== null
        );
@endphp


<button
    type="button"
    x-data
    x-on:click="$dispatch(
        'attendance-map-open',
        {{ Illuminate\Support\Js::from($payload) }}
    )"
    @disabled(!$hasMapPoint)
    class="inline-flex
           h-9
           items-center
           justify-center
           gap-2
           rounded-xl
           border
           px-3
           text-[10px]
           font-semibold
           transition
           {{
               $hasMapPoint
                   ? 'border-blue-100 bg-blue-50 text-blue-700 hover:bg-blue-100'
                   : 'cursor-not-allowed border-black/[0.05] bg-[#f6f5f1] text-[#bbb8b0]'
           }}"
>
    <svg
        xmlns="http://www.w3.org/2000/svg"
        class="h-3.5 w-3.5"
        viewBox="0 0 24 24"
        fill="none"
        stroke="currentColor"
        stroke-width="1.8"
    >
        <path
            d="M20 10c0 5-8 11-8 11S4 15 4 10a8 8 0 1 1 16 0Z"
        />

        <circle
            cx="12"
            cy="10"
            r="2.5"
        />
    </svg>

    {{ __('attendance-map.view_map') }}
</button>
