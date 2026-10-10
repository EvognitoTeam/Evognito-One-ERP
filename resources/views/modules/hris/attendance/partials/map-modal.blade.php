@once
    @push('styles')
        <link
            rel="stylesheet"
            href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css"
            integrity="sha256-p4NxAoJBhIINfQ3ynhStkwAgNwmZUrD+Zcml7YxZxkI="
            crossorigin=""
        >
    @endpush

    @push('scripts')
        <script
            src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js"
            integrity="sha256-20nQCchB9co0qIjJZRGuk2/Z9VM+kNiyxNV1lvTlZBo="
            crossorigin=""
        ></script>

        <script>
            function attendanceMapModal() {
                return {
                    openState:
                        false,

                    data:
                        null,

                    map:
                        null,

                    layers:
                        [],


                    open(payload) {
                        this.data =
                            payload;

                        this.openState =
                            true;

                        this.$nextTick(() => {
                            setTimeout(() => {
                                this.renderMap();
                            }, 80);
                        });
                    },


                    close() {
                        this.openState =
                            false;
                    },


                    renderMap() {
                        const canvas =
                            document.getElementById(
                                'attendance-map-canvas'
                            );

                        if (
                            !canvas
                            ||
                            typeof L === 'undefined'
                        ) {
                            return;
                        }

                        if (!this.map) {
                            this.map =
                                L.map(
                                    canvas,
                                    {
                                        zoomControl:
                                            true,

                                        attributionControl:
                                            true,
                                    }
                                );

                            L.tileLayer(
                                'https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png',
                                {
                                    maxZoom:
                                        19,

                                    attribution:
                                        '&copy; OpenStreetMap contributors',
                                }
                            )
                                .addTo(
                                    this.map
                                );
                        }

                        this.clearLayers();

                        const bounds =
                            [];

                        /*
                        |--------------------------------------------------------------------------
                        | Work Location
                        |--------------------------------------------------------------------------
                        */

                        const workLocation =
                            this.data
                                ?.work_location;

                        if (
                            this.hasPoint(
                                workLocation
                            )
                        ) {
                            const locationPoint = [
                                Number(
                                    workLocation.latitude
                                ),
                                Number(
                                    workLocation.longitude
                                ),
                            ];

                            const locationMarker =
                                L.circleMarker(
                                    locationPoint,
                                    {
                                        radius:
                                            8,

                                        weight:
                                            3,

                                        opacity:
                                            1,

                                        fillOpacity:
                                            0.85,
                                    }
                                )
                                    .addTo(
                                        this.map
                                    )
                                    .bindPopup(
                                        this.popupHtml(
                                            this.escapeHtml(
                                                workLocation.name
                                                || 'Work Location'
                                            ),
                                            [
                                                workLocation.address
                                                    ? this.escapeHtml(
                                                        workLocation.address
                                                    )
                                                    : null,

                                                workLocation.radius_meters
                                                    ? `${this.escapeHtml(
                                                        @js(
                                                            __('attendance-map.radius')
                                                        )
                                                    )}: ${Number(
                                                        workLocation.radius_meters
                                                    ).toLocaleString()} m`
                                                    : null,
                                            ]
                                        )
                                    );

                            this.layers.push(
                                locationMarker
                            );

                            bounds.push(
                                locationPoint
                            );


                            if (
                                workLocation.geofence_enabled
                                &&
                                Number(
                                    workLocation.radius_meters
                                ) > 0
                            ) {
                                const radiusCircle =
                                    L.circle(
                                        locationPoint,
                                        {
                                            radius:
                                                Number(
                                                    workLocation.radius_meters
                                                ),

                                            weight:
                                                2,

                                            opacity:
                                                0.55,

                                            fillOpacity:
                                                0.08,
                                        }
                                    )
                                        .addTo(
                                            this.map
                                        );

                                this.layers.push(
                                    radiusCircle
                                );

                                const circleBounds =
                                    radiusCircle.getBounds();

                                bounds.push(
                                    circleBounds.getNorthEast()
                                );

                                bounds.push(
                                    circleBounds.getSouthWest()
                                );
                            }
                        }


                        /*
                        |--------------------------------------------------------------------------
                        | Clock In
                        |--------------------------------------------------------------------------
                        */

                        const clockIn =
                            this.data
                                ?.clock_in;

                        if (
                            this.hasPoint(
                                clockIn
                            )
                        ) {
                            const clockInPoint = [
                                Number(
                                    clockIn.latitude
                                ),
                                Number(
                                    clockIn.longitude
                                ),
                            ];

                            const clockInMarker =
                                L.circleMarker(
                                    clockInPoint,
                                    {
                                        radius:
                                            9,

                                        weight:
                                            3,

                                        opacity:
                                            1,

                                        fillOpacity:
                                            0.95,
                                    }
                                )
                                    .addTo(
                                        this.map
                                    )
                                    .bindPopup(
                                        this.popupHtml(
                                            @js(
                                                __('attendance-map.clock_in_point')
                                            ),
                                            [
                                                this.data
                                                    ?.attendance
                                                    ?.clock_in
                                                    ? this.escapeHtml(
                                                        this.data.attendance.clock_in
                                                    )
                                                    : null,

                                                clockIn.distance_meters !== null
                                                    ? `${this.escapeHtml(
                                                        @js(
                                                            __('attendance-map.distance')
                                                        )
                                                    )}: ${Number(
                                                        clockIn.distance_meters
                                                    ).toLocaleString()} m`
                                                    : null,

                                                clockIn.accuracy_meters !== null
                                                    ? `${this.escapeHtml(
                                                        @js(
                                                            __('attendance-map.accuracy')
                                                        )
                                                    )}: ±${Number(
                                                        clockIn.accuracy_meters
                                                    ).toLocaleString()} m`
                                                    : null,

                                                this.geofenceText(
                                                    clockIn.within_geofence
                                                ),
                                            ]
                                        )
                                    );

                            this.layers.push(
                                clockInMarker
                            );

                            bounds.push(
                                clockInPoint
                            );
                        }


                        /*
                        |--------------------------------------------------------------------------
                        | Clock Out
                        |--------------------------------------------------------------------------
                        */

                        const clockOut =
                            this.data
                                ?.clock_out;

                        if (
                            this.hasPoint(
                                clockOut
                            )
                        ) {
                            const clockOutPoint = [
                                Number(
                                    clockOut.latitude
                                ),
                                Number(
                                    clockOut.longitude
                                ),
                            ];

                            const clockOutMarker =
                                L.circleMarker(
                                    clockOutPoint,
                                    {
                                        radius:
                                            9,

                                        weight:
                                            3,

                                        opacity:
                                            1,

                                        fillOpacity:
                                            0.95,
                                    }
                                )
                                    .addTo(
                                        this.map
                                    )
                                    .bindPopup(
                                        this.popupHtml(
                                            @js(
                                                __('attendance-map.clock_out_point')
                                            ),
                                            [
                                                this.data
                                                    ?.attendance
                                                    ?.clock_out
                                                    ? this.escapeHtml(
                                                        this.data.attendance.clock_out
                                                    )
                                                    : null,

                                                clockOut.distance_meters !== null
                                                    ? `${this.escapeHtml(
                                                        @js(
                                                            __('attendance-map.distance')
                                                        )
                                                    )}: ${Number(
                                                        clockOut.distance_meters
                                                    ).toLocaleString()} m`
                                                    : null,

                                                clockOut.accuracy_meters !== null
                                                    ? `${this.escapeHtml(
                                                        @js(
                                                            __('attendance-map.accuracy')
                                                        )
                                                    )}: ±${Number(
                                                        clockOut.accuracy_meters
                                                    ).toLocaleString()} m`
                                                    : null,

                                                this.geofenceText(
                                                    clockOut.within_geofence
                                                ),
                                            ]
                                        )
                                    );

                            this.layers.push(
                                clockOutMarker
                            );

                            bounds.push(
                                clockOutPoint
                            );
                        }


                        /*
                        |--------------------------------------------------------------------------
                        | Clock In → Clock Out line
                        |--------------------------------------------------------------------------
                        */

                        if (
                            this.hasPoint(
                                clockIn
                            )
                            &&
                            this.hasPoint(
                                clockOut
                            )
                        ) {
                            const connection =
                                L.polyline(
                                    [
                                        [
                                            Number(
                                                clockIn.latitude
                                            ),
                                            Number(
                                                clockIn.longitude
                                            ),
                                        ],

                                        [
                                            Number(
                                                clockOut.latitude
                                            ),
                                            Number(
                                                clockOut.longitude
                                            ),
                                        ],
                                    ],
                                    {
                                        weight:
                                            2,

                                        opacity:
                                            0.55,

                                        dashArray:
                                            '5, 7',
                                    }
                                )
                                    .addTo(
                                        this.map
                                    );

                            this.layers.push(
                                connection
                            );
                        }


                        /*
                        |--------------------------------------------------------------------------
                        | Fit
                        |--------------------------------------------------------------------------
                        */

                        setTimeout(() => {
                            this.map
                                .invalidateSize();

                            if (
                                bounds.length
                                > 0
                            ) {
                                const mapBounds =
                                    L.latLngBounds(
                                        bounds
                                    );

                                this.map.fitBounds(
                                    mapBounds,
                                    {
                                        padding: [
                                            35,
                                            35,
                                        ],

                                        maxZoom:
                                            18,
                                    }
                                );
                            } else {
                                this.map.setView(
                                    [
                                        -2.5489,
                                        118.0149,
                                    ],
                                    5
                                );
                            }
                        }, 80);
                    },


                    clearLayers() {
                        if (!this.map) {
                            return;
                        }

                        this.layers
                            .forEach(
                                (layer) => {
                                    try {
                                        this.map.removeLayer(
                                            layer
                                        );
                                    } catch (error) {
                                    }
                                }
                            );

                        this.layers =
                            [];
                    },


                    hasPoint(point) {
                        return (
                            point
                            &&
                            point.latitude !== null
                            &&
                            point.longitude !== null
                            &&
                            Number.isFinite(
                                Number(
                                    point.latitude
                                )
                            )
                            &&
                            Number.isFinite(
                                Number(
                                    point.longitude
                                )
                            )
                        );
                    },


                    geofenceText(value) {
                        if (
                            value === true
                            ||
                            value === 1
                            ||
                            value === '1'
                        ) {
                            return @js(
                                __('attendance-map.inside_geofence')
                            );
                        }

                        if (
                            value === false
                            ||
                            value === 0
                            ||
                            value === '0'
                        ) {
                            return @js(
                                __('attendance-map.outside_geofence')
                            );
                        }

                        return null;
                    },


                    popupHtml(title, lines) {
                        const validLines =
                            lines.filter(
                                Boolean
                            );

                        return `
                            <div style="min-width:180px">
                                <div style="font-size:12px;font-weight:700;margin-bottom:5px">
                                    ${title}
                                </div>

                                ${validLines
                                    .map(
                                        (line) => `
                                            <div style="font-size:10px;line-height:1.6;color:#666">
                                                ${line}
                                            </div>
                                        `
                                    )
                                    .join('')
                                }
                            </div>
                        `;
                    },


                    escapeHtml(value) {
                        return String(
                            value ?? ''
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
                    },


                    formatNumber(value) {
                        if (
                            value === null
                            ||
                            value === undefined
                            ||
                            value === ''
                        ) {
                            return '—';
                        }

                        return Number(
                            value
                        ).toLocaleString();
                    },


                    geofenceBadgeClass(value) {
                        if (
                            value === true
                            ||
                            value === 1
                            ||
                            value === '1'
                        ) {
                            return 'bg-emerald-50 text-emerald-700 border-emerald-100';
                        }

                        if (
                            value === false
                            ||
                            value === 0
                            ||
                            value === '0'
                        ) {
                            return 'bg-red-50 text-red-700 border-red-100';
                        }

                        return 'bg-[#f3f2ee] text-[#77746d] border-black/[0.06]';
                    },


                    geofenceBadgeText(value) {
                        if (
                            value === true
                            ||
                            value === 1
                            ||
                            value === '1'
                        ) {
                            return @js(
                                __('attendance-map.inside')
                            );
                        }

                        if (
                            value === false
                            ||
                            value === 0
                            ||
                            value === '0'
                        ) {
                            return @js(
                                __('attendance-map.outside')
                            );
                        }

                        return @js(
                            __('attendance-map.not_available')
                        );
                    },
                };
            }
        </script>
    @endpush
@endonce


<div
    x-data="attendanceMapModal()"
    x-on:attendance-map-open.window="
        open($event.detail)
    "
    x-on:keydown.escape.window="
        if (openState) {
            close()
        }
    "
>
    <div
        x-show="openState"
        x-cloak
        class="fixed
               inset-0
               z-[180]"
    >
        <div
            class="absolute
                   inset-0
                   bg-black/45
                   backdrop-blur-[2px]"
            x-on:click="
                close()
            "
        ></div>


        <div
            class="relative
                   flex
                   min-h-full
                   items-center
                   justify-center
                   p-3
                   sm:p-5"
        >
            <div
                x-on:click.stop
                class="flex
                       max-h-[92vh]
                       w-full
                       max-w-[1100px]
                       flex-col
                       overflow-hidden
                       rounded-[24px]
                       border
                       border-black/[0.06]
                       bg-white
                       shadow-[0_24px_80px_rgba(0,0,0,.18)]"
            >
                {{-- HEADER --}}
                <div
                    class="flex
                           items-start
                           justify-between
                           gap-4
                           border-b
                           border-black/[0.06]
                           px-5 py-4
                           sm:px-6"
                >
                    <div class="min-w-0">
                        <div
                            class="text-[9px]
                                   font-bold
                                   uppercase
                                   tracking-[.09em]
                                   text-blue-600"
                        >
                            {{ __('attendance-map.attendance_location') }}
                        </div>

                        <h3
                            class="mt-1
                                   truncate
                                   text-[18px]
                                   font-semibold
                                   tracking-[-.025em]"
                            x-text="
                                data?.employee?.name
                                || '-'
                            "
                        ></h3>

                        <div
                            class="mt-1
                                   flex
                                   flex-wrap
                                   gap-x-3
                                   gap-y-1
                                   text-[9px]
                                   text-[#99968e]"
                        >
                            <span
                                x-show="
                                    data
                                    ?.employee
                                    ?.employee_number
                                "
                            >
                                <span
                                    x-text="
                                        data
                                        ?.employee
                                        ?.employee_number
                                    "
                                ></span>
                            </span>

                            <span
                                x-show="
                                    data
                                    ?.attendance
                                    ?.date
                                "
                                x-text="
                                    data
                                    ?.attendance
                                    ?.date
                                "
                            ></span>

                            <span
                                x-show="
                                    data
                                    ?.attendance
                                    ?.method
                                "
                                x-text="
                                    data
                                    ?.attendance
                                    ?.method
                                "
                            ></span>
                        </div>
                    </div>


                    <button
                        type="button"
                        x-on:click="
                            close()
                        "
                        class="flex
                               h-9 w-9
                               shrink-0
                               items-center
                               justify-center
                               rounded-xl
                               text-[#77746d]
                               transition
                               hover:bg-[#f3f2ee]
                               hover:text-[#252421]"
                    >
                        ✕
                    </button>
                </div>


                {{-- BODY --}}
                <div
                    class="min-h-0
                           flex-1
                           overflow-y-auto"
                >
                    {{-- MAP --}}
                    <div
                        id="attendance-map-canvas"
                        class="h-[380px]
                               w-full
                               border-b
                               border-black/[0.06]
                               bg-[#efeee9]
                               sm:h-[460px]"
                    ></div>


                    {{-- DETAILS --}}
                    <div
                        class="grid
                               gap-4
                               p-5
                               sm:grid-cols-2
                               sm:p-6
                               lg:grid-cols-3"
                    >
                        {{-- WORK LOCATION --}}
                        <div
                            class="rounded-2xl
                                   border
                                   border-black/[0.06]
                                   bg-[#faf9f6]
                                   p-4"
                        >
                            <div
                                class="text-[9px]
                                       font-bold
                                       uppercase
                                       tracking-[.07em]
                                       text-[#99968e]"
                            >
                                {{ __('attendance-map.work_location') }}
                            </div>

                            <div
                                class="mt-2
                                       text-[12px]
                                       font-semibold"
                                x-text="
                                    data
                                    ?.work_location
                                    ?.name
                                    || '—'
                                "
                            ></div>

                            <div
                                class="mt-1
                                       text-[9px]
                                       leading-4
                                       text-[#99968e]"
                                x-text="
                                    data
                                    ?.work_location
                                    ?.address
                                    || ''
                                "
                            ></div>

                            <div
                                class="mt-3
                                       text-[9px]
                                       text-[#77746d]"
                            >
                                {{ __('attendance-map.radius') }}:

                                <span
                                    class="font-semibold"
                                    x-text="
                                        data
                                        ?.work_location
                                        ?.radius_meters
                                        ? `${formatNumber(
                                            data.work_location.radius_meters
                                        )} m`
                                        : '—'
                                    "
                                ></span>
                            </div>
                        </div>


                        {{-- CLOCK IN --}}
                        <div
                            class="rounded-2xl
                                   border
                                   border-black/[0.06]
                                   bg-white
                                   p-4"
                        >
                            <div
                                class="flex
                                       items-center
                                       justify-between
                                       gap-2"
                            >
                                <div
                                    class="text-[9px]
                                           font-bold
                                           uppercase
                                           tracking-[.07em]
                                           text-[#99968e]"
                                >
                                    {{ __('attendance-map.clock_in') }}
                                </div>

                                <span
                                    class="rounded-full
                                           border
                                           px-2 py-1
                                           text-[8px]
                                           font-bold"
                                    x-bind:class="
                                        geofenceBadgeClass(
                                            data
                                            ?.clock_in
                                            ?.within_geofence
                                        )
                                    "
                                    x-text="
                                        geofenceBadgeText(
                                            data
                                            ?.clock_in
                                            ?.within_geofence
                                        )
                                    "
                                ></span>
                            </div>


                            <div
                                class="mt-2
                                       text-[11px]
                                       font-semibold"
                                x-text="
                                    data
                                    ?.attendance
                                    ?.clock_in
                                    || '—'
                                "
                            ></div>


                            <div
                                class="mt-3
                                       grid
                                       grid-cols-2
                                       gap-3"
                            >
                                <div>
                                    <div
                                        class="text-[8px]
                                               uppercase
                                               text-[#aaa79f]"
                                    >
                                        {{ __('attendance-map.distance') }}
                                    </div>

                                    <div
                                        class="mt-1
                                               text-[10px]
                                               font-semibold"
                                        x-text="
                                            data
                                            ?.clock_in
                                            ?.distance_meters
                                            !== null
                                                ? `${formatNumber(
                                                    data.clock_in.distance_meters
                                                )} m`
                                                : '—'
                                        "
                                    ></div>
                                </div>

                                <div>
                                    <div
                                        class="text-[8px]
                                               uppercase
                                               text-[#aaa79f]"
                                    >
                                        {{ __('attendance-map.accuracy') }}
                                    </div>

                                    <div
                                        class="mt-1
                                               text-[10px]
                                               font-semibold"
                                        x-text="
                                            data
                                            ?.clock_in
                                            ?.accuracy_meters
                                            !== null
                                                ? `±${formatNumber(
                                                    data.clock_in.accuracy_meters
                                                )} m`
                                                : '—'
                                        "
                                    ></div>
                                </div>
                            </div>


                            <div
                                class="mt-3
                                       break-all
                                       text-[8px]
                                       leading-4
                                       text-[#99968e]"
                            >
                                <span
                                    x-text="
                                        data
                                        ?.clock_in
                                        ?.latitude
                                        ?? '—'
                                    "
                                ></span>

                                ,

                                <span
                                    x-text="
                                        data
                                        ?.clock_in
                                        ?.longitude
                                        ?? '—'
                                    "
                                ></span>
                            </div>
                        </div>


                        {{-- CLOCK OUT --}}
                        <div
                            class="rounded-2xl
                                   border
                                   border-black/[0.06]
                                   bg-white
                                   p-4"
                        >
                            <div
                                class="flex
                                       items-center
                                       justify-between
                                       gap-2"
                            >
                                <div
                                    class="text-[9px]
                                           font-bold
                                           uppercase
                                           tracking-[.07em]
                                           text-[#99968e]"
                                >
                                    {{ __('attendance-map.clock_out') }}
                                </div>

                                <span
                                    class="rounded-full
                                           border
                                           px-2 py-1
                                           text-[8px]
                                           font-bold"
                                    x-bind:class="
                                        geofenceBadgeClass(
                                            data
                                            ?.clock_out
                                            ?.within_geofence
                                        )
                                    "
                                    x-text="
                                        geofenceBadgeText(
                                            data
                                            ?.clock_out
                                            ?.within_geofence
                                        )
                                    "
                                ></span>
                            </div>


                            <div
                                class="mt-2
                                       text-[11px]
                                       font-semibold"
                                x-text="
                                    data
                                    ?.attendance
                                    ?.clock_out
                                    || '—'
                                "
                            ></div>


                            <div
                                class="mt-3
                                       grid
                                       grid-cols-2
                                       gap-3"
                            >
                                <div>
                                    <div
                                        class="text-[8px]
                                               uppercase
                                               text-[#aaa79f]"
                                    >
                                        {{ __('attendance-map.distance') }}
                                    </div>

                                    <div
                                        class="mt-1
                                               text-[10px]
                                               font-semibold"
                                        x-text="
                                            data
                                            ?.clock_out
                                            ?.distance_meters
                                            !== null
                                                ? `${formatNumber(
                                                    data.clock_out.distance_meters
                                                )} m`
                                                : '—'
                                        "
                                    ></div>
                                </div>

                                <div>
                                    <div
                                        class="text-[8px]
                                               uppercase
                                               text-[#aaa79f]"
                                    >
                                        {{ __('attendance-map.accuracy') }}
                                    </div>

                                    <div
                                        class="mt-1
                                               text-[10px]
                                               font-semibold"
                                        x-text="
                                            data
                                            ?.clock_out
                                            ?.accuracy_meters
                                            !== null
                                                ? `±${formatNumber(
                                                    data.clock_out.accuracy_meters
                                                )} m`
                                                : '—'
                                        "
                                    ></div>
                                </div>
                            </div>


                            <div
                                class="mt-3
                                       break-all
                                       text-[8px]
                                       leading-4
                                       text-[#99968e]"
                            >
                                <span
                                    x-text="
                                        data
                                        ?.clock_out
                                        ?.latitude
                                        ?? '—'
                                    "
                                ></span>

                                ,

                                <span
                                    x-text="
                                        data
                                        ?.clock_out
                                        ?.longitude
                                        ?? '—'
                                    "
                                ></span>
                            </div>
                        </div>
                    </div>


                    {{-- LEGEND --}}
                    <div
                        class="border-t
                               border-black/[0.06]
                               px-5 py-4
                               text-[9px]
                               text-[#77746d]
                               sm:px-6"
                    >
                        <div
                            class="flex
                                   flex-wrap
                                   gap-x-5
                                   gap-y-2"
                        >
                            <span>
                                ● {{ __('attendance-map.work_location_point') }}
                            </span>

                            <span>
                                ● {{ __('attendance-map.clock_in_point') }}
                            </span>

                            <span>
                                ● {{ __('attendance-map.clock_out_point') }}
                            </span>

                            <span>
                                ○ {{ __('attendance-map.geofence_radius') }}
                            </span>
                        </div>
                    </div>
                </div>


                {{-- FOOTER --}}
                <div
                    class="flex
                           justify-end
                           border-t
                           border-black/[0.06]
                           bg-[#faf9f6]
                           px-5 py-4
                           sm:px-6"
                >
                    <button
                        type="button"
                        x-on:click="
                            close()
                        "
                        class="h-10
                               rounded-xl
                               bg-[#1d1d1b]
                               px-5
                               text-[10px]
                               font-semibold
                               text-white"
                    >
                        {{ __('attendance-map.close') }}
                    </button>
                </div>
            </div>
        </div>
    </div>
</div>
