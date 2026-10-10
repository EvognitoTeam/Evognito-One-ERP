<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title', 'Evognito One')</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    <style>[x-cloak]{display:none!important}</style>
    @stack('styles')
</head>
@php
    $currentUser = auth()->user();
    $currentOrganization = $currentOrganization ?? $organization ?? null;
    $availableOrganizations = collect($availableOrganizations ?? []);
    $organizationHomeUrl = $currentOrganization?->slug ? url('/'.$currentOrganization->slug) : url('/');
    $userName = $currentUser?->name ?? $currentUser?->email ?? __('workspace.user');
    $userInitial = mb_strtoupper(mb_substr($userName, 0, 1));
@endphp
<body class="min-h-screen bg-[#f7f6f2] font-sans text-[#252421] antialiased">
<div x-data="{ organizationOpen:false, profileOpen:false }" class="min-h-screen">
    <header class="sticky top-0 z-[80] border-b border-black/[0.06] bg-white/95 backdrop-blur-xl">
        <div class="mx-auto flex h-[64px] w-full max-w-[1800px] items-center justify-between gap-3 px-4 sm:px-5 lg:px-7">
            <div class="flex min-w-0 items-center gap-3">
                <a href="{{ $organizationHomeUrl }}" class="flex shrink-0 items-center gap-2.5">
                    <div class="flex h-9 w-9 items-center justify-center rounded-xl bg-[#252421] text-[13px] font-bold text-white shadow-sm">E</div>
                    <div class="hidden sm:block">
                        <div class="text-[13px] font-bold tracking-[-.025em] text-[#252421]">Evognito One</div>
                        <div class="mt-0.5 text-[8px] font-semibold uppercase tracking-[.12em] text-[#aaa79f]">{{ __('workspace.platform') }}</div>
                    </div>
                </a>

                <div class="hidden h-7 w-px bg-black/[0.07] sm:block"></div>

                @if($currentOrganization)
                    <div class="relative min-w-0">
                        <button
                            type="button"
                            @click="organizationOpen=!organizationOpen; profileOpen=false"
                            @click.outside="organizationOpen=false"
                            class="flex max-w-[260px] items-center gap-2 rounded-xl px-2.5 py-2 text-left transition hover:bg-[#f6f5f1]"
                        >
                            <div class="flex h-7 w-7 shrink-0 items-center justify-center rounded-lg bg-blue-50 text-[10px] font-bold text-blue-700">
                                {{ mb_strtoupper(mb_substr($currentOrganization->name ?? 'O', 0, 1)) }}
                            </div>
                            <div class="hidden min-w-0 md:block">
                                <div class="truncate text-[10px] font-semibold text-[#aaa79f]">{{ __('workspace.organization') }}</div>
                                <div class="max-w-[170px] truncate text-[11px] font-semibold text-[#44413c]">{{ $currentOrganization->name ?? $currentOrganization->slug }}</div>
                            </div>
                            <svg xmlns="http://www.w3.org/2000/svg" class="hidden h-3.5 w-3.5 shrink-0 text-[#aaa79f] transition md:block" :class="{'rotate-180':organizationOpen}" viewBox="0 0 20 20" fill="currentColor"><path fill-rule="evenodd" d="M5.23 7.21a.75.75 0 011.06.02L10 11.168l3.71-3.938a.75.75 0 111.08 1.04l-4.25 4.51a.75.75 0 01-1.08 0l-4.25-4.51a.75.75 0 01.02-1.06z" clip-rule="evenodd"/></svg>
                        </button>

                        <div x-show="organizationOpen" x-cloak x-transition.origin.top.left class="absolute left-0 z-[120] mt-2 w-[290px] overflow-hidden rounded-2xl border border-black/[0.07] bg-white p-2 shadow-[0_18px_55px_rgba(0,0,0,.12)]">
                            <div class="px-3 pb-2 pt-1 text-[9px] font-bold uppercase tracking-[.09em] text-[#aaa79f]">{{ __('workspace.switch_organization') }}</div>
                            <a href="{{ $organizationHomeUrl }}" class="flex items-center gap-3 rounded-xl bg-[#f6f5f1] px-3 py-2.5">
                                <div class="flex h-8 w-8 shrink-0 items-center justify-center rounded-lg bg-[#252421] text-[10px] font-bold text-white">{{ mb_strtoupper(mb_substr($currentOrganization->name ?? 'O', 0, 1)) }}</div>
                                <div class="min-w-0">
                                    <div class="truncate text-[11px] font-semibold text-[#44413c]">{{ $currentOrganization->name ?? $currentOrganization->slug }}</div>
                                    <div class="mt-0.5 text-[8px] font-medium text-emerald-600">{{ __('workspace.current_organization') }}</div>
                                </div>
                            </a>

                            @foreach($availableOrganizations as $availableOrganization)
                                @continue((int)$availableOrganization->id === (int)$currentOrganization->id)
                                <a href="{{ url('/'.$availableOrganization->slug) }}" class="mt-1 flex items-center gap-3 rounded-xl px-3 py-2.5 transition hover:bg-[#faf9f6]">
                                    <div class="flex h-8 w-8 shrink-0 items-center justify-center rounded-lg bg-blue-50 text-[10px] font-bold text-blue-700">{{ mb_strtoupper(mb_substr($availableOrganization->name ?? 'O', 0, 1)) }}</div>
                                    <div class="min-w-0 truncate text-[11px] font-semibold text-[#55524c]">{{ $availableOrganization->name ?? $availableOrganization->slug }}</div>
                                </a>
                            @endforeach
                        </div>
                    </div>
                @endif
            </div>

            <div class="hidden max-w-[440px] flex-1 lg:block">
                <div class="relative">
                    <svg xmlns="http://www.w3.org/2000/svg" class="absolute left-3 top-1/2 h-4 w-4 -translate-y-1/2 text-[#aaa79f]" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.8"><circle cx="11" cy="11" r="7"/><path d="m20 20-3.5-3.5"/></svg>
                    <input type="search" placeholder="{{ __('workspace.search_placeholder') }}" class="h-9 w-full rounded-xl border border-black/[0.06] bg-[#f8f7f4] pl-9 pr-3 text-[10px] outline-none transition placeholder:text-[#aaa79f] focus:border-blue-200 focus:bg-white focus:ring-2 focus:ring-blue-50">
                </div>
            </div>

            <div class="flex shrink-0 items-center gap-1.5">
                @if($currentOrganization)
                    <a href="{{ $organizationHomeUrl }}" title="{{ __('workspace.apps') }}" class="flex h-9 w-9 items-center justify-center rounded-xl border border-black/[0.06] bg-white text-[#77746d] transition hover:bg-[#f6f5f1] hover:text-[#252421]">
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8"><rect x="4" y="4" width="6" height="6" rx="1"/><rect x="14" y="4" width="6" height="6" rx="1"/><rect x="4" y="14" width="6" height="6" rx="1"/><rect x="14" y="14" width="6" height="6" rx="1"/></svg>
                    </a>
                @endif

                <x-language-switcher />

                <button type="button" title="{{ __('common.notifications') }}" class="relative flex h-9 w-9 items-center justify-center rounded-xl border border-black/[0.06] bg-white text-[#77746d] transition hover:bg-[#f6f5f1] hover:text-[#252421]">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8"><path d="M18 8a6 6 0 0 0-12 0c0 7-3 7-3 9h18c0-2-3-2-3-9"/><path d="M13.73 21a2 2 0 0 1-3.46 0"/></svg>
                </button>

                <div class="relative">
                    <button type="button" @click="profileOpen=!profileOpen; organizationOpen=false" @click.outside="profileOpen=false" class="flex h-9 items-center gap-2 rounded-xl px-1.5 transition hover:bg-[#f6f5f1]">
                        <div class="flex h-8 w-8 items-center justify-center rounded-xl bg-[#252421] text-[10px] font-bold text-white">{{ $userInitial }}</div>
                        <div class="hidden max-w-[130px] text-left xl:block">
                            <div class="truncate text-[10px] font-semibold text-[#44413c]">{{ $userName }}</div>
                            <div class="mt-0.5 truncate text-[8px] text-[#aaa79f]">{{ $currentUser?->email ?? __('workspace.user') }}</div>
                        </div>
                    </button>

                    <div x-show="profileOpen" x-cloak x-transition.origin.top.right class="absolute right-0 z-[120] mt-2 w-[245px] overflow-hidden rounded-2xl border border-black/[0.07] bg-white p-2 shadow-[0_18px_55px_rgba(0,0,0,.12)]">
                        <div class="border-b border-black/[0.06] px-3 pb-3 pt-2">
                            <div class="truncate text-[11px] font-semibold text-[#44413c]">{{ $userName }}</div>
                            <div class="mt-1 truncate text-[9px] text-[#99968e]">{{ $currentUser?->email }}</div>
                        </div>

                        @if(\Illuminate\Support\Facades\Route::has('profile.edit'))
                            <a href="{{ route('profile.edit') }}" class="mt-2 block rounded-xl px-3 py-2.5 text-[10px] font-semibold text-[#55524c] transition hover:bg-[#faf9f6]">{{ __('common.account_settings') }}</a>
                        @endif

                        @if($currentUser?->is_super_admin && \Illuminate\Support\Facades\Route::has('superadmin.dashboard'))
                            <a href="{{ route('superadmin.dashboard') }}" class="block rounded-xl px-3 py-2.5 text-[10px] font-semibold text-[#55524c] transition hover:bg-[#faf9f6]">{{ __('workspace.superadmin') }}</a>
                        @endif

                        <div class="my-2 h-px bg-black/[0.06]"></div>
                        <form method="POST" action="{{ \Illuminate\Support\Facades\Route::has('logout') ? route('logout') : url('/logout') }}">
                            @csrf
                            <button type="submit" class="w-full rounded-xl px-3 py-2.5 text-left text-[10px] font-semibold text-red-600 transition hover:bg-red-50">{{ __('common.sign_out') }}</button>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </header>

    <main class="mx-auto w-full max-w-[1800px] px-4 py-6 sm:px-5 lg:px-7 lg:py-8">
        @yield('content')
    </main>

    <footer class="mx-auto w-full max-w-[1800px] px-4 pb-6 text-center text-[9px] font-medium text-[#aaa79f] sm:px-5 lg:px-7">
        © {{ date('Y') }} Evognito One · {{ __('workspace.platform') }}
    </footer>
</div>
@stack('scripts')
</body>
</html>
