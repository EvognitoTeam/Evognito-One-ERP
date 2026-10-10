@extends('layouts.workspace')

@section('title', __('hris.overview.title').' - HRIS')

@section('content')
@php
    $totalEmployees = $totalEmployees ?? $employeesCount ?? 0;
    $presentToday = $presentToday ?? $presentCount ?? 0;
    $lateToday = $lateToday ?? $lateCount ?? 0;
    $onLeaveToday = $onLeaveToday ?? $leaveToday ?? 0;
    $recentAttendances = collect($recentAttendances ?? $latestAttendances ?? []);
@endphp

<section>
    <div class="text-[13px] font-bold uppercase tracking-[.1em] text-blue-600">HRIS</div>
    <h1 class="mt-1 text-[28px] font-semibold tracking-[-.04em]">{{ __('hris.overview.title') }}</h1>
    <p class="mt-2 max-w-[720px] text-[13px] leading-5 text-[#85827b]">{{ __('hris.overview.description') }}</p>
</section>

<div class="mt-7">@include('modules.hris.partials.navigation')</div>

<section class="mt-6 grid gap-3 sm:grid-cols-2 xl:grid-cols-4">
    @foreach([
        [__('hris.overview.total_employees'), $totalEmployees, 'text-[#252421]'],
        [__('hris.overview.present_today'), $presentToday, 'text-emerald-700'],
        [__('hris.overview.late_today'), $lateToday, 'text-orange-700'],
        [__('hris.overview.on_leave_today'), $onLeaveToday, 'text-blue-700'],
    ] as [$label, $value, $class])
        <div class="rounded-2xl border border-black/[0.06] bg-white p-5">
            <div class="text-[13px] text-[#99968e]">{{ $label }}</div>
            <div class="mt-3 text-[28px] font-semibold {{ $class }}">{{ number_format((int)$value) }}</div>
        </div>
    @endforeach
</section>

<section class="mt-5 grid gap-5 xl:grid-cols-[360px_1fr]">
    <div class="rounded-2xl border border-black/[0.06] bg-white p-5">
        <div class="text-[13px] font-semibold">{{ __('hris.overview.quick_actions') }}</div>
        <div class="mt-4 grid gap-2">
            <a href="{{ route('hris.employees.index', ['organization' => $organization->slug]) }}" class="rounded-xl border border-black/[0.06] bg-[#faf9f6] px-4 py-3 text-[13px] font-semibold hover:bg-[#f3f2ee]">{{ __('hris.overview.manage_employees') }}</a>
            <a href="{{ route('hris.attendance.index', ['organization' => $organization->slug]) }}" class="rounded-xl border border-black/[0.06] bg-[#faf9f6] px-4 py-3 text-[13px] font-semibold hover:bg-[#f3f2ee]">{{ __('hris.overview.open_attendance') }}</a>
            <a href="{{ route('hris.calendar.index', ['organization' => $organization->slug]) }}" class="rounded-xl border border-black/[0.06] bg-[#faf9f6] px-4 py-3 text-[13px] font-semibold hover:bg-[#f3f2ee]">{{ __('hris.overview.open_calendar') }}</a>
            <a href="{{ route('hris.leave.index', ['organization' => $organization->slug]) }}" class="rounded-xl border border-black/[0.06] bg-[#faf9f6] px-4 py-3 text-[13px] font-semibold hover:bg-[#f3f2ee]">{{ __('hris.overview.open_leave') }}</a>
        </div>
    </div>

    <div class="overflow-hidden rounded-2xl border border-black/[0.06] bg-white">
        <div class="border-b border-black/[0.06] px-5 py-4 text-[13px] font-semibold">{{ __('hris.overview.recent_attendance') }}</div>
        <div class="divide-y divide-black/[0.05]">
            @forelse($recentAttendances as $attendance)
                @php
                    $employeeName = $attendance->employee?->name ?? $attendance->employee?->user?->name ?? '—';
                @endphp
                <div class="flex flex-col gap-2 px-5 py-4 sm:flex-row sm:items-center sm:justify-between">
                    <div>
                        <div class="text-[13px] font-semibold">{{ $employeeName }}</div>
                        <div class="mt-1 text-xs text-[#99968e]">{{ optional($attendance->attendance_date)->format('d M Y') ?? $attendance->attendance_date }}</div>
                    </div>
                    <div class="flex items-center gap-4 text-[13px]">
                        <span>{{ __('hris.attendance.clock_in') }}: <b>{{ optional($attendance->clock_in)->format('H:i') ?? '—' }}</b></span>
                        <span>{{ __('hris.attendance.clock_out') }}: <b>{{ optional($attendance->clock_out)->format('H:i') ?? '—' }}</b></span>
                        @if($attendance->is_late ?? false)<span class="rounded-full bg-orange-50 px-2 py-1 text-xs font-semibold text-orange-700">{{ __('hris.attendance.late') }}</span>@endif
                    </div>
                </div>
            @empty
                <div class="px-6 py-14 text-center text-[13px] text-[#99968e]">{{ __('hris.overview.no_recent_attendance') }}</div>
            @endforelse
        </div>
    </div>
</section>
@endsection
