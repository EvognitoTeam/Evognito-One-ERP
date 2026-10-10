@php
    $user = $user ?? null;
@endphp

@if ($errors->any())
    <div
        class="mb-5 rounded-xl
               border border-red-200
               bg-red-50
               p-4
               text-[13px]
               text-red-700">
        @foreach ($errors->all() as $error)
            <div>• {{ $error }}</div>
        @endforeach
    </div>
@endif

<div class="grid gap-5
           xl:grid-cols-[1fr_360px]">
    <div class="space-y-5">

        <section class="rounded-2xl
                   border border-black/[0.07]
                   bg-white p-6">
            <h2 class="text-[16px]
                       font-semibold">
                Account Information
            </h2>

            <p class="mt-1
                       text-[12px]
                       text-[#99968e]">
                Informasi identity Evognito One.
            </p>

            <div class="mt-6 grid gap-5
                       md:grid-cols-2">
                <div>
                    <label
                        class="mb-2 block
                               text-[13px]
                               font-medium">
                        Full Name
                    </label>

                    <input type="text" name="name" required
                        value="{{ old('name', $user?->name) }}"
                        class="h-11 w-full
                               rounded-xl
                               border border-black/[0.09]
                               bg-[#faf9f6]
                               px-3.5
                               text-[13px]">
                </div>

                <div>
                    <label
                        class="mb-2 block
                               text-[13px]
                               font-medium">
                        Email
                    </label>

                    <input type="email" name="email" required
                        value="{{ old('email', $user?->email) }}"
                        class="h-11 w-full
                               rounded-xl
                               border border-black/[0.09]
                               bg-[#faf9f6]
                               px-3.5
                               text-[13px]">
                </div>

                <div>
                    <label
                        class="mb-2 block
                               text-[13px]
                               font-medium">
                        Username
                    </label>

                    <input type="text" name="username"
                        value="{{ old('username', $user?->username) }}"
                        class="h-11 w-full
                               rounded-xl
                               border border-black/[0.09]
                               bg-[#faf9f6]
                               px-3.5
                               text-[13px]">
                </div>

                <div>
                    <label
                        class="mb-2 block
                               text-[13px]
                               font-medium">
                        Phone
                    </label>

                    <input type="text" name="phone"
                        value="{{ old('phone', $user?->phone) }}"
                        class="h-11 w-full
                               rounded-xl
                               border border-black/[0.09]
                               bg-[#faf9f6]
                               px-3.5
                               text-[13px]">
                </div>
            </div>
        </section>

        <section class="rounded-2xl
                   border border-black/[0.07]
                   bg-white p-6">
            <h2 class="text-[16px]
                       font-semibold">
                Password
            </h2>

            @if ($user)
                <p class="mt-1
                           text-[12px]
                           text-[#99968e]">
                    Kosongkan jika password
                    tidak ingin diubah.
                </p>
            @endif

            <div class="mt-6 grid gap-5
                       md:grid-cols-2">
                <div>
                    <label
                        class="mb-2 block
                               text-[13px]
                               font-medium">
                        Password
                    </label>

                    <input type="password" name="password" @required(!$user)
                        class="h-11 w-full
                               rounded-xl
                               border border-black/[0.09]
                               bg-[#faf9f6]
                               px-3.5
                               text-[13px]">
                </div>

                <div>
                    <label
                        class="mb-2 block
                               text-[13px]
                               font-medium">
                        Confirm Password
                    </label>

                    <input type="password" name="password_confirmation" @required(!$user)
                        class="h-11 w-full
                               rounded-xl
                               border border-black/[0.09]
                               bg-[#faf9f6]
                               px-3.5
                               text-[13px]">
                </div>
            </div>
        </section>

    </div>

    <div class="space-y-5">

        <section class="rounded-2xl
                   border border-black/[0.07]
                   bg-white p-6">
            <h2 class="text-[16px]
                       font-semibold">
                Account Status
            </h2>

            <div class="mt-5">
                <select name="status"
                    class="h-11 w-full
                           rounded-xl
                           border border-black/[0.09]
                           bg-[#faf9f6]
                           px-3
                           text-[13px]">
                    @foreach ([
        'active' => 'Active',
        'inactive' => 'Inactive',
        'suspended' => 'Suspended',
    ] as $value => $label)
                        <option value="{{ $value }}" @selected(old('status', $user?->status ?? 'active') === $value)>
                            {{ $label }}
                        </option>
                    @endforeach
                </select>
            </div>
        </section>

        @if (!$user)
            <section
                class="rounded-2xl
                       border border-black/[0.07]
                       bg-white p-6">
                <h2 class="text-[16px]
                           font-semibold">
                    Initial Organization
                </h2>

                <p
                    class="mt-1
                           text-[12px]
                           leading-5
                           text-[#99968e]">
                    Optional. Membership lain
                    bisa ditambahkan nanti.
                </p>

                <div class="mt-5 space-y-4">
                    <div>
                        <label
                            class="mb-2 block
                                   text-[13px]
                                   font-medium">
                            Organization
                        </label>

                        <select name="organization_id" id="user-organization"
                            class="h-11 w-full
                                   rounded-xl
                                   border border-black/[0.09]
                                   bg-[#faf9f6]
                                   px-3
                                   text-[13px]">
                            <option value="">
                                No organization
                            </option>

                            @foreach ($organizations as $organization)
                                <option value="{{ $organization->id }}" @selected(old('organization_id') == $organization->id)>
                                    {{ $organization->name }}
                                </option>
                            @endforeach
                        </select>
                    </div>

                    <div>
                        <label
                            class="mb-2 block
                                   text-[13px]
                                   font-medium">
                            Role
                        </label>

                        <select name="role_id" id="user-role"
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
                        class="flex items-center
                               gap-2
                               text-[13px]">
                        <input type="checkbox" name="is_default" value="1" checked>

                        Set as default organization
                    </label>
                </div>
            </section>
        @endif

    </div>
</div>

<div class="mt-6
           flex justify-end gap-3">
    <a href="{{ route('superadmin.users.index') }}"
        class="flex h-11
               items-center
               rounded-xl
               border border-black/[0.08]
               bg-white px-4
               text-[13px]
               font-semibold">
        Cancel
    </a>

    <button type="submit"
        class="h-11
               cursor-pointer
               rounded-xl
               bg-[#1d1d1b]
               px-5
               text-[13px]
               font-semibold
               text-white">
        {{ $submitLabel ?? 'Save User' }}
    </button>
</div>

@if (!$user)
    <script>
        document.addEventListener('DOMContentLoaded', () => {
            const organization =
                document.getElementById(
                    'user-organization'
                );

            const role =
                document.getElementById(
                    'user-role'
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
