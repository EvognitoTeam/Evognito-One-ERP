@extends('layouts.workspace')
@section('title', __('sales.visits.detail').' - Sales Force')
@section('content')
@php $employeeName = $visit->employee?->name ?? $visit->employee?->user?->name ?? '-'; @endphp
<section class="flex flex-col gap-5 lg:flex-row lg:items-end lg:justify-between"><div><div class="text-xs font-bold uppercase tracking-[0.12em] text-teal-700">Sales Force · {{ __('sales.visits.title') }}</div><h1 class="mt-2 text-3xl font-semibold tracking-[-0.04em]">{{ __('sales.visits.detail') }}</h1><p class="mt-3 text-sm text-[#77746d]">{{ $visit->customer?->name ?? '—' }} · {{ $employeeName }}</p></div><a href="{{ route('sales.visits.index',['organization'=>$organization->slug]) }}" class="inline-flex h-11 items-center rounded-xl border border-black/[0.08] bg-white px-5 text-sm font-semibold">← {{ __('common.back') }}</a></section>
<div class="mt-8">@include('modules.sales.partials.navigation')</div>@include('modules.sales.partials.flash')
<section class="mt-5 grid gap-5 xl:grid-cols-[1.25fr_.75fr]">
<div class="space-y-5">
    <article class="rounded-2xl border border-black/[0.06] bg-white p-6"><h2 class="text-lg font-semibold">{{ __('sales.visits.visit_information') }}</h2><div class="mt-5 grid gap-4 sm:grid-cols-2 lg:grid-cols-3">
        @foreach([
            [__('sales.visits.sales_employee'), $employeeName],
            [__('sales.visits.customer'), $visit->customer?->name ?? '—'],
            [__('sales.visits.area'), $visit->area?->name ?? '—'],
            [__('sales.visits.start_date'), $visit->start_date?->format('d M Y') ?? '—'],
            [__('sales.visits.end_date'), $visit->end_date?->format('d M Y') ?? '—'],
            [__('common.status'), __('sales.status.'.$visit->status)],
        ] as [$label,$value])<div><div class="text-xs font-semibold text-[#99968e]">{{ $label }}</div><div class="mt-1 text-sm font-semibold text-[#33312d]">{{ $value }}</div></div>@endforeach
    </div>@if($visit->purpose)<div class="mt-5 border-t border-black/[0.06] pt-5"><div class="text-xs font-semibold text-[#99968e]">{{ __('sales.visits.purpose') }}</div><div class="mt-1 text-sm text-[#44413c]">{{ $visit->purpose }}</div></div>@endif
    @if($visit->notes)<div class="mt-4"><div class="text-xs font-semibold text-[#99968e]">{{ __('sales.common.notes') }}</div><div class="mt-1 whitespace-pre-line text-sm text-[#44413c]">{{ $visit->notes }}</div></div>@endif</article>

    <article class="rounded-2xl border border-black/[0.06] bg-white p-6"><h2 class="text-lg font-semibold">{{ __('sales.visits.products') }}</h2><div class="mt-4 flex flex-wrap gap-2">@forelse($visit->products as $product)<span class="rounded-full bg-teal-50 px-3 py-1.5 text-xs font-semibold text-teal-700">{{ $product->name }}{{ $product->packaging ? ' · '.$product->packaging : '' }}</span>@empty<span class="text-sm text-[#85827b]">{{ __('sales.visits.no_products') }}</span>@endforelse</div></article>

    <article class="rounded-2xl border border-black/[0.06] bg-white p-6"><h2 class="text-lg font-semibold">{{ __('sales.visits.visit_attendance') }}</h2>@if($visit->attendance)<div class="mt-5 grid gap-4 sm:grid-cols-2 lg:grid-cols-4">@foreach([
        [__('sales.visits.clock_in'), $visit->attendance->clock_in?->format('d M Y H:i') ?? '—'],
        [__('sales.visits.clock_out'), $visit->attendance->clock_out?->format('d M Y H:i') ?? '—'],
        [__('sales.visits.clock_in_distance'), $visit->attendance->clock_in_distance_meters !== null ? number_format($visit->attendance->clock_in_distance_meters,0).' m' : '—'],
        [__('sales.visits.clock_out_distance'), $visit->attendance->clock_out_distance_meters !== null ? number_format($visit->attendance->clock_out_distance_meters,0).' m' : '—'],
    ] as [$label,$value])<div><div class="text-xs font-semibold text-[#99968e]">{{ $label }}</div><div class="mt-1 text-sm font-semibold">{{ $value }}</div></div>@endforeach</div><div class="mt-5 flex flex-wrap gap-2"><span class="rounded-full px-3 py-1.5 text-xs font-semibold {{ $visit->attendance->clock_in_within_geofence === true ? 'bg-emerald-50 text-emerald-700' : ($visit->attendance->clock_in_within_geofence === false ? 'bg-red-50 text-red-700' : 'bg-slate-100 text-slate-600') }}">IN: {{ $visit->attendance->clock_in_within_geofence === true ? __('sales.monitoring.inside') : ($visit->attendance->clock_in_within_geofence === false ? __('sales.monitoring.outside') : __('sales.monitoring.unknown')) }}</span><span class="rounded-full px-3 py-1.5 text-xs font-semibold {{ $visit->attendance->clock_out_within_geofence === true ? 'bg-emerald-50 text-emerald-700' : ($visit->attendance->clock_out_within_geofence === false ? 'bg-red-50 text-red-700' : 'bg-slate-100 text-slate-600') }}">OUT: {{ $visit->attendance->clock_out_within_geofence === true ? __('sales.monitoring.inside') : ($visit->attendance->clock_out_within_geofence === false ? __('sales.monitoring.outside') : __('sales.monitoring.unknown')) }}</span></div>@else<p class="mt-4 text-sm text-[#85827b]">{{ __('sales.visits.attendance_not_available') }}</p>@endif</article>
</div>

<aside class="space-y-5">
    <article class="rounded-2xl border border-black/[0.06] bg-white p-6"><h2 class="text-lg font-semibold">{{ __('sales.visits.receipts') }}</h2><form method="POST" enctype="multipart/form-data" action="{{ route('sales.visits.receipts.store',['organization'=>$organization->slug,'visit'=>$visit->id]) }}" class="mt-4 space-y-3">@csrf<input type="file" name="receipt" accept="image/jpeg,image/png,application/pdf" required class="block w-full rounded-xl border border-black/[0.08] bg-[#faf9f6] p-3 text-sm"><input type="number" step="0.01" min="0" name="amount" placeholder="{{ __('sales.visits.receipt_amount') }}" class="h-11 w-full rounded-xl border border-black/[0.08] bg-[#faf9f6] px-4 text-sm"><textarea name="notes" rows="2" placeholder="{{ __('sales.common.notes') }}" class="w-full rounded-xl border border-black/[0.08] bg-[#faf9f6] px-4 py-3 text-sm"></textarea><button class="h-11 w-full rounded-xl bg-[#1d1d1b] text-sm font-semibold text-white">{{ __('sales.visits.upload_receipt') }}</button></form>
    <div class="mt-5 space-y-3 border-t border-black/[0.06] pt-5">@forelse($visit->receipts as $receipt)<div class="rounded-xl border border-black/[0.06] bg-[#faf9f6] p-4"><a target="_blank" href="{{ Storage::disk('public')->url($receipt->file_path) }}" class="block truncate text-sm font-semibold text-teal-700">{{ $receipt->original_name }}</a><div class="mt-1 text-xs text-[#99968e]">{{ $receipt->uploaded_at?->format('d M Y H:i') }}{{ $receipt->amount !== null ? ' · Rp '.number_format((float)$receipt->amount,0,',','.') : '' }}</div><form method="POST" action="{{ route('sales.receipts.destroy',['organization'=>$organization->slug,'receipt'=>$receipt->id]) }}" class="mt-3" onsubmit="return confirm(@js(__('sales.common.confirm_delete')))" >@csrf @method('DELETE')<button class="text-xs font-semibold text-red-600">{{ __('common.delete') }}</button></form></div>@empty<p class="text-sm text-[#85827b]">{{ __('sales.visits.no_receipts') }}</p>@endforelse</div></article>
</aside>
</section>
@endsection
