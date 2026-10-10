@extends('layouts.superadmin')

@section('title', 'Record Payment')
@section('page_name', 'Record Payment')

@section('content')

    <div class="mb-7">
        <a href="{{ route('superadmin.payments.index') }}"
            class="text-[12px]
               font-semibold
               text-[#88857d]">
            ← Payments
        </a>

        <h1 class="mt-4 text-[30px]
               font-semibold
               tracking-[-.045em]">
            Record Payment
        </h1>

        <p class="mt-2 text-[14px]
               text-[#85827b]">
            Catat pembayaran manual.
            Gateway Midtrans akan ditambahkan nanti.
        </p>
    </div>

    @if ($errors->any())
        <div
            class="mb-5 rounded-xl
               border border-red-200
               bg-red-50 p-4
               text-[13px]
               text-red-700">
            @foreach ($errors->all() as $error)
                <div>• {{ $error }}</div>
            @endforeach
        </div>
    @endif

    <form method="POST" action="{{ route('superadmin.payments.store') }}">
        @csrf

        <div
            class="mx-auto max-w-[850px]
               rounded-2xl
               border border-black/[0.07]
               bg-white p-6">
            <div class="grid gap-5
                   md:grid-cols-2">
                <div class="md:col-span-2">
                    <label class="mb-2 block
                           text-[13px]
                           font-medium">
                        Invoice
                    </label>

                    <select name="invoice_id" required
                        class="h-12 w-full
                           rounded-xl
                           border border-black/[0.09]
                           bg-[#faf9f6]
                           px-3 text-[13px]">
                        <option value="">
                            Select invoice
                        </option>

                        @foreach ($invoices as $invoice)
                            <option value="{{ $invoice->id }}" @selected(old('invoice_id', $invoiceId) == $invoice->id)>
                                {{ $invoice->invoice_number }}
                                —
                                {{ $invoice->organization?->name }}
                                —
                                Rp
                                {{ number_format($invoice->outstanding_amount, 0, ',', '.') }}
                            </option>
                        @endforeach
                    </select>
                </div>

                <div>
                    <label class="mb-2 block text-[13px] font-medium">
                        Payment Method
                    </label>

                    <select name="method" required
                        class="h-11 w-full
                           rounded-xl border
                           border-black/[0.09]
                           bg-[#faf9f6]
                           px-3 text-[13px]">
                        <option value="bank_transfer">
                            Bank Transfer
                        </option>

                        <option value="cash">
                            Cash
                        </option>

                        <option value="virtual_account">
                            Virtual Account
                        </option>

                        <option value="other">
                            Other
                        </option>
                    </select>
                </div>

                <div>
                    <label class="mb-2 block text-[13px] font-medium">
                        Status
                    </label>

                    <select name="status"
                        class="h-11 w-full
                           rounded-xl border
                           border-black/[0.09]
                           bg-[#faf9f6]
                           px-3 text-[13px]">
                        <option value="paid">
                            Paid
                        </option>

                        <option value="pending">
                            Pending
                        </option>
                    </select>
                </div>

                <div>
                    <label class="mb-2 block text-[13px] font-medium">
                        Amount
                    </label>

                    <input type="number" name="amount" min="1" required value="{{ old('amount') }}"
                        class="h-11 w-full
                           rounded-xl border
                           border-black/[0.09]
                           bg-[#faf9f6]
                           px-3 text-[13px]">
                </div>

                <div>
                    <label class="mb-2 block text-[13px] font-medium">
                        Paid At
                    </label>

                    <input type="datetime-local" name="paid_at"
                        value="{{ old('paid_at', now()->format('Y-m-d\TH:i')) }}"
                        class="h-11 w-full
                           rounded-xl border
                           border-black/[0.09]
                           bg-[#faf9f6]
                           px-3 text-[13px]">
                </div>

                <div class="md:col-span-2">
                    <label class="mb-2 block text-[13px] font-medium">
                        Reference
                    </label>

                    <input type="text" name="reference" value="{{ old('reference') }}"
                        placeholder="Nomor transfer / reference"
                        class="h-11 w-full
                           rounded-xl border
                           border-black/[0.09]
                           bg-[#faf9f6]
                           px-3 text-[13px]">
                </div>

                <div class="md:col-span-2">
                    <label class="mb-2 block text-[13px] font-medium">
                        Notes
                    </label>

                    <textarea name="notes" rows="4"
                        class="w-full resize-none
                           rounded-xl border
                           border-black/[0.09]
                           bg-[#faf9f6]
                           p-3 text-[13px]">{{ old('notes') }}</textarea>
                </div>
            </div>

            <div class="mt-7 flex
                   justify-end gap-3">
                <a href="{{ route('superadmin.payments.index') }}"
                    class="flex h-11
                       items-center
                       rounded-xl border
                       border-black/[0.08]
                       px-4 text-[13px]
                       font-semibold">
                    Cancel
                </a>

                <button
                    class="h-11 rounded-xl
                       bg-[#1d1d1b]
                       px-5 text-[13px]
                       font-semibold
                       text-white">
                    Record Payment
                </button>
            </div>
        </div>
    </form>

@endsection
