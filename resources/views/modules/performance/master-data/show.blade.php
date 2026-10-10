@extends('layouts.workspace')

@section('title', __('performance.import.detail_title').' - Performance')

@section('content')

@include('modules.performance.partials.header', [
    'title' => __('performance.import.detail_title'),
    'description' => $import->original_filename,
])

<div class="mt-6 flex flex-wrap items-center justify-between gap-3">
    <a
        href="{{ route('performance.master-data.index', ['organization' => $organization->slug]) }}"
        class="inline-flex h-10 items-center rounded-xl border border-black/[0.08] bg-white px-4 text-sm font-semibold text-[#55524c]"
    >
        ← {{ __('performance.import.back_master_data') }}
    </a>

    <span class="rounded-full px-3 py-1.5 text-xs font-semibold {{ $import->status === 'completed' ? 'bg-emerald-50 text-emerald-700' : ($import->status === 'failed' ? 'bg-red-50 text-red-700' : 'bg-amber-50 text-amber-700') }}">
        {{ __('performance.import.status.'.$import->status) }}
    </span>
</div>

<section class="mt-5 grid gap-4 sm:grid-cols-2 xl:grid-cols-6">
    @foreach([
        [__('performance.import.valid_rows'), $import->valid_employee_rows],
        [__('performance.import.blank_rows'), $import->blank_rows],
        [__('performance.import.products_found'), $import->products_found],
        [__('performance.import.products_created'), $import->products_created],
        [__('performance.import.rows_with_product'), $import->rows_with_product],
        [__('performance.import.rows_without_product'), $import->rows_without_product],
    ] as [$label, $value])
        <article class="rounded-2xl border border-black/[0.06] bg-white p-4">
            <div class="text-xs leading-5 text-[#85827b]">{{ $label }}</div>
            <div class="mt-2 text-2xl font-semibold text-[#252421]">{{ number_format((int) $value) }}</div>
        </article>
    @endforeach
</section>

<section class="mt-5 grid gap-5 xl:grid-cols-3">
    <div class="rounded-2xl border border-purple-100 bg-purple-50/50 p-5">
        <div class="text-xs font-semibold text-purple-700">{{ __('performance.import.detected_sheet') }}</div>
        <div class="mt-2 text-lg font-semibold text-purple-900">{{ $import->sheet_name ?: '—' }}</div>
        <p class="mt-2 text-sm leading-6 text-purple-800">{{ __('performance.import.detected_sheet_help') }}</p>
    </div>

    <div class="rounded-2xl border border-purple-100 bg-purple-50/50 p-5">
        <h2 class="text-lg font-semibold text-purple-900">{{ __('performance.import.staging_ready') }}</h2>
        <p class="mt-2 text-sm leading-6 text-purple-800">{{ __('performance.import.staging_ready_help') }}</p>
    </div>

    <div class="rounded-2xl border border-amber-100 bg-amber-50/60 p-5">
        <h2 class="text-lg font-semibold text-amber-900">{{ __('performance.import.supervisor_review') }}</h2>
        <div class="mt-3 grid gap-3 sm:grid-cols-2">
            <div>
                <div class="text-xs text-amber-700">{{ __('performance.import.unresolved_l1') }}</div>
                <div class="mt-1 text-xl font-semibold text-amber-900">{{ $import->unresolved_supervisor_level1 }}</div>
            </div>
            <div>
                <div class="text-xs text-amber-700">{{ __('performance.import.unresolved_l2') }}</div>
                <div class="mt-1 text-xl font-semibold text-amber-900">{{ $import->unresolved_supervisor_level2 }}</div>
            </div>
        </div>
    </div>
</section>

@if($import->error_message)
    <div class="mt-5 rounded-xl border border-red-100 bg-red-50 px-4 py-3 text-sm text-red-700">
        {{ $import->error_message }}
    </div>
@endif

<section class="mt-5 overflow-hidden rounded-2xl border border-black/[0.06] bg-white">
    <div class="border-b border-black/[0.06] px-5 py-4">
        <h2 class="text-lg font-semibold">{{ __('performance.import.staged_rows') }}</h2>
        <p class="mt-1 text-sm leading-6 text-[#85827b]">{{ __('performance.import.staged_rows_help') }}</p>
    </div>

    <div class="overflow-x-auto">
        <table class="w-full min-w-[1500px]">
            <thead class="bg-[#faf9f6] text-left text-xs text-[#77746d]">
                <tr>
                    <th class="px-4 py-4">#</th>
                    <th class="px-4 py-4">NIK</th>
                    <th class="px-4 py-4">{{ __('performance.import.employee_name') }}</th>
                    <th class="px-4 py-4">{{ __('performance.import.company') }}</th>
                    <th class="px-4 py-4">{{ __('performance.import.position') }}</th>
                    <th class="px-4 py-4">{{ __('performance.import.level1') }}</th>
                    <th class="px-4 py-4">{{ __('performance.import.level2') }}</th>
                    <th class="px-4 py-4">{{ __('performance.products.product') }}</th>
                    <th class="px-4 py-4">Home Based</th>
                    <th class="px-4 py-4">{{ __('performance.import.employment_status') }}</th>
                    <th class="px-4 py-4">{{ __('performance.import.warning') }}</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-black/[0.05]">
                @forelse($rows as $row)
                    <tr>
                        <td class="px-4 py-4 text-sm">{{ $row->source_row_number }}</td>
                        <td class="px-4 py-4 text-sm font-medium">{{ $row->employee_number ?: '—' }}</td>
                        <td class="px-4 py-4 text-sm font-semibold">{{ $row->employee_name ?: '—' }}</td>
                        <td class="px-4 py-4 text-sm">{{ $row->company_name ?: '—' }}</td>
                        <td class="px-4 py-4 text-sm">{{ $row->position_name ?: '—' }}</td>
                        <td class="px-4 py-4 text-sm">{{ $row->supervisor_level1_raw ?: '—' }}</td>
                        <td class="px-4 py-4 text-sm">{{ $row->supervisor_level2_raw ?: '—' }}</td>
                        <td class="px-4 py-4">
                            @if($row->product_names)
                                <div class="flex max-w-[280px] flex-wrap gap-1.5">
                                    @foreach($row->product_names as $productName)
                                        <span class="rounded-full bg-purple-50 px-2.5 py-1 text-xs font-semibold text-purple-700">{{ $productName }}</span>
                                    @endforeach
                                </div>
                            @else
                                <span class="text-sm text-[#99968e]">—</span>
                            @endif
                        </td>
                        <td class="px-4 py-4 text-sm">{{ $row->home_based ?: '—' }}</td>
                        <td class="px-4 py-4 text-sm">{{ $row->employment_status ?: '—' }}</td>
                        <td class="px-4 py-4">
                            @if(!empty($row->warnings))
                                <span class="rounded-full bg-amber-50 px-2.5 py-1 text-xs font-semibold text-amber-700">
                                    {{ count($row->warnings) }}
                                </span>
                            @else
                                <span class="rounded-full bg-emerald-50 px-2.5 py-1 text-xs font-semibold text-emerald-700">OK</span>
                            @endif
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="11" class="px-6 py-12 text-center text-sm text-[#85827b]">
                            {{ __('performance.import.no_rows') }}
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    @if($rows->hasPages())
        <div class="border-t border-black/[0.06] px-5 py-4">
            {{ $rows->links() }}
        </div>
    @endif
</section>

@endsection
