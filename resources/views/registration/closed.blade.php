@extends('layouts.public')

@section('title', 'Registration — Evognito One')

@section('content')

    <section
        class="flex min-h-screen
           items-center justify-center
           bg-[#f7f6f2]
           px-5 pt-[72px]">
        <div
            class="max-w-[520px]
               rounded-2xl
               border border-black/[0.07]
               bg-white p-8
               text-center">
            <div
                class="text-[10px] font-bold
                   uppercase tracking-[.13em]
                   text-[#e8742a]">
                Evognito One
            </div>

            <h1 class="mt-4 text-[28px]
                   font-semibold
                   tracking-[-.045em]">
                Registration by invitation.
            </h1>

            <p class="mt-4 text-[12px]
                   leading-6 text-[#77746d]">
                Saat ini pendaftaran organization
                dilakukan melalui registration link
                yang diberikan oleh administrator
                Evognito.
            </p>

            <a href="{{ route('home') }}"
                class="mt-6 inline-flex
                   h-10 items-center
                   rounded-xl
                   bg-[#1d1d1b]
                   px-4 text-[10px]
                   font-semibold text-white">
                Back to home
            </a>
        </div>
    </section>

@endsection
