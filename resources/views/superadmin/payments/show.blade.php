@extends('layouts.superadmin')

@section('title', $payment->payment_number)
@section('page_name', 'Payment')

@section('content')

    @if (session('success'))
        <div
            class="mb-5 rounded-xl
               border border-emerald-200
               bg-emerald-50 p-4
               text-[13px]
               text-emerald-700">
            {{ session('success') }}
        </div>
    @endif

    <div class="flex flex-col gap-5
           lg:flex-row
           lg:items-end
           lg:justify-between">
        <div>
            <div
                class="text-[12px]
                   font-semibold uppercase
                   tracking-[.08em]
                   text-[#e8742a]">
                Payment
            </div>

            <h1 class="mt-2 text-[30px]
                   font-semibold
                   tracking-[-.045em]">
                {{ $payment->payment_number }}
            </h1>

            <div class="mt-2 text-[14px]
                   text-[#85827b]">
                {{ $payment->organization?->name }}
            </div>
        </div>

        @if (!in_array($payment->status, ['canceled', 'refunded'], true))
            <form method="POST"
                action="{{ route('superadmin.payments.cancel', $payment) }}"
                onsubmit="
                return confirm(
                    'Batalkan payment ini?'
                );
            ">
                @csrf

                <button
                    class="h-10 rounded-xl
                       border border-red-200
                       bg-red-50
                       px-4 text-[12px]
                       font-semibold
                       text-red-700">
                    Cancel Payment
                </button>
            </form>
        @endif
    </div>

    <div class="mt-8 grid gap-4
           md:grid-cols-3">
        <div class="rounded-2xl
               border border-black/[0.07]
               bg-white p-5">
            <div class="text-[12px] text-[#99968e]">
                Amount
            </div>

            <div class="mt-4 text-[24px]
                   font-semibold">
                Rp
                {{ number_format($payment->amount, 0, ',', '.') }}
            </div>
        </div>

        <div class="rounded-2xl
               border border-black/[0.07]
               bg-white p-5">
            <div class="text-[12px] text-[#99968e]">
                Status
            </div>

            <div class="mt-4 text-[20px]
                   font-semibold capitalize">
                {{ $payment->status }}
            </div>
        </div>

        <div class="rounded-2xl
               border border-black/[0.07]
               bg-white p-5">
            <div class="text-[12px] text-[#99968e]">
                Provider
            </div>

            <div class="mt-4 text-[20px]
                   font-semibold capitalize">
                {{ $payment->provider }}
            </div>
        </div>
    </div>

    <div class="mt-5 grid gap-5
           xl:grid-cols-[1fr_360px]">
        <section class="rounded-2xl
               border border-black/[0.07]
               bg-white p-6">
            <h2 class="text-[16px] font-semibold">
                Payment Information
            </h2>

            <div class="mt-6 space-y-5">
                @foreach ([['Method', ucwords(str_replace('_', ' ', $payment->method))], ['Reference', $payment->reference ?: '—'], ['Paid At', $payment->paid_at?->format('d M Y H:i') ?? '—'], ['Currency', $payment->currency], ['Provider Reference', $payment->provider_reference ?: '—']] as $row)
                    <div class="flex
                           justify-between gap-5">
                        <span class="text-[12px]
                               text-[#99968e]">
                            {{ $row[0] }}
                        </span>

                        <span
                            class="text-right
                               text-[12px]
                               font-semibold">
                            {{ $row[1] }}
                        </span>
                    </div>
                @endforeach
            </div>

            @if ($payment->notes)
                <div class="mt-6 border-t
                       border-black/[0.06]
                       pt-5">
                    <div class="text-[12px]
                           text-[#99968e]">
                        Notes
                    </div>

                    <p class="mt-2 text-[13px]
                           leading-6">
                        {{ $payment->notes }}
                    </p>
                </div>
            @endif
        </section>

        <section class="h-fit rounded-2xl
               bg-[#1d1d1b]
               p-6 text-white">
            <div class="text-[12px]
                   uppercase tracking-[.08em]
                   text-white/40">
                Related Invoice
            </div>

            @if ($payment->invoice)
                <div class="mt-4 text-[19px]
                       font-semibold">
                    {{ $payment->invoice->invoice_number }}
                </div>

                <div class="mt-2 text-[13px]
                       text-white/50">
                    Invoice total:
                    Rp
                    {{ number_format($payment->invoice->total, 0, ',', '.') }}
                </div>

                <a href="{{ route('superadmin.invoices.show', $payment->invoice) }}"
                    class="mt-6 inline-flex
                       h-10 items-center
                       rounded-xl bg-white
                       px-4 text-[12px]
                       font-semibold text-black">
                    Open Invoice →
                </a>
            @endif
        </section>
    </div>

@endsection
