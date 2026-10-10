@extends('layouts.superadmin')

@section('title', 'Create Plan')
@section('page_name', 'Create Plan')

@section('content')

    <div class="mb-7">
        <a href="{{ route('superadmin.plans.index') }}" class="text-[12px]
               font-semibold text-[#88857d]">
            ← Plans & Pricing
        </a>

        <h1 class="mt-4 text-[30px]
               font-semibold
               tracking-[-.045em]">
            Create Subscription Plan
        </h1>

        <p class="mt-2 text-[14px]
               text-[#85827b]">
            Buat paket aplikasi dan pricing
            Evognito One.
        </p>
    </div>

    <form method="POST" action="{{ route('superadmin.plans.store') }}">
        @csrf

        @include('superadmin.plans._form', [
            'submitLabel' => 'Create Plan',
        ])
    </form>

@endsection
