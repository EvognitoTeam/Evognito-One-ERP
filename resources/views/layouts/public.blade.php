@php
    $workspaceUrl = route('login');
    $workspaceLabel = 'Login';
    $currentUserOrganization = null;

    if (auth()->check()) {
        $workspaceLabel = 'Open Workspace';

        if (auth()->user()->isSuperAdmin()) {
            $workspaceUrl = route('superadmin.dashboard');
        } else {
            $currentUserOrganization = auth()
                ->user()
                ->organizations()
                ->wherePivot('status', 'active')
                ->where('organizations.status', 'active')
                ->orderByPivot('is_default', 'desc')
                ->orderBy('organizations.name')
                ->first();

            if ($currentUserOrganization) {
                $workspaceUrl = route('workspace.dashboard', [
                    'organization' => $currentUserOrganization->slug,
                ]);
            } else {
                $workspaceUrl = route('home');
            }
        }
    }
@endphp

<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">

    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <meta name="csrf-token" content="{{ csrf_token() }}">

    <meta name="description" content="@yield('meta_description', 'Evognito One adalah platform modular untuk mengelola organisasi, people, performance, learning, operations dan digital presence.')">

    <title>
        @yield('title', 'Evognito One')
    </title>

    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>

<body class="min-h-screen">

    <header
        class="fixed inset-x-0 top-0 z-40
           border-b border-black/[0.06]
           bg-[#f7f6f2]/90
           backdrop-blur-xl">
        <div class="ev-container flex h-[72px]
               items-center justify-between">
            <a href="{{ route('home') }}" class="flex items-center gap-3">
                <div
                    class="flex h-9 w-9
                       items-center justify-center
                       rounded-xl bg-[#1d1d1b]
                       text-sm font-bold text-white">
                    E
                </div>

                <div>
                    <div
                        class="text-[15px]
                           font-semibold
                           tracking-[-0.02em]">
                        Evognito One
                    </div>

                    <div
                        class="text-[10px]
                           font-medium uppercase
                           tracking-[0.13em]
                           text-[#8a8881]">
                        Organization Platform
                    </div>
                </div>
            </a>

            <nav
                class="hidden items-center gap-7
                   text-[13px] font-medium
                   text-[#62615c]
                   md:flex">
                <button type="button" data-scroll-to="#platform"
                    class="cursor-pointer
                       transition
                       hover:text-black">
                    Platform
                </button>

                <button type="button" data-scroll-to="#apps"
                    class="cursor-pointer
                       transition
                       hover:text-black">
                    Apps
                </button>

                <button type="button" data-scroll-to="#industries"
                    class="cursor-pointer
                       transition
                       hover:text-black">
                    Industries
                </button>

                <button type="button" data-scroll-to="#security"
                    class="cursor-pointer
                       transition
                       hover:text-black">
                    Security
                </button>
            </nav>

            <div class="flex items-center gap-2">

                <a href="{{ $workspaceUrl }}"
                    class="inline-flex
                       items-center gap-2
                       rounded-xl
                       bg-[#1d1d1b]
                       px-4 py-2.5
                       text-[13px]
                       font-semibold text-white
                       transition
                       hover:bg-black">
                    {{ $workspaceLabel }}

                    @auth
                        <svg class="h-4 w-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                            <path d="M5 12h14" />
                            <path d="m13 6 6 6-6 6" />
                        </svg>
                    @endauth
                </a>

            </div>
        </div>
    </header>

    <main>
        @yield('content')
    </main>

    <footer class="border-t
           border-black/[0.07]
           bg-[#f2f1ec]">
        <div
            class="ev-container
               flex flex-col gap-8
               py-10
               md:flex-row
               md:items-end
               md:justify-between">
            <div>
                <div class="flex items-center gap-3">
                    <div
                        class="flex h-8 w-8
                           items-center justify-center
                           rounded-lg
                           bg-[#1d1d1b]
                           text-xs font-bold
                           text-white">
                        E
                    </div>

                    <div class="font-semibold">
                        Evognito One
                    </div>
                </div>

                <p
                    class="mt-3 max-w-md
                       text-[13px]
                       leading-6
                       text-[#77766f]">
                    Satu core untuk people,
                    performance, learning,
                    operations, commerce dan
                    digital presence organisasi Anda.
                </p>
            </div>

            <div class="text-[12px]
                   text-[#8a8881]
                   md:text-right">
                <div>
                    Evognito Organization Platform
                </div>

                <div class="mt-1">
                    © {{ date('Y') }} Evognito.
                </div>
            </div>
        </div>
    </footer>

</body>

</html>
