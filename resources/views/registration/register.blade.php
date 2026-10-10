@extends('layouts.public')

@section('title', 'Register — Evognito One')

@section('content')

    <section class="min-h-screen
           bg-[#f7f6f2]
           pb-20 pt-[120px]">
        <div class="mx-auto w-[min(100%-32px,720px)]">
            <div class="rounded-2xl
                   border border-black/[0.07]
                   bg-white p-7">
                <div
                    class="text-[10px] font-bold
                       uppercase tracking-[.13em]
                       text-[#e8742a]">
                    Create Workspace
                </div>

                <h1 class="mt-3 text-[32px]
                       font-semibold
                       tracking-[-.045em]">
                    Register your organization.
                </h1>

                @if ($mode === 'approval')
                    <p class="mt-3 text-[12px]
                           leading-6 text-[#77746d]">
                        Setelah dikirim,
                        registrasi akan direview
                        terlebih dahulu oleh Evognito.
                    </p>
                @endif

                @if (session('success'))
                    <div
                        class="mt-6 rounded-xl
                           border border-emerald-200
                           bg-emerald-50 p-4
                           text-[11px]
                           text-emerald-700">
                        {{ session('success') }}
                    </div>
                @endif

                @if ($errors->any())
                    <div
                        class="mt-6 rounded-xl
                           border border-red-200
                           bg-red-50 p-4
                           text-[10px]
                           text-red-700">
                        @foreach ($errors->all() as $error)
                            <div>{{ $error }}</div>
                        @endforeach
                    </div>
                @endif

                <form method="POST" action="{{ route('registration.self.store') }}"
                    class="mt-7">
                    @csrf

                    <div class="grid gap-5
                           md:grid-cols-2">
                        <div class="md:col-span-2">
                            <label class="mb-2 block text-[10px] font-semibold">
                                Organization Name
                            </label>

                            <input type="text" name="organization_name" required value="{{ old('organization_name') }}"
                                class="h-11 w-full rounded-xl border border-black/[0.09] bg-[#faf9f6] px-3 text-[11px]">
                        </div>

                        <div>
                            <label class="mb-2 block text-[10px] font-semibold">
                                Organization Type
                            </label>

                            <select name="organization_type"
                                class="h-11 w-full rounded-xl border border-black/[0.09] bg-[#faf9f6] px-3 text-[11px]">
                                <option value="business">
                                    Business
                                </option>

                                <option value="education">
                                    Education
                                </option>

                                <option value="nonprofit">
                                    Nonprofit
                                </option>

                                <option value="government">
                                    Government
                                </option>

                                <option value="other">
                                    Other
                                </option>
                            </select>
                        </div>

                        <div>
                            <label class="mb-2 block text-[10px] font-semibold">
                                Owner Name
                            </label>

                            <input type="text" name="owner_name" required value="{{ old('owner_name') }}"
                                class="h-11 w-full rounded-xl border border-black/[0.09] bg-[#faf9f6] px-3 text-[11px]">
                        </div>

                        <div>
                            <label class="mb-2 block text-[10px] font-semibold">
                                Email
                            </label>

                            <input type="email" name="email" required value="{{ old('email') }}"
                                class="h-11 w-full rounded-xl border border-black/[0.09] bg-[#faf9f6] px-3 text-[11px]">
                        </div>

                        <div>
                            <label class="mb-2 block text-[10px] font-semibold">
                                Phone
                            </label>

                            <input type="text" name="phone" value="{{ old('phone') }}"
                                class="h-11 w-full rounded-xl border border-black/[0.09] bg-[#faf9f6] px-3 text-[11px]">
                        </div>

                        <div>
                            <label class="mb-2 block text-[10px] font-semibold">
                                Password
                            </label>

                            <input type="password" name="password" required
                                class="h-11 w-full rounded-xl border border-black/[0.09] bg-[#faf9f6] px-3 text-[11px]">
                        </div>

                        <div>
                            <label class="mb-2 block text-[10px] font-semibold">
                                Confirm Password
                            </label>

                            <input type="password" name="password_confirmation" required
                                class="h-11 w-full rounded-xl border border-black/[0.09] bg-[#faf9f6] px-3 text-[11px]">
                        </div>
                    </div>

                    <button type="submit"
                        class="mt-7 h-11
                           rounded-xl
                           bg-[#1d1d1b]
                           px-5 text-[10px]
                           font-semibold text-white">
                        Submit Registration →
                    </button>
                </form>
            </div>
        </div>
    </section>

@endsection
