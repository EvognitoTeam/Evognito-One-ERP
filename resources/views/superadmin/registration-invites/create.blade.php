@extends('layouts.superadmin')

@section('title', 'Create Registration Invite')
@section('page_name', 'Create Registration Invite')

@section('content')

    <div class="mb-7">
        <a href="{{ route('superadmin.registration-invites.index') }}"
            class="text-[10px]
               font-semibold
               text-[#88857d]">
            ← Registration Invites
        </a>

        <h1 class="mt-4 text-[30px]
               font-semibold
               tracking-[-.045em]">
            Create Registration Link
        </h1>

        <p class="mt-2 text-[12px]
               text-[#85827b]">
            Pre-fill organization, aplikasi,
            pricing dan masa berlaku link.
        </p>
    </div>

    @if ($errors->any())
        <div
            class="mb-5 rounded-xl
               border border-red-200
               bg-red-50 p-4
               text-[10px]
               text-red-700">
            @foreach ($errors->all() as $error)
                <div>{{ $error }}</div>
            @endforeach
        </div>
    @endif

    <form method="POST" action="{{ route('superadmin.registration-invites.store') }}">
        @csrf

        <div class="grid gap-5
               xl:grid-cols-[1fr_360px]">
            <div class="space-y-5">

                <section
                    class="rounded-2xl
                       border border-black/[0.07]
                       bg-white p-6">
                    <h2 class="text-[13px]
                           font-semibold">
                        Customer Information
                    </h2>

                    <div class="mt-6 grid gap-5
                           md:grid-cols-2">
                        <div>
                            <label class="mb-2 block text-[10px] font-semibold">
                                Organization Name
                            </label>

                            <input type="text" name="organization_name" value="{{ old('organization_name') }}"
                                class="h-11 w-full rounded-xl border border-black/[0.09] bg-[#faf9f6] px-3 text-[11px]">
                        </div>

                        <div>
                            <label class="mb-2 block text-[10px] font-semibold">
                                Type
                            </label>

                            <select name="organization_type"
                                class="h-11 w-full rounded-xl border border-black/[0.09] bg-[#faf9f6] px-3 text-[11px]">
                                @foreach ([
            'business' => 'Business',
            'education' => 'Education',
            'nonprofit' => 'Nonprofit',
            'government' => 'Government',
            'other' => 'Other',
        ] as $value => $label)
                                    <option value="{{ $value }}">
                                        {{ $label }}
                                    </option>
                                @endforeach
                            </select>
                        </div>

                        <div>
                            <label class="mb-2 block text-[10px] font-semibold">
                                Contact Name
                            </label>

                            <input type="text" name="contact_name" value="{{ old('contact_name') }}"
                                class="h-11 w-full rounded-xl border border-black/[0.09] bg-[#faf9f6] px-3 text-[11px]">
                        </div>

                        <div>
                            <label class="mb-2 block text-[10px] font-semibold">
                                Contact Email
                            </label>

                            <input type="email" name="contact_email" value="{{ old('contact_email') }}"
                                class="h-11 w-full rounded-xl border border-black/[0.09] bg-[#faf9f6] px-3 text-[11px]">
                        </div>

                        <div>
                            <label class="mb-2 block text-[10px] font-semibold">
                                Phone
                            </label>

                            <input type="text" name="contact_phone" value="{{ old('contact_phone') }}"
                                class="h-11 w-full rounded-xl border border-black/[0.09] bg-[#faf9f6] px-3 text-[11px]">
                        </div>

                        <div>
                            <label class="mb-2 block text-[10px] font-semibold">
                                Link Expires
                            </label>

                            <input type="datetime-local" name="expires_at"
                                value="{{ old('expires_at', now()->addHours($defaultExpiryHours)->format('Y-m-d\TH:i')) }}"
                                required
                                class="h-11 w-full rounded-xl border border-black/[0.09] bg-[#faf9f6] px-3 text-[11px]">
                        </div>
                    </div>

                    <div
                        class="mt-6 border-t
                           border-black/[0.06]
                           pt-5">
                        <div class="text-[10px]
                               font-semibold">
                            Locked Fields
                        </div>

                        <div class="mt-3 flex
                               flex-wrap gap-4">
                            @foreach ([
            'organization_name' => 'Organization Name',

            'organization_type' => 'Organization Type',

            'contact_email' => 'Email',
        ] as $value => $label)
                                <label class="flex items-center
                                       gap-2 text-[10px]">
                                    <input type="checkbox" name="locked_fields[]" value="{{ $value }}">

                                    {{ $label }}
                                </label>
                            @endforeach
                        </div>
                    </div>
                </section>

                <section
                    class="rounded-2xl
                       border border-black/[0.07]
                       bg-white p-6">
                    <h2 class="text-[13px] font-semibold">
                        Apps & Custom Pricing
                    </h2>

                    <p class="mt-1 text-[10px]
                           text-[#99968e]">
                        Pilih modul dan harga khusus
                        yang disepakati dengan customer.
                    </p>

                    <div class="mt-6 space-y-3">
                        @foreach ($modules as $module)
                            <div
                                class="rounded-xl
                                   border border-black/[0.07]
                                   bg-[#faf9f6]
                                   p-4">
                                <div class="flex
                                       items-center gap-3">
                                    <input type="checkbox" name="module_ids[]" value="{{ $module->id }}">

                                    <div>
                                        <div
                                            class="text-[11px]
                                               font-semibold">
                                            {{ $module->name }}
                                        </div>

                                        <div
                                            class="text-[9px]
                                               text-[#99968e]">
                                            {{ $module->category }}
                                        </div>
                                    </div>
                                </div>

                                <div class="mt-4 grid gap-3
                                       md:grid-cols-4">
                                    <select name="pricing[{{ $module->id }}][pricing_type]"
                                        class="h-9 rounded-lg
           border border-black/[0.08]
           bg-white px-2 text-[9px]">
                                        <option value="fixed" @selected($module->pricing_type === 'fixed')>
                                            Fixed
                                        </option>

                                        <option value="per_user" @selected($module->pricing_type === 'per_user')>
                                            Per User
                                        </option>

                                        <option value="hybrid" @selected($module->pricing_type === 'hybrid')>
                                            Hybrid
                                        </option>
                                    </select>

                                    <input type="number" min="0" name="pricing[{{ $module->id }}][fixed_price]"
                                        placeholder="Fixed price"
                                        class="h-9 rounded-lg border border-black/[0.08] bg-white px-2 text-[9px]">

                                    <input type="number" min="0"
                                        name="pricing[{{ $module->id }}][price_per_user]" placeholder="Price/user"
                                        class="h-9 rounded-lg border border-black/[0.08] bg-white px-2 text-[9px]">

                                    <input type="number" min="0"
                                        name="pricing[{{ $module->id }}][included_users]" placeholder="Included users"
                                        class="h-9 rounded-lg border border-black/[0.08] bg-white px-2 text-[9px]">
                                </div>

                                <div class="mt-3">
                                    <select name="pricing[{{ $module->id }}][billing_user_scope]"
                                        class="h-9 w-full rounded-lg border border-black/[0.08] bg-white px-2 text-[9px]">
                                        <option value="licensed_users">
                                            Licensed users
                                        </option>

                                        <option value="active_employees">
                                            Active employees
                                        </option>

                                        <option value="employees">
                                            Employees
                                        </option>

                                        <option value="active_module_users">
                                            Active module users
                                        </option>

                                        <option value="students">
                                            Students
                                        </option>

                                        <option value="teachers">
                                            Teachers
                                        </option>
                                    </select>
                                </div>
                            </div>
                        @endforeach
                    </div>
                </section>
            </div>

            <div>
                <section
                    class="sticky top-[96px]
                       rounded-2xl
                       border border-black/[0.07]
                       bg-white p-6">
                    <div
                        class="text-[10px] font-bold
                           uppercase tracking-[.12em]
                           text-[#e8742a]">
                        Registration
                    </div>

                    <h2 class="mt-3 text-[18px]
                           font-semibold">
                        One-time invite
                    </h2>

                    <p
                        class="mt-3 text-[10px]
                           leading-5
                           text-[#88857d]">
                        Token hanya berlaku sampai
                        waktu yang ditentukan dan
                        otomatis tidak dapat digunakan
                        kembali setelah registrasi selesai.
                    </p>

                    <button type="submit"
                        class="mt-6 h-11 w-full
                           cursor-pointer
                           rounded-xl
                           bg-[#1d1d1b]
                           text-[10px]
                           font-semibold text-white">
                        Generate Registration Link
                    </button>
                </section>
            </div>
        </div>
    </form>

@endsection
