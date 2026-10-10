@extends('layouts.superadmin')

@section('title', 'Subscriptions')
@section('page_name', 'Subscriptions')

@section('content')

    <div class="flex flex-col gap-5
           md:flex-row
           md:items-end
           md:justify-between">
        <div>
            <div
                class="text-[11px]
                   font-bold uppercase
                   tracking-[.1em]
                   text-[#e8742a]">
                Commercial
            </div>

            <h1 class="mt-2 text-[30px]
                   font-semibold
                   tracking-[-.045em]">
                Subscriptions
            </h1>

            <p class="mt-2 text-[14px]
                   text-[#85827b]">
                Kelola subscription setiap
                organization Evognito One.
            </p>
        </div>

        <a href="{{ route('superadmin.subscriptions.create') }}"
            class="inline-flex h-11
               items-center rounded-xl
               bg-[#1d1d1b] px-4
               text-[13px]
               font-semibold text-white">
            + New Subscription
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
           rounded-2xl border
           border-black/[0.07]
           bg-white p-4
           md:flex-row">
        <input type="text" name="search" value="{{ $search }}" placeholder="Organization..."
            class="h-11 flex-1
               rounded-xl border
               border-black/[0.08]
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
            'trialing' => 'Trialing',
            'active' => 'Active',
            'past_due' => 'Past Due',
            'suspended' => 'Suspended',
            'canceled' => 'Canceled',
            'expired' => 'Expired',
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
           rounded-2xl border
           border-black/[0.07]
           bg-white">
        <div class="overflow-x-auto">
            <table class="w-full min-w-[1000px]">

                <thead>
                    <tr
                        class="border-b
                           border-black/[0.06]
                           bg-[#faf9f6]">
                        @foreach (['Organization', 'Plan', 'Period', 'Auto Renew', 'Status', ''] as $heading)
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
                    @forelse($subscriptions
                    as $subscription)
                        @php
                            $statusClass = match ($subscription->status) {
                                'active' => 'bg-emerald-50 text-emerald-700',

                                'trialing' => 'bg-blue-50 text-blue-700',

                                'past_due' => 'bg-amber-50 text-amber-700',

                                'suspended' => 'bg-orange-50 text-orange-700',

                                default => 'bg-zinc-100 text-zinc-600',
                            };
                        @endphp

                        <tr
                            class="border-b
                           border-black/[0.05]
                           last:border-0
                           hover:bg-[#faf9f6]">
                            <td class="px-5 py-4">
                                <div class="text-[14px]
                                   font-semibold">
                                    {{ $subscription->organization?->name ?? '—' }}
                                </div>

                                <div class="mt-1 text-[11px]
                                   text-[#99968e]">
                                    /{{ $subscription->organization?->slug }}
                                </div>
                            </td>

                            <td class="px-5 py-4">
                                <div class="text-[13px]
                                   font-semibold">
                                    {{ $subscription->plan?->name ?? 'Custom' }}
                                </div>

                                <div class="mt-1 text-[11px]
                                   text-[#99968e]">
                                    {{ ucfirst($subscription->plan?->billing_interval ?? '-') }}
                                </div>
                            </td>

                            <td class="px-5 py-4">
                                <div class="text-[12px]">
                                    {{ $subscription->current_period_start?->format('d M Y') ?? '—' }}
                                </div>

                                <div class="mt-1 text-[11px]
                                   text-[#99968e]">
                                    to
                                    {{ $subscription->current_period_end?->format('d M Y') ?? '—' }}
                                </div>
                            </td>

                            <td class="px-5 py-4
                               text-[12px]">
                                {{ $subscription->auto_renew ? 'Yes' : 'No' }}
                            </td>

                            <td class="px-5 py-4">
                                <span
                                    class="rounded-full
                                   px-3 py-1.5
                                   text-[11px]
                                   font-semibold
                                   {{ $statusClass }}">
                                    {{ ucfirst(str_replace('_', ' ', $subscription->status)) }}
                                </span>
                            </td>

                            <td class="px-5 py-4
                               text-right">
                                <a href="{{ route('superadmin.subscriptions.show', $subscription) }}"
                                    class="text-[12px]
                                   font-semibold">
                                    Manage →
                                </a>
                            </td>
                        </tr>

                    @empty
                        <tr>
                            <td colspan="6"
                                class="px-6 py-16
                               text-center
                               text-[14px]">
                                Belum ada subscription.
                            </td>
                        </tr>
                    @endforelse
                </tbody>

            </table>
        </div>

        @if ($subscriptions->hasPages())
            <div class="border-t
                   border-black/[0.06]
                   px-5 py-4">
                {{ $subscriptions->links() }}
            </div>
        @endif
    </div>

@endsection
