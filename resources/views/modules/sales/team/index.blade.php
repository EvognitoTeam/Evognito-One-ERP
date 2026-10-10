@extends('layouts.workspace')

@section('title', __('sales.team.title').' - Sales Force')

@section('content')
@php
    $employeeName = fn ($employee) => $employee?->name ?? $employee?->user?->name ?? '-';
    $showForm = request()->boolean('create') || $editing || $errors->any();
@endphp

<section class="flex flex-col gap-5 lg:flex-row lg:items-end lg:justify-between">
    <div>
        <div class="text-xs font-bold uppercase tracking-[0.12em] text-teal-700">Sales Force</div>
        <h1 class="mt-2 text-3xl font-semibold tracking-[-0.04em] text-[#252421]">{{ __('sales.team.title') }}</h1>
        <p class="mt-3 max-w-3xl text-sm leading-6 text-[#77746d]">{{ __('sales.team.description') }}</p>
    </div>
    <a href="{{ route('sales.team.index', ['organization' => $organization->slug, 'create' => 1]) }}"
       class="inline-flex h-11 items-center justify-center rounded-xl bg-[#1d1d1b] px-5 text-sm font-semibold text-white hover:bg-black">
        + {{ __('sales.team.add') }}
    </a>
</section>

<div class="mt-8">@include('modules.sales.partials.navigation')</div>
@include('modules.sales.partials.flash')

@if($showForm)
<section class="mt-5 rounded-2xl border border-black/[0.06] bg-white p-6">
    <div class="flex items-center justify-between gap-4">
        <div>
            <h2 class="text-xl font-semibold text-[#252421]">{{ $editing ? __('sales.team.edit') : __('sales.team.add') }}</h2>
            <p class="mt-1 text-sm text-[#85827b]">{{ __('sales.team.form_help') }}</p>
        </div>
        <a href="{{ route('sales.team.index', ['organization' => $organization->slug]) }}" class="text-sm font-semibold text-[#77746d]">{{ __('common.cancel') }}</a>
    </div>

    <form method="POST" action="{{ $editing
        ? route('sales.team.update', ['organization' => $organization->slug, 'profile' => $editing->id])
        : route('sales.team.store', ['organization' => $organization->slug]) }}" class="mt-6">
        @csrf
        @if($editing) @method('PUT') @endif

        <div class="grid gap-5 md:grid-cols-2 xl:grid-cols-3">
            <div>
                <label class="mb-2 block text-xs font-semibold text-[#66635d]">{{ __('sales.team.employee') }}</label>
                <select name="employee_id" required class="h-11 w-full rounded-xl border border-black/[0.08] bg-[#faf9f6] px-4 text-sm">
                    <option value="">{{ __('sales.common.select') }}</option>
                    @foreach($employees as $employee)
                        <option value="{{ $employee->id }}" @selected((string) old('employee_id', $editing?->employee_id) === (string) $employee->id)>
                            {{ $employeeName($employee) }}{{ $employee->employee_number ? ' — '.$employee->employee_number : '' }}
                        </option>
                    @endforeach
                </select>
            </div>

            <div>
                <label class="mb-2 block text-xs font-semibold text-[#66635d]">{{ __('sales.team.supervisor') }}</label>
                <select name="supervisor_employee_id" class="h-11 w-full rounded-xl border border-black/[0.08] bg-[#faf9f6] px-4 text-sm">
                    <option value="">{{ __('sales.common.none') }}</option>
                    @foreach($employees as $employee)
                        <option value="{{ $employee->id }}" @selected((string) old('supervisor_employee_id', $editing?->supervisor_employee_id) === (string) $employee->id)>
                            {{ $employeeName($employee) }}
                        </option>
                    @endforeach
                </select>
            </div>

            <div>
                <label class="mb-2 block text-xs font-semibold text-[#66635d]">{{ __('sales.team.area') }}</label>
                <select name="area_id" class="h-11 w-full rounded-xl border border-black/[0.08] bg-[#faf9f6] px-4 text-sm">
                    <option value="">{{ __('sales.common.none') }}</option>
                    @foreach($areas as $area)
                        <option value="{{ $area->id }}" @selected((string) old('area_id', $editing?->area_id) === (string) $area->id)>{{ $area->name }}</option>
                    @endforeach
                </select>
            </div>

            <div>
                <label class="mb-2 block text-xs font-semibold text-[#66635d]">{{ __('sales.team.daily_target') }}</label>
                <input type="number" min="0" max="100" name="daily_visit_target" value="{{ old('daily_visit_target', $editing?->daily_visit_target ?? 0) }}"
                       class="h-11 w-full rounded-xl border border-black/[0.08] bg-[#faf9f6] px-4 text-sm">
            </div>

            <label class="flex items-center gap-3 rounded-xl border border-black/[0.06] bg-[#faf9f6] p-4">
                <input type="checkbox" name="is_field_sales" value="1" class="size-4" @checked(old('is_field_sales', $editing?->is_field_sales ?? true))>
                <span class="text-sm font-semibold text-[#44413c]">{{ __('sales.team.field_sales') }}</span>
            </label>

            <label class="flex items-center gap-3 rounded-xl border border-black/[0.06] bg-[#faf9f6] p-4">
                <input type="checkbox" name="is_active" value="1" class="size-4" @checked(old('is_active', $editing?->is_active ?? true))>
                <span class="text-sm font-semibold text-[#44413c]">{{ __('sales.common.active') }}</span>
            </label>
        </div>

        <div class="mt-6 flex justify-end gap-2 border-t border-black/[0.06] pt-5">
            <a href="{{ route('sales.team.index', ['organization' => $organization->slug]) }}" class="inline-flex h-11 items-center rounded-xl border border-black/[0.08] px-5 text-sm font-semibold">{{ __('common.cancel') }}</a>
            <button class="h-11 rounded-xl bg-[#1d1d1b] px-6 text-sm font-semibold text-white">{{ __('common.save') }}</button>
        </div>
    </form>
</section>
@endif

<section class="mt-5 rounded-2xl border border-black/[0.06] bg-white p-5">
    <form method="GET" class="grid gap-3 md:grid-cols-[1fr_220px_180px_auto]">
        <input name="search" value="{{ $search }}" placeholder="{{ __('sales.team.search_placeholder') }}" class="h-11 rounded-xl border border-black/[0.08] bg-[#faf9f6] px-4 text-sm">
        <select name="area_id" class="h-11 rounded-xl border border-black/[0.08] bg-[#faf9f6] px-4 text-sm">
            <option value="">{{ __('sales.team.all_areas') }}</option>
            @foreach($areas as $area)<option value="{{ $area->id }}" @selected((string) $areaId === (string) $area->id)>{{ $area->name }}</option>@endforeach
        </select>
        <select name="status" class="h-11 rounded-xl border border-black/[0.08] bg-[#faf9f6] px-4 text-sm">
            <option value="">{{ __('sales.common.all_status') }}</option>
            <option value="active" @selected($status === 'active')>{{ __('sales.common.active') }}</option>
            <option value="inactive" @selected($status === 'inactive')>{{ __('sales.common.inactive') }}</option>
        </select>
        <button class="h-11 rounded-xl bg-[#1d1d1b] px-5 text-sm font-semibold text-white">{{ __('common.filter') }}</button>
    </form>
</section>

<section class="mt-5 overflow-hidden rounded-2xl border border-black/[0.06] bg-white">
    <div class="overflow-x-auto">
        <table class="w-full min-w-[1000px]">
            <thead class="border-b border-black/[0.06] bg-[#faf9f6] text-left text-xs font-semibold text-[#77746d]">
                <tr>
                    <th class="px-5 py-4">{{ __('sales.team.employee') }}</th>
                    <th class="px-5 py-4">{{ __('sales.team.position_branch') }}</th>
                    <th class="px-5 py-4">{{ __('sales.team.supervisor') }}</th>
                    <th class="px-5 py-4">{{ __('sales.team.area') }}</th>
                    <th class="px-5 py-4">{{ __('sales.team.daily_target') }}</th>
                    <th class="px-5 py-4">{{ __('common.status') }}</th>
                    <th class="px-5 py-4 text-right">{{ __('common.actions') }}</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-black/[0.05]">
                @forelse($profiles as $profile)
                    <tr class="hover:bg-[#fcfbf8]">
                        <td class="px-5 py-4">
                            <div class="text-sm font-semibold">{{ $employeeName($profile->employee) }}</div>
                            <div class="mt-1 text-xs text-[#99968e]">{{ $profile->employee?->employee_number ?? '—' }}</div>
                        </td>
                        <td class="px-5 py-4 text-sm text-[#55524c]">
                            {{ $profile->employee?->position?->name ?? '—' }}<br>
                            <span class="text-xs text-[#99968e]">{{ $profile->employee?->branch?->name ?? '—' }}</span>
                        </td>
                        <td class="px-5 py-4 text-sm">{{ $employeeName($profile->supervisor) }}</td>
                        <td class="px-5 py-4 text-sm">{{ $profile->area?->name ?? '—' }}</td>
                        <td class="px-5 py-4 text-sm font-semibold">{{ $profile->daily_visit_target }}</td>
                        <td class="px-5 py-4">
                            <span class="inline-flex rounded-full px-3 py-1.5 text-xs font-semibold {{ $profile->is_active ? 'bg-emerald-50 text-emerald-700' : 'bg-slate-100 text-slate-600' }}">
                                {{ $profile->is_active ? __('sales.common.active') : __('sales.common.inactive') }}
                            </span>
                        </td>
                        <td class="px-5 py-4">
                            <div class="flex justify-end gap-2">
                                <a href="{{ route('sales.team.index', ['organization' => $organization->slug, 'edit' => $profile->id]) }}" class="rounded-lg border border-black/[0.08] px-3 py-2 text-xs font-semibold">{{ __('common.edit') }}</a>
                                <form method="POST" action="{{ route('sales.team.destroy', ['organization' => $organization->slug, 'profile' => $profile->id]) }}" onsubmit="return confirm(@js(__('sales.common.confirm_delete')))" >
                                    @csrf @method('DELETE')
                                    <button class="rounded-lg border border-red-100 bg-red-50 px-3 py-2 text-xs font-semibold text-red-700">{{ __('common.delete') }}</button>
                                </form>
                            </div>
                        </td>
                    </tr>
                @empty
                    <tr><td colspan="7" class="px-6 py-16 text-center text-sm text-[#85827b]">{{ __('sales.team.empty') }}</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
    @if($profiles->hasPages())<div class="border-t border-black/[0.06] px-5 py-4">{{ $profiles->links() }}</div>@endif
</section>
@endsection
