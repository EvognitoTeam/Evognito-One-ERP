@extends('layouts.superadmin')

@section('title', 'Edit User')
@section('page_name', 'Edit User')

@section('content')

    <div class="mb-7">
        <a href="{{ route('superadmin.users.show', $user) }}"
            class="text-[12px]
               font-semibold
               text-[#88857d]">
            ← {{ $user->name }}
        </a>

        <h1 class="mt-4 text-[30px]
               font-semibold
               tracking-[-.045em]">
            Edit User
        </h1>

        <p class="mt-2
               text-[14px]
               text-[#85827b]">
            Perbarui account identity
            dan status user.
        </p>
    </div>

    <form method="POST" action="{{ route('superadmin.users.update', $user) }}">
        @csrf
        @method('PUT')

        @include('superadmin.users._form', [
            'user' => $user,
            'submitLabel' => 'Save Changes',
        ])
    </form>

@endsection
