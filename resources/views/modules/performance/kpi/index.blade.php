@extends('layouts.workspace')
@section('title', __('performance.kpi.title').' - Performance')
@section('content')
@include('modules.performance.partials.header', [
    'title' => __('performance.kpi.title'),
    'description' => __('performance.kpi.description'),
])

@if(session('success'))
    <div class="mt-5 rounded-xl border border-emerald-100 bg-emerald-50 px-4 py-3 text-sm text-emerald-700">{{ session('success') }}</div>
@endif

<section class="mt-6 grid gap-5 xl:grid-cols-[380px_minmax(0,1fr)]">
    <form method="POST" action="{{ route('performance.kpi.store', ['organization' => $organization->slug]) }}"
        class="h-fit rounded-2xl border border-black/[0.06] bg-white p-5">
        @csrf
        <h2 class="text-lg font-semibold">{{ __('performance.kpi.add_kpi') }}</h2>
        <div class="mt-5 space-y-4">
            <div><label class="mb-2 block text-xs font-semibold">{{ __('performance.fields.code') }}</label><input name="code" class="h-11 w-full rounded-xl border border-black/[0.08] px-4 text-sm"></div>
            <div><label class="mb-2 block text-xs font-semibold">{{ __('performance.fields.name') }}</label><input name="name" required class="h-11 w-full rounded-xl border border-black/[0.08] px-4 text-sm"></div>
            <div><label class="mb-2 block text-xs font-semibold">{{ __('performance.fields.unit') }}</label><input name="unit" class="h-11 w-full rounded-xl border border-black/[0.08] px-4 text-sm"></div>
            <div><label class="mb-2 block text-xs font-semibold">{{ __('performance.fields.frequency') }}</label><select name="default_frequency" class="h-11 w-full rounded-xl border border-black/[0.08] px-4 text-sm"><option value="annual">{{ __('performance.frequency.annual') }}</option><option value="semester">{{ __('performance.frequency.semester') }}</option><option value="quarterly">{{ __('performance.frequency.quarterly') }}</option><option value="monthly">{{ __('performance.frequency.monthly') }}</option></select></div>
            <div><label class="mb-2 block text-xs font-semibold">{{ __('performance.fields.calculation_type') }}</label><select name="default_calculation_type" class="h-11 w-full rounded-xl border border-black/[0.08] px-4 text-sm"><option value="HIGHER_BETTER">{{ __('performance.calculation.HIGHER_BETTER') }}</option><option value="LOWER_BETTER">{{ __('performance.calculation.LOWER_BETTER') }}</option></select></div>
            <div><label class="mb-2 block text-xs font-semibold">{{ __('performance.fields.description') }}</label><textarea name="description" rows="3" class="w-full rounded-xl border border-black/[0.08] px-4 py-3 text-sm"></textarea></div>
            <button class="h-11 w-full rounded-xl bg-purple-700 text-sm font-semibold text-white">{{ __('performance.actions.save_kpi') }}</button>
        </div>
    </form>

    <div class="space-y-5">
        @forelse($kpis as $kpi)
            <article class="rounded-2xl border border-black/[0.06] bg-white">
                <div class="border-b border-black/[0.06] px-5 py-4">
                    <div class="text-base font-semibold">{{ $kpi->name }}</div>
                    <div class="mt-1 text-xs text-[#85827b]">{{ $kpi->code ?: '—' }} · {{ $kpi->unit ?: __('performance.common.no_unit') }}</div>
                </div>
                <div class="p-5">
                    <form method="POST" action="{{ route('performance.kpi.products.store', ['organization' => $organization->slug, 'kpi' => $kpi->id]) }}"
                        class="grid gap-4 rounded-xl bg-[#faf9f6] p-4 md:grid-cols-2 xl:grid-cols-4">
                        @csrf
                        <div><label class="mb-2 block text-xs font-semibold">{{ __('performance.products.product') }}</label><select name="product_id" required class="h-11 w-full rounded-xl border border-black/[0.08] bg-white px-3 text-sm"><option value="">{{ __('performance.common.select') }}</option>@foreach($products as $product)<option value="{{ $product->id }}">{{ $product->name }}</option>@endforeach</select></div>
                        <div><label class="mb-2 block text-xs font-semibold">{{ __('performance.fields.weight') }} (%)</label><input type="number" step="0.01" min="0" max="100" name="weight" value="0" required class="h-11 w-full rounded-xl border border-black/[0.08] bg-white px-3 text-sm"></div>
                        <div><label class="mb-2 block text-xs font-semibold">{{ __('performance.fields.target') }}</label><input type="number" step="0.01" min="0" name="target" value="0" required class="h-11 w-full rounded-xl border border-black/[0.08] bg-white px-3 text-sm"></div>
                        <div><label class="mb-2 block text-xs font-semibold">{{ __('performance.fields.target_time') }}</label><input type="number" step="0.01" min="0" name="target_time" value="0" required class="h-11 w-full rounded-xl border border-black/[0.08] bg-white px-3 text-sm"></div>
                        <div><label class="mb-2 block text-xs font-semibold">{{ __('performance.fields.frequency') }}</label><select name="frequency" class="h-11 w-full rounded-xl border border-black/[0.08] bg-white px-3 text-sm"><option value="annual">{{ __('performance.frequency.annual') }}</option><option value="semester">{{ __('performance.frequency.semester') }}</option><option value="quarterly">{{ __('performance.frequency.quarterly') }}</option><option value="monthly">{{ __('performance.frequency.monthly') }}</option></select></div>
                        <div><label class="mb-2 block text-xs font-semibold">{{ __('performance.fields.calculation_type') }}</label><select name="calculation_type" class="h-11 w-full rounded-xl border border-black/[0.08] bg-white px-3 text-sm"><option value="HIGHER_BETTER">{{ __('performance.calculation.HIGHER_BETTER') }}</option><option value="LOWER_BETTER">{{ __('performance.calculation.LOWER_BETTER') }}</option></select></div>
                        <div><label class="mb-2 block text-xs font-semibold">{{ __('performance.fields.quantity_weight') }} (%)</label><input type="number" step="0.01" min="0" max="100" name="quantity_weight" value="70" required class="h-11 w-full rounded-xl border border-black/[0.08] bg-white px-3 text-sm"></div>
                        <div><label class="mb-2 block text-xs font-semibold">{{ __('performance.fields.time_weight') }} (%)</label><input type="number" step="0.01" min="0" max="100" name="time_weight" value="30" required class="h-11 w-full rounded-xl border border-black/[0.08] bg-white px-3 text-sm"></div>
                        <div><label class="mb-2 block text-xs font-semibold">{{ __('performance.fields.max_achievement') }} (%)</label><input type="number" step="0.01" min="100" name="max_achievement" value="150" required class="h-11 w-full rounded-xl border border-black/[0.08] bg-white px-3 text-sm"></div>
                        <div class="md:col-span-2"><label class="mb-2 block text-xs font-semibold">{{ __('performance.fields.notes') }}</label><input name="notes" class="h-11 w-full rounded-xl border border-black/[0.08] bg-white px-3 text-sm"></div>
                        <div class="flex items-end"><button class="h-11 w-full rounded-xl bg-[#1d1d1b] px-4 text-sm font-semibold text-white">{{ __('performance.kpi.assign_to_product') }}</button></div>
                    </form>

                    <div class="mt-5 overflow-x-auto">
                        <table class="w-full min-w-[980px]">
                            <thead class="text-left text-xs text-[#77746d]"><tr><th class="px-3 py-3">{{ __('performance.products.product') }}</th><th class="px-3 py-3">{{ __('performance.fields.weight') }}</th><th class="px-3 py-3">{{ __('performance.fields.target') }}</th><th class="px-3 py-3">{{ __('performance.fields.target_time') }}</th><th class="px-3 py-3">{{ __('performance.fields.quantity_time_weight') }}</th><th class="px-3 py-3">{{ __('performance.fields.calculation_type') }}</th><th class="px-3 py-3 text-right">{{ __('performance.fields.actions') }}</th></tr></thead>
                            <tbody class="divide-y divide-black/[0.05]">
                                @forelse($kpi->productAssignments as $assignment)
                                    <tr>
                                        <td class="px-3 py-4 text-sm font-semibold">{{ $assignment->product?->name }}</td>
                                        <td class="px-3 py-4 text-sm">{{ $assignment->weight }}%</td>
                                        <td class="px-3 py-4 text-sm">{{ $assignment->target }}</td>
                                        <td class="px-3 py-4 text-sm">{{ $assignment->target_time }}</td>
                                        <td class="px-3 py-4 text-sm">{{ $assignment->quantity_weight }}% / {{ $assignment->time_weight }}%</td>
                                        <td class="px-3 py-4 text-sm">{{ __('performance.calculation.'.$assignment->calculation_type) }}</td>
                                        <td class="px-3 py-4 text-right"><form method="POST" action="{{ route('performance.kpi.products.destroy', ['organization' => $organization->slug, 'kpi' => $kpi->id, 'assignment' => $assignment->id]) }}">@csrf @method('DELETE')<button class="rounded-lg bg-red-50 px-3 py-2 text-xs font-semibold text-red-700">{{ __('performance.actions.remove') }}</button></form></td>
                                    </tr>
                                @empty
                                    <tr><td colspan="7" class="px-3 py-8 text-center text-sm text-[#85827b]">{{ __('performance.kpi.no_product_assignments') }}</td></tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
            </article>
        @empty
            <div class="rounded-2xl border border-black/[0.06] bg-white px-6 py-14 text-center text-sm text-[#85827b]">{{ __('performance.kpi.empty') }}</div>
        @endforelse
    </div>
</section>
@endsection
