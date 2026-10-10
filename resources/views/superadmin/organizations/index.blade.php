@extends('layouts.superadmin')

@section('title', 'Organizations')
@section('page_name', 'Organizations')

@section('content')

    <div class="flex flex-col gap-5
           lg:flex-row
           lg:items-end
           lg:justify-between">
        <div>
            <div
                class="text-[10px] font-bold
                   uppercase tracking-[.13em]
                   text-[#e8742a]">
                Tenant Management
            </div>

            <h1 class="mt-2 text-[30px]
                   font-semibold
                   tracking-[-.045em]">
                Organizations
            </h1>

            <p class="mt-2 text-[12px]
                   leading-5 text-[#85827b]">
                Kelola seluruh business, school,
                institution dan tenant Evognito One.
            </p>
        </div>

        <a href="{{ route('superadmin.organizations.create') }}"
            class="inline-flex h-11
               items-center justify-center
               gap-2 rounded-xl
               bg-[#1d1d1b]
               px-4 text-[11px]
               font-semibold text-white
               transition hover:bg-black">
            <span class="text-base leading-none">
                +
            </span>

            New Organization
        </a>
    </div>

    @if (session('success'))
        <div
            class="mt-6 rounded-xl
               border border-emerald-200
               bg-emerald-50
               px-4 py-3
               text-[11px]
               text-emerald-700">
            {{ session('success') }}
        </div>
    @endif

    {{-- FILTER --}}
    <form method="GET" action="{{ route('superadmin.organizations.index') }}"
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

            <input type="text" name="search" value="{{ $search }}" placeholder="Name, slug, email..."
                class="h-10 w-full
                   rounded-xl
                   border border-black/[0.08]
                   bg-[#faf9f6]
                   px-3 text-[11px]
                   outline-none
                   transition
                   focus:border-[#e8742a]
                   focus:ring-4
                   focus:ring-orange-100">
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
                   px-3 text-[11px]
                   outline-none">
                <option value="">
                    All types
                </option>

                @foreach ([
            'business' => 'Business',
            'education' => 'Education',
            'nonprofit' => 'Nonprofit',
            'government' => 'Government',
            'other' => 'Other',
        ] as $value => $label)
                    <option value="{{ $value }}" @selected($type === $value)>
                        {{ $label }}
                    </option>
                @endforeach
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
                   px-3 text-[11px]
                   outline-none">
                <option value="">
                    All status
                </option>

                <option value="active" @selected($status === 'active')>
                    Active
                </option>

                <option value="inactive" @selected($status === 'inactive')>
                    Inactive
                </option>

                <option value="suspended" @selected($status === 'suspended')>
                    Suspended
                </option>
            </select>
        </div>

        <div class="flex items-end gap-2">
            <button type="submit"
                class="h-10 cursor-pointer
                   rounded-xl bg-[#1d1d1b]
                   px-4 text-[10px]
                   font-semibold text-white">
                Filter
            </button>

            <a href="{{ route('superadmin.organizations.index') }}"
                class="flex h-10 items-center
                   rounded-xl
                   border border-black/[0.08]
                   bg-white px-4
                   text-[10px] font-semibold
                   text-[#77736c]">
                Reset
            </a>
        </div>
    </form>

    {{-- LIST --}}
    <div
        class="mt-5 overflow-hidden
           rounded-2xl
           border border-black/[0.07]
           bg-white
           ev-card-shadow">
        <div
            class="flex items-center
               justify-between
               border-b border-black/[0.06]
               px-5 py-4">
            <div>
                <div class="text-[12px] font-semibold">
                    Organization Directory
                </div>

                <div class="mt-1 text-[9px]
                       text-[#99968e]">
                    {{ number_format($organizations->total()) }}
                    organizations registered
                </div>
            </div>
        </div>

        <div class="overflow-x-auto">
            <table class="w-full min-w-[950px]">
                <thead>
                    <tr
                        class="border-b
                           border-black/[0.06]
                           bg-[#faf9f6]">
                        <th
                            class="px-5 py-3
                               text-left text-[9px]
                               font-bold uppercase
                               tracking-[.09em]
                               text-[#99968e]">
                            Organization
                        </th>

                        <th
                            class="px-4 py-3
                               text-left text-[9px]
                               font-bold uppercase
                               tracking-[.09em]
                               text-[#99968e]">
                            Type
                        </th>

                        <th
                            class="px-4 py-3
                               text-center text-[9px]
                               font-bold uppercase
                               tracking-[.09em]
                               text-[#99968e]">
                            Users
                        </th>

                        <th
                            class="px-4 py-3
                               text-center text-[9px]
                               font-bold uppercase
                               tracking-[.09em]
                               text-[#99968e]">
                            Employees
                        </th>

                        <th
                            class="px-4 py-3
                               text-center text-[9px]
                               font-bold uppercase
                               tracking-[.09em]
                               text-[#99968e]">
                            Apps
                        </th>

                        <th
                            class="px-4 py-3
                               text-left text-[9px]
                               font-bold uppercase
                               tracking-[.09em]
                               text-[#99968e]">
                            Status
                        </th>

                        <th class="px-5 py-3"></th>
                    </tr>
                </thead>

                <tbody>
                    @forelse($organizations as $organization)
                        <tr
                            class="border-b
                               border-black/[0.05]
                               last:border-0
                               transition
                               hover:bg-[#faf9f6]">
                            <td class="px-5 py-4">
                                <div class="flex items-center gap-3">
                                    @if ($organization->logo)
                                        <img src="{{ Storage::url($organization->logo) }}" alt=""
                                            class="h-9 w-9
                                               rounded-xl
                                               object-cover">
                                    @else
                                        <div
                                            class="flex h-9 w-9
                                               items-center
                                               justify-center
                                               rounded-xl
                                               bg-[#f0efe9]
                                               text-[10px]
                                               font-bold">
                                            {{ strtoupper(substr($organization->name, 0, 2)) }}
                                        </div>
                                    @endif

                                    <div>
                                        <a href="{{ route('superadmin.organizations.show', $organization) }}"
                                            class="text-[11px]
                                               font-semibold
                                               hover:underline">
                                            {{ $organization->name }}
                                        </a>

                                        <div
                                            class="mt-0.5
                                               text-[9px]
                                               text-[#99968e]">
                                            /{{ $organization->slug }}
                                        </div>
                                    </div>
                                </div>
                            </td>

                            <td class="px-4 py-4">
                                <span
                                    class="rounded-full
                                       bg-[#f2f1ec]
                                       px-2.5 py-1
                                       text-[9px]
                                       font-semibold
                                       capitalize
                                       text-[#747169]">
                                    {{ $organization->type }}
                                </span>
                            </td>

                            <td
                                class="px-4 py-4
                                   text-center
                                   text-[11px]
                                   font-semibold">
                                {{ number_format($organization->users_count) }}
                            </td>

                            <td
                                class="px-4 py-4
                                   text-center
                                   text-[11px]
                                   font-semibold">
                                {{ number_format($organization->employees_count) }}
                            </td>

                            <td
                                class="px-4 py-4
                                   text-center
                                   text-[11px]
                                   font-semibold">
                                {{ number_format($organization->modules_count) }}
                            </td>

                            <td class="px-4 py-4">
                                @php
                                    $statusClass = match ($organization->status) {
                                        'active' => 'bg-emerald-50 text-emerald-700',

                                        'suspended' => 'bg-amber-50 text-amber-700',

                                        default => 'bg-zinc-100 text-zinc-600',
                                    };
                                @endphp

                                <span
                                    class="inline-flex
                                       items-center
                                       rounded-full
                                       px-2.5 py-1
                                       text-[9px]
                                       font-semibold
                                       {{ $statusClass }}">
                                    {{ ucfirst($organization->status) }}
                                </span>
                            </td>

                            <td class="px-5 py-4
                                   text-right">
                                <a href="{{ route('superadmin.organizations.show', $organization) }}"
                                    class="text-[10px]
                                       font-semibold
                                       text-[#65625b]
                                       hover:text-black">
                                    Manage →
                                </a>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="7" class="px-5 py-16
                                   text-center">
                                <div class="text-[12px]
                                       font-semibold">
                                    Belum ada organization.
                                </div>

                                <div
                                    class="mt-2
                                       text-[10px]
                                       text-[#99968e]">
                                    Buat organization
                                    pertama untuk mulai
                                    menggunakan Evognito One.
                                </div>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @if ($organizations->hasPages())
            <div class="border-t
                   border-black/[0.06]
                   px-5 py-4">
                {{ $organizations->links() }}
            </div>
        @endif
    </div>

@endsection
