@extends('layouts.superadmin')

@section('title', 'Plans & Pricing')
@section('page_name', 'Plans & Pricing')

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
                Plans & Pricing
            </h1>

            <p class="mt-2 text-[14px]
                   text-[#85827b]">
                Kelola bundle aplikasi dan
                model harga Evognito One.
            </p>
        </div>

        <a href="{{ route('superadmin.plans.create') }}"
            class="inline-flex h-11
               items-center rounded-xl
               bg-[#1d1d1b] px-4
               text-[13px]
               font-semibold text-white">
            + New Plan
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
        <input type="text" name="search" value="{{ $search }}" placeholder="Search plans..."
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
            <option value="">All status</option>

            <option value="active" @selected($status === 'active')>
                Active
            </option>

            <option value="inactive" @selected($status === 'inactive')>
                Inactive
            </option>
        </select>

        <button type="submit"
            class="h-11 rounded-xl
               bg-[#1d1d1b]
               px-5 text-[13px]
               font-semibold text-white">
            Filter
        </button>
    </form>

    <div class="mt-6 grid gap-4
           md:grid-cols-2
           xl:grid-cols-3">
        @forelse($plans as $plan)
            <article
                class="flex flex-col
                   rounded-[22px]
                   border border-black/[0.07]
                   bg-white p-6">
                <div class="flex items-start
                       justify-between gap-4">
                    <div>
                        <div
                            class="text-[12px]
                               font-semibold
                               uppercase
                               tracking-[.08em]
                               text-[#99968e]">
                            {{ $plan->code }}
                        </div>

                        <h2
                            class="mt-2 text-[21px]
                               font-semibold
                               tracking-[-.03em]">
                            {{ $plan->name }}
                        </h2>
                    </div>

                    <span
                        class="rounded-full
                           px-3 py-1.5
                           text-[11px]
                           font-semibold
                           {{ $plan->status === 'active' ? 'bg-emerald-50 text-emerald-700' : 'bg-zinc-100 text-zinc-600' }}">
                        {{ ucfirst($plan->status) }}
                    </span>
                </div>

                <p
                    class="mt-4 min-h-[48px]
                       text-[13px]
                       leading-6
                       text-[#85827b]">
                    {{ $plan->description ?: 'No description.' }}
                </p>

                <div class="mt-6">
                    <div
                        class="text-[28px]
                           font-semibold
                           tracking-[-.045em]">
                        Rp
                        {{ number_format($plan->base_price, 0, ',', '.') }}
                    </div>

                    <div class="mt-1 text-[12px]
                           text-[#99968e]">
                        / {{ $plan->billing_interval }}

                        @if ($plan->price_per_user > 0)
                            +
                            Rp
                            {{ number_format($plan->price_per_user, 0, ',', '.') }}
                            / user
                        @endif
                    </div>
                </div>

                <div class="mt-6 grid grid-cols-2
                       gap-3">
                    <div class="rounded-xl
                           bg-[#f7f6f3]
                           p-4">
                        <div class="text-[11px]
                               text-[#99968e]">
                            Applications
                        </div>

                        <div class="mt-1 text-[18px]
                               font-semibold">
                            {{ $plan->modules_count }}
                        </div>
                    </div>

                    <div class="rounded-xl
                           bg-[#f7f6f3]
                           p-4">
                        <div class="text-[11px]
                               text-[#99968e]">
                            Subscriptions
                        </div>

                        <div class="mt-1 text-[18px]
                               font-semibold">
                            {{ $plan->active_subscriptions_count }}
                        </div>
                    </div>
                </div>

                <div class="mt-6 flex
                       items-center
                       justify-between">
                    <div class="text-[12px]
                           text-[#88857d]">
                        {{ $plan->is_public ? 'Public plan' : 'Private plan' }}
                    </div>

                    <a href="{{ route('superadmin.plans.show', $plan) }}"
                        class="text-[12px]
                           font-semibold">
                        Manage →
                    </a>
                </div>
            </article>

        @empty

            <div
                class="col-span-full
                   rounded-2xl
                   border border-dashed
                   border-black/[0.12]
                   bg-white py-16
                   text-center">
                <div class="text-[15px] font-semibold">
                    Belum ada subscription plan.
                </div>
            </div>
        @endforelse
    </div>

@endsection
