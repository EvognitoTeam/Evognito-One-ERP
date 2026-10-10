@extends('layouts.superadmin')

@section('title', 'Users')
@section('page_name', 'Users')

@section('content')

    <div class="flex flex-col gap-5
           md:flex-row
           md:items-end
           md:justify-between">
        <div>
            <div
                class="text-[11px] font-bold
                   uppercase tracking-[.11em]
                   text-[#e8742a]">
                Identity & Access
            </div>

            <h1 class="mt-2 text-[30px]
                   font-semibold
                   tracking-[-.045em]">
                Platform Users
            </h1>

            <p class="mt-2 text-[14px]
                   leading-6 text-[#85827b]">
                Kelola seluruh akun yang menggunakan
                Evognito One dan organization terkait.
            </p>
        </div>

        <a href="{{ route('superadmin.users.create') }}"
            class="inline-flex h-11
               items-center gap-2
               rounded-xl
               bg-[#1d1d1b]
               px-4 text-[13px]
               font-semibold text-white">
            + New User
        </a>
    </div>

    @if (session('success'))
        <div
            class="mt-6 rounded-xl
               border border-emerald-200
               bg-emerald-50
               px-4 py-3
               text-[13px]
               text-emerald-700">
            {{ session('success') }}
        </div>
    @endif

    @if (session('error'))
        <div
            class="mt-6 rounded-xl
               border border-red-200
               bg-red-50
               px-4 py-3
               text-[13px]
               text-red-700">
            {{ session('error') }}
        </div>
    @endif

    <form method="GET"
        class="mt-7 grid gap-3
           rounded-2xl
           border border-black/[0.07]
           bg-white p-4
           md:grid-cols-[1fr_180px_180px_auto]">
        <div>
            <label
                class="mb-2 block
                   text-[11px]
                   font-semibold
                   text-[#77746d]">
                Search
            </label>

            <input type="text" name="search" value="{{ $search }}" placeholder="Name, email, username..."
                class="h-10 w-full
                   rounded-xl
                   border border-black/[0.08]
                   bg-[#faf9f6]
                   px-3
                   text-[13px]">
        </div>

        <div>
            <label
                class="mb-2 block
                   text-[11px]
                   font-semibold
                   text-[#77746d]">
                Account Type
            </label>

            <select name="type"
                class="h-10 w-full
                   rounded-xl
                   border border-black/[0.08]
                   bg-[#faf9f6]
                   px-3
                   text-[13px]">
                <option value="">
                    All accounts
                </option>

                <option value="organization" @selected($type === 'organization')>
                    Organization User
                </option>

                <option value="superadmin" @selected($type === 'superadmin')>
                    Platform Superadmin
                </option>
            </select>
        </div>

        <div>
            <label
                class="mb-2 block
                   text-[11px]
                   font-semibold
                   text-[#77746d]">
                Status
            </label>

            <select name="status"
                class="h-10 w-full
                   rounded-xl
                   border border-black/[0.08]
                   bg-[#faf9f6]
                   px-3
                   text-[13px]">
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
                   rounded-xl
                   bg-[#1d1d1b]
                   px-4
                   text-[12px]
                   font-semibold
                   text-white">
                Filter
            </button>

            <a href="{{ route('superadmin.users.index') }}"
                class="flex h-10
                   items-center
                   rounded-xl
                   border border-black/[0.08]
                   bg-white px-4
                   text-[12px]
                   font-semibold">
                Reset
            </a>
        </div>
    </form>

    <div class="mt-5 overflow-hidden
           rounded-2xl
           border border-black/[0.07]
           bg-white">
        <div
            class="flex items-center
               justify-between
               border-b border-black/[0.06]
               px-5 py-4">
            <div>
                <div class="text-[14px]
                       font-semibold">
                    User Directory
                </div>

                <div class="mt-1
                       text-[12px]
                       text-[#99968e]">
                    {{ number_format($users->total()) }}
                    accounts
                </div>
            </div>
        </div>

        <div class="overflow-x-auto">
            <table class="w-full min-w-[950px]">
                <thead>
                    <tr class="border-b
                       border-black/[0.06]
                       bg-[#faf9f6]">
                        @foreach (['User', 'Account', 'Organizations', 'Last Login', 'Status', ''] as $heading)
                            <th
                                class="px-5 py-3
                               text-left
                               text-[11px]
                               font-semibold
                               text-[#85827b]">
                                {{ $heading }}
                            </th>
                        @endforeach
                    </tr>
                </thead>

                <tbody>
                    @forelse($users as $user)
                        @php
                            $initials = collect(preg_split('/\s+/', $user->name))
                                ->filter()
                                ->map(fn($word) => strtoupper(substr($word, 0, 1)))
                                ->take(2)
                                ->implode('');

                            $statusClass = match ($user->status) {
                                'active' => 'bg-emerald-50 text-emerald-700',

                                'suspended' => 'bg-amber-50 text-amber-700',

                                default => 'bg-zinc-100 text-zinc-600',
                            };
                        @endphp

                        <tr
                            class="border-b
                           border-black/[0.05]
                           last:border-0
                           hover:bg-[#faf9f6]">
                            <td class="px-5 py-4">
                                <div class="flex
                                   items-center gap-3">
                                    <div
                                        class="flex h-10 w-10
                                       shrink-0
                                       items-center
                                       justify-center
                                       rounded-full
                                       bg-[#efeee9]
                                       text-[11px]
                                       font-bold">
                                        {{ $initials }}
                                    </div>

                                    <div>
                                        <a href="{{ route('superadmin.users.show', $user) }}"
                                            class="text-[13px]
                                           font-semibold
                                           hover:underline">
                                            {{ $user->name }}
                                        </a>

                                        <div
                                            class="mt-1
                                           text-[11px]
                                           text-[#99968e]">
                                            {{ $user->email }}
                                        </div>
                                    </div>
                                </div>
                            </td>

                            <td class="px-5 py-4">
                                @if ($user->is_super_admin)
                                    <span
                                        class="rounded-full
                                       bg-violet-50
                                       px-2.5 py-1
                                       text-[11px]
                                       font-semibold
                                       text-violet-700">
                                        Platform Admin
                                    </span>
                                @else
                                    <span class="text-[12px]
                                       text-[#65625b]">
                                        Organization User
                                    </span>
                                @endif
                            </td>

                            <td
                                class="px-5 py-4
                               text-[13px]
                               font-semibold">
                                {{ number_format($user->organizations_count) }}
                            </td>

                            <td class="px-5 py-4">
                                @if ($user->last_login_at)
                                    <div class="text-[12px]">
                                        {{ $user->last_login_at->format('d M Y') }}
                                    </div>

                                    <div
                                        class="mt-1
                                       text-[11px]
                                       text-[#99968e]">
                                        {{ $user->last_login_at->format('H:i') }}
                                    </div>
                                @else
                                    <span class="text-[12px]
                                       text-[#aaa79f]">
                                        Never
                                    </span>
                                @endif
                            </td>

                            <td class="px-5 py-4">
                                <span
                                    class="rounded-full
                                   px-2.5 py-1
                                   text-[11px]
                                   font-semibold
                                   {{ $statusClass }}">
                                    {{ ucfirst($user->status) }}
                                </span>
                            </td>

                            <td class="px-5 py-4
                               text-right">
                                <a href="{{ route('superadmin.users.show', $user) }}"
                                    class="text-[12px]
                                   font-semibold">
                                    Manage →
                                </a>
                            </td>
                        </tr>

                    @empty
                        <tr>
                            <td colspan="6" class="px-6 py-16
                               text-center">
                                <div class="text-[14px]
                                   font-semibold">
                                    Tidak ada user.
                                </div>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @if ($users->hasPages())
            <div class="border-t
                   border-black/[0.06]
                   px-5 py-4">
                {{ $users->links() }}
            </div>
        @endif
    </div>

@endsection
