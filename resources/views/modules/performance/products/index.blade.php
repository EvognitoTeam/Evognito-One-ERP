@extends('layouts.workspace')
@section('title', __('performance.products.title').' - Performance')
@section('content')
@include('modules.performance.partials.header', [
    'title' => __('performance.products.title'),
    'description' => __('performance.products.description'),
])

@if(session('success'))
    <div class="mt-5 rounded-xl border border-emerald-100 bg-emerald-50 px-4 py-3 text-sm text-emerald-700">{{ session('success') }}</div>
@endif

<section class="mt-6 grid gap-5 xl:grid-cols-[380px_minmax(0,1fr)]">
    <div class="space-y-5">
        <form method="POST" action="{{ route('performance.products.store', ['organization' => $organization->slug]) }}"
            class="rounded-2xl border border-black/[0.06] bg-white p-5">
            @csrf
            <h2 class="text-lg font-semibold">{{ __('performance.products.add_product') }}</h2>
            <div class="mt-5 space-y-4">
                <div><label class="mb-2 block text-xs font-semibold">{{ __('performance.fields.code') }}</label><input name="code" class="h-11 w-full rounded-xl border border-black/[0.08] px-4 text-sm"></div>
                <div><label class="mb-2 block text-xs font-semibold">{{ __('performance.fields.name') }}</label><input name="name" required class="h-11 w-full rounded-xl border border-black/[0.08] px-4 text-sm"></div>
                <div><label class="mb-2 block text-xs font-semibold">{{ __('performance.fields.description') }}</label><textarea name="description" rows="3" class="w-full rounded-xl border border-black/[0.08] px-4 py-3 text-sm"></textarea></div>
                <button class="h-11 w-full rounded-xl bg-purple-700 text-sm font-semibold text-white">{{ __('performance.actions.save_product') }}</button>
            </div>
        </form>

        @if($products->isNotEmpty())
            <form method="POST"
                action="{{ route('performance.products.employees.store', ['organization' => $organization->slug, 'product' => $products->first()->id]) }}"
                id="employee-product-form" class="rounded-2xl border border-black/[0.06] bg-white p-5">
                @csrf
                <h2 class="text-lg font-semibold">{{ __('performance.products.assign_employee') }}</h2>
                <p class="mt-2 text-sm leading-6 text-[#85827b]">{{ __('performance.products.assign_employee_help') }}</p>
                <div class="mt-5 space-y-4">
                    <div>
                        <label class="mb-2 block text-xs font-semibold">{{ __('performance.products.product') }}</label>
                        <select class="h-11 w-full rounded-xl border border-black/[0.08] px-4 text-sm"
                            onchange="document.getElementById('employee-product-form').action='{{ url('/'.$organization->slug.'/performance/products') }}/'+this.value+'/employees';">
                            @foreach($products as $product)<option value="{{ $product->id }}">{{ $product->name }}</option>@endforeach
                        </select>
                    </div>
                    <div>
                        <label class="mb-2 block text-xs font-semibold">{{ __('performance.products.employee') }}</label>
                        <select name="employee_id" required class="h-11 w-full rounded-xl border border-black/[0.08] px-4 text-sm">
                            <option value="">{{ __('performance.common.select') }}</option>
                            @foreach($employees as $employee)
                                <option value="{{ $employee->id }}">{{ $employee->name ?? $employee->user?->name ?? ('Employee #'.$employee->id) }}@if($employee->position?->name) — {{ $employee->position->name }}@endif</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="grid gap-3 sm:grid-cols-2">
                        <div><label class="mb-2 block text-xs font-semibold">{{ __('performance.fields.effective_from') }}</label><input type="date" name="effective_from" class="h-11 w-full rounded-xl border border-black/[0.08] px-3 text-sm"></div>
                        <div><label class="mb-2 block text-xs font-semibold">{{ __('performance.fields.effective_until') }}</label><input type="date" name="effective_until" class="h-11 w-full rounded-xl border border-black/[0.08] px-3 text-sm"></div>
                    </div>
                    <label class="flex items-center gap-3 text-sm"><input type="checkbox" name="is_primary" value="1">{{ __('performance.products.primary_product') }}</label>
                    <button class="h-11 w-full rounded-xl bg-[#1d1d1b] text-sm font-semibold text-white">{{ __('performance.products.assign') }}</button>
                </div>
            </form>
        @endif
    </div>

    <div class="space-y-5">
        <div class="overflow-hidden rounded-2xl border border-black/[0.06] bg-white">
            <div class="border-b border-black/[0.06] px-5 py-4"><h2 class="text-lg font-semibold">{{ __('performance.products.product_list') }}</h2></div>
            <div class="overflow-x-auto">
                <table class="w-full min-w-[720px]">
                    <thead class="bg-[#faf9f6] text-left text-xs text-[#77746d]"><tr><th class="px-5 py-4">{{ __('performance.fields.code') }}</th><th class="px-5 py-4">{{ __('performance.fields.name') }}</th><th class="px-5 py-4">{{ __('performance.products.employees') }}</th><th class="px-5 py-4">{{ __('performance.products.kpis') }}</th></tr></thead>
                    <tbody class="divide-y divide-black/[0.05]">
                        @forelse($products as $product)
                            <tr><td class="px-5 py-4 text-sm">{{ $product->code ?: '—' }}</td><td class="px-5 py-4"><div class="text-sm font-semibold">{{ $product->name }}</div><div class="mt-1 text-xs text-[#85827b]">{{ $product->description }}</div></td><td class="px-5 py-4 text-sm">{{ $product->employees_count }}</td><td class="px-5 py-4 text-sm">{{ $product->kpi_assignments_count }}</td></tr>
                        @empty
                            <tr><td colspan="4" class="px-6 py-12 text-center text-sm text-[#85827b]">{{ __('performance.products.empty') }}</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>

        <div class="overflow-hidden rounded-2xl border border-black/[0.06] bg-white">
            <div class="border-b border-black/[0.06] px-5 py-4"><h2 class="text-lg font-semibold">{{ __('performance.products.employee_assignments') }}</h2></div>
            <div class="overflow-x-auto">
                <table class="w-full min-w-[880px]">
                    <thead class="bg-[#faf9f6] text-left text-xs text-[#77746d]"><tr><th class="px-5 py-4">{{ __('performance.products.employee') }}</th><th class="px-5 py-4">{{ __('performance.products.product') }}</th><th class="px-5 py-4">{{ __('performance.fields.period') }}</th><th class="px-5 py-4">{{ __('performance.products.primary') }}</th><th class="px-5 py-4 text-right">{{ __('performance.fields.actions') }}</th></tr></thead>
                    <tbody class="divide-y divide-black/[0.05]">
                        @forelse($assignments as $assignment)
                            <tr>
                                <td class="px-5 py-4"><div class="text-sm font-semibold">{{ $assignment->employee?->name ?? $assignment->employee?->user?->name ?? ('Employee #'.$assignment->employee_id) }}</div><div class="mt-1 text-xs text-[#85827b]">{{ $assignment->employee?->position?->name }}</div></td>
                                <td class="px-5 py-4 text-sm font-semibold">{{ $assignment->product?->name }}</td>
                                <td class="px-5 py-4 text-sm">{{ $assignment->effective_from?->format('d M Y') ?: '—' }} – {{ $assignment->effective_until?->format('d M Y') ?: '∞' }}</td>
                                <td class="px-5 py-4">@if($assignment->is_primary)<span class="rounded-full bg-purple-50 px-3 py-1.5 text-xs font-semibold text-purple-700">{{ __('performance.products.primary') }}</span>@else<span class="text-sm text-[#85827b]">—</span>@endif</td>
                                <td class="px-5 py-4 text-right"><form method="POST" action="{{ route('performance.products.employees.destroy', ['organization' => $organization->slug, 'product' => $assignment->product_id, 'employee' => $assignment->employee_id]) }}">@csrf @method('DELETE')<button class="rounded-lg bg-red-50 px-3 py-2 text-xs font-semibold text-red-700">{{ __('performance.actions.remove') }}</button></form></td>
                            </tr>
                        @empty
                            <tr><td colspan="5" class="px-6 py-12 text-center text-sm text-[#85827b]">{{ __('performance.products.no_assignments') }}</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</section>
@endsection
