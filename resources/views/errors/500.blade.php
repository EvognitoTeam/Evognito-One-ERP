@extends('errors.layout')
@section('title', 'Terjadi Kesalahan - EvognitoOne')
@section('eyebrow', 'System Error')
@section('heading', 'Sistem mengalami kendala saat memproses permintaan.')
@section('description', 'EvognitoOne tidak dapat menyelesaikan proses tersebut saat ini. Anda dapat kembali ke workspace atau mencoba lagi setelah beberapa saat.')
@section('code', '500')
@section('code_label', 'Internal Server Error')

@section('actions')
    <a href="{{ url()->previous() }}" class="button button-secondary">Kembali</a>
    <a href="{{ url('/') }}" class="button button-primary">Ke Workspace</a>
@endsection

@section('side_content')
    <div class="info-item">
        <div class="info-icon">01</div>
        <div>
            <div class="info-title">Periksa sebelum submit ulang</div>
            <div class="info-text">Jika proses sebelumnya menyimpan data, cek hasilnya terlebih dahulu agar tidak terjadi duplikasi.</div>
        </div>
    </div>
    <div class="info-item">
        <div class="info-icon">02</div>
        <div>
            <div class="info-title">Detail teknis tidak ditampilkan</div>
            <div class="info-text">Administrator dapat memeriksa log aplikasi untuk melihat penyebab error.</div>
        </div>
    </div>
@endsection
