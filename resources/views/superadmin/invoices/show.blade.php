@extends('layouts.superadmin')

@section('title', $invoice->invoice_number)

@section('page_name', 'Invoice')

@section('content')

    @php
        $isManual = $invoice->source === 'manual';

        $statusStyles = [
            'draft' => 'bg-zinc-100 text-zinc-700',

            'issued' => 'bg-blue-50 text-blue-700',

            'partial' => 'bg-cyan-50 text-cyan-700',

            'overdue' => 'bg-red-50 text-red-700',

            'paid' => 'bg-emerald-50 text-emerald-700',

            'canceled' => 'bg-zinc-100 text-zinc-500',
        ];

        $statusClass = $statusStyles[$invoice->status] ?? 'bg-zinc-100 text-zinc-600';

        $taxPercent = data_get($invoice->metadata, 'tax_percent', 0);
    @endphp


    {{-- ========================================================= --}}
    {{-- ALERTS                                                    --}}
    {{-- ========================================================= --}}

    @if (session('success'))
        <div
            class="mb-6
               rounded-xl
               border border-emerald-200
               bg-emerald-50
               p-4
               text-[13px]
               text-emerald-700">
            {{ session('success') }}
        </div>
    @endif

    @if (session('error'))
        <div
            class="mb-6
               rounded-xl
               border border-red-200
               bg-red-50
               p-4
               text-[13px]
               text-red-700">
            {{ session('error') }}
        </div>
    @endif


    {{-- ========================================================= --}}
    {{-- HEADER                                                    --}}
    {{-- ========================================================= --}}

    <div class="flex flex-col gap-5
           xl:flex-row
           xl:items-end
           xl:justify-between">
        <div>
            <a href="{{ route('superadmin.invoices.index') }}"
                class="text-[12px]
                   font-semibold
                   text-[#88857d]">
                ← Invoices
            </a>

            <div class="mt-4
                   flex flex-wrap
                   items-center
                   gap-2">
                <span
                    class="rounded-full
                       px-3 py-1.5
                       text-[11px]
                       font-semibold
                       {{ $isManual ? 'bg-orange-50 text-orange-700' : 'bg-blue-50 text-blue-700' }}">
                    {{ $isManual ? 'Custom Invoice' : 'Subscription Invoice' }}
                </span>

                <span
                    class="rounded-full
                       px-3 py-1.5
                       text-[11px]
                       font-semibold
                       {{ $statusClass }}">
                    {{ ucfirst($invoice->status) }}
                </span>
            </div>

            <h1
                class="mt-3
                   text-[30px]
                   font-semibold
                   tracking-[-.045em]">
                {{ $invoice->invoice_number }}
            </h1>

            @if ($invoice->title)
                <div
                    class="mt-1
                       text-[16px]
                       font-medium
                       text-[#4c4a45]">
                    {{ $invoice->title }}
                </div>
            @endif

            <div class="mt-2
                   text-[14px]
                   text-[#85827b]">
                {{ $invoice->organization?->name ?? 'Organization unavailable' }}
            </div>
        </div>


        {{-- ACTIONS --}}
        <div class="flex flex-wrap gap-2">
            @if ($invoice->status === 'draft')
                <form method="POST"
                    action="{{ route('superadmin.invoices.issue', $invoice) }}">
                    @csrf

                    <button type="submit"
                        class="h-10
                           rounded-xl
                           bg-[#1d1d1b]
                           px-4
                           text-[12px]
                           font-semibold
                           text-white">
                        Issue Invoice
                    </button>
                </form>
            @endif

            @if (in_array($invoice->status, ['issued', 'partial', 'overdue'], true))
                <a href="{{ route('superadmin.payments.create', [
                    'invoice' => $invoice->id,
                ]) }}"
                    class="flex h-10
                       items-center
                       rounded-xl
                       bg-emerald-600
                       px-4
                       text-[12px]
                       font-semibold
                       text-white
                       transition
                       hover:bg-emerald-700">
                    Record Payment
                </a>
            @endif

            @if (!in_array($invoice->status, ['paid', 'canceled'], true))
                <form method="POST"
                    action="{{ route('superadmin.invoices.cancel', $invoice) }}"
                    onsubmit="
                    return confirm(
                        'Batalkan invoice ini?'
                    );
                ">
                    @csrf

                    <button type="submit"
                        class="h-10
                           rounded-xl
                           border border-red-200
                           bg-red-50
                           px-4
                           text-[12px]
                           font-semibold
                           text-red-700">
                        Cancel Invoice
                    </button>
                </form>
            @endif
        </div>
    </div>


    {{-- ========================================================= --}}
    {{-- FINANCIAL SUMMARY                                         --}}
    {{-- ========================================================= --}}

    <div class="mt-8 grid gap-3
           sm:grid-cols-2
           xl:grid-cols-4">
        <div class="rounded-2xl
               border border-black/[0.07]
               bg-white
               p-5">
            <div class="text-[12px]
                   text-[#99968e]">
                Invoice Total
            </div>

            <div
                class="mt-4
                   text-[23px]
                   font-semibold
                   tracking-[-.035em]">
                Rp
                {{ number_format($invoice->total, 0, ',', '.') }}
            </div>
        </div>

        <div class="rounded-2xl
               border border-black/[0.07]
               bg-white
               p-5">
            <div class="text-[12px]
                   text-[#99968e]">
                Paid
            </div>

            <div
                class="mt-4
                   text-[23px]
                   font-semibold
                   tracking-[-.035em]
                   text-emerald-700">
                Rp
                {{ number_format($invoice->paid_amount, 0, ',', '.') }}
            </div>
        </div>

        <div class="rounded-2xl
               border border-black/[0.07]
               bg-white
               p-5">
            <div class="text-[12px]
                   text-[#99968e]">
                Outstanding
            </div>

            <div
                class="mt-4
                   text-[23px]
                   font-semibold
                   tracking-[-.035em]
                   {{ $invoice->outstanding_amount > 0 ? 'text-orange-700' : 'text-emerald-700' }}">
                Rp
                {{ number_format($invoice->outstanding_amount, 0, ',', '.') }}
            </div>
        </div>

        <div class="rounded-2xl
               border border-black/[0.07]
               bg-white
               p-5">
            <div class="text-[12px]
                   text-[#99968e]">
                Due Date
            </div>

            <div class="mt-4
                   text-[18px]
                   font-semibold">
                {{ $invoice->due_at?->format('d M Y') ?? '—' }}
            </div>

            @if ($invoice->due_at)
                <div class="mt-1
                       text-[11px]
                       text-[#99968e]">
                    {{ $invoice->due_at->format('H:i') }}
                    WIB
                </div>
            @endif
        </div>
    </div>


    {{-- ========================================================= --}}
    {{-- CONTENT                                                   --}}
    {{-- ========================================================= --}}

    <div class="mt-5 grid gap-5
           xl:grid-cols-[1fr_370px]">
        {{-- LEFT --}}
        <div class="space-y-5">

            {{-- INVOICE ITEMS --}}
            <section
                class="overflow-hidden
                   rounded-2xl
                   border border-black/[0.07]
                   bg-white">
                <div
                    class="flex items-center
                       justify-between
                       border-b
                       border-black/[0.06]
                       px-6 py-5">
                    <div>
                        <h2 class="text-[16px]
                               font-semibold">
                            Invoice Items
                        </h2>

                        <p
                            class="mt-1
                               text-[12px]
                               text-[#99968e]">
                            Rincian biaya yang masuk
                            dalam invoice.
                        </p>
                    </div>

                    <div
                        class="text-[12px]
                           font-medium
                           text-[#77746d]">
                        {{ $invoice->items->count() }}
                        item
                    </div>
                </div>

                <div class="overflow-x-auto">
                    <table class="w-full
                           min-w-[760px]">
                        <thead>
                            <tr
                                class="border-b
                                   border-black/[0.05]
                                   bg-[#faf9f6]">
                                <th
                                    class="px-6 py-3
                                       text-left
                                       text-[12px]
                                       font-semibold
                                       text-[#77746d]">
                                    Item
                                </th>

                                <th
                                    class="px-5 py-3
                                       text-right
                                       text-[12px]
                                       font-semibold
                                       text-[#77746d]">
                                    Qty
                                </th>

                                <th
                                    class="px-5 py-3
                                       text-right
                                       text-[12px]
                                       font-semibold
                                       text-[#77746d]">
                                    Unit Price
                                </th>

                                <th
                                    class="px-6 py-3
                                       text-right
                                       text-[12px]
                                       font-semibold
                                       text-[#77746d]">
                                    Amount
                                </th>
                            </tr>
                        </thead>

                        <tbody>
                            @forelse($invoice->items
                                as $item)
                                <tr
                                    class="border-b
                                       border-black/[0.05]
                                       last:border-0">
                                    <td class="px-6 py-4">
                                        <div
                                            class="text-[13px]
                                               font-semibold">
                                            {{ $item->description }}
                                        </div>

                                        <div
                                            class="mt-1
                                               flex
                                               items-center
                                               gap-2">
                                            @if ($item->code)
                                                <span
                                                    class="text-[11px]
                                                       text-[#99968e]">
                                                    {{ $item->code }}
                                                </span>
                                            @endif

                                            @if ($item->module)
                                                <span
                                                    class="rounded-full
                                                       bg-[#f1f0eb]
                                                       px-2 py-0.5
                                                       text-[10px]
                                                       font-medium">
                                                    {{ $item->module->name }}
                                                </span>
                                            @endif

                                            @if ($item->type === 'module_included')
                                                <span
                                                    class="rounded-full
                                                       bg-emerald-50
                                                       px-2 py-0.5
                                                       text-[10px]
                                                       font-medium
                                                       text-emerald-700">
                                                    Included
                                                </span>
                                            @endif
                                        </div>
                                    </td>

                                    <td
                                        class="px-5 py-4
                                           text-right
                                           text-[13px]">
                                        {{ number_format($item->quantity, 2, ',', '.') }}
                                    </td>

                                    <td
                                        class="px-5 py-4
                                           text-right
                                           text-[13px]">
                                        Rp
                                        {{ number_format($item->unit_price, 0, ',', '.') }}
                                    </td>

                                    <td
                                        class="px-6 py-4
                                           text-right
                                           text-[13px]
                                           font-semibold">
                                        Rp
                                        {{ number_format($item->amount, 0, ',', '.') }}
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="4"
                                        class="px-6 py-12
                                           text-center
                                           text-[13px]
                                           text-[#99968e]">
                                        Tidak ada invoice item.
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>


                {{-- TOTALS --}}
                <div
                    class="border-t
                       border-black/[0.06]
                       bg-[#faf9f6]
                       px-6 py-6">
                    <div class="ml-auto
                           max-w-[380px]
                           space-y-3">
                        <div
                            class="flex
                               items-center
                               justify-between
                               text-[13px]">
                            <span class="text-[#77746d]">
                                Subtotal
                            </span>

                            <span class="font-semibold">
                                Rp
                                {{ number_format($invoice->subtotal, 0, ',', '.') }}
                            </span>
                        </div>

                        @if ((float) $invoice->additional_total > 0)
                            <div
                                class="flex
                                   items-center
                                   justify-between
                                   text-[13px]">
                                <span class="text-[#77746d]">
                                    Additional
                                </span>

                                <span class="font-semibold">
                                    Rp
                                    {{ number_format($invoice->additional_total, 0, ',', '.') }}
                                </span>
                            </div>
                        @endif

                        @if ((float) $invoice->discount_total > 0)
                            <div
                                class="flex
                                   items-center
                                   justify-between
                                   text-[13px]">
                                <span class="text-[#77746d]">
                                    Discount
                                </span>

                                <span class="font-semibold
                                       text-red-600">
                                    - Rp
                                    {{ number_format($invoice->discount_total, 0, ',', '.') }}
                                </span>
                            </div>
                        @endif

                        @if ((float) $invoice->tax_total > 0)
                            <div
                                class="flex
                                   items-center
                                   justify-between
                                   text-[13px]">
                                <span class="text-[#77746d]">
                                    Tax
                                    @if ($taxPercent)
                                        ({{ number_format($taxPercent, 2, ',', '.') }}%)
                                    @endif
                                </span>

                                <span class="font-semibold">
                                    Rp
                                    {{ number_format($invoice->tax_total, 0, ',', '.') }}
                                </span>
                            </div>
                        @endif

                        <div
                            class="flex
                               items-center
                               justify-between
                               border-t
                               border-black/[0.08]
                               pt-4">
                            <span class="text-[14px]
                                   font-semibold">
                                Total
                            </span>

                            <span
                                class="text-[20px]
                                   font-semibold
                                   tracking-[-.035em]">
                                Rp
                                {{ number_format($invoice->total, 0, ',', '.') }}
                            </span>
                        </div>

                        @if ($invoice->paid_amount > 0)
                            <div
                                class="flex
                                   items-center
                                   justify-between
                                   text-[13px]">
                                <span class="text-emerald-700">
                                    Paid
                                </span>

                                <span class="font-semibold
                                       text-emerald-700">
                                    - Rp
                                    {{ number_format($invoice->paid_amount, 0, ',', '.') }}
                                </span>
                            </div>

                            <div
                                class="flex
                                   items-center
                                   justify-between
                                   text-[14px]
                                   font-semibold">
                                <span>
                                    Outstanding
                                </span>

                                <span>
                                    Rp
                                    {{ number_format($invoice->outstanding_amount, 0, ',', '.') }}
                                </span>
                            </div>
                        @endif
                    </div>
                </div>
            </section>


            {{-- PAYMENTS --}}
            <section
                class="overflow-hidden
                   rounded-2xl
                   border border-black/[0.07]
                   bg-white">
                <div
                    class="flex items-center
                       justify-between
                       border-b
                       border-black/[0.06]
                       px-6 py-5">
                    <div>
                        <h2 class="text-[16px]
                               font-semibold">
                            Payment History
                        </h2>

                        <p
                            class="mt-1
                               text-[12px]
                               text-[#99968e]">
                            Pembayaran yang terhubung
                            dengan invoice ini.
                        </p>
                    </div>

                    @if (in_array($invoice->status, ['issued', 'partial', 'overdue'], true))
                        <a href="{{ route('superadmin.payments.create', [
                            'invoice' => $invoice->id,
                        ]) }}"
                            class="flex h-9
                               items-center
                               rounded-xl
                               bg-[#1d1d1b]
                               px-3
                               text-[11px]
                               font-semibold
                               text-white">
                            + Payment
                        </a>
                    @endif
                </div>

                @forelse($invoice->payments
                    as $payment)
                    <a href="{{ route('superadmin.payments.show', $payment) }}"
                        class="flex flex-col
                           gap-3
                           border-b
                           border-black/[0.05]
                           px-6 py-4
                           last:border-0
                           transition
                           hover:bg-[#faf9f6]
                           sm:flex-row
                           sm:items-center
                           sm:justify-between">
                        <div>
                            <div class="text-[13px]
                                   font-semibold">
                                {{ $payment->payment_number }}
                            </div>

                            <div
                                class="mt-1
                                   text-[11px]
                                   text-[#99968e]">
                                {{ ucwords(str_replace('_', ' ', $payment->method)) }}

                                @if ($payment->paid_at)
                                    ·
                                    {{ $payment->paid_at->format('d M Y H:i') }}
                                    WIB
                                @endif
                            </div>
                        </div>

                        <div class="sm:text-right">
                            <div class="text-[13px]
                                   font-semibold">
                                Rp
                                {{ number_format($payment->amount, 0, ',', '.') }}
                            </div>

                            <div
                                class="mt-1
                                   text-[11px]
                                   font-medium
                                   capitalize
                                   {{ $payment->status === 'paid' ? 'text-emerald-700' : 'text-[#99968e]' }}">
                                {{ $payment->status }}
                            </div>
                        </div>
                    </a>
                @empty
                    <div class="px-6 py-12
                           text-center">
                        <div class="text-[14px]
                               font-semibold">
                            Belum ada pembayaran
                        </div>

                        <p
                            class="mt-2
                               text-[12px]
                               text-[#99968e]">
                            Payment akan tampil di sini
                            setelah dicatat.
                        </p>
                    </div>
                @endforelse
            </section>


            {{-- NOTES --}}
            @if ($invoice->notes)
                <section
                    class="rounded-2xl
                       border border-black/[0.07]
                       bg-white p-6">
                    <h2 class="text-[16px]
                           font-semibold">
                        Notes
                    </h2>

                    <p
                        class="mt-4
                           whitespace-pre-line
                           text-[13px]
                           leading-6
                           text-[#66635d]">
                        {{ $invoice->notes }}
                    </p>
                </section>
            @endif
        </div>


        {{-- ===================================================== --}}
        {{-- RIGHT SIDEBAR                                         --}}
        {{-- ===================================================== --}}

        <aside class="space-y-5">

            {{-- CUSTOMER --}}
            <section class="rounded-2xl
                   border border-black/[0.07]
                   bg-white p-6">
                <div
                    class="text-[12px]
                       font-semibold
                       uppercase
                       tracking-[.08em]
                       text-[#99968e]">
                    Bill To
                </div>

                <h2 class="mt-4
                       text-[17px]
                       font-semibold">
                    {{ $invoice->organization?->legal_name ?: $invoice->organization?->name ?: '—' }}
                </h2>

                @if ($invoice->organization?->name && $invoice->organization?->legal_name)
                    <div class="mt-1
                           text-[12px]
                           text-[#99968e]">
                        {{ $invoice->organization->name }}
                    </div>
                @endif

                <div
                    class="mt-5 space-y-2
                       text-[12px]
                       leading-5
                       text-[#77746d]">
                    @if ($invoice->organization?->email)
                        <div>
                            {{ $invoice->organization->email }}
                        </div>
                    @endif

                    @if ($invoice->organization?->phone)
                        <div>
                            {{ $invoice->organization->phone }}
                        </div>
                    @endif

                    @if ($invoice->organization?->address)
                        <div>
                            {{ $invoice->organization->address }}
                        </div>
                    @endif
                </div>

                @if ($invoice->organization)
                    <a href="{{ route('superadmin.organizations.show', $invoice->organization) }}"
                        class="mt-5
                           inline-flex
                           text-[12px]
                           font-semibold">
                        Open Organization →
                    </a>
                @endif
            </section>


            {{-- INVOICE DETAIL --}}
            <section class="rounded-2xl
                   border border-black/[0.07]
                   bg-white p-6">
                <h2 class="text-[16px]
                       font-semibold">
                    Invoice Details
                </h2>

                <dl class="mt-6 space-y-4">
                    <div class="flex
                           justify-between
                           gap-5">
                        <dt class="text-[12px]
                               text-[#99968e]">
                            Source
                        </dt>

                        <dd
                            class="text-right
                               text-[12px]
                               font-semibold">
                            {{ $isManual ? 'Created by Evognito' : 'Subscription' }}
                        </dd>
                    </div>

                    <div class="flex
                           justify-between
                           gap-5">
                        <dt class="text-[12px]
                               text-[#99968e]">
                            Plan
                        </dt>

                        <dd
                            class="text-right
                               text-[12px]
                               font-semibold">
                            {{ $invoice->subscription?->plan?->name ?? ($isManual ? 'Custom Invoice' : '—') }}
                        </dd>
                    </div>

                    <div class="flex
                           justify-between
                           gap-5">
                        <dt class="text-[12px]
                               text-[#99968e]">
                            Created By
                        </dt>

                        <dd
                            class="text-right
                               text-[12px]
                               font-semibold">
                            {{ $invoice->creator?->name ?? ($isManual ? 'Evognito' : 'System') }}
                        </dd>
                    </div>

                    <div class="flex
                           justify-between
                           gap-5">
                        <dt class="text-[12px]
                               text-[#99968e]">
                            Currency
                        </dt>

                        <dd
                            class="text-right
                               text-[12px]
                               font-semibold">
                            {{ $invoice->currency }}
                        </dd>
                    </div>

                    <div class="flex
                           justify-between
                           gap-5">
                        <dt class="text-[12px]
                               text-[#99968e]">
                            Issued
                        </dt>

                        <dd
                            class="text-right
                               text-[12px]
                               font-semibold">
                            @if ($invoice->issued_at)
                                {{ $invoice->issued_at->format('d M Y H:i') }}
                                WIB
                            @else
                                Draft
                            @endif
                        </dd>
                    </div>

                    <div class="flex
                           justify-between
                           gap-5">
                        <dt class="text-[12px]
                               text-[#99968e]">
                            Due
                        </dt>

                        <dd
                            class="text-right
                               text-[12px]
                               font-semibold">
                            @if ($invoice->due_at)
                                {{ $invoice->due_at->format('d M Y H:i') }}
                                WIB
                            @else
                                —
                            @endif
                        </dd>
                    </div>

                    <div class="flex
                           justify-between
                           gap-5">
                        <dt class="text-[12px]
                               text-[#99968e]">
                            Paid At
                        </dt>

                        <dd
                            class="text-right
                               text-[12px]
                               font-semibold">
                            @if ($invoice->paid_at)
                                {{ $invoice->paid_at->format('d M Y H:i') }}
                                WIB
                            @else
                                —
                            @endif
                        </dd>
                    </div>
                </dl>
            </section>


            {{-- SERVICE PERIOD --}}
            @if ($invoice->period_start || $invoice->period_end)
                <section
                    class="rounded-2xl
                       border border-black/[0.07]
                       bg-white p-6">
                    <div
                        class="text-[12px]
                           font-semibold
                           uppercase
                           tracking-[.08em]
                           text-[#99968e]">
                        Service Period
                    </div>

                    <div class="mt-4
                           text-[16px]
                           font-semibold">
                        {{ $invoice->period_start?->format('d M Y') ?? '—' }}
                    </div>

                    <div class="mt-1
                           text-[12px]
                           text-[#99968e]">
                        sampai
                    </div>

                    <div class="mt-1
                           text-[16px]
                           font-semibold">
                        {{ $invoice->period_end?->format('d M Y') ?? '—' }}
                    </div>
                </section>
            @endif


            {{-- PAYMENT SUMMARY --}}
            <section
                class="rounded-2xl
                   bg-[#1d1d1b]
                   p-6
                   text-white">
                <div
                    class="text-[11px]
                       font-semibold
                       uppercase
                       tracking-[.09em]
                       text-white/40">
                    Payment Summary
                </div>

                <div
                    class="mt-5
                       text-[27px]
                       font-semibold
                       tracking-[-.045em]">
                    Rp
                    {{ number_format($invoice->outstanding_amount, 0, ',', '.') }}
                </div>

                <div class="mt-1
                       text-[12px]
                       text-white/45">
                    outstanding
                </div>

                <div class="mt-6 space-y-3">
                    <div class="flex
                           justify-between
                           text-[12px]">
                        <span class="text-white/45">
                            Invoice
                        </span>

                        <span class="font-semibold">
                            Rp
                            {{ number_format($invoice->total, 0, ',', '.') }}
                        </span>
                    </div>

                    <div class="flex
                           justify-between
                           text-[12px]">
                        <span class="text-white/45">
                            Paid
                        </span>

                        <span class="font-semibold">
                            Rp
                            {{ number_format($invoice->paid_amount, 0, ',', '.') }}
                        </span>
                    </div>
                </div>

                @if (in_array($invoice->status, ['issued', 'partial', 'overdue'], true))
                    <a href="{{ route('superadmin.payments.create', [
                        'invoice' => $invoice->id,
                    ]) }}"
                        class="mt-6
                           flex h-11
                           w-full
                           items-center
                           justify-center
                           rounded-xl
                           bg-white
                           text-[13px]
                           font-semibold
                           text-black">
                        Record Payment
                    </a>
                @elseif($invoice->status === 'paid')
                    <div
                        class="mt-6
                           rounded-xl
                           bg-emerald-400/10
                           px-4 py-3
                           text-center
                           text-[12px]
                           font-semibold
                           text-emerald-300">
                        Invoice Paid
                    </div>
                @elseif($invoice->status === 'draft')
                    <div
                        class="mt-6
                           rounded-xl
                           bg-white/10
                           px-4 py-3
                           text-center
                           text-[12px]
                           text-white/60">
                        Issue invoice before payment
                    </div>
                @endif
            </section>

        </aside>
    </div>

@endsection
