@extends('layouts.superadmin')

@section('title', 'Registration Invites')
@section('page_name', 'Registration Invites')

@section('content')

    <div class="flex flex-col gap-5
           md:flex-row
           md:items-end
           md:justify-between">
        <div>
            <div
                class="text-[10px] font-bold
                   uppercase tracking-[.13em]
                   text-[#e8742a]">
                Onboarding
            </div>

            <h1 class="mt-2 text-[30px]
                   font-semibold
                   tracking-[-.045em]">
                Registration Invites
            </h1>

            <p class="mt-2 text-[12px]
                   text-[#85827b]">
                Buat link registrasi pre-fill
                untuk calon organization.
            </p>
        </div>

        <a href="{{ route('superadmin.registration-invites.create') }}"
            class="inline-flex h-11
               items-center
               rounded-xl
               bg-[#1d1d1b]
               px-4 text-[10px]
               font-semibold text-white">
            + Create Invite
        </a>
    </div>

    @if (session('success'))
        <div
            class="mt-5 rounded-xl
               border border-emerald-200
               bg-emerald-50
               px-4 py-3
               text-[11px]
               text-emerald-700">
            {{ session('success') }}
        </div>
    @endif

    @if (session('error'))
        <div
            class="mt-5 rounded-xl
               border border-red-200
               bg-red-50
               px-4 py-3
               text-[11px]
               text-red-700">
            {{ session('error') }}
        </div>
    @endif

    @if (session('invite_url'))
        <div class="mt-5 rounded-2xl
               border border-orange-200
               bg-orange-50 p-5">
            <div class="text-[10px]
                   font-semibold
                   text-orange-800">
                Registration link
            </div>

            <div class="mt-3 flex
                   flex-col gap-2
                   md:flex-row">
                <input id="created-invite-url" type="text" readonly value="{{ session('invite_url') }}"
                    class="h-10 flex-1
                       rounded-xl
                       border border-orange-200
                       bg-white px-3
                       text-[10px]">

                <button type="button"
                    onclick="
                    navigator.clipboard.writeText(
                        document.getElementById(
                            'created-invite-url'
                        ).value
                    );
                    this.innerText = 'Copied';
                "
                    class="h-10 cursor-pointer
                       rounded-xl bg-[#1d1d1b]
                       px-4 text-[10px]
                       font-semibold text-white">
                    Copy Link
                </button>
            </div>
        </div>
    @endif

    <div class="mt-6 overflow-hidden
           rounded-2xl
           border border-black/[0.07]
           bg-white">
        <div class="overflow-x-auto">
            <table class="w-full min-w-[1050px]">
                <thead>
                    <tr class="border-b
                       border-black/[0.06]
                       bg-[#faf9f6]">
                        @foreach (['Organization', 'Contact', 'Apps', 'Expires', 'Status', 'Link', ''] as $heading)
                            <th
                                class="px-5 py-3
                               text-left text-[9px]
                               font-bold uppercase
                               tracking-[.08em]
                               text-[#99968e]">
                                {{ $heading }}
                            </th>
                        @endforeach
                    </tr>
                </thead>

                <tbody>
                    @forelse($invites as $invite)
                        @php
                            $status = $invite->statusLabel();

                            $statusClass = match ($status) {
                                'Active' => 'bg-emerald-50 text-emerald-700',

                                'Expired' => 'bg-amber-50 text-amber-700',

                                'Used' => 'bg-blue-50 text-blue-700',

                                default => 'bg-red-50 text-red-700',
                            };

                            $url = $invite->registrationUrl();
                        @endphp

                        <tr
                            class="border-b
                           border-black/[0.05]
                           last:border-0">
                            <td class="px-5 py-4">
                                <div class="text-[11px]
                                   font-semibold">
                                    {{ $invite->organization_name ?: 'Not pre-filled' }}
                                </div>

                                <div
                                    class="mt-1 text-[9px]
                                   capitalize
                                   text-[#99968e]">
                                    {{ $invite->organization_type }}
                                </div>
                            </td>

                            <td class="px-5 py-4">
                                <div class="text-[10px]
                                   font-medium">
                                    {{ $invite->contact_name ?: '—' }}
                                </div>

                                <div class="mt-1 text-[9px]
                                   text-[#99968e]">
                                    {{ $invite->contact_email ?: '—' }}
                                </div>
                            </td>

                            <td class="px-5 py-4
                               text-[10px]">
                                {{ count($invite->module_ids ?? []) }}
                            </td>

                            <td class="px-5 py-4">
                                <div class="text-[10px]">
                                    {{ $invite->expires_at->format('d M Y') }}
                                </div>

                                <div class="mt-1 text-[9px]
                                   text-[#99968e]">
                                    {{ $invite->expires_at->format('H:i') }}
                                </div>
                            </td>

                            <td class="px-5 py-4">
                                <span
                                    class="rounded-full
                                   px-2.5 py-1
                                   text-[9px]
                                   font-semibold
                                   {{ $statusClass }}">
                                    {{ $status }}
                                </span>
                            </td>

                            <td class="px-5 py-4">
                                @if ($url && $status === 'Active')
                                    <button type="button" data-copy="{{ $url }}"
                                        onclick="
                                    navigator.clipboard.writeText(
                                        this.dataset.copy
                                    );
                                    this.innerText = 'Copied';
                                "
                                        class="cursor-pointer
                                       text-[9px]
                                       font-semibold">
                                        Copy link
                                    </button>
                                @else
                                    <span class="text-[9px]
                                       text-[#aaa79f]">
                                        —
                                    </span>
                                @endif
                            </td>

                            <td class="px-5 py-4">
                                @if (!$invite->used_at)
                                    <div class="flex justify-end
                                       gap-2">
                                        <form method="POST"
                                            action="{{ route('superadmin.registration-invites.regenerate', $invite) }}">
                                            @csrf

                                            <button
                                                class="cursor-pointer
                                               text-[9px]
                                               font-semibold">
                                                Regenerate
                                            </button>
                                        </form>

                                        @if (!$invite->revoked_at)
                                            <form method="POST"
                                                action="{{ route('superadmin.registration-invites.revoke', $invite) }}">
                                                @csrf

                                                <button
                                                    class="cursor-pointer
                                                   text-[9px]
                                                   font-semibold
                                                   text-red-600">
                                                    Revoke
                                                </button>
                                            </form>
                                        @endif
                                    </div>
                                @endif
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="7"
                                class="px-6 py-16
                               text-center
                               text-[11px]
                               text-[#99968e]">
                                Belum ada registration invite.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @if ($invites->hasPages())
            <div class="border-t
                   border-black/[0.06]
                   px-5 py-4">
                {{ $invites->links() }}
            </div>
        @endif
    </div>

@endsection
