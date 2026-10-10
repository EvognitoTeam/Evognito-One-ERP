@extends('layouts.superadmin')

@section('title', 'Integrations')
@section('page_name', 'Integrations')

@section('content')

    <div class="flex flex-col gap-5
           md:flex-row
           md:items-end
           md:justify-between">
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
                Integrations
            </h1>

            <p class="mt-2 text-[14px]
                   text-[#85827b]">
                Kelola koneksi external service
                yang digunakan Evognito One.
            </p>
        </div>

        <a href="{{ route('superadmin.integrations.create') }}"
            class="inline-flex h-11
               items-center
               rounded-xl
               bg-[#1d1d1b]
               px-4 text-[13px]
               font-semibold text-white">
            + New Integration
        </a>
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

    <form method="GET"
        class="mt-7 grid gap-3
           rounded-2xl
           border border-black/[0.07]
           bg-white p-4
           md:grid-cols-[1fr_190px_160px_auto]">
        <input type="text" name="search" value="{{ $search }}" placeholder="Name, code or provider..."
            class="h-11 rounded-xl
               border border-black/[0.08]
               bg-[#faf9f6]
               px-4 text-[13px]">

        <select name="category"
            class="h-11 rounded-xl
               border border-black/[0.08]
               bg-[#faf9f6]
               px-3 text-[13px]">
            <option value="">
                All categories
            </option>

            @foreach ([
            'payment' => 'Payment',
            'messaging' => 'Messaging',
            'email' => 'Email',
            'storage' => 'Storage',
            'identity' => 'Identity',
            'analytics' => 'Analytics',
            'webhook' => 'Webhook',
            'other' => 'Other',
        ] as $value => $label)
                <option value="{{ $value }}" @selected($category === $value)>
                    {{ $label }}
                </option>
            @endforeach
        </select>

        <select name="status"
            class="h-11 rounded-xl
               border border-black/[0.08]
               bg-[#faf9f6]
               px-3 text-[13px]">
            <option value="">
                All status
            </option>

            <option value="active" @selected($status === 'active')>
                Active
            </option>

            <option value="inactive" @selected($status === 'inactive')>
                Inactive
            </option>
        </select>

        <button
            class="h-11 rounded-xl
               bg-[#1d1d1b]
               px-5 text-[13px]
               font-semibold text-white">
            Filter
        </button>
    </form>

    <div class="mt-6 grid gap-4
           md:grid-cols-2
           xl:grid-cols-3">
        @forelse($integrations
            as $integration)
            <article class="rounded-[22px]
                   border border-black/[0.07]
                   bg-white p-6">
                <div class="flex items-start
                       justify-between gap-4">
                    <div
                        class="flex h-11 w-11
                           items-center
                           justify-center
                           rounded-xl
                           bg-[#f0efe9]
                           text-[12px]
                           font-bold">
                        {{ strtoupper(substr($integration->name, 0, 2)) }}
                    </div>

                    <span
                        class="rounded-full
                           px-3 py-1.5
                           text-[11px]
                           font-semibold
                           {{ $integration->status === 'active' ? 'bg-emerald-50 text-emerald-700' : 'bg-zinc-100 text-zinc-600' }}">
                        {{ ucfirst($integration->status) }}
                    </span>
                </div>

                <h2 class="mt-5 text-[17px]
                       font-semibold">
                    {{ $integration->name }}
                </h2>

                <div class="mt-1 text-[12px]
                       text-[#99968e]">
                    {{ $integration->provider ?: 'Generic' }}
                    ·
                    {{ ucfirst($integration->environment) }}
                </div>

                <div
                    class="mt-5 flex
                       items-center
                       justify-between
                       rounded-xl
                       bg-[#f7f6f3]
                       px-4 py-3">
                    <div>
                        <div class="text-[11px]
                               text-[#99968e]">
                            Category
                        </div>

                        <div
                            class="mt-1
                               text-[12px]
                               font-semibold
                               capitalize">
                            {{ $integration->category }}
                        </div>
                    </div>

                    <div class="text-right">
                        <div class="text-[11px]
                               text-[#99968e]">
                            Credentials
                        </div>

                        <div
                            class="mt-1
                               text-[12px]
                               font-semibold">
                            {{ $integration->hasCredentials() ? 'Configured' : 'Not set' }}
                        </div>
                    </div>
                </div>

                <div class="mt-5 flex
                       justify-end">
                    <a href="{{ route('superadmin.integrations.show', $integration) }}"
                        class="text-[12px]
                           font-semibold">
                        Manage →
                    </a>
                </div>
            </article>
        @empty
            <div
                class="col-span-full
                   rounded-2xl
                   border border-dashed
                   border-black/[0.12]
                   bg-white
                   py-16 text-center">
                <div class="text-[15px]
                       font-semibold">
                    Belum ada integration.
                </div>
            </div>
        @endforelse
    </div>

    @if ($integrations->hasPages())
        <div class="mt-5">
            {{ $integrations->links() }}
        </div>
    @endif

@endsection
