@extends('layouts.workspace')

@section('title', 'Apps')

@section('content')

    @php
        /*
    |--------------------------------------------------------------------------
    | Workspace Module State
    |--------------------------------------------------------------------------
    */

        $activeAppsCount = $modules->filter(fn($module) => (bool) ($module->workspace_enabled ?? false))->count();

        $lockedAppsCount = $modules->count() - $activeAppsCount;

        $totalAppsCount = $modules->count();

        $availableCategories = $modules->pluck('category')->filter()->unique()->sort()->values();
    @endphp


    {{-- ========================================================= --}}
    {{-- FLASH                                                     --}}
    {{-- ========================================================= --}}

    @if (session('success'))
        <div
            class="mb-5
               rounded-xl
               border border-emerald-200
               bg-emerald-50
               px-4 py-3
               text-[12px]
               text-emerald-700">
            {{ session('success') }}
        </div>
    @endif


    @if (session('warning'))
        <div
            class="mb-5
               rounded-xl
               border border-amber-200
               bg-amber-50
               px-4 py-3
               text-[12px]
               text-amber-700">
            {{ session('warning') }}
        </div>
    @endif


    @if (session('error'))
        <div
            class="mb-5
               rounded-xl
               border border-red-200
               bg-red-50
               px-4 py-3
               text-[12px]
               text-red-700">
            {{ session('error') }}
        </div>
    @endif


    <div x-data="workspaceApps()">

        {{-- ===================================================== --}}
        {{-- HEADER                                                --}}
        {{-- ===================================================== --}}

        <section
            class="flex flex-col gap-5
               lg:flex-row
               lg:items-end
               lg:justify-between">
            <div>
                <div
                    class="text-[11px]
                       font-bold
                       uppercase
                       tracking-[.1em]
                       text-[#e8742a]">
                    Workspace
                </div>

                <h1
                    class="mt-2
                       text-[30px]
                       font-semibold
                       tracking-[-.045em]
                       sm:text-[34px]">
                    All Apps
                </h1>

                <p
                    class="mt-2
                       max-w-[650px]
                       text-[13px]
                       leading-6
                       text-[#85827b]">
                    Kelola seluruh aktivitas
                    {{ $organization->name }}
                    dari satu workspace.
                </p>
            </div>


            {{-- SEARCH --}}
            <div class="relative
                   w-full
                   lg:w-[330px]">
                <svg class="pointer-events-none
                       absolute
                       left-3.5
                       top-1/2
                       h-4 w-4
                       -translate-y-1/2
                       text-[#aaa79f]"
                    viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8">
                    <circle cx="11" cy="11" r="7" />

                    <path d="m20 20-3.5-3.5" />
                </svg>

                <input type="search" x-model="search" placeholder="Search applications..."
                    class="h-11 w-full
                       rounded-xl
                       border border-black/[0.08]
                       bg-white
                       pl-10 pr-4
                       text-[12px]
                       outline-none
                       transition
                       placeholder:text-[#aaa79f]
                       focus:border-black/[0.16]">
            </div>
        </section>


        {{-- ===================================================== --}}
        {{-- SUMMARY                                               --}}
        {{-- ===================================================== --}}

        <section class="mt-7
               grid gap-3
               sm:grid-cols-3">
            {{-- ALL --}}
            <button type="button" @click="filter = 'all'"
                :class="filter === 'all'
                    ?
                    'border-[#252421] bg-[#252421] text-white' :
                    'border-black/[0.07] bg-white text-[#292825]'"
                class="rounded-2xl
                   border
                   p-5
                   text-left
                   transition">
                <div :class="filter === 'all'
                    ?
                    'text-white/50' :
                    'text-[#99968e]'"
                    class="text-[11px]">
                    All Applications
                </div>

                <div
                    class="mt-3
                       text-[27px]
                       font-semibold
                       tracking-[-.04em]">
                    {{ number_format($totalAppsCount) }}
                </div>
            </button>


            {{-- ACTIVE --}}
            <button type="button" @click="filter = 'active'"
                :class="filter === 'active'
                    ?
                    'border-emerald-600 bg-emerald-600 text-white' :
                    'border-black/[0.07] bg-white text-[#292825]'"
                class="rounded-2xl
                   border
                   p-5
                   text-left
                   transition">
                <div :class="filter === 'active'
                    ?
                    'text-white/60' :
                    'text-[#99968e]'"
                    class="text-[11px]">
                    Active
                </div>

                <div
                    class="mt-3
                       text-[27px]
                       font-semibold
                       tracking-[-.04em]">
                    {{ number_format($activeAppsCount) }}
                </div>
            </button>


            {{-- LOCKED --}}
            <button type="button" @click="filter = 'locked'"
                :class="filter === 'locked'
                    ?
                    'border-orange-500 bg-orange-500 text-white' :
                    'border-black/[0.07] bg-white text-[#292825]'"
                class="rounded-2xl
                   border
                   p-5
                   text-left
                   transition">
                <div :class="filter === 'locked'
                    ?
                    'text-white/60' :
                    'text-[#99968e]'"
                    class="text-[11px]">
                    Payment Required
                </div>

                <div
                    class="mt-3
                       text-[27px]
                       font-semibold
                       tracking-[-.04em]">
                    {{ number_format($lockedAppsCount) }}
                </div>
            </button>
        </section>


        {{-- ===================================================== --}}
        {{-- FILTERS                                               --}}
        {{-- ===================================================== --}}

        <section class="mt-7
               flex gap-2
               overflow-x-auto
               pb-1">
            <button type="button" @click="category = 'all'"
                :class="category === 'all'
                    ?
                    'bg-[#252421] text-white' :
                    'border border-black/[0.07] bg-white text-[#69665f]'"
                class="shrink-0
                   rounded-full
                   px-4 py-2
                   text-[11px]
                   font-semibold
                   transition">
                All Categories
            </button>


            @foreach ($availableCategories as $categoryName)
                <button type="button"
                    @click="
                    category =
                        @js(strtolower($categoryName))
                "
                    :class="category ===
                        @js(strtolower($categoryName)) ?
                        'bg-[#252421] text-white' :
                        'border border-black/[0.07] bg-white text-[#69665f]'"
                    class="shrink-0
                       rounded-full
                       px-4 py-2
                       text-[11px]
                       font-semibold
                       transition">
                    {{ $categoryName }}
                </button>
            @endforeach
        </section>


        {{-- ===================================================== --}}
        {{-- APP GRID                                              --}}
        {{-- ===================================================== --}}

        <section class="mt-5
               grid gap-4
               sm:grid-cols-2
               xl:grid-cols-3">
            @forelse(
                            $modules
                            as $module
                        )

                @php
                    /*
                |--------------------------------------------------------------------------
                | Runtime access
                |--------------------------------------------------------------------------
                |
                | workspace_enabled berasal dari Workspace DashboardController.
                |
                */

                    $enabled = (bool) ($module->workspace_enabled ?? false);

                    $locked = !$enabled;

                    /*
                |--------------------------------------------------------------------------
                | Workspace URL
                |--------------------------------------------------------------------------
                |
                | INTERNAL
                | /{organization}/{workspace_path}
                |
                | EXTERNAL
                | entry_url
                |
                | workspace_url dibuat controller.
                |
                */

                    $targetUrl = $enabled && filled($module->workspace_url) ? $module->workspace_url : '#';

                    $moduleCategory = strtolower($module->category ?: 'other');

                    $pricingType = $module->workspace_pricing_type ?? $module->pricing_type;

                    $fixedPrice = (float) ($module->workspace_fixed_price ?? ($module->fixed_price ?? 0));

                    $pricePerUser = (float) ($module->workspace_price_per_user ?? ($module->price_per_user ?? 0));

                    $billingInterval = $module->workspace_billing_interval ?? ($module->billing_interval ?? 'monthly');

                    $moduleDescription = $module->description ?: 'Application for Evognito One.';
                @endphp


                <article
                    x-show="matches(
                    @js(strtolower($module->name)),
                    @js(strtolower($module->code)),
                    @js($moduleCategory),
                    @js($enabled ? 'active' : 'locked')
                )"
                    x-transition.opacity.duration.150ms
                    class="group
                       relative
                       overflow-hidden
                       rounded-[22px]
                       border border-black/[0.07]
                       bg-white
                       p-5
                       transition
                       hover:-translate-y-[2px]
                       hover:border-black/[0.1]
                       hover:shadow-[0_18px_60px_rgba(0,0,0,.06)]">

                    {{-- STATUS TOP --}}
                    <div
                        class="flex
                           items-start
                           justify-between
                           gap-4">
                        {{-- ICON --}}
                        <div
                            class="flex h-12 w-12
                               shrink-0
                               items-center
                               justify-center
                               rounded-[15px]
                               {{ match (strtolower($module->code)) {
                                   'hris' => 'bg-blue-50 text-blue-600',
                               
                                   'performance', 'kpi' => 'bg-purple-50 text-purple-600',
                               
                                   'lms' => 'bg-emerald-50 text-emerald-600',
                               
                                   'kansiku' => 'bg-orange-50 text-orange-600',
                               
                                   'sales', 'sales_force', 'sfa' => 'bg-teal-50 text-teal-600',
                               
                                   'sites' => 'bg-rose-50 text-rose-600',
                               
                                   default => 'bg-[#f3f2ee] text-[#57544d]',
                               } }}">
                            @if (strtolower($module->code) === 'hris')
                                <svg class="h-6 w-6" viewBox="0 0 24 24" fill="none" stroke="currentColor"
                                    stroke-width="1.7">
                                    <path d="M16 21v-2a4 4 0 0 0-4-4H6a4 4 0 0 0-4 4v2" />

                                    <circle cx="9" cy="7" r="4" />

                                    <path d="M22 21v-2a4 4 0 0 0-3-3.87" />

                                    <path d="M16 3.13a4 4 0 0 1 0 7.75" />
                                </svg>
                            @elseif(in_array(strtolower($module->code), ['sales', 'sales_force', 'sfa']))
                                <svg class="h-6 w-6" viewBox="0 0 24 24" fill="none" stroke="currentColor"
                                    stroke-width="1.7">
                                    <path d="M3 3v18h18" />

                                    <path d="m7 15 4-4 3 3 5-7" />
                                </svg>
                            @elseif(strtolower($module->code) === 'lms')
                                <svg class="h-6 w-6" viewBox="0 0 24 24" fill="none" stroke="currentColor"
                                    stroke-width="1.7">
                                    <path d="m3 10 9-5 9 5-9 5Z" />

                                    <path d="m7 12.2v5L12 20l5-2.8v-5" />
                                </svg>
                            @else
                                <svg class="h-6 w-6" viewBox="0 0 24 24" fill="none" stroke="currentColor"
                                    stroke-width="1.7">
                                    <rect x="3" y="3" width="7" height="7" rx="2" />

                                    <rect x="14" y="3" width="7" height="7" rx="2" />

                                    <rect x="3" y="14" width="7" height="7" rx="2" />

                                    <rect x="14" y="14" width="7" height="7" rx="2" />
                                </svg>
                            @endif
                        </div>


                        {{-- STATUS --}}
                        @if ($enabled)
                            <span
                                class="inline-flex
                                   items-center
                                   gap-1.5
                                   rounded-full
                                   bg-emerald-50
                                   px-2.5 py-1
                                   text-[10px]
                                   font-semibold
                                   text-emerald-700">
                                <span
                                    class="h-1.5 w-1.5
                                       rounded-full
                                       bg-emerald-500"></span>

                                Active
                            </span>
                        @else
                            <span
                                class="inline-flex
                                   items-center
                                   gap-1.5
                                   rounded-full
                                   bg-orange-50
                                   px-2.5 py-1
                                   text-[10px]
                                   font-semibold
                                   text-orange-700">
                                <svg class="h-3 w-3" viewBox="0 0 24 24" fill="none" stroke="currentColor"
                                    stroke-width="2">
                                    <rect x="5" y="10" width="14" height="10" rx="2" />

                                    <path d="M8 10V7a4 4 0 0 1 8 0v3" />
                                </svg>

                                Payment Required
                            </span>
                        @endif
                    </div>


                    {{-- BODY --}}
                    <div class="mt-5">
                        <div
                            class="flex
                               flex-wrap
                               items-center
                               gap-2">
                            <h2
                                class="text-[16px]
                                   font-semibold
                                   tracking-[-.025em]
                                   text-[#292825]">
                                {{ $module->name }}
                            </h2>

                            <span
                                class="rounded-md
                                   bg-[#f3f2ee]
                                   px-2 py-1
                                   font-mono
                                   text-[9px]
                                   font-semibold
                                   text-[#817e77]">
                                v{{ $module->version ?: '1.0.0' }}
                            </span>
                        </div>

                        <div
                            class="mt-1
                               text-[10px]
                               font-semibold
                               uppercase
                               tracking-[.08em]
                               text-[#aaa79f]">
                            {{ $module->category ?: 'Application' }}
                        </div>

                        <p
                            class="mt-4
                               line-clamp-3
                               min-h-[60px]
                               text-[12px]
                               leading-5
                               text-[#85827b]">
                            {{ $moduleDescription }}
                        </p>
                    </div>


                    {{-- CONFIGURATION --}}
                    <div
                        class="mt-5
                           flex flex-wrap
                           items-center
                           gap-2">
                        <span
                            class="rounded-lg
                               bg-[#f6f5f1]
                               px-2.5 py-1.5
                               text-[10px]
                               font-medium
                               text-[#75726b]">
                            {{ $module->type === 'internal' ? 'Evognito One' : 'External' }}
                        </span>

                        @if ($module->type === 'internal' && filled($module->workspace_path))
                            <span
                                class="rounded-lg
                                   bg-[#f6f5f1]
                                   px-2.5 py-1.5
                                   font-mono
                                   text-[9px]
                                   font-medium
                                   text-[#75726b]">
                                /{{ trim($module->workspace_path, '/') }}
                            </span>
                        @endif
                    </div>


                    {{-- FOOTER --}}
                    <div
                        class="mt-6
                           border-t
                           border-black/[0.06]
                           pt-4">
                        @if ($enabled)
                            @if (filled($module->workspace_url))
                                <a href="{{ $targetUrl }}"
                                    @if ($module->type === 'external') target="_blank"
                                    rel="noopener noreferrer" @endif
                                    class="flex h-10
                                       w-full
                                       items-center
                                       justify-center
                                       gap-2
                                       rounded-xl
                                       bg-[#1d1d1b]
                                       px-4
                                       text-[12px]
                                       font-semibold
                                       text-white
                                       transition
                                       hover:bg-black">
                                    Open App

                                    <svg class="h-3.5 w-3.5" viewBox="0 0 24 24" fill="none" stroke="currentColor"
                                        stroke-width="2">
                                        <path d="M5 12h14" />

                                        <path d="m13 6 6 6-6 6" />
                                    </svg>
                                </a>
                            @else
                                <button type="button" disabled
                                    class="flex h-10
                                       w-full
                                       cursor-not-allowed
                                       items-center
                                       justify-center
                                       rounded-xl
                                       bg-[#f1f0ec]
                                       px-4
                                       text-[12px]
                                       font-semibold
                                       text-[#aaa79f]">
                                    Route Not Configured
                                </button>
                            @endif
                        @else
                            <button type="button" @click='openLockedApp(@js([
                                'id' => $module->id,
                            
                                'name' => $module->name,
                            
                                'version' => $module->version ?: '1.0.0',
                            
                                'category' => $module->category ?: 'Application',
                            
                                'description' => $moduleDescription,
                            
                                'pricing_type' => $pricingType,
                            
                                'fixed_price' => $fixedPrice,
                            
                                'price_per_user' => $pricePerUser,
                            
                                'billing_interval' => $billingInterval,
                            ]))'
                                class="flex h-10
                                   w-full
                                   items-center
                                   justify-center
                                   gap-2
                                   rounded-xl
                                   border border-orange-200
                                   bg-orange-50
                                   px-4
                                   text-[12px]
                                   font-semibold
                                   text-orange-700
                                   transition
                                   hover:bg-orange-100">
                                <svg class="h-3.5 w-3.5" viewBox="0 0 24 24" fill="none" stroke="currentColor"
                                    stroke-width="2">
                                    <rect x="5" y="10" width="14" height="10" rx="2" />

                                    <path d="M8 10V7a4 4 0 0 1 8 0v3" />
                                </svg>

                                Payment Required
                            </button>
                        @endif
                    </div>
                </article>


            @empty

                <div
                    class="col-span-full
                       rounded-2xl
                       border border-dashed
                       border-black/[0.1]
                       bg-white
                       px-6 py-16
                       text-center">
                    <div
                        class="mx-auto
                           flex h-12 w-12
                           items-center
                           justify-center
                           rounded-2xl
                           bg-[#f3f2ee]
                           text-[#88857d]">
                        <svg class="h-5 w-5" viewBox="0 0 24 24" fill="none" stroke="currentColor"
                            stroke-width="1.8">
                            <rect x="3" y="3" width="7" height="7" rx="2" />

                            <rect x="14" y="3" width="7" height="7" rx="2" />

                            <rect x="3" y="14" width="7" height="7" rx="2" />

                            <rect x="14" y="14" width="7" height="7" rx="2" />
                        </svg>
                    </div>

                    <div class="mt-4
                           text-[13px]
                           font-semibold">
                        No applications available.
                    </div>
                </div>
            @endforelse
        </section>


        {{-- ===================================================== --}}
        {{-- NO FILTER RESULT                                      --}}
        {{-- ===================================================== --}}

        <div x-show="visibleCount === 0" x-cloak
            class="mt-5
               rounded-2xl
               border border-dashed
               border-black/[0.1]
               bg-white
               px-6 py-14
               text-center">
            <div class="text-[13px]
                   font-semibold">
                No applications found.
            </div>

            <p class="mt-1
                   text-[11px]
                   text-[#99968e]">
                Coba ubah pencarian atau filter aplikasi.
            </p>
        </div>


        {{-- ===================================================== --}}
        {{-- LOCKED APP MODAL                                      --}}
        {{-- ===================================================== --}}

        <div x-show="lockedModal" x-cloak class="fixed
               inset-0
               z-[100]"
            @keydown.escape.window="
            lockedModal = false
        ">
            {{-- OVERLAY --}}
            <div x-show="lockedModal" x-transition.opacity @click="
                lockedModal = false
            "
                class="absolute
                   inset-0
                   bg-black/40
                   backdrop-blur-[2px]">
            </div>


            <div
                class="relative
                   flex min-h-full
                   items-center
                   justify-center
                   p-4">
                <div x-show="lockedModal" x-transition @click.stop
                    class="w-full
                       max-w-[480px]
                       overflow-hidden
                       rounded-[24px]
                       bg-white
                       shadow-[0_30px_100px_rgba(0,0,0,.18)]">
                    {{-- MODAL HEAD --}}
                    <div
                        class="flex
                           items-start
                           justify-between
                           gap-5
                           border-b
                           border-black/[0.06]
                           p-6">
                        <div class="flex
                               items-start
                               gap-4">
                            <div
                                class="flex h-11 w-11
                                   shrink-0
                                   items-center
                                   justify-center
                                   rounded-xl
                                   bg-orange-50
                                   text-orange-600">
                                <svg class="h-5 w-5" viewBox="0 0 24 24" fill="none" stroke="currentColor"
                                    stroke-width="1.8">
                                    <rect x="5" y="10" width="14" height="10" rx="2" />

                                    <path d="M8 10V7a4 4 0 0 1 8 0v3" />
                                </svg>
                            </div>

                            <div>
                                <div
                                    class="text-[10px]
                                       font-semibold
                                       uppercase
                                       tracking-[.08em]
                                       text-orange-600">
                                    Payment Required
                                </div>

                                <div
                                    class="mt-1
                                       flex
                                       items-center
                                       gap-2">
                                    <h3 class="text-[19px]
                                           font-semibold
                                           tracking-[-.03em]"
                                        x-text="
                                        selectedApp?.name
                                        || 'Application'
                                    ">
                                    </h3>

                                    <span
                                        class="rounded-md
                                           bg-[#f3f2ee]
                                           px-2 py-1
                                           font-mono
                                           text-[9px]
                                           text-[#77746d]"
                                        x-text="
                                        'v'
                                        +
                                        (
                                            selectedApp?.version
                                            || '1.0.0'
                                        )
                                    "></span>
                                </div>
                            </div>
                        </div>


                        <button type="button"
                            @click="
                            lockedModal = false
                        "
                            class="flex h-9 w-9
                               shrink-0
                               items-center
                               justify-center
                               rounded-xl
                               text-[#99968e]
                               transition
                               hover:bg-[#f3f2ee]
                               hover:text-[#292825]">
                            <svg class="h-4 w-4" viewBox="0 0 24 24" fill="none" stroke="currentColor"
                                stroke-width="2">
                                <path d="M18 6 6 18" />

                                <path d="m6 6 12 12" />
                            </svg>
                        </button>
                    </div>


                    {{-- MODAL BODY --}}
                    <div class="p-6">
                        <p class="text-[12px]
                               leading-6
                               text-[#77746d]"
                            x-text="
                            selectedApp?.description
                            || ''
                        ">
                        </p>


                        {{-- PRICE --}}
                        <div
                            class="mt-5
                               rounded-2xl
                               border border-black/[0.06]
                               bg-[#faf9f6]
                               p-5">
                            <div
                                class="text-[10px]
                                   font-semibold
                                   uppercase
                                   tracking-[.08em]
                                   text-[#99968e]">
                                Default Pricing
                            </div>


                            <template
                                x-if="
                                selectedApp
                                &&
                                (
                                    selectedApp.pricing_type
                                    === 'fixed'
                                    ||
                                    selectedApp.pricing_type
                                    === 'hybrid'
                                )
                            ">
                                <div class="mt-3">
                                    <span class="text-[12px]
                                           text-[#77746d]">
                                        Starting from
                                    </span>

                                    <div
                                        class="mt-1
                                           text-[24px]
                                           font-semibold
                                           tracking-[-.04em]">
                                        Rp
                                        <span
                                            x-text="
                                            formatCurrency(
                                                selectedApp
                                                    .fixed_price
                                            )
                                        "></span>
                                    </div>

                                    <div class="mt-1
                                           text-[10px]
                                           text-[#99968e]"
                                        x-text="
                                        '/'
                                        +
                                        billingLabel(
                                            selectedApp
                                                .billing_interval
                                        )
                                    ">
                                    </div>
                                </div>
                            </template>


                            <template
                                x-if="
                                selectedApp
                                &&
                                (
                                    selectedApp.pricing_type
                                    === 'per_user'
                                    ||
                                    selectedApp.pricing_type
                                    === 'hybrid'
                                )
                            ">
                                <div
                                    class="mt-4
                                       border-t
                                       border-black/[0.06]
                                       pt-4">
                                    <span class="text-[12px]
                                           text-[#77746d]">
                                        Per user
                                    </span>

                                    <div
                                        class="mt-1
                                           text-[17px]
                                           font-semibold">
                                        Rp
                                        <span
                                            x-text="
                                            formatCurrency(
                                                selectedApp
                                                    .price_per_user
                                            )
                                        "></span>

                                        <span
                                            class="text-[10px]
                                               font-normal
                                               text-[#99968e]">
                                            / user
                                        </span>
                                    </div>
                                </div>
                            </template>
                        </div>


                        <div
                            class="mt-5
                               rounded-xl
                               border border-blue-100
                               bg-blue-50/60
                               px-4 py-3">
                            <p
                                class="text-[11px]
                                   leading-5
                                   text-blue-700">
                                Aplikasi ini belum tersedia
                                pada subscription organization
                                Anda. Aktivasi melalui billing
                                akan kita hubungkan pada tahap
                                berikutnya.
                            </p>
                        </div>
                    </div>


                    {{-- MODAL FOOTER --}}
                    <div
                        class="flex
                           items-center
                           justify-end
                           gap-2
                           border-t
                           border-black/[0.06]
                           bg-[#faf9f6]
                           px-6 py-4">
                        <button type="button"
                            @click="
                            lockedModal = false
                        "
                            class="inline-flex
                               h-10
                               items-center
                               justify-center
                               rounded-xl
                               border border-black/[0.08]
                               bg-white
                               px-4
                               text-[12px]
                               font-semibold">
                            Close
                        </button>

                        <button type="button" disabled
                            class="inline-flex
                               h-10
                               cursor-not-allowed
                               items-center
                               justify-center
                               rounded-xl
                               bg-[#d7d5cf]
                               px-4
                               text-[12px]
                               font-semibold
                               text-white">
                            Activate App
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </div>


    @once
        <script>
            function workspaceApps() {
                return {
                    search: '',
                    filter: 'all',
                    category: 'all',

                    lockedModal: false,
                    selectedApp: null,

                    visibleCount: {{ $totalAppsCount }},


                    matches(
                        name,
                        code,
                        moduleCategory,
                        status
                    ) {
                        const query =
                            this.search
                            .trim()
                            .toLowerCase();

                        const searchMatch = !query ||
                            name.includes(query) ||
                            code.includes(query) ||
                            moduleCategory
                            .includes(query);


                        const statusMatch =
                            this.filter === 'all' ||
                            this.filter === status;


                        const categoryMatch =
                            this.category === 'all' ||
                            this.category ===
                            moduleCategory;


                        return (
                            searchMatch &&
                            statusMatch &&
                            categoryMatch
                        );
                    },


                    openLockedApp(app) {
                        this.selectedApp = app;

                        this.lockedModal = true;
                    },


                    formatCurrency(value) {
                        const number =
                            Number(value || 0);

                        return new Intl
                            .NumberFormat(
                                'id-ID', {
                                    maximumFractionDigits: 0,
                                }
                            )
                            .format(number);
                    },


                    billingLabel(interval) {
                        if (
                            interval === 'yearly'
                        ) {
                            return 'year';
                        }

                        return 'month';
                    },
                };
            }
        </script>
    @endonce

@endsection
