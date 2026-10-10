@extends('layouts.superadmin')

@section('title', $organization->name)

@section('page_name', 'Organization')

@section('content')

    @php
        /*
    |--------------------------------------------------------------------------
    | Organization State
    |--------------------------------------------------------------------------
    */

        $isActive = $organization->status === 'active';

        $organizationType = match (strtolower((string) $organization->type)) {
            'business' => 'Business',

            'education' => 'Education',

            default => ucfirst((string) $organization->type),
        };

        /*
    |--------------------------------------------------------------------------
    | Counts
    |--------------------------------------------------------------------------
    |
    | Kalau controller sudah memakai loadCount(),
    | hasil *_count akan langsung dipakai.
    |
    */

        $usersCount = $organization->users_count ?? $organization->users()->count();

        $employeesCount = $organization->employees_count ?? $organization->employees()->count();

        $branchesCount = $organization->branches_count ?? $organization->branches()->count();

        $departmentsCount = $organization->departments_count ?? $organization->departments()->count();

        $positionsCount = $organization->positions_count ?? $organization->positions()->count();

        $activeAppsCount = $organization
            ->modules()
            ->wherePivot('enabled', true)
            ->wherePivot('status', 'active')
            ->where('modules.status', 'active')
            ->count();
    @endphp


    {{-- ========================================================= --}}
    {{-- ALERTS                                                    --}}
    {{-- ========================================================= --}}

    @if (session('success'))
        <div
            class="mb-6
               rounded-xl
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
            class="mb-6
               rounded-xl
               border border-red-200
               bg-red-50
               px-4 py-3
               text-[13px]
               text-red-700">
            {{ session('error') }}
        </div>
    @endif


    {{-- ========================================================= --}}
    {{-- HEADER                                                    --}}
    {{-- ========================================================= --}}

    <div class="flex flex-col gap-6
           xl:flex-row
           xl:items-end
           xl:justify-between">
        <div class="min-w-0">

            {{-- BACK --}}
            <a href="{{ route('superadmin.organizations.index') }}"
                class="inline-flex
                   items-center
                   gap-1.5
                   text-[12px]
                   font-semibold
                   text-[#88857d]
                   transition
                   hover:text-[#292825]">
                <svg class="h-3.5 w-3.5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                    <path d="m15 18-6-6 6-6" />
                </svg>

                Organizations
            </a>


            {{-- ORGANIZATION --}}
            <div class="mt-5
                   flex items-start
                   gap-4">
                {{-- LOGO / INITIAL --}}
                <div
                    class="flex h-14 w-14
                       shrink-0
                       items-center
                       justify-center
                       overflow-hidden
                       rounded-2xl
                       bg-[#1d1d1b]
                       text-[17px]
                       font-semibold
                       text-white">
                    @if (!empty($organization->logo))
                        <img src="{{ asset('storage/' . $organization->logo) }}"
                            alt="{{ $organization->name }}"
                            class="h-full w-full
                               object-cover">
                    @else
                        {{ strtoupper(substr($organization->name, 0, 2)) }}
                    @endif
                </div>


                <div class="min-w-0">
                    <div class="flex flex-wrap
                           items-center
                           gap-2">
                        <div
                            class="text-[11px]
                               font-bold
                               uppercase
                               tracking-[.1em]
                               text-[#e8742a]">
                            Organization
                        </div>

                        <span
                            class="rounded-full
                               px-2.5 py-1
                               text-[10px]
                               font-semibold
                               {{ $isActive ? 'bg-emerald-50 text-emerald-700' : 'bg-zinc-100 text-zinc-600' }}">
                            {{ $isActive ? 'Active' : ucfirst($organization->status ?: 'inactive') }}
                        </span>
                    </div>

                    <h1
                        class="mt-2
                           truncate
                           text-[30px]
                           font-semibold
                           tracking-[-.045em]
                           sm:text-[34px]">
                        {{ $organization->name }}
                    </h1>

                    <div
                        class="mt-2
                           flex flex-wrap
                           items-center
                           gap-x-3 gap-y-1
                           text-[13px]
                           text-[#85827b]">
                        <span>
                            {{ $organizationType }}
                        </span>

                        <span class="text-[#ccc9c1]">
                            •
                        </span>

                        <span>
                            /{{ $organization->slug }}
                        </span>
                    </div>
                </div>
            </div>
        </div>


        {{-- ===================================================== --}}
        {{-- ACTION BUTTONS                                        --}}
        {{-- ===================================================== --}}

        <div class="flex flex-wrap gap-2">
            {{-- EDIT ORGANIZATION --}}
            <a href="{{ route('superadmin.organizations.edit', $organization) }}"
                class="inline-flex
                   h-10
                   items-center
                   gap-2
                   rounded-xl
                   border border-black/[0.08]
                   bg-white
                   px-4
                   text-[12px]
                   font-semibold
                   text-[#292825]
                   transition
                   hover:bg-[#f6f5f1]">
                <svg class="h-4 w-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8">
                    <path d="M12 20h9" />

                    <path d="M16.5 3.5a2.1 2.1 0 0 1 3 3L7 19l-4 1 1-4Z" />
                </svg>

                Edit
            </a>


            {{-- MANAGE APPS --}}
            <a href="{{ route('superadmin.organizations.apps.index', $organization) }}"
                class="inline-flex
                   h-10
                   items-center
                   gap-2
                   rounded-xl
                   border border-black/[0.08]
                   bg-white
                   px-4
                   text-[12px]
                   font-semibold
                   text-[#292825]
                   transition
                   hover:border-orange-200
                   hover:bg-orange-50
                   hover:text-orange-800">
                <svg class="h-4 w-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8">
                    <rect x="3" y="3" width="7" height="7" rx="2" />

                    <rect x="14" y="3" width="7" height="7" rx="2" />

                    <rect x="3" y="14" width="7" height="7" rx="2" />

                    <rect x="14" y="14" width="7" height="7" rx="2" />
                </svg>

                Manage Apps
            </a>


            {{-- OPEN WORKSPACE --}}
            <a href="{{ route('workspace.dashboard', [
                'organization' => $organization->slug,
            ]) }}"
                target="_blank" rel="noopener noreferrer"
                class="inline-flex
                   h-10
                   items-center
                   gap-2
                   rounded-xl
                   bg-[#1d1d1b]
                   px-4
                   text-[12px]
                   font-semibold
                   text-white
                   transition
                   hover:bg-black">
                Open Workspace

                <svg class="h-4 w-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8">
                    <path d="M7 17 17 7" />

                    <path d="M7 7h10v10" />
                </svg>
            </a>
        </div>
    </div>


    {{-- ========================================================= --}}
    {{-- SUMMARY                                                   --}}
    {{-- ========================================================= --}}

    <div class="mt-8
           grid gap-3
           sm:grid-cols-2
           lg:grid-cols-3
           xl:grid-cols-6">
        {{-- USERS --}}
        <div class="rounded-2xl
               border border-black/[0.07]
               bg-white
               p-5">
            <div class="text-[12px]
                   text-[#99968e]">
                Users
            </div>

            <div
                class="mt-4
                   text-[26px]
                   font-semibold
                   tracking-[-.04em]">
                {{ number_format($usersCount) }}
            </div>
        </div>


        {{-- EMPLOYEES --}}
        <div class="rounded-2xl
               border border-black/[0.07]
               bg-white
               p-5">
            <div class="text-[12px]
                   text-[#99968e]">
                Employees
            </div>

            <div
                class="mt-4
                   text-[26px]
                   font-semibold
                   tracking-[-.04em]">
                {{ number_format($employeesCount) }}
            </div>
        </div>


        {{-- BRANCHES --}}
        <div class="rounded-2xl
               border border-black/[0.07]
               bg-white
               p-5">
            <div class="text-[12px]
                   text-[#99968e]">
                Branches
            </div>

            <div
                class="mt-4
                   text-[26px]
                   font-semibold
                   tracking-[-.04em]">
                {{ number_format($branchesCount) }}
            </div>
        </div>


        {{-- DEPARTMENTS --}}
        <div class="rounded-2xl
               border border-black/[0.07]
               bg-white
               p-5">
            <div class="text-[12px]
                   text-[#99968e]">
                Departments
            </div>

            <div
                class="mt-4
                   text-[26px]
                   font-semibold
                   tracking-[-.04em]">
                {{ number_format($departmentsCount) }}
            </div>
        </div>


        {{-- POSITIONS --}}
        <div class="rounded-2xl
               border border-black/[0.07]
               bg-white
               p-5">
            <div class="text-[12px]
                   text-[#99968e]">
                Positions
            </div>

            <div
                class="mt-4
                   text-[26px]
                   font-semibold
                   tracking-[-.04em]">
                {{ number_format($positionsCount) }}
            </div>
        </div>


        {{-- APPS --}}
        <a href="{{ route('superadmin.organizations.apps.index', $organization) }}"
            class="group
               rounded-2xl
               border border-black/[0.07]
               bg-white
               p-5
               transition
               hover:border-orange-200
               hover:bg-orange-50/30">
            <div class="flex items-center
                   justify-between">
                <span class="text-[12px]
                       text-[#99968e]">
                    Active Apps
                </span>

                <svg class="h-3.5 w-3.5
                       text-[#aaa79f]
                       transition
                       group-hover:text-orange-600"
                    viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                    <path d="M5 12h14" />
                    <path d="m13 6 6 6-6 6" />
                </svg>
            </div>

            <div
                class="mt-4
                   text-[26px]
                   font-semibold
                   tracking-[-.04em]
                   text-emerald-700">
                {{ number_format($activeAppsCount) }}
            </div>
        </a>
    </div>


    {{-- ========================================================= --}}
    {{-- MAIN CONTENT                                              --}}
    {{-- ========================================================= --}}

    <div class="mt-5
           grid gap-5
           xl:grid-cols-[1fr_370px]">
        {{-- ===================================================== --}}
        {{-- LEFT                                                  --}}
        {{-- ===================================================== --}}

        <div class="space-y-5">

            {{-- ORGANIZATION INFORMATION --}}
            <section
                class="rounded-2xl
                   border border-black/[0.07]
                   bg-white
                   p-6">
                <div>
                    <h2 class="text-[16px]
                           font-semibold">
                        Organization Information
                    </h2>

                    <p class="mt-1
                           text-[12px]
                           text-[#99968e]">
                        Informasi utama organization
                        di Evognito One.
                    </p>
                </div>


                <div
                    class="mt-6
                       grid gap-x-8
                       gap-y-6
                       md:grid-cols-2">
                    {{-- ORGANIZATION NAME --}}
                    <div>
                        <div
                            class="text-[11px]
                               font-medium
                               uppercase
                               tracking-[.06em]
                               text-[#99968e]">
                            Organization Name
                        </div>

                        <div
                            class="mt-2
                               text-[13px]
                               font-semibold">
                            {{ $organization->name }}
                        </div>
                    </div>


                    {{-- LEGAL NAME --}}
                    <div>
                        <div
                            class="text-[11px]
                               font-medium
                               uppercase
                               tracking-[.06em]
                               text-[#99968e]">
                            Legal Name
                        </div>

                        <div
                            class="mt-2
                               text-[13px]
                               font-semibold">
                            {{ $organization->legal_name ?: '—' }}
                        </div>
                    </div>


                    {{-- TYPE --}}
                    <div>
                        <div
                            class="text-[11px]
                               font-medium
                               uppercase
                               tracking-[.06em]
                               text-[#99968e]">
                            Type
                        </div>

                        <div
                            class="mt-2
                               text-[13px]
                               font-semibold">
                            {{ $organizationType }}
                        </div>
                    </div>


                    {{-- SLUG --}}
                    <div>
                        <div
                            class="text-[11px]
                               font-medium
                               uppercase
                               tracking-[.06em]
                               text-[#99968e]">
                            Workspace Slug
                        </div>

                        <div
                            class="mt-2
                               font-mono
                               text-[12px]
                               font-semibold">
                            /{{ $organization->slug }}
                        </div>
                    </div>


                    {{-- EMAIL --}}
                    <div>
                        <div
                            class="text-[11px]
                               font-medium
                               uppercase
                               tracking-[.06em]
                               text-[#99968e]">
                            Email
                        </div>

                        <div
                            class="mt-2
                               break-all
                               text-[13px]
                               font-semibold">
                            {{ $organization->email ?: '—' }}
                        </div>
                    </div>


                    {{-- PHONE --}}
                    <div>
                        <div
                            class="text-[11px]
                               font-medium
                               uppercase
                               tracking-[.06em]
                               text-[#99968e]">
                            Phone
                        </div>

                        <div
                            class="mt-2
                               text-[13px]
                               font-semibold">
                            {{ $organization->phone ?: '—' }}
                        </div>
                    </div>


                    {{-- CREATED --}}
                    <div>
                        <div
                            class="text-[11px]
                               font-medium
                               uppercase
                               tracking-[.06em]
                               text-[#99968e]">
                            Created
                        </div>

                        <div
                            class="mt-2
                               text-[13px]
                               font-semibold">
                            {{ $organization->created_at?->format('d M Y H:i') ?? '—' }}
                            WIB
                        </div>
                    </div>


                    {{-- STATUS --}}
                    <div>
                        <div
                            class="text-[11px]
                               font-medium
                               uppercase
                               tracking-[.06em]
                               text-[#99968e]">
                            Status
                        </div>

                        <div class="mt-2">
                            <span
                                class="inline-flex
                                   rounded-full
                                   px-2.5 py-1
                                   text-[11px]
                                   font-semibold
                                   {{ $isActive ? 'bg-emerald-50 text-emerald-700' : 'bg-zinc-100 text-zinc-600' }}">
                                {{ ucfirst($organization->status ?: 'inactive') }}
                            </span>
                        </div>
                    </div>
                </div>


                {{-- ADDRESS --}}
                <div
                    class="mt-7
                       border-t
                       border-black/[0.06]
                       pt-6">
                    <div
                        class="text-[11px]
                           font-medium
                           uppercase
                           tracking-[.06em]
                           text-[#99968e]">
                        Address
                    </div>

                    <p
                        class="mt-2
                           whitespace-pre-line
                           text-[13px]
                           leading-6
                           text-[#5f5c56]">
                        {{ $organization->address ?: 'No address information.' }}
                    </p>
                </div>
            </section>


            {{-- ================================================= --}}
            {{-- APPLICATION ACCESS                                --}}
            {{-- ================================================= --}}

            <section
                class="rounded-2xl
                   border border-black/[0.07]
                   bg-white
                   p-6">
                <div
                    class="flex flex-col
                       gap-4
                       sm:flex-row
                       sm:items-center
                       sm:justify-between">
                    <div>
                        <h2 class="text-[16px]
                               font-semibold">
                            Application Access
                        </h2>

                        <p
                            class="mt-1
                               text-[12px]
                               leading-5
                               text-[#99968e]">
                            Aplikasi aktif dapat dibuka
                            dari workspace organization.
                            Aplikasi lainnya tetap tampil
                            sebagai Payment Required.
                        </p>
                    </div>

                    <a href="{{ route('superadmin.organizations.apps.index', $organization) }}"
                        class="inline-flex
                           h-10
                           shrink-0
                           items-center
                           gap-2
                           rounded-xl
                           bg-[#1d1d1b]
                           px-4
                           text-[12px]
                           font-semibold
                           text-white">
                        Manage Apps

                        <svg class="h-3.5 w-3.5" viewBox="0 0 24 24" fill="none" stroke="currentColor"
                            stroke-width="2">
                            <path d="M5 12h14" />
                            <path d="m13 6 6 6-6 6" />
                        </svg>
                    </a>
                </div>


                {{-- ACTIVE APPS PREVIEW --}}
                <div
                    class="mt-6
                       grid gap-3
                       sm:grid-cols-2
                       lg:grid-cols-3">
                    @php
                        $activeModules = $organization
                            ->modules()
                            ->wherePivot('enabled', true)
                            ->wherePivot('status', 'active')
                            ->where('modules.status', 'active')
                            ->orderBy('modules.sort_order')
                            ->orderBy('modules.name')
                            ->limit(9)
                            ->get();
                    @endphp

                    @forelse($activeModules
                        as $module)
                        <div
                            class="rounded-xl
                               border border-black/[0.06]
                               bg-[#faf9f6]
                               p-4">
                            <div
                                class="flex
                                   items-start
                                   justify-between
                                   gap-3">
                                <div
                                    class="flex h-10 w-10
                                       items-center
                                       justify-center
                                       rounded-xl
                                       bg-white
                                       text-[12px]
                                       font-bold
                                       shadow-sm">
                                    {{ strtoupper(substr($module->name, 0, 2)) }}
                                </div>

                                <span
                                    class="rounded-full
                                       bg-emerald-50
                                       px-2 py-1
                                       text-[9px]
                                       font-semibold
                                       text-emerald-700">
                                    Active
                                </span>
                            </div>

                            <div
                                class="mt-4
                                   text-[13px]
                                   font-semibold">
                                {{ $module->name }}
                            </div>

                            <div
                                class="mt-1
                                   text-[11px]
                                   text-[#99968e]">
                                {{ $module->category ?: 'Application' }}
                            </div>

                            <div
                                class="mt-2
                                   inline-flex
                                   rounded-md
                                   bg-white
                                   px-2 py-1
                                   text-[10px]
                                   font-semibold
                                   text-[#77746d]">
                                v{{ $module->version ?: '1.0.0' }}
                            </div>
                        </div>

                    @empty
                        <div
                            class="col-span-full
                               rounded-xl
                               border border-dashed
                               border-black/[0.1]
                               bg-[#faf9f6]
                               px-5 py-10
                               text-center">
                            <div class="text-[13px]
                                   font-semibold">
                                Belum ada aplikasi aktif.
                            </div>

                            <p
                                class="mt-1
                                   text-[12px]
                                   text-[#99968e]">
                                Gunakan Manage Apps untuk
                                memberikan akses aplikasi.
                            </p>
                        </div>
                    @endforelse
                </div>
            </section>
        </div>


        {{-- ===================================================== --}}
        {{-- RIGHT SIDEBAR                                         --}}
        {{-- ===================================================== --}}

        <aside class="space-y-5">

            {{-- WORKSPACE --}}
            <section
                class="rounded-2xl
                   bg-[#1d1d1b]
                   p-6
                   text-white">
                <div
                    class="text-[11px]
                       font-semibold
                       uppercase
                       tracking-[.09em]
                       text-white/40">
                    Workspace
                </div>

                <h2
                    class="mt-4
                       text-[21px]
                       font-semibold
                       tracking-[-.03em]">
                    Customer Workspace
                </h2>

                <p
                    class="mt-3
                       text-[13px]
                       leading-6
                       text-white/50">
                    Buka workspace
                    {{ $organization->name }}
                    untuk melihat All Apps
                    sebagaimana ditampilkan
                    kepada customer.
                </p>

                <div
                    class="mt-5
                       rounded-xl
                       border border-white/10
                       bg-white/[0.06]
                       p-4">
                    <div
                        class="text-[10px]
                           uppercase
                           tracking-[.08em]
                           text-white/40">
                        Workspace URL
                    </div>

                    <div
                        class="mt-2
                           truncate
                           font-mono
                           text-[12px]
                           text-white/80">
                        /{{ $organization->slug }}
                    </div>
                </div>

                <a href="{{ route('workspace.dashboard', [
                    'organization' => $organization->slug,
                ]) }}"
                    target="_blank" rel="noopener noreferrer"
                    class="mt-5
                       flex h-11
                       w-full
                       items-center
                       justify-center
                       gap-2
                       rounded-xl
                       bg-white
                       text-[13px]
                       font-semibold
                       text-black
                       transition
                       hover:bg-[#f3f2ef]">
                    Open Workspace

                    <svg class="h-4 w-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8">
                        <path d="M7 17 17 7" />

                        <path d="M7 7h10v10" />
                    </svg>
                </a>
            </section>


            {{-- APP ACCESS --}}
            <section
                class="rounded-2xl
                   border border-black/[0.07]
                   bg-white
                   p-5">
                <div class="text-[12px]
                       font-semibold">
                    App Access
                </div>

                <div class="mt-5
                       flex items-end
                       justify-between">
                    <div>
                        <div
                            class="text-[28px]
                               font-semibold
                               tracking-[-.04em]">
                            {{ number_format($activeAppsCount) }}
                        </div>

                        <div
                            class="mt-1
                               text-[11px]
                               text-[#99968e]">
                            active applications
                        </div>
                    </div>

                    <div
                        class="flex h-10 w-10
                           items-center
                           justify-center
                           rounded-xl
                           bg-emerald-50
                           text-emerald-700">
                        <svg class="h-[18px] w-[18px]" viewBox="0 0 24 24" fill="none" stroke="currentColor"
                            stroke-width="1.8">
                            <rect x="3" y="3" width="7" height="7" rx="2" />

                            <rect x="14" y="3" width="7" height="7" rx="2" />

                            <rect x="3" y="14" width="7" height="7" rx="2" />

                            <rect x="14" y="14" width="7" height="7" rx="2" />
                        </svg>
                    </div>
                </div>

                <a href="{{ route('superadmin.organizations.apps.index', $organization) }}"
                    class="mt-5
                       flex h-10
                       w-full
                       items-center
                       justify-center
                       rounded-xl
                       border border-black/[0.08]
                       bg-[#faf9f6]
                       text-[12px]
                       font-semibold
                       transition
                       hover:bg-[#f1f0eb]">
                    Manage Applications
                </a>
            </section>


            {{-- SYSTEM INFORMATION --}}
            <section
                class="rounded-2xl
                   border border-black/[0.07]
                   bg-white
                   p-5">
                <div class="text-[12px]
                       font-semibold">
                    System Information
                </div>

                <div class="mt-5 space-y-4">
                    <div
                        class="flex
                           items-start
                           justify-between
                           gap-5">
                        <span class="text-[11px]
                               text-[#99968e]">
                            Database ID
                        </span>

                        <span
                            class="text-right
                               text-[11px]
                               font-semibold">
                            #{{ $organization->id }}
                        </span>
                    </div>

                    @if ($organization->ulid)
                        <div
                            class="flex
                               items-start
                               justify-between
                               gap-5">
                            <span class="text-[11px]
                                   text-[#99968e]">
                                ULID
                            </span>

                            <span
                                class="max-w-[210px]
                                   break-all
                                   text-right
                                   font-mono
                                   text-[10px]
                                   font-semibold">
                                {{ $organization->ulid }}
                            </span>
                        </div>
                    @endif

                    <div
                        class="flex
                           items-start
                           justify-between
                           gap-5">
                        <span class="text-[11px]
                               text-[#99968e]">
                            Last Updated
                        </span>

                        <span
                            class="text-right
                               text-[11px]
                               font-semibold">
                            {{ $organization->updated_at?->format('d M Y H:i') ?? '—' }}
                            WIB
                        </span>
                    </div>
                </div>
            </section>

        </aside>
    </div>

@endsection
