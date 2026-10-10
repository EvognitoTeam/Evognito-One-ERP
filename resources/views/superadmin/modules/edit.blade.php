@extends('layouts.superadmin')

@section('title', 'Edit Module')
@section('page_name', 'Edit Module')

@section('content')

    <div class="mb-7">
        <a href="{{ route('superadmin.modules.show', $module) }}"
            class="text-[10px]
               font-semibold
               text-[#88857d]">
            ← {{ $module->name }}
        </a>

        <h1 class="mt-4 text-[30px]
               font-semibold
               tracking-[-.045em]">
            Edit Module
        </h1>

        <p class="mt-2 text-[12px]
               text-[#85827b]">
            Konfigurasi aplikasi,
            endpoint dan pricing.
        </p>
    </div>

    <form method="POST" action="{{ route('superadmin.modules.update', $module) }}">
        @csrf
        @method('PUT')

        @include('superadmin.modules._form', [
            'module' => $module,
        
            'submitLabel' => 'Save Changes',
        ])
    </form>

@endsection
