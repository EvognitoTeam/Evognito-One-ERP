@php
    $plan = $plan ?? null;

    $selectedModules = $plan ? $plan->modules->keyBy('id') : collect();
@endphp

@if ($errors->any())
    <div
        class="mb-6 rounded-xl
               border border-red-200
               bg-red-50 p-4
               text-[13px]
               text-red-700">
        @foreach ($errors->all() as $error)
            <div>• {{ $error }}</div>
        @endforeach
    </div>
@endif

<div class="grid gap-5
           xl:grid-cols-[1fr_370px]">
    <div class="space-y-5">

        <section class="rounded-2xl
                   border border-black/[0.07]
                   bg-white p-6">
            <h2 class="text-[16px]
                       font-semibold">
                Plan Information
            </h2>

            <div class="mt-6 grid gap-5
                       md:grid-cols-2">
                <div>
                    <label class="mb-2 block text-[13px] font-medium">
                        Plan Name
                    </label>

                    <input type="text" id="plan-name" name="name" required
                        value="{{ old('name', $plan?->name) }}"
                        class="h-11 w-full rounded-xl border border-black/[0.09] bg-[#faf9f6] px-3.5 text-[13px]">
                </div>

                <div>
                    <label class="mb-2 block text-[13px] font-medium">
                        Code
                    </label>

                    <input type="text" id="plan-code" name="code" required
                        value="{{ old('code', $plan?->code) }}"
                        placeholder="business"
                        class="h-11 w-full rounded-xl border border-black/[0.09] bg-[#faf9f6] px-3.5 text-[13px]">
                </div>

                <div class="md:col-span-2">
                    <label class="mb-2 block text-[13px] font-medium">
                        Description
                    </label>

                    <textarea name="description" rows="4"
                        class="w-full resize-none rounded-xl border border-black/[0.09] bg-[#faf9f6] p-3.5 text-[13px]">{{ old('description', $plan?->description) }}</textarea>
                </div>
            </div>
        </section>

        <section class="rounded-2xl
                   border border-black/[0.07]
                   bg-white p-6">
            <h2 class="text-[16px] font-semibold">
                Base Pricing
            </h2>

            <p class="mt-1
                       text-[12px]
                       text-[#99968e]">
                Harga paket sebelum biaya module
                tambahan atau custom organization.
            </p>

            <div class="mt-6 grid gap-5
                       md:grid-cols-2">
                <div>
                    <label class="mb-2 block text-[13px] font-medium">
                        Billing Interval
                    </label>

                    <select name="billing_interval"
                        class="h-11 w-full rounded-xl border border-black/[0.09] bg-[#faf9f6] px-3 text-[13px]">
                        <option value="monthly" @selected(old('billing_interval', $plan?->billing_interval ?? 'monthly') === 'monthly')>
                            Monthly
                        </option>

                        <option value="yearly" @selected(old('billing_interval', $plan?->billing_interval) === 'yearly')>
                            Yearly
                        </option>
                    </select>
                </div>

                <div>
                    <label class="mb-2 block text-[13px] font-medium">
                        Base Price
                    </label>

                    <input type="number" name="base_price" min="0" required
                        value="{{ old('base_price', $plan?->base_price ?? 0) }}"
                        class="h-11 w-full rounded-xl border border-black/[0.09] bg-[#faf9f6] px-3 text-[13px]">
                </div>

                <div>
                    <label class="mb-2 block text-[13px] font-medium">
                        Price Per User
                    </label>

                    <input type="number" name="price_per_user" min="0" required
                        value="{{ old('price_per_user', $plan?->price_per_user ?? 0) }}"
                        class="h-11 w-full rounded-xl border border-black/[0.09] bg-[#faf9f6] px-3 text-[13px]">
                </div>

                <div>
                    <label class="mb-2 block text-[13px] font-medium">
                        Included Users
                    </label>

                    <input type="number" name="included_users" min="0" required
                        value="{{ old('included_users', $plan?->included_users ?? 0) }}"
                        class="h-11 w-full rounded-xl border border-black/[0.09] bg-[#faf9f6] px-3 text-[13px]">
                </div>

                <div>
                    <label class="mb-2 block text-[13px] font-medium">
                        User Scope
                    </label>

                    <select name="billing_user_scope"
                        class="h-11 w-full rounded-xl border border-black/[0.09] bg-[#faf9f6] px-3 text-[13px]">
                        @foreach ([
        'licensed_users' => 'Licensed Users',
        'all_users' => 'All Users',
        'employees' => 'Employees',
        'active_employees' => 'Active Employees',
        'active_module_users' => 'Active Module Users',
        'students' => 'Students',
        'teachers' => 'Teachers',
    ] as $value => $label)
                            <option value="{{ $value }}" @selected(old('billing_user_scope', $plan?->billing_user_scope ?? 'licensed_users') === $value)>
                                {{ $label }}
                            </option>
                        @endforeach
                    </select>
                </div>

                <div>
                    <label class="mb-2 block text-[13px] font-medium">
                        Trial Days
                    </label>

                    <input type="number" name="trial_days" min="0" max="365" required
                        value="{{ old('trial_days', $plan?->trial_days ?? 0) }}"
                        class="h-11 w-full rounded-xl border border-black/[0.09] bg-[#faf9f6] px-3 text-[13px]">
                </div>
            </div>
        </section>

        <section class="rounded-2xl
                   border border-black/[0.07]
                   bg-white p-6">
            <h2 class="text-[16px] font-semibold">
                Included Applications
            </h2>

            <p class="mt-1 text-[12px]
                       leading-5 text-[#99968e]">
                Included berarti module sudah termasuk
                harga plan. Inherit memakai harga default
                module. Override menggunakan harga khusus plan.
            </p>

            <div class="mt-6 space-y-4">
                @foreach ($modules as $module)
                    @php
                        $selected = $selectedModules->get($module->id);

                        $pivot = $selected?->pivot;
                    @endphp

                    <div
                        class="rounded-2xl
                               border border-black/[0.07]
                               bg-[#faf9f6]
                               p-5">
                        <div class="flex items-start gap-3">
                            <input type="checkbox" name="module_ids[]" value="{{ $module->id }}"
                                @checked(old('module_ids') ? in_array($module->id, old('module_ids', [])) : (bool) $selected) class="mt-1">

                            <div class="flex-1">
                                <div class="text-[14px]
                                           font-semibold">
                                    {{ $module->name }}
                                </div>

                                <div class="mt-1 text-[12px]
                                           text-[#99968e]">
                                    {{ $module->category }}
                                    · Default:
                                    {{ $module->pricingLabel() }}
                                </div>
                            </div>
                        </div>

                        <div class="mt-5 grid gap-3
                                   md:grid-cols-3">
                            <div>
                                <label class="mb-2 block text-[12px] font-medium">
                                    Billing Mode
                                </label>

                                <select name="modules[{{ $module->id }}][billing_mode]"
                                    class="h-10 w-full rounded-xl border border-black/[0.08] bg-white px-3 text-[12px]">
                                    @foreach ([
        'included' => 'Included in Plan',
        'inherit' => 'Use Module Default',
        'override' => 'Custom Plan Price',
    ] as $value => $label)
                                        <option value="{{ $value }}" @selected(($pivot?->billing_mode ?? 'included') === $value)>
                                            {{ $label }}
                                        </option>
                                    @endforeach
                                </select>
                            </div>

                            <div>
                                <label class="mb-2 block text-[12px] font-medium">
                                    Pricing Type
                                </label>

                                <select name="modules[{{ $module->id }}][pricing_type]"
                                    class="h-10 w-full rounded-xl border border-black/[0.08] bg-white px-3 text-[12px]">
                                    @foreach ([
        'fixed' => 'Fixed',
        'per_user' => 'Per User',
        'hybrid' => 'Hybrid',
    ] as $value => $label)
                                        <option value="{{ $value }}" @selected(($pivot?->pricing_type ?? ($module->pricing_type ?? 'fixed')) === $value)>
                                            {{ $label }}
                                        </option>
                                    @endforeach
                                </select>
                            </div>

                            <div>
                                <label class="mb-2 block text-[12px] font-medium">
                                    User Scope
                                </label>

                                <select name="modules[{{ $module->id }}][billing_user_scope]"
                                    class="h-10 w-full rounded-xl border border-black/[0.08] bg-white px-3 text-[12px]">
                                    @foreach ([
        'licensed_users' => 'Licensed Users',
        'active_employees' => 'Active Employees',
        'employees' => 'Employees',
        'active_module_users' => 'Active Module Users',
        'students' => 'Students',
        'teachers' => 'Teachers',
    ] as $value => $label)
                                        <option value="{{ $value }}" @selected(($pivot?->billing_user_scope ?? ($module->billing_user_scope ?? 'licensed_users')) === $value)>
                                            {{ $label }}
                                        </option>
                                    @endforeach
                                </select>
                            </div>

                            <div>
                                <label class="mb-2 block text-[12px] font-medium">
                                    Fixed Price
                                </label>

                                <input type="number" min="0" name="modules[{{ $module->id }}][fixed_price]"
                                    value="{{ $pivot?->fixed_price ?? ($module->fixed_price ?? 0) }}"
                                    class="h-10 w-full rounded-xl border border-black/[0.08] bg-white px-3 text-[12px]">
                            </div>

                            <div>
                                <label class="mb-2 block text-[12px] font-medium">
                                    Price / User
                                </label>

                                <input type="number" min="0"
                                    name="modules[{{ $module->id }}][price_per_user]"
                                    value="{{ $pivot?->price_per_user ?? ($module->price_per_user ?? 0) }}"
                                    class="h-10 w-full rounded-xl border border-black/[0.08] bg-white px-3 text-[12px]">
                            </div>

                            <div>
                                <label class="mb-2 block text-[12px] font-medium">
                                    Included Users
                                </label>

                                <input type="number" min="0"
                                    name="modules[{{ $module->id }}][included_users]"
                                    value="{{ $pivot?->included_users ?? ($module->included_users ?? 0) }}"
                                    class="h-10 w-full rounded-xl border border-black/[0.08] bg-white px-3 text-[12px]">
                            </div>

                            <div>
                                <label class="mb-2 block text-[12px] font-medium">
                                    Minimum Users
                                </label>

                                <input type="number" min="0"
                                    name="modules[{{ $module->id }}][minimum_users]"
                                    value="{{ $pivot?->minimum_users ?? $module->minimum_users }}"
                                    class="h-10 w-full rounded-xl border border-black/[0.08] bg-white px-3 text-[12px]">
                            </div>

                            <div>
                                <label class="mb-2 block text-[12px] font-medium">
                                    Maximum Users
                                </label>

                                <input type="number" min="0"
                                    name="modules[{{ $module->id }}][maximum_users]"
                                    value="{{ $pivot?->maximum_users ?? $module->maximum_users }}"
                                    class="h-10 w-full rounded-xl border border-black/[0.08] bg-white px-3 text-[12px]">
                            </div>
                        </div>
                    </div>
                @endforeach
            </div>
        </section>

    </div>

    <div class="space-y-5">

        <section class="rounded-2xl
                   border border-black/[0.07]
                   bg-white p-6">
            <h2 class="text-[16px] font-semibold">
                Availability
            </h2>

            <div class="mt-5 space-y-5">
                <div>
                    <label class="mb-2 block text-[13px] font-medium">
                        Status
                    </label>

                    <select name="status"
                        class="h-11 w-full rounded-xl border border-black/[0.09] bg-[#faf9f6] px-3 text-[13px]">
                        <option value="active" @selected(old('status', $plan?->status ?? 'active') === 'active')>
                            Active
                        </option>

                        <option value="inactive" @selected(old('status', $plan?->status) === 'inactive')>
                            Inactive
                        </option>
                    </select>
                </div>

                <label class="flex items-center
                           gap-3 text-[13px]">
                    <input type="checkbox" name="is_public" value="1" @checked(old('is_public', $plan?->is_public ?? true))>

                    Show as public plan
                </label>

                <div>
                    <label class="mb-2 block text-[13px] font-medium">
                        Sort Order
                    </label>

                    <input type="number" name="sort_order" min="0"
                        value="{{ old('sort_order', $plan?->sort_order ?? 0) }}"
                        class="h-11 w-full rounded-xl border border-black/[0.09] bg-[#faf9f6] px-3 text-[13px]">
                </div>
            </div>
        </section>

        <section class="rounded-2xl
                   bg-[#1d1d1b]
                   p-6 text-white">
            <div
                class="text-[12px]
                       font-semibold uppercase
                       tracking-[.08em]
                       text-white/40">
                Pricing Logic
            </div>

            <div class="mt-4 text-[20px]
                       font-semibold">
                Base + usage
            </div>

            <p class="mt-3 text-[13px]
                       leading-6
                       text-white/50">
                Harga plan dapat memiliki base fee,
                per-user fee dan module-specific fee.
                Organization override tetap memiliki
                prioritas tertinggi.
            </p>
        </section>

    </div>
</div>

<div class="mt-6 flex
           justify-end gap-3">
    <a href="{{ route('superadmin.plans.index') }}"
        class="flex h-11 items-center
               rounded-xl border
               border-black/[0.08]
               bg-white px-4
               text-[13px] font-semibold">
        Cancel
    </a>

    <button type="submit"
        class="h-11 cursor-pointer
               rounded-xl bg-[#1d1d1b]
               px-5 text-[13px]
               font-semibold text-white">
        {{ $submitLabel ?? 'Save Plan' }}
    </button>
</div>

<script>
    document.addEventListener('DOMContentLoaded', () => {
        const name =
            document.getElementById('plan-name');

        const code =
            document.getElementById('plan-code');

        if (!name || !code) {
            return;
        }

        let edited =
            code.value.trim() !== '';

        code.addEventListener('input', () => {
            edited =
                code.value.trim() !== '';
        });

        name.addEventListener('input', () => {
            if (edited) {
                return;
            }

            code.value =
                name.value
                .toLowerCase()
                .normalize('NFD')
                .replace(
                    /[\u0300-\u036f]/g,
                    ''
                )
                .replace(
                    /[^a-z0-9]+/g,
                    '_'
                )
                .replace(
                    /^_+|_+$/g,
                    ''
                );
        });
    });
</script>
