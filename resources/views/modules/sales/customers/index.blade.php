@extends('layouts.workspace')

@section('title', __('sales.customers.title').' - Sales Force')

@section('content')
@php $showForm = request()->boolean('create') || $editing || $errors->any(); @endphp

<section class="flex flex-col gap-5 lg:flex-row lg:items-end lg:justify-between">
    <div>
        <div class="text-xs font-bold uppercase tracking-[0.12em] text-teal-700">Sales Force</div>
        <h1 class="mt-2 text-3xl font-semibold tracking-[-0.04em] text-[#252421]">{{ __('sales.customers.title') }}</h1>
        <p class="mt-3 max-w-3xl text-sm leading-6 text-[#77746d]">{{ __('sales.customers.description') }}</p>
    </div>
    <a href="{{ route('sales.customers.index', ['organization' => $organization->slug, 'create' => 1]) }}" class="inline-flex h-11 items-center rounded-xl bg-[#1d1d1b] px-5 text-sm font-semibold text-white">+ {{ __('sales.customers.add') }}</a>
</section>

<div class="mt-8">@include('modules.sales.partials.navigation')</div>
@include('modules.sales.partials.flash')

@if($showForm)
<section class="mt-5 rounded-2xl border border-black/[0.06] bg-white p-6">
    <div class="flex items-center justify-between gap-4">
        <div>
            <h2 class="text-xl font-semibold text-[#252421]">{{ $editing ? __('sales.customers.edit') : __('sales.customers.add') }}</h2>
            <p class="mt-1 text-sm text-[#85827b]">{{ __('sales.customers.form_help') }}</p>
        </div>
        <a href="{{ route('sales.customers.index', ['organization' => $organization->slug]) }}" class="text-sm font-semibold text-[#77746d]">{{ __('common.cancel') }}</a>
    </div>

    <form method="POST" action="{{ $editing ? route('sales.customers.update', ['organization' => $organization->slug, 'customer' => $editing->id]) : route('sales.customers.store', ['organization' => $organization->slug]) }}" class="mt-6">
        @csrf @if($editing) @method('PUT') @endif
        <div class="grid gap-5 md:grid-cols-2 xl:grid-cols-3">
            <div><label class="mb-2 block text-xs font-semibold">{{ __('sales.customers.code') }}</label><input name="code" value="{{ old('code', $editing?->code) }}" class="h-11 w-full rounded-xl border border-black/[0.08] bg-[#faf9f6] px-4 text-sm"></div>
            <div><label class="mb-2 block text-xs font-semibold">{{ __('sales.customers.name') }}</label><input required name="name" value="{{ old('name', $editing?->name) }}" class="h-11 w-full rounded-xl border border-black/[0.08] bg-[#faf9f6] px-4 text-sm"></div>
            <div><label class="mb-2 block text-xs font-semibold">{{ __('sales.customers.area') }}</label><select name="area_id" class="h-11 w-full rounded-xl border border-black/[0.08] bg-[#faf9f6] px-4 text-sm"><option value="">{{ __('sales.common.none') }}</option>@foreach($areas as $area)<option value="{{ $area->id }}" @selected((string) old('area_id', $editing?->area_id) === (string) $area->id)>{{ $area->name }}</option>@endforeach</select></div>
            <div><label class="mb-2 block text-xs font-semibold">{{ __('sales.customers.contact_name') }}</label><input name="contact_name" value="{{ old('contact_name', $editing?->contact_name) }}" class="h-11 w-full rounded-xl border border-black/[0.08] bg-[#faf9f6] px-4 text-sm"></div>
            <div><label class="mb-2 block text-xs font-semibold">{{ __('sales.customers.phone') }}</label><input name="phone" value="{{ old('phone', $editing?->phone) }}" class="h-11 w-full rounded-xl border border-black/[0.08] bg-[#faf9f6] px-4 text-sm"></div>
            <div><label class="mb-2 block text-xs font-semibold">{{ __('sales.customers.email') }}</label><input type="email" name="email" value="{{ old('email', $editing?->email) }}" class="h-11 w-full rounded-xl border border-black/[0.08] bg-[#faf9f6] px-4 text-sm"></div>
            <div><label class="mb-2 block text-xs font-semibold">{{ __('sales.customers.city') }}</label><input name="city" value="{{ old('city', $editing?->city) }}" class="h-11 w-full rounded-xl border border-black/[0.08] bg-[#faf9f6] px-4 text-sm"></div>
            <div><label class="mb-2 block text-xs font-semibold">Latitude</label><input type="number" step="0.0000001" name="latitude" value="{{ old('latitude', $editing?->latitude) }}" class="h-11 w-full rounded-xl border border-black/[0.08] bg-[#faf9f6] px-4 text-sm"></div>
            <div><label class="mb-2 block text-xs font-semibold">Longitude</label><input type="number" step="0.0000001" name="longitude" value="{{ old('longitude', $editing?->longitude) }}" class="h-11 w-full rounded-xl border border-black/[0.08] bg-[#faf9f6] px-4 text-sm"></div>
            <div><label class="mb-2 block text-xs font-semibold">{{ __('sales.customers.radius') }}</label><input required type="number" min="10" name="geofence_radius_meters" value="{{ old('geofence_radius_meters', $editing?->geofence_radius_meters ?? 100) }}" class="h-11 w-full rounded-xl border border-black/[0.08] bg-[#faf9f6] px-4 text-sm"></div>
            <div class="md:col-span-2 xl:col-span-3"><label class="mb-2 block text-xs font-semibold">{{ __('sales.customers.address') }}</label><textarea name="address" rows="3" class="w-full rounded-xl border border-black/[0.08] bg-[#faf9f6] px-4 py-3 text-sm">{{ old('address', $editing?->address) }}</textarea></div>
            <div class="md:col-span-2 xl:col-span-3"><label class="mb-2 block text-xs font-semibold">{{ __('sales.common.notes') }}</label><textarea name="notes" rows="3" class="w-full rounded-xl border border-black/[0.08] bg-[#faf9f6] px-4 py-3 text-sm">{{ old('notes', $editing?->notes) }}</textarea></div>
            <label class="flex items-center gap-3 rounded-xl border border-black/[0.06] bg-[#faf9f6] p-4"><input type="checkbox" name="geofence_enabled" value="1" class="size-4" @checked(old('geofence_enabled', $editing?->geofence_enabled ?? true))><span class="text-sm font-semibold">{{ __('sales.customers.geofence_enabled') }}</span></label>
            <label class="flex items-center gap-3 rounded-xl border border-black/[0.06] bg-[#faf9f6] p-4"><input type="checkbox" name="is_active" value="1" class="size-4" @checked(old('is_active', $editing?->is_active ?? true))><span class="text-sm font-semibold">{{ __('sales.common.active') }}</span></label>
        </div>
        <div class="mt-6 flex justify-end gap-2 border-t border-black/[0.06] pt-5"><a href="{{ route('sales.customers.index', ['organization' => $organization->slug]) }}" class="inline-flex h-11 items-center rounded-xl border border-black/[0.08] px-5 text-sm font-semibold">{{ __('common.cancel') }}</a><button class="h-11 rounded-xl bg-[#1d1d1b] px-6 text-sm font-semibold text-white">{{ __('common.save') }}</button></div>
    </form>
</section>
@endif

<section class="mt-5 rounded-2xl border border-black/[0.06] bg-white p-5">
    <form method="GET" class="grid gap-3 md:grid-cols-[1fr_220px_180px_auto]">
        <input name="search" value="{{ $search }}" placeholder="{{ __('sales.customers.search_placeholder') }}" class="h-11 rounded-xl border border-black/[0.08] bg-[#faf9f6] px-4 text-sm">
        <select name="area_id" class="h-11 rounded-xl border border-black/[0.08] bg-[#faf9f6] px-4 text-sm"><option value="">{{ __('sales.customers.all_areas') }}</option>@foreach($areas as $area)<option value="{{ $area->id }}" @selected((string) $areaId === (string) $area->id)>{{ $area->name }}</option>@endforeach</select>
        <select name="status" class="h-11 rounded-xl border border-black/[0.08] bg-[#faf9f6] px-4 text-sm"><option value="">{{ __('sales.common.all_status') }}</option><option value="active" @selected($status === 'active')>{{ __('sales.common.active') }}</option><option value="inactive" @selected($status === 'inactive')>{{ __('sales.common.inactive') }}</option></select>
        <button class="h-11 rounded-xl bg-[#1d1d1b] px-5 text-sm font-semibold text-white">{{ __('common.filter') }}</button>
    </form>
</section>

<section class="mt-5 overflow-hidden rounded-2xl border border-black/[0.06] bg-white">
    <div class="overflow-x-auto"><table class="w-full min-w-[1200px]">
        <thead class="border-b border-black/[0.06] bg-[#faf9f6] text-left text-xs font-semibold text-[#77746d]"><tr><th class="px-5 py-4">{{ __('sales.customers.customer') }}</th><th class="px-5 py-4">{{ __('sales.customers.location') }}</th><th class="px-5 py-4">{{ __('sales.customers.contact') }}</th><th class="px-5 py-4">{{ __('sales.customers.area') }}</th><th class="px-5 py-4">Geofence</th><th class="px-5 py-4">{{ __('common.status') }}</th><th class="px-5 py-4 text-right">{{ __('common.actions') }}</th></tr></thead>
        <tbody class="divide-y divide-black/[0.05]">
        @forelse($customers as $customer)
            <tr class="hover:bg-[#fcfbf8]">
                <td class="px-5 py-4"><div class="text-sm font-semibold">{{ $customer->name }}</div><div class="mt-1 text-xs text-[#99968e]">{{ $customer->code ?? '—' }}</div></td>
                <td class="px-5 py-4"><div class="max-w-[280px] truncate text-sm">{{ $customer->address ?? '—' }}</div><div class="mt-1 text-xs text-[#99968e]">{{ $customer->city ?? '—' }}</div></td>
                <td class="px-5 py-4 text-sm">{{ $customer->contact_name ?? '—' }}<div class="mt-1 text-xs text-[#99968e]">{{ $customer->phone ?? $customer->email ?? '—' }}</div></td>
                <td class="px-5 py-4 text-sm">{{ $customer->area?->name ?? '—' }}</td>
                <td class="px-5 py-4"><span class="inline-flex rounded-full px-3 py-1.5 text-xs font-semibold {{ $customer->geofence_enabled ? 'bg-blue-50 text-blue-700' : 'bg-slate-100 text-slate-600' }}">{{ $customer->geofence_enabled ? number_format($customer->geofence_radius_meters).' m' : __('sales.common.disabled') }}</span></td>
                <td class="px-5 py-4"><span class="inline-flex rounded-full px-3 py-1.5 text-xs font-semibold {{ $customer->is_active ? 'bg-emerald-50 text-emerald-700' : 'bg-slate-100 text-slate-600' }}">{{ $customer->is_active ? __('sales.common.active') : __('sales.common.inactive') }}</span></td>
                <td class="px-5 py-4"><div class="flex justify-end gap-2"><a href="{{ route('sales.customers.index', ['organization' => $organization->slug, 'edit' => $customer->id]) }}" class="rounded-lg border border-black/[0.08] px-3 py-2 text-xs font-semibold">{{ __('common.edit') }}</a><form method="POST" action="{{ route('sales.customers.destroy', ['organization' => $organization->slug, 'customer' => $customer->id]) }}" onsubmit="return confirm(@js(__('sales.common.confirm_delete')))" >@csrf @method('DELETE')<button class="rounded-lg border border-red-100 bg-red-50 px-3 py-2 text-xs font-semibold text-red-700">{{ __('common.delete') }}</button></form></div></td>
            </tr>
        @empty<tr><td colspan="7" class="px-6 py-16 text-center text-sm text-[#85827b]">{{ __('sales.customers.empty') }}</td></tr>@endforelse
        </tbody>
    </table></div>
    @if($customers->hasPages())<div class="border-t border-black/[0.06] px-5 py-4">{{ $customers->links() }}</div>@endif
</section>
@endsection
