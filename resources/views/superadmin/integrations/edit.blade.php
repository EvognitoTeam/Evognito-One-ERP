@extends('layouts.superadmin')

@section('title', 'Edit Integration')
@section('page_name', 'Edit Integration')

@section('content')

    <div class="mb-7">
        <a href="{{ route('superadmin.integrations.show', $integration) }}"
            class="text-[12px]
               font-semibold
               text-[#88857d]">
            ← {{ $integration->name }}
        </a>

        <h1 class="mt-4 text-[30px]
               font-semibold
               tracking-[-.045em]">
            Edit Integration
        </h1>
    </div>

    <form method="POST" action="{{ route('superadmin.integrations.update', $integration) }}">
        @csrf
        @method('PUT')

        @include('superadmin.integrations._form', [
            'integration' => $integration,
        
            'submitLabel' => 'Save Changes',
        ])
    </form>

@endsection
