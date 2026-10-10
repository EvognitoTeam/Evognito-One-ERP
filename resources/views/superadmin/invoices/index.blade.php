@extends('layouts.superadmin')

@section('title', 'Invoices')
@section('page_name', 'Invoices')

@section('content')

    @php
        $statusStyles = [
            'draft' => [
                'label' => 'Draft',
                'class' => 'bg-zinc-100 text-zinc-700',
            ],

            'issued' => [
                'label' => 'Issued',
                'class' => 'bg-blue-50 text-blue-700',
            ],

            'partial' => [
                'label' => 'Partial',
                'class' => 'bg-cyan-50 text-cyan-700',
            ],

            'overdue' => [
                'label' => 'Overdue',
                'class' => 'bg-red-50 text-red-700',
            ],

            'paid' => [
                'label' => 'Paid',
                'class' => 'bg-emerald-50 text-emerald-700',
            ],

            'canceled' => [
                'label' => 'Canceled',
                'class' => 'bg-zinc-100 text-zinc-500',
            ],
        ];
    @endphp


    {{-- ========================================================= --}}
    {{-- PAGE HEADER                                               --}}
    {{-- ========================================================= --}}

    <div class="flex flex-col gap-5
           xl:flex-row
           xl:items-end
           xl:justify-between">
        <div>
            <div
                class="text-[12px]
                   font-bold
                   uppercase
                   tracking-[.1em]
                   text-[#e8742a]">
                Commercial
            </div>

            <h1
                class="mt-2
                   text-[30px]
                   font-semibold
                   tracking-[-.045em]">
                Invoices
            </h1>

            <p
                class="mt-2
                   max-w-[650px]
                   text-[14px]
                   leading-6
                   text-[#85827b]">
                Kelola invoice subscription dan
                invoice custom yang diterbitkan
                langsung oleh Evognito.
            </p>
        </div>

        <div class="flex flex-wrap gap-2">
            <a href="{{ route('superadmin.invoices.create') }}"
                class="inline-flex h-11
                   items-center gap-2
                   rounded-xl
                   border border-black/[0.08]
                   bg-white
                   px-4
                   text-[13px]
                   font-semibold
                   text-[#292825]
                   transition
                   hover:bg-[#f6f5f1]">
                <svg class="h-4 w-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8">
                    <path d="M12 3v18" />
                    <path d="M3 12h18" />
                </svg>

                Subscription Invoice
            </a>

            <a href="{{ route('superadmin.invoices.manual.create') }}"
                class="inline-flex h-11
                   items-center gap-2
                   rounded-xl
                   bg-[#1d1d1b]
                   px-4
                   text-[13px]
                   font-semibold
                   text-white
                   transition
                   hover:bg-black">
                <svg class="h-4 w-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8">
                    <path d="M12 3v18" />
                    <path d="M3 12h18" />
                </svg>

                Custom Invoice
            </a>
        </div>
    </div>


    {{-- ========================================================= --}}
    {{-- ALERTS                                                    --}}
    {{-- ========================================================= --}}

    @if (session('success'))
        <div
            class="mt-6
               rounded-xl
               border border-emerald-200
               bg-emerald-50
               px-4 py-3
               text-[13px]
               text-emerald-700">
            {{ session('success') }}
        </div>
    @endif

    @if (session('error'))
        <div
            class="mt-6
               rounded-xl
               border border-red-200
               bg-red-50
               px-4 py-3
               text-[13px]
               text-red-700">
            {{ session('error') }}
        </div>
    @endif


    {{-- ========================================================= --}}
    {{-- SUMMARY                                                   --}}
    {{-- ========================================================= --}}

    <div class="mt-7 grid gap-3
           sm:grid-cols-2
           xl:grid-cols-4">
        <div class="rounded-2xl
               border border-black/[0.07]
               bg-white
               p-5">
            <div class="flex items-center
                   justify-between">
                <span class="text-[13px]
                       font-medium
                       text-[#77746d]">
                    Total Results
                </span>

                <div
                    class="flex h-9 w-9
                       items-center
                       justify-center
                       rounded-xl
                       bg-[#f1f0eb]">
                    <svg class="h-[18px] w-[18px]" viewBox="0 0 24 24" fill="none" stroke="currentColor"
                        stroke-width="1.8">
                        <path d="M6 2h9l5 5v15H6z" />
                        <path d="M14 2v6h6" />
                    </svg>
                </div>
            </div>

            <div
                class="mt-5
                   text-[27px]
                   font-semibold
                   tracking-[-.04em]">
                {{ number_format($invoices->total()) }}
            </div>

            <div class="mt-1
                   text-[12px]
                   text-[#99968e]">
                Invoice sesuai filter
            </div>
        </div>

        <div class="rounded-2xl
               border border-black/[0.07]
               bg-white
               p-5">
            <div class="text-[13px]
                   font-medium
                   text-[#77746d]">
                Current Page Value
            </div>

            <div
                class="mt-5
                   text-[24px]
                   font-semibold
                   tracking-[-.04em]">
                Rp
                {{ number_format($invoices->getCollection()->sum(fn($invoice) => (float) $invoice->total), 0, ',', '.') }}
            </div>

            <div class="mt-1
                   text-[12px]
                   text-[#99968e]">
                Nilai halaman yang sedang tampil
            </div>
        </div>

        <div class="rounded-2xl
               border border-black/[0.07]
               bg-white
               p-5">
            <div class="text-[13px]
                   font-medium
                   text-[#77746d]">
                Subscription
            </div>

            <div
                class="mt-5
                   text-[27px]
                   font-semibold
                   tracking-[-.04em]">
                {{ $invoices->getCollection()->where('source', 'subscription')->count() }}
            </div>

            <div class="mt-1
                   text-[12px]
                   text-[#99968e]">
                Pada halaman ini
            </div>
        </div>

        <div class="rounded-2xl
               border border-black/[0.07]
               bg-white
               p-5">
            <div class="text-[13px]
                   font-medium
                   text-[#77746d]">
                Custom Invoice
            </div>

            <div
                class="mt-5
                   text-[27px]
                   font-semibold
                   tracking-[-.04em]">
                {{ $invoices->getCollection()->where('source', 'manual')->count() }}
            </div>

            <div class="mt-1
                   text-[12px]
                   text-[#99968e]">
                Pada halaman ini
            </div>
        </div>
    </div>


    {{-- ========================================================= --}}
    {{-- FILTER                                                     --}}
    {{-- ========================================================= --}}

    <form method="GET" action="{{ route('superadmin.invoices.index') }}"
        class="mt-5
           rounded-2xl
           border border-black/[0.07]
           bg-white
           p-4">
        <div class="grid gap-3
               lg:grid-cols-[1fr_200px_190px_auto]">
            <div class="relative">
                <svg class="absolute left-3.5
                       top-1/2
                       h-4 w-4
                       -translate-y-1/2
                       text-[#aaa79f]"
                    viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                    <circle cx="11" cy="11" r="8" />

                    <path d="m21 21-4.3-4.3" />
                </svg>

                <input type="text" name="search" value="{{ $search }}"
                    placeholder="Invoice number, title, organization..."
                    class="h-11 w-full
                       rounded-xl
                       border border-black/[0.08]
                       bg-[#faf9f6]
                       pl-10 pr-4
                       text-[13px]
                       outline-none
                       focus:border-black/[0.15]
                       focus:bg-white">
            </div>

            <select name="source"
                class="h-11 w-full
                   rounded-xl
                   border border-black/[0.08]
                   bg-[#faf9f6]
                   px-3
                   text-[13px]">
                <option value="">
                    All invoice types
                </option>

                <option value="subscription" @selected($source === 'subscription')>
                    Subscription
                </option>

                <option value="manual" @selected($source === 'manual')>
                    Custom Invoice
                </option>
            </select>

            <select name="status"
                class="h-11 w-full
                   rounded-xl
                   border border-black/[0.08]
                   bg-[#faf9f6]
                   px-3
                   text-[13px]">
                <option value="">
                    All status
                </option>

                @foreach ([
            'draft' => 'Draft',
            'issued' => 'Issued',
            'partial' => 'Partial',
            'overdue' => 'Overdue',
            'paid' => 'Paid',
            'canceled' => 'Canceled',
        ] as $value => $label)
                    <option value="{{ $value }}" @selected($status === $value)>
                        {{ $label }}
                    </option>
                @endforeach
            </select>

            <div class="flex gap-2">
                <button type="submit"
                    class="h-11
                       rounded-xl
                       bg-[#1d1d1b]
                       px-5
                       text-[13px]
                       font-semibold
                       text-white">
                    Filter
                </button>

                <a href="{{ route('superadmin.invoices.index') }}"
                    class="flex h-11
                       items-center
                       rounded-xl
                       border border-black/[0.08]
                       bg-white
                       px-4
                       text-[13px]
                       font-semibold">
                    Reset
                </a>
            </div>
        </div>
    </form>


    {{-- ========================================================= --}}
    {{-- TABLE                                                      --}}
    {{-- ========================================================= --}}

    <div
        class="mt-5
           overflow-hidden
           rounded-2xl
           border border-black/[0.07]
           bg-white">
        <div
            class="flex items-center
               justify-between
               border-b
               border-black/[0.06]
               px-5 py-4">
            <div>
                <h2 class="text-[15px]
                       font-semibold">
                    Invoice Directory
                </h2>

                <p class="mt-1
                       text-[12px]
                       text-[#99968e]">
                    Subscription dan custom billing
                    dalam satu invoice ledger.
                </p>
            </div>
        </div>

        <div class="overflow-x-auto">
            <table class="w-full
                   min-w-[1180px]">
                <thead>
                    <tr
                        class="border-b
                           border-black/[0.06]
                           bg-[#faf9f6]">
                        <th
                            class="px-5 py-3.5
                               text-left
                               text-[12px]
                               font-semibold
                               text-[#77746d]">
                            Invoice
                        </th>

                        <th
                            class="px-5 py-3.5
                               text-left
                               text-[12px]
                               font-semibold
                               text-[#77746d]">
                            Organization
                        </th>

                        <th
                            class="px-5 py-3.5
                               text-left
                               text-[12px]
                               font-semibold
                               text-[#77746d]">
                            Billing Period
                        </th>

                        <th
                            class="px-5 py-3.5
                               text-left
                               text-[12px]
                               font-semibold
                               text-[#77746d]">
                            Due Date
                        </th>

                        <th
                            class="px-5 py-3.5
                               text-right
                               text-[12px]
                               font-semibold
                               text-[#77746d]">
                            Total
                        </th>

                        <th
                            class="px-5 py-3.5
                               text-left
                               text-[12px]
                               font-semibold
                               text-[#77746d]">
                            Status
                        </th>

                        <th
                            class="px-5 py-3.5
                               text-right
                               text-[12px]
                               font-semibold
                               text-[#77746d]">
                        </th>
                    </tr>
                </thead>

                <tbody>
                    @forelse($invoices
                        as $invoice)
                        @php
                            $invoiceStatus = $statusStyles[$invoice->status] ?? [
                                'label' => ucfirst($invoice->status),

                                'class' => 'bg-zinc-100 text-zinc-600',
                            ];

                            $isManual = $invoice->source === 'manual';
                        @endphp

                        <tr
                            class="border-b
                               border-black/[0.05]
                               last:border-0
                               transition
                               hover:bg-[#faf9f6]">
                            <td class="px-5 py-4">
                                <a href="{{ route('superadmin.invoices.show', $invoice) }}"
                                    class="text-[13px]
                                       font-semibold
                                       text-[#292825]
                                       hover:underline">
                                    {{ $invoice->invoice_number }}
                                </a>

                                <div
                                    class="mt-2
                                       flex
                                       flex-wrap
                                       items-center
                                       gap-2">
                                    <span
                                        class="rounded-full
                                           px-2.5 py-1
                                           text-[10px]
                                           font-semibold
                                           {{ $isManual ? 'bg-orange-50 text-orange-700' : 'bg-blue-50 text-blue-700' }}">
                                        {{ $isManual ? 'Custom' : 'Subscription' }}
                                    </span>

                                    @if ($invoice->title)
                                        <span
                                            class="max-w-[220px]
                                               truncate
                                               text-[11px]
                                               text-[#99968e]">
                                            {{ $invoice->title }}
                                        </span>
                                    @endif
                                </div>
                            </td>

                            <td class="px-5 py-4">
                                <div class="text-[13px]
                                       font-semibold">
                                    {{ $invoice->organization?->name ?? '—' }}
                                </div>

                                @if ($invoice->organization?->slug)
                                    <div
                                        class="mt-1
                                           text-[11px]
                                           text-[#99968e]">
                                        /{{ $invoice->organization->slug }}
                                    </div>
                                @endif
                            </td>

                            <td class="px-5 py-4">
                                @if ($invoice->period_start || $invoice->period_end)
                                    <div class="text-[12px]">
                                        {{ $invoice->period_start?->format('d M Y') ?? '—' }}
                                    </div>

                                    <div
                                        class="mt-1
                                           text-[11px]
                                           text-[#99968e]">
                                        sampai
                                        {{ $invoice->period_end?->format('d M Y') ?? '—' }}
                                    </div>
                                @else
                                    <span class="text-[12px]
                                           text-[#aaa79f]">
                                        No service period
                                    </span>
                                @endif
                            </td>

                            <td class="px-5 py-4">
                                @if ($invoice->due_at)
                                    <div class="text-[12px]
                                           font-medium">
                                        {{ $invoice->due_at->format('d M Y') }}
                                    </div>

                                    <div
                                        class="mt-1
                                           text-[11px]
                                           text-[#99968e]">
                                        {{ $invoice->due_at->format('H:i') }}
                                        WIB
                                    </div>
                                @else
                                    <span class="text-[12px]
                                           text-[#aaa79f]">
                                        —
                                    </span>
                                @endif
                            </td>

                            <td class="px-5 py-4
                                   text-right">
                                <div class="text-[14px]
                                       font-semibold">
                                    Rp
                                    {{ number_format($invoice->total, 0, ',', '.') }}
                                </div>

                                <div
                                    class="mt-1
                                       text-[11px]
                                       text-[#99968e]">
                                    {{ $invoice->currency }}
                                </div>
                            </td>

                            <td class="px-5 py-4">
                                <span
                                    class="inline-flex
                                       rounded-full
                                       px-3 py-1.5
                                       text-[11px]
                                       font-semibold
                                       {{ $invoiceStatus['class'] }}">
                                    {{ $invoiceStatus['label'] }}
                                </span>
                            </td>

                            <td class="px-5 py-4
                                   text-right">
                                <a href="{{ route('superadmin.invoices.show', $invoice) }}"
                                    class="inline-flex
                                       items-center gap-1
                                       text-[12px]
                                       font-semibold">
                                    Manage

                                    <span>
                                        →
                                    </span>
                                </a>
                            </td>
                        </tr>

                    @empty
                        <tr>
                            <td colspan="7" class="px-6 py-20
                                   text-center">
                                <div
                                    class="mx-auto
                                       flex h-14 w-14
                                       items-center
                                       justify-center
                                       rounded-2xl
                                       bg-[#f1f0eb]">
                                    <svg class="h-6 w-6
                                           text-[#77746d]"
                                        viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.7">
                                        <path d="M6 2h9l5 5v15H6z" />
                                        <path d="M14 2v6h6" />
                                        <path d="M9 13h6" />
                                        <path d="M9 17h6" />
                                    </svg>
                                </div>

                                <div
                                    class="mt-4
                                       text-[15px]
                                       font-semibold">
                                    Belum ada invoice
                                </div>

                                <p
                                    class="mx-auto mt-2
                                       max-w-[430px]
                                       text-[13px]
                                       leading-6
                                       text-[#99968e]">
                                    Generate subscription invoice
                                    atau buat custom invoice
                                    untuk organization.
                                </p>

                                <div
                                    class="mt-5
                                       flex
                                       justify-center
                                       gap-2">
                                    <a href="{{ route('superadmin.invoices.create') }}"
                                        class="flex h-10
                                           items-center
                                           rounded-xl
                                           border border-black/[0.08]
                                           px-4
                                           text-[12px]
                                           font-semibold">
                                        Subscription Invoice
                                    </a>

                                    <a href="{{ route('superadmin.invoices.manual.create') }}"
                                        class="flex h-10
                                           items-center
                                           rounded-xl
                                           bg-[#1d1d1b]
                                           px-4
                                           text-[12px]
                                           font-semibold
                                           text-white">
                                        Custom Invoice
                                    </a>
                                </div>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @if ($invoices->hasPages())
            <div class="border-t
                   border-black/[0.06]
                   px-5 py-4">
                {{ $invoices->links() }}
            </div>
        @endif
    </div>

@endsection
