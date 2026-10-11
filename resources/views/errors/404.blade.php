@extends('errors.layout')
@section('title', 'Halaman Tidak Ditemukan - EvognitoOne')
@section('eyebrow', 'Page Not Found')
@section('heading', 'Halaman yang Anda cari tidak ditemukan.')
@section('description', 'Alamat mungkin berubah, data sudah tidak tersedia, atau URL yang dibuka tidak sesuai dengan route EvognitoOne.')
@section('code', '404')
@section('code_label', 'Not Found')

@section('actions')
    <a href="{{ url()->previous() }}" class="button button-secondary">Kembali</a>
    <a href="{{ url('/') }}" class="button button-primary">Ke Workspace</a>
@endsection

@section('side_content')
    <div class="info-item">
        <div class="info-icon">01</div>
        <div>
            <div class="info-title">Periksa URL</div>
            <div class="info-text">Pastikan slug organisasi dan alamat modul yang dibuka sudah benar.</div>
        </div>
    </div>
    <div class="info-item">
        <div class="info-icon">02</div>
        <div>
            <div class="info-title">Gunakan App Launcher</div>
            <div class="info-text">Kembali ke workspace lalu buka modul dari daftar aplikasi yang tersedia.</div>
        </div>
    </div>
@endsection
