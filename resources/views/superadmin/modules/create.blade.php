@extends('layouts.superadmin')

@section('title', 'Create Module')
@section('page_name', 'Create Module')

@section('content')

    <div class="mb-7">
        <a href="{{ route('superadmin.modules.index') }}"
            class="text-[10px]
               font-semibold
               text-[#88857d]">
            ← Modules
        </a>

        <h1 class="mt-4 text-[30px]
               font-semibold
               tracking-[-.045em]">
            Create Module
        </h1>

        <p class="mt-2 text-[12px]
               text-[#85827b]">
            Tambahkan aplikasi atau module baru
            ke ecosystem Evognito One.
        </p>
    </div>

    <form method="POST" action="{{ route('superadmin.modules.store') }}">
        @csrf

        @include('superadmin.modules._form', [
            'submitLabel' => 'Create Module',
        ])
    </form>

@endsection
