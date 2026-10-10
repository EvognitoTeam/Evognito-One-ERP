@extends('layouts.superadmin')

@section('title', 'Modules')
@section('page_name', 'Modules')

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
                Platform Apps
            </div>

            <h1 class="mt-2 text-[30px]
                   font-semibold
                   tracking-[-.045em]">
                Modules
            </h1>

            <p class="mt-2 text-[12px]
                   text-[#85827b]">
                Kelola aplikasi dan module
                ecosystem Evognito One.
            </p>
        </div>

        <a href="{{ route('superadmin.modules.create') }}"
            class="inline-flex h-11
               items-center gap-2
               rounded-xl
               bg-[#1d1d1b]
               px-4 text-[10px]
               font-semibold text-white">
            + New Module
        </a>
    </div>

    @if (session('success'))
        <div
            class="mt-5 rounded-xl
               border border-emerald-200
               bg-emerald-50
               px-4 py-3
               text-[10px]
               text-emerald-700">
            {{ session('success') }}
        </div>
    @endif

    <form method="GET"
        class="mt-7 grid gap-3
           rounded-2xl
           border border-black/[0.07]
           bg-white p-4
           md:grid-cols-[1fr_180px_180px_auto]
           ev-card-shadow">
        <div>
            <label
                class="mb-2 block
                   text-[9px] font-bold
                   uppercase tracking-[.1em]
                   text-[#99968e]">
                Search
            </label>

            <input type="text" name="search" value="{{ $search }}" placeholder="Module name, code..."
                class="h-10 w-full
                   rounded-xl
                   border border-black/[0.08]
                   bg-[#faf9f6]
                   px-3 text-[10px]">
        </div>

        <div>
            <label
                class="mb-2 block
                   text-[9px] font-bold
                   uppercase tracking-[.1em]
                   text-[#99968e]">
                Type
            </label>

            <select name="type"
                class="h-10 w-full
                   rounded-xl
                   border border-black/[0.08]
                   bg-[#faf9f6]
                   px-3 text-[10px]">
                <option value="">
                    All Types
                </option>

                <option value="internal" @selected($type === 'internal')>
                    Internal
                </option>

                <option value="external" @selected($type === 'external')>
                    External
                </option>
            </select>
        </div>

        <div>
            <label
                class="mb-2 block
                   text-[9px] font-bold
                   uppercase tracking-[.1em]
                   text-[#99968e]">
                Status
            </label>

            <select name="status"
                class="h-10 w-full
                   rounded-xl
                   border border-black/[0.08]
                   bg-[#faf9f6]
                   px-3 text-[10px]">
                <option value="">
                    All Status
                </option>

                <option value="active" @selected($status === 'active')>
                    Active
                </option>

                <option value="inactive" @selected($status === 'inactive')>
                    Inactive
                </option>
            </select>
        </div>

        <div class="flex items-end gap-2">
            <button
                class="h-10 cursor-pointer
                   rounded-xl
                   bg-[#1d1d1b]
                   px-4 text-[9px]
                   font-semibold text-white">
                Filter
            </button>

            <a href="{{ route('superadmin.modules.index') }}"
                class="flex h-10
                   items-center
                   rounded-xl
                   border border-black/[0.08]
                   px-4 text-[9px]
                   font-semibold">
                Reset
            </a>
        </div>
    </form>

    <div class="mt-5 grid gap-3
           sm:grid-cols-2
           xl:grid-cols-3">
        @forelse($modules as $module)
            <article
                class="rounded-2xl
                   border border-black/[0.07]
                   bg-white p-5
                   ev-card-shadow">
                <div class="flex items-start
                       justify-between">
                    <div
                        class="flex h-11 w-11
                           items-center
                           justify-center
                           rounded-xl
                           bg-[#f1f0eb]
                           text-[10px]
                           font-bold">
                        {{ strtoupper(substr($module->code, 0, 2)) }}
                    </div>

                    <span
                        class="rounded-full
                           px-2.5 py-1
                           text-[9px]
                           font-semibold
                           {{ $module->status === 'active' ? 'bg-emerald-50 text-emerald-700' : 'bg-zinc-100 text-zinc-600' }}">
                        {{ ucfirst($module->status) }}
                    </span>
                </div>

                <div class="mt-6">
                    <div class="flex items-center
                           gap-2">
                        <h2 class="text-[14px]
                               font-semibold">
                            {{ $module->name }}
                        </h2>

                        <span
                            class="rounded-full
                               bg-[#f2f1ec]
                               px-2 py-0.5
                               text-[8px]
                               uppercase
                               text-[#77736c]">
                            {{ $module->type }}
                        </span>
                    </div>

                    <div class="mt-1 text-[9px]
                           text-[#99968e]">
                        {{ $module->code }}
                        ·
                        {{ $module->category ?: 'Uncategorized' }}
                    </div>
                </div>

                <p
                    class="mt-4 min-h-[42px]
                       text-[10px]
                       leading-5
                       text-[#85827b]">
                    {{ $module->description ?: 'No description.' }}
                </p>

                <div class="mt-5 rounded-xl
                       bg-[#faf9f6]
                       p-4">
                    <div class="flex items-center
                           justify-between">
                        <div>
                            <div
                                class="text-[8px]
                                   uppercase
                                   tracking-[.08em]
                                   text-[#99968e]">
                                Default Pricing
                            </div>

                            <div
                                class="mt-1
                                   text-[11px]
                                   font-semibold">
                                @if ($module->pricing_type === 'per_user')
                                    Rp
                                    {{ number_format($module->price_per_user, 0, ',', '.') }}
                                    / user
                                @elseif($module->pricing_type === 'hybrid')
                                    Rp
                                    {{ number_format($module->fixed_price, 0, ',', '.') }}
                                    +
                                    Rp
                                    {{ number_format($module->price_per_user, 0, ',', '.') }}
                                    / user
                                @else
                                    Rp
                                    {{ number_format($module->fixed_price, 0, ',', '.') }}
                                @endif
                            </div>
                        </div>

                        <div class="text-right">
                            <div class="text-[8px]
                                   text-[#99968e]">
                                Organizations
                            </div>

                            <div
                                class="mt-1
                                   text-[12px]
                                   font-semibold">
                                {{ number_format($module->organizations_count) }}
                            </div>
                        </div>
                    </div>
                </div>

                <div class="mt-5 flex
                       items-center
                       justify-between">
                    <div class="text-[9px]
                           text-[#99968e]">
                        {{ ucfirst($module->billing_interval) }}
                    </div>

                    <a href="{{ route('superadmin.modules.show', $module) }}"
                        class="text-[9px]
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
                   border-black/[0.1]
                   bg-white px-6 py-16
                   text-center">
                <div class="text-[12px]
                       font-semibold">
                    Tidak ada module.
                </div>
            </div>
        @endforelse
    </div>

    @if ($modules->hasPages())
        <div class="mt-5">
            {{ $modules->links() }}
        </div>
    @endif

@endsection
