@php
    $items = [
        ['performance.dashboard', 'performance.navigation.overview'],
        ['performance.master-data.index', 'performance.navigation.master_data'],
        ['performance.products.index', 'performance.navigation.products'],
        ['performance.kpi.index', 'performance.navigation.kpi'],
        ['performance.configuration.index', 'performance.navigation.configuration'],
    ];
@endphp

<div class="flex max-w-full gap-1 overflow-x-auto border-b border-black/[0.06]">
    @foreach($items as [$routeName, $labelKey])
        @php
            $active = request()->routeIs($routeName)
                || request()->routeIs(str($routeName)->beforeLast('.')->value().'.*');
        @endphp

        <a
            href="{{ route($routeName, ['organization' => $organization->slug]) }}"
            class="relative shrink-0 px-4 py-3.5 text-[13px] font-semibold transition {{ $active ? 'text-purple-800' : 'text-[#77746d] hover:text-[#252421]' }}"
        >
            {{ __($labelKey) }}

            @if($active)
                <span class="absolute bottom-0 left-3 right-3 h-[2px] rounded-full bg-purple-600"></span>
            @endif
        </a>
    @endforeach
</div>
