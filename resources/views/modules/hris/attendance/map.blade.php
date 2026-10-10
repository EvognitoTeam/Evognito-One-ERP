@extends('layouts.workspace')

@section('title', __('attendance-daily-map.title') . ' - HRIS')

@push('styles')
    <link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css">

    <style>
        /*
            |--------------------------------------------------------------------------
            | Leaflet Container
            |--------------------------------------------------------------------------
            */

        #daily-attendance-map {
            position: relative;
            z-index: 0;
            overflow: hidden;
            outline: 0;
            background: #efeee9;
        }

        /*
            |--------------------------------------------------------------------------
            | Leaflet + Tailwind Compatibility
            |--------------------------------------------------------------------------
            */

        #daily-attendance-map .leaflet-pane,
        #daily-attendance-map .leaflet-tile,
        #daily-attendance-map .leaflet-marker-icon,
        #daily-attendance-map .leaflet-marker-shadow,
        #daily-attendance-map .leaflet-tile-container,
        #daily-attendance-map .leaflet-overlay-pane svg,
        #daily-attendance-map .leaflet-zoom-box,
        #daily-attendance-map .leaflet-image-layer {
            position: absolute;
            left: 0;
            top: 0;
        }

        #daily-attendance-map .leaflet-map-pane {
            z-index: 2;
        }

        #daily-attendance-map .leaflet-tile-pane {
            z-index: 200;
        }

        #daily-attendance-map .leaflet-overlay-pane {
            z-index: 400;
        }

        #daily-attendance-map .leaflet-shadow-pane {
            z-index: 500;
        }

        #daily-attendance-map .leaflet-marker-pane {
            z-index: 600;
        }

        #daily-attendance-map .leaflet-tooltip-pane {
            z-index: 650;
        }

        #daily-attendance-map .leaflet-popup-pane {
            z-index: 700;
        }

        #daily-attendance-map .leaflet-control {
            position: relative;
            z-index: 800;
            pointer-events: auto;
        }

        #daily-attendance-map img,
        #daily-attendance-map img.leaflet-tile,
        #daily-attendance-map .leaflet-marker-icon,
        #daily-attendance-map .leaflet-marker-shadow {
            max-width: none !important;
            max-height: none !important;
        }

        #daily-attendance-map img.leaflet-tile {
            width: 256px !important;
            height: 256px !important;
        }

        #daily-attendance-map .leaflet-tile {
            visibility: hidden;
            user-select: none;
            -webkit-user-drag: none;
        }

        #daily-attendance-map .leaflet-tile-loaded {
            visibility: inherit;
        }

        /*
            |--------------------------------------------------------------------------
            | Leaflet Controls
            |--------------------------------------------------------------------------
            */

        #daily-attendance-map .leaflet-control-container {
            position: relative;
            z-index: 900;
        }

        #daily-attendance-map .leaflet-top,
        #daily-attendance-map .leaflet-bottom {
            position: absolute;
            z-index: 1000;
            pointer-events: none;
        }

        #daily-attendance-map .leaflet-top {
            top: 0;
        }

        #daily-attendance-map .leaflet-right {
            right: 0;
        }

        #daily-attendance-map .leaflet-bottom {
            bottom: 0;
        }

        #daily-attendance-map .leaflet-left {
            left: 0;
        }

        #daily-attendance-map .leaflet-control {
            float: left;
            clear: both;
        }

        #daily-attendance-map .leaflet-right .leaflet-control {
            float: right;
        }

        #daily-attendance-map .leaflet-top .leaflet-control {
            margin-top: 10px;
        }

        #daily-attendance-map .leaflet-bottom .leaflet-control {
            margin-bottom: 10px;
        }

        #daily-attendance-map .leaflet-left .leaflet-control {
            margin-left: 10px;
        }

        #daily-attendance-map .leaflet-right .leaflet-control {
            margin-right: 10px;
        }

        /*
            |--------------------------------------------------------------------------
            | Zoom Control
            |--------------------------------------------------------------------------
            */

        #daily-attendance-map .leaflet-control-zoom {
            overflow: hidden;
            border-radius: 10px;
            border: 1px solid rgba(0, 0, 0, .08);
            background: white;
            box-shadow: 0 3px 12px rgba(0, 0, 0, .08);
        }

        #daily-attendance-map .leaflet-control-zoom a {
            display: flex;
            width: 32px;
            height: 32px;
            align-items: center;
            justify-content: center;
            background: white;
            color: #44413c;
            font-size: 18px;
            font-weight: 500;
            line-height: 32px;
            text-decoration: none;
        }

        #daily-attendance-map .leaflet-control-zoom a:hover {
            background: #f6f5f1;
        }

        #daily-attendance-map .leaflet-control-zoom-in {
            border-bottom: 1px solid rgba(0, 0, 0, .06);
        }

        /*
            |--------------------------------------------------------------------------
            | Leaflet Popup
            |--------------------------------------------------------------------------
            */

        #daily-attendance-map .leaflet-popup {
            position: absolute;
            text-align: center;
            margin-bottom: 20px;
        }

        #daily-attendance-map .leaflet-popup-content-wrapper {
            border-radius: 15px;
            background: white;
            box-shadow:
                0 10px 35px rgba(0, 0, 0, .15);
        }

        #daily-attendance-map .leaflet-popup-content {
            margin: 13px 15px;
            text-align: left;
        }

        #daily-attendance-map .leaflet-popup-tip-container {
            position: absolute;
            left: 50%;
            width: 40px;
            height: 20px;
            margin-left: -20px;
            overflow: hidden;
            pointer-events: none;
        }

        #daily-attendance-map .leaflet-popup-tip {
            width: 17px;
            height: 17px;
            margin: -10px auto 0;
            padding: 1px;
            transform: rotate(45deg);
            background: white;
        }

        /*
            |--------------------------------------------------------------------------
            | Attribution
            |--------------------------------------------------------------------------
            */

        #daily-attendance-map .leaflet-control-attribution {
            margin: 0;
            padding: 3px 6px;
            border-radius: 7px 0 0 0;
            background: rgba(255, 255, 255, .85);
            font-size: 8px;
            color: #77746d;
        }

        #daily-attendance-map .leaflet-control-attribution a {
            color: #2563eb;
            text-decoration: none;
        }

        /*
            |--------------------------------------------------------------------------
            | Toolbar
            |--------------------------------------------------------------------------
            */

        .attendance-map-toolbar {
            position: relative;
            z-index: 50;
        }

        .attendance-mode-button {
            position: relative;
            z-index: 60;

            cursor: pointer;
            pointer-events: auto;

            border: 1px solid rgba(0, 0, 0, .08);
            background: #ffffff;
            color: #66635d;

            transition:
                background .15s ease,
                border-color .15s ease,
                color .15s ease;
        }

        .attendance-mode-button:hover {
            background: #f6f5f1;
        }

        .attendance-mode-button.active {
            background: #252421;
            border-color: #252421;
            color: #ffffff;
        }

        /*
            |--------------------------------------------------------------------------
            | Employee List
            |--------------------------------------------------------------------------
            */

        .attendance-employee-row {
            cursor: pointer;
        }

        .attendance-employee-row.is-hidden {
            display: none;
        }

        /*
            |--------------------------------------------------------------------------
            | Custom Map Pin
            |--------------------------------------------------------------------------
            */

        .evg-map-pin-wrapper {
            position: relative;

            width: 42px !important;
            height: 52px !important;

            border: none !important;
            background: transparent !important;
        }

        .evg-map-pin {
            position: relative;

            width: 42px;
            height: 52px;

            transform-origin: center bottom;

            transition:
                transform .15s ease,
                filter .15s ease;

            filter:
                drop-shadow(0 4px 5px rgba(0, 0, 0, .22));
        }

        .evg-map-pin:hover {
            transform:
                translateY(-3px) scale(1.08);

            filter:
                drop-shadow(0 7px 8px rgba(0, 0, 0, .28));
        }

        .evg-map-pin>svg {
            display: block;

            width: 42px;
            height: 52px;

            overflow: visible;
        }

        /*
            |--------------------------------------------------------------------------
            | Pin IN / OUT
            |--------------------------------------------------------------------------
            */

        .evg-map-pin-label {
            position: absolute;

            left: 50%;
            top: 14px;

            transform:
                translateX(-50%);

            min-width: 22px;

            text-align: center;

            font-size: 7px;
            font-weight: 900;

            line-height: 1;

            letter-spacing: -.02em;

            color: #ffffff;

            pointer-events: none;
        }

        /*
            |--------------------------------------------------------------------------
            | Work Location Pin Icon
            |--------------------------------------------------------------------------
            */

        .evg-map-pin-location-icon {
            position: absolute;

            left: 50%;
            top: 11px;

            transform:
                translateX(-50%);

            width: 18px;
            height: 18px;

            color: #ffffff;

            pointer-events: none;
        }

        .evg-map-pin-location-icon svg {
            display: block;

            width: 18px;
            height: 18px;
        }
    </style>
@endpush


@section('content')

    {{-- ============================================================= --}}
    {{-- HEADER                                                        --}}
    {{-- ============================================================= --}}

    <section
        class="flex
           flex-col
           gap-5
           xl:flex-row
           xl:items-end
           xl:justify-between">
        <div>
            <div
                class="text-[10px]
                   font-bold
                   uppercase
                   tracking-[.1em]
                   text-blue-600">
                HRIS ·
                {{ __('hris.navigation.attendance') }}
            </div>

            <h1
                class="mt-1
                   text-[28px]
                   font-semibold
                   tracking-[-.04em]">
                {{ __('attendance-daily-map.title') }}
            </h1>

            <p
                class="mt-2
                   max-w-[760px]
                   text-[12px]
                   leading-5
                   text-[#85827b]">
                {{ __('attendance-daily-map.description') }}
            </p>
        </div>


        <a href="{{ route('hris.attendance.index', [
            'organization' => $organization->slug,
        ]) }}"
            class="inline-flex
               h-10
               items-center
               justify-center
               rounded-xl
               border
               border-black/[0.08]
               bg-white
               px-4
               text-[10px]
               font-semibold
               text-[#55524c]
               transition
               hover:bg-[#f6f5f1]">
            ←
            {{ __('attendance-daily-map.back_to_attendance') }}
        </a>
    </section>


    {{-- ============================================================= --}}
    {{-- HRIS NAVIGATION                                               --}}
    {{-- ============================================================= --}}

    <div class="mt-7">
        @include('modules.hris.partials.navigation')
    </div>


    {{-- ============================================================= --}}
    {{-- DATE NAVIGATION                                               --}}
    {{-- ============================================================= --}}

    <section
        class="mt-5
           flex
           flex-col
           gap-3
           rounded-2xl
           border
           border-black/[0.06]
           bg-white
           p-4
           lg:flex-row
           lg:items-center
           lg:justify-between">
        <div class="flex
               flex-wrap
               items-center
               gap-2">
            <a href="{{ route('hris.attendance.map', [
                'organization' => $organization->slug,
            
                'date' => $previousDate,
            ]) }}"
                class="flex
                   h-10 w-10
                   items-center
                   justify-center
                   rounded-xl
                   border
                   border-black/[0.08]
                   bg-white
                   text-[17px]
                   text-[#55524c]
                   transition
                   hover:bg-[#f6f5f1]">
                ‹
            </a>


            <form method="GET"
                action="{{ route('hris.attendance.map', [
                    'organization' => $organization->slug,
                ]) }}"
                class="flex
                   items-center
                   gap-2">
                <input type="date" name="date" value="{{ $date->toDateString() }}"
                    class="h-10
                       rounded-xl
                       border
                       border-black/[0.08]
                       bg-[#faf9f6]
                       px-3
                       text-[11px]
                       outline-none
                       focus:border-blue-200
                       focus:ring-2
                       focus:ring-blue-50">

                <button type="submit"
                    class="h-10
                       rounded-xl
                       bg-[#1d1d1b]
                       px-4
                       text-[10px]
                       font-semibold
                       text-white">
                    {{ __('common.filter') }}
                </button>
            </form>


            <a href="{{ route('hris.attendance.map', [
                'organization' => $organization->slug,
            
                'date' => $nextDate,
            ]) }}"
                class="flex
                   h-10 w-10
                   items-center
                   justify-center
                   rounded-xl
                   border
                   border-black/[0.08]
                   bg-white
                   text-[17px]
                   text-[#55524c]
                   transition
                   hover:bg-[#f6f5f1]">
                ›
            </a>
        </div>


        <div class="flex
               flex-wrap
               items-center
               gap-2">
            <a href="{{ route('hris.attendance.map', [
                'organization' => $organization->slug,
            
                'date' => now()->toDateString(),
            ]) }}"
                class="inline-flex
                   h-10
                   items-center
                   rounded-xl
                   border
                   border-black/[0.08]
                   bg-white
                   px-4
                   text-[10px]
                   font-semibold
                   text-[#55524c]">
                {{ __('common.today') }}
            </a>


            <div
                class="rounded-xl
                   bg-[#f6f5f1]
                   px-4
                   py-2.5
                   text-[11px]
                   font-semibold
                   text-[#55524c]">
                {{ $date->translatedFormat('l, d F Y') }}
            </div>
        </div>
    </section>


    {{-- ============================================================= --}}
    {{-- STATISTICS                                                    --}}
    {{-- ============================================================= --}}

    <section
        class="mt-4
           grid
           gap-3
           sm:grid-cols-2
           lg:grid-cols-3
           xl:grid-cols-6">
        @php
            $stats = [
                [
                    'label' => __('attendance-daily-map.active_employees'),

                    'value' => $activeEmployeeCount,

                    'class' => '',
                ],

                [
                    'label' => __('attendance-daily-map.attended'),

                    'value' => $attendedCount,

                    'class' => '',
                ],

                [
                    'label' => __('attendance-daily-map.with_gps'),

                    'value' => $withGpsCount,

                    'class' => '',
                ],

                [
                    'label' => __('attendance-daily-map.inside_geofence'),

                    'value' => $insideCount,

                    'class' => 'text-emerald-700',
                ],

                [
                    'label' => __('attendance-daily-map.outside_geofence'),

                    'value' => $outsideCount,

                    'class' => 'text-red-600',
                ],

                [
                    'label' => __('attendance-daily-map.not_recorded'),

                    'value' => $notRecordedCount,

                    'class' => 'text-[#77746d]',
                ],
            ];
        @endphp


        @foreach ($stats as $stat)
            <div
                class="rounded-2xl
                   border
                   border-black/[0.06]
                   bg-white
                   p-4">
                <div class="text-[9px]
                       text-[#99968e]">
                    {{ $stat['label'] }}
                </div>

                <div
                    class="mt-2
                       text-[22px]
                       font-semibold
                       {{ $stat['class'] }}">
                    {{ number_format($stat['value']) }}
                </div>
            </div>
        @endforeach
    </section>


    {{-- ============================================================= --}}
    {{-- MAP                                                           --}}
    {{-- ============================================================= --}}

    <section
        class="relative
           isolate
           mt-5
           grid
           overflow-hidden
           rounded-2xl
           border
           border-black/[0.06]
           bg-white
           xl:grid-cols-[minmax(0,1fr)_340px]">
        {{-- ========================================================= --}}
        {{-- LEFT MAP                                                  --}}
        {{-- ========================================================= --}}

        <div class="relative
               min-w-0">
            {{-- TOOLBAR --}}
            <div
                class="attendance-map-toolbar
                   flex
                   flex-col
                   gap-3
                   border-b
                   border-black/[0.06]
                   bg-white
                   px-4 py-3
                   sm:flex-row
                   sm:items-center
                   sm:justify-between">
                <div class="flex
                       flex-wrap
                       gap-2">
                    <button type="button" data-attendance-mode="clock_in"
                        class="attendance-mode-button
                           active
                           h-9
                           rounded-xl
                           px-4
                           text-[9px]
                           font-semibold">
                        {{ __('attendance-daily-map.clock_in_points') }}
                    </button>


                    <button type="button" data-attendance-mode="clock_out"
                        class="attendance-mode-button
                           h-9
                           rounded-xl
                           px-4
                           text-[9px]
                           font-semibold">
                        {{ __('attendance-daily-map.clock_out_points') }}
                    </button>


                    <button type="button" data-attendance-mode="both"
                        class="attendance-mode-button
                           h-9
                           rounded-xl
                           px-4
                           text-[9px]
                           font-semibold">
                        {{ __('attendance-daily-map.both_points') }}
                    </button>
                </div>


                {{-- SEARCH --}}
                <label
                    class="relative
                       block
                       w-full
                       sm:w-[270px]">
                    <svg xmlns="http://www.w3.org/2000/svg"
                        class="pointer-events-none
                           absolute
                           left-3
                           top-1/2
                           h-3.5 w-3.5
                           -translate-y-1/2
                           text-[#aaa79f]"
                        fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.8">
                        <circle cx="11" cy="11" r="7" />

                        <path d="m20 20-3.5-3.5" />
                    </svg>

                    <input id="attendance-employee-search" type="search" autocomplete="off"
                        placeholder="{{ __('attendance-daily-map.search_employee') }}"
                        class="h-9
                           w-full
                           rounded-xl
                           border
                           border-black/[0.08]
                           bg-[#faf9f6]
                           pl-9
                           pr-3
                           text-[10px]
                           outline-none
                           focus:border-blue-200
                           focus:ring-2
                           focus:ring-blue-50">
                </label>
            </div>


            {{-- ACTUAL MAP --}}
            <div id="daily-attendance-map" class="h-[560px]
                   w-full
                   md:h-[620px]">
            </div>


            {{-- LEGEND --}}
            <div
                class="relative
                   z-10
                   flex
                   flex-wrap
                   gap-x-5
                   gap-y-2
                   border-t
                   border-black/[0.06]
                   bg-white
                   px-4 py-3
                   text-[9px]
                   text-[#77746d]">
                <span class="inline-flex
                       items-center
                       gap-1.5">
                    <span
                        class="h-2.5 w-2.5
                           rounded-full
                           bg-emerald-500"></span>

                    {{ __('attendance-daily-map.inside') }}
                </span>


                <span class="inline-flex
                       items-center
                       gap-1.5">
                    <span
                        class="h-2.5 w-2.5
                           rounded-full
                           bg-red-500"></span>

                    {{ __('attendance-daily-map.outside') }}
                </span>


                <span class="inline-flex
                       items-center
                       gap-1.5">
                    <span
                        class="h-2.5 w-2.5
                           rounded-full
                           bg-amber-500"></span>

                    {{ __('attendance-daily-map.unknown_geofence') }}
                </span>


                <span class="inline-flex
                       items-center
                       gap-1.5">
                    <span
                        class="h-2.5 w-2.5
                           rounded-full
                           bg-blue-500"></span>

                    {{ __('attendance-daily-map.work_location') }}
                </span>
            </div>
        </div>


        {{-- ========================================================= --}}
        {{-- EMPLOYEE PANEL                                            --}}
        {{-- ========================================================= --}}

        <aside
            class="relative
               z-10
               border-t
               border-black/[0.06]
               bg-white
               xl:border-l
               xl:border-t-0">
            <div class="border-b
                   border-black/[0.06]
                   px-4 py-4">
                <div class="text-[11px]
                       font-semibold">
                    {{ __('attendance-daily-map.employee_list') }}
                </div>

                <div
                    class="mt-1
                       text-[9px]
                       leading-4
                       text-[#99968e]">
                    {{ __('attendance-daily-map.employee_list_help') }}
                </div>
            </div>


            <div class="max-h-[650px]
                   overflow-y-auto">
                @forelse(
                    $rows
                    as $row
                )

                    @php
                        $attendance = $row['attendance'];

                        $searchText = strtolower(
                            implode(
                                ' ',
                                array_filter([
                                    $row['employee_name'],

                                    $row['employee_number'],

                                    $row['position'],

                                    $row['branch'],
                                ]),
                            ),
                        );

                        $hasGps =
                            $attendance &&
                            ($attendance['clock_in_location']['latitude'] !== null ||
                                $attendance['clock_out_location']['latitude'] !== null);

                        $outside =
                            $attendance &&
                            ($attendance['clock_in_location']['within_geofence'] === false ||
                                $attendance['clock_out_location']['within_geofence'] === false);
                    @endphp


                    <button type="button" data-employee-id="{{ $row['employee_id'] }}" data-search="{{ $searchText }}"
                        class="attendance-employee-row
                           block
                           w-full
                           border-b
                           border-black/[0.05]
                           px-4 py-3
                           text-left
                           transition
                           hover:bg-[#faf9f6]">
                        <div
                            class="flex
                               items-start
                               justify-between
                               gap-3">
                            <div class="min-w-0">
                                <div
                                    class="truncate
                                       text-[10px]
                                       font-semibold">
                                    {{ $row['employee_name'] }}
                                </div>

                                <div
                                    class="mt-1
                                       truncate
                                       text-[8px]
                                       text-[#99968e]">
                                    {{ $row['employee_number'] ?? '—' }}

                                    @if ($row['position'])
                                        ·
                                        {{ $row['position'] }}
                                    @endif

                                    @if ($row['branch'])
                                        ·
                                        {{ $row['branch'] }}
                                    @endif
                                </div>
                            </div>


                            @if (!$attendance)
                                <span
                                    class="shrink-0
                                       rounded-full
                                       bg-[#f3f2ee]
                                       px-2 py-1
                                       text-[8px]
                                       font-semibold
                                       text-[#77746d]">
                                    {{ __('attendance-daily-map.not_recorded') }}
                                </span>
                            @elseif(!$hasGps)
                                <span
                                    class="shrink-0
                                       rounded-full
                                       bg-amber-50
                                       px-2 py-1
                                       text-[8px]
                                       font-semibold
                                       text-amber-700">
                                    {{ __('attendance-daily-map.no_gps') }}
                                </span>
                            @elseif($outside)
                                <span
                                    class="shrink-0
                                       rounded-full
                                       bg-red-50
                                       px-2 py-1
                                       text-[8px]
                                       font-semibold
                                       text-red-700">
                                    {{ __('attendance-daily-map.outside') }}
                                </span>
                            @else
                                <span
                                    class="shrink-0
                                       rounded-full
                                       bg-emerald-50
                                       px-2 py-1
                                       text-[8px]
                                       font-semibold
                                       text-emerald-700">
                                    {{ __('attendance-daily-map.attended') }}
                                </span>
                            @endif
                        </div>


                        @if ($attendance)
                            <div
                                class="mt-2
                                   flex
                                   flex-wrap
                                   gap-x-3
                                   gap-y-1
                                   text-[8px]
                                   text-[#77746d]">
                                <span>
                                    IN

                                    {{ $attendance['clock_in'] ?? '—' }}
                                </span>

                                <span>
                                    OUT

                                    {{ $attendance['clock_out'] ?? '—' }}
                                </span>


                                @if ($attendance['is_late'])
                                    <span class="font-semibold
                                           text-amber-700">
                                        +
                                        {{ $attendance['late_minutes'] }}
                                        min
                                    </span>
                                @endif
                            </div>
                        @endif
                    </button>

                @empty

                    <div
                        class="px-5 py-14
                           text-center
                           text-[10px]
                           text-[#99968e]">
                        {{ __('attendance-daily-map.not_recorded') }}
                    </div>
                @endforelse
            </div>
        </aside>
    </section>

@endsection


@push('scripts')
    <script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js"></script>


    <script>
        (function() {
            'use strict';


            /*
            |--------------------------------------------------------------------------
            | Server Data
            |--------------------------------------------------------------------------
            */

            const attendances =
                {{ Illuminate\Support\Js::from($mapAttendances) }};


            const workLocations =
                {{ Illuminate\Support\Js::from($workLocations) }};


            /*
            |--------------------------------------------------------------------------
            | State
            |--------------------------------------------------------------------------
            */

            let map =
                null;

            let pointMode =
                'clock_in';

            let mapLayers = [];

            let employeeLayers = {};


            /*
            |--------------------------------------------------------------------------
            | DOM
            |--------------------------------------------------------------------------
            */

            const mapElement =
                document.getElementById(
                    'daily-attendance-map'
                );

            const modeButtons =
                document.querySelectorAll(
                    '[data-attendance-mode]'
                );

            const searchInput =
                document.getElementById(
                    'attendance-employee-search'
                );

            const employeeRows =
                document.querySelectorAll(
                    '.attendance-employee-row'
                );


            /*
            |--------------------------------------------------------------------------
            | Boot
            |--------------------------------------------------------------------------
            */

            function boot() {
                if (!mapElement) {
                    return;
                }


                if (
                    typeof window.L ===
                    'undefined'
                ) {
                    console.error(
                        'Leaflet JS gagal dimuat.'
                    );

                    return;
                }


                createMap();

                bindModeButtons();

                bindSearch();

                bindEmployeeRows();

                renderMap();


                window.setTimeout(
                    function() {
                        if (!map) {
                            return;
                        }

                        map.invalidateSize(
                            true
                        );

                        renderMap();
                    },
                    250
                );


                window.addEventListener(
                    'resize',
                    function() {
                        if (map) {
                            map.invalidateSize();
                        }
                    }
                );
            }


            /*
            |--------------------------------------------------------------------------
            | Create Map
            |--------------------------------------------------------------------------
            */

            function createMap() {
                map =
                    L.map(
                        mapElement, {
                            zoomControl: true,

                            attributionControl: true,

                            preferCanvas: false,
                        }
                    );


                L.tileLayer(
                        'https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
                            maxZoom: 19,

                            minZoom: 2,

                            tileSize: 256,

                            zoomOffset: 0,

                            detectRetina: false,

                            attribution: '&copy; OpenStreetMap contributors',
                        }
                    )
                    .addTo(
                        map
                    );
            }


            /*
            |--------------------------------------------------------------------------
            | Mode Buttons
            |--------------------------------------------------------------------------
            */

            function bindModeButtons() {
                modeButtons.forEach(
                    function(
                        button
                    ) {
                        button.addEventListener(
                            'click',
                            function(
                                event
                            ) {
                                event.preventDefault();

                                event.stopPropagation();

                                setPointMode(
                                    button.dataset
                                    .attendanceMode
                                );
                            }
                        );
                    }
                );
            }


            function setPointMode(
                mode
            ) {
                if (
                    ![
                        'clock_in',
                        'clock_out',
                        'both',
                    ].includes(
                        mode
                    )
                ) {
                    return;
                }


                pointMode =
                    mode;


                modeButtons.forEach(
                    function(
                        button
                    ) {
                        button.classList.toggle(
                            'active',

                            button.dataset
                            .attendanceMode ===
                            pointMode
                        );
                    }
                );


                renderMap();
            }


            /*
            |--------------------------------------------------------------------------
            | Search Employee
            |--------------------------------------------------------------------------
            */

            function bindSearch() {
                if (!searchInput) {
                    return;
                }


                searchInput.addEventListener(
                    'input',
                    function() {
                        const keyword =
                            searchInput
                            .value
                            .trim()
                            .toLowerCase();


                        employeeRows.forEach(
                            function(
                                row
                            ) {
                                const text =
                                    (
                                        row.dataset
                                        .search ??
                                        ''
                                    )
                                    .toLowerCase();


                                const show =
                                    keyword === '' ||
                                    text.includes(
                                        keyword
                                    );


                                row.classList.toggle(
                                    'is-hidden',
                                    !show
                                );
                            }
                        );
                    }
                );
            }


            /*
            |--------------------------------------------------------------------------
            | Employee List Click
            |--------------------------------------------------------------------------
            */

            function bindEmployeeRows() {
                employeeRows.forEach(
                    function(
                        row
                    ) {
                        row.addEventListener(
                            'click',
                            function() {
                                focusEmployee(
                                    String(
                                        row.dataset
                                        .employeeId
                                    )
                                );
                            }
                        );
                    }
                );
            }


            /*
            |--------------------------------------------------------------------------
            | Render Map
            |--------------------------------------------------------------------------
            */

            function renderMap() {
                if (!map) {
                    return;
                }


                map.invalidateSize();


                clearLayers();


                employeeLayers = {};


                const bounds = [];


                /*
                |--------------------------------------------------------------------------
                | Work Locations
                |--------------------------------------------------------------------------
                */

                workLocations.forEach(
                    function(
                        location
                    ) {
                        addWorkLocation(
                            location,
                            bounds
                        );
                    }
                );


                /*
                |--------------------------------------------------------------------------
                | Attendance
                |--------------------------------------------------------------------------
                */

                attendances.forEach(
                    function(
                        attendance
                    ) {
                        const layers = [];


                        /*
                        |--------------------------------------------------------------------------
                        | Clock In
                        |--------------------------------------------------------------------------
                        */

                        if (
                            pointMode ===
                            'clock_in' ||
                            pointMode ===
                            'both'
                        ) {
                            const marker =
                                addEmployeePoint(
                                    attendance,

                                    attendance
                                    .clock_in_location,

                                    'clock_in',

                                    bounds
                                );


                            if (marker) {
                                layers.push(
                                    marker
                                );
                            }
                        }


                        /*
                        |--------------------------------------------------------------------------
                        | Clock Out
                        |--------------------------------------------------------------------------
                        */

                        if (
                            pointMode ===
                            'clock_out' ||
                            pointMode ===
                            'both'
                        ) {
                            const marker =
                                addEmployeePoint(
                                    attendance,

                                    attendance
                                    .clock_out_location,

                                    'clock_out',

                                    bounds
                                );


                            if (marker) {
                                layers.push(
                                    marker
                                );
                            }
                        }


                        /*
                        |--------------------------------------------------------------------------
                        | Clock In → Clock Out Connection
                        |--------------------------------------------------------------------------
                        */

                        if (
                            pointMode ===
                            'both' &&
                            hasPoint(
                                attendance
                                .clock_in_location
                            ) &&
                            hasPoint(
                                attendance
                                .clock_out_location
                            )
                        ) {
                            const line =
                                L.polyline(
                                    [
                                        [
                                            Number(
                                                attendance
                                                .clock_in_location
                                                .latitude
                                            ),

                                            Number(
                                                attendance
                                                .clock_in_location
                                                .longitude
                                            ),
                                        ],

                                        [
                                            Number(
                                                attendance
                                                .clock_out_location
                                                .latitude
                                            ),

                                            Number(
                                                attendance
                                                .clock_out_location
                                                .longitude
                                            ),
                                        ],
                                    ], {
                                        color: '#64748b',

                                        opacity: .5,

                                        weight: 2,

                                        dashArray: '5, 7',
                                    }
                                )
                                .addTo(
                                    map
                                );


                            mapLayers.push(
                                line
                            );
                        }


                        if (
                            layers.length >
                            0
                        ) {
                            employeeLayers[
                                String(
                                    attendance
                                    .employee_id
                                )
                            ] = layers;
                        }
                    }
                );


                /*
                |--------------------------------------------------------------------------
                | Fit Bounds
                |--------------------------------------------------------------------------
                */

                window.setTimeout(
                    function() {
                        map.invalidateSize(
                            true
                        );


                        if (
                            bounds.length >
                            0
                        ) {
                            map.fitBounds(
                                L.latLngBounds(
                                    bounds
                                ), {
                                    padding: [
                                        55,
                                        55,
                                    ],

                                    maxZoom: 17,

                                    animate: false,
                                }
                            );

                            return;
                        }


                        map.setView(
                            [
                                -2.5489,
                                118.0149,
                            ],
                            5
                        );
                    },
                    80
                );
            }


            /*
            |--------------------------------------------------------------------------
            | Create Pin Icon
            |--------------------------------------------------------------------------
            */

            function createPinIcon(
                color,
                label = '',
                type = 'attendance'
            ) {
                let centerContent =
                    '';


                /*
                |--------------------------------------------------------------------------
                | Work Location Icon
                |--------------------------------------------------------------------------
                */

                if (
                    type ===
                    'work_location'
                ) {
                    centerContent = `
                <span
                    class="
                        evg-map-pin-location-icon
                    "
                >
                    <svg
                        xmlns="http://www.w3.org/2000/svg"
                        viewBox="0 0 24 24"
                        fill="none"
                        stroke="currentColor"
                        stroke-width="2"
                        stroke-linecap="round"
                        stroke-linejoin="round"
                    >
                        <path
                            d="M3 21h18"
                        ></path>

                        <path
                            d="M6 21V8l6-4 6 4v13"
                        ></path>

                        <path
                            d="M9 12h2"
                        ></path>

                        <path
                            d="M13 12h2"
                        ></path>

                        <path
                            d="M9 16h2"
                        ></path>

                        <path
                            d="M13 16h2"
                        ></path>
                    </svg>
                </span>
            `;
                }

                /*
                |--------------------------------------------------------------------------
                | Attendance IN / OUT
                |--------------------------------------------------------------------------
                */
                else {
                    centerContent = `
                <span
                    class="
                        evg-map-pin-label
                    "
                >
                    ${escapeHtml(
                        label
                    )}
                </span>
            `;
                }


                /*
                |--------------------------------------------------------------------------
                | Pin Shape
                |--------------------------------------------------------------------------
                */

                const html = `
            <div
                class="
                    evg-map-pin
                "
            >
                <svg
                    viewBox="0 0 42 52"
                    xmlns="http://www.w3.org/2000/svg"
                >
                    <path
                        d="
                            M21 1
                            C9.95 1 1 9.73 1 20.5
                            C1 34.2 21 51 21 51
                            C21 51 41 34.2 41 20.5
                            C41 9.73 32.05 1 21 1
                            Z
                        "
                        fill="${color}"
                        stroke="#ffffff"
                        stroke-width="2.5"
                    ></path>

                    <circle
                        cx="21"
                        cy="20"
                        r="11"
                        fill="rgba(255,255,255,.14)"
                    ></circle>
                </svg>

                ${centerContent}
            </div>
        `;


                return L.divIcon({
                    className: 'evg-map-pin-wrapper',

                    html: html,

                    iconSize: [
                        42,
                        52,
                    ],

                    iconAnchor: [
                        21,
                        51,
                    ],

                    popupAnchor: [
                        0,
                        -49,
                    ],

                    tooltipAnchor: [
                        0,
                        -45,
                    ],
                });
            }


            /*
            |--------------------------------------------------------------------------
            | Work Location
            |--------------------------------------------------------------------------
            */

            function addWorkLocation(
                location,
                bounds
            ) {
                if (
                    !hasPoint(
                        location
                    )
                ) {
                    return;
                }


                const point = [
                    Number(
                        location.latitude
                    ),

                    Number(
                        location.longitude
                    ),
                ];


                /*
                |--------------------------------------------------------------------------
                | Blue Work Location Pin
                |--------------------------------------------------------------------------
                */

                const marker =
                    L.marker(
                        point, {
                            icon: createPinIcon(
                                '#2563eb',
                                '',
                                'work_location'
                            ),

                            riseOnHover: true,

                            keyboard: true,

                            zIndexOffset: 100,
                        }
                    )
                    .addTo(
                        map
                    )
                    .bindTooltip(
                        escapeHtml(
                            location.name ||
                            @js(__('attendance-daily-map.work_location'))
                        ), {
                            direction: 'top',

                            offset: [
                                0,
                                -5,
                            ],

                            opacity: .95,
                        }
                    )
                    .bindPopup(
                        buildWorkLocationPopup(
                            location
                        )
                    );


                mapLayers.push(
                    marker
                );


                bounds.push(
                    point
                );


                /*
                |--------------------------------------------------------------------------
                | Geofence Radius
                |--------------------------------------------------------------------------
                */

                if (
                    location.geofence_enabled &&
                    Number(
                        location.radius_meters
                    ) > 0
                ) {
                    const circle =
                        L.circle(
                            point, {
                                radius: Number(
                                    location
                                    .radius_meters
                                ),

                                color: '#2563eb',

                                fillColor: '#3b82f6',

                                fillOpacity: .07,

                                opacity: .55,

                                weight: 2,
                            }
                        )
                        .addTo(
                            map
                        );


                    mapLayers.push(
                        circle
                    );


                    const circleBounds =
                        circle.getBounds();


                    bounds.push(
                        circleBounds
                        .getNorthEast()
                    );


                    bounds.push(
                        circleBounds
                        .getSouthWest()
                    );
                }
            }


            /*
            |--------------------------------------------------------------------------
            | Employee Attendance Pin
            |--------------------------------------------------------------------------
            */

            function addEmployeePoint(
                attendance,
                location,
                type,
                bounds
            ) {
                if (
                    !hasPoint(
                        location
                    )
                ) {
                    return null;
                }


                const point = [
                    Number(
                        location.latitude
                    ),

                    Number(
                        location.longitude
                    ),
                ];


                /*
                |--------------------------------------------------------------------------
                | Color
                |--------------------------------------------------------------------------
                */

                const color =
                    geofenceColor(
                        location
                        .within_geofence
                    );


                /*
                |--------------------------------------------------------------------------
                | IN / OUT
                |--------------------------------------------------------------------------
                */

                const label =
                    type === 'clock_out' ?
                    'OUT' :
                    'IN';


                /*
                |--------------------------------------------------------------------------
                | Pin Marker
                |--------------------------------------------------------------------------
                */

                const marker =
                    L.marker(
                        point, {
                            icon: createPinIcon(
                                color,
                                label,
                                'attendance'
                            ),

                            riseOnHover: true,

                            keyboard: true,

                            zIndexOffset: type ===
                                'clock_in' ?
                                300 :
                                250,
                        }
                    )
                    .addTo(
                        map
                    )
                    .bindTooltip(
                        escapeHtml(
                            attendance
                            .employee_name
                        ), {
                            direction: 'top',

                            offset: [
                                0,
                                -6,
                            ],

                            opacity: .95,
                        }
                    )
                    .bindPopup(
                        buildEmployeePopup(
                            attendance,
                            location,
                            type
                        )
                    );


                mapLayers.push(
                    marker
                );


                bounds.push(
                    point
                );


                return marker;
            }


            /*
            |--------------------------------------------------------------------------
            | Focus Employee
            |--------------------------------------------------------------------------
            */

            function focusEmployee(
                employeeId
            ) {
                const layers =
                    employeeLayers[
                        employeeId
                    ];


                if (
                    !layers ||
                    layers.length ===
                    0
                ) {
                    return;
                }


                const marker =
                    layers[0];


                if (
                    typeof marker.getLatLng !==
                    'function'
                ) {
                    return;
                }


                map.setView(
                    marker.getLatLng(),
                    18, {
                        animate: true,
                    }
                );


                marker.openPopup();
            }


            /*
            |--------------------------------------------------------------------------
            | Clear Layers
            |--------------------------------------------------------------------------
            */

            function clearLayers() {
                mapLayers.forEach(
                    function(
                        layer
                    ) {
                        try {
                            map.removeLayer(
                                layer
                            );
                        } catch (
                            error
                        ) {
                            //
                        }
                    }
                );


                mapLayers = [];
            }


            /*
            |--------------------------------------------------------------------------
            | Point Validation
            |--------------------------------------------------------------------------
            */

            function hasPoint(
                point
            ) {
                if (!point) {
                    return false;
                }


                if (
                    point.latitude ===
                    null ||
                    point.longitude ===
                    null ||
                    point.latitude ===
                    undefined ||
                    point.longitude ===
                    undefined
                ) {
                    return false;
                }


                const latitude =
                    Number(
                        point.latitude
                    );


                const longitude =
                    Number(
                        point.longitude
                    );


                return (
                    Number.isFinite(
                        latitude
                    ) &&
                    Number.isFinite(
                        longitude
                    )
                );
            }


            /*
            |--------------------------------------------------------------------------
            | Geofence Color
            |--------------------------------------------------------------------------
            */

            function geofenceColor(
                value
            ) {
                /*
                |--------------------------------------------------------------------------
                | Inside
                |--------------------------------------------------------------------------
                */

                if (
                    value === true ||
                    value === 1 ||
                    value === '1'
                ) {
                    return '#16a34a';
                }


                /*
                |--------------------------------------------------------------------------
                | Outside
                |--------------------------------------------------------------------------
                */

                if (
                    value === false ||
                    value === 0 ||
                    value === '0'
                ) {
                    return '#dc2626';
                }


                /*
                |--------------------------------------------------------------------------
                | Unknown
                |--------------------------------------------------------------------------
                */

                return '#d97706';
            }


            /*
            |--------------------------------------------------------------------------
            | Geofence Text
            |--------------------------------------------------------------------------
            */

            function geofenceText(
                value
            ) {
                if (
                    value === true ||
                    value === 1 ||
                    value === '1'
                ) {
                    return @js(__('attendance-daily-map.inside'));
                }


                if (
                    value === false ||
                    value === 0 ||
                    value === '0'
                ) {
                    return @js(__('attendance-daily-map.outside'));
                }


                return @js(__('attendance-daily-map.unknown_geofence'));
            }


            /*
            |--------------------------------------------------------------------------
            | Employee Popup
            |--------------------------------------------------------------------------
            */

            function buildEmployeePopup(
                attendance,
                location,
                type
            ) {
                const typeLabel =
                    type === 'clock_out' ?
                    @js(__('attendance-daily-map.clock_out')) :
                    @js(__('attendance-daily-map.clock_in'));


                const time =
                    type === 'clock_out' ?
                    attendance
                    .clock_out :
                    attendance
                    .clock_in;


                const geofenceStatus =
                    geofenceText(
                        location
                        .within_geofence
                    );


                const lines = [];


                /*
                |--------------------------------------------------------------------------
                | Employee Number
                |--------------------------------------------------------------------------
                */

                if (
                    attendance.employee_number
                ) {
                    lines.push(
                        escapeHtml(
                            attendance
                            .employee_number
                        )
                    );
                }


                /*
                |--------------------------------------------------------------------------
                | Position
                |--------------------------------------------------------------------------
                */

                if (
                    attendance.position
                ) {
                    lines.push(
                        escapeHtml(
                            attendance.position
                        )
                    );
                }


                /*
                |--------------------------------------------------------------------------
                | Branch
                |--------------------------------------------------------------------------
                */

                if (
                    attendance.branch
                ) {
                    lines.push(
                        escapeHtml(
                            attendance.branch
                        )
                    );
                }


                /*
                |--------------------------------------------------------------------------
                | Clock
                |--------------------------------------------------------------------------
                */

                lines.push(
                    `
                ${escapeHtml(
                    typeLabel
                )}:

                <strong>
                    ${escapeHtml(
                        time
                        || '—'
                    )}
                </strong>
            `
                );


                /*
                |--------------------------------------------------------------------------
                | Geofence
                |--------------------------------------------------------------------------
                */

                lines.push(
                    `
                ${escapeHtml(
                    @js(__('attendance-daily-map.geofence'))
                )}:

                <strong>
                    ${escapeHtml(
                        geofenceStatus
                    )}
                </strong>
            `
                );


                /*
                |--------------------------------------------------------------------------
                | Distance
                |--------------------------------------------------------------------------
                */

                if (
                    location.distance_meters !==
                    null &&
                    location.distance_meters !==
                    undefined
                ) {
                    lines.push(
                        `
                    ${escapeHtml(
                        @js(__('attendance-daily-map.distance'))
                    )}:

                    <strong>
                        ${formatNumber(
                            location
                                .distance_meters
                        )} m
                    </strong>
                `
                    );
                }


                /*
                |--------------------------------------------------------------------------
                | GPS Accuracy
                |--------------------------------------------------------------------------
                */

                if (
                    location.accuracy_meters !==
                    null &&
                    location.accuracy_meters !==
                    undefined
                ) {
                    lines.push(
                        `
                    ${escapeHtml(
                        @js(__('attendance-daily-map.accuracy'))
                    )}:

                    <strong>
                        ±${formatNumber(
                            location
                                .accuracy_meters
                        )} m
                    </strong>
                `
                    );
                }


                /*
                |--------------------------------------------------------------------------
                | Late
                |--------------------------------------------------------------------------
                */

                if (
                    attendance.is_late
                ) {
                    lines.push(
                        `
                    ${escapeHtml(
                        @js(__('attendance-daily-map.late'))
                    )}:

                    <strong>
                        ${formatNumber(
                            attendance
                                .late_minutes
                        )} min
                    </strong>
                `
                    );
                }


                /*
                |--------------------------------------------------------------------------
                | Work Location
                |--------------------------------------------------------------------------
                */

                if (
                    attendance
                    .work_location
                    ?.name
                ) {
                    lines.push(
                        `
                    ${escapeHtml(
                        @js(__('attendance-daily-map.work_location'))
                    )}:

                    <strong>
                        ${escapeHtml(
                            attendance
                                .work_location
                                .name
                        )}
                    </strong>
                `
                    );
                }


                return `
            <div
                style="
                    min-width: 220px;
                "
            >
                <div
                    style="
                        margin-bottom: 7px;
                        font-size: 12px;
                        font-weight: 700;
                        color: #252421;
                    "
                >
                    ${escapeHtml(
                        attendance
                            .employee_name
                    )}
                </div>


                ${lines
                    .map(
                        function (
                            line
                        ) {
                            return `
                                    <div
                                        style="
                                            margin-top: 3px;
                                            font-size: 10px;
                                            line-height: 1.55;
                                            color: #66635d;
                                        "
                                    >
                                        ${line}
                                    </div>
                                `;
                        }
                    )
                    .join('')
                }
            </div>
        `;
            }


            /*
            |--------------------------------------------------------------------------
            | Work Location Popup
            |--------------------------------------------------------------------------
            */

            function buildWorkLocationPopup(
                location
            ) {
                return `
            <div
                style="
                    min-width: 210px;
                "
            >
                <div
                    style="
                        font-size: 12px;
                        font-weight: 700;
                        color: #252421;
                    "
                >
                    ${escapeHtml(
                        location.name
                        ||
                        @js(__('attendance-daily-map.work_location'))
                    )}
                </div>


                ${
                    location.address
                        ? `
                                <div
                                    style="
                                        margin-top: 4px;
                                        font-size: 10px;
                                        line-height: 1.55;
                                        color: #66635d;
                                    "
                                >
                                    ${escapeHtml(
                                        location.address
                                    )}
                                </div>
                            `
                        : ''
                }


                <div
                    style="
                        margin-top: 6px;
                        font-size: 10px;
                        color: #66635d;
                    "
                >
                    ${escapeHtml(
                        @js(__('attendance-daily-map.radius'))
                    )}:

                    <strong>
                        ${formatNumber(
                            location
                                .radius_meters
                            || 0
                        )} m
                    </strong>
                </div>
            </div>
        `;
            }


            /*
            |--------------------------------------------------------------------------
            | Number Formatter
            |--------------------------------------------------------------------------
            */

            function formatNumber(
                value
            ) {
                const number =
                    Number(
                        value
                    );


                if (
                    !Number.isFinite(
                        number
                    )
                ) {
                    return '0';
                }


                return number.toLocaleString(
                    document.documentElement.lang ===
                    'id' ?
                    'id-ID' :
                    'en-US', {
                        maximumFractionDigits: 2,
                    }
                );
            }


            /*
            |--------------------------------------------------------------------------
            | Escape HTML
            |--------------------------------------------------------------------------
            */

            function escapeHtml(
                value
            ) {
                return String(
                        value ??
                        ''
                    )
                    .replaceAll(
                        '&',
                        '&amp;'
                    )
                    .replaceAll(
                        '<',
                        '&lt;'
                    )
                    .replaceAll(
                        '>',
                        '&gt;'
                    )
                    .replaceAll(
                        '"',
                        '&quot;'
                    )
                    .replaceAll(
                        "'",
                        '&#039;'
                    );
            }


            /*
            |--------------------------------------------------------------------------
            | Start
            |--------------------------------------------------------------------------
            */

            if (
                document.readyState ===
                'loading'
            ) {
                document.addEventListener(
                    'DOMContentLoaded',
                    boot
                );
            } else {
                boot();
            }

        })();
    </script>
@endpush
