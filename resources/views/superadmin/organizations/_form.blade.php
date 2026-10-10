@php
    $organization = $organization ?? null;
@endphp

@if ($errors->any())
    <div
        class="mb-5 rounded-xl
               border border-red-200
               bg-red-50
               px-4 py-3
               text-[11px]
               text-red-700">
        <div class="font-semibold">
            Ada data yang perlu diperbaiki.
        </div>

        <ul class="mt-2 list-disc
                   space-y-1 pl-4">
            @foreach ($errors->all() as $error)
                <li>{{ $error }}</li>
            @endforeach
        </ul>
    </div>
@endif

<div class="grid gap-5
           xl:grid-cols-[1fr_340px]">
    <div class="space-y-5">

        <section
            class="rounded-2xl
                   border border-black/[0.07]
                   bg-white p-6
                   ev-card-shadow">
            <div>
                <h2 class="text-[13px]
                           font-semibold">
                    Organization Information
                </h2>

                <p class="mt-1 text-[10px]
                           text-[#99968e]">
                    Informasi utama tenant
                    Evognito One.
                </p>
            </div>

            <div class="mt-6 grid gap-5
                       md:grid-cols-2">
                <div>
                    <label
                        class="mb-2 block
                               text-[10px]
                               font-semibold">
                        Organization Name
                    </label>

                    <input type="text" id="organization-name" name="name" required
                        value="{{ old('name', $organization?->name) }}"
                        class="h-11 w-full
           rounded-xl
           border border-black/[0.09]
           bg-[#faf9f6]
           px-3.5 text-[11px]">
                </div>

                <div>
                    <label
                        class="mb-2 block
                               text-[10px]
                               font-semibold">
                        Slug
                    </label>

                    <input type="text" id="organization-slug" name="slug"
                        value="{{ old('slug', $organization?->slug) }}" placeholder="generated-automatically"
                        class="h-11 w-full
           rounded-xl
           border border-black/[0.09]
           bg-[#faf9f6]
           px-3.5 text-[11px]">

                    <div
                        class="mt-1.5
                               text-[9px]
                               text-[#aaa79f]">
                        Workspace:
                        /organization-slug
                    </div>
                </div>

                <div>
                    <label
                        class="mb-2 block
                               text-[10px]
                               font-semibold">
                        Type
                    </label>

                    <select name="type" required
                        class="h-11 w-full
                               rounded-xl
                               border border-black/[0.09]
                               bg-[#faf9f6]
                               px-3.5 text-[11px]">
                        @foreach ([
        'business' => 'Business',
        'education' => 'Education',
        'nonprofit' => 'Nonprofit',
        'government' => 'Government',
        'other' => 'Other',
    ] as $value => $label)
                            <option value="{{ $value }}" @selected(old('type', $organization?->type ?? 'business') === $value)>
                                {{ $label }}
                            </option>
                        @endforeach
                    </select>
                </div>

                <div>
                    <label
                        class="mb-2 block
                               text-[10px]
                               font-semibold">
                        Legal Name
                    </label>

                    <input type="text" name="legal_name" value="{{ old('legal_name', $organization?->legal_name) }}"
                        class="h-11 w-full
                               rounded-xl
                               border border-black/[0.09]
                               bg-[#faf9f6]
                               px-3.5 text-[11px]">
                </div>
            </div>
        </section>

        <section
            class="rounded-2xl
                   border border-black/[0.07]
                   bg-white p-6
                   ev-card-shadow">
            <h2 class="text-[13px]
                       font-semibold">
                Contact
            </h2>

            <div class="mt-6 grid gap-5
                       md:grid-cols-2">
                <div>
                    <label
                        class="mb-2 block
                               text-[10px]
                               font-semibold">
                        Email
                    </label>

                    <input type="email" name="email" value="{{ old('email', $organization?->email) }}"
                        class="h-11 w-full
                               rounded-xl
                               border border-black/[0.09]
                               bg-[#faf9f6]
                               px-3.5 text-[11px]">
                </div>

                <div>
                    <label
                        class="mb-2 block
                               text-[10px]
                               font-semibold">
                        Phone
                    </label>

                    <input type="text" name="phone" value="{{ old('phone', $organization?->phone) }}"
                        class="h-11 w-full
                               rounded-xl
                               border border-black/[0.09]
                               bg-[#faf9f6]
                               px-3.5 text-[11px]">
                </div>

                <div class="md:col-span-2">
                    <label
                        class="mb-2 block
                               text-[10px]
                               font-semibold">
                        Address
                    </label>

                    <textarea name="address" rows="4"
                        class="w-full resize-none
                               rounded-xl
                               border border-black/[0.09]
                               bg-[#faf9f6]
                               p-3.5 text-[11px]">{{ old('address', $organization?->address) }}</textarea>
                </div>
            </div>
        </section>
    </div>

    <div class="space-y-5">

        <section
            class="rounded-2xl
                   border border-black/[0.07]
                   bg-white p-6
                   ev-card-shadow">
            <h2 class="text-[13px]
                       font-semibold">
                Workspace
            </h2>

            <div class="mt-6 space-y-5">
                <div>
                    <label
                        class="mb-2 block
                               text-[10px]
                               font-semibold">
                        Status
                    </label>

                    <select name="status"
                        class="h-11 w-full
                               rounded-xl
                               border border-black/[0.09]
                               bg-[#faf9f6]
                               px-3.5 text-[11px]">
                        @foreach ([
        'active' => 'Active',
        'inactive' => 'Inactive',
        'suspended' => 'Suspended',
    ] as $value => $label)
                            <option value="{{ $value }}" @selected(old('status', $organization?->status ?? 'active') === $value)>
                                {{ $label }}
                            </option>
                        @endforeach
                    </select>
                </div>

                <div>
                    <label
                        class="mb-2 block
                               text-[10px]
                               font-semibold">
                        Timezone
                    </label>

                    <select name="timezone"
                        class="h-11 w-full
                               rounded-xl
                               border border-black/[0.09]
                               bg-[#faf9f6]
                               px-3.5 text-[11px]">
                        @foreach (['Asia/Jakarta', 'Asia/Makassar', 'Asia/Jayapura'] as $timezone)
                            <option value="{{ $timezone }}" @selected(old('timezone', $organization?->timezone ?? 'Asia/Jakarta') === $timezone)>
                                {{ $timezone }}
                            </option>
                        @endforeach
                    </select>
                </div>

                <div>
                    <label
                        class="mb-2 block
                               text-[10px]
                               font-semibold">
                        Language
                    </label>

                    <select name="locale"
                        class="h-11 w-full
                               rounded-xl
                               border border-black/[0.09]
                               bg-[#faf9f6]
                               px-3.5 text-[11px]">
                        <option value="id" @selected(old('locale', $organization?->locale ?? 'id') === 'id')>
                            Bahasa Indonesia
                        </option>

                        <option value="en" @selected(old('locale', $organization?->locale) === 'en')>
                            English
                        </option>
                    </select>
                </div>
            </div>
        </section>

        <section
            class="rounded-2xl
                   border border-black/[0.07]
                   bg-white p-6
                   ev-card-shadow">
            <h2 class="text-[13px]
                       font-semibold">
                Organization Logo
            </h2>

            @if ($organization?->logo)
                <div class="mt-5">
                    <img src="{{ Storage::url($organization->logo) }}" alt=""
                        class="h-20 w-20
                               rounded-2xl
                               border border-black/[0.07]
                               object-cover">

                    <label
                        class="mt-3 flex
                               items-center gap-2
                               text-[10px]
                               text-[#77736c]">
                        <input type="checkbox" name="remove_logo" value="1">

                        Remove current logo
                    </label>
                </div>
            @endif

            <input type="file" name="logo" accept=".jpg,.jpeg,.png,.webp"
                class="mt-5 block w-full
                       text-[10px]
                       text-[#77736c]
                       file:mr-3
                       file:rounded-lg
                       file:border-0
                       file:bg-[#f0efe9]
                       file:px-3
                       file:py-2
                       file:text-[10px]
                       file:font-semibold">

            <div class="mt-2 text-[9px]
                       leading-5 text-[#aaa79f]">
                JPG, PNG atau WEBP.
                Maksimal 2 MB.
            </div>
        </section>
    </div>
</div>

<div class="mt-6 flex
           justify-end gap-3">
    <a href="{{ route('superadmin.organizations.index') }}"
        class="flex h-11 items-center
               rounded-xl
               border border-black/[0.08]
               bg-white px-4
               text-[10px] font-semibold
               text-[#77736c]">
        Cancel
    </a>

    <button type="submit"
        class="h-11 cursor-pointer
               rounded-xl
               bg-[#1d1d1b]
               px-5 text-[10px]
               font-semibold text-white">
        {{ $submitLabel ?? 'Save Organization' }}
    </button>
</div>

<script>
    document.addEventListener('DOMContentLoaded', () => {
        const nameInput =
            document.getElementById('organization-name');

        const slugInput =
            document.getElementById('organization-slug');

        if (!nameInput || !slugInput) {
            return;
        }

        let manuallyEdited =
            slugInput.value.trim() !== '';

        slugInput.addEventListener('input', () => {
            manuallyEdited =
                slugInput.value.trim() !== '';
        });

        nameInput.addEventListener('input', () => {
            if (manuallyEdited) {
                return;
            }

            slugInput.value =
                nameInput.value
                .toLowerCase()
                .normalize('NFD')
                .replace(
                    /[\u0300-\u036f]/g,
                    ''
                )
                .replace(
                    /[^a-z0-9]+/g,
                    '-'
                )
                .replace(
                    /^-+|-+$/g,
                    ''
                );
        });
    });
</script>
