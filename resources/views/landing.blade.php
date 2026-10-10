@extends('layouts.public')

@php
    $workspaceUrl = route('login');
    $workspaceLabel = 'Login';

    if (auth()->check()) {
        $workspaceLabel = 'Open Workspace';

        if (auth()->user()->isSuperAdmin()) {
            $workspaceUrl = route('superadmin.dashboard');
        } else {
            $workspaceOrganization = auth()
                ->user()
                ->organizations()
                ->wherePivot('status', 'active')
                ->where('organizations.status', 'active')
                ->orderByPivot('is_default', 'desc')
                ->orderBy('organizations.name')
                ->first();

            if ($workspaceOrganization) {
                $workspaceUrl = route('workspace.dashboard', [
                    'organization' => $workspaceOrganization->slug,
                ]);
            }
        }
    }
@endphp

@section('title', 'Evognito One - One Platform for Every Organization')

@section('content')

    {{-- HERO --}}
    <section class="relative overflow-hidden
           border-b border-black/[0.06]
           bg-[#f7f6f2] pt-[72px]">
        <div class="pointer-events-none absolute
               inset-0 ev-grid-pattern
               opacity-[0.45]"></div>

        <div
            class="pointer-events-none absolute
               left-1/2 top-24
               h-[500px] w-[750px]
               -translate-x-1/2
               rounded-full
               bg-orange-200/20 blur-3xl">
        </div>

        <div
            class="ev-container relative
               grid min-h-[760px]
               items-center gap-14
               py-24 lg:grid-cols-[.9fr_1.1fr]">
            <div>
                <div
                    class="mb-6 inline-flex
                       items-center gap-2
                       rounded-full
                       border border-black/[0.08]
                       bg-white/70
                       px-3 py-1.5
                       text-[11px] font-semibold
                       text-[#605e58]
                       backdrop-blur">
                    <span
                        class="h-1.5 w-1.5
                           rounded-full
                           bg-[#e8742a]"></span>

                    EVOGNITO ORGANIZATION PLATFORM
                </div>

                <h1
                    class="max-w-[720px]
                       text-[48px] font-semibold
                       leading-[1.02]
                       tracking-[-0.055em]
                       text-[#1d1d1b]
                       sm:text-[62px]
                       lg:text-[72px]">
                    Satu platform untuk
                    <span class="text-[#77766f]">
                        menjalankan seluruh organisasi.
                    </span>
                </h1>

                <p
                    class="mt-7 max-w-[600px]
                       text-[16px] leading-7
                       text-[#6d6b64]
                       sm:text-[17px]">
                    Hubungkan people, performance,
                    learning, attendance, sales,
                    website dan operasi bisnis dalam
                    satu ecosystem dengan identity,
                    organization dan billing yang sama.
                </p>

                <div class="mt-9 flex flex-wrap gap-3">
                    <a href="{{ $workspaceUrl }}"
                        class="inline-flex
                                items-center gap-2
                                rounded-xl
                                bg-[#1d1d1b]
                                px-5 py-3.5
                                text-[13px]
                                font-semibold text-white
                                transition
                                hover:-translate-y-0.5
                                hover:bg-black">
                        @auth
                            Open Workspace

                            <svg class="h-4 w-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                <path d="M5 12h14" />
                                <path d="m13 6 6 6-6 6" />
                            </svg>
                        @else
                            Login
                        @endauth
                    </a>

                    <button type="button" data-scroll-to="#platform"
                        class="inline-flex cursor-pointer
                           items-center gap-2
                           rounded-xl
                           border border-black/[0.1]
                           bg-white/70
                           px-5 py-3.5
                           text-[13px] font-semibold
                           text-[#4c4b46]
                           backdrop-blur
                           transition hover:bg-white">
                        Lihat platform
                    </button>
                </div>

                <div
                    class="mt-12 grid max-w-[580px]
                       grid-cols-3
                       divide-x divide-black/[0.07]
                       border-y border-black/[0.07]
                       py-5">
                    <div class="pr-5">
                        <div class="text-[12px] font-semibold
                               text-[#292825]">
                            One identity
                        </div>

                        <div class="mt-1 text-[11px]
                               leading-5 text-[#8a8881]">
                            SSO lintas aplikasi
                        </div>
                    </div>

                    <div class="px-5">
                        <div class="text-[12px] font-semibold
                               text-[#292825]">
                            Modular
                        </div>

                        <div class="mt-1 text-[11px]
                               leading-5 text-[#8a8881]">
                            Aktifkan sesuai kebutuhan
                        </div>
                    </div>

                    <div class="pl-5">
                        <div class="text-[12px] font-semibold
                               text-[#292825]">
                            Multi-org
                        </div>

                        <div class="mt-1 text-[11px]
                               leading-5 text-[#8a8881]">
                            Business & education
                        </div>
                    </div>
                </div>
            </div>

            {{-- ERP PREVIEW --}}
            <div class="relative">
                <div
                    class="absolute -inset-8
                       rounded-[50px]
                       bg-orange-200/20
                       blur-3xl">
                </div>

                <div
                    class="ev-soft-shadow relative
                       overflow-hidden
                       rounded-[20px]
                       border border-black/[0.09]
                       bg-white">
                    <div
                        class="flex h-12 items-center
                           justify-between
                           border-b border-black/[0.06]
                           px-4">
                        <div class="flex items-center gap-2">
                            <span class="h-2.5 w-2.5 rounded-full
                                   bg-[#e3e1da]"></span>

                            <span class="h-2.5 w-2.5 rounded-full
                                   bg-[#e3e1da]"></span>

                            <span class="h-2.5 w-2.5 rounded-full
                                   bg-[#e3e1da]"></span>
                        </div>

                        <div class="text-[10px] font-medium
                               text-[#99968e]">
                            Evognito One / Workspace
                        </div>

                        <div class="w-12"></div>
                    </div>

                    <div
                        class="grid min-h-[500px]
                           grid-cols-[135px_1fr]
                           sm:grid-cols-[165px_1fr]">
                        <aside
                            class="border-r border-black/[0.06]
                               bg-[#f2f1ec]
                               p-3 sm:p-4">
                            <div class="flex items-center gap-2
                                   px-2 py-2">
                                <div
                                    class="flex h-7 w-7
                                       items-center justify-center
                                       rounded-lg
                                       bg-[#1d1d1b]
                                       text-[9px] font-bold
                                       text-white">
                                    E
                                </div>

                                <div class="hidden sm:block">
                                    <div class="text-[10px]
                                           font-semibold">
                                        EvognitoOne
                                    </div>

                                    <div class="text-[8px]
                                           text-[#949189]">
                                        Workspace
                                    </div>
                                </div>
                            </div>

                            <div class="mt-6">
                                <div
                                    class="px-2 text-[8px]
                                       font-bold tracking-[.11em]
                                       text-[#aaa79e]">
                                    WORKSPACE
                                </div>

                                <div
                                    class="mt-2 rounded-lg
                                       bg-white px-2.5 py-2
                                       text-[9px] font-semibold">
                                    Overview
                                </div>

                                <div
                                    class="mt-1 px-2.5 py-2
                                       text-[9px]
                                       text-[#77746c]">
                                    Apps
                                </div>
                            </div>

                            <div class="mt-6">
                                <div
                                    class="px-2 text-[8px]
                                       font-bold tracking-[.11em]
                                       text-[#aaa79e]">
                                    ORGANIZATION
                                </div>

                                @foreach (['People', 'Access', 'Billing', 'Settings'] as $item)
                                    <div
                                        class="px-2.5 py-2
                                           text-[9px]
                                           text-[#77746c]">
                                        {{ $item }}
                                    </div>
                                @endforeach
                            </div>
                        </aside>

                        <div class="bg-[#faf9f6] p-4 sm:p-6">
                            <div class="flex items-start
                                   justify-between">
                                <div>
                                    <div class="text-[9px]
                                           text-[#9b978f]">
                                        Thursday,
                                        8 October 2026
                                    </div>

                                    <div
                                        class="mt-1 text-[17px]
                                           font-semibold
                                           tracking-[-.03em]">
                                        Good evening, Evognito.
                                    </div>
                                </div>

                                <div
                                    class="flex h-8 w-8
                                       items-center justify-center
                                       rounded-full
                                       bg-[#eceae4]
                                       text-[9px] font-bold">
                                    EV
                                </div>
                            </div>

                            <div class="mt-7">
                                <div class="text-[9px] font-semibold
                                       text-[#77746c]">
                                    YOUR APPS
                                </div>

                                <div
                                    class="mt-3 grid
                                       grid-cols-2 gap-2
                                       sm:grid-cols-3">
                                    @php
                                        $previewApps = [
                                            ['HR', 'HRIS', 'People'],
                                            ['PA', 'Performance', 'KPI & PA'],
                                            ['LM', 'LMS', 'Learning'],
                                            ['KS', 'Kansiku', 'Attendance'],
                                            ['SF', 'Sales Force', 'Operations'],
                                            ['ST', 'Sites', 'Digital'],
                                        ];
                                    @endphp

                                    @foreach ($previewApps as $app)
                                        <div
                                            class="rounded-xl
                                               border border-black/[0.06]
                                               bg-white p-3
                                               ev-card-shadow">
                                            <div
                                                class="flex h-7 w-7
                                                   items-center
                                                   justify-center
                                                   rounded-lg
                                                   bg-[#f3f1eb]
                                                   text-[8px]
                                                   font-bold">
                                                {{ $app[0] }}
                                            </div>

                                            <div
                                                class="mt-4 text-[10px]
                                                   font-semibold">
                                                {{ $app[1] }}
                                            </div>

                                            <div
                                                class="mt-0.5 text-[8px]
                                                   text-[#9a978f]">
                                                {{ $app[2] }}
                                            </div>
                                        </div>
                                    @endforeach
                                </div>
                            </div>

                            <div class="mt-5 grid
                                   grid-cols-2 gap-2">
                                <div class="rounded-xl
           border border-black/[0.06]
           bg-white p-3">
                                    <div class="text-[8px]
               text-[#97948c]">
                                        Workspace
                                    </div>

                                    <div class="mt-1 text-[12px]
               font-semibold">
                                        Your Organization
                                    </div>

                                    <div
                                        class="mt-3
               flex items-center gap-1.5
               text-[8px]
               font-medium text-emerald-600">
                                        <span
                                            class="h-1.5 w-1.5
                   rounded-full
                   bg-emerald-500"></span>

                                        Active workspace
                                    </div>
                                </div>

                                <div
                                    class="rounded-xl
                                       border border-black/[0.06]
                                       bg-[#1d1d1b] p-3
                                       text-white">
                                    <div class="text-[8px]
                                           text-white/55">
                                        Active apps
                                    </div>

                                    <div class="mt-1 text-[22px]
                                           font-semibold">
                                        5
                                    </div>

                                    <div class="mt-2 text-[8px]
                                           text-white/55">
                                        All systems operational
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <div
                    class="absolute -bottom-5 -left-5
                       hidden rounded-xl
                       border border-black/[0.07]
                       bg-white px-4 py-3
                       ev-card-shadow sm:block">
                    <div class="text-[9px]
                           text-[#99968f]">
                        Identity
                    </div>

                    <div class="mt-1 flex items-center gap-2
                           text-[11px] font-semibold">
                        <span class="h-2 w-2 rounded-full
                               bg-emerald-500"></span>

                        SSO connected
                    </div>
                </div>
            </div>
        </div>
    </section>

    {{-- PLATFORM --}}
    <section id="platform" class="border-b border-black/[0.06]
           bg-white py-24">
        <div class="ev-container">
            <div class="grid gap-12
                   lg:grid-cols-[.7fr_1.3fr]">
                <div>
                    <div
                        class="text-[11px] font-bold
                           uppercase tracking-[.16em]
                           text-[#e8742a]">
                        Platform
                    </div>

                    <h2
                        class="mt-4 max-w-md
                           text-[36px] font-semibold
                           leading-[1.08]
                           tracking-[-.045em]
                           sm:text-[44px]">
                        Bukan kumpulan aplikasi
                        yang berdiri sendiri.
                    </h2>
                </div>

                <div
                    class="grid gap-px overflow-hidden
                       rounded-2xl
                       border border-black/[0.07]
                       bg-black/[0.07]
                       sm:grid-cols-2">
                    @php
                        $coreFeatures = [
                            [
                                '01',
                                'One identity',
                                'User masuk sekali lalu menggunakan seluruh aplikasi Evognito yang diizinkan.',
                            ],
                            [
                                '02',
                                'Organization core',
                                'User, employee, branch, department dan organization menggunakan sumber data yang sama.',
                            ],
                            [
                                '03',
                                'Module entitlement',
                                'Setiap organisasi hanya melihat dan menggunakan aplikasi yang aktif dalam subscription.',
                            ],
                            [
                                '04',
                                'Central billing',
                                'Fixed pricing, per-user maupun hybrid dikelola dari satu billing engine.',
                            ],
                        ];
                    @endphp

                    @foreach ($coreFeatures as $feature)
                        <div class="bg-[#faf9f6] p-7">
                            <div class="text-[10px] font-semibold
                                   text-[#aaa69e]">
                                {{ $feature[0] }}
                            </div>

                            <h3 class="mt-9 text-[17px]
                                   font-semibold">
                                {{ $feature[1] }}
                            </h3>

                            <p
                                class="mt-2 text-[13px]
                                   leading-6
                                   text-[#77746d]">
                                {{ $feature[2] }}
                            </p>
                        </div>
                    @endforeach
                </div>
            </div>
        </div>
    </section>

    {{-- APPS --}}
    <section id="apps" class="border-b border-black/[0.06]
           bg-[#f7f6f2] py-24">
        <div class="ev-container">
            <div
                class="flex flex-col gap-5
                   md:flex-row
                   md:items-end
                   md:justify-between">
                <div>
                    <div
                        class="text-[11px] font-bold
                           uppercase tracking-[.16em]
                           text-[#e8742a]">
                        Apps
                    </div>

                    <h2
                        class="mt-3 text-[36px]
                           font-semibold
                           tracking-[-.045em]
                           sm:text-[44px]">
                        Satu core. Banyak aplikasi.
                    </h2>
                </div>

                <p class="max-w-[470px]
                       text-[13px] leading-6
                       text-[#77746d]">
                    Mulai dari modul yang dibutuhkan,
                    lalu aktifkan modul lain saat
                    organisasi berkembang.
                </p>
            </div>

            <div class="mt-12 grid gap-3
                   sm:grid-cols-2
                   lg:grid-cols-3">
                @php
                    $apps = [
                        ['HR', 'HRIS', 'People', 'Employee, organization, leave, payroll dan workforce operations.'],
                        ['PA', 'Performance', 'People', 'KPI, competency, appraisal, development dan career planning.'],
                        ['LM', 'LMS', 'Learning', 'Course, training, assessment, progress dan learning development.'],
                        ['KS', 'Kansiku', 'Attendance', 'Digital attendance, device, biometric dan IoT integration.'],
                        [
                            'SF',
                            'Sales Force',
                            'Operations',
                            'Customer visit, geofencing, live tracking dan field sales.',
                        ],
                        ['ST', 'Sites', 'Digital', 'Company profile, school website, content dan custom domain.'],
                    ];
                @endphp

                @foreach ($apps as $app)
                    <article
                        class="group rounded-2xl
                           border border-black/[0.07]
                           bg-white p-6
                           transition duration-300
                           hover:-translate-y-1
                           hover:shadow-[0_18px_45px_rgba(0,0,0,.07)]">
                        <div class="flex items-start
                               justify-between">
                            <div
                                class="flex h-11 w-11
                                   items-center justify-center
                                   rounded-xl
                                   bg-[#f1f0eb]
                                   text-[11px]
                                   font-bold
                                   transition
                                   group-hover:bg-[#fff0e5]
                                   group-hover:text-[#d95f17]">
                                {{ $app[0] }}
                            </div>

                            <span
                                class="rounded-full
                                   bg-[#f4f3ef]
                                   px-2.5 py-1
                                   text-[9px]
                                   font-semibold
                                   uppercase
                                   tracking-[.08em]
                                   text-[#85827a]">
                                {{ $app[2] }}
                            </span>
                        </div>

                        <h3 class="mt-8 text-[18px]
                               font-semibold">
                            {{ $app[1] }}
                        </h3>

                        <p
                            class="mt-2 min-h-[48px]
                               text-[13px]
                               leading-6
                               text-[#77746d]">
                            {{ $app[3] }}
                        </p>

                        <div
                            class="mt-8 flex items-center
                               gap-2 text-[11px]
                               font-semibold">
                            Evognito App

                            <span class="transition
                                   group-hover:translate-x-1">
                                →
                            </span>
                        </div>
                    </article>
                @endforeach
            </div>
        </div>
    </section>

    {{-- INDUSTRIES --}}
    <section id="industries" class="border-b border-black/[0.06]
           bg-white py-24">
        <div class="ev-container">
            <div class="mx-auto max-w-2xl
                   text-center">
                <div
                    class="text-[11px] font-bold
                       uppercase tracking-[.16em]
                       text-[#e8742a]">
                    Industries
                </div>

                <h2
                    class="mt-3 text-[36px]
                       font-semibold
                       tracking-[-.045em]
                       sm:text-[44px]">
                    Dibangun untuk business
                    dan education.
                </h2>

                <p class="mt-4 text-[14px]
                       leading-7 text-[#77746d]">
                    Core-nya sama. Modul, workflow
                    dan pengalaman pengguna menyesuaikan
                    jenis organisasi.
                </p>
            </div>

            <div class="mt-12 grid gap-4
                   lg:grid-cols-2">
                <div
                    class="relative overflow-hidden
                       rounded-[22px]
                       bg-[#1d1d1b]
                       p-8 text-white
                       sm:p-10">
                    <div
                        class="absolute -right-20 -top-20
                           h-64 w-64
                           rounded-full
                           bg-orange-500/10
                           blur-2xl">
                    </div>

                    <div class="relative">
                        <div
                            class="text-[10px] font-semibold
                               uppercase tracking-[.14em]
                               text-white/45">
                            Business
                        </div>

                        <h3
                            class="mt-4 text-[30px]
                               font-semibold
                               tracking-[-.04em]">
                            Jalankan organisasi
                            dari people sampai operations.
                        </h3>

                        <div class="mt-10 grid
                               grid-cols-2 gap-3">
                            @foreach (['People & HR', 'Performance', 'Sales', 'Operations', 'Learning', 'Digital Presence'] as $item)
                                <div
                                    class="rounded-xl
                                       border border-white/10
                                       bg-white/[0.04]
                                       px-4 py-3
                                       text-[12px]
                                       text-white/75">
                                    {{ $item }}
                                </div>
                            @endforeach
                        </div>
                    </div>
                </div>

                <div
                    class="rounded-[22px]
                       border border-black/[0.07]
                       bg-[#f6f4ef] p-8
                       sm:p-10">
                    <div
                        class="text-[10px] font-semibold
                           uppercase tracking-[.14em]
                           text-[#99958d]">
                        Education
                    </div>

                    <h3
                        class="mt-4 text-[30px]
                           font-semibold
                           tracking-[-.04em]">
                        Satu ecosystem untuk
                        sekolah dan institusi pendidikan.
                    </h3>

                    <div class="mt-10 grid
                           grid-cols-2 gap-3">
                        @foreach (['Student', 'Teacher', 'Academic', 'Learning', 'Attendance', 'School Website'] as $item)
                            <div
                                class="rounded-xl
                                   border border-black/[0.07]
                                   bg-white px-4 py-3
                                   text-[12px]
                                   text-[#65625b]">
                                {{ $item }}
                            </div>
                        @endforeach
                    </div>
                </div>
            </div>
        </div>
    </section>

    {{-- SECURITY --}}
    <section id="security" class="border-b border-black/[0.06]
           bg-[#f7f6f2] py-24">
        <div class="ev-container">
            <div class="grid items-center gap-14
                   lg:grid-cols-2">
                <div>
                    <div
                        class="text-[11px] font-bold
                           uppercase tracking-[.16em]
                           text-[#e8742a]">
                        Organization isolation
                    </div>

                    <h2
                        class="mt-4 max-w-xl
                           text-[36px] font-semibold
                           leading-[1.1]
                           tracking-[-.045em]
                           sm:text-[44px]">
                        Setiap organisasi bekerja
                        di ruang datanya sendiri.
                    </h2>

                    <p
                        class="mt-5 max-w-xl
                           text-[14px] leading-7
                           text-[#77746d]">
                        Identity, organization membership,
                        role, permission dan module entitlement
                        diverifikasi sebelum user mengakses
                        data ataupun aplikasi.
                    </p>
                </div>

                <div
                    class="overflow-hidden rounded-2xl
                       border border-black/[0.07]
                       bg-white">
                    @php
                        $securityRows = [
                            ['Tenant context', 'Data dibatasi berdasarkan organisasi aktif.'],
                            ['Role & permission', 'Akses mengikuti tanggung jawab pengguna.'],
                            ['Module entitlement', 'Modul hanya aktif jika organization berhak menggunakannya.'],
                            ['Activity trace', 'Aktivitas penting tercatat melalui audit log.'],
                        ];
                    @endphp

                    @foreach ($securityRows as $index => $row)
                        <div
                            class="flex gap-5 p-6
                               {{ $index !== 0 ? 'border-t border-black/[0.06]' : '' }}">
                            <div
                                class="flex h-8 w-8
                                   shrink-0 items-center
                                   justify-center
                                   rounded-lg
                                   bg-[#f1f0eb]
                                   text-[10px]
                                   font-bold">
                                {{ str_pad($index + 1, 2, '0', STR_PAD_LEFT) }}
                            </div>

                            <div>
                                <div class="text-[13px]
                                       font-semibold">
                                    {{ $row[0] }}
                                </div>

                                <div
                                    class="mt-1 text-[12px]
                                       leading-5
                                       text-[#85827b]">
                                    {{ $row[1] }}
                                </div>
                            </div>
                        </div>
                    @endforeach
                </div>
            </div>
        </div>
    </section>

    {{-- CTA --}}
    <section class="bg-[#f7f6f2] py-20">
        <div class="ev-container">
            <div
                class="relative overflow-hidden
                   rounded-[24px]
                   bg-[#1d1d1b]
                   px-7 py-14 text-white
                   sm:px-12 lg:px-16">
                <div
                    class="absolute -right-32 -top-32
                       h-96 w-96
                       rounded-full
                       bg-orange-500/10 blur-3xl">
                </div>

                <div
                    class="relative flex flex-col
                       gap-8 lg:flex-row
                       lg:items-center
                       lg:justify-between">
                    <div>
                        <div
                            class="text-[10px] font-semibold
                               uppercase tracking-[.14em]
                               text-white/40">
                            Evognito One
                        </div>

                        <h2
                            class="mt-3 max-w-2xl
                               text-[34px] font-semibold
                               leading-[1.1]
                               tracking-[-.04em]
                               sm:text-[42px]">
                            Satu tempat untuk
                            menghubungkan seluruh organisasi.
                        </h2>

                        <p
                            class="mt-4 max-w-xl
                               text-[13px] leading-6
                               text-white/55">
                            Mulai dari Core, aktifkan
                            aplikasi yang dibutuhkan,
                            lalu berkembang tanpa membangun
                            ulang ecosystem Anda.
                        </p>
                    </div>

                    <a href="{{ $workspaceUrl }}"
                        class="inline-flex w-fit
           shrink-0 items-center gap-2
           rounded-xl
           bg-white
           px-5 py-3.5
           text-[13px]
           font-semibold
           text-black
           transition
           hover:bg-[#f2f0e9]">
                        @auth
                            Open Workspace
                            <span>→</span>
                        @else
                            Login
                        @endauth
                    </a>
                </div>
            </div>
        </div>
    </section>

@endsection
