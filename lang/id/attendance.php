<?php

return [
    'clock_in_success' =>
        'Presensi masuk berhasil.',

    'clock_out_success' =>
        'Presensi pulang berhasil.',

    'errors' => [
        'employee_not_linked' =>
            'Akun belum terhubung dengan data karyawan pada organisasi ini.',

        'employee_inactive' =>
            'Akun karyawan tidak aktif.',

        'manual_disabled' =>
            'Penyesuaian presensi manual dinonaktifkan oleh kebijakan presensi.',

        'clock_in_required' =>
            'Jam masuk wajib tersedia.',

        'clock_out_before_clock_in' =>
            'Jam pulang tidak boleh lebih awal dari jam masuk.',

        'duplicate' =>
            'Karyawan sudah memiliki presensi pada tanggal tersebut.',

        'early_clock_out' =>
            'Pulang sebelum jadwal selesai tidak diperbolehkan.',

        'no_open_attendance' =>
            'Tidak ada presensi masuk aktif yang dapat diproses untuk Clock Out.',

        'on_annual_leave' =>
            'Anda dijadwalkan cuti tahunan pada :date.',

        'on_leave' =>
            'Anda sedang :type pada :date.',

        'non_workday' =>
            'Presensi tidak diperbolehkan pada hari non-kerja.',

        'holiday' =>
            'Presensi tidak diperbolehkan karena :date merupakan hari libur: :holiday.',

        'too_early' =>
            'Clock In belum dapat dilakukan. Presensi dibuka pukul :time.',

        'too_late' =>
            'Batas waktu Clock In telah berakhir pukul :time.',

        'photo_in_required' =>
            'Foto Clock In wajib tersedia.',

        'photo_out_required' =>
            'Foto Clock Out wajib tersedia.',

        'gps_accuracy' =>
            'Akurasi GPS terlalu rendah. Akurasi saat ini :accuracy meter, maksimum :maximum meter.',

        'location_in_required' =>
            'Lokasi perangkat wajib tersedia saat Clock In.',

        'location_out_required' =>
            'Lokasi perangkat wajib tersedia saat Clock Out.',

        'no_work_location' =>
            'Tidak ada Work Location aktif yang dapat digunakan untuk karyawan ini.',

        'outside_geofence' =>
            'Anda berada di luar area :location. Jarak sekitar :distance meter dari titik lokasi, sedangkan radius yang diizinkan :radius meter.',
    ],
];
