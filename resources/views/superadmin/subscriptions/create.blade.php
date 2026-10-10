@extends('layouts.superadmin')

@section('title', 'New Subscription')
@section('page_name', 'New Subscription')

@section('content')

    <div class="mb-7">
        <a href="{{ route('superadmin.subscriptions.index') }}"
            class="text-[12px]
               font-semibold text-[#88857d]">
            ← Subscriptions
        </a>

        <h1 class="mt-4 text-[30px]
               font-semibold
               tracking-[-.045em]">
            Create Subscription
        </h1>

        <p class="mt-2 text-[14px]
               text-[#85827b]">
            Hubungkan organization dengan
            subscription plan.
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

    <form method="POST" action="{{ route('superadmin.subscriptions.store') }}">
        @csrf

        <div
            class="mx-auto max-w-[850px]
               rounded-2xl border
               border-black/[0.07]
               bg-white p-6">
            <div class="grid gap-5
                   md:grid-cols-2">
                <div>
                    <label class="mb-2 block
                           text-[13px]
                           font-medium">
                        Organization
                    </label>

                    <select name="organization_id" required
                        class="h-11 w-full
                           rounded-xl border
                           border-black/[0.09]
                           bg-[#faf9f6]
                           px-3 text-[13px]">
                        <option value="">
                            Select organization
                        </option>

                        @foreach ($organizations as $organization)
                            <option value="{{ $organization->id }}" @selected(old('organization_id') == $organization->id)>
                                {{ $organization->name }}
                            </option>
                        @endforeach
                    </select>
                </div>

                <div>
                    <label class="mb-2 block
                           text-[13px]
                           font-medium">
                        Plan
                    </label>

                    <select name="subscription_plan_id" required
                        class="h-11 w-full
                           rounded-xl border
                           border-black/[0.09]
                           bg-[#faf9f6]
                           px-3 text-[13px]">
                        <option value="">
                            Select plan
                        </option>

                        @foreach ($plans as $plan)
                            <option value="{{ $plan->id }}" @selected(old('subscription_plan_id') == $plan->id)>
                                {{ $plan->name }}
                                —
                                Rp
                                {{ number_format($plan->base_price, 0, ',', '.') }}
                            </option>
                        @endforeach
                    </select>
                </div>

                <div>
                    <label class="mb-2 block
                           text-[13px]
                           font-medium">
                        Starts At
                    </label>

                    <input type="date" name="starts_at" required
                        value="{{ old('starts_at', now()->toDateString()) }}"
                        class="h-11 w-full
                           rounded-xl border
                           border-black/[0.09]
                           bg-[#faf9f6]
                           px-3 text-[13px]">
                </div>

                <div class="flex items-end">
                    <label
                        class="flex h-11
                           items-center gap-3
                           text-[13px]">
                        <input type="checkbox" name="auto_renew" value="1" checked>

                        Automatically renew subscription
                    </label>
                </div>
            </div>

            <div class="mt-7 flex
                   justify-end gap-3">
                <a href="{{ route('superadmin.subscriptions.index') }}"
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
                       font-semibold text-white">
                    Create Subscription
                </button>
            </div>
        </div>
    </form>

@endsection
