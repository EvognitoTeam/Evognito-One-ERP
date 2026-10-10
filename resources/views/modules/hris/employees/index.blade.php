@extends('layouts.workspace')

@section('title', __('hris.employees.title').' - HRIS')

@section('content')
@php
    $employees = $employees ?? collect();
    $branches = $branches ?? collect();
    $departments = $departments ?? collect();
    $positions = $positions ?? collect();
    $supervisors = $supervisors ?? $employees;
@endphp

@if(session('success'))
    <div class="mb-5 rounded-xl border border-emerald-200 bg-emerald-50 px-4 py-3 text-[13px] text-emerald-700">{{ session('success') }}</div>
@endif
@if($errors->any())
    <div class="mb-5 rounded-xl border border-red-200 bg-red-50 px-4 py-3"><ul class="list-disc space-y-1 pl-5 text-[13px] text-red-600">@foreach($errors->all() as $error)<li>{{ $error }}</li>@endforeach</ul></div>
@endif

<div x-data="employeesPage()">
    <section class="flex flex-col gap-5 lg:flex-row lg:items-end lg:justify-between">
        <div>
            <div class="text-[13px] font-bold uppercase tracking-[.1em] text-blue-600">HRIS</div>
            <h1 class="mt-1 text-[28px] font-semibold tracking-[-.04em]">{{ __('hris.employees.title') }}</h1>
            <p class="mt-2 max-w-[720px] text-[13px] leading-5 text-[#85827b]">{{ __('hris.employees.description') }}</p>
        </div>
        <button type="button" @click="openCreate()" class="h-10 rounded-xl bg-[#1d1d1b] px-4 text-[13px] font-semibold text-white">+ {{ __('hris.employees.add') }}</button>
    </section>

    <div class="mt-7">@include('modules.hris.partials.navigation')</div>

    <section class="mt-5 rounded-2xl border border-black/[0.06] bg-white p-4">
        <form method="GET" class="grid gap-3 md:grid-cols-2 xl:grid-cols-5">
            <input type="search" name="search" value="{{ request('search') }}" placeholder="{{ __('hris.employees.search_placeholder') }}" class="h-10 rounded-xl border border-black/[0.08] bg-[#faf9f6] px-3 text-[13px] xl:col-span-2">
            <select name="branch_id" class="h-10 rounded-xl border border-black/[0.08] bg-[#faf9f6] px-3 text-[13px]"><option value="">{{ __('hris.employees.all_branches') }}</option>@foreach($branches as $branch)<option value="{{ $branch->id }}" @selected((string)request('branch_id') === (string)$branch->id)>{{ $branch->name }}</option>@endforeach</select>
            <select name="department_id" class="h-10 rounded-xl border border-black/[0.08] bg-[#faf9f6] px-3 text-[13px]"><option value="">{{ __('hris.employees.all_departments') }}</option>@foreach($departments as $department)<option value="{{ $department->id }}" @selected((string)request('department_id') === (string)$department->id)>{{ $department->name }}</option>@endforeach</select>
            <div class="flex gap-2"><button type="submit" class="h-10 flex-1 rounded-xl bg-[#1d1d1b] px-4 text-[13px] font-semibold text-white">{{ __('common.filter') }}</button><a href="{{ route('hris.employees.index', ['organization' => $organization->slug]) }}" class="flex h-10 items-center rounded-xl border border-black/[0.08] px-3 text-[13px] font-semibold">{{ __('common.reset') }}</a></div>
        </form>
    </section>

    <section class="mt-5 overflow-hidden rounded-2xl border border-black/[0.06] bg-white">
        <div class="overflow-x-auto">
            <table class="w-full min-w-[1100px]">
                <thead class="border-b border-black/[0.06] bg-[#faf9f6]"><tr class="text-left text-xs uppercase tracking-[.05em] text-[#99968e]"><th class="px-5 py-3">{{ __('hris.employees.employee') }}</th><th class="px-4 py-3">{{ __('hris.employees.branch') }}</th><th class="px-4 py-3">{{ __('hris.employees.department') }}</th><th class="px-4 py-3">{{ __('hris.employees.position') }}</th><th class="px-4 py-3">{{ __('common.status') }}</th><th class="px-4 py-3">{{ __('hris.employees.leave_quota') }}</th><th class="px-5 py-3 text-right">{{ __('common.actions') }}</th></tr></thead>
                <tbody class="divide-y divide-black/[0.05]">
                @forelse($employees as $employee)
                    @php
                        $profile = $employee->hrisProfile ?? $employee->hrProfile ?? null;
                        $employeeName = $employee->name ?? $employee->user?->name ?? '—';
                        $payload = [
                            'action' => route('hris.employees.update', ['organization' => $organization->slug, 'employee' => $employee->id]),
                            'employee_number' => $employee->employee_number ?? '', 'name' => $employeeName,
                            'email' => $employee->email ?? $employee->user?->email ?? '', 'phone' => $employee->phone ?? '',
                            'branch_id' => $employee->branch_id ?? '', 'department_id' => $employee->department_id ?? '', 'position_id' => $employee->position_id ?? '',
                            'status' => $employee->status ?? 'active', 'employment_type' => $profile?->employment_type ?? '', 'join_date' => $profile?->join_date?->format('Y-m-d') ?? '',
                            'end_date' => $profile?->end_date?->format('Y-m-d') ?? '', 'supervisor_employee_id' => $profile?->supervisor_employee_id ?? '',
                            'leave_quota' => $profile?->leave_quota ?? 10, 'debt_minutes' => $profile?->debt_minutes ?? 0, 'notes' => $profile?->notes ?? '',
                        ];
                    @endphp
                    <tr class="transition hover:bg-[#fcfbf8]">
                        <td class="px-5 py-4"><div class="text-[13px] font-semibold">{{ $employeeName }}</div><div class="mt-1 text-xs text-[#99968e]">{{ $employee->employee_number ?? '—' }} · {{ $employee->email ?? $employee->user?->email ?? '—' }}</div></td>
                        <td class="px-4 py-4 text-[13px]">{{ $employee->branch?->name ?? '—' }}</td>
                        <td class="px-4 py-4 text-[13px]">{{ $employee->department?->name ?? '—' }}</td>
                        <td class="px-4 py-4 text-[13px]">{{ $employee->position?->name ?? '—' }}</td>
                        <td class="px-4 py-4"><span class="rounded-full {{ ($employee->status ?? 'active') === 'active' ? 'bg-emerald-50 text-emerald-700' : 'bg-[#f3f2ee] text-[#77746d]' }} px-2.5 py-1 text-xs font-semibold">{{ ($employee->status ?? 'active') === 'active' ? __('common.active') : __('common.inactive') }}</span></td>
                        <td class="px-4 py-4 text-[13px] font-semibold">{{ $profile?->leave_quota ?? 10 }} {{ __('common.days') }}</td>
                        <td class="px-5 py-4 text-right"><button type="button" x-on:click="openEdit({{ Illuminate\Support\Js::from($payload) }})" class="h-9 rounded-xl border border-black/[0.08] px-3 text-[13px] font-semibold">{{ __('common.edit') }}</button></td>
                    </tr>
                @empty
                    <tr><td colspan="7" class="px-6 py-16 text-center text-[13px] text-[#99968e]">{{ __('hris.employees.no_employee') }}</td></tr>
                @endforelse
                </tbody>
            </table>
        </div>
        @if(method_exists($employees, 'hasPages') && $employees->hasPages())<div class="border-t border-black/[0.06] px-5 py-4">{{ $employees->links() }}</div>@endif
    </section>

    <div x-show="modal" x-cloak class="fixed inset-0 z-[100]" @keydown.escape.window="modal=false">
        <div class="absolute inset-0 bg-black/40 backdrop-blur-[2px]" @click="modal=false"></div>
        <div class="relative flex min-h-full items-center justify-center p-4">
            <form method="POST" :action="form.action" @click.stop class="w-full max-w-[760px] overflow-hidden rounded-[24px] bg-white">
                @csrf
                <input type="hidden" name="_method" :value="form.mode === 'edit' ? 'PUT' : 'POST'">
                <div class="flex items-start justify-between border-b border-black/[0.06] p-6"><div><div class="text-[13px] font-bold uppercase tracking-[.08em] text-blue-600">HRIS</div><h3 class="mt-1 text-[19px] font-semibold" x-text="form.mode === 'edit' ? @js(__('hris.employees.edit')) : @js(__('hris.employees.add'))"></h3></div><button type="button" @click="modal=false" class="flex h-9 w-9 items-center justify-center rounded-xl hover:bg-[#f3f2ee]">✕</button></div>
                <div class="max-h-[72vh] overflow-y-auto p-6">
                    <div class="mb-3 text-[13px] font-bold uppercase tracking-[.06em] text-[#99968e]">{{ __('hris.employees.basic_information') }}</div>
                    <div class="grid gap-4 sm:grid-cols-2">
                        <div><label class="mb-2 block text-[13px] font-semibold">{{ __('hris.employees.employee_number') }} *</label><input name="employee_number" x-model="form.employee_number" required class="h-10 w-full rounded-xl border border-black/[0.08] bg-[#faf9f6] px-3 text-[13px]"></div>
                        <div><label class="mb-2 block text-[13px] font-semibold">{{ __('common.name') }} *</label><input name="name" x-model="form.name" required class="h-10 w-full rounded-xl border border-black/[0.08] bg-[#faf9f6] px-3 text-[13px]"></div>
                        <div><label class="mb-2 block text-[13px] font-semibold">{{ __('hris.employees.email') }}</label><input type="email" name="email" x-model="form.email" class="h-10 w-full rounded-xl border border-black/[0.08] bg-[#faf9f6] px-3 text-[13px]"></div>
                        <div><label class="mb-2 block text-[13px] font-semibold">{{ __('hris.employees.phone') }}</label><input name="phone" x-model="form.phone" class="h-10 w-full rounded-xl border border-black/[0.08] bg-[#faf9f6] px-3 text-[13px]"></div>
                    </div>

                    <div class="mb-3 mt-6 text-[13px] font-bold uppercase tracking-[.06em] text-[#99968e]">{{ __('hris.employees.employment_information') }}</div>
                    <div class="grid gap-4 sm:grid-cols-2">
                        <div><label class="mb-2 block text-[13px] font-semibold">{{ __('hris.employees.branch') }}</label><select name="branch_id" x-model="form.branch_id" class="h-10 w-full rounded-xl border border-black/[0.08] bg-[#faf9f6] px-3 text-[13px]"><option value="">—</option>@foreach($branches as $branch)<option value="{{ $branch->id }}">{{ $branch->name }}</option>@endforeach</select></div>
                        <div><label class="mb-2 block text-[13px] font-semibold">{{ __('hris.employees.department') }}</label><select name="department_id" x-model="form.department_id" class="h-10 w-full rounded-xl border border-black/[0.08] bg-[#faf9f6] px-3 text-[13px]"><option value="">—</option>@foreach($departments as $department)<option value="{{ $department->id }}">{{ $department->name }}</option>@endforeach</select></div>
                        <div><label class="mb-2 block text-[13px] font-semibold">{{ __('hris.employees.position') }}</label><select name="position_id" x-model="form.position_id" class="h-10 w-full rounded-xl border border-black/[0.08] bg-[#faf9f6] px-3 text-[13px]"><option value="">—</option>@foreach($positions as $position)<option value="{{ $position->id }}">{{ $position->name }}</option>@endforeach</select></div>
                        <div><label class="mb-2 block text-[13px] font-semibold">{{ __('hris.employees.employee_status') }}</label><select name="status" x-model="form.status" class="h-10 w-full rounded-xl border border-black/[0.08] bg-[#faf9f6] px-3 text-[13px]"><option value="active">{{ __('common.active') }}</option><option value="inactive">{{ __('common.inactive') }}</option></select></div>
                        <div><label class="mb-2 block text-[13px] font-semibold">{{ __('hris.employees.employment_type') }}</label><input name="employment_type" x-model="form.employment_type" class="h-10 w-full rounded-xl border border-black/[0.08] bg-[#faf9f6] px-3 text-[13px]"></div>
                        <div><label class="mb-2 block text-[13px] font-semibold">{{ __('hris.employees.supervisor') }}</label><select name="supervisor_employee_id" x-model="form.supervisor_employee_id" class="h-10 w-full rounded-xl border border-black/[0.08] bg-[#faf9f6] px-3 text-[13px]"><option value="">{{ __('hris.employees.select_supervisor') }}</option>@foreach($supervisors as $supervisor)<option value="{{ $supervisor->id }}">{{ $supervisor->name ?? $supervisor->user?->name ?? '—' }}</option>@endforeach</select></div>
                        <div><label class="mb-2 block text-[13px] font-semibold">{{ __('hris.employees.join_date') }}</label><input type="date" name="join_date" x-model="form.join_date" class="h-10 w-full rounded-xl border border-black/[0.08] bg-[#faf9f6] px-3 text-[13px]"></div>
                        <div><label class="mb-2 block text-[13px] font-semibold">{{ __('hris.employees.end_date') }}</label><input type="date" name="end_date" x-model="form.end_date" class="h-10 w-full rounded-xl border border-black/[0.08] bg-[#faf9f6] px-3 text-[13px]"></div>
                    </div>

                    <div class="mb-3 mt-6 text-[13px] font-bold uppercase tracking-[.06em] text-[#99968e]">{{ __('hris.employees.leave_information') }}</div>
                    <div class="grid gap-4 sm:grid-cols-2">
                        <div><label class="mb-2 block text-[13px] font-semibold">{{ __('hris.employees.leave_quota') }}</label><input type="number" min="0" name="leave_quota" x-model="form.leave_quota" class="h-10 w-full rounded-xl border border-black/[0.08] bg-[#faf9f6] px-3 text-[13px]"></div>
                        <div><label class="mb-2 block text-[13px] font-semibold">{{ __('hris.employees.debt_minutes') }}</label><input type="number" min="0" name="debt_minutes" x-model="form.debt_minutes" class="h-10 w-full rounded-xl border border-black/[0.08] bg-[#faf9f6] px-3 text-[13px]"></div>
                        <div class="sm:col-span-2"><label class="mb-2 block text-[13px] font-semibold">{{ __('common.notes') }}</label><textarea rows="3" name="notes" x-model="form.notes" class="w-full resize-none rounded-xl border border-black/[0.08] bg-[#faf9f6] px-3 py-2 text-[13px]"></textarea></div>
                    </div>
                </div>
                <div class="flex justify-end gap-2 border-t border-black/[0.06] bg-[#faf9f6] px-6 py-4"><button type="button" @click="modal=false" class="h-10 rounded-xl border border-black/[0.08] bg-white px-4 text-[13px] font-semibold">{{ __('common.cancel') }}</button><button type="submit" class="h-10 rounded-xl bg-[#1d1d1b] px-5 text-[13px] font-semibold text-white">{{ __('common.save') }}</button></div>
            </form>
        </div>
    </div>
</div>

@once
<script>
function employeesPage() {
    const storeUrl = @js(route('hris.employees.store', ['organization' => $organization->slug]));
    const blank = () => ({ mode:'create', action:storeUrl, employee_number:'', name:'', email:'', phone:'', branch_id:'', department_id:'', position_id:'', status:'active', employment_type:'', join_date:'', end_date:'', supervisor_employee_id:'', leave_quota:10, debt_minutes:0, notes:'' });
    return {
        modal:false,
        form:blank(),
        openCreate(){ this.form=blank(); this.modal=true; },
        openEdit(data){ this.form={...blank(), ...data, mode:'edit'}; this.modal=true; },
    };
}
</script>
@endonce
@endsection
