@extends('layouts.superadmin')

@section('title', $plan->name)
@section('page_name', 'Subscription Plan')

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
                   font-semibold
                   uppercase
                   tracking-[.08em]
                   text-[#e8742a]">
                {{ $plan->code }}
            </div>

            <h1 class="mt-2 text-[30px]
                   font-semibold
                   tracking-[-.045em]">
                {{ $plan->name }}
            </h1>

            <p class="mt-2 max-w-2xl
                   text-[14px] leading-6
                   text-[#85827b]">
                {{ $plan->description ?: 'Subscription plan Evognito One.' }}
            </p>
        </div>

        <div class="flex gap-2">

            <form method="POST"
                action="{{ route('superadmin.plans.toggle-status', $plan) }}">
                @csrf

                <button
                    class="h-10 rounded-xl
                       border border-black/[0.08]
                       bg-white px-4
                       text-[12px]
                       font-semibold">
                    {{ $plan->status === 'active' ? 'Deactivate' : 'Activate' }}
                </button>
            </form>

            <a href="{{ route('superadmin.plans.edit', $plan) }}"
                class="flex h-10 items-center
                   rounded-xl bg-[#1d1d1b]
                   px-4 text-[12px]
                   font-semibold text-white">
                Edit Plan
            </a>
        </div>
    </div>

    <div class="mt-8 grid gap-4
           md:grid-cols-2
           xl:grid-cols-4">
        <div class="rounded-2xl border border-black/[0.07] bg-white p-5">
            <div class="text-[12px] text-[#99968e]">
                Base Price
            </div>

            <div class="mt-4 text-[22px] font-semibold">
                Rp
                {{ number_format($plan->base_price, 0, ',', '.') }}
            </div>
        </div>

        <div class="rounded-2xl border border-black/[0.07] bg-white p-5">
            <div class="text-[12px] text-[#99968e]">
                Per User
            </div>

            <div class="mt-4 text-[22px] font-semibold">
                Rp
                {{ number_format($plan->price_per_user, 0, ',', '.') }}
            </div>
        </div>

        <div class="rounded-2xl border border-black/[0.07] bg-white p-5">
            <div class="text-[12px] text-[#99968e]">
                Applications
            </div>

            <div class="mt-4 text-[22px] font-semibold">
                {{ $plan->modules_count }}
            </div>
        </div>

        <div class="rounded-2xl border border-black/[0.07] bg-white p-5">
            <div class="text-[12px] text-[#99968e]">
                Active Subscriptions
            </div>

            <div class="mt-4 text-[22px] font-semibold">
                {{ $plan->active_subscriptions_count }}
            </div>
        </div>
    </div>

    <div class="mt-5 grid gap-5
           xl:grid-cols-[1fr_360px]">
        <section class="overflow-hidden rounded-2xl
               border border-black/[0.07]
               bg-white">
            <div class="border-b border-black/[0.06]
                   px-6 py-5">
                <h2 class="text-[16px] font-semibold">
                    Applications
                </h2>
            </div>

            @forelse($plan->modules as $module)
                <div
                    class="flex items-center
                       justify-between
                       border-b
                       border-black/[0.05]
                       px-6 py-5
                       last:border-0">
                    <div>
                        <div class="text-[14px]
                               font-semibold">
                            {{ $module->name }}
                        </div>

                        <div
                            class="mt-1
                               text-[12px]
                               text-[#99968e]">
                            {{ $module->category }}
                        </div>
                    </div>

                    <div class="text-right">
                        <div
                            class="text-[12px]
                               font-semibold
                               capitalize">
                            {{ $module->pivot->billing_mode }}
                        </div>

                        @if ($module->pivot->billing_mode === 'override')
                            <div
                                class="mt-1
                                   text-[11px]
                                   text-[#99968e]">
                                {{ str_replace('_', ' ', $module->pivot->pricing_type) }}
                            </div>
                        @endif
                    </div>
                </div>

            @empty

                <div
                    class="px-6 py-14
                       text-center
                       text-[13px]
                       text-[#99968e]">
                    No applications in this plan.
                </div>
            @endforelse
        </section>

        <section class="h-fit rounded-2xl
               bg-[#1d1d1b]
               p-6 text-white">
            <div
                class="text-[12px]
                   uppercase
                   tracking-[.08em]
                   text-white/40">
                Plan Summary
            </div>

            <div class="mt-5 text-[28px]
                   font-semibold">
                Rp
                {{ number_format($plan->base_price, 0, ',', '.') }}
            </div>

            <div class="mt-1 text-[12px]
                   text-white/45">
                / {{ $plan->billing_interval }}
            </div>

            <div class="mt-7 space-y-4">
                <div class="flex justify-between">
                    <span class="text-[12px] text-white/40">
                        Included users
                    </span>

                    <span class="text-[12px] font-semibold">
                        {{ $plan->included_users }}
                    </span>
                </div>

                <div class="flex justify-between">
                    <span class="text-[12px] text-white/40">
                        User scope
                    </span>

                    <span class="text-[12px] font-semibold capitalize">
                        {{ str_replace('_', ' ', $plan->billing_user_scope) }}
                    </span>
                </div>

                <div class="flex justify-between">
                    <span class="text-[12px] text-white/40">
                        Trial
                    </span>

                    <span class="text-[12px] font-semibold">
                        {{ $plan->trial_days }} days
                    </span>
                </div>

                <div class="flex justify-between">
                    <span class="text-[12px] text-white/40">
                        Visibility
                    </span>

                    <span class="text-[12px] font-semibold">
                        {{ $plan->is_public ? 'Public' : 'Private' }}
                    </span>
                </div>
            </div>
        </section>
    </div>

@endsection
