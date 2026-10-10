@extends('layouts.superadmin')

@section('title', 'Edit Organization')
@section('page_name', 'Edit Organization')

@section('content')

    <div class="mb-7">
        <a href="{{ route('superadmin.organizations.show', $organization) }}"
            class="text-[10px]
               font-semibold
               text-[#88857d]">
            ← {{ $organization->name }}
        </a>

        <h1 class="mt-4 text-[30px]
               font-semibold
               tracking-[-.045em]">
            Edit Organization
        </h1>

        <p class="mt-2 text-[12px]
               text-[#85827b]">
            Perbarui informasi
            dan workspace settings.
        </p>
    </div>

    <form method="POST" action="{{ route('superadmin.organizations.update', $organization) }}"
        enctype="multipart/form-data">
        @csrf
        @method('PUT')

        @include('superadmin.organizations._form', [
            'submitLabel' => 'Save Changes',
        ])
    </form>

@endsection
