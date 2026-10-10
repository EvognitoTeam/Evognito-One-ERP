@extends('layouts.superadmin')

@section('title', 'Edit Plan')
@section('page_name', 'Edit Plan')

@section('content')

    <div class="mb-7">
        <a href="{{ route('superadmin.plans.show', $plan) }}"
            class="text-[12px]
               font-semibold text-[#88857d]">
            ← {{ $plan->name }}
        </a>

        <h1 class="mt-4 text-[30px]
               font-semibold
               tracking-[-.045em]">
            Edit Subscription Plan
        </h1>

        <p class="mt-2 text-[14px]
               text-[#85827b]">
            Perbarui bundle aplikasi dan pricing.
        </p>
    </div>

    <form method="POST" action="{{ route('superadmin.plans.update', $plan) }}">
        @csrf
        @method('PUT')

        @include('superadmin.plans._form', [
            'plan' => $plan,
            'submitLabel' => 'Save Changes',
        ])
    </form>

@endsection
