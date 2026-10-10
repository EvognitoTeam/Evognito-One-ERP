@extends('layouts.superadmin')

@section('title', 'Registration Requests')
@section('page_name', 'Registration Requests')

@section('content')

    <div>
        <div class="text-[10px] font-bold
               uppercase tracking-[.13em]
               text-[#e8742a]">
            Onboarding
        </div>

        <h1 class="mt-2 text-[30px]
               font-semibold
               tracking-[-.045em]">
            Registration Requests
        </h1>

        <p class="mt-2 text-[12px]
               text-[#85827b]">
            Review pendaftaran mandiri
            Evognito One.
        </p>
    </div>

    @if (session('success'))
        <div
            class="mt-5 rounded-xl
               border border-emerald-200
               bg-emerald-50 p-4
               text-[10px]
               text-emerald-700">
            {{ session('success') }}
        </div>
    @endif

    @if (session('error'))
        <div
            class="mt-5 rounded-xl
               border border-red-200
               bg-red-50 p-4
               text-[10px]
               text-red-700">
            {{ session('error') }}
        </div>
    @endif

    <div class="mt-6 overflow-hidden
           rounded-2xl
           border border-black/[0.07]
           bg-white">
        <div class="overflow-x-auto">
            <table class="w-full min-w-[950px]">
                <thead>
                    <tr class="border-b
                       border-black/[0.06]
                       bg-[#faf9f6]">
                        @foreach (['Organization', 'Owner', 'Submitted', 'Status', ''] as $heading)
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
                    @forelse($registrations as $registration)
                        <tr
                            class="border-b
                           border-black/[0.05]
                           last:border-0">
                            <td class="px-5 py-4">
                                <div class="text-[11px] font-semibold">
                                    {{ $registration->organization_name }}
                                </div>

                                <div
                                    class="mt-1 text-[9px]
                                   capitalize
                                   text-[#99968e]">
                                    {{ $registration->organization_type }}
                                </div>
                            </td>

                            <td class="px-5 py-4">
                                <div class="text-[10px] font-medium">
                                    {{ $registration->owner_name }}
                                </div>

                                <div class="mt-1 text-[9px]
                                   text-[#99968e]">
                                    {{ $registration->email }}
                                </div>
                            </td>

                            <td class="px-5 py-4
                               text-[10px]">
                                {{ $registration->submitted_at?->format('d M Y H:i') }}
                            </td>

                            <td class="px-5 py-4">
                                <span
                                    class="rounded-full
                                   bg-[#f1f0eb]
                                   px-2.5 py-1
                                   text-[9px]
                                   font-semibold capitalize">
                                    {{ $registration->status }}
                                </span>
                            </td>

                            <td class="px-5 py-4">
                                @if ($registration->status === 'submitted')
                                    <div class="flex
                                       justify-end gap-2">
                                        <form method="POST"
                                            action="{{ route('superadmin.registration-requests.approve', $registration) }}">
                                            @csrf

                                            <button type="submit"
                                                class="h-8 cursor-pointer
                                               rounded-lg
                                               bg-[#1d1d1b]
                                               px-3 text-[9px]
                                               font-semibold
                                               text-white">
                                                Approve
                                            </button>
                                        </form>

                                        <form method="POST"
                                            action="{{ route('superadmin.registration-requests.reject', $registration) }}"
                                            class="flex gap-2">
                                            @csrf

                                            <input type="text" name="reason" required placeholder="Reason"
                                                class="h-8 rounded-lg
                                               border border-black/[0.08]
                                               px-2 text-[9px]">

                                            <button type="submit"
                                                class="h-8 cursor-pointer
                                               rounded-lg
                                               border border-red-200
                                               bg-red-50
                                               px-3 text-[9px]
                                               font-semibold
                                               text-red-700">
                                                Reject
                                            </button>
                                        </form>
                                    </div>
                                @elseif($registration->organization)
                                    <a href="{{ route('superadmin.organizations.show', $registration->organization) }}"
                                        class="text-[9px]
                                       font-semibold">
                                        Open Organization →
                                    </a>
                                @endif
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="5"
                                class="px-5 py-16
                               text-center
                               text-[10px]
                               text-[#99968e]">
                                Belum ada registration request.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

@endsection
