<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">

    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <meta name="csrf-token" content="{{ csrf_token() }}">

    <title>
        Sign in - Evognito One
    </title>

    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>

<body class="min-h-screen bg-[#f7f6f2]">

    <div class="grid min-h-screen
           lg:grid-cols-[1.05fr_.95fr]">
        {{-- LEFT --}}
        <section
            class="relative hidden overflow-hidden
               bg-[#1d1d1b]
               p-12 text-white
               lg:flex lg:flex-col">
            <div class="absolute inset-0
                   opacity-[0.07]
                   ev-grid-pattern"></div>

            <div
                class="absolute -bottom-40 -right-28
                   h-[500px] w-[500px]
                   rounded-full
                   bg-orange-500/20
                   blur-3xl">
            </div>

            <a href="{{ url('/') }}" class="relative flex items-center gap-3">
                <div
                    class="flex h-10 w-10
                       items-center justify-center
                       rounded-xl bg-white
                       text-sm font-bold text-black">
                    E
                </div>

                <div>
                    <div class="text-[15px]
                           font-semibold">
                        Evognito One
                    </div>

                    <div
                        class="text-[9px]
                           uppercase
                           tracking-[.14em]
                           text-white/40">
                        Organization Platform
                    </div>
                </div>
            </a>

            <div class="relative my-auto
                   max-w-[560px]">
                <div
                    class="text-[10px] font-semibold
                       uppercase tracking-[.15em]
                       text-orange-300/70">
                    One ecosystem
                </div>

                <h1
                    class="mt-5 text-[52px]
                       font-semibold leading-[1.02]
                       tracking-[-.055em]">
                    Satu identity.
                    <span class="text-white/45">
                        Semua aplikasi.
                    </span>
                </h1>

                <p
                    class="mt-7 max-w-[490px]
                       text-[14px] leading-7
                       text-white/50">
                    Gunakan satu akun Evognito One untuk
                    masuk ke aplikasi, organisasi dan
                    layanan yang tersedia untuk Anda.
                </p>

                <div class="mt-10 grid
                       grid-cols-3 gap-2">
                    @foreach ([['HR', 'HRIS'], ['PA', 'Performance'], ['LM', 'Learning']] as $item)
                        <div
                            class="rounded-xl
                               border border-white/10
                               bg-white/[0.04]
                               p-4">
                            <div
                                class="flex h-8 w-8
                                   items-center justify-center
                                   rounded-lg
                                   bg-white/[0.08]
                                   text-[9px] font-bold">
                                {{ $item[0] }}
                            </div>

                            <div
                                class="mt-4 text-[11px]
                                   font-semibold
                                   text-white/70">
                                {{ $item[1] }}
                            </div>
                        </div>
                    @endforeach
                </div>
            </div>

            <div class="relative text-[11px]
                   text-white/30">
                Evognito Organization Platform
            </div>
        </section>

        {{-- LOGIN --}}
        <section
            class="flex min-h-screen
               items-center justify-center
               px-6 py-12 sm:px-10">
            <div class="w-full max-w-[400px]">
                <a href="{{ url('/') }}" class="mb-12 flex items-center
                       gap-3 lg:hidden">
                    <div
                        class="flex h-9 w-9
                           items-center justify-center
                           rounded-xl bg-[#1d1d1b]
                           text-xs font-bold text-white">
                        E
                    </div>

                    <div class="font-semibold">
                        Evognito One
                    </div>
                </a>

                <div
                    class="text-[10px] font-bold
                       uppercase tracking-[.14em]
                       text-[#e8742a]">
                    Workspace access
                </div>

                <h2
                    class="mt-3 text-[34px]
                       font-semibold
                       tracking-[-.045em]">
                    Welcome back.
                </h2>

                <p class="mt-3 text-[13px]
                       leading-6 text-[#77746d]">
                    Masuk menggunakan email atau username
                    Evognito One Anda.
                </p>

                @if (session('success'))
                    <div
                        class="mt-6 rounded-xl
               border border-emerald-200
               bg-emerald-50
               px-4 py-3
               text-[12px]
               leading-5 text-emerald-700">
                        {{ session('success') }}
                    </div>
                @endif

                @if ($errors->any())
                    <div
                        class="mt-6 rounded-xl
                           border border-red-200
                           bg-red-50 px-4 py-3
                           text-[12px] leading-5
                           text-red-700">
                        {{ $errors->first() }}
                    </div>
                @endif

                <form method="POST" action="{{ route('login.store') }}" class="mt-8">
                    @csrf

                    <div>
                        <label for="login"
                            class="mb-2 block
                               text-[12px] font-semibold">
                            Email atau Username
                        </label>

                        <input type="text" id="login" name="login" value="{{ old('login') }}"
                            autocomplete="username" autofocus required placeholder="name@company.com"
                            class="h-12 w-full
                               rounded-xl
                               border border-black/[0.1]
                               bg-white px-4
                               text-[13px]
                               outline-none
                               transition
                               placeholder:text-[#aaa79f]
                               focus:border-[#e8742a]
                               focus:ring-4
                               focus:ring-orange-100">
                    </div>

                    <div class="mt-5">
                        <div class="mb-2 flex
                               items-center justify-between">
                            <label for="password" class="text-[12px]
                                   font-semibold">
                                Password
                            </label>
                        </div>

                        <input type="password" id="password" name="password" autocomplete="current-password" required
                            placeholder="••••••••"
                            class="h-12 w-full
                               rounded-xl
                               border border-black/[0.1]
                               bg-white px-4
                               text-[13px]
                               outline-none
                               transition
                               placeholder:text-[#aaa79f]
                               focus:border-[#e8742a]
                               focus:ring-4
                               focus:ring-orange-100">
                    </div>

                    <label
                        class="mt-5 flex cursor-pointer
                           items-center gap-2.5
                           text-[12px]
                           text-[#69665f]">
                        <input type="checkbox" name="remember" value="1"
                            class="h-4 w-4
                               rounded border-[#cbc8c0]
                               accent-[#e8742a]">

                        Tetap masuk di perangkat ini
                    </label>

                    <button type="submit"
                        class="mt-7 flex h-12 w-full
                           cursor-pointer
                           items-center justify-center
                           gap-2 rounded-xl
                           bg-[#1d1d1b]
                           text-[13px] font-semibold
                           text-white
                           transition
                           hover:bg-black">
                        Masuk ke Evognito One

                        <span>→</span>
                    </button>
                </form>

                <div
                    class="mt-8 border-t
                       border-black/[0.07]
                       pt-6 text-center">
                    <a href="{{ url('/') }}"
                        class="text-[11px] font-medium
                           text-[#88857d]
                           transition hover:text-black">
                        ← Kembali ke Evognito One
                    </a>
                </div>
            </div>
        </section>
    </div>

</body>

</html>
