@extends('layouts.superadmin')

@section('title', 'Manage Apps - ' . $organization->name)

@section('page_name', 'Manage Apps')

@section('content')

    {{-- ========================================================= --}}
    {{-- HEADER                                                    --}}
    {{-- ========================================================= --}}

    <div class="flex flex-col gap-5
           lg:flex-row
           lg:items-end
           lg:justify-between">
        <div>
            <a href="{{ route('superadmin.organizations.show', $organization) }}"
                class="text-[12px]
                   font-semibold
                   text-[#88857d]">
                ← {{ $organization->name }}
            </a>

            <div
                class="mt-4
                   text-[11px]
                   font-bold
                   uppercase
                   tracking-[.1em]
                   text-[#e8742a]">
                Organization
            </div>

            <h1
                class="mt-2
                   text-[30px]
                   font-semibold
                   tracking-[-.045em]">
                Manage Apps
            </h1>

            <p
                class="mt-2
                   max-w-[700px]
                   text-[14px]
                   leading-6
                   text-[#85827b]">
                Tentukan aplikasi Evognito One
                yang dapat digunakan oleh
                {{ $organization->name }}.
            </p>
        </div>

        <a href="{{ route('workspace.dashboard', [
            'organization' => $organization->slug,
        ]) }}"
            target="_blank" rel="noopener"
            class="inline-flex
               h-11
               items-center
               gap-2
               rounded-xl
               border border-black/[0.08]
               bg-white
               px-4
               text-[13px]
               font-semibold
               transition
               hover:bg-[#f6f5f1]">
            Open Workspace

            <svg class="h-4 w-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8">
                <path d="M7 17 17 7" />
                <path d="M7 7h10v10" />
            </svg>
        </a>
    </div>


    {{-- ========================================================= --}}
    {{-- ALERT                                                     --}}
    {{-- ========================================================= --}}

    @if (session('success'))
        <div
            class="mt-6
               rounded-xl
               border border-emerald-200
               bg-emerald-50
               px-4 py-3
               text-[13px]
               text-emerald-700">
            {{ session('success') }}
        </div>
    @endif

    @if ($errors->any())
        <div
            class="mt-6
               rounded-xl
               border border-red-200
               bg-red-50
               p-4
               text-[13px]
               text-red-700">
            @foreach ($errors->all() as $error)
                <div>
                    • {{ $error }}
                </div>
            @endforeach
        </div>
    @endif


    {{-- ========================================================= --}}
    {{-- SUMMARY                                                   --}}
    {{-- ========================================================= --}}

    <div class="mt-7 grid gap-3
           sm:grid-cols-2
           lg:grid-cols-4">
        <div class="rounded-2xl
               border border-black/[0.07]
               bg-white
               p-5">
            <div class="text-[12px]
                   text-[#99968e]">
                Available Apps
            </div>

            <div class="mt-4
                   text-[27px]
                   font-semibold">
                {{ $modules->count() }}
            </div>
        </div>

        <div class="rounded-2xl
               border border-black/[0.07]
               bg-white
               p-5">
            <div class="text-[12px]
                   text-[#99968e]">
                Active Apps
            </div>

            <div
                class="mt-4
                   text-[27px]
                   font-semibold
                   text-emerald-700">
                {{ $activeCount }}
            </div>
        </div>

        <div class="rounded-2xl
               border border-black/[0.07]
               bg-white
               p-5">
            <div class="text-[12px]
                   text-[#99968e]">
                Locked Apps
            </div>

            <div
                class="mt-4
                   text-[27px]
                   font-semibold
                   text-orange-700">
                {{ $lockedCount }}
            </div>
        </div>

        <div class="rounded-2xl
               border border-black/[0.07]
               bg-white
               p-5">
            <div class="text-[12px]
                   text-[#99968e]">
                Subscription
            </div>

            <div class="mt-4
                   text-[17px]
                   font-semibold">
                {{ $subscription?->plan?->name ?? 'No active plan' }}
            </div>

            @if ($subscription)
                <div
                    class="mt-2
                       text-[11px]
                       capitalize
                       text-[#99968e]">
                    {{ str_replace('_', ' ', $subscription->status) }}
                </div>
            @endif
        </div>
    </div>


    {{-- ========================================================= --}}
    {{-- SEARCH                                                    --}}
    {{-- ========================================================= --}}

    <div class="mt-5
           rounded-2xl
           border border-black/[0.07]
           bg-white
           p-4">
        <div class="relative">
            <svg class="absolute
                   left-3.5
                   top-1/2
                   h-4 w-4
                   -translate-y-1/2
                   text-[#aaa79f]"
                viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                <circle cx="11" cy="11" r="8" />

                <path d="m21 21-4.3-4.3" />
            </svg>

            <input type="search" id="app-search" placeholder="Search application..."
                class="h-11 w-full
                   rounded-xl
                   border border-black/[0.08]
                   bg-[#faf9f6]
                   pl-10 pr-4
                   text-[13px]
                   outline-none">
        </div>
    </div>


    {{-- ========================================================= --}}
    {{-- FORM                                                      --}}
    {{-- ========================================================= --}}

    <form method="POST" action="{{ route('superadmin.organizations.apps.update', $organization) }}"
        class="mt-5">
        @csrf
        @method('PUT')

        <div
            class="overflow-hidden
               rounded-2xl
               border border-black/[0.07]
               bg-white">
            <div
                class="flex
                   flex-col gap-3
                   border-b
                   border-black/[0.06]
                   px-6 py-5
                   sm:flex-row
                   sm:items-center
                   sm:justify-between">
                <div>
                    <h2 class="text-[16px]
                           font-semibold">
                        Application Entitlements
                    </h2>

                    <p class="mt-1
                           text-[12px]
                           text-[#99968e]">
                        Perubahan menentukan apakah
                        aplikasi terbuka atau terkunci
                        di customer workspace.
                    </p>
                </div>

                <button type="submit"
                    class="h-10
                       rounded-xl
                       bg-[#1d1d1b]
                       px-4
                       text-[12px]
                       font-semibold
                       text-white">
                    Save App Access
                </button>
            </div>


            <div class="divide-y
                   divide-black/[0.05]">
                @foreach ($modules as $module)
                    @php
                        $enabled = (bool) $module->organization_enabled;

                        $platformActive = $module->status === 'active';
                    @endphp

                    <div data-module-row
                        data-module-search="{{ strtolower(
                            $module->name . ' ' . $module->code . ' ' . ($module->category ?? '') . ' ' . ($module->version ?? ''),
                        ) }}"
                        class="px-6 py-5">
                        <div
                            class="flex
                               flex-col gap-5
                               xl:flex-row
                               xl:items-center">
                            {{-- APP --}}
                            <div
                                class="flex
                                   min-w-0
                                   flex-1
                                   items-start
                                   gap-4">
                                <div
                                    class="flex
                                       h-12 w-12
                                       shrink-0
                                       items-center
                                       justify-center
                                       rounded-xl
                                       bg-[#f1f0eb]
                                       text-[13px]
                                       font-bold">
                                    {{ strtoupper(substr($module->name, 0, 2)) }}
                                </div>

                                <div class="min-w-0">
                                    <div
                                        class="flex
                                           flex-wrap
                                           items-center
                                           gap-2">
                                        <h3
                                            class="text-[14px]
                                               font-semibold">
                                            {{ $module->name }}
                                        </h3>

                                        <span
                                            class="rounded-md
                                               bg-[#f3f2ee]
                                               px-2 py-1
                                               text-[10px]
                                               font-semibold
                                               text-[#77746d]">
                                            v{{ $module->version ?: '1.0.0' }}
                                        </span>

                                        @if (!$platformActive)
                                            <span
                                                class="rounded-full
                                                   bg-red-50
                                                   px-2.5 py-1
                                                   text-[10px]
                                                   font-semibold
                                                   text-red-700">
                                                Platform Inactive
                                            </span>
                                        @elseif($enabled)
                                            <span
                                                class="rounded-full
                                                   bg-emerald-50
                                                   px-2.5 py-1
                                                   text-[10px]
                                                   font-semibold
                                                   text-emerald-700">
                                                Active
                                            </span>
                                        @else
                                            <span
                                                class="rounded-full
                                                   bg-orange-50
                                                   px-2.5 py-1
                                                   text-[10px]
                                                   font-semibold
                                                   text-orange-700">
                                                Locked
                                            </span>
                                        @endif
                                    </div>

                                    <div
                                        class="mt-1
                                           text-[12px]
                                           text-[#99968e]">
                                        {{ $module->category ?: 'Application' }}

                                        ·

                                        {{ $module->code }}
                                    </div>

                                    @if ($module->description)
                                        <p
                                            class="mt-2
                                               max-w-[650px]
                                               text-[12px]
                                               leading-5
                                               text-[#77746d]">
                                            {{ $module->description }}
                                        </p>
                                    @endif
                                </div>
                            </div>


                            {{-- PRICING --}}
                            <div class="w-full
                                   xl:w-[200px]">
                                <div class="text-[11px]
                                       text-[#99968e]">
                                    Default Pricing
                                </div>

                                <div
                                    class="mt-1
                                       text-[13px]
                                       font-semibold">
                                    @if ($module->pricing_type === 'per_user')
                                        Rp
                                        {{ number_format($module->price_per_user, 0, ',', '.') }}
                                        / user
                                    @elseif($module->pricing_type === 'hybrid')
                                        Rp
                                        {{ number_format($module->fixed_price, 0, ',', '.') }}

                                        <div
                                            class="mt-1
                                               text-[11px]
                                               font-normal
                                               text-[#99968e]">
                                            +
                                            Rp
                                            {{ number_format($module->price_per_user, 0, ',', '.') }}
                                            / user
                                        </div>
                                    @else
                                        Rp
                                        {{ number_format($module->fixed_price, 0, ',', '.') }}
                                    @endif
                                </div>
                            </div>


                            {{-- EXPIRY --}}
                            <div class="w-full
                                   xl:w-[190px]">
                                <label
                                    class="mb-2 block
                                       text-[11px]
                                       font-medium
                                       text-[#77746d]">
                                    Access Expires
                                </label>

                                <input type="date" name="modules[{{ $module->id }}][expires_at]"
                                    value="{{ old(
                                        "modules.{$module->id}.expires_at",
                                        $module->organization_expires_at
                                            ? \Illuminate\Support\Carbon::parse($module->organization_expires_at)->format('Y-m-d')
                                            : '',
                                    ) }}"
                                    @disabled(!$platformActive)
                                    class="h-10 w-full
                                       rounded-xl
                                       border
                                       border-black/[0.08]
                                       bg-[#faf9f6]
                                       px-3
                                       text-[12px]
                                       disabled:cursor-not-allowed
                                       disabled:opacity-50">
                            </div>


                            {{-- ENABLE --}}
                            <div class="w-full
                                   xl:w-[150px]">
                                <input type="hidden" name="modules[{{ $module->id }}][enabled]" value="0">

                                <label
                                    class="flex h-11
                                       cursor-pointer
                                       items-center
                                       justify-between
                                       rounded-xl
                                       border
                                       border-black/[0.08]
                                       bg-[#faf9f6]
                                       px-3
                                       text-[12px]
                                       font-semibold
                                       {{ !$platformActive ? 'cursor-not-allowed opacity-50' : '' }}">
                                    <span>
                                        Enable
                                    </span>

                                    <input type="checkbox" name="modules[{{ $module->id }}][enabled]" value="1"
                                        @checked($enabled) @disabled(!$platformActive) class="h-4 w-4">
                                </label>
                            </div>
                        </div>
                    </div>
                @endforeach
            </div>


            {{-- EMPTY SEARCH --}}
            <div id="module-empty" class="hidden
                   px-6 py-16
                   text-center">
                <div class="text-[14px]
                       font-semibold">
                    Application not found.
                </div>

                <div class="mt-1
                       text-[12px]
                       text-[#99968e]">
                    Coba gunakan kata pencarian lain.
                </div>
            </div>


            {{-- FOOTER --}}
            <div
                class="flex
                   justify-end
                   border-t
                   border-black/[0.06]
                   bg-[#faf9f6]
                   px-6 py-4">
                <button type="submit"
                    class="h-11
                       rounded-xl
                       bg-[#1d1d1b]
                       px-5
                       text-[13px]
                       font-semibold
                       text-white">
                    Save App Access
                </button>
            </div>
        </div>
    </form>


    <script>
        document.addEventListener(
            'DOMContentLoaded',
            () => {
                const search =
                    document.getElementById(
                        'app-search'
                    );

                const rows = [
                    ...document.querySelectorAll(
                        '[data-module-row]'
                    ),
                ];

                const empty =
                    document.getElementById(
                        'module-empty'
                    );

                const refresh = () => {
                    const query =
                        (
                            search?.value ||
                            ''
                        )
                        .trim()
                        .toLowerCase();

                    let visible = 0;

                    rows.forEach(
                        row => {
                            const haystack =
                                row.dataset
                                .moduleSearch ||
                                '';

                            const show =
                                query === '' ||
                                haystack.includes(
                                    query
                                );

                            row.classList.toggle(
                                'hidden',
                                !show
                            );

                            if (show) {
                                visible++;
                            }
                        }
                    );

                    empty?.classList.toggle(
                        'hidden',
                        visible > 0
                    );
                };

                search?.addEventListener(
                    'input',
                    refresh
                );

                refresh();
            }
        );
    </script>

@endsection
