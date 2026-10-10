@extends('layouts.public')

@section('title', 'Complete Registration — Evognito One')

@section('content')

    <section class="min-h-screen
           bg-[#f7f6f2]
           pb-20 pt-[120px]">
        <div
            class="mx-auto grid
               w-[min(100%-32px,1050px)]
               gap-5
               lg:grid-cols-[1fr_360px]">
            <div class="rounded-2xl
                   border border-black/[0.07]
                   bg-white p-7">
                <div
                    class="text-[10px] font-bold
                       uppercase tracking-[.13em]
                       text-[#e8742a]">
                    Invitation
                </div>

                <h1 class="mt-3 text-[32px]
                       font-semibold
                       tracking-[-.045em]">
                    Complete your organization.
                </h1>

                <p class="mt-3 text-[12px]
                       leading-6 text-[#77746d]">
                    Anda menerima invitation untuk
                    membuat workspace Evognito One.
                </p>

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

                @php
                    $locked = $invite->locked_fields ?? [];
                @endphp

                <form method="POST"
                    action="{{ route('registration.invite.accept', $token) }}"
                    class="mt-7">
                    @csrf

                    <div class="grid gap-5
                           md:grid-cols-2">
                        <div>
                            <label class="mb-2 block text-[10px] font-semibold">
                                Organization Name
                            </label>

                            <input type="text" name="organization_name" required
                                value="{{ old('organization_name', $invite->organization_name) }}"
                                @readonly(in_array('organization_name', $locked, true))
                                class="h-11 w-full rounded-xl border border-black/[0.09] bg-[#faf9f6] px-3 text-[11px]">
                        </div>

                        <div>
                            <label class="mb-2 block text-[10px] font-semibold">
                                Organization Type
                            </label>

                            <select name="organization_type"
                                class="h-11 w-full rounded-xl border border-black/[0.09] bg-[#faf9f6] px-3 text-[11px]"
                                @disabled(in_array('organization_type', $locked, true))>
                                @foreach ([
            'business' => 'Business',
            'education' => 'Education',
            'nonprofit' => 'Nonprofit',
            'government' => 'Government',
            'other' => 'Other',
        ] as $value => $label)
                                    <option value="{{ $value }}" @selected($invite->organization_type === $value)>
                                        {{ $label }}
                                    </option>
                                @endforeach
                            </select>

                            @if (in_array('organization_type', $locked, true))
                                <input type="hidden" name="organization_type" value="{{ $invite->organization_type }}">
                            @endif
                        </div>

                        <div>
                            <label class="mb-2 block text-[10px] font-semibold">
                                Owner Name
                            </label>

                            <input type="text" name="owner_name" required
                                value="{{ old('owner_name', $invite->contact_name) }}"
                                class="h-11 w-full rounded-xl border border-black/[0.09] bg-[#faf9f6] px-3 text-[11px]">
                        </div>

                        <div>
                            <label class="mb-2 block text-[10px] font-semibold">
                                Email
                            </label>

                            <input type="email" name="owner_email" required
                                value="{{ old('owner_email', $invite->contact_email) }}"
                                @readonly(in_array('contact_email', $locked, true))
                                class="h-11 w-full rounded-xl border border-black/[0.09] bg-[#faf9f6] px-3 text-[11px]">
                        </div>

                        <div>
                            <label class="mb-2 block text-[10px] font-semibold">
                                Phone
                            </label>

                            <input type="text" name="owner_phone"
                                value="{{ old('owner_phone', $invite->contact_phone) }}"
                                class="h-11 w-full rounded-xl border border-black/[0.09] bg-[#faf9f6] px-3 text-[11px]">
                        </div>

                        <div></div>

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

                    <div
                        class="mt-7 border-t
                           border-black/[0.06]
                           pt-6">
                        <button type="submit"
                            class="h-11 rounded-xl
                               bg-[#1d1d1b]
                               px-5 text-[10px]
                               font-semibold text-white">
                            Create Organization →
                        </button>
                    </div>
                </form>
            </div>

            <aside class="h-fit rounded-2xl
                   bg-[#1d1d1b]
                   p-6 text-white">
                <div
                    class="text-[9px] font-semibold
                       uppercase tracking-[.12em]
                       text-white/40">
                    Your Evognito Apps
                </div>

                <div class="mt-5 space-y-3">
                    @forelse($modules as $module)
                        @php
                            $pricing = $invite->pricing_snapshot[(string) $module->id] ?? [];

                            $pricingType = $pricing['pricing_type'] ?? 'fixed';
                        @endphp

                        <div
                            class="rounded-xl
                               border border-white/10
                               bg-white/[0.04]
                               p-4">
                            <div class="text-[11px]
                                   font-semibold">
                                {{ $module->name }}
                            </div>

                            <div class="mt-1 text-[9px]
                                   text-white/45">
                                @if ($pricingType === 'fixed')
                                    Rp
                                    {{ number_format($pricing['fixed_price'] ?? 0, 0, ',', '.') }}
                                    / period
                                @elseif($pricingType === 'per_user')
                                    Rp
                                    {{ number_format($pricing['price_per_user'] ?? 0, 0, ',', '.') }}
                                    / user
                                @else
                                    Rp
                                    {{ number_format($pricing['fixed_price'] ?? 0, 0, ',', '.') }}
                                    +
                                    Rp
                                    {{ number_format($pricing['price_per_user'] ?? 0, 0, ',', '.') }}
                                    / user
                                @endif
                            </div>
                        </div>
                    @empty
                        <div class="text-[10px]
                               text-white/45">
                            Apps akan dikonfigurasi
                            setelah registrasi.
                        </div>
                    @endforelse
                </div>

                <div class="mt-7 border-t
                       border-white/10 pt-5">
                    <div class="text-[9px]
                           text-white/40">
                        Link expires
                    </div>

                    <div class="mt-1 text-[10px]
                           font-semibold">
                        {{ $invite->expires_at->format('d M Y, H:i') }}
                    </div>
                </div>
            </aside>
        </div>
    </section>

@endsection
