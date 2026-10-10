@extends('layouts.superadmin')

@section('title', 'Create Integration')
@section('page_name', 'Create Integration')

@section('content')

    <div class="mb-7">
        <a href="{{ route('superadmin.integrations.index') }}"
            class="text-[12px]
               font-semibold
               text-[#88857d]">
            ← Integrations
        </a>

        <h1 class="mt-4 text-[30px]
               font-semibold
               tracking-[-.045em]">
            Create Integration
        </h1>

        <p class="mt-2 text-[14px]
               text-[#85827b]">
            Tambahkan external service atau
            infrastructure connector.
        </p>
    </div>

    <form method="POST" action="{{ route('superadmin.integrations.store') }}">
        @csrf

        @include('superadmin.integrations._form', [
            'submitLabel' => 'Create Integration',
        ])
    </form>

@endsection
