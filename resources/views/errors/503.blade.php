@extends('errors.layout')

@section('title', 'Coming Soon - EvognitoOne')

@section('eyebrow', 'Coming Soon')

@section('heading', 'Something new is coming.')

@section('description')
    EvognitoOne sedang dipersiapkan untuk segera hadir.
    Kami sedang menyelesaikan beberapa bagian terakhir sebelum platform tersedia untuk digunakan.
@endsection

@section('code', 'EVOGNITOONE')

@section('code_label', 'Coming Soon')

@section('actions')
    <button
        type="button"
        class="button button-primary"
        onclick="window.location.reload()"
    >
        Periksa Kembali
    </button>
@endsection

@section('side_title', 'Segera Hadir')

@section('side_content')

    <div class="info-item">
        <div class="info-icon">
            01
        </div>

        <div>
            <div class="info-title">
                Platform sedang dipersiapkan
            </div>

            <div class="info-text">
                Kami sedang menyelesaikan pengembangan
                dan memastikan seluruh layanan siap
                digunakan.
            </div>
        </div>
    </div>

    <div class="info-item">
        <div class="info-icon">
            02
        </div>

        <div>
            <div class="info-title">
                Satu platform untuk operasional bisnis
            </div>

            <div class="info-text">
                HRIS, Performance, Sales Force, dan
                berbagai modul bisnis lainnya akan
                tersedia dalam satu workspace.
            </div>
        </div>
    </div>

    <div class="info-item">
        <div class="info-icon">
            03
        </div>

        <div>
            <div class="info-title">
                Stay tuned
            </div>

            <div class="info-text">
                EvognitoOne akan segera tersedia.
                Silakan kunjungi kembali halaman ini
                dalam waktu dekat.
            </div>
        </div>
    </div>

@endsection