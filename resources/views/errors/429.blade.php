@extends('errors.layout')
@section('title', 'Terlalu Banyak Permintaan - EvognitoOne')
@section('eyebrow', 'Rate Limit')
@section('heading', 'Permintaan dilakukan terlalu cepat.')
@section('description', 'Sistem membatasi jumlah permintaan untuk menjaga stabilitas dan keamanan. Tunggu sebentar sebelum mencoba lagi.')
@section('code', '429')
@section('code_label', 'Too Many Requests')

@section('actions')
    <a href="{{ url()->previous() }}" class="button button-secondary">Kembali</a>
    <button type="button" class="button button-primary" onclick="window.location.reload()">Coba Lagi</button>
@endsection

@section('side_content')
    <div class="info-item">
        <div class="info-icon">01</div>
        <div>
            <div class="info-title">Tunggu beberapa saat</div>
            <div class="info-text">Hindari menekan tombol submit atau refresh secara berulang.</div>
        </div>
    </div>
    <div class="info-item">
        <div class="info-icon">02</div>
        <div>
            <div class="info-title">Tidak perlu mengirim ulang terus-menerus</div>
            <div class="info-text">Coba kembali setelah batas request tersedia lagi.</div>
        </div>
    </div>
@endsection
