@extends('errors.layout')
@section('title', 'Sesi Berakhir - EvognitoOne')
@section('eyebrow', 'Session Expired')
@section('heading', 'Sesi Anda sudah berakhir.')
@section('description', 'Halaman terlalu lama terbuka atau token keamanan sudah kedaluwarsa. Muat ulang halaman lalu ulangi tindakan Anda.')
@section('code', '419')
@section('code_label', 'Page Expired')

@section('actions')
    <button type="button" class="button button-secondary" onclick="window.location.reload()">Muat Ulang</button>
    <a href="{{ url('/') }}" class="button button-primary">Ke Workspace</a>
@endsection

@section('side_content')
    <div class="info-item">
        <div class="info-icon">01</div>
        <div>
            <div class="info-title">Muat ulang halaman</div>
            <div class="info-text">Form akan memperoleh token keamanan baru setelah halaman dimuat ulang.</div>
        </div>
    </div>
    <div class="info-item">
        <div class="info-icon">02</div>
        <div>
            <div class="info-title">Login kembali bila diperlukan</div>
            <div class="info-text">Jika session login ikut berakhir, sistem akan meminta Anda masuk kembali.</div>
        </div>
    </div>
@endsection
