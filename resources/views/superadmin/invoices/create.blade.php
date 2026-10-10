@extends('layouts.superadmin')

@section('title', 'Generate Invoice')
@section('page_name', 'Generate Invoice')

@section('content')

    <div class="mb-7">
        <a href="{{ route('superadmin.invoices.index') }}"
            class="text-[12px]
               font-semibold
               text-[#88857d]">
            ← Invoices
        </a>

        <h1 class="mt-4 text-[30px]
               font-semibold
               tracking-[-.045em]">
            Generate Invoice
        </h1>

        <p class="mt-2 text-[14px]
               text-[#85827b]">
            Invoice dihitung otomatis berdasarkan
            subscription, plan, module dan
            organization-specific pricing.
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

    <form method="POST" action="{{ route('superadmin.invoices.store') }}">
        @csrf

        <div
            class="mx-auto max-w-[850px]
               rounded-2xl
               border border-black/[0.07]
               bg-white p-6">
            <div>
                <label class="mb-2 block
                       text-[13px]
                       font-medium">
                    Subscription
                </label>

                <select name="subscription_id" required
                    class="h-12 w-full
                       rounded-xl
                       border border-black/[0.09]
                       bg-[#faf9f6]
                       px-3 text-[13px]">
                    <option value="">
                        Select subscription
                    </option>

                    @foreach ($subscriptions as $subscription)
                        <option value="{{ $subscription->id }}">
                            {{ $subscription->organization?->name }}
                            —
                            {{ $subscription->plan?->name }}
                            —
                            {{ $subscription->current_period_start?->format('d M Y') }}
                            to
                            {{ $subscription->current_period_end?->format('d M Y') }}
                        </option>
                    @endforeach
                </select>
            </div>

            <label class="mt-5 flex
                   items-center gap-3
                   text-[13px]">
                <input type="checkbox" name="issue_now" value="1" checked>

                Issue invoice immediately
            </label>

            <div
                class="mt-6 rounded-xl
                   bg-[#f7f6f3]
                   p-4
                   text-[12px]
                   leading-6
                   text-[#77746d]">
                Harga akan dihitung saat tombol
                Generate ditekan. Invoice untuk
                subscription dan periode yang sama
                tidak dapat dibuat dua kali.
            </div>

            <div class="mt-7 flex
                   justify-end gap-3">
                <a href="{{ route('superadmin.invoices.index') }}"
                    class="flex h-11
                       items-center
                       rounded-xl
                       border border-black/[0.08]
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
                    Generate Invoice
                </button>
            </div>
        </div>
    </form>

@endsection
