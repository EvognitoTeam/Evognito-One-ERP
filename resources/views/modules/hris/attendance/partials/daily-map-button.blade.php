<a
    href="{{ route(
        'hris.attendance.map',
        [
            'organization' =>
                $organization->slug,

            'date' =>
                request('date')
                ?? now()->toDateString(),
        ]
    ) }}"
    class="inline-flex
           h-10
           items-center
           justify-center
           gap-2
           rounded-xl
           border
           border-blue-100
           bg-blue-50
           px-4
           text-[10px]
           font-semibold
           text-blue-700
           transition
           hover:bg-blue-100"
>
    <svg
        xmlns="http://www.w3.org/2000/svg"
        class="h-4 w-4"
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

    {{ __('attendance-daily-map.open_daily_map') }}
</a>
