@extends('layouts.superadmin')

@section('title', 'New Organization')
@section('page_name', 'New Organization')

@section('content')

    <div class="mb-7">
        <a href="{{ route('superadmin.organizations.index') }}"
            class="text-[10px]
               font-semibold
               text-[#88857d]">
            ← Organizations
        </a>

        <h1 class="mt-4 text-[30px]
               font-semibold
               tracking-[-.045em]">
            Create Organization
        </h1>

        <p class="mt-2 text-[12px]
               text-[#85827b]">
            Buat workspace baru
            di Evognito One.
        </p>
    </div>

    <form method="POST" action="{{ route('superadmin.organizations.store') }}" enctype="multipart/form-data">
        @csrf

        @include('superadmin.organizations._form', [
            'submitLabel' => 'Create Organization',
        ])
    </form>

@endsection
