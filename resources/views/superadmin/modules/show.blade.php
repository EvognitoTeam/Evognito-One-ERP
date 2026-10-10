@extends('layouts.superadmin')

@section('title', $module->name)
@section('page_name', 'Module')

@section('content')

    @if (session('success'))
        <div
            class="mb-5 rounded-xl
               border border-emerald-200
               bg-emerald-50
               p-4 text-[10px]
               text-emerald-700">
            {{ session('success') }}
        </div>
    @endif

    <div class="flex flex-col gap-5
           md:flex-row
           md:items-end
           md:justify-between">
        <div class="flex items-center gap-4">
            <div
                class="flex h-14 w-14
                   items-center
                   justify-center
                   rounded-2xl
                   bg-[#eceae4]
                   text-[12px]
                   font-bold">
                {{ strtoupper(substr($module->code, 0, 2)) }}
            </div>

            <div>
                <div class="flex flex-wrap
                       items-center gap-2">
                    <h1
                        class="text-[30px]
                           font-semibold
                           tracking-[-.045em]">
                        {{ $module->name }}
                    </h1>

                    <span
                        class="rounded-full
                           bg-[#f0efe9]
                           px-2.5 py-1
                           text-[9px]
                           font-semibold">
                        {{ ucfirst($module->type) }}
                    </span>
                </div>

                <div class="mt-1 text-[10px]
                       text-[#99968e]">
                    {{ $module->code }}
                    ·
                    {{ $module->ulid }}
                </div>
            </div>
        </div>

        <div class="flex gap-2">

            <form method="POST"
                action="{{ route('superadmin.modules.toggle-status', $module) }}">
                @csrf

                <button type="submit"
                    class="h-10 cursor-pointer
                       rounded-xl
                       border border-black/[0.08]
                       bg-white px-4
                       text-[9px]
                       font-semibold">
                    {{ $module->status === 'active' ? 'Deactivate' : 'Activate' }}
                </button>
            </form>

            <a href="{{ route('superadmin.modules.edit', $module) }}"
                class="flex h-10
                   items-center
                   rounded-xl
                   bg-[#1d1d1b]
                   px-4 text-[9px]
                   font-semibold
                   text-white">
                Edit Module
            </a>

        </div>
    </div>

    <div class="mt-8 grid gap-3
           sm:grid-cols-2
           xl:grid-cols-4">
        @foreach ([['Organizations', $module->organizations_count], ['Permissions', $module->permissions_count], ['Pricing', $module->pricingLabel()], ['Status', ucfirst($module->status)]] as $stat)
            <div
                class="rounded-2xl
                   border border-black/[0.07]
                   bg-white p-5
                   ev-card-shadow">
                <div class="text-[9px]
                       text-[#99968e]">
                    {{ $stat[0] }}
                </div>

                <div
                    class="mt-5
                       text-[20px]
                       font-semibold
                       tracking-[-.03em]">
                    {{ is_numeric($stat[1]) ? number_format($stat[1]) : $stat[1] }}
                </div>
            </div>
        @endforeach
    </div>

    <div class="mt-5 grid gap-5
           xl:grid-cols-[1fr_380px]">
        <div class="space-y-5">

            <section
                class="rounded-2xl
                   border border-black/[0.07]
                   bg-white p-6
                   ev-card-shadow">
                <h2 class="text-[13px]
                       font-semibold">
                    Module Information
                </h2>

                <dl class="mt-6 space-y-4">

                    @foreach ([['Code', $module->code], ['Category', $module->category ?: '—'], ['Type', ucfirst($module->type)], ['Entry URL', $module->entry_url ?: '—'], ['API URL', $module->api_url ?: '—'], ['Sort Order', $module->sort_order]] as $row)
                        <div
                            class="flex
                               justify-between
                               gap-5">
                            <dt class="text-[9px]
                                   text-[#99968e]">
                                {{ $row[0] }}
                            </dt>

                            <dd
                                class="max-w-[70%]
                                   break-all
                                   text-right
                                   text-[10px]
                                   font-semibold">
                                {{ $row[1] }}
                            </dd>
                        </div>
                    @endforeach

                </dl>

                @if ($module->description)
                    <div
                        class="mt-6 border-t
                           border-black/[0.06]
                           pt-5">
                        <div class="text-[9px]
                               text-[#99968e]">
                            Description
                        </div>

                        <p class="mt-2 text-[10px]
                               leading-6">
                            {{ $module->description }}
                        </p>
                    </div>
                @endif
            </section>

            <section
                class="overflow-hidden
                   rounded-2xl
                   border border-black/[0.07]
                   bg-white
                   ev-card-shadow">
                <div class="border-b
                       border-black/[0.06]
                       px-6 py-5">
                    <h2 class="text-[13px]
                           font-semibold">
                        Organizations
                    </h2>

                    <p class="mt-1 text-[9px]
                           text-[#99968e]">
                        Organization yang menggunakan
                        module ini.
                    </p>
                </div>

                @forelse($organizations as $organization)
                    <div
                        class="flex items-center
                           justify-between
                           border-b
                           border-black/[0.05]
                           px-6 py-4
                           last:border-0">
                        <div>
                            <div class="text-[10px]
                                   font-semibold">
                                {{ $organization->name }}
                            </div>

                            <div
                                class="mt-1
                                   text-[8px]
                                   text-[#99968e]">
                                /{{ $organization->slug }}
                            </div>
                        </div>

                        <a href="{{ route('superadmin.organizations.show', $organization) }}"
                            class="text-[9px]
                               font-semibold">
                            Open →
                        </a>
                    </div>

                @empty

                    <div
                        class="px-6 py-12
                           text-center
                           text-[10px]
                           text-[#99968e]">
                        Belum digunakan oleh
                        organization.
                    </div>
                @endforelse
            </section>

        </div>

        <div class="space-y-5">

            <section
                class="rounded-2xl
                   bg-[#1d1d1b]
                   p-6 text-white
                   ev-card-shadow">
                <div
                    class="text-[9px]
                       font-semibold uppercase
                       tracking-[.12em]
                       text-white/40">
                    Default Pricing
                </div>

                @if ($module->pricing_type === 'fixed')
                    <div
                        class="mt-5 text-[28px]
                           font-semibold
                           tracking-[-.05em]">
                        Rp
                        {{ number_format($module->fixed_price, 0, ',', '.') }}
                    </div>

                    <div class="mt-1 text-[9px]
                           text-white/45">
                        / {{ $module->billing_interval }}
                    </div>
                @elseif($module->pricing_type === 'per_user')
                    <div
                        class="mt-5 text-[28px]
                           font-semibold
                           tracking-[-.05em]">
                        Rp
                        {{ number_format($module->price_per_user, 0, ',', '.') }}
                    </div>

                    <div class="mt-1 text-[9px]
                           text-white/45">
                        / user /
                        {{ $module->billing_interval }}
                    </div>
                @else
                    <div
                        class="mt-5 text-[24px]
                           font-semibold
                           tracking-[-.05em]">
                        Rp
                        {{ number_format($module->fixed_price, 0, ',', '.') }}
                    </div>

                    <div class="mt-1 text-[9px]
                           text-white/45">
                        +
                        Rp
                        {{ number_format($module->price_per_user, 0, ',', '.') }}
                        / user
                    </div>
                @endif

                <div class="mt-7 space-y-4">
                    @foreach ([['Included users', $module->included_users], ['Minimum users', $module->minimum_users ?? '—'], ['Maximum users', $module->maximum_users ?? '—'], ['User scope', str_replace('_', ' ', $module->billing_user_scope)]] as $row)
                        <div
                            class="flex justify-between
                               border-b
                               border-white/10
                               pb-4
                               last:border-0">
                            <span class="text-[9px]
                                   text-white/40">
                                {{ $row[0] }}
                            </span>

                            <span
                                class="text-[9px]
                                   font-semibold
                                   capitalize">
                                {{ $row[1] }}
                            </span>
                        </div>
                    @endforeach
                </div>
            </section>

        </div>
    </div>

@endsection
