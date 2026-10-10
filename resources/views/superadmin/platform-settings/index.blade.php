@extends('layouts.superadmin')

@section('title', 'Platform Settings')
@section('page_name', 'Platform Settings')

@section('content')

    <div>
        <div
            class="text-[11px]
               font-bold uppercase
               tracking-[.1em]
               text-[#e8742a]">
            System
        </div>

        <h1 class="mt-2 text-[30px]
               font-semibold
               tracking-[-.045em]">
            Platform Settings
        </h1>

        <p class="mt-2 max-w-2xl
               text-[14px]
               leading-6
               text-[#85827b]">
            Konfigurasi global yang berlaku
            untuk Evognito One.
        </p>
    </div>

    @if (session('success'))
        <div
            class="mt-5 rounded-xl
               border border-emerald-200
               bg-emerald-50 p-4
               text-[13px]
               text-emerald-700">
            {{ session('success') }}
        </div>
    @endif

    @if ($errors->any())
        <div
            class="mt-5 rounded-xl
               border border-red-200
               bg-red-50 p-4
               text-[13px]
               text-red-700">
            @foreach ($errors->all() as $error)
                <div>• {{ $error }}</div>
            @endforeach
        </div>
    @endif

    <form method="POST" action="{{ route('superadmin.platform-settings.update') }}" class="mt-7">
        @csrf
        @method('PUT')

        <div class="grid gap-5
               xl:grid-cols-[1fr_360px]">
            <div class="space-y-5">

                {{-- GENERAL --}}
                <section
                    class="rounded-2xl
                       border border-black/[0.07]
                       bg-white p-6">
                    <h2 class="text-[16px]
                           font-semibold">
                        General
                    </h2>

                    <p class="mt-1 text-[12px]
                           text-[#99968e]">
                        Default configuration
                        platform.
                    </p>

                    <div class="mt-6 grid gap-5
                           md:grid-cols-2">
                        <div class="md:col-span-2">
                            <label class="mb-2 block text-[13px] font-medium">
                                Platform Name
                            </label>

                            <input type="text" name="platform_name" required
                                value="{{ old('platform_name', $settings['platform_name']) }}"
                                class="h-11 w-full rounded-xl border border-black/[0.09] bg-[#faf9f6] px-3.5 text-[13px]">
                        </div>

                        <div>
                            <label class="mb-2 block text-[13px] font-medium">
                                Support Email
                            </label>

                            <input type="email" name="support_email"
                                value="{{ old('support_email', $settings['support_email']) }}"
                                class="h-11 w-full rounded-xl border border-black/[0.09] bg-[#faf9f6] px-3.5 text-[13px]">
                        </div>

                        <div>
                            <label class="mb-2 block text-[13px] font-medium">
                                Support Phone
                            </label>

                            <input type="text" name="support_phone"
                                value="{{ old('support_phone', $settings['support_phone']) }}"
                                class="h-11 w-full rounded-xl border border-black/[0.09] bg-[#faf9f6] px-3.5 text-[13px]">
                        </div>

                        <div>
                            <label class="mb-2 block text-[13px] font-medium">
                                Currency
                            </label>

                            <select name="default_currency"
                                class="h-11 w-full rounded-xl border border-black/[0.09] bg-[#faf9f6] px-3 text-[13px]">
                                <option value="IDR" @selected($settings['default_currency'] === 'IDR')>
                                    IDR — Indonesian Rupiah
                                </option>

                                <option value="USD" @selected($settings['default_currency'] === 'USD')>
                                    USD
                                </option>
                            </select>
                        </div>

                        <div>
                            <label class="mb-2 block text-[13px] font-medium">
                                Locale
                            </label>

                            <select name="default_locale"
                                class="h-11 w-full rounded-xl border border-black/[0.09] bg-[#faf9f6] px-3 text-[13px]">
                                <option value="id" @selected($settings['default_locale'] === 'id')>
                                    Bahasa Indonesia
                                </option>

                                <option value="en" @selected($settings['default_locale'] === 'en')>
                                    English
                                </option>
                            </select>
                        </div>

                        <div class="md:col-span-2">
                            <label class="mb-2 block text-[13px] font-medium">
                                Default Timezone
                            </label>

                            <select name="default_timezone"
                                class="h-11 w-full rounded-xl border border-black/[0.09] bg-[#faf9f6] px-3 text-[13px]">
                                @foreach ([
            'Asia/Jakarta' => 'Asia/Jakarta — WIB',
            'Asia/Makassar' => 'Asia/Makassar — WITA',
            'Asia/Jayapura' => 'Asia/Jayapura — WIT',
            'UTC' => 'UTC',
        ] as $value => $label)
                                    <option value="{{ $value }}" @selected($settings['default_timezone'] === $value)>
                                        {{ $label }}
                                    </option>
                                @endforeach
                            </select>
                        </div>
                    </div>
                </section>

                {{-- REGISTRATION --}}
                <section
                    class="rounded-2xl
                       border border-black/[0.07]
                       bg-white p-6">
                    <h2 class="text-[16px]
                           font-semibold">
                        Registration
                    </h2>

                    <div class="mt-6 grid gap-5
                           md:grid-cols-2">
                        <div>
                            <label class="mb-2 block text-[13px] font-medium">
                                Registration Mode
                            </label>

                            <select name="registration_mode"
                                class="h-11 w-full rounded-xl border border-black/[0.09] bg-[#faf9f6] px-3 text-[13px]">
                                <option value="invite_only" @selected($settings['registration_mode'] === 'invite_only')>
                                    Invite Only
                                </option>

                                <option value="approval" @selected($settings['registration_mode'] === 'approval')>
                                    Self Register + Approval
                                </option>

                                <option value="open" @selected($settings['registration_mode'] === 'open')>
                                    Open Registration
                                </option>
                            </select>
                        </div>

                        <div>
                            <label class="mb-2 block text-[13px] font-medium">
                                Default Invite Expiry
                            </label>

                            <div class="relative">
                                <input type="number" name="default_invite_expiry_hours" min="1" max="2160"
                                    required
                                    value="{{ old('default_invite_expiry_hours', $settings['default_invite_expiry_hours']) }}"
                                    class="h-11 w-full rounded-xl border border-black/[0.09] bg-[#faf9f6] px-3 pr-16 text-[13px]">

                                <span
                                    class="absolute right-3
                                       top-1/2
                                       -translate-y-1/2
                                       text-[11px]
                                       text-[#99968e]">
                                    hours
                                </span>
                            </div>
                        </div>
                    </div>
                </section>

                {{-- BILLING --}}
                <section
                    class="rounded-2xl
                       border border-black/[0.07]
                       bg-white p-6">
                    <h2 class="text-[16px]
                           font-semibold">
                        Billing
                    </h2>

                    <p
                        class="mt-1 text-[12px]
                           leading-5
                           text-[#99968e]">
                        Invoice generation day akan
                        digunakan oleh automatic
                        billing scheduler yang kita
                        aktifkan setelah seluruh
                        Superadmin selesai.
                    </p>

                    <div class="mt-6 grid gap-5
                           md:grid-cols-2">
                        <div>
                            <label class="mb-2 block text-[13px] font-medium">
                                Invoice Generation Day
                            </label>

                            <input type="number" name="invoice_generation_day" min="1" max="28" required
                                value="{{ old('invoice_generation_day', $settings['invoice_generation_day']) }}"
                                class="h-11 w-full rounded-xl border border-black/[0.09] bg-[#faf9f6] px-3 text-[13px]">

                            <p
                                class="mt-2
                                   text-[11px]
                                   text-[#99968e]">
                                Default saat ini: tanggal 25.
                            </p>
                        </div>

                        <div>
                            <label class="mb-2 block text-[13px] font-medium">
                                Invoice Due
                            </label>

                            <div class="relative">
                                <input type="number" name="invoice_due_days" min="0" max="60" required
                                    value="{{ old('invoice_due_days', $settings['invoice_due_days']) }}"
                                    class="h-11 w-full rounded-xl border border-black/[0.09] bg-[#faf9f6] px-3 pr-14 text-[13px]">

                                <span
                                    class="absolute right-3
                                       top-1/2
                                       -translate-y-1/2
                                       text-[11px]
                                       text-[#99968e]">
                                    days
                                </span>
                            </div>
                        </div>

                        <div>
                            <label class="mb-2 block text-[13px] font-medium">
                                Default Tax
                            </label>

                            <div class="relative">
                                <input type="number" step="0.01" name="default_tax_percent" min="0"
                                    max="100" required
                                    value="{{ old('default_tax_percent', $settings['default_tax_percent']) }}"
                                    class="h-11 w-full rounded-xl border border-black/[0.09] bg-[#faf9f6] px-3 pr-10 text-[13px]">

                                <span
                                    class="absolute right-3
                                       top-1/2
                                       -translate-y-1/2
                                       text-[12px]
                                       text-[#99968e]">
                                    %
                                </span>
                            </div>
                        </div>

                        <div>
                            <label class="mb-2 block text-[13px] font-medium">
                                Access Lock After Due
                            </label>

                            <div class="relative">
                                <input type="number" name="access_lock_after_due_days" min="0" max="365"
                                    required
                                    value="{{ old('access_lock_after_due_days', $settings['access_lock_after_due_days']) }}"
                                    class="h-11 w-full rounded-xl border border-black/[0.09] bg-[#faf9f6] px-3 pr-14 text-[13px]">

                                <span
                                    class="absolute right-3
                                       top-1/2
                                       -translate-y-1/2
                                       text-[11px]
                                       text-[#99968e]">
                                    days
                                </span>
                            </div>

                            <p
                                class="mt-2
                                   text-[11px]
                                   text-[#99968e]">
                                0 berarti tepat setelah
                                batas pembayaran.
                            </p>
                        </div>
                    </div>
                </section>

                {{-- SECURITY --}}
                <section
                    class="rounded-2xl
                       border border-black/[0.07]
                       bg-white p-6">
                    <h2 class="text-[16px]
                           font-semibold">
                        Security & Audit
                    </h2>

                    <div class="mt-6">
                        <label class="mb-2 block text-[13px] font-medium">
                            Audit Log Retention
                        </label>

                        <div class="relative">
                            <input type="number" name="audit_retention_days" min="30" max="3650" required
                                value="{{ old('audit_retention_days', $settings['audit_retention_days']) }}"
                                class="h-11 w-full rounded-xl border border-black/[0.09] bg-[#faf9f6] px-3 pr-14 text-[13px]">

                            <span
                                class="absolute right-3
                                   top-1/2
                                   -translate-y-1/2
                                   text-[11px]
                                   text-[#99968e]">
                                days
                            </span>
                        </div>
                    </div>
                </section>

            </div>

            <aside>
                <section
                    class="sticky top-[96px]
                       rounded-2xl
                       bg-[#1d1d1b]
                       p-6 text-white">
                    <div
                        class="text-[11px]
                           uppercase
                           tracking-[.08em]
                           text-white/40">
                        Platform
                    </div>

                    <h2 class="mt-4 text-[21px]
                           font-semibold">
                        Global configuration
                    </h2>

                    <p
                        class="mt-3 text-[13px]
                           leading-6
                           text-white/50">
                        Perubahan di halaman ini
                        berlaku pada seluruh platform,
                        bukan satu organization saja.
                    </p>

                    <button type="submit"
                        class="mt-7 h-11 w-full
                           rounded-xl
                           bg-white
                           text-[13px]
                           font-semibold
                           text-black">
                        Save Platform Settings
                    </button>
                </section>
            </aside>
        </div>
    </form>

@endsection
