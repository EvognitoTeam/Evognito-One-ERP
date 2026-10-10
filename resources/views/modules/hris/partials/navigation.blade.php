@php
    $navigation = [
        ['label' => __('hris.navigation.overview'), 'route' => 'hris.dashboard', 'active' => request()->routeIs('hris.dashboard')],
        ['label' => __('hris.navigation.calendar'), 'route' => 'hris.calendar.index', 'active' => request()->routeIs('hris.calendar.*') || request()->routeIs('hris.holidays.*')],
        ['label' => __('hris.navigation.employees'), 'route' => 'hris.employees.index', 'active' => request()->routeIs('hris.employees.*')],
        ['label' => __('hris.navigation.attendance'), 'route' => 'hris.attendance.index', 'active' => request()->routeIs('hris.attendance.*')],
        ['label' => __('hris.navigation.leave'), 'route' => 'hris.leave.index', 'active' => request()->routeIs('hris.leave.*')],
        ['label' => __('hris.navigation.payroll'), 'route' => 'hris.payroll.index', 'active' => request()->routeIs('hris.payroll.*')],
        ['label' => __('hris.navigation.configuration'), 'route' => 'hris.configuration.organization-structure.index', 'active' => request()->routeIs('hris.configuration.*')],
    ];
@endphp
<div class="border-b border-black/[0.06]">
    <div class="flex max-w-full gap-1 overflow-x-auto pb-px">
        @foreach($navigation as $item)
            <a href="{{ route($item['route'], ['organization' => $organization->slug]) }}" class="relative shrink-0 rounded-t-lg px-3.5 py-3 text-[13px] font-semibold transition {{ $item['active'] ? 'text-[#1d1d1b]' : 'text-[#8b8881] hover:text-[#1d1d1b]' }}">
                {{ $item['label'] }}
                @if($item['active'])<span class="absolute bottom-0 left-3 right-3 h-[2px] rounded-full bg-[#e8742a]"></span>@endif
            </a>
        @endforeach
    </div>
</div>
