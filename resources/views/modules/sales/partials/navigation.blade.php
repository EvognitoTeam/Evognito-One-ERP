@php
    $navigation = [
        [
            'label' => __('sales.navigation.overview'),
            'route' => 'sales.dashboard',
            'active' => request()->routeIs('sales.dashboard'),
        ],
        [
            'label' => __('sales.navigation.team'),
            'route' => 'sales.team.index',
            'active' => request()->routeIs('sales.team.*'),
        ],
        [
            'label' => __('sales.navigation.customers'),
            'route' => 'sales.customers.index',
            'active' => request()->routeIs('sales.customers.*'),
        ],
        [
            'label' => __('sales.navigation.products'),
            'route' => 'sales.products.index',
            'active' => request()->routeIs('sales.products.*'),
        ],
        [
            'label' => __('sales.navigation.visits'),
            'route' => 'sales.visits.index',
            'active' => request()->routeIs('sales.visits.*'),
        ],
        [
            'label' => __('sales.navigation.monitoring'),
            'route' => 'sales.monitoring.index',
            'active' => request()->routeIs('sales.monitoring.*'),
        ],
        [
            'label' => __('sales.navigation.records'),
            'route' => 'sales.records.index',
            'active' => request()->routeIs('sales.records.*'),
        ],
        [
            'label' => __('sales.navigation.reports'),
            'route' => 'sales.reports.index',
            'active' => request()->routeIs('sales.reports.*'),
        ],
        [
            'label' => __('sales.navigation.configuration'),
            'route' => 'sales.configuration.index',
            'active' => request()->routeIs('sales.configuration.*'),
        ],
    ];
@endphp

<div class="border-b border-black/[0.06]">
    <div class="flex max-w-full gap-1 overflow-x-auto pb-px">
        @foreach($navigation as $item)
            <a
                href="{{ route(
                    $item['route'],
                    [
                        'organization' =>
                            $organization->slug
                    ]
                ) }}"
                class="relative
                       shrink-0
                       rounded-t-lg
                       px-4 py-3.5
                       text-[13px]
                       font-semibold
                       transition
                       {{
                            $item['active']
                                ? 'text-[#1d1d1b]'
                                : 'text-[#7d7a73] hover:text-[#1d1d1b]'
                       }}"
            >
                {{ $item['label'] }}

                @if($item['active'])
                    <span
                        class="absolute
                               bottom-0
                               left-3 right-3
                               h-[2px]
                               rounded-full
                               bg-teal-600"
                    ></span>
                @endif
            </a>
        @endforeach
    </div>
</div>
