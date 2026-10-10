@extends('layouts.superadmin')

@section('title', 'Payments')
@section('page_name', 'Payments')

@section('content')

    <div class="flex flex-col gap-5
           md:flex-row
           md:items-end
           md:justify-between">
        <div>
            <div
                class="text-[11px] font-bold
                   uppercase tracking-[.1em]
                   text-[#e8742a]">
                Commercial
            </div>

            <h1 class="mt-2 text-[30px]
                   font-semibold
                   tracking-[-.045em]">
                Payments
            </h1>

            <p class="mt-2 text-[14px]
                   text-[#85827b]">
                Riwayat pembayaran invoice
                Evognito One.
            </p>
        </div>

        <a href="{{ route('superadmin.payments.create') }}"
            class="inline-flex h-11
               items-center
               rounded-xl
               bg-[#1d1d1b]
               px-4 text-[13px]
               font-semibold text-white">
            + Record Payment
        </a>
    </div>

    @if (session('success'))
        <div
            class="mt-5 rounded-xl
               border border-emerald-200
               bg-emerald-50 p-4
               text-[13px]
               text-emerald-700">
            {{ session('success') }}
        </div>
    @endif

    <form method="GET"
        class="mt-7 flex flex-col gap-3
           rounded-2xl
           border border-black/[0.07]
           bg-white p-4
           md:flex-row">
        <input type="text" name="search" value="{{ $search }}"
            placeholder="Payment number, reference, organization..."
            class="h-11 flex-1
               rounded-xl
               border border-black/[0.08]
               bg-[#faf9f6]
               px-4 text-[13px]">

        <select name="status"
            class="h-11 rounded-xl
               border border-black/[0.08]
               bg-[#faf9f6]
               px-4 text-[13px]">
            <option value="">
                All status
            </option>

            @foreach ([
            'pending' => 'Pending',
            'paid' => 'Paid',
            'failed' => 'Failed',
            'canceled' => 'Canceled',
            'refunded' => 'Refunded',
        ] as $value => $label)
                <option value="{{ $value }}" @selected($status === $value)>
                    {{ $label }}
                </option>
            @endforeach
        </select>

        <button
            class="h-11 rounded-xl
               bg-[#1d1d1b]
               px-5 text-[13px]
               font-semibold text-white">
            Filter
        </button>
    </form>

    <div class="mt-5 overflow-hidden
           rounded-2xl
           border border-black/[0.07]
           bg-white">
        <div class="overflow-x-auto">
            <table class="w-full min-w-[1050px]">

                <thead>
                    <tr
                        class="border-b
                           border-black/[0.06]
                           bg-[#faf9f6]">
                        @foreach (['Payment', 'Organization', 'Invoice', 'Method', 'Amount', 'Status', ''] as $heading)
                            <th
                                class="px-5 py-3
                                   text-left
                                   text-[12px]
                                   font-semibold
                                   text-[#85827b]">
                                {{ $heading }}
                            </th>
                        @endforeach
                    </tr>
                </thead>

                <tbody>
                    @forelse($payments as $payment)
                        <tr
                            class="border-b
                           border-black/[0.05]
                           last:border-0
                           hover:bg-[#faf9f6]">
                            <td class="px-5 py-4">
                                <a href="{{ route('superadmin.payments.show', $payment) }}"
                                    class="text-[13px]
                                   font-semibold">
                                    {{ $payment->payment_number }}
                                </a>

                                <div
                                    class="mt-1
                                   text-[11px]
                                   text-[#99968e]">
                                    {{ $payment->provider }}
                                </div>
                            </td>

                            <td
                                class="px-5 py-4
                               text-[13px]
                               font-semibold">
                                {{ $payment->organization?->name ?? '—' }}
                            </td>

                            <td class="px-5 py-4">
                                @if ($payment->invoice)
                                    <a href="{{ route('superadmin.invoices.show', $payment->invoice) }}"
                                        class="text-[12px]
                                       font-semibold">
                                        {{ $payment->invoice->invoice_number }}
                                    </a>
                                @else
                                    —
                                @endif
                            </td>

                            <td class="px-5 py-4
                               text-[12px]">
                                {{ ucwords(str_replace('_', ' ', $payment->method)) }}
                            </td>

                            <td
                                class="px-5 py-4
                               text-[13px]
                               font-semibold">
                                Rp
                                {{ number_format($payment->amount, 0, ',', '.') }}
                            </td>

                            <td
                                class="px-5 py-4
                               text-[12px]
                               font-semibold
                               capitalize">
                                {{ $payment->status }}
                            </td>

                            <td class="px-5 py-4
                               text-right">
                                <a href="{{ route('superadmin.payments.show', $payment) }}"
                                    class="text-[12px]
                                   font-semibold">
                                    Manage →
                                </a>
                            </td>
                        </tr>

                    @empty
                        <tr>
                            <td colspan="7"
                                class="px-6 py-16
                               text-center
                               text-[14px]">
                                Belum ada payment.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @if ($payments->hasPages())
            <div class="border-t
                   border-black/[0.06]
                   px-5 py-4">
                {{ $payments->links() }}
            </div>
        @endif
    </div>

@endsection
