@extends('layouts.workspace')

@section('title', __('attendance-report.title') . ' - HRIS')

@section('content')

    @php
        $employeeName = function ($employee) {
            return $employee->name ?? ($employee->user?->name ?? '-');
        };

        $formatMinutes = function ($minutes) {
            $minutes = (int) ($minutes ?? 0);

            $hours = intdiv($minutes, 60);

            $remaining = $minutes % 60;

            return $hours . 'h ' . $remaining . 'm';
        };

        $geofenceStatus = function ($attendance) {
            if ($attendance->clock_in_within_geofence === false || $attendance->clock_out_within_geofence === false) {
                return 'outside';
            }

            if ($attendance->clock_in_within_geofence === true) {
                return 'inside';
            }

            return 'unknown';
        };

        $currentQuery = request()->query();

        $todayQuery = array_merge($currentQuery, [
            'from' => now()->toDateString(),

            'to' => now()->toDateString(),
        ]);

        $monthQuery = array_merge($currentQuery, [
            'from' => now()->startOfMonth()->toDateString(),

            'to' => now()->endOfMonth()->toDateString(),
        ]);

        $lateQuery = array_merge($currentQuery, [
            'late' => 'yes',
        ]);

        $outsideQuery = array_merge($currentQuery, [
            'geofence' => 'outside',
        ]);

        $openQuery = array_merge($currentQuery, [
            'status' => 'open',
        ]);
    @endphp


    {{-- ============================================================= --}}
    {{-- HEADER                                                        --}}
    {{-- ============================================================= --}}

    <section
        class="flex
           flex-col
           gap-6
           xl:flex-row
           xl:items-end
           xl:justify-between">
        <div>
            <div
                class="text-xs
                   font-bold
                   uppercase
                   tracking-[0.12em]
                   text-blue-600">
                HRIS · {{ __('hris.navigation.attendance') }}
            </div>

            <h1
                class="mt-2
                   text-3xl
                   font-semibold
                   tracking-[-0.04em]
                   text-[#252421]">
                {{ __('attendance-report.title') }}
            </h1>

            <p
                class="mt-3
                   max-w-3xl
                   text-sm
                   leading-6
                   text-[#77746d]">
                {{ __('attendance-report.description') }}
            </p>
        </div>


        <div class="flex
               flex-wrap
               gap-3">
            <a href="{{ route('hris.attendance.map', [
                'organization' => $organization->slug,
            
                'date' => $filters['to'],
            ]) }}"
                class="inline-flex
                   h-11
                   items-center
                   justify-center
                   rounded-xl
                   border
                   border-blue-100
                   bg-blue-50
                   px-5
                   text-sm
                   font-semibold
                   text-blue-700
                   transition
                   hover:bg-blue-100">
                {{ __('attendance-report.daily_map') }}
            </a>


            <a href="{{ route(
                'hris.attendance.report.export-detailed',
                array_merge(
                    [
                        'organization' => $organization->slug,
                    ],
                    request()->query(),
                ),
            ) }}"
                class="inline-flex
                   h-11
                   items-center
                   justify-center
                   gap-2
                   rounded-xl
                   border
                   border-black/[0.08]
                   bg-white
                   px-5
                   text-sm
                   font-semibold
                   text-[#55524c]
                   transition
                   hover:bg-[#f6f5f1]">
                ↓ {{ __('attendance-report.export_detailed') }}
            </a>


            <a href="{{ route(
                'hris.attendance.report.export-summary',
                array_merge(
                    [
                        'organization' => $organization->slug,
                    ],
                    request()->query(),
                ),
            ) }}"
                class="inline-flex
                   h-11
                   items-center
                   justify-center
                   gap-2
                   rounded-xl
                   bg-[#1d1d1b]
                   px-5
                   text-sm
                   font-semibold
                   text-white
                   transition
                   hover:bg-black">
                ↓ {{ __('attendance-report.export_summary') }}
            </a>
        </div>
    </section>


    <div class="mt-8">
        @include('modules.hris.partials.navigation')
    </div>


    {{-- ============================================================= --}}
    {{-- QUICK FILTERS                                                 --}}
    {{-- ============================================================= --}}

    <section class="mt-6
           flex
           flex-wrap
           items-center
           gap-2">
        <span
            class="mr-2
               text-xs
               font-semibold
               uppercase
               tracking-wide
               text-[#8c8982]">
            {{ __('attendance-report.quick_filters') }}
        </span>


        <a href="{{ route(
            'hris.attendance.report.index',
            array_merge(
                [
                    'organization' => $organization->slug,
                ],
                $todayQuery,
            ),
        ) }}"
            class="rounded-full
               border
               border-black/[0.08]
               bg-white
               px-4 py-2
               text-xs
               font-semibold
               text-[#55524c]
               transition
               hover:bg-[#f6f5f1]">
            {{ __('attendance-report.today') }}
        </a>


        <a href="{{ route(
            'hris.attendance.report.index',
            array_merge(
                [
                    'organization' => $organization->slug,
                ],
                $monthQuery,
            ),
        ) }}"
            class="rounded-full
               border
               border-black/[0.08]
               bg-white
               px-4 py-2
               text-xs
               font-semibold
               text-[#55524c]
               transition
               hover:bg-[#f6f5f1]">
            {{ __('attendance-report.this_month') }}
        </a>


        <a href="{{ route(
            'hris.attendance.report.index',
            array_merge(
                [
                    'organization' => $organization->slug,
                ],
                $lateQuery,
            ),
        ) }}"
            class="rounded-full
               border
               border-amber-100
               bg-amber-50
               px-4 py-2
               text-xs
               font-semibold
               text-amber-700">
            {{ __('attendance-report.late_only') }}
        </a>


        <a href="{{ route(
            'hris.attendance.report.index',
            array_merge(
                [
                    'organization' => $organization->slug,
                ],
                $outsideQuery,
            ),
        ) }}"
            class="rounded-full
               border
               border-red-100
               bg-red-50
               px-4 py-2
               text-xs
               font-semibold
               text-red-700">
            {{ __('attendance-report.outside_only') }}
        </a>


        <a href="{{ route(
            'hris.attendance.report.index',
            array_merge(
                [
                    'organization' => $organization->slug,
                ],
                $openQuery,
            ),
        ) }}"
            class="rounded-full
               border
               border-orange-100
               bg-orange-50
               px-4 py-2
               text-xs
               font-semibold
               text-orange-700">
            {{ __('attendance-report.open_only') }}
        </a>


        @if ($activeFilterCount > 0)
            <span
                class="ml-2
                   rounded-full
                   bg-blue-50
                   px-3 py-2
                   text-xs
                   font-semibold
                   text-blue-700">
                {{ trans_choice('attendance-report.active_filters', $activeFilterCount, [
                    'count' => $activeFilterCount,
                ]) }}
            </span>
        @endif
    </section>


    {{-- ============================================================= --}}
    {{-- FILTER                                                        --}}
    {{-- ============================================================= --}}

    <section
        class="mt-4
           rounded-2xl
           border
           border-black/[0.06]
           bg-white
           p-6">
        <form method="GET"
            action="{{ route('hris.attendance.report.index', [
                'organization' => $organization->slug,
            ]) }}">
            <div class="grid
                   gap-5
                   md:grid-cols-2
                   xl:grid-cols-4">
                {{-- FROM --}}
                <div>
                    <label
                        class="mb-2
                           block
                           text-xs
                           font-semibold
                           text-[#66635d]">
                        {{ __('common.start_date') }}
                    </label>

                    <input type="date" name="from" value="{{ $filters['from'] }}"
                        class="h-11
                           w-full
                           rounded-xl
                           border
                           border-black/[0.08]
                           bg-[#faf9f6]
                           px-4
                           text-sm
                           text-[#33312d]
                           outline-none
                           transition
                           focus:border-blue-300
                           focus:bg-white
                           focus:ring-2
                           focus:ring-blue-50">
                </div>


                {{-- TO --}}
                <div>
                    <label
                        class="mb-2
                           block
                           text-xs
                           font-semibold
                           text-[#66635d]">
                        {{ __('common.end_date') }}
                    </label>

                    <input type="date" name="to" value="{{ $filters['to'] }}"
                        class="h-11
                           w-full
                           rounded-xl
                           border
                           border-black/[0.08]
                           bg-[#faf9f6]
                           px-4
                           text-sm
                           text-[#33312d]
                           outline-none
                           transition
                           focus:border-blue-300
                           focus:bg-white
                           focus:ring-2
                           focus:ring-blue-50">
                </div>


                {{-- EMPLOYEE --}}
                <div>
                    <label
                        class="mb-2
                           block
                           text-xs
                           font-semibold
                           text-[#66635d]">
                        {{ __('attendance-report.employee') }}
                    </label>

                    <select name="employee_id"
                        class="h-11
                           w-full
                           rounded-xl
                           border
                           border-black/[0.08]
                           bg-[#faf9f6]
                           px-4
                           text-sm
                           text-[#33312d]">
                        <option value="">
                            {{ __('attendance-report.all_employees') }}
                        </option>

                        @foreach ($employees as $employee)
                            <option value="{{ $employee->id }}" @selected((string) $filters['employee_id'] === (string) $employee->id)>
                                {{ $employeeName($employee) }}

                                @if ($employee->employee_number)
                                    — {{ $employee->employee_number }}
                                @endif
                            </option>
                        @endforeach
                    </select>
                </div>


                {{-- SEARCH --}}
                <div>
                    <label
                        class="mb-2
                           block
                           text-xs
                           font-semibold
                           text-[#66635d]">
                        {{ __('common.search') }}
                    </label>

                    <input type="search" name="search" value="{{ $filters['search'] }}"
                        placeholder="{{ __('attendance-report.search_placeholder') }}"
                        class="h-11
                           w-full
                           rounded-xl
                           border
                           border-black/[0.08]
                           bg-[#faf9f6]
                           px-4
                           text-sm
                           text-[#33312d]
                           outline-none
                           transition
                           focus:border-blue-300
                           focus:bg-white
                           focus:ring-2
                           focus:ring-blue-50">
                </div>


                {{-- BRANCH --}}
                <div>
                    <label
                        class="mb-2
                           block
                           text-xs
                           font-semibold
                           text-[#66635d]">
                        {{ __('attendance-report.branch') }}
                    </label>

                    <select name="branch_id"
                        class="h-11
                           w-full
                           rounded-xl
                           border
                           border-black/[0.08]
                           bg-[#faf9f6]
                           px-4
                           text-sm
                           text-[#33312d]">
                        <option value="">
                            {{ __('common.all') }}
                        </option>

                        @foreach ($branches as $branch)
                            <option value="{{ $branch->id }}" @selected((string) $filters['branch_id'] === (string) $branch->id)>
                                {{ $branch->name }}
                            </option>
                        @endforeach
                    </select>
                </div>


                {{-- POSITION --}}
                <div>
                    <label
                        class="mb-2
                           block
                           text-xs
                           font-semibold
                           text-[#66635d]">
                        {{ __('attendance-report.position') }}
                    </label>

                    <select name="position_id"
                        class="h-11
                           w-full
                           rounded-xl
                           border
                           border-black/[0.08]
                           bg-[#faf9f6]
                           px-4
                           text-sm
                           text-[#33312d]">
                        <option value="">
                            {{ __('common.all') }}
                        </option>

                        @foreach ($positions as $position)
                            <option value="{{ $position->id }}" @selected((string) $filters['position_id'] === (string) $position->id)>
                                {{ $position->name }}
                            </option>
                        @endforeach
                    </select>
                </div>


                {{-- STATUS --}}
                <div>
                    <label
                        class="mb-2
                           block
                           text-xs
                           font-semibold
                           text-[#66635d]">
                        {{ __('common.status') }}
                    </label>

                    <select name="status"
                        class="h-11
                           w-full
                           rounded-xl
                           border
                           border-black/[0.08]
                           bg-[#faf9f6]
                           px-4
                           text-sm
                           text-[#33312d]">
                        <option value="">
                            {{ __('common.all') }}
                        </option>

                        <option value="open" @selected($filters['status'] === 'open')>
                            {{ __('attendance-report.open') }}
                        </option>

                        <option value="completed" @selected($filters['status'] === 'completed')>
                            {{ __('common.completed') }}
                        </option>
                    </select>
                </div>


                {{-- LATE --}}
                <div>
                    <label
                        class="mb-2
                           block
                           text-xs
                           font-semibold
                           text-[#66635d]">
                        {{ __('attendance-report.late') }}
                    </label>

                    <select name="late"
                        class="h-11
                           w-full
                           rounded-xl
                           border
                           border-black/[0.08]
                           bg-[#faf9f6]
                           px-4
                           text-sm
                           text-[#33312d]">
                        <option value="">
                            {{ __('common.all') }}
                        </option>

                        <option value="yes" @selected($filters['late'] === 'yes')>
                            {{ __('common.yes') }}
                        </option>

                        <option value="no" @selected($filters['late'] === 'no')>
                            {{ __('common.no') }}
                        </option>
                    </select>
                </div>


                {{-- GEOFENCE --}}
                <div>
                    <label
                        class="mb-2
                           block
                           text-xs
                           font-semibold
                           text-[#66635d]">
                        {{ __('attendance-report.geofence') }}
                    </label>

                    <select name="geofence"
                        class="h-11
                           w-full
                           rounded-xl
                           border
                           border-black/[0.08]
                           bg-[#faf9f6]
                           px-4
                           text-sm
                           text-[#33312d]">
                        <option value="">
                            {{ __('common.all') }}
                        </option>

                        <option value="inside" @selected($filters['geofence'] === 'inside')>
                            {{ __('attendance-report.inside') }}
                        </option>

                        <option value="outside" @selected($filters['geofence'] === 'outside')>
                            {{ __('attendance-report.outside') }}
                        </option>

                        <option value="unknown" @selected($filters['geofence'] === 'unknown')>
                            {{ __('attendance-report.unknown') }}
                        </option>
                    </select>
                </div>


                {{-- METHOD --}}
                <div>
                    <label
                        class="mb-2
                           block
                           text-xs
                           font-semibold
                           text-[#66635d]">
                        {{ __('attendance-report.method') }}
                    </label>

                    <select name="method"
                        class="h-11
                           w-full
                           rounded-xl
                           border
                           border-black/[0.08]
                           bg-[#faf9f6]
                           px-4
                           text-sm
                           text-[#33312d]">
                        <option value="">
                            {{ __('common.all') }}
                        </option>

                        <option value="mobile" @selected($filters['method'] === 'mobile')>
                            Mobile
                        </option>

                        <option value="web" @selected($filters['method'] === 'web')>
                            Web
                        </option>

                        <option value="device" @selected($filters['method'] === 'device')>
                            Device
                        </option>

                        <option value="manual" @selected($filters['method'] === 'manual')>
                            Manual
                        </option>
                    </select>
                </div>
            </div>


            <div
                class="mt-6
                   flex
                   flex-col
                   gap-4
                   border-t
                   border-black/[0.06]
                   pt-5
                   sm:flex-row
                   sm:items-center
                   sm:justify-between">
                <p class="text-xs
                       leading-5
                       text-[#85827b]">
                    {{ __('attendance-report.export_help') }}
                </p>


                <div class="flex
                       flex-wrap
                       gap-2">
                    <a href="{{ route('hris.attendance.report.index', [
                        'organization' => $organization->slug,
                    ]) }}"
                        class="inline-flex
                           h-11
                           items-center
                           rounded-xl
                           border
                           border-black/[0.08]
                           bg-white
                           px-5
                           text-sm
                           font-semibold
                           text-[#55524c]">
                        {{ __('common.reset') }}
                    </a>


                    <button type="submit"
                        class="h-11
                           rounded-xl
                           bg-[#1d1d1b]
                           px-6
                           text-sm
                           font-semibold
                           text-white">
                        {{ __('common.filter') }}
                    </button>
                </div>
            </div>
        </form>
    </section>


    {{-- ============================================================= --}}
    {{-- SUMMARY                                                       --}}
    {{-- ============================================================= --}}

    <section
        class="mt-5
           grid
           gap-4
           sm:grid-cols-2
           lg:grid-cols-4
           2xl:grid-cols-8">
        @php
            $cards = [
                [
                    'label' => __('attendance-report.employees'),

                    'value' => number_format($employeeCount),

                    'class' => '',
                ],

                [
                    'label' => __('attendance-report.total_records'),

                    'value' => number_format($totalRecords),

                    'class' => '',
                ],

                [
                    'label' => __('attendance-report.completed'),

                    'value' => number_format($completedCount),

                    'class' => 'text-emerald-700',
                ],

                [
                    'label' => __('attendance-report.open'),

                    'value' => number_format($openCount),

                    'class' => 'text-orange-700',
                ],

                [
                    'label' => __('attendance-report.late'),

                    'value' => number_format($lateCount),

                    'class' => 'text-amber-700',
                ],

                [
                    'label' => __('attendance-report.inside'),

                    'value' => number_format($insideCount),

                    'class' => 'text-emerald-700',
                ],

                [
                    'label' => __('attendance-report.outside'),

                    'value' => number_format($outsideCount),

                    'class' => 'text-red-600',
                ],

                [
                    'label' => __('attendance-report.total_work'),

                    'value' => $formatMinutes($totalWorkMinutes),

                    'class' => 'text-blue-700',
                ],
            ];
        @endphp


        @foreach ($cards as $card)
            <article
                class="rounded-2xl
                   border
                   border-black/[0.06]
                   bg-white
                   p-5">
                <div
                    class="text-xs
                       font-medium
                       leading-5
                       text-[#85827b]">
                    {{ $card['label'] }}
                </div>

                <div
                    class="mt-2
                       text-2xl
                       font-semibold
                       tracking-tight
                       {{ $card['class'] }}">
                    {{ $card['value'] }}
                </div>
            </article>
        @endforeach
    </section>


    {{-- ============================================================= --}}
    {{-- CURRENT RESULT                                                --}}
    {{-- ============================================================= --}}

    <section
        class="mt-5
           flex
           flex-col
           gap-3
           rounded-2xl
           border
           border-blue-100
           bg-blue-50/60
           px-5 py-4
           sm:flex-row
           sm:items-center
           sm:justify-between">
        <div>
            <div class="text-sm
                   font-semibold
                   text-blue-900">
                {{ __('attendance-report.current_result') }}
            </div>

            <div class="mt-1
                   text-xs
                   leading-5
                   text-blue-700">
                {{ $filters['from'] }}
                →
                {{ $filters['to'] }}

                ·

                {{ number_format($totalRecords) }}
                {{ __('attendance-report.records') }}
            </div>
        </div>

        <p class="text-xs
               text-blue-700">
            {{ __('attendance-report.download_uses_filters') }}
        </p>
    </section>


    {{-- ============================================================= --}}
    {{-- TABLE                                                         --}}
    {{-- ============================================================= --}}

    <section
        class="mt-5
           overflow-hidden
           rounded-2xl
           border
           border-black/[0.06]
           bg-white">
        <div class="overflow-x-auto">
            <table class="w-full
                   min-w-[1600px]">
                <thead class="border-b
                       border-black/[0.06]
                       bg-[#faf9f6]">
                    <tr
                        class="text-left
                           text-xs
                           font-semibold
                           text-[#77746d]">
                        <th class="px-5 py-4">
                            {{ __('common.date') }}
                        </th>

                        <th class="px-5 py-4">
                            {{ __('attendance-report.employee') }}
                        </th>

                        <th class="px-5 py-4">
                            {{ __('attendance-report.schedule') }}
                        </th>

                        <th class="px-5 py-4">
                            {{ __('attendance-report.clock_in') }}
                        </th>

                        <th class="px-5 py-4">
                            {{ __('attendance-report.clock_out') }}
                        </th>

                        <th class="px-5 py-4">
                            {{ __('attendance-report.late') }}
                        </th>

                        <th class="px-5 py-4">
                            {{ __('attendance-report.work_duration') }}
                        </th>

                        <th class="px-5 py-4">
                            {{ __('attendance-report.geofence') }}
                        </th>

                        <th class="px-5 py-4">
                            {{ __('attendance-report.work_location') }}
                        </th>

                        <th class="px-5 py-4">
                            {{ __('attendance-report.method') }}
                        </th>

                        <th class="px-5 py-4">
                            {{ __('common.status') }}
                        </th>

                        <th class="px-5 py-4
                               text-right">
                            {{ __('common.actions') }}
                        </th>
                    </tr>
                </thead>


                <tbody class="divide-y
                       divide-black/[0.05]">
                    @forelse($attendances
                        as $attendance)
                        @php
                            $employee = $attendance->employee;

                            $geoStatus = $geofenceStatus($attendance);
                        @endphp


                        <tr class="transition
                               hover:bg-[#fcfbf8]">
                            {{-- DATE --}}
                            <td class="px-5 py-5">
                                <div
                                    class="text-sm
                                       font-semibold
                                       text-[#33312d]">
                                    {{ $attendance->attendance_date?->format('d M Y') }}
                                </div>

                                <div
                                    class="mt-1
                                       text-xs
                                       text-[#99968e]">
                                    {{ $attendance->attendance_date?->translatedFormat('l') }}
                                </div>
                            </td>


                            {{-- EMPLOYEE --}}
                            <td class="px-5 py-5">
                                <div
                                    class="text-sm
                                       font-semibold
                                       text-[#33312d]">
                                    {{ $employeeName($employee) }}
                                </div>

                                <div
                                    class="mt-1
                                       max-w-[260px]
                                       truncate
                                       text-xs
                                       text-[#99968e]">
                                    {{ $employee?->employee_number ?? '—' }}

                                    @if ($employee?->position?->name)
                                        ·
                                        {{ $employee->position->name }}
                                    @endif

                                    @if ($employee?->branch?->name)
                                        ·
                                        {{ $employee->branch->name }}
                                    @endif
                                </div>
                            </td>


                            {{-- SCHEDULE --}}
                            <td class="px-5 py-5">
                                <div
                                    class="text-sm
                                       font-medium
                                       text-[#44413c]">
                                    {{ $attendance->scheduled_clock_in ?? '—' }}

                                    –

                                    {{ $attendance->scheduled_clock_out ?? '—' }}
                                </div>
                            </td>


                            {{-- CLOCK IN --}}
                            <td class="px-5 py-5">
                                <div
                                    class="text-sm
                                       font-semibold
                                       text-[#33312d]">
                                    {{ $attendance->clock_in?->format('H:i:s') ?? '—' }}
                                </div>

                                @if ($attendance->clock_in_accuracy_meters !== null)
                                    <div
                                        class="mt-1
                                           text-xs
                                           text-[#99968e]">
                                        GPS ±
                                        {{ number_format($attendance->clock_in_accuracy_meters, 0, ',', '.') }}
                                        m
                                    </div>
                                @endif
                            </td>


                            {{-- CLOCK OUT --}}
                            <td class="px-5 py-5">
                                <div
                                    class="text-sm
                                       font-semibold
                                       text-[#33312d]">
                                    {{ $attendance->clock_out?->format('H:i:s') ?? '—' }}
                                </div>

                                @if ($attendance->clock_out_accuracy_meters !== null)
                                    <div
                                        class="mt-1
                                           text-xs
                                           text-[#99968e]">
                                        GPS ±
                                        {{ number_format($attendance->clock_out_accuracy_meters, 0, ',', '.') }}
                                        m
                                    </div>
                                @endif
                            </td>


                            {{-- LATE --}}
                            <td class="px-5 py-5">
                                @if ($attendance->is_late)
                                    <span
                                        class="inline-flex
                                           rounded-full
                                           bg-amber-50
                                           px-3 py-1.5
                                           text-xs
                                           font-semibold
                                           text-amber-700">
                                        +
                                        {{ number_format($attendance->late_minutes) }}
                                        min
                                    </span>
                                @else
                                    <span
                                        class="inline-flex
                                           rounded-full
                                           bg-emerald-50
                                           px-3 py-1.5
                                           text-xs
                                           font-semibold
                                           text-emerald-700">
                                        {{ __('attendance-report.on_time') }}
                                    </span>
                                @endif
                            </td>


                            {{-- WORK --}}
                            <td class="px-5 py-5">
                                <span
                                    class="text-sm
                                       font-semibold
                                       text-[#33312d]">
                                    {{ $attendance->work_minutes !== null ? $formatMinutes($attendance->work_minutes) : '—' }}
                                </span>
                            </td>


                            {{-- GEOFENCE --}}
                            <td class="px-5 py-5">
                                @if ($geoStatus === 'inside')
                                    <span
                                        class="inline-flex
                                           rounded-full
                                           bg-emerald-50
                                           px-3 py-1.5
                                           text-xs
                                           font-semibold
                                           text-emerald-700">
                                        {{ __('attendance-report.inside') }}
                                    </span>
                                @elseif($geoStatus === 'outside')
                                    <span
                                        class="inline-flex
                                           rounded-full
                                           bg-red-50
                                           px-3 py-1.5
                                           text-xs
                                           font-semibold
                                           text-red-700">
                                        {{ __('attendance-report.outside') }}
                                    </span>
                                @else
                                    <span
                                        class="inline-flex
                                           rounded-full
                                           bg-[#f3f2ee]
                                           px-3 py-1.5
                                           text-xs
                                           font-semibold
                                           text-[#66635d]">
                                        {{ __('attendance-report.unknown') }}
                                    </span>
                                @endif
                            </td>


                            {{-- WORK LOCATION --}}
                            <td class="px-5 py-5">
                                <div
                                    class="max-w-[200px]
                                       truncate
                                       text-sm
                                       font-semibold
                                       text-[#33312d]">
                                    {{ $attendance->workLocation?->name ?? '—' }}
                                </div>

                                @if ($attendance->clock_in_distance_meters !== null)
                                    <div
                                        class="mt-1
                                           text-xs
                                           text-[#99968e]">
                                        IN
                                        {{ number_format($attendance->clock_in_distance_meters, 0, ',', '.') }}
                                        m
                                    </div>
                                @endif

                                @if ($attendance->clock_out_distance_meters !== null)
                                    <div class="text-xs
                                           text-[#99968e]">
                                        OUT
                                        {{ number_format($attendance->clock_out_distance_meters, 0, ',', '.') }}
                                        m
                                    </div>
                                @endif
                            </td>


                            {{-- METHOD --}}
                            <td class="px-5 py-5">
                                <span
                                    class="inline-flex
                                       rounded-full
                                       bg-blue-50
                                       px-3 py-1.5
                                       text-xs
                                       font-semibold
                                       capitalize
                                       text-blue-700">
                                    {{ $attendance->method ?? '—' }}
                                </span>
                            </td>


                            {{-- STATUS --}}
                            <td class="px-5 py-5">
                                @if ($attendance->status === 'completed')
                                    <span
                                        class="inline-flex
                                           rounded-full
                                           bg-emerald-50
                                           px-3 py-1.5
                                           text-xs
                                           font-semibold
                                           text-emerald-700">
                                        {{ __('common.completed') }}
                                    </span>
                                @else
                                    <span
                                        class="inline-flex
                                           rounded-full
                                           bg-orange-50
                                           px-3 py-1.5
                                           text-xs
                                           font-semibold
                                           text-orange-700">
                                        {{ __('attendance-report.open') }}
                                    </span>
                                @endif
                            </td>


                            {{-- ACTION --}}
                            <td class="px-5 py-5
                                   text-right">
                                <a href="{{ route('hris.attendance.map', [
                                    'organization' => $organization->slug,
                                
                                    'date' => $attendance->attendance_date?->format('Y-m-d'),
                                ]) }}"
                                    class="inline-flex
                                       h-9
                                       items-center
                                       rounded-lg
                                       border
                                       border-blue-100
                                       bg-blue-50
                                       px-4
                                       text-xs
                                       font-semibold
                                       text-blue-700
                                       transition
                                       hover:bg-blue-100">
                                    {{ __('attendance-report.view_map') }}
                                </a>
                            </td>
                        </tr>

                    @empty

                        <tr>
                            <td colspan="12" class="px-6 py-20
                                   text-center">
                                <div
                                    class="text-base
                                       font-semibold
                                       text-[#33312d]">
                                    {{ __('attendance-report.no_data') }}
                                </div>

                                <p
                                    class="mt-2
                                       text-sm
                                       text-[#85827b]">
                                    {{ __('attendance-report.no_data_description') }}
                                </p>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>


        @if ($attendances->hasPages())
            <div class="border-t
                   border-black/[0.06]
                   px-5 py-5">
                {{ $attendances->links() }}
            </div>
        @endif
    </section>

@endsection
