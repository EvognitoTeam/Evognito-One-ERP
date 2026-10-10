@extends('layouts.workspace')

@section(
    'title',
    __('sales.overview.title').' - Sales Force'
)

@section('content')

<section
    class="flex
           flex-col
           gap-5
           xl:flex-row
           xl:items-end
           xl:justify-between"
>
    <div>
        <div
            class="text-xs
                   font-bold
                   uppercase
                   tracking-[0.12em]
                   text-teal-700"
        >
            Sales Force
        </div>

        <h1
            class="mt-2
                   text-3xl
                   font-semibold
                   tracking-[-0.04em]
                   text-[#252421]"
        >
            {{ __('sales.overview.title') }}
        </h1>

        <p
            class="mt-3
                   max-w-3xl
                   text-sm
                   leading-6
                   text-[#77746d]"
        >
            {{ __('sales.overview.description') }}
        </p>
    </div>

    <div
        class="inline-flex
               items-center
               gap-2
               rounded-full
               border
               border-teal-100
               bg-teal-50
               px-4 py-2
               text-xs
               font-semibold
               text-teal-700"
    >
        <span
            class="h-2 w-2
                   rounded-full
                   bg-teal-500"
        ></span>

        {{ __('sales.overview.module_active') }}
    </div>
</section>


<div class="mt-8">
    @include(
        'modules.sales.partials.navigation'
    )
</div>


{{-- ============================================================= --}}
{{-- KPI                                                           --}}
{{-- ============================================================= --}}

<section
    class="mt-6
           grid
           gap-4
           sm:grid-cols-2
           xl:grid-cols-5"
>
    @foreach([
        [
            __('sales.overview.field_sales'),
            $fieldSalesCount ?? 0,
            'text-[#252421]',
        ],
        [
            __('sales.overview.active_customers'),
            $activeCustomers ?? 0,
            'text-blue-700',
        ],
        [
            __('sales.overview.visits_today'),
            $visitsToday ?? 0,
            'text-teal-700',
        ],
        [
            __('sales.overview.in_progress'),
            $inProgressToday ?? 0,
            'text-orange-700',
        ],
        [
            __('sales.overview.completed_today'),
            $completedToday ?? 0,
            'text-emerald-700',
        ],
    ] as [$label, $value, $class])

        <article
            class="rounded-2xl
                   border
                   border-black/[0.06]
                   bg-white
                   p-5"
        >
            <div
                class="text-xs
                       font-medium
                       leading-5
                       text-[#85827b]"
            >
                {{ $label }}
            </div>

            <div
                class="mt-3
                       text-2xl
                       font-semibold
                       tracking-tight
                       {{ $class }}"
            >
                {{
                    number_format(
                        (int) $value
                    )
                }}
            </div>
        </article>

    @endforeach
</section>


{{-- ============================================================= --}}
{{-- CONTENT                                                       --}}
{{-- ============================================================= --}}

<section
    class="mt-5
           grid
           gap-5
           xl:grid-cols-[380px_minmax(0,1fr)]"
>
    {{-- QUICK ACTIONS --}}
    <div
        class="rounded-2xl
               border
               border-black/[0.06]
               bg-white
               p-5"
    >
        <h2
            class="text-lg
                   font-semibold
                   tracking-tight
                   text-[#252421]"
        >
            {{ __('sales.overview.quick_actions') }}
        </h2>

        <p
            class="mt-2
                   text-sm
                   leading-6
                   text-[#85827b]"
        >
            {{ __('sales.overview.quick_actions_description') }}
        </p>

        <div
            class="mt-5
                   grid
                   gap-3"
        >
            <a
                href="{{ route(
                    'sales.team.index',
                    [
                        'organization' =>
                            $organization->slug
                    ]
                ) }}"
                class="rounded-xl
                       border
                       border-black/[0.06]
                       bg-[#faf9f6]
                       px-4 py-3.5
                       text-sm
                       font-semibold
                       text-[#44413c]
                       transition
                       hover:bg-[#f3f2ee]"
            >
                {{ __('sales.overview.manage_team') }}
            </a>

            <a
                href="{{ route(
                    'sales.customers.index',
                    [
                        'organization' =>
                            $organization->slug
                    ]
                ) }}"
                class="rounded-xl
                       border
                       border-black/[0.06]
                       bg-[#faf9f6]
                       px-4 py-3.5
                       text-sm
                       font-semibold
                       text-[#44413c]
                       transition
                       hover:bg-[#f3f2ee]"
            >
                {{ __('sales.overview.manage_customers') }}
            </a>

            <a
                href="{{ route(
                    'sales.visits.index',
                    [
                        'organization' =>
                            $organization->slug
                    ]
                ) }}"
                class="rounded-xl
                       border
                       border-black/[0.06]
                       bg-[#faf9f6]
                       px-4 py-3.5
                       text-sm
                       font-semibold
                       text-[#44413c]
                       transition
                       hover:bg-[#f3f2ee]"
            >
                {{ __('sales.overview.manage_visits') }}
            </a>

            <a
                href="{{ route(
                    'sales.monitoring.index',
                    [
                        'organization' =>
                            $organization->slug
                    ]
                ) }}"
                class="rounded-xl
                       border
                       border-teal-100
                       bg-teal-50
                       px-4 py-3.5
                       text-sm
                       font-semibold
                       text-teal-700
                       transition
                       hover:bg-teal-100"
            >
                {{ __('sales.overview.open_monitoring') }}
            </a>
        </div>
    </div>


    {{-- UPCOMING VISITS --}}
    <div
        class="overflow-hidden
               rounded-2xl
               border
               border-black/[0.06]
               bg-white"
    >
        <div
            class="flex
                   items-center
                   justify-between
                   gap-4
                   border-b
                   border-black/[0.06]
                   px-5 py-4"
        >
            <div>
                <h2
                    class="text-lg
                           font-semibold
                           tracking-tight
                           text-[#252421]"
                >
                    {{ __('sales.overview.upcoming_visits') }}
                </h2>

                <p
                    class="mt-1
                           text-xs
                           leading-5
                           text-[#85827b]"
                >
                    {{ __('sales.overview.upcoming_visits_description') }}
                </p>
            </div>

            <a
                href="{{ route(
                    'sales.visits.index',
                    [
                        'organization' =>
                            $organization->slug
                    ]
                ) }}"
                class="shrink-0
                       text-xs
                       font-semibold
                       text-teal-700
                       hover:text-teal-800"
            >
                {{ __('sales.overview.view_all') }}
            </a>
        </div>

        <div
            class="divide-y
                   divide-black/[0.05]"
        >
            @forelse(
                collect(
                    $upcomingVisits ?? []
                )
                as $visit
            )
                @php
                    $employeeName =
                        $visit->employee_name
                        ?: (
                            $visit->employee_number
                            ?: __('sales.common.unassigned')
                        );

                    $dateRange =
                        $visit->start_date
                        ===
                        $visit->end_date
                            ? \Carbon\Carbon::parse(
                                $visit->start_date
                            )->format('d M Y')
                            : \Carbon\Carbon::parse(
                                $visit->start_date
                            )->format('d M')
                                .' – '.
                              \Carbon\Carbon::parse(
                                  $visit->end_date
                              )->format('d M Y');
                @endphp

                <div
                    class="flex
                           flex-col
                           gap-3
                           px-5 py-4
                           lg:flex-row
                           lg:items-center
                           lg:justify-between"
                >
                    <div class="min-w-0">
                        <div
                            class="truncate
                                   text-sm
                                   font-semibold
                                   text-[#33312d]"
                        >
                            {{
                                $visit->customer_name
                                ?: __('sales.common.customer_not_set')
                            }}
                        </div>

                        <div
                            class="mt-1
                                   text-xs
                                   leading-5
                                   text-[#85827b]"
                        >
                            {{ $employeeName }}

                            ·

                            {{ $dateRange }}

                            @if(
                                $visit->planned_start_time
                            )
                                ·
                                {{
                                    substr(
                                        (string)
                                        $visit->planned_start_time,
                                        0,
                                        5
                                    )
                                }}
                            @endif
                        </div>

                        @if(
                            $visit->customer_address
                        )
                            <div
                                class="mt-1
                                       max-w-2xl
                                       truncate
                                       text-xs
                                       text-[#a09d96]"
                            >
                                {{ $visit->customer_address }}
                            </div>
                        @endif
                    </div>

                    <span
                        class="inline-flex
                               w-fit
                               rounded-full
                               px-3 py-1.5
                               text-xs
                               font-semibold
                               {{
                                    $visit->status === 'in_progress'
                                        ? 'bg-orange-50 text-orange-700'
                                        : 'bg-blue-50 text-blue-700'
                               }}"
                    >
                        {{
                            __('sales.status.'.$visit->status)
                        }}
                    </span>
                </div>

            @empty

                <div
                    class="px-6 py-14
                           text-center"
                >
                    <div
                        class="text-base
                               font-semibold
                               text-[#44413c]"
                    >
                        {{ __('sales.overview.no_visits') }}
                    </div>

                    <p
                        class="mx-auto
                               mt-2
                               max-w-lg
                               text-sm
                               leading-6
                               text-[#85827b]"
                    >
                        {{ __('sales.overview.no_visits_description') }}
                    </p>
                </div>

            @endforelse
        </div>
    </div>
</section>


{{-- ============================================================= --}}
{{-- MODULE ROADMAP                                                --}}
{{-- ============================================================= --}}

<section
    class="mt-5
           rounded-2xl
           border
           border-black/[0.06]
           bg-white
           p-6"
>
    <div
        class="flex
               flex-col
               gap-2
               lg:flex-row
               lg:items-end
               lg:justify-between"
    >
        <div>
            <h2
                class="text-lg
                       font-semibold
                       tracking-tight
                       text-[#252421]"
            >
                {{ __('sales.overview.workspace_structure') }}
            </h2>

            <p
                class="mt-2
                       max-w-3xl
                       text-sm
                       leading-6
                       text-[#85827b]"
            >
                {{ __('sales.overview.workspace_structure_description') }}
            </p>
        </div>
    </div>

    <div
        class="mt-5
               grid
               gap-3
               md:grid-cols-2
               xl:grid-cols-4"
    >
        @foreach([
            ['team', 'sales.team.index'],
            ['customers', 'sales.customers.index'],
            ['products', 'sales.products.index'],
            ['visits', 'sales.visits.index'],
            ['monitoring', 'sales.monitoring.index'],
            ['records', 'sales.records.index'],
            ['reports', 'sales.reports.index'],
            ['configuration', 'sales.configuration.index'],
        ] as [$key, $routeName])

            <a
                href="{{ route(
                    $routeName,
                    [
                        'organization' =>
                            $organization->slug
                    ]
                ) }}"
                class="group
                       rounded-2xl
                       border
                       border-black/[0.06]
                       bg-[#faf9f6]
                       p-4
                       transition
                       hover:border-teal-100
                       hover:bg-teal-50/50"
            >
                <div
                    class="text-sm
                           font-semibold
                           text-[#33312d]
                           group-hover:text-teal-800"
                >
                    {{
                        __('sales.sections.'.$key.'.title')
                    }}
                </div>

                <p
                    class="mt-2
                           text-xs
                           leading-5
                           text-[#85827b]"
                >
                    {{
                        __('sales.sections.'.$key.'.short')
                    }}
                </p>
            </a>

        @endforeach
    </div>
</section>

@endsection
