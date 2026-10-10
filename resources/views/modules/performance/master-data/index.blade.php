@extends('layouts.workspace')

@section('title', __('performance.import.title').' - Performance')

@section('content')

@include('modules.performance.partials.header', [
    'title' => __('performance.import.title'),
    'description' => __('performance.import.description'),
])

@if(session('success'))
    <div class="mt-5 rounded-xl border border-emerald-100 bg-emerald-50 px-4 py-3 text-sm text-emerald-700">
        {{ session('success') }}
    </div>
@endif

@if($errors->any())
    <div class="mt-5 rounded-xl border border-red-100 bg-red-50 px-4 py-3 text-sm text-red-700">
        <ul class="list-disc space-y-1 pl-5">
            @foreach($errors->all() as $error)
                <li>{{ $error }}</li>
            @endforeach
        </ul>
    </div>
@endif

<section class="mt-6 grid gap-5 xl:grid-cols-[420px_minmax(0,1fr)]">
    <form
        method="POST"
        action="{{ route('performance.master-data.store', ['organization' => $organization->slug]) }}"
        enctype="multipart/form-data"
        class="h-fit rounded-2xl border border-purple-100 bg-purple-50/50 p-6"
    >
        @csrf

        <div class="flex items-start justify-between gap-4">
            <div>
                <div class="text-xs font-bold uppercase tracking-[0.08em] text-purple-700">
                    {{ __('performance.import.master_data') }}
                </div>
                <h2 class="mt-2 text-lg font-semibold text-[#252421]">
                    {{ __('performance.import.upload_title') }}
                </h2>
            </div>

            <span class="rounded-full bg-white px-3 py-1.5 text-xs font-semibold text-purple-700">
                XLSX
            </span>
        </div>

        <p class="mt-3 text-sm leading-6 text-[#6f6b65]">
            {{ __('performance.import.header_detection_help') }}
        </p>

        <div class="mt-5 rounded-xl border border-purple-100 bg-white p-4">
            <div class="text-xs font-semibold text-[#55524c]">
                {{ __('performance.import.required_headers') }}
            </div>

            <div class="mt-3 flex flex-wrap gap-2">
                @foreach([
                    'NIK Karyawan',
                    'Nama',
                    'Nama Perusahaan',
                    'Kode Perusahaan',
                    'Jabatan',
                    'Level',
                    'Atasan Level 1',
                    'Atasan Level 2',
                    'Produk',
                    'Home Based',
                    'Status Karyawan',
                ] as $header)
                    <span class="rounded-full bg-[#f5f3ff] px-2.5 py-1 text-xs font-semibold text-purple-700">
                        {{ $header }}
                    </span>
                @endforeach
            </div>

            <p class="mt-4 text-xs leading-5 text-[#77746d]">
                {{ __('performance.import.sheet_name_not_required') }}
            </p>
        </div>

        <div class="mt-5">
            <label class="mb-2 block text-xs font-semibold text-[#55524c]">
                {{ __('performance.import.file') }}
            </label>

            <input
                type="file"
                name="file"
                accept=".xlsx,application/vnd.openxmlformats-officedocument.spreadsheetml.sheet"
                required
                class="block w-full rounded-xl border border-purple-100 bg-white px-3 py-3 text-sm file:mr-3 file:rounded-lg file:border-0 file:bg-purple-100 file:px-3 file:py-2 file:text-xs file:font-semibold file:text-purple-800"
            >
        </div>

        <button
            type="submit"
            class="mt-5 h-11 w-full rounded-xl bg-purple-700 px-5 text-sm font-semibold text-white transition hover:bg-purple-800"
        >
            {{ __('performance.import.import_button') }}
        </button>

        <p class="mt-3 text-xs leading-5 text-[#77746d]">
            {{ __('performance.import.staging_note') }}
        </p>
    </form>

    <div class="overflow-hidden rounded-2xl border border-black/[0.06] bg-white">
        <div class="border-b border-black/[0.06] px-5 py-4">
            <h2 class="text-lg font-semibold">{{ __('performance.import.history') }}</h2>
            <p class="mt-1 text-sm leading-6 text-[#85827b]">{{ __('performance.import.history_help') }}</p>
        </div>

        <div class="overflow-x-auto">
            <table class="w-full min-w-[980px]">
                <thead class="bg-[#faf9f6] text-left text-xs text-[#77746d]">
                    <tr>
                        <th class="px-5 py-4">{{ __('performance.import.filename') }}</th>
                        <th class="px-5 py-4">{{ __('performance.import.detected_sheet') }}</th>
                        <th class="px-5 py-4">{{ __('performance.import.rows') }}</th>
                        <th class="px-5 py-4">{{ __('performance.import.products_found') }}</th>
                        <th class="px-5 py-4">{{ __('performance.import.status_label') }}</th>
                        <th class="px-5 py-4 text-right">{{ __('performance.fields.actions') }}</th>
                    </tr>
                </thead>

                <tbody class="divide-y divide-black/[0.05]">
                    @forelse($imports as $import)
                        <tr>
                            <td class="px-5 py-4">
                                <div class="text-sm font-semibold text-[#33312d]">{{ $import->original_filename }}</div>
                                <div class="mt-1 text-xs text-[#85827b]">{{ $import->created_at?->format('d M Y H:i') }}</div>
                            </td>
                            <td class="px-5 py-4 text-sm">{{ $import->sheet_name ?: '—' }}</td>
                            <td class="px-5 py-4 text-sm">{{ number_format((int) $import->valid_employee_rows) }}</td>
                            <td class="px-5 py-4 text-sm">{{ number_format((int) $import->products_found) }}</td>
                            <td class="px-5 py-4">
                                <span class="rounded-full px-3 py-1.5 text-xs font-semibold {{ $import->status === 'completed' ? 'bg-emerald-50 text-emerald-700' : ($import->status === 'failed' ? 'bg-red-50 text-red-700' : 'bg-amber-50 text-amber-700') }}">
                                    {{ __('performance.import.status.'.$import->status) }}
                                </span>
                            </td>
                            <td class="px-5 py-4 text-right">
                                <a
                                    href="{{ route('performance.master-data.show', ['organization' => $organization->slug, 'import' => $import->id]) }}"
                                    class="rounded-lg bg-purple-50 px-3 py-2 text-xs font-semibold text-purple-700"
                                >
                                    {{ __('performance.import.open_detail') }}
                                </a>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" class="px-6 py-14 text-center text-sm text-[#85827b]">
                                {{ __('performance.import.no_history') }}
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @if($imports->hasPages())
            <div class="border-t border-black/[0.06] px-5 py-4">
                {{ $imports->links() }}
            </div>
        @endif
    </div>
</section>

@endsection
