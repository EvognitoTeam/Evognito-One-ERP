@extends('layouts.superadmin')

@section('title', 'Audit Log')
@section('page_name', 'Audit Log')

@section('content')

    <div class="mb-7">
        <a href="{{ route('superadmin.audit-logs.index') }}"
            class="text-[12px]
               font-semibold
               text-[#88857d]">
            ← Audit Logs
        </a>

        <h1 class="mt-4 text-[30px]
               font-semibold
               tracking-[-.045em]">
            Activity Detail
        </h1>

        <p class="mt-2 text-[14px]
               text-[#85827b]">
            {{ $auditLog->event }}
        </p>
    </div>

    <div class="grid gap-5
           xl:grid-cols-[1fr_360px]">
        <section class="rounded-2xl
               border border-black/[0.07]
               bg-white p-6">
            <h2 class="text-[16px] font-semibold">
                Request Information
            </h2>

            <div class="mt-6 space-y-5">
                @foreach ([['Action', ucfirst($auditLog->action)], ['Route', $auditLog->route ?: '—'], ['Method', $auditLog->method ?: '—'], ['Subject', $auditLog->subject_type ? $auditLog->subject_type . ' #' . $auditLog->subject_id : '—'], ['Organization', $auditLog->organization?->name ?: '—'], ['IP Address', $auditLog->ip_address ?: '—'], ['Time', $auditLog->created_at?->format('d M Y H:i:s') ?: '—']] as $row)
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

            @if ($auditLog->metadata)
                <div class="mt-7 border-t
                       border-black/[0.06]
                       pt-6">
                    <div class="text-[13px]
                           font-semibold">
                        Metadata
                    </div>

                    <pre
                        class="mt-3 overflow-auto
                           rounded-xl
                           bg-[#f7f6f3]
                           p-4 text-[12px]
                           leading-6">{{ json_encode($auditLog->metadata, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE) }}</pre>
                </div>
            @endif
        </section>

        <aside class="space-y-5">
            <section class="rounded-2xl
                   bg-[#1d1d1b]
                   p-6 text-white">
                <div
                    class="text-[11px]
                       uppercase
                       tracking-[.08em]
                       text-white/40">
                    Administrator
                </div>

                <div class="mt-4 text-[19px]
                       font-semibold">
                    {{ $auditLog->user?->name ?: 'System' }}
                </div>

                <div class="mt-1 text-[12px]
                       text-white/45">
                    {{ $auditLog->user?->email }}
                </div>
            </section>

            <section class="rounded-2xl
                   border border-black/[0.07]
                   bg-white p-5">
                <div class="text-[12px]
                       font-semibold">
                    User Agent
                </div>

                <p
                    class="mt-3 break-words
                       text-[12px]
                       leading-6
                       text-[#77746d]">
                    {{ $auditLog->user_agent ?: '—' }}
                </p>
            </section>
        </aside>
    </div>

@endsection
