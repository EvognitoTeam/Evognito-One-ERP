@extends('layouts.workspace')
@section('title', __('hris.calendar.title').' - HRIS')
@section('content')
@php
    $employeeName = fn($employee) => $employee->name ?? $employee->user?->name ?? '—';
    $filterParams = request()->except('month');
@endphp
<section class="flex flex-col gap-5 lg:flex-row lg:items-end lg:justify-between">
    <div><div class="text-[13px] font-bold uppercase tracking-[.1em] text-blue-600">HRIS</div><h1 class="mt-1 text-[28px] font-semibold tracking-[-.04em]">{{ __('hris.calendar.title') }}</h1><p class="mt-2 max-w-[720px] text-[13px] leading-5 text-[#85827b]">{{ __('hris.calendar.description') }}</p></div>
    <a href="{{ route('hris.calendar.annual-leave.index',['organization'=>$organization->slug]) }}" class="inline-flex h-10 items-center justify-center rounded-xl bg-[#1d1d1b] px-4 text-[13px] font-semibold text-white">+ {{ __('hris.calendar.schedule_annual_leave') }}</a>
</section>
<div class="mt-7">@include('modules.hris.partials.navigation')</div>
<div class="mt-5">@include('modules.hris.calendar.partials.nav')</div>
<section class="mt-5 rounded-2xl border border-black/[0.06] bg-white p-4">
    <form method="GET" action="{{ route('hris.calendar.index',['organization'=>$organization->slug]) }}" class="grid gap-3 md:grid-cols-2 xl:grid-cols-5">
        <input type="hidden" name="month" value="{{ $month->format('Y-m') }}">
        <select name="type" class="h-10 rounded-xl border border-black/[0.08] bg-[#faf9f6] px-3 text-[13px]">
            @foreach(['all'=>__('hris.calendar.all_events'),'holiday'=>__('hris.calendar.holiday'),'annual_leave'=>__('hris.calendar.annual_leave'),'cuti'=>__('hris.calendar.approved_leave'),'izin'=>__('hris.calendar.permission'),'sakit'=>__('hris.calendar.sick')] as $value=>$label)<option value="{{ $value }}" @selected(($filters['type']??'all')===$value)>{{ $label }}</option>@endforeach
        </select>
        <select name="branch_id" class="h-10 rounded-xl border border-black/[0.08] bg-[#faf9f6] px-3 text-[13px]"><option value="">{{ __('hris.calendar.all_branches') }}</option>@foreach($branches as $branch)<option value="{{ $branch->id }}" @selected((string)($filters['branch_id']??'')===(string)$branch->id)>{{ $branch->name }}</option>@endforeach</select>
        <select name="department_id" class="h-10 rounded-xl border border-black/[0.08] bg-[#faf9f6] px-3 text-[13px]"><option value="">{{ __('hris.calendar.all_departments') }}</option>@foreach($departments as $department)<option value="{{ $department->id }}" @selected((string)($filters['department_id']??'')===(string)$department->id)>{{ $department->name }}</option>@endforeach</select>
        <select name="employee_id" class="h-10 rounded-xl border border-black/[0.08] bg-[#faf9f6] px-3 text-[13px]"><option value="">{{ __('hris.calendar.all_employees') }}</option>@foreach($employees as $employee)<option value="{{ $employee->id }}" @selected((string)($filters['employee_id']??'')===(string)$employee->id)>{{ $employeeName($employee) }}</option>@endforeach</select>
        <div class="flex gap-2"><button class="h-10 flex-1 rounded-xl bg-[#1d1d1b] px-4 text-[13px] font-semibold text-white">{{ __('common.filter') }}</button><a href="{{ route('hris.calendar.index',['organization'=>$organization->slug]) }}" class="flex h-10 items-center rounded-xl border border-black/[0.08] px-3 text-[13px] font-semibold">{{ __('common.reset') }}</a></div>
    </form>
</section>
<section class="mt-5 overflow-hidden rounded-2xl border border-black/[0.06] bg-white">
    <div class="flex items-center justify-between border-b border-black/[0.06] px-5 py-4">
        <a href="{{ route('hris.calendar.index',array_merge(['organization'=>$organization->slug,'month'=>$previousMonth],$filterParams)) }}" class="flex h-9 w-9 items-center justify-center rounded-xl border border-black/[0.08] text-[16px]">‹</a>
        <div class="text-center"><div class="text-[15px] font-semibold">{{ $month->translatedFormat('F Y') }}</div><a href="{{ route('hris.calendar.index',['organization'=>$organization->slug]) }}" class="mt-1 inline-block text-xs font-semibold text-blue-600">{{ __('common.today') }}</a></div>
        <a href="{{ route('hris.calendar.index',array_merge(['organization'=>$organization->slug,'month'=>$nextMonth],$filterParams)) }}" class="flex h-9 w-9 items-center justify-center rounded-xl border border-black/[0.08] text-[16px]">›</a>
    </div>
    <div class="grid grid-cols-7 border-b border-black/[0.06] bg-[#faf9f6]">@foreach([1,2,3,4,5,6,7] as $dow)<div class="px-2 py-3 text-center text-xs font-bold uppercase tracking-[.06em] text-[#99968e]">{{ \Carbon\Carbon::now()->startOfWeek()->addDays($dow-1)->translatedFormat('D') }}</div>@endforeach</div>
    <div class="grid grid-cols-7">
        @foreach($calendarDays as $day)
            @php $date=$day['date']; @endphp
            <div class="min-h-[135px] border-b border-r border-black/[0.05] p-2 {{ !$day['current_month']?'bg-[#faf9f6]/60':'bg-white' }}">
                <span class="flex h-7 w-7 items-center justify-center rounded-full text-[13px] font-semibold {{ $day['today']?'bg-[#1d1d1b] text-white':($day['current_month']?'text-[#44413c]':'text-[#bbb8b0]') }}">{{ $date->day }}</span>
                <div class="mt-2 space-y-1">@foreach($day['events']->take(4) as $event)<div class="truncate rounded-md px-2 py-1.5 text-xs font-semibold text-white" style="background-color:{{ $event['color'] }}" title="{{ $event['title'] }}">{{ $event['title'] }}</div>@endforeach @if($day['events']->count()>4)<div class="px-1 text-xs font-semibold text-[#77746d]">+{{ $day['events']->count()-4 }} {{ __('hris.calendar.more') }}</div>@endif</div>
            </div>
        @endforeach
    </div>
</section>
<div class="mt-4 flex flex-wrap gap-4 text-xs text-[#77746d]"><span>{{ __('hris.calendar.legend') }}:</span><span>● {{ __('hris.calendar.holiday') }}</span><span>● {{ __('hris.calendar.annual_leave') }}</span><span>● {{ __('hris.calendar.permission') }}</span><span>● {{ __('hris.calendar.sick') }}</span></div>
@endsection
