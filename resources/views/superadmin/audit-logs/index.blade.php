@extends('layouts.superadmin')

@section('title', 'Audit Logs')
@section('page_name', 'Audit Logs')

@section('content')

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
            Audit Logs
        </h1>

        <p class="mt-2 text-[14px]
               text-[#85827b]">
            Jejak aktivitas administratif
            pada Evognito One.
        </p>
    </div>

    <form method="GET"
        class="mt-7 grid gap-3
           rounded-2xl
           border border-black/[0.07]
           bg-white p-4
           xl:grid-cols-[1fr_170px_220px_150px_150px_auto]">
        <input type="text" name="search" value="{{ $search }}" placeholder="Search activity..."
            class="h-11 rounded-xl
               border border-black/[0.08]
               bg-[#faf9f6]
               px-4 text-[13px]">

        <select name="action"
            class="h-11 rounded-xl
               border border-black/[0.08]
               bg-[#faf9f6]
               px-3 text-[13px]">
            <option value="">
                All actions
            </option>

            @foreach ([
            'create' => 'Create',
            'update' => 'Update',
            'delete' => 'Delete',
        ] as $value => $label)
                <option value="{{ $value }}" @selected($action === $value)>
                    {{ $label }}
                </option>
            @endforeach
        </select>

        <select name="user_id"
            class="h-11 rounded-xl
               border border-black/[0.08]
               bg-[#faf9f6]
               px-3 text-[13px]">
            <option value="">
                All administrators
            </option>

            @foreach ($users as $user)
                <option value="{{ $user->id }}" @selected($userId === $user->id)>
                    {{ $user->name }}
                </option>
            @endforeach
        </select>

        <input type="date" name="date_from" value="{{ $dateFrom }}"
            class="h-11 rounded-xl
               border border-black/[0.08]
               bg-[#faf9f6]
               px-3 text-[12px]">

        <input type="date" name="date_to" value="{{ $dateTo }}"
            class="h-11 rounded-xl
               border border-black/[0.08]
               bg-[#faf9f6]
               px-3 text-[12px]">

        <button
            class="h-11 rounded-xl
               bg-[#1d1d1b]
               px-5 text-[13px]
               font-semibold text-white">
            Filter
        </button>
    </form>

    <div class="mt-5 overflow-hidden
           rounded-2xl
           border border-black/[0.07]
           bg-white">
        <div class="overflow-x-auto">
            <table class="w-full min-w-[1100px]">

                <thead>
                    <tr
                        class="border-b
                           border-black/[0.06]
                           bg-[#faf9f6]">
                        @foreach (['Activity', 'Administrator', 'Subject', 'Method', 'IP Address', 'Time', ''] as $heading)
                            <th
                                class="px-5 py-3
                                   text-left
                                   text-[12px]
                                   font-semibold
                                   text-[#85827b]">
                                {{ $heading }}
                            </th>
                        @endforeach
                    </tr>
                </thead>

                <tbody>
                    @forelse($logs as $log)
                        <tr
                            class="border-b
                           border-black/[0.05]
                           last:border-0
                           hover:bg-[#faf9f6]">
                            <td class="px-5 py-4">
                                <div class="text-[13px]
                                   font-semibold">
                                    {{ $log->event ?: $log->action }}
                                </div>

                                <div
                                    class="mt-1
                                   text-[11px]
                                   text-[#99968e]">
                                    {{ $log->description }}
                                </div>
                            </td>

                            <td class="px-5 py-4">
                                <div class="text-[12px]
                                   font-semibold">
                                    {{ $log->user?->name ?: 'System' }}
                                </div>

                                <div
                                    class="mt-1
                                   text-[11px]
                                   text-[#99968e]">
                                    {{ $log->user?->email }}
                                </div>
                            </td>

                            <td class="px-5 py-4">
                                <div class="text-[12px]">
                                    {{ $log->subject_type ?: '—' }}
                                </div>

                                @if ($log->subject_id)
                                    <div
                                        class="mt-1
                                       text-[11px]
                                       text-[#99968e]">
                                        #{{ $log->subject_id }}
                                    </div>
                                @endif
                            </td>

                            <td class="px-5 py-4">
                                <span
                                    class="rounded-lg
                                   bg-[#f1f0eb]
                                   px-2.5 py-1.5
                                   text-[11px]
                                   font-semibold">
                                    {{ $log->method }}
                                </span>
                            </td>

                            <td class="px-5 py-4
                               text-[12px]">
                                {{ $log->ip_address ?: '—' }}
                            </td>

                            <td class="px-5 py-4">
                                <div class="text-[12px]">
                                    {{ $log->created_at?->format('d M Y') }}
                                </div>

                                <div
                                    class="mt-1
                                   text-[11px]
                                   text-[#99968e]">
                                    {{ $log->created_at?->format('H:i:s') }}
                                </div>
                            </td>

                            <td class="px-5 py-4
                               text-right">
                                <a href="{{ route('superadmin.audit-logs.show', $log) }}"
                                    class="text-[12px]
                                   font-semibold">
                                    Detail →
                                </a>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="7"
                                class="px-6 py-16
                               text-center
                               text-[14px]">
                                Belum ada audit log.
                            </td>
                        </tr>
                    @endforelse
                </tbody>

            </table>
        </div>

        @if ($logs->hasPages())
            <div class="border-t
                   border-black/[0.06]
                   px-5 py-4">
                {{ $logs->links() }}
            </div>
        @endif
    </div>

@endsection
