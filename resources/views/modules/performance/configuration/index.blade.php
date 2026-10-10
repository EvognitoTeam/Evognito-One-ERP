@extends('layouts.workspace')
@section('title', __('performance.configuration.title').' - Performance')
@section('content')
@include('modules.performance.partials.header', [
    'title' => __('performance.configuration.title'),
    'description' => __('performance.configuration.description'),
])
@if(session('success'))<div class="mt-5 rounded-xl border border-emerald-100 bg-emerald-50 px-4 py-3 text-sm text-emerald-700">{{ session('success') }}</div>@endif
<form method="POST" action="{{ route('performance.configuration.update', ['organization' => $organization->slug]) }}"
    class="mt-6 max-w-3xl rounded-2xl border border-black/[0.06] bg-white p-6">
    @csrf @method('PUT')
    <h2 class="text-lg font-semibold">{{ __('performance.configuration.weight_settings') }}</h2>
    <p class="mt-2 text-sm leading-6 text-[#85827b]">{{ __('performance.configuration.weight_help') }}</p>
    <div class="mt-5 grid gap-4 sm:grid-cols-2">
        <div><label class="mb-2 block text-xs font-semibold">{{ __('performance.configuration.competency_weight') }}</label><input type="number" step="0.01" min="0" max="100" name="competency_weight" value="{{ $policy->competency_weight }}" class="h-11 w-full rounded-xl border border-black/[0.08] px-4 text-sm"></div>
        <div><label class="mb-2 block text-xs font-semibold">{{ __('performance.configuration.kpi_weight') }}</label><input type="number" step="0.01" min="0" max="100" name="kpi_weight" value="{{ $policy->kpi_weight }}" class="h-11 w-full rounded-xl border border-black/[0.08] px-4 text-sm"></div>
    </div>
    <div class="mt-5 space-y-3">
        <label class="flex items-center gap-3 text-sm"><input type="checkbox" name="require_level1_approval" value="1" @checked($policy->require_level1_approval)>{{ __('performance.configuration.require_level1') }}</label>
        <label class="flex items-center gap-3 text-sm"><input type="checkbox" name="require_level2_approval" value="1" @checked($policy->require_level2_approval)>{{ __('performance.configuration.require_level2') }}</label>
        <label class="flex items-center gap-3 text-sm"><input type="checkbox" name="require_employee_acknowledgement" value="1" @checked($policy->require_employee_acknowledgement)>{{ __('performance.configuration.require_acknowledgement') }}</label>
    </div>
    <button class="mt-6 h-11 rounded-xl bg-purple-700 px-5 text-sm font-semibold text-white">{{ __('performance.actions.save_settings') }}</button>
</form>
@endsection
