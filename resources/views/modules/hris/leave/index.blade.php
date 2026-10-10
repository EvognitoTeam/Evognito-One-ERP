@extends('layouts.workspace')

@section('title', __('hris.leave.title').' - HRIS')

@section('content')
@php
    $leaves = $leaves ?? collect();
    $employees = $employees ?? collect();
    $profiles = collect($profiles ?? []);
    $totalRequests = $totalRequests ?? (method_exists($leaves,'total') ? $leaves->total() : $leaves->count());
    $pendingCount = $pendingCount ?? $leaves->where('status','pending')->count();
    $approvedCount = $approvedCount ?? $leaves->where('status','approved')->count();
    $rejectedCount = $rejectedCount ?? $leaves->where('status','rejected')->count();
@endphp

@if(session('success'))<div class="mb-5 rounded-xl border border-emerald-200 bg-emerald-50 px-4 py-3 text-[13px] text-emerald-700">{{ session('success') }}</div>@endif
@if($errors->any())<div class="mb-5 rounded-xl border border-red-200 bg-red-50 px-4 py-3"><ul class="list-disc space-y-1 pl-5 text-[13px] text-red-600">@foreach($errors->all() as $error)<li>{{ $error }}</li>@endforeach</ul></div>@endif

<div x-data="leavePage()">
    <section class="flex flex-col gap-5 lg:flex-row lg:items-end lg:justify-between">
        <div><div class="text-[13px] font-bold uppercase tracking-[.1em] text-blue-600">HRIS</div><h1 class="mt-1 text-[28px] font-semibold tracking-[-.04em]">{{ __('hris.leave.title') }}</h1><p class="mt-2 max-w-[720px] text-[13px] leading-5 text-[#85827b]">{{ __('hris.leave.description') }}</p></div>
        <button type="button" @click="openCreate()" class="h-10 rounded-xl bg-[#1d1d1b] px-4 text-[13px] font-semibold text-white">+ {{ __('hris.leave.new_request') }}</button>
    </section>

    <div class="mt-7">@include('modules.hris.partials.navigation')</div>

    <section class="mt-6 grid gap-3 sm:grid-cols-2 xl:grid-cols-4">
        @foreach([[__('hris.leave.total_requests'),$totalRequests],[__('hris.leave.pending_requests'),$pendingCount],[__('hris.leave.approved_requests'),$approvedCount],[__('hris.leave.rejected_requests'),$rejectedCount]] as [$label,$value])
            <div class="rounded-2xl border border-black/[0.06] bg-white p-5"><div class="text-[13px] text-[#99968e]">{{ $label }}</div><div class="mt-3 text-[25px] font-semibold">{{ number_format((int)$value) }}</div></div>
        @endforeach
    </section>

    <section class="mt-5 rounded-2xl border border-black/[0.06] bg-white p-4">
        <form method="GET" class="grid gap-3 md:grid-cols-2 xl:grid-cols-5">
            <input type="search" name="search" value="{{ request('search') }}" placeholder="{{ __('hris.leave.search_placeholder') }}" class="h-10 rounded-xl border border-black/[0.08] bg-[#faf9f6] px-3 text-[13px] xl:col-span-2">
            <select name="type" class="h-10 rounded-xl border border-black/[0.08] bg-[#faf9f6] px-3 text-[13px]"><option value="">{{ __('hris.leave.all_types') }}</option>@foreach(['cuti','izin','sakit'] as $type)<option value="{{ $type }}" @selected(request('type')===$type)>{{ __('hris.leave.'.$type) }}</option>@endforeach</select>
            <select name="status" class="h-10 rounded-xl border border-black/[0.08] bg-[#faf9f6] px-3 text-[13px]"><option value="">{{ __('hris.leave.all_status') }}</option>@foreach(['pending','approved','rejected'] as $status)<option value="{{ $status }}" @selected(request('status')===$status)>{{ __('common.'.$status) }}</option>@endforeach</select>
            <div class="flex gap-2"><button class="h-10 flex-1 rounded-xl bg-[#1d1d1b] px-4 text-[13px] font-semibold text-white">{{ __('common.filter') }}</button><a href="{{ route('hris.leave.index',['organization'=>$organization->slug]) }}" class="flex h-10 items-center rounded-xl border border-black/[0.08] px-3 text-[13px] font-semibold">{{ __('common.reset') }}</a></div>
        </form>
    </section>

    <section class="mt-5 overflow-hidden rounded-2xl border border-black/[0.06] bg-white">
        <div class="overflow-x-auto"><table class="w-full min-w-[1250px]">
            <thead class="border-b border-black/[0.06] bg-[#faf9f6]"><tr class="text-left text-xs uppercase tracking-[.05em] text-[#99968e]"><th class="px-5 py-3">{{ __('common.employee') }}</th><th class="px-4 py-3">{{ __('hris.leave.type') }}</th><th class="px-4 py-3">{{ __('hris.leave.request_period') }}</th><th class="px-4 py-3">{{ __('hris.leave.duration') }}</th><th class="px-4 py-3">{{ __('hris.leave.reason') }}</th><th class="px-4 py-3">{{ __('common.status') }}</th><th class="px-5 py-3 text-right">{{ __('common.actions') }}</th></tr></thead>
            <tbody class="divide-y divide-black/[0.05]">
            @forelse($leaves as $leave)
                @php
                    $employeeName=$leave->employee?->name ?? $leave->employee?->user?->name ?? '—';
                    $payload=[
                        'action'=>route('hris.leave.update',['organization'=>$organization->slug,'leave'=>$leave->id]),
                        'employee_id'=>$leave->employee_id,'type'=>$leave->type,'start_date'=>$leave->start_date?->format('Y-m-d') ?? '',
                        'end_date'=>$leave->end_date?->format('Y-m-d') ?? '', 'start_time'=>$leave->start_time ?? '', 'end_time'=>$leave->end_time ?? '',
                        'reason'=>is_string($leave->reason ?? null) ? $leave->reason : json_encode($leave->reason ?? ''),
                    ];
                    $reviewPayload=[
                        'action'=>route('hris.leave.status',['organization'=>$organization->slug,'leave'=>$leave->id]),
                        'name'=>$employeeName,'status'=>$leave->status,'approval_notes'=>$leave->approval_notes ?? $leave->notes ?? ''
                    ];
                @endphp
                <tr class="transition hover:bg-[#fcfbf8]">
                    <td class="px-5 py-4"><div class="text-[13px] font-semibold">{{ $employeeName }}</div><div class="mt-1 text-xs text-[#99968e]">{{ $leave->employee?->employee_number ?? '—' }}</div></td>
                    <td class="px-4 py-4"><span class="rounded-full bg-blue-50 px-2.5 py-1 text-xs font-semibold text-blue-700">{{ __('hris.leave.'.$leave->type) }}</span></td>
                    <td class="px-4 py-4 text-[13px] font-medium">{{ $leave->start_date?->format('d M Y') }} – {{ $leave->end_date?->format('d M Y') }}</td>
                    <td class="px-4 py-4 text-[13px] font-semibold">{{ $leave->duration_days ?? 1 }} {{ __('common.days') }}</td>
                    <td class="max-w-[260px] px-4 py-4 text-[13px] text-[#77746d]">{{ is_string($leave->reason ?? null) ? $leave->reason : json_encode($leave->reason ?? '') }}</td>
                    <td class="px-4 py-4"><span class="rounded-full px-2.5 py-1 text-xs font-semibold {{ $leave->status==='approved' ? 'bg-emerald-50 text-emerald-700' : ($leave->status==='rejected' ? 'bg-red-50 text-red-600' : 'bg-orange-50 text-orange-700') }}">{{ __('common.'.$leave->status) }}</span></td>
                    <td class="px-5 py-4 text-right"><div class="flex justify-end gap-2">@if($leave->status==='pending')<button type="button" x-on:click="openEdit({{ Illuminate\Support\Js::from($payload) }})" class="h-9 rounded-xl border border-black/[0.08] px-3 text-[13px] font-semibold">{{ __('common.edit') }}</button>@endif<button type="button" x-on:click="openReview({{ Illuminate\Support\Js::from($reviewPayload) }})" class="h-9 rounded-xl bg-[#1d1d1b] px-3 text-[13px] font-semibold text-white">{{ __('hris.leave.review') }}</button>@if($leave->attachment_path ?? false)<a href="{{ route('hris.leave.attachment',['organization'=>$organization->slug,'leave'=>$leave->id]) }}" class="flex h-9 items-center rounded-xl border border-black/[0.08] px-3 text-[13px] font-semibold">{{ __('hris.leave.view_attachment') }}</a>@endif</div></td>
                </tr>
            @empty
                <tr><td colspan="7" class="px-6 py-16 text-center text-[13px] text-[#99968e]">{{ __('hris.leave.no_requests') }}</td></tr>
            @endforelse
            </tbody>
        </table></div>
        @if(method_exists($leaves,'hasPages') && $leaves->hasPages())<div class="border-t border-black/[0.06] px-5 py-4">{{ $leaves->links() }}</div>@endif
    </section>

    <div x-show="modal" x-cloak class="fixed inset-0 z-[100]" @keydown.escape.window="modal=false"><div class="absolute inset-0 bg-black/40" @click="modal=false"></div><div class="relative flex min-h-full items-center justify-center p-4">
        <form method="POST" enctype="multipart/form-data" :action="form.action" @click.stop class="w-full max-w-[650px] overflow-hidden rounded-[24px] bg-white">@csrf<input type="hidden" name="_method" :value="form.mode==='edit'?'PUT':'POST'">
            <div class="border-b border-black/[0.06] p-6"><div class="text-[13px] font-bold uppercase tracking-[.08em] text-blue-600">{{ __('hris.leave.title') }}</div><h3 class="mt-1 text-[19px] font-semibold" x-text="form.mode==='edit'?@js(__('hris.leave.edit_request')):@js(__('hris.leave.new_request'))"></h3></div>
            <div class="max-h-[72vh] overflow-y-auto p-6"><div class="grid gap-4 sm:grid-cols-2">
                <div class="sm:col-span-2"><label class="mb-2 block text-[13px] font-semibold">{{ __('common.employee') }} *</label><select name="employee_id" x-model="form.employee_id" required class="h-10 w-full rounded-xl border border-black/[0.08] bg-[#faf9f6] px-3 text-[13px]"><option value="">{{ __('common.select') }}</option>@foreach($employees as $employee)<option value="{{ $employee->id }}">{{ $employee->name ?? $employee->user?->name ?? '—' }}</option>@endforeach</select></div>
                <div><label class="mb-2 block text-[13px] font-semibold">{{ __('hris.leave.type') }} *</label><select name="type" x-model="form.type" required class="h-10 w-full rounded-xl border border-black/[0.08] bg-[#faf9f6] px-3 text-[13px]">@foreach(['cuti','izin','sakit'] as $type)<option value="{{ $type }}">{{ __('hris.leave.'.$type) }}</option>@endforeach</select></div><div></div>
                <div><label class="mb-2 block text-[13px] font-semibold">{{ __('common.start_date') }} *</label><input type="date" name="start_date" x-model="form.start_date" required class="h-10 w-full rounded-xl border border-black/[0.08] bg-[#faf9f6] px-3 text-[13px]"></div>
                <div><label class="mb-2 block text-[13px] font-semibold">{{ __('common.end_date') }} *</label><input type="date" name="end_date" x-model="form.end_date" :min="form.start_date" required class="h-10 w-full rounded-xl border border-black/[0.08] bg-[#faf9f6] px-3 text-[13px]"></div>
                <div><label class="mb-2 block text-[13px] font-semibold">{{ __('common.start_time') }}</label><input type="time" name="start_time" x-model="form.start_time" class="h-10 w-full rounded-xl border border-black/[0.08] bg-[#faf9f6] px-3 text-[13px]"></div>
                <div><label class="mb-2 block text-[13px] font-semibold">{{ __('common.end_time') }}</label><input type="time" name="end_time" x-model="form.end_time" class="h-10 w-full rounded-xl border border-black/[0.08] bg-[#faf9f6] px-3 text-[13px]"></div>
                <div class="sm:col-span-2"><label class="mb-2 block text-[13px] font-semibold">{{ __('hris.leave.reason') }} *</label><textarea name="reason" x-model="form.reason" rows="4" required class="w-full resize-none rounded-xl border border-black/[0.08] bg-[#faf9f6] px-3 py-2 text-[13px]"></textarea></div>
                <div class="sm:col-span-2"><label class="mb-2 block text-[13px] font-semibold">{{ __('hris.leave.attachment') }}</label><input type="file" name="attachment" accept=".pdf,.jpg,.jpeg,.png" class="block w-full rounded-xl border border-black/[0.08] bg-[#faf9f6] px-3 py-2 text-[13px]"></div>
            </div></div>
            <div class="flex justify-end gap-2 border-t border-black/[0.06] bg-[#faf9f6] px-6 py-4"><button type="button" @click="modal=false" class="h-10 rounded-xl border border-black/[0.08] bg-white px-4 text-[13px] font-semibold">{{ __('common.cancel') }}</button><button type="submit" class="h-10 rounded-xl bg-[#1d1d1b] px-5 text-[13px] font-semibold text-white">{{ __('common.save') }}</button></div>
        </form>
    </div></div>

    <div x-show="reviewModal" x-cloak class="fixed inset-0 z-[110]"><div class="absolute inset-0 bg-black/40" @click="reviewModal=false"></div><div class="relative flex min-h-full items-center justify-center p-4">
        <form method="POST" :action="review.action" @click.stop class="w-full max-w-[500px] overflow-hidden rounded-[24px] bg-white">@csrf @method('PUT')
            <div class="p-6"><div class="text-[13px] font-bold uppercase tracking-[.08em] text-blue-600">{{ __('hris.leave.approval') }}</div><h3 class="mt-1 text-[18px] font-semibold" x-text="review.name"></h3><label class="mb-2 mt-5 block text-[13px] font-semibold">{{ __('hris.leave.approval_notes') }}</label><textarea name="approval_notes" x-model="review.approval_notes" rows="4" class="w-full resize-none rounded-xl border border-black/[0.08] bg-[#faf9f6] px-3 py-2 text-[13px]"></textarea></div>
            <div class="flex justify-end gap-2 border-t border-black/[0.06] bg-[#faf9f6] px-6 py-4"><button type="button" @click="reviewModal=false" class="h-10 rounded-xl border border-black/[0.08] bg-white px-4 text-[13px] font-semibold">{{ __('common.close') }}</button><button type="submit" name="status" value="rejected" class="h-10 rounded-xl bg-red-600 px-5 text-[13px] font-semibold text-white">{{ __('hris.leave.reject') }}</button><button type="submit" name="status" value="approved" class="h-10 rounded-xl bg-emerald-600 px-5 text-[13px] font-semibold text-white">{{ __('hris.leave.approve') }}</button></div>
        </form>
    </div></div>
</div>

@once
<script>
function leavePage(){
    const storeUrl=@js(route('hris.leave.store',['organization'=>$organization->slug]));
    const blank=()=>({mode:'create',action:storeUrl,employee_id:'',type:'cuti',start_date:'',end_date:'',start_time:'',end_time:'',reason:''});
    return{modal:false,reviewModal:false,form:blank(),review:{action:'',name:'',approval_notes:''},openCreate(){this.form=blank();this.modal=true},openEdit(d){this.form={...blank(),...d,mode:'edit'};this.modal=true},openReview(d){this.review={...this.review,...d};this.reviewModal=true}};
}
</script>
@endonce
@endsection
