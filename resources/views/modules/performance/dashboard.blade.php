@extends('layouts.workspace')
@section('title', __('performance.overview.title').' - Performance')
@section('content')
@include('modules.performance.partials.header', [
    'title' => __('performance.overview.title'),
    'description' => __('performance.overview.description'),
])

<section class="mt-6 grid gap-4 sm:grid-cols-2 xl:grid-cols-4">
    @foreach([
        [__('performance.overview.products'), $stats['products'] ?? 0, 'text-purple-700'],
        [__('performance.overview.kpis'), $stats['kpis'] ?? 0, 'text-blue-700'],
        [__('performance.overview.kpi_product_assignments'), $stats['assignments'] ?? 0, 'text-orange-700'],
        [__('performance.overview.employee_products'), $stats['employeeProducts'] ?? 0, 'text-emerald-700'],
    ] as [$label, $value, $tone])
        <article class="rounded-2xl border border-black/[0.06] bg-white p-5">
            <div class="text-xs font-medium text-[#85827b]">{{ $label }}</div>
            <div class="mt-3 text-2xl font-semibold {{ $tone }}">{{ number_format((int) $value) }}</div>
        </article>
    @endforeach
</section>

<section class="mt-5 rounded-2xl border border-black/[0.06] bg-white">
    <div class="border-b border-black/[0.06] px-5 py-4">
        <h2 class="text-lg font-semibold">{{ __('performance.overview.product_summary') }}</h2>
        <p class="mt-1 text-sm leading-6 text-[#85827b]">{{ __('performance.overview.product_summary_help') }}</p>
    </div>
    <div class="overflow-x-auto">
        <table class="w-full min-w-[760px]">
            <thead class="bg-[#faf9f6] text-left text-xs text-[#77746d]">
                <tr>
                    <th class="px-5 py-4">{{ __('performance.fields.code') }}</th>
                    <th class="px-5 py-4">{{ __('performance.fields.name') }}</th>
                    <th class="px-5 py-4">{{ __('performance.products.employees') }}</th>
                    <th class="px-5 py-4">{{ __('performance.products.kpis') }}</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-black/[0.05]">
                @forelse($products as $product)
                    <tr>
                        <td class="px-5 py-4 text-sm">{{ $product->code ?: '—' }}</td>
                        <td class="px-5 py-4 text-sm font-semibold">{{ $product->name }}</td>
                        <td class="px-5 py-4 text-sm">{{ $product->employees_count }}</td>
                        <td class="px-5 py-4 text-sm">{{ $product->kpi_assignments_count }}</td>
                    </tr>
                @empty
                    <tr><td colspan="4" class="px-6 py-12 text-center text-sm text-[#85827b]">{{ __('performance.products.empty') }}</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
</section>
@endsection
