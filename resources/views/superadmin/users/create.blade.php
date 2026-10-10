@extends('layouts.superadmin')

@section('title', 'Create User')
@section('page_name', 'Create User')

@section('content')

    <div class="mb-7">
        <a href="{{ route('superadmin.users.index') }}"
            class="text-[12px]
               font-semibold
               text-[#88857d]">
            ← Users
        </a>

        <h1 class="mt-4 text-[30px]
               font-semibold
               tracking-[-.045em]">
            Create User
        </h1>

        <p class="mt-2
               text-[14px]
               text-[#85827b]">
            Buat account Evognito One
            secara manual.
        </p>
    </div>

    <form method="POST" action="{{ route('superadmin.users.store') }}">
        @csrf

        @include('superadmin.users._form', [
            'submitLabel' => 'Create User',
        ])
    </form>

@endsection
