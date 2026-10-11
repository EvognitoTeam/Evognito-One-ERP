@extends('errors.layout')
@section('title', 'Akses Ditolak - EvognitoOne')
@section('eyebrow', 'Access Restricted')
@section('heading', 'Anda tidak memiliki akses ke halaman ini.')
@section('description', 'Akun Anda sudah dikenali, tetapi izin yang dimiliki tidak mencakup halaman atau tindakan yang sedang dibuka.')
@section('code', '403')
@section('code_label', 'Forbidden')

@section('actions')
    <a href="{{ url()->previous() }}" class="button button-secondary">Kembali</a>
    <a href="{{ url('/') }}" class="button button-primary">Ke Workspace</a>
@endsection

@section('side_content')
    <div class="info-item">
        <div class="info-icon">01</div>
        <div>
            <div class="info-title">Periksa organisasi aktif</div>
            <div class="info-text">Pastikan Anda sedang berada di workspace organisasi yang benar.</div>
        </div>
    </div>
    <div class="info-item">
        <div class="info-icon">02</div>
        <div>
            <div class="info-title">Periksa role & permission</div>
            <div class="info-text">Akses modul dan fitur mengikuti role serta permission akun.</div>
        </div>
    </div>
@endsection
