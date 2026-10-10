@extends('layouts.superadmin')

@section('title', $integration->name)
@section('page_name', 'Integration')

@section('content')

    @if (session('success'))
        <div
            class="mb-5 rounded-xl
               border border-emerald-200
               bg-emerald-50 p-4
               text-[13px]
               text-emerald-700">
            {{ session('success') }}
        </div>
    @endif

    <div class="flex flex-col gap-5
           lg:flex-row
           lg:items-end
           lg:justify-between">
        <div>
            <div
                class="text-[12px]
                   font-semibold uppercase
                   tracking-[.08em]
                   text-[#e8742a]">
                {{ $integration->category }}
            </div>

            <h1 class="mt-2 text-[30px]
                   font-semibold
                   tracking-[-.045em]">
                {{ $integration->name }}
            </h1>

            <div class="mt-2 text-[13px]
                   text-[#85827b]">
                {{ $integration->code }}
                ·
                {{ $integration->provider ?: 'Generic Provider' }}
            </div>
        </div>

        <div class="flex gap-2">
            <form method="POST" action="{{ route('superadmin.integrations.toggle-status', $integration) }}">
                @csrf

                <button
                    class="h-10 rounded-xl
                       border border-black/[0.08]
                       bg-white px-4
                       text-[12px]
                       font-semibold">
                    {{ $integration->status === 'active' ? 'Deactivate' : 'Activate' }}
                </button>
            </form>

            <a href="{{ route('superadmin.integrations.edit', $integration) }}"
                class="flex h-10 items-center
                   rounded-xl
                   bg-[#1d1d1b]
                   px-4 text-[12px]
                   font-semibold text-white">
                Edit Integration
            </a>
        </div>
    </div>

    <div class="mt-8 grid gap-4
           sm:grid-cols-2
           xl:grid-cols-4">
        @foreach ([['Status', ucfirst($integration->status)], ['Environment', ucfirst($integration->environment)], ['Provider', $integration->provider ?: 'Generic'], ['Credentials', $integration->hasCredentials() ? 'Configured' : 'Not Configured']] as $item)
            <div class="rounded-2xl
                   border border-black/[0.07]
                   bg-white p-5">
                <div class="text-[12px]
                       text-[#99968e]">
                    {{ $item[0] }}
                </div>

                <div class="mt-4 text-[18px]
                       font-semibold">
                    {{ $item[1] }}
                </div>
            </div>
        @endforeach
    </div>

    <div class="mt-5 grid gap-5
           xl:grid-cols-[1fr_360px]">
        <section class="rounded-2xl
               border border-black/[0.07]
               bg-white p-6">
            <h2 class="text-[16px] font-semibold">
                Configuration
            </h2>

            <div class="mt-6 space-y-5">
                @foreach ([['Base URL', $integration->base_url ?: '—'], ['Webhook URL', $integration->webhook_url ?: '—'], ['Created By', $integration->creator?->name ?: 'System'], ['Created', $integration->created_at->format('d M Y H:i')]] as $row)
                    <div class="flex
                           justify-between
                           gap-6">
                        <span class="text-[12px]
                               text-[#99968e]">
                            {{ $row[0] }}
                        </span>

                        <span
                            class="max-w-[70%]
                               break-all
                               text-right
                               text-[12px]
                               font-semibold">
                            {{ $row[1] }}
                        </span>
                    </div>
                @endforeach
            </div>

            @if ($integration->config)
                <div class="mt-6 border-t
                       border-black/[0.06]
                       pt-5">
                    <div class="text-[12px]
                           font-semibold">
                        Public Configuration
                    </div>

                    <pre
                        class="mt-3 overflow-x-auto
                           rounded-xl
                           bg-[#f7f6f3]
                           p-4 text-[12px]
                           leading-6">{{ json_encode($integration->config, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES) }}</pre>
                </div>
            @endif
        </section>

        <div class="space-y-5">
            <section class="rounded-2xl
                   bg-[#1d1d1b]
                   p-6 text-white">
                <div
                    class="text-[11px]
                       uppercase
                       tracking-[.09em]
                       text-white/40">
                    Credentials
                </div>

                @if ($integration->hasCredentials())
                    <div class="mt-4 text-[20px]
                           font-semibold">
                        Securely configured
                    </div>

                    <div class="mt-4
                           flex flex-wrap
                           gap-2">
                        @foreach ($integration->credentialKeys() as $key)
                            <span
                                class="rounded-lg
                                   bg-white/10
                                   px-2.5 py-1.5
                                   text-[11px]">
                                {{ $key }}
                            </span>
                        @endforeach
                    </div>
                @else
                    <div class="mt-4 text-[20px]
                           font-semibold">
                        Not configured
                    </div>
                @endif

                <p class="mt-4 text-[12px]
                       leading-6
                       text-white/45">
                    Secret value tidak ditampilkan
                    kembali melalui interface.
                </p>
            </section>

            @if ($integration->notes)
                <section
                    class="rounded-2xl
                       border border-black/[0.07]
                       bg-white p-5">
                    <div class="text-[12px]
                           font-semibold">
                        Notes
                    </div>

                    <p
                        class="mt-3 text-[13px]
                           leading-6
                           text-[#77746d]">
                        {{ $integration->notes }}
                    </p>
                </section>
            @endif
        </div>
    </div>

@endsection
