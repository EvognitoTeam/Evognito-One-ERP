@extends('layouts.superadmin')

@section('title', $user->name)
@section('page_name', 'User')

@section('content')

    @if (session('success'))
        <div
            class="mb-5 rounded-xl
               border border-emerald-200
               bg-emerald-50
               p-4
               text-[13px]
               text-emerald-700">
            {{ session('success') }}
        </div>
    @endif

    @if (session('error'))
        <div
            class="mb-5 rounded-xl
               border border-red-200
               bg-red-50
               p-4
               text-[13px]
               text-red-700">
            {{ session('error') }}
        </div>
    @endif

    @php
        $initials = collect(preg_split('/\s+/', $user->name))
            ->filter()
            ->map(fn($word) => strtoupper(substr($word, 0, 1)))
            ->take(2)
            ->implode('');
    @endphp

    <div class="flex flex-col gap-5
           lg:flex-row
           lg:items-end
           lg:justify-between">
        <div class="flex items-center gap-4">
            <div
                class="flex h-16 w-16
                   items-center
                   justify-center
                   rounded-full
                   bg-[#eae9e4]
                   text-[16px]
                   font-bold">
                {{ $initials }}
            </div>

            <div>
                <div class="flex flex-wrap
                       items-center gap-2">
                    <h1
                        class="text-[30px]
                           font-semibold
                           tracking-[-.045em]">
                        {{ $user->name }}
                    </h1>

                    @if ($user->isSuperAdmin())
                        <span
                            class="rounded-full
                               bg-violet-50
                               px-3 py-1.5
                               text-[11px]
                               font-semibold
                               text-violet-700">
                            Platform Superadmin
                        </span>
                    @endif
                </div>

                <div class="mt-1
                       text-[12px]
                       text-[#99968e]">
                    {{ $user->email }}
                    ·
                    {{ $user->ulid }}
                </div>
            </div>
        </div>

        <div class="flex gap-2">
            @if ($user->id !== auth()->id())
                <form method="POST"
                    action="{{ route('superadmin.users.toggle-status', $user) }}">
                    @csrf

                    <button type="submit"
                        class="h-10
                           cursor-pointer
                           rounded-xl
                           border border-black/[0.08]
                           bg-white px-4
                           text-[12px]
                           font-semibold">
                        {{ $user->status === 'active' ? 'Suspend' : 'Activate' }}
                    </button>
                </form>
            @endif

            <a href="{{ route('superadmin.users.edit', $user) }}"
                class="flex h-10
                   items-center
                   rounded-xl
                   bg-[#1d1d1b]
                   px-4
                   text-[12px]
                   font-semibold
                   text-white">
                Edit User
            </a>
        </div>
    </div>

    <div class="mt-8 grid gap-3
           sm:grid-cols-2
           xl:grid-cols-4">
        @foreach ([['Organizations', $user->organizations->count()], ['Roles', $user->roles->count()], ['Account Status', ucfirst($user->status)], ['Last Login', $user->last_login_at ? $user->last_login_at->format('d M Y H:i') : 'Never']] as $item)
            <div class="rounded-2xl
                   border border-black/[0.07]
                   bg-white p-5">
                <div class="text-[12px]
                       text-[#99968e]">
                    {{ $item[0] }}
                </div>

                <div
                    class="mt-4
                       text-[19px]
                       font-semibold
                       tracking-[-.03em]">
                    {{ $item[1] }}
                </div>
            </div>
        @endforeach
    </div>

    <div class="mt-5 grid gap-5
           xl:grid-cols-[1fr_380px]">

        {{-- MEMBERSHIP --}}
        <section
            class="overflow-hidden
               rounded-2xl
               border border-black/[0.07]
               bg-white">
            <div class="border-b
                   border-black/[0.06]
                   px-6 py-5">
                <h2 class="text-[16px]
                       font-semibold">
                    Organization Membership
                </h2>

                <p class="mt-1
                       text-[12px]
                       text-[#99968e]">
                    Workspace yang dapat
                    diakses user.
                </p>
            </div>

            @forelse($user->organizations
                as $organization)
                @php
                    $role = $user->roles->firstWhere('pivot.organization_id', $organization->id);
                @endphp

                <div
                    class="flex flex-col gap-4
                       border-b
                       border-black/[0.05]
                       px-6 py-5
                       last:border-0
                       md:flex-row
                       md:items-center
                       md:justify-between">
                    <div>
                        <a href="{{ route('superadmin.organizations.show', $organization) }}"
                            class="text-[14px]
                               font-semibold
                               hover:underline">
                            {{ $organization->name }}
                        </a>

                        <div
                            class="mt-1
                               text-[12px]
                               text-[#99968e]">
                            {{ $role?->name ?: 'No role' }}

                            @if ($organization->pivot->is_default)
                                · Default workspace
                            @endif
                        </div>
                    </div>

                    <div class="flex
                           flex-wrap gap-2">
                        @if (!$organization->pivot->is_default)
                            <form method="POST"
                                action="{{ route('superadmin.users.organizations.default', [$user, $organization]) }}">
                                @csrf

                                <button type="submit"
                                    class="h-9
                                       cursor-pointer
                                       rounded-xl
                                       border
                                       border-black/[0.08]
                                       px-3
                                       text-[11px]
                                       font-semibold">
                                    Set Default
                                </button>
                            </form>
                        @endif

                        @if (!$user->isSuperAdmin())
                            <form method="POST"
                                action="{{ route('superadmin.users.organizations.destroy', [$user, $organization]) }}"
                                onsubmit="
                                return confirm(
                                    'Cabut akses organization ini?'
                                );
                            ">
                                @csrf
                                @method('DELETE')

                                <button type="submit"
                                    class="h-9
                                       cursor-pointer
                                       rounded-xl
                                       border
                                       border-red-200
                                       bg-red-50
                                       px-3
                                       text-[11px]
                                       font-semibold
                                       text-red-700">
                                    Remove
                                </button>
                            </form>
                        @endif
                    </div>
                </div>

            @empty

                <div class="px-6 py-14
                       text-center">
                    <div class="text-[14px]
                           font-semibold">
                        No organization access
                    </div>

                    <p class="mt-2
                           text-[12px]
                           text-[#99968e]">
                        User belum terhubung
                        dengan organization.
                    </p>
                </div>
            @endforelse
        </section>


        <div class="space-y-5">

            {{-- ACCOUNT DETAILS --}}
            <section class="rounded-2xl
                   border border-black/[0.07]
                   bg-white p-6">
                <h2 class="text-[16px]
                       font-semibold">
                    Account Details
                </h2>

                <dl class="mt-6 space-y-4">

                    @foreach ([['Username', $user->username ?: '—'], ['Phone', $user->phone ?: '—'], ['Status', ucfirst($user->status)], ['Account Type', $user->isSuperAdmin() ? 'Platform Superadmin' : 'Organization User'], ['Created', $user->created_at->format('d M Y H:i')]] as $row)
                        <div
                            class="flex
                               justify-between
                               gap-5">
                            <dt class="text-[12px]
                                   text-[#99968e]">
                                {{ $row[0] }}
                            </dt>

                            <dd
                                class="text-right
                                   text-[12px]
                                   font-semibold">
                                {{ $row[1] }}
                            </dd>
                        </div>
                    @endforeach

                </dl>
            </section>

            @if (!$user->isSuperAdmin())
                <section
                    class="rounded-2xl
                       border border-black/[0.07]
                       bg-white p-6">
                    <h2 class="text-[16px]
                           font-semibold">
                        Add Membership
                    </h2>

                    <p
                        class="mt-1
                           text-[12px]
                           leading-5
                           text-[#99968e]">
                        Berikan user akses ke
                        organization lain.
                    </p>

                    <form method="POST"
                        action="{{ route('superadmin.users.memberships.store', $user) }}"
                        class="mt-5 space-y-4">
                        @csrf

                        <div>
                            <label
                                class="mb-2 block
                                   text-[12px]
                                   font-medium">
                                Organization
                            </label>

                            <select id="membership-organization" name="organization_id" required
                                class="h-11 w-full
                                   rounded-xl
                                   border border-black/[0.09]
                                   bg-[#faf9f6]
                                   px-3
                                   text-[13px]">
                                <option value="">
                                    Select organization
                                </option>

                                @foreach ($organizations as $organization)
                                    <option value="{{ $organization->id }}">
                                        {{ $organization->name }}
                                    </option>
                                @endforeach
                            </select>
                        </div>

                        <div>
                            <label
                                class="mb-2 block
                                   text-[12px]
                                   font-medium">
                                Role
                            </label>

                            <select id="membership-role" name="role_id"
                                class="h-11 w-full
                                   rounded-xl
                                   border border-black/[0.09]
                                   bg-[#faf9f6]
                                   px-3
                                   text-[13px]">
                                <option value="">
                                    No role
                                </option>

                                @foreach ($roles as $role)
                                    <option value="{{ $role->id }}"
                                        data-organization-id="{{ $role->organization_id }}">
                                        {{ $role->name }}
                                    </option>
                                @endforeach
                            </select>
                        </div>

                        <label
                            class="flex
                               items-center gap-2
                               text-[12px]">
                            <input type="checkbox" name="is_default" value="1">

                            Make default workspace
                        </label>

                        <button type="submit"
                            class="h-10 w-full
                               cursor-pointer
                               rounded-xl
                               bg-[#1d1d1b]
                               text-[12px]
                               font-semibold
                               text-white">
                            Add Membership
                        </button>
                    </form>
                </section>
            @endif

        </div>
    </div>

    @if (!$user->isSuperAdmin())
        <script>
            document.addEventListener('DOMContentLoaded', () => {
                const organization =
                    document.getElementById(
                        'membership-organization'
                    );

                const role =
                    document.getElementById(
                        'membership-role'
                    );

                if (!organization || !role) {
                    return;
                }

                const filterRoles = () => {
                    const organizationId =
                        organization.value;

                    [...role.options].forEach(
                        (option) => {
                            if (!option.value) {
                                option.hidden = false;
                                return;
                            }

                            option.hidden =
                                option.dataset.organizationId !==
                                organizationId;
                        }
                    );

                    const selected =
                        role.options[
                            role.selectedIndex
                        ];

                    if (
                        selected &&
                        selected.value &&
                        selected.hidden
                    ) {
                        role.value = '';
                    }
                };

                organization.addEventListener(
                    'change',
                    filterRoles
                );

                filterRoles();
            });
        </script>
    @endif

@endsection
