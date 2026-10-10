@extends('layouts.workspace')
@section('title', __('hris.configuration.position_schedules').' - HRIS')
@section('content')
@php
    $positions=$positions??collect();
    $positionSettings=collect($positionSettings??$settings??[]);
@endphp
@if(session('success'))<div class="mb-5 rounded-xl border border-emerald-200 bg-emerald-50 px-4 py-3 text-[13px] text-emerald-700">{{ session('success') }}</div>@endif
@if($errors->any())<div class="mb-5 rounded-xl border border-red-200 bg-red-50 px-4 py-3"><ul class="list-disc pl-5 text-[13px] text-red-600">@foreach($errors->all() as $error)<li>{{ $error }}</li>@endforeach</ul></div>@endif
<section><div class="text-[13px] font-bold uppercase tracking-[.1em] text-blue-600">HRIS</div><h1 class="mt-1 text-[28px] font-semibold tracking-[-.04em]">{{ __('hris.configuration.position_schedules') }}</h1><p class="mt-2 max-w-[720px] text-[13px] text-[#85827b]">{{ __('hris.configuration.position_schedule_help') }}</p></section>
<div class="mt-7">@include('modules.hris.partials.navigation')</div><div class="mt-5">@include('modules.hris.configuration.partials.nav')</div>
<section class="mt-5 rounded-2xl border border-black/[0.06] bg-white p-5">
    <div class="flex flex-col gap-4 lg:flex-row lg:items-end lg:justify-between"><div><div class="text-[13px] font-semibold">{{ __('hris.configuration.default_schedule') }}</div><div class="mt-1 text-xs text-[#99968e]">08:00 – 17:00</div></div>
        <form method="POST" action="{{ route('hris.configuration.position-schedules.apply-default',['organization'=>$organization->slug]) }}" class="flex flex-wrap gap-2">@csrf
            <input type="time" name="clock_in" value="08:00" class="h-9 rounded-xl border border-black/[0.08] bg-[#faf9f6] px-3 text-[13px]">
            <input type="time" name="clock_out" value="17:00" class="h-9 rounded-xl border border-black/[0.08] bg-[#faf9f6] px-3 text-[13px]">
            <input type="number" min="0" max="1440" name="late_tolerance_minutes" value="0" class="h-9 w-[120px] rounded-xl border border-black/[0.08] bg-[#faf9f6] px-3 text-[13px]" title="{{ __('hris.configuration.late_tolerance') }}">
            <button class="h-9 rounded-xl bg-[#1d1d1b] px-4 text-[13px] font-semibold text-white">{{ __('hris.configuration.apply_default') }}</button>
        </form>
    </div>
</section>
<section class="mt-5 overflow-hidden rounded-2xl border border-black/[0.06] bg-white"><div class="overflow-x-auto"><table class="w-full min-w-[950px]"><thead class="border-b border-black/[0.06] bg-[#faf9f6]"><tr class="text-left text-xs uppercase text-[#99968e]"><th class="px-5 py-3">{{ __('common.position') }}</th><th class="px-4 py-3">{{ __('hris.configuration.clock_in') }}</th><th class="px-4 py-3">{{ __('hris.configuration.clock_out') }}</th><th class="px-4 py-3">{{ __('hris.configuration.late_tolerance') }}</th><th class="px-4 py-3">{{ __('hris.configuration.mobile_position') }}</th><th class="px-5 py-3 text-right">{{ __('common.actions') }}</th></tr></thead><tbody class="divide-y divide-black/[0.05]">
@forelse($positions as $position)
@php
    $setting=$positionSettings->get($position->id) ?? $position->hrisPositionSetting ?? $position->hrisSetting ?? null;
@endphp
<tr>
<td class="px-5 py-4"><div class="text-[13px] font-semibold">{{ $position->name }}</div><div class="mt-1 text-xs text-[#99968e]">{{ $position->code??'—' }}</div></td>
<td class="px-4 py-4"><input type="time" name="clock_in" form="position-form-{{ $position->id }}" value="{{ old('clock_in', substr($setting?->clock_in??'08:00',0,5)) }}" required class="h-9 rounded-xl border border-black/[0.08] bg-[#faf9f6] px-3 text-[13px]"></td>
<td class="px-4 py-4"><input type="time" name="clock_out" form="position-form-{{ $position->id }}" value="{{ old('clock_out', substr($setting?->clock_out??'17:00',0,5)) }}" required class="h-9 rounded-xl border border-black/[0.08] bg-[#faf9f6] px-3 text-[13px]"></td>
<td class="px-4 py-4"><div class="flex items-center gap-2"><input type="number" name="late_tolerance_minutes" form="position-form-{{ $position->id }}" min="0" max="1440" value="{{ old('late_tolerance_minutes',$setting?->late_tolerance_minutes??0) }}" class="h-9 w-[90px] rounded-xl border border-black/[0.08] bg-[#faf9f6] px-3 text-[13px]"><span class="text-xs text-[#99968e]">{{ __('common.minutes') }}</span></div></td>
<td class="px-4 py-4"><label class="inline-flex items-center gap-2 text-[13px]"><input type="checkbox" name="is_mobile" form="position-form-{{ $position->id }}" value="1" @checked(old('is_mobile',$setting?->is_mobile??false))>{{ __('common.yes') }}</label></td>
<td class="px-5 py-4 text-right"><form id="position-form-{{ $position->id }}" method="POST" action="{{ route('hris.configuration.position-schedules.update',['organization'=>$organization->slug,'position'=>$position->id]) }}">@csrf @method('PUT')<button class="h-9 rounded-xl bg-[#1d1d1b] px-3 text-[13px] font-semibold text-white">{{ __('common.save') }}</button></form></td>
</tr>
@empty<tr><td colspan="6" class="px-6 py-16 text-center text-[13px] text-[#99968e]">{{ __('hris.configuration.no_positions') }}</td></tr>@endforelse
</tbody></table></div></section>
@endsection
