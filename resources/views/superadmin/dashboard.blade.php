@extends('layouts.superadmin')

@section('title', 'Platform Overview')
@section('page_name', 'Overview')

@section('content')

    <div class="flex flex-col gap-5
           md:flex-row
           md:items-end
           md:justify-between">
        <div>
            <div
                class="text-[10px] font-bold
                   uppercase tracking-[.13em]
                   text-[#e8742a]">
                Platform Control Center
            </div>

            <h1 class="mt-2 text-[30px]
                   font-semibold
                   tracking-[-.045em]">
                Platform Overview
            </h1>

            <p class="mt-2 text-[12px]
                   leading-5 text-[#85827b]">
                Monitor organizations, users,
                subscriptions dan seluruh ecosystem
                Evognito One.
            </p>
        </div>

        <div class="text-[10px]
               text-[#99968e]">
            {{ now()->translatedFormat('l, d F Y • H:i') }}
        </div>
    </div>

    {{-- STATS --}}
    <div class="mt-8 grid gap-3
           sm:grid-cols-2
           xl:grid-cols-3
           2xl:grid-cols-6">
        @php
            $cards = [
                ['Organizations', $stats['organizations'], 'All tenants'],
                ['Users', $stats['users'], 'Platform accounts'],
                ['Employees', $stats['employees'], 'Across organizations'],
                ['Modules', $stats['modules'], 'Active apps'],
                ['Subscriptions', $stats['active_subscriptions'], 'Active'],
                ['Unpaid invoices', $stats['unpaid_invoices'], 'Require attention'],
            ];
        @endphp

        @foreach ($cards as $index => $card)
            <div
                class="rounded-2xl
                   border border-black/[0.07]
                   bg-white p-5
                   ev-card-shadow">
                <div class="flex items-center
                       justify-between">
                    <div class="text-[10px] font-medium
                           text-[#8c8981]">
                        {{ $card[0] }}
                    </div>

                    <div
                        class="h-2 w-2 rounded-full
                           {{ $index === 5 && $card[1] > 0 ? 'bg-amber-400' : 'bg-emerald-400' }}">
                    </div>
                </div>

                <div class="mt-6 text-[27px]
                       font-semibold
                       tracking-[-.04em]">
                    {{ number_format($card[1]) }}
                </div>

                <div class="mt-1 text-[9px]
                       text-[#aaa79f]">
                    {{ $card[2] }}
                </div>
            </div>
        @endforeach
    </div>

    {{-- MAIN GRID --}}
    <div class="mt-5 grid gap-5
           xl:grid-cols-[1.4fr_.6fr]">
        {{-- ECOSYSTEM --}}
        <section
            class="rounded-2xl
               border border-black/[0.07]
               bg-white
               ev-card-shadow">
            <div
                class="flex items-center
                   justify-between
                   border-b border-black/[0.06]
                   px-6 py-5">
                <div>
                    <h2 class="text-[13px]
                           font-semibold">
                        Evognito ecosystem
                    </h2>

                    <p class="mt-1 text-[10px]
                           text-[#99968e]">
                        Core applications registered
                        on the platform.
                    </p>
                </div>

                <span
                    class="rounded-full
                       bg-[#f3f2ed]
                       px-2.5 py-1
                       text-[9px]
                       font-semibold
                       text-[#77736c]">
                    {{ number_format($stats['modules']) }}
                    active
                </span>
            </div>

            <div
                class="grid gap-px
                   bg-black/[0.05]
                   sm:grid-cols-2
                   lg:grid-cols-3">
                @php
                    $apps = [
                        ['HR', 'HRIS', 'People'],
                        ['PA', 'Performance', 'People'],
                        ['LM', 'LMS', 'Learning'],
                        ['KS', 'Kansiku', 'Attendance'],
                        ['SF', 'Sales Force', 'Operations'],
                        ['ST', 'Sites', 'Digital'],
                    ];
                @endphp

                @foreach ($apps as $app)
                    <div
                        class="bg-white p-5
                           transition
                           hover:bg-[#faf9f6]">
                        <div
                            class="flex h-9 w-9
                               items-center justify-center
                               rounded-xl
                               bg-[#f2f1ec]
                               text-[9px]
                               font-bold">
                            {{ $app[0] }}
                        </div>

                        <div class="mt-5 text-[12px]
                               font-semibold">
                            {{ $app[1] }}
                        </div>

                        <div class="mt-1 text-[9px]
                               text-[#99968e]">
                            {{ $app[2] }}
                        </div>

                        <div
                            class="mt-5 flex
                               items-center gap-1.5
                               text-[9px]
                               font-medium
                               text-emerald-600">
                            <span
                                class="h-1.5 w-1.5
                                   rounded-full
                                   bg-emerald-500"></span>

                            Available
                        </div>
                    </div>
                @endforeach
            </div>
        </section>

        {{-- PLATFORM HEALTH --}}
        <section
            class="rounded-2xl
               border border-black/[0.07]
               bg-[#1d1d1b]
               p-6 text-white
               ev-card-shadow">
            <div
                class="text-[10px] font-semibold
                   uppercase tracking-[.12em]
                   text-white/35">
                Platform health
            </div>

            <div class="mt-4 flex
                   items-end gap-2">
                <div class="text-[32px]
                       font-semibold
                       tracking-[-.05em]">
                    Operational
                </div>
            </div>

            <div class="mt-7 space-y-4">
                @foreach ([['Core API', 'Operational'], ['OAuth / SSO', 'Operational'], ['Database', 'Connected'], ['Billing engine', 'Ready']] as $health)
                    <div
                        class="flex items-center
                           justify-between
                           border-b border-white/[0.07]
                           pb-4 last:border-none">
                        <div class="text-[10px]
                               text-white/55">
                            {{ $health[0] }}
                        </div>

                        <div
                            class="flex items-center gap-2
                               text-[9px]
                               font-semibold
                               text-white/75">
                            <span
                                class="h-1.5 w-1.5
                                   rounded-full
                                   bg-emerald-400"></span>

                            {{ $health[1] }}
                        </div>
                    </div>
                @endforeach
            </div>
        </section>
    </div>

    {{-- LOWER --}}
    <div class="mt-5 grid gap-5
           lg:grid-cols-2">
        <section
            class="rounded-2xl
               border border-black/[0.07]
               bg-white p-6
               ev-card-shadow">
            <div
                class="text-[10px] font-semibold
                   uppercase tracking-[.12em]
                   text-[#99968e]">
                Quick actions
            </div>

            <div class="mt-5 grid gap-2 sm:grid-cols-2">
                @foreach ([['Create organization', 'Add a new tenant workspace'], ['Manage modules', 'Configure Evognito applications'], ['Pricing setup', 'Fixed, per-user and hybrid'], ['Review billing', 'Invoices and payment status']] as $action)
                    <button type="button"
                        class="cursor-pointer
                           rounded-xl
                           border border-black/[0.07]
                           bg-[#faf9f6]
                           p-4 text-left
                           transition
                           hover:border-black/[0.12]
                           hover:bg-[#f5f3ee]">
                        <div class="text-[11px]
                               font-semibold">
                            {{ $action[0] }}
                        </div>

                        <div
                            class="mt-1 text-[9px]
                               leading-5
                               text-[#99968e]">
                            {{ $action[1] }}
                        </div>
                    </button>
                @endforeach
            </div>
        </section>

        <section
            class="rounded-2xl
               border border-black/[0.07]
               bg-white p-6
               ev-card-shadow">
            <div class="flex items-center
                   justify-between">
                <div>
                    <div
                        class="text-[10px]
                           font-semibold
                           uppercase
                           tracking-[.12em]
                           text-[#99968e]">
                        Platform setup
                    </div>

                    <h2 class="mt-2 text-[15px]
                           font-semibold">
                        Evognito One V2 Foundation
                    </h2>
                </div>

                <div
                    class="rounded-full
                       bg-emerald-50
                       px-2.5 py-1
                       text-[9px]
                       font-semibold
                       text-emerald-700">
                    Ready
                </div>
            </div>

            <p class="mt-4 text-[11px]
                   leading-6 text-[#88857d]">
                Core identity, organizations,
                module registry, subscriptions,
                flexible pricing dan billing foundation
                sudah tersedia untuk tahap development
                berikutnya.
            </p>

            <div
                class="mt-6 h-1.5
                   overflow-hidden
                   rounded-full
                   bg-[#efede7]">
                <div class="h-full w-[38%]
                       rounded-full
                       bg-[#e8742a]"></div>
            </div>

            <div
                class="mt-2 flex
                   justify-between
                   text-[9px]
                   text-[#aaa79f]">
                <span>Foundation</span>
                <span>38%</span>
            </div>
        </section>
    </div>

@endsection
