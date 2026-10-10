@extends('layouts.workspace')

@section('title', __('hris.attendance.title') . ' - HRIS')

@section('content')
    @php
        $attendances = $attendances ?? collect();
        $employees = $employees ?? collect();
        $totalRecords =
            $totalRecords ?? (method_exists($attendances, 'total') ? $attendances->total() : $attendances->count());
        $presentCount = $presentCount ?? $attendances->count();
        $lateCount = $lateCount ?? $attendances->where('is_late', true)->count();
        $completedCount = $completedCount ?? $attendances->where('status', 'completed')->count();
    @endphp

    @if (session('success'))
        <div class="mb-5 rounded-xl border border-emerald-200 bg-emerald-50 px-4 py-3 text-[12px] text-emerald-700">
            {{ session('success') }}</div>
    @endif
    @if ($errors->any())
        <div class="mb-5 rounded-xl border border-red-200 bg-red-50 px-4 py-3">
            <ul class="list-disc space-y-1 pl-5 text-[11px] text-red-600">
                @foreach ($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    <div x-data="attendancePage()">
        <section class="flex flex-col gap-5 lg:flex-row lg:items-end lg:justify-between">
            <div>
                <div class="text-[10px] font-bold uppercase tracking-[.1em] text-blue-600">HRIS</div>
                <h1 class="mt-1 text-[28px] font-semibold tracking-[-.04em]">{{ __('hris.attendance.title') }}</h1>
                <p class="mt-2 max-w-[720px] text-[12px] leading-5 text-[#85827b]">{{ __('hris.attendance.description') }}
                </p>
            </div>
            <div class="flex
           flex-wrap
           gap-2">
                <a href="{{ route('hris.attendance.report.index', [
                    'organization' => $organization->slug,
                ]) }}"
                    class="inline-flex
               h-10
               items-center
               justify-center
               rounded-xl
               border
               border-black/[0.08]
               bg-white
               px-4
               text-[10px]
               font-semibold
               text-[#55524c]">
                    {{ __('attendance-report.title') }}
                </a>


                @include('modules.hris.attendance.partials.daily-map-button')


                <button type="button" @click="openCreate()"
                    class="h-10
               rounded-xl
               bg-[#1d1d1b]
               px-4
               text-[10px]
               font-semibold
               text-white">
                    + {{ __('hris.attendance.add') }}
                </button>
            </div>
        </section>

        <div class="mt-7">@include('modules.hris.partials.navigation')</div>
        {{-- <div class="mt-7">@include('modules.hris.attendance.partials.daily-map-button')</div> --}}


        <section class="mt-6 grid gap-3 sm:grid-cols-2 xl:grid-cols-4">
            @foreach ([[__('hris.attendance.total_records'), $totalRecords], [__('hris.attendance.present_records'), $presentCount], [__('hris.attendance.late_records'), $lateCount], [__('hris.attendance.completed_records'), $completedCount]] as [$label, $value])
                <div class="rounded-2xl border border-black/[0.06] bg-white p-5">
                    <div class="text-[10px] text-[#99968e]">{{ $label }}</div>
                    <div class="mt-3 text-[25px] font-semibold">{{ number_format((int) $value) }}</div>
                </div>
            @endforeach
        </section>

        <section class="mt-5 rounded-2xl border border-black/[0.06] bg-white p-4">
            <form method="GET" class="grid gap-3 md:grid-cols-2 xl:grid-cols-5">
                <input type="search" name="search" value="{{ request('search') }}"
                    placeholder="{{ __('hris.attendance.search_placeholder') }}"
                    class="h-10 rounded-xl border border-black/[0.08] bg-[#faf9f6] px-3 text-[11px] xl:col-span-2">
                <input type="date" name="from" value="{{ request('from') }}"
                    class="h-10 rounded-xl border border-black/[0.08] bg-[#faf9f6] px-3 text-[11px]">
                <input type="date" name="to" value="{{ request('to') }}"
                    class="h-10 rounded-xl border border-black/[0.08] bg-[#faf9f6] px-3 text-[11px]">
                <div class="flex gap-2"><button
                        class="h-10 flex-1 rounded-xl bg-[#1d1d1b] px-4 text-[10px] font-semibold text-white">{{ __('common.filter') }}</button><a
                        href="{{ route('hris.attendance.index', ['organization' => $organization->slug]) }}"
                        class="flex h-10 items-center rounded-xl border border-black/[0.08] px-3 text-[10px] font-semibold">{{ __('common.reset') }}</a>
                </div>
            </form>
        </section>

        <section class="mt-5 overflow-hidden rounded-2xl border border-black/[0.06] bg-white">
            <div class="overflow-x-auto">
                <table class="w-full min-w-[1250px]">
                    <thead class="border-b border-black/[0.06] bg-[#faf9f6]">
                        <tr class="text-left text-[9px] uppercase tracking-[.05em] text-[#99968e]">
                            <th class="px-5 py-3">{{ __('common.employee') }}</th>
                            <th class="px-4 py-3">{{ __('common.date') }}</th>
                            <th class="px-4 py-3">{{ __('hris.attendance.clock_in') }}</th>
                            <th class="px-4 py-3">{{ __('hris.attendance.clock_out') }}</th>
                            <th class="px-4 py-3">{{ __('hris.attendance.late') }}</th>
                            <th class="px-4 py-3">{{ __('hris.attendance.work_duration') }}</th>
                            <th class="px-4 py-3">{{ __('hris.attendance.method') }}</th>
                            <th class="px-5 py-3 text-right">{{ __('common.actions') }}</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-black/[0.05]">
                        @forelse($attendances as $attendance)
                            @php
                                $employeeName =
                                    $attendance->employee?->name ?? ($attendance->employee?->user?->name ?? '—');
                                $payload = [
                                    'action' => route('hris.attendance.update', [
                                        'organization' => $organization->slug,
                                        'attendance' => $attendance->id,
                                    ]),
                                    'employee_id' => $attendance->employee_id,
                                    'clock_in' => $attendance->clock_in?->format('Y-m-d\\TH:i') ?? '',
                                    'clock_out' => $attendance->clock_out?->format('Y-m-d\\TH:i') ?? '',
                                    'method' => $attendance->method ?? 'manual',
                                    'clock_in_latitude' => $attendance->clock_in_latitude ?? '',
                                    'clock_in_longitude' => $attendance->clock_in_longitude ?? '',
                                    'clock_out_latitude' => $attendance->clock_out_latitude ?? '',
                                    'clock_out_longitude' => $attendance->clock_out_longitude ?? '',
                                    'notes' => $attendance->notes ?? '',
                                ];
                            @endphp
                            <tr class="transition hover:bg-[#fcfbf8]">
                                <td class="px-5 py-4">
                                    <div class="text-[11px] font-semibold">{{ $employeeName }}</div>
                                    <div class="mt-1 text-[9px] text-[#99968e]">
                                        {{ $attendance->employee?->employee_number ?? '—' }}</div>
                                </td>
                                <td class="px-4 py-4 text-[10px]">
                                    {{ optional($attendance->attendance_date)->format('d M Y') ?? $attendance->attendance_date }}
                                </td>
                                <td class="px-4 py-4 text-[10px] font-semibold">
                                    {{ $attendance->clock_in?->format('H:i') ?? '—' }}</td>
                                <td class="px-4 py-4 text-[10px] font-semibold">
                                    {{ $attendance->clock_out?->format('H:i') ?? '—' }}</td>
                                <td class="px-4 py-4">
                                    @if ($attendance->is_late)
                                        <span
                                            class="rounded-full bg-orange-50 px-2.5 py-1 text-[9px] font-semibold text-orange-700">{{ $attendance->late_minutes }}
                                        {{ __('common.minutes') }}</span>@else<span
                                            class="rounded-full bg-emerald-50 px-2.5 py-1 text-[9px] font-semibold text-emerald-700">{{ __('hris.attendance.present') }}</span>
                                    @endif
                                </td>
                                <td class="px-4 py-4 text-[10px]">
                                    {{ $attendance->work_minutes !== null ? $attendance->work_minutes . ' ' . __('common.minutes') : '—' }}
                                </td>
                                <td class="px-4 py-4"><span
                                        class="rounded-full bg-[#f3f2ee] px-2.5 py-1 text-[9px] font-semibold text-[#77746d]">{{ __('hris.attendance.' . ($attendance->method ?? 'manual')) }}</span>
                                </td>
                                <td class="px-5 py-4 text-right"><button type="button"
                                        x-on:click="openEdit({{ Illuminate\Support\Js::from($payload) }})"
                                        class="h-9 rounded-xl border border-black/[0.08] px-3 text-[10px] font-semibold">{{ __('common.edit') }}</button>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="8" class="px-6 py-16 text-center text-[11px] text-[#99968e]">
                                    {{ __('hris.attendance.no_attendance') }}</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
            @if (method_exists($attendances, 'hasPages') && $attendances->hasPages())
                <div class="border-t border-black/[0.06] px-5 py-4">{{ $attendances->links() }}</div>
            @endif
        </section>

        <div x-show="modal" x-cloak class="fixed inset-0 z-[100]" @keydown.escape.window="modal=false">
            <div class="absolute inset-0 bg-black/40 backdrop-blur-[2px]" @click="modal=false"></div>
            <div class="relative flex min-h-full items-center justify-center p-4">
                <form method="POST" :action="form.action" @click.stop
                    class="w-full max-w-[720px] overflow-hidden rounded-[24px] bg-white">
                    @csrf<input type="hidden" name="_method" :value="form.mode === 'edit' ? 'PUT' : 'POST'">
                    <div class="flex items-start justify-between border-b border-black/[0.06] p-6">
                        <div>
                            <div class="text-[10px] font-bold uppercase tracking-[.08em] text-blue-600">
                                {{ __('hris.attendance.title') }}</div>
                            <h3 class="mt-1 text-[19px] font-semibold"
                                x-text="form.mode==='edit' ? @js(__('hris.attendance.edit')) : @js(__('hris.attendance.add'))">
                            </h3>
                        </div><button type="button" @click="modal=false"
                            class="flex h-9 w-9 items-center justify-center rounded-xl hover:bg-[#f3f2ee]">✕</button>
                    </div>
                    <div class="max-h-[72vh] overflow-y-auto p-6">
                        <div class="grid gap-4 sm:grid-cols-2">
                            <div class="sm:col-span-2"><label
                                    class="mb-2 block text-[11px] font-semibold">{{ __('common.employee') }}
                                    *</label><select name="employee_id" x-model="form.employee_id" required
                                    class="h-10 w-full rounded-xl border border-black/[0.08] bg-[#faf9f6] px-3 text-[11px]">
                                    <option value="">{{ __('common.select') }}</option>
                                    @foreach ($employees as $employee)
                                        <option value="{{ $employee->id }}">
                                            {{ $employee->name ?? ($employee->user?->name ?? '—') }}</option>
                                    @endforeach
                                </select></div>
                            <div><label class="mb-2 block text-[11px] font-semibold">{{ __('hris.attendance.clock_in') }}
                                    *</label><input type="datetime-local" name="clock_in" x-model="form.clock_in"
                                    required
                                    class="h-10 w-full rounded-xl border border-black/[0.08] bg-[#faf9f6] px-3 text-[11px]">
                            </div>
                            <div><label
                                    class="mb-2 block text-[11px] font-semibold">{{ __('hris.attendance.clock_out') }}</label><input
                                    type="datetime-local" name="clock_out" x-model="form.clock_out"
                                    class="h-10 w-full rounded-xl border border-black/[0.08] bg-[#faf9f6] px-3 text-[11px]">
                            </div>
                            <div><label
                                    class="mb-2 block text-[11px] font-semibold">{{ __('hris.attendance.method') }}</label><select
                                    name="method" x-model="form.method"
                                    class="h-10 w-full rounded-xl border border-black/[0.08] bg-[#faf9f6] px-3 text-[11px]">
                                    <option value="manual">{{ __('hris.attendance.manual') }}</option>
                                    <option value="web">{{ __('hris.attendance.web') }}</option>
                                    <option value="mobile">{{ __('hris.attendance.mobile') }}</option>
                                    <option value="device">{{ __('hris.attendance.device') }}</option>
                                </select></div>
                        </div>
                        <div class="mb-3 mt-6 text-[10px] font-bold uppercase tracking-[.06em] text-[#99968e]">
                            {{ __('hris.attendance.coordinates') }}</div>
                        <div class="grid gap-4 sm:grid-cols-2"><input name="clock_in_latitude"
                                x-model="form.clock_in_latitude"
                                placeholder="{{ __('hris.attendance.clock_in_latitude') }}"
                                class="h-10 rounded-xl border border-black/[0.08] bg-[#faf9f6] px-3 text-[11px]"><input
                                name="clock_in_longitude" x-model="form.clock_in_longitude"
                                placeholder="{{ __('hris.attendance.clock_in_longitude') }}"
                                class="h-10 rounded-xl border border-black/[0.08] bg-[#faf9f6] px-3 text-[11px]"><input
                                name="clock_out_latitude" x-model="form.clock_out_latitude"
                                placeholder="{{ __('hris.attendance.clock_out_latitude') }}"
                                class="h-10 rounded-xl border border-black/[0.08] bg-[#faf9f6] px-3 text-[11px]"><input
                                name="clock_out_longitude" x-model="form.clock_out_longitude"
                                placeholder="{{ __('hris.attendance.clock_out_longitude') }}"
                                class="h-10 rounded-xl border border-black/[0.08] bg-[#faf9f6] px-3 text-[11px]"></div>
                        <div class="mt-4"><label
                                class="mb-2 block text-[11px] font-semibold">{{ __('common.notes') }}</label>
                            <textarea name="notes" x-model="form.notes" rows="3"
                                class="w-full resize-none rounded-xl border border-black/[0.08] bg-[#faf9f6] px-3 py-2 text-[11px]"></textarea>
                        </div>
                    </div>
                    <div class="flex justify-end gap-2 border-t border-black/[0.06] bg-[#faf9f6] px-6 py-4"><button
                            type="button" @click="modal=false"
                            class="h-10 rounded-xl border border-black/[0.08] bg-white px-4 text-[11px] font-semibold">{{ __('common.cancel') }}</button><button
                            type="submit"
                            class="h-10 rounded-xl bg-[#1d1d1b] px-5 text-[11px] font-semibold text-white">{{ __('common.save') }}</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    @once
        <script>
            function attendancePage() {
                const storeUrl = @js(route('hris.attendance.store', ['organization' => $organization->slug]));
                const blank = () => ({
                    mode: 'create',
                    action: storeUrl,
                    employee_id: '',
                    clock_in: '',
                    clock_out: '',
                    method: 'manual',
                    clock_in_latitude: '',
                    clock_in_longitude: '',
                    clock_out_latitude: '',
                    clock_out_longitude: '',
                    notes: ''
                });
                return {
                    modal: false,
                    form: blank(),
                    openCreate() {
                        this.form = blank();
                        this.modal = true
                    },
                    openEdit(data) {
                        this.form = {
                            ...blank(),
                            ...data,
                            mode: 'edit'
                        };
                        this.modal = true
                    }
                };
            }
        </script>
    @endonce
@endsection
