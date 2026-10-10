@extends('layouts.public')

@section('title', 'Registration Link — Evognito One')

@section('content')

    <section
        class="flex min-h-screen
           items-center justify-center
           bg-[#f7f6f2]
           px-5 pt-[72px]">
        <div
            class="w-full max-w-[480px]
               rounded-2xl
               border border-black/[0.07]
               bg-white p-8
               text-center">
            <div
                class="mx-auto flex h-12 w-12
                   items-center justify-center
                   rounded-full
                   bg-amber-50
                   text-lg">
                !
            </div>

            <h1 class="mt-5 text-[24px]
                   font-semibold
                   tracking-[-.04em]">
                Registration link unavailable
            </h1>

            <p class="mt-3 text-[12px]
                   leading-6 text-[#77746d]">
                {{ $message }}
            </p>

            <a href="{{ route('home') }}"
                class="mt-6 inline-flex
                   h-10 items-center
                   rounded-xl
                   bg-[#1d1d1b]
                   px-4 text-[10px]
                   font-semibold text-white">
                Back to Evognito One
            </a>
        </div>
    </section>

@endsection
