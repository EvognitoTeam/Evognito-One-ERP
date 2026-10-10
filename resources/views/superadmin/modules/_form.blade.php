@php
    $isEdit = isset($module) && $module?->exists;

    $currentType = old('type', $module?->type ?? 'internal');

    $currentStatus = old('status', $module?->status ?? 'active');

    $currentPricingType = old('pricing_type', $module?->pricing_type ?? 'fixed');

    $currentBillingInterval = old('billing_interval', $module?->billing_interval ?? 'monthly');

    $currentBillingScope = old('billing_user_scope', $module?->billing_user_scope ?? 'active_employees');
@endphp


<div x-data="moduleForm({
    type: @js($currentType),
    pricingType: @js($currentPricingType),
})" class="space-y-5">

    {{-- ===================================================== --}}
    {{-- VALIDATION ERROR                                      --}}
    {{-- ===================================================== --}}

    @if ($errors->any())
        <div
            class="rounded-2xl
                   border border-red-200
                   bg-red-50
                   px-5 py-4">
            <div class="text-[13px]
                       font-semibold
                       text-red-700">
                Ada beberapa data yang perlu diperbaiki.
            </div>

            <ul
                class="mt-2
                       list-disc
                       space-y-1
                       pl-5
                       text-[12px]
                       text-red-600">
                @foreach ($errors->all() as $error)
                    <li>
                        {{ $error }}
                    </li>
                @endforeach
            </ul>
        </div>
    @endif


    {{-- ===================================================== --}}
    {{-- BASIC INFORMATION                                     --}}
    {{-- ===================================================== --}}

    <section class="rounded-2xl
               border border-black/[0.07]
               bg-white
               p-6">
        <div>
            <h2 class="text-[16px]
                       font-semibold
                       tracking-[-.02em]">
                Application Information
            </h2>

            <p
                class="mt-1
                       text-[12px]
                       leading-5
                       text-[#99968e]">
                Informasi dasar aplikasi yang akan
                ditampilkan pada Evognito One.
            </p>
        </div>


        <div class="mt-6
                   grid gap-5
                   md:grid-cols-2">
            {{-- NAME --}}
            <div>
                <label for="name"
                    class="mb-2 block
                           text-[12px]
                           font-semibold
                           text-[#56534d]">
                    Application Name
                    <span class="text-red-500">*</span>
                </label>

                <input id="name" type="text" name="name"
                    value="{{ old('name', $module?->name) }}"
                    required autocomplete="off" placeholder="HRIS"
                    class="h-11 w-full
                           rounded-xl
                           border border-black/[0.09]
                           bg-[#faf9f6]
                           px-3.5
                           text-[13px]
                           text-[#292825]
                           outline-none
                           transition
                           placeholder:text-[#aaa79f]
                           focus:border-black/[0.2]
                           focus:bg-white">
            </div>


            {{-- CODE --}}
            <div>
                <label for="code"
                    class="mb-2 block
                           text-[12px]
                           font-semibold
                           text-[#56534d]">
                    Application Code
                    <span class="text-red-500">*</span>
                </label>

                <input id="code" type="text" name="code"
                    value="{{ old('code', $module?->code) }}"
                    required autocomplete="off" placeholder="hris"
                    class="h-11 w-full
                           rounded-xl
                           border border-black/[0.09]
                           bg-[#faf9f6]
                           px-3.5
                           font-mono
                           text-[12px]
                           text-[#292825]
                           outline-none
                           transition
                           placeholder:text-[#aaa79f]
                           focus:border-black/[0.2]
                           focus:bg-white">

                <p
                    class="mt-2
                           text-[11px]
                           leading-5
                           text-[#99968e]">
                    Gunakan huruf kecil, angka,
                    dan underscore. Contoh:
                    <strong>hris</strong> atau
                    <strong>sales_force</strong>.
                </p>
            </div>


            {{-- VERSION --}}
            <div>
                <label for="version"
                    class="mb-2 block
                           text-[12px]
                           font-semibold
                           text-[#56534d]">
                    Version
                </label>

                <input id="version" type="text" name="version"
                    value="{{ old('version', $module?->version ?? '1.0.0') }}"
                    placeholder="1.0.0"
                    class="h-11 w-full
                           rounded-xl
                           border border-black/[0.09]
                           bg-[#faf9f6]
                           px-3.5
                           font-mono
                           text-[12px]
                           outline-none
                           transition
                           focus:border-black/[0.2]
                           focus:bg-white">
            </div>


            {{-- CATEGORY --}}
            <div>
                <label for="category"
                    class="mb-2 block
                           text-[12px]
                           font-semibold
                           text-[#56534d]">
                    Category
                </label>

                <input id="category" type="text" name="category"
                    value="{{ old('category', $module?->category) }}"
                    placeholder="People"
                    class="h-11 w-full
                           rounded-xl
                           border border-black/[0.09]
                           bg-[#faf9f6]
                           px-3.5
                           text-[13px]
                           outline-none
                           transition
                           focus:border-black/[0.2]
                           focus:bg-white">

                <p class="mt-2
                           text-[11px]
                           text-[#99968e]">
                    Contoh: People, Sales,
                    Learning, Operations.
                </p>
            </div>


            {{-- ICON --}}
            <div class="md:col-span-2">
                <label for="icon"
                    class="mb-2 block
                           text-[12px]
                           font-semibold
                           text-[#56534d]">
                    Icon
                </label>

                <input id="icon" type="text" name="icon"
                    value="{{ old('icon', $module?->icon) }}"
                    placeholder="users"
                    class="h-11 w-full
                           rounded-xl
                           border border-black/[0.09]
                           bg-[#faf9f6]
                           px-3.5
                           text-[13px]
                           outline-none
                           transition
                           focus:border-black/[0.2]
                           focus:bg-white">
            </div>


            {{-- DESCRIPTION --}}
            <div class="md:col-span-2">
                <label for="description"
                    class="mb-2 block
                           text-[12px]
                           font-semibold
                           text-[#56534d]">
                    Description
                </label>

                <textarea id="description" name="description" rows="4" placeholder="Describe this application..."
                    class="w-full
                           resize-none
                           rounded-xl
                           border border-black/[0.09]
                           bg-[#faf9f6]
                           px-3.5 py-3
                           text-[13px]
                           leading-6
                           outline-none
                           transition
                           focus:border-black/[0.2]
                           focus:bg-white">{{ old('description', $module?->description) }}</textarea>
            </div>
        </div>
    </section>


    {{-- ===================================================== --}}
    {{-- APPLICATION CONFIGURATION                             --}}
    {{-- ===================================================== --}}

    <section class="rounded-2xl
               border border-black/[0.07]
               bg-white
               p-6">
        <div>
            <h2 class="text-[16px]
                       font-semibold
                       tracking-[-.02em]">
                Application Configuration
            </h2>

            <p
                class="mt-1
                       text-[12px]
                       leading-5
                       text-[#99968e]">
                Tentukan apakah aplikasi berjalan
                langsung di Evognito One atau melalui
                aplikasi eksternal.
            </p>
        </div>


        <div class="mt-6
                   grid gap-5
                   md:grid-cols-2">
            {{-- TYPE --}}
            <div>
                <label for="type"
                    class="mb-2 block
                           text-[12px]
                           font-semibold
                           text-[#56534d]">
                    Application Type
                    <span class="text-red-500">*</span>
                </label>

                <select id="type" name="type" x-model="type" required
                    class="h-11 w-full
                           rounded-xl
                           border border-black/[0.09]
                           bg-[#faf9f6]
                           px-3.5
                           text-[13px]
                           outline-none
                           transition
                           focus:border-black/[0.2]
                           focus:bg-white">
                    <option value="internal">
                        Internal
                    </option>

                    <option value="external">
                        External
                    </option>
                </select>

                <p
                    class="mt-2
                           text-[11px]
                           leading-5
                           text-[#99968e]">
                    Internal berjalan di dalam
                    Evognito One. External membuka
                    aplikasi atau sistem lain.
                </p>
            </div>


            {{-- STATUS --}}
            <div>
                <label for="status"
                    class="mb-2 block
                           text-[12px]
                           font-semibold
                           text-[#56534d]">
                    Status
                    <span class="text-red-500">*</span>
                </label>

                <select id="status" name="status" required
                    class="h-11 w-full
                           rounded-xl
                           border border-black/[0.09]
                           bg-[#faf9f6]
                           px-3.5
                           text-[13px]
                           outline-none
                           transition
                           focus:border-black/[0.2]
                           focus:bg-white">
                    <option value="active" @selected($currentStatus === 'active')>
                        Active
                    </option>

                    <option value="inactive" @selected($currentStatus === 'inactive')>
                        Inactive
                    </option>
                </select>
            </div>


            {{-- WORKSPACE PATH --}}
            <div x-show="type === 'internal'" x-cloak>
                <label for="workspace_path"
                    class="mb-2 block
                           text-[12px]
                           font-semibold
                           text-[#56534d]">
                    Workspace Path
                </label>

                <div class="relative">
                    <span
                        class="pointer-events-none
                               absolute
                               left-3.5
                               top-1/2
                               -translate-y-1/2
                               text-[13px]
                               font-medium
                               text-[#aaa79f]">
                        /
                    </span>

                    <input id="workspace_path" type="text" name="workspace_path"
                        value="{{ old('workspace_path', $module?->workspace_path) }}"
                        autocomplete="off" placeholder="hris"
                        class="h-11 w-full
                               rounded-xl
                               border border-black/[0.09]
                               bg-[#faf9f6]
                               pl-7 pr-3.5
                               font-mono
                               text-[12px]
                               outline-none
                               transition
                               placeholder:text-[#aaa79f]
                               focus:border-black/[0.2]
                               focus:bg-white">
                </div>

                <p
                    class="mt-2
                           text-[11px]
                           leading-5
                           text-[#99968e]">
                    Khusus aplikasi internal.
                    Contoh:
                    <strong>hris</strong>,
                    <strong>performance</strong>,
                    atau <strong>sales</strong>.
                </p>
            </div>


            {{-- ENTRY URL --}}
            <div x-show="type === 'external'" x-cloak>
                <label for="entry_url"
                    class="mb-2 block
                           text-[12px]
                           font-semibold
                           text-[#56534d]">
                    Entry URL
                </label>

                <input id="entry_url" type="text" name="entry_url"
                    value="{{ old('entry_url', $module?->entry_url) }}"
                    placeholder="https://app.example.com"
                    class="h-11 w-full
                           rounded-xl
                           border border-black/[0.09]
                           bg-[#faf9f6]
                           px-3.5
                           text-[12px]
                           outline-none
                           transition
                           focus:border-black/[0.2]
                           focus:bg-white">

                <p
                    class="mt-2
                           text-[11px]
                           leading-5
                           text-[#99968e]">
                    Digunakan untuk aplikasi
                    external.
                </p>
            </div>


            {{-- API URL --}}
            <div>
                <label for="api_url"
                    class="mb-2 block
                           text-[12px]
                           font-semibold
                           text-[#56534d]">
                    API URL
                </label>

                <input id="api_url" type="text" name="api_url"
                    value="{{ old('api_url', $module?->api_url) }}"
                    placeholder="https://api.example.com"
                    class="h-11 w-full
                           rounded-xl
                           border border-black/[0.09]
                           bg-[#faf9f6]
                           px-3.5
                           text-[12px]
                           outline-none
                           transition
                           focus:border-black/[0.2]
                           focus:bg-white">

                <p
                    class="mt-2
                           text-[11px]
                           leading-5
                           text-[#99968e]">
                    Opsional. Dapat digunakan oleh
                    aplikasi mobile atau integrasi.
                </p>
            </div>
        </div>


        {{-- INTERNAL INFO --}}
        <div x-show="type === 'internal'" x-cloak
            class="mt-5
                   rounded-xl
                   border border-blue-100
                   bg-blue-50/60
                   px-4 py-3">
            <div class="text-[12px]
                       font-semibold
                       text-blue-800">
                Internal Application
            </div>

            <p
                class="mt-1
                       text-[11px]
                       leading-5
                       text-blue-700/70">
                URL akan dibuat berdasarkan
                organization slug dan Workspace Path.
                Contoh Workspace Path
                <strong>hris</strong> menghasilkan
                URL seperti
                <strong>/demo-company/hris</strong>.
            </p>
        </div>
    </section>


    {{-- ===================================================== --}}
    {{-- PRICING                                               --}}
    {{-- ===================================================== --}}

    <section class="rounded-2xl
               border border-black/[0.07]
               bg-white
               p-6">
        <div>
            <h2 class="text-[16px]
                       font-semibold
                       tracking-[-.02em]">
                Default Pricing
            </h2>

            <p
                class="mt-1
                       text-[12px]
                       leading-5
                       text-[#99968e]">
                Harga default ketika aplikasi tidak
                mendapatkan harga dari plan atau
                organization-specific pricing.
            </p>
        </div>


        <div class="mt-6
                   grid gap-5
                   md:grid-cols-2">
            {{-- PRICING TYPE --}}
            <div>
                <label for="pricing_type"
                    class="mb-2 block
                           text-[12px]
                           font-semibold
                           text-[#56534d]">
                    Pricing Type
                </label>

                <select id="pricing_type" name="pricing_type" x-model="pricingType"
                    class="h-11 w-full
                           rounded-xl
                           border border-black/[0.09]
                           bg-[#faf9f6]
                           px-3.5
                           text-[13px]
                           outline-none
                           transition
                           focus:border-black/[0.2]
                           focus:bg-white">
                    <option value="fixed">
                        Fixed
                    </option>

                    <option value="per_user">
                        Per User
                    </option>

                    <option value="hybrid">
                        Hybrid
                    </option>
                </select>
            </div>


            {{-- BILLING INTERVAL --}}
            <div>
                <label for="billing_interval"
                    class="mb-2 block
                           text-[12px]
                           font-semibold
                           text-[#56534d]">
                    Billing Interval
                </label>

                <select id="billing_interval" name="billing_interval"
                    class="h-11 w-full
                           rounded-xl
                           border border-black/[0.09]
                           bg-[#faf9f6]
                           px-3.5
                           text-[13px]
                           outline-none
                           transition
                           focus:border-black/[0.2]
                           focus:bg-white">
                    <option value="monthly" @selected($currentBillingInterval === 'monthly')>
                        Monthly
                    </option>

                    <option value="yearly" @selected($currentBillingInterval === 'yearly')>
                        Yearly
                    </option>
                </select>
            </div>


            {{-- FIXED PRICE --}}
            <div x-show="
                    pricingType === 'fixed'
                    ||
                    pricingType === 'hybrid'
                "
                x-cloak>
                <label for="fixed_price"
                    class="mb-2 block
                           text-[12px]
                           font-semibold
                           text-[#56534d]">
                    Fixed Price
                </label>

                <div class="relative">
                    <span
                        class="pointer-events-none
                               absolute
                               left-3.5
                               top-1/2
                               -translate-y-1/2
                               text-[12px]
                               font-semibold
                               text-[#99968e]">
                        Rp
                    </span>

                    <input id="fixed_price" type="number" name="fixed_price" min="0" step="1"
                        value="{{ old('fixed_price', $module?->fixed_price ?? 0) }}"
                        class="h-11 w-full
                               rounded-xl
                               border border-black/[0.09]
                               bg-[#faf9f6]
                               pl-10 pr-3.5
                               text-[13px]
                               outline-none
                               transition
                               focus:border-black/[0.2]
                               focus:bg-white">
                </div>
            </div>


            {{-- PER USER --}}
            <div x-show="
                    pricingType === 'per_user'
                    ||
                    pricingType === 'hybrid'
                "
                x-cloak>
                <label for="price_per_user"
                    class="mb-2 block
                           text-[12px]
                           font-semibold
                           text-[#56534d]">
                    Price Per User
                </label>

                <div class="relative">
                    <span
                        class="pointer-events-none
                               absolute
                               left-3.5
                               top-1/2
                               -translate-y-1/2
                               text-[12px]
                               font-semibold
                               text-[#99968e]">
                        Rp
                    </span>

                    <input id="price_per_user" type="number" name="price_per_user" min="0" step="1"
                        value="{{ old('price_per_user', $module?->price_per_user ?? 0) }}"
                        class="h-11 w-full
                               rounded-xl
                               border border-black/[0.09]
                               bg-[#faf9f6]
                               pl-10 pr-3.5
                               text-[13px]
                               outline-none
                               transition
                               focus:border-black/[0.2]
                               focus:bg-white">
                </div>
            </div>


            {{-- INCLUDED USERS --}}
            <div x-show="
                    pricingType === 'per_user'
                    ||
                    pricingType === 'hybrid'
                "
                x-cloak>
                <label for="included_users"
                    class="mb-2 block
                           text-[12px]
                           font-semibold
                           text-[#56534d]">
                    Included Users
                </label>

                <input id="included_users" type="number" name="included_users" min="0" step="1"
                    value="{{ old('included_users', $module?->included_users ?? 0) }}"
                    class="h-11 w-full
                           rounded-xl
                           border border-black/[0.09]
                           bg-[#faf9f6]
                           px-3.5
                           text-[13px]
                           outline-none
                           transition
                           focus:border-black/[0.2]
                           focus:bg-white">
            </div>


            {{-- BILLING SCOPE --}}
            <div x-show="
                    pricingType === 'per_user'
                    ||
                    pricingType === 'hybrid'
                "
                x-cloak>
                <label for="billing_user_scope"
                    class="mb-2 block
                           text-[12px]
                           font-semibold
                           text-[#56534d]">
                    Billing User Scope
                </label>

                <select id="billing_user_scope" name="billing_user_scope"
                    class="h-11 w-full
                           rounded-xl
                           border border-black/[0.09]
                           bg-[#faf9f6]
                           px-3.5
                           text-[13px]
                           outline-none
                           transition
                           focus:border-black/[0.2]
                           focus:bg-white">
                    @foreach ([
        'all_users' => 'All Users',

        'employees' => 'Employees',

        'active_employees' => 'Active Employees',

        'licensed_users' => 'Licensed Users',

        'active_module_users' => 'Active Module Users',

        'students' => 'Students',

        'teachers' => 'Teachers',
    ] as $value => $label)
                        <option value="{{ $value }}" @selected($currentBillingScope === $value)>
                            {{ $label }}
                        </option>
                    @endforeach
                </select>
            </div>


            {{-- MINIMUM USERS --}}
            <div x-show="
                    pricingType === 'per_user'
                    ||
                    pricingType === 'hybrid'
                "
                x-cloak>
                <label for="minimum_users"
                    class="mb-2 block
                           text-[12px]
                           font-semibold
                           text-[#56534d]">
                    Minimum Users
                </label>

                <input id="minimum_users" type="number" name="minimum_users" min="0" step="1"
                    value="{{ old('minimum_users', $module?->minimum_users) }}"
                    placeholder="0"
                    class="h-11 w-full
                           rounded-xl
                           border border-black/[0.09]
                           bg-[#faf9f6]
                           px-3.5
                           text-[13px]
                           outline-none
                           transition
                           focus:border-black/[0.2]
                           focus:bg-white">
            </div>


            {{-- MAXIMUM USERS --}}
            <div x-show="
                    pricingType === 'per_user'
                    ||
                    pricingType === 'hybrid'
                "
                x-cloak>
                <label for="maximum_users"
                    class="mb-2 block
                           text-[12px]
                           font-semibold
                           text-[#56534d]">
                    Maximum Users
                </label>

                <input id="maximum_users" type="number" name="maximum_users" min="0" step="1"
                    value="{{ old('maximum_users', $module?->maximum_users) }}"
                    placeholder="Unlimited"
                    class="h-11 w-full
                           rounded-xl
                           border border-black/[0.09]
                           bg-[#faf9f6]
                           px-3.5
                           text-[13px]
                           outline-none
                           transition
                           focus:border-black/[0.2]
                           focus:bg-white">

                <p class="mt-2
                           text-[11px]
                           text-[#99968e]">
                    Kosongkan jika tidak ada batas.
                </p>
            </div>
        </div>
    </section>


    {{-- ===================================================== --}}
    {{-- SAVE                                                  --}}
    {{-- ===================================================== --}}

    <div
        class="sticky
               bottom-4
               z-20
               flex flex-col gap-3
               rounded-2xl
               border border-black/[0.07]
               bg-white/95
               p-4
               shadow-[0_16px_60px_rgba(0,0,0,.08)]
               backdrop-blur-xl
               sm:flex-row
               sm:items-center
               sm:justify-between">
        <div>
            <div class="text-[12px]
                       font-semibold
                       text-[#373530]">
                {{ $isEdit ? 'Update Application' : 'Create Application' }}
            </div>

            <div class="mt-0.5
                       text-[11px]
                       text-[#99968e]">
                Pastikan configuration dan pricing
                sudah sesuai sebelum disimpan.
            </div>
        </div>


        <div class="flex
                   items-center
                   gap-2">
            <a href="{{ route('superadmin.modules.index') }}"
                class="inline-flex
                       h-10
                       items-center
                       justify-center
                       rounded-xl
                       border border-black/[0.08]
                       bg-white
                       px-4
                       text-[12px]
                       font-semibold
                       text-[#56534d]
                       transition
                       hover:bg-[#f6f5f1]">
                Cancel
            </a>

            <button type="submit"
                class="inline-flex
                       h-10
                       items-center
                       justify-center
                       gap-2
                       rounded-xl
                       bg-[#1d1d1b]
                       px-5
                       text-[12px]
                       font-semibold
                       text-white
                       transition
                       hover:bg-black">
                <svg class="h-4 w-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8">
                    <path d="M19 21H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h11l5 5v11a2 2 0 0 1-2 2Z" />

                    <path d="M17 21v-8H7v8" />

                    <path d="M7 3v5h8" />
                </svg>

                {{ $submitLabel ?? ($isEdit ? 'Save Changes' : 'Create Application') }}
            </button>
        </div>
    </div>
</div>


@once
    <script>
        document.addEventListener(
            'alpine:init',
            () => {
                Alpine.data(
                    'moduleForm',
                    (initial = {}) => ({
                        type: initial.type ||
                            'internal',

                        pricingType: initial.pricingType ||
                            'fixed',
                    })
                );
            }
        );


        document.addEventListener(
            'DOMContentLoaded',
            () => {
                const nameInput =
                    document.getElementById(
                        'name'
                    );

                const codeInput =
                    document.getElementById(
                        'code'
                    );

                if (
                    !nameInput ||
                    !codeInput
                ) {
                    return;
                }

                let codeChangedManually =
                    codeInput.value.trim()
                    .length > 0;


                codeInput.addEventListener(
                    'input',
                    () => {
                        codeChangedManually =
                            codeInput.value
                            .trim()
                            .length > 0;
                    }
                );


                nameInput.addEventListener(
                    'input',
                    () => {
                        if (
                            codeChangedManually
                        ) {
                            return;
                        }

                        codeInput.value =
                            nameInput.value
                            .toLowerCase()
                            .trim()
                            .replace(
                                /[^a-z0-9]+/g,
                                '_'
                            )
                            .replace(
                                /^_+|_+$/g,
                                ''
                            );
                    }
                );


                const minInput =
                    document.getElementById(
                        'minimum_users'
                    );

                const maxInput =
                    document.getElementById(
                        'maximum_users'
                    );

                const validateSeats = () => {
                    if (
                        !minInput ||
                        !maxInput
                    ) {
                        return;
                    }

                    maxInput.setCustomValidity('');

                    const minimum =
                        parseInt(
                            minInput.value || '0',
                            10
                        );

                    const maximum =
                        maxInput.value ?
                        parseInt(
                            maxInput.value,
                            10
                        ) :
                        null;

                    if (
                        maximum !== null &&
                        maximum < minimum
                    ) {
                        maxInput.setCustomValidity(
                            'Maximum users tidak boleh lebih kecil dari minimum users.'
                        );
                    }
                };


                minInput?.addEventListener(
                    'input',
                    validateSeats
                );

                maxInput?.addEventListener(
                    'input',
                    validateSeats
                );
            }
        );
    </script>
@endonce
