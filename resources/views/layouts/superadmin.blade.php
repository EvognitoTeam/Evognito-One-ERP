<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">

    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <meta name="csrf-token" content="{{ csrf_token() }}">

    <title>
        @yield('title', 'Control Center')
        - Evognito One
    </title>

    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>

<body class="bg-[#f7f6f2]">

    <div class="min-h-screen lg:flex">

        {{-- MOBILE BACKDROP --}}
        <div data-sidebar-backdrop
            class="fixed inset-0 z-40 hidden
               bg-black/30 opacity-0
               backdrop-blur-[2px]
               transition-opacity lg:hidden">
        </div>

        {{-- SIDEBAR --}}
        <aside data-sidebar
            class="ev-scrollbar fixed inset-y-0
               left-0 z-50 flex w-[264px]
               -translate-x-full
               flex-col overflow-y-auto
               border-r border-black/[0.07]
               bg-[#f0efe9]
               transition-transform
               duration-200
               lg:sticky lg:top-0
               lg:h-screen
               lg:translate-x-0">
            <div
                class="flex h-[76px]
                   shrink-0 items-center
                   justify-between
                   border-b border-black/[0.06]
                   px-5">
                <a href="{{ route('superadmin.dashboard') }}" class="flex items-center gap-3">
                    <div
                        class="flex h-9 w-9
                           items-center justify-center
                           rounded-xl
                           bg-[#1d1d1b]
                           text-xs font-bold text-white">
                        E
                    </div>

                    <div>
                        <div class="text-[14px]
                               font-semibold">
                            Evognito One
                        </div>

                        <div
                            class="text-[9px]
                               font-semibold uppercase
                               tracking-[.11em]
                               text-[#99968e]">
                            Control Center
                        </div>
                    </div>
                </a>

                <button type="button" data-sidebar-close
                    class="flex h-8 w-8
                       cursor-pointer items-center
                       justify-center rounded-lg
                       hover:bg-black/[0.05]
                       lg:hidden">
                    ✕
                </button>
            </div>

            <div class="flex-1 px-3 py-5">

                <div
                    class="mb-2 px-3
           text-[9px] font-bold
           uppercase tracking-[.13em]
           text-[#aaa69e]">
                    Platform
                </div>

                <nav class="space-y-1">

                    <a href="{{ route('superadmin.dashboard') }}"
                        class="flex items-center gap-3
               rounded-xl px-3 py-2.5
               text-[12px] font-medium
               {{ request()->routeIs('superadmin.dashboard')
                   ? 'bg-white text-[#1d1d1b] shadow-sm'
                   : 'text-[#6f6c65] hover:bg-black/[0.035]' }}">
                        <span
                            class="h-2 w-2 rounded-full
                   {{ request()->routeIs('superadmin.dashboard') ? 'bg-[#e8742a]' : 'bg-[#c6c3bb]' }}"></span>

                        Overview
                    </a>

                    <a href="{{ route('superadmin.organizations.index') }}"
                        class="flex items-center gap-3
               rounded-xl px-3 py-2.5
               text-[12px] font-medium
               {{ request()->routeIs('superadmin.organizations.*')
                   ? 'bg-white text-[#1d1d1b] shadow-sm'
                   : 'text-[#6f6c65] hover:bg-black/[0.035]' }}">
                        <span
                            class="h-2 w-2 rounded-full
                   {{ request()->routeIs('superadmin.organizations.*') ? 'bg-[#e8742a]' : 'bg-[#c6c3bb]' }}"></span>

                        Organizations
                    </a>

                    <a href="{{ route('superadmin.users.index') }}"
                        class="flex items-center gap-3
           rounded-xl px-3 py-2.5
           text-[13px] font-medium
           {{ request()->routeIs('superadmin.users.*')
               ? 'bg-white text-[#1d1d1b] shadow-sm'
               : 'text-[#6f6c65] hover:bg-black/[0.035]' }}">
                        <span
                            class="h-2 w-2 rounded-full
               {{ request()->routeIs('superadmin.users.*') ? 'bg-[#e8742a]' : 'bg-[#c6c3bb]' }}"></span>

                        Users
                    </a>

                    <a href="{{ route('superadmin.modules.index') }}"
                        class="flex items-center gap-3
           rounded-xl px-3 py-2.5
           text-[12px] font-medium
           {{ request()->routeIs('superadmin.modules.*')
               ? 'bg-white text-[#1d1d1b] shadow-sm'
               : 'text-[#6f6c65] hover:bg-black/[0.035]' }}">
                        <span
                            class="h-2 w-2 rounded-full
               {{ request()->routeIs('superadmin.modules.*') ? 'bg-[#e8742a]' : 'bg-[#c6c3bb]' }}"></span>

                        Modules
                    </a>
                </nav>

                <div
                    class="mb-2 mt-8 px-3
           text-[9px] font-bold
           uppercase tracking-[.13em]
           text-[#aaa69e]">
                    Onboarding
                </div>

                <nav class="space-y-1">

                    <a href="{{ route('superadmin.registration-invites.index') }}"
                        class="flex items-center gap-3
               rounded-xl px-3 py-2.5
               text-[12px] font-medium
               {{ request()->routeIs('superadmin.registration-invites.*')
                   ? 'bg-white text-[#1d1d1b] shadow-sm'
                   : 'text-[#6f6c65] hover:bg-black/[0.035]' }}">
                        <span
                            class="h-2 w-2 rounded-full
                   {{ request()->routeIs('superadmin.registration-invites.*') ? 'bg-[#e8742a]' : 'bg-[#c6c3bb]' }}"></span>

                        Registration Invites
                    </a>

                    <a href="{{ route('superadmin.registration-requests.index') }}"
                        class="flex items-center gap-3
               rounded-xl px-3 py-2.5
               text-[12px] font-medium
               {{ request()->routeIs('superadmin.registration-requests.*')
                   ? 'bg-white text-[#1d1d1b] shadow-sm'
                   : 'text-[#6f6c65] hover:bg-black/[0.035]' }}">
                        <span
                            class="h-2 w-2 rounded-full
                   {{ request()->routeIs('superadmin.registration-requests.*') ? 'bg-[#e8742a]' : 'bg-[#c6c3bb]' }}"></span>

                        Registration Requests
                    </a>

                </nav>

                <div
                    class="mb-2 mt-8 px-3
                       text-[9px] font-bold
                       uppercase tracking-[.13em]
                       text-[#aaa69e]">
                    Commercial
                </div>

                <nav class="space-y-1">
                    <a href="{{ route('superadmin.plans.index') }}"
                        class="flex items-center gap-3
           rounded-xl px-3 py-2.5
           text-[13px] font-medium
           {{ request()->routeIs('superadmin.plans.*')
               ? 'bg-white text-[#1d1d1b] shadow-sm'
               : 'text-[#6f6c65] hover:bg-black/[0.035]' }}">
                        <span
                            class="h-2 w-2 rounded-full
               {{ request()->routeIs('superadmin.plans.*') ? 'bg-[#e8742a]' : 'bg-[#c6c3bb]' }}"></span>

                        Plans & Pricing
                    </a>
                    <a href="{{ route('superadmin.subscriptions.index') }}"
                        class="flex items-center gap-3
           rounded-xl px-3 py-2.5
           text-[13px] font-medium
           {{ request()->routeIs('superadmin.subscriptions.*')
               ? 'bg-white text-[#1d1d1b] shadow-sm'
               : 'text-[#6f6c65] hover:bg-black/[0.035]' }}">
                        <span
                            class="h-2 w-2 rounded-full
               {{ request()->routeIs('superadmin.subscriptions.*') ? 'bg-[#e8742a]' : 'bg-[#c6c3bb]' }}"></span>

                        Subscriptions
                    </a>

                    <a href="{{ route('superadmin.invoices.index') }}"
                        class="flex items-center gap-3
           rounded-xl px-3 py-2.5
           text-[13px] font-medium
           {{ request()->routeIs('superadmin.invoices.*')
               ? 'bg-white text-[#1d1d1b] shadow-sm'
               : 'text-[#6f6c65] hover:bg-black/[0.035]' }}">
                        <span
                            class="h-2 w-2 rounded-full
               {{ request()->routeIs('superadmin.invoices.*') ? 'bg-[#e8742a]' : 'bg-[#c6c3bb]' }}"></span>

                        Invoices
                    </a>

                    <a href="{{ route('superadmin.payments.index') }}"
                        class="flex items-center gap-3
           rounded-xl px-3 py-2.5
           text-[13px] font-medium
           {{ request()->routeIs('superadmin.payments.*')
               ? 'bg-white text-[#1d1d1b] shadow-sm'
               : 'text-[#6f6c65] hover:bg-black/[0.035]' }}">
                        <span
                            class="h-2 w-2 rounded-full
               {{ request()->routeIs('superadmin.payments.*') ? 'bg-[#e8742a]' : 'bg-[#c6c3bb]' }}"></span>

                        Payments
                    </a>
                </nav>

                <div
                    class="mb-2 mt-8 px-3
                       text-[9px] font-bold
                       uppercase tracking-[.13em]
                       text-[#aaa69e]">
                    System
                </div>

                <nav class="space-y-1">
                    <a href="{{ route('superadmin.integrations.index') }}"
                        class="flex items-center gap-3
           rounded-xl px-3 py-2.5
           text-[13px] font-medium
           {{ request()->routeIs('superadmin.integrations.*')
               ? 'bg-white text-[#1d1d1b] shadow-sm'
               : 'text-[#6f6c65] hover:bg-black/[0.035]' }}">
                        <span
                            class="h-2 w-2 rounded-full
               {{ request()->routeIs('superadmin.integrations.*') ? 'bg-[#e8742a]' : 'bg-[#c6c3bb]' }}"></span>

                        Integrations
                    </a>

                    <a href="{{ route('superadmin.audit-logs.index') }}"
                        class="flex items-center gap-3
           rounded-xl px-3 py-2.5
           text-[13px] font-medium
           {{ request()->routeIs('superadmin.audit-logs.*')
               ? 'bg-white text-[#1d1d1b] shadow-sm'
               : 'text-[#6f6c65] hover:bg-black/[0.035]' }}">
                        <span
                            class="h-2 w-2 rounded-full
               {{ request()->routeIs('superadmin.audit-logs.*') ? 'bg-[#e8742a]' : 'bg-[#c6c3bb]' }}"></span>

                        Audit Logs
                    </a>

                    <a href="{{ route('superadmin.platform-settings.index') }}"
                        class="flex items-center gap-3
           rounded-xl px-3 py-2.5
           text-[13px] font-medium
           {{ request()->routeIs('superadmin.platform-settings.*')
               ? 'bg-white text-[#1d1d1b] shadow-sm'
               : 'text-[#6f6c65] hover:bg-black/[0.035]' }}">
                        <span
                            class="h-2 w-2 rounded-full
               {{ request()->routeIs('superadmin.platform-settings.*') ? 'bg-[#e8742a]' : 'bg-[#c6c3bb]' }}"></span>

                        Platform Settings
                    </a>
                </nav>
            </div>

            <div class="border-t border-black/[0.06]
                   p-3">
                <div class="rounded-xl bg-white/60
                       p-3">
                    <div class="flex items-center gap-3">
                        <div
                            class="flex h-9 w-9
                               shrink-0 items-center
                               justify-center
                               rounded-full
                               bg-[#1d1d1b]
                               text-[10px] font-bold
                               text-white">
                            {{ strtoupper(substr(auth()->user()->name, 0, 2)) }}
                        </div>

                        <div class="min-w-0 flex-1">
                            <div class="truncate text-[11px]
                                   font-semibold">
                                {{ auth()->user()->name }}
                            </div>

                            <div class="truncate text-[9px]
                                   text-[#96928a]">
                                Platform Superadmin
                            </div>
                        </div>
                    </div>

                    <form action="{{ route('logout') }}" method="POST" class="mt-3">
                        @csrf

                        <button type="submit"
                            class="w-full cursor-pointer
                               rounded-lg
                               border border-black/[0.07]
                               bg-white px-3 py-2
                               text-[10px] font-semibold
                               text-[#68655e]
                               transition
                               hover:bg-[#f8f7f3]">
                            Sign out
                        </button>
                    </form>
                </div>
            </div>
        </aside>

        {{-- CONTENT --}}
        <div class="min-w-0 flex-1">

            {{-- TOPBAR --}}
            <header
                class="sticky top-0 z-30
                   flex h-[76px]
                   items-center justify-between
                   border-b border-black/[0.06]
                   bg-[#f7f6f2]/90
                   px-5 backdrop-blur-xl
                   sm:px-7 lg:px-9">
                <div class="flex items-center gap-3">
                    <button type="button" data-sidebar-open
                        class="flex h-9 w-9
                           cursor-pointer
                           items-center justify-center
                           rounded-xl
                           border border-black/[0.08]
                           bg-white
                           lg:hidden">
                        <svg class="h-4 w-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                            <path d="M4 7h16" />
                            <path d="M4 12h16" />
                            <path d="M4 17h16" />
                        </svg>
                    </button>

                    <div>
                        <div
                            class="text-[10px]
                               font-medium
                               text-[#96928b]">
                            Evognito Platform
                        </div>

                        <div class="text-[13px]
                               font-semibold">
                            @yield('page_name', 'Overview')
                        </div>
                    </div>
                </div>

                <div class="flex items-center gap-2">
                    <a href="{{ url('/') }}" target="_blank"
                        class="hidden rounded-xl
                           border border-black/[0.07]
                           bg-white px-3.5 py-2.5
                           text-[10px] font-semibold
                           text-[#68655f]
                           transition
                           hover:bg-[#f4f3ef]
                           sm:block">
                        View website ↗
                    </a>

                    <button type="button"
                        class="relative flex h-9 w-9
                           items-center justify-center
                           rounded-xl
                           border border-black/[0.07]
                           bg-white">
                        <svg class="h-4 w-4" fill="none" stroke="currentColor" stroke-width="1.7"
                            viewBox="0 0 24 24">
                            <path d="M18 8a6 6 0 0 0-12 0c0 7-3 7-3 9h18c0-2-3-2-3-9" />
                            <path d="M13.73 21a2 2 0 0 1-3.46 0" />
                        </svg>

                        <span
                            class="absolute right-2 top-2
                               h-1.5 w-1.5
                               rounded-full
                               bg-[#e8742a]"></span>
                    </button>

                    <div
                        class="flex h-9 w-9
                           items-center justify-center
                           rounded-full
                           bg-[#1d1d1b]
                           text-[9px] font-bold
                           text-white">
                        {{ strtoupper(substr(auth()->user()->name, 0, 2)) }}
                    </div>
                </div>
            </header>

            <main
                class="mx-auto w-full
                   max-w-[1500px]
                   px-5 py-7
                   sm:px-7 lg:px-9 lg:py-9">
                @yield('content')
            </main>
        </div>
    </div>

</body>

</html>
