@extends('layouts.superadmin')

@section('title', $subscription->organization?->name ?? 'Subscription')

@section('page_name', 'Subscription')

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
                Subscription
            </div>

            <h1 class="mt-2 text-[30px]
                   font-semibold
                   tracking-[-.045em]">
                {{ $subscription->organization?->name }}
            </h1>

            <div class="mt-2 text-[14px]
                   text-[#85827b]">
                {{ $subscription->plan?->name ?? 'Custom Plan' }}
            </div>
        </div>

        <div class="flex flex-wrap gap-2">
            @if ($subscription->status !== 'active')
                <form method="POST"
                    action="{{ route('superadmin.subscriptions.activate', $subscription) }}">
                    @csrf

                    <button
                        class="h-10 rounded-xl
                           bg-emerald-600
                           px-4 text-[12px]
                           font-semibold text-white">
                        Activate
                    </button>
                </form>
            @endif

            @if (!in_array($subscription->status, ['suspended', 'canceled'], true))
                <form method="POST"
                    action="{{ route('superadmin.subscriptions.suspend', $subscription) }}">
                    @csrf

                    <button
                        class="h-10 rounded-xl
                           border border-amber-200
                           bg-amber-50
                           px-4 text-[12px]
                           font-semibold
                           text-amber-700">
                        Suspend
                    </button>
                </form>
            @endif

            @if ($subscription->status !== 'canceled')
                <form method="POST"
                    action="{{ route('superadmin.subscriptions.cancel', $subscription) }}"
                    onsubmit="
                    return confirm(
                        'Batalkan subscription ini?'
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
                        Cancel
                    </button>
                </form>
            @endif
        </div>
    </div>

    <div class="mt-8 grid gap-4
           sm:grid-cols-2
           xl:grid-cols-4">
        @foreach ([['Status', ucfirst(str_replace('_', ' ', $subscription->status))], ['Starts', $subscription->starts_at?->format('d M Y') ?? '—'], ['Current Period', ($subscription->current_period_start?->format('d M Y') ?? '—') . ' – ' . ($subscription->current_period_end?->format('d M Y') ?? '—')], ['Auto Renew', $subscription->auto_renew ? 'Enabled' : 'Disabled']] as $item)
            <div class="rounded-2xl border
                   border-black/[0.07]
                   bg-white p-5">
                <div class="text-[12px]
                       text-[#99968e]">
                    {{ $item[0] }}
                </div>

                <div class="mt-4 text-[18px]
                       font-semibold">
                    {{ $item[1] }}
                </div>
            </div>
        @endforeach
    </div>

    <div class="mt-5 grid gap-5
           xl:grid-cols-[1fr_360px]">
        <section
            class="overflow-hidden
               rounded-2xl border
               border-black/[0.07]
               bg-white">
            <div class="border-b
                   border-black/[0.06]
                   px-6 py-5">
                <h2 class="text-[16px] font-semibold">
                    Enabled Applications
                </h2>
            </div>

            @forelse($subscription->plan?->modules
                ?? []
                as $module)
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

                    <div class="text-[12px]
                           font-semibold
                           capitalize">
                        {{ $module->pivot->billing_mode }}
                    </div>
                </div>
            @empty
                <div
                    class="px-6 py-14
                       text-center
                       text-[13px]
                       text-[#99968e]">
                    No applications.
                </div>
            @endforelse
        </section>

        <div class="space-y-5">
            <section class="rounded-2xl
                   bg-[#1d1d1b]
                   p-6 text-white">
                <div
                    class="text-[12px]
                       uppercase
                       tracking-[.08em]
                       text-white/40">
                    Current Plan
                </div>

                <h2 class="mt-3 text-[22px]
                       font-semibold">
                    {{ $subscription->plan?->name ?? 'Custom' }}
                </h2>

                @if ($subscription->plan)
                    <div class="mt-6 text-[28px]
                           font-semibold">
                        Rp
                        {{ number_format($subscription->plan->base_price, 0, ',', '.') }}
                    </div>

                    <div class="mt-1
                           text-[12px]
                           text-white/45">
                        /
                        {{ $subscription->plan->billing_interval }}
                    </div>
                @endif
            </section>

            @if ($subscription->trial_ends_at)
                <section
                    class="rounded-2xl
                       border border-blue-100
                       bg-blue-50 p-5">
                    <div class="text-[12px]
                           text-blue-600">
                        Trial Period
                    </div>

                    <div
                        class="mt-2 text-[16px]
                           font-semibold
                           text-blue-900">
                        Until
                        {{ $subscription->trial_ends_at->format('d M Y') }}
                    </div>
                </section>
            @endif
        </div>
    </div>

@endsection
