<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create(
            'hris_attendance_policies',
            function (Blueprint $table) {
                $table->id();

                $table
                    ->char('ulid', 26)
                    ->unique();

                $table
                    ->foreignId('organization_id')
                    ->constrained('organizations')
                    ->cascadeOnDelete();

                /*
                |--------------------------------------------------------------------------
                | Working Days
                |--------------------------------------------------------------------------
                |
                | ISO-8601:
                |
                | 1 = Monday
                | 2 = Tuesday
                | 3 = Wednesday
                | 4 = Thursday
                | 5 = Friday
                | 6 = Saturday
                | 7 = Sunday
                |
                */

                $table
                    ->json('work_days')
                    ->nullable();

                /*
                |--------------------------------------------------------------------------
                | Evidence Requirements
                |--------------------------------------------------------------------------
                */

                $table
                    ->boolean('require_location')
                    ->default(true);

                $table
                    ->boolean('require_photo_clock_in')
                    ->default(true);

                $table
                    ->boolean('require_photo_clock_out')
                    ->default(true);

                /*
                |--------------------------------------------------------------------------
                | GPS
                |--------------------------------------------------------------------------
                |
                | GPS accuracy dari device.
                |
                | Contoh:
                | accuracy 25m <= limit 100m => accepted
                |
                */

                $table
                    ->unsignedInteger(
                        'maximum_gps_accuracy_meters'
                    )
                    ->default(100);

                /*
                |--------------------------------------------------------------------------
                | Geofence Mode
                |--------------------------------------------------------------------------
                |
                | strict
                | - seluruh mobile attendance harus di dalam geofence.
                |
                | mobile_remote
                | - normal position harus di dalam geofence.
                | - position is_mobile=true boleh remote.
                |
                | record_only
                | - lokasi tetap direkam tetapi tidak memblokir.
                |
                */

                $table
                    ->string(
                        'geofence_mode',
                        30
                    )
                    ->default(
                        'mobile_remote'
                    );

                /*
                |--------------------------------------------------------------------------
                | Attendance Window
                |--------------------------------------------------------------------------
                |
                | Berapa menit sebelum schedule Clock In
                | employee mulai boleh melakukan presensi.
                |
                | Contoh:
                | schedule 08:00
                | before = 120
                | maka mulai bisa clock in pukul 06:00.
                |
                */

                $table
                    ->unsignedInteger(
                        'clock_in_before_minutes'
                    )
                    ->default(120);

                /*
                |--------------------------------------------------------------------------
                | Maximum Late Clock In
                |--------------------------------------------------------------------------
                |
                | Bukan late tolerance.
                |
                | Ini batas waktu Clock In masih dapat
                | dilakukan setelah jam schedule.
                |
                | Contoh:
                | schedule 08:00
                | after = 240
                | presensi masih diterima sampai 12:00.
                |
                */

                $table
                    ->unsignedInteger(
                        'clock_in_after_minutes'
                    )
                    ->default(240);

                /*
                |--------------------------------------------------------------------------
                | Early Clock Out
                |--------------------------------------------------------------------------
                */

                $table
                    ->boolean(
                        'allow_early_clock_out'
                    )
                    ->default(true);

                /*
                |--------------------------------------------------------------------------
                | Holiday / Non Workday
                |--------------------------------------------------------------------------
                */

                $table
                    ->boolean(
                        'allow_attendance_on_holidays'
                    )
                    ->default(false);

                $table
                    ->boolean(
                        'allow_attendance_on_non_workdays'
                    )
                    ->default(false);

                /*
                |--------------------------------------------------------------------------
                | Admin Adjustment
                |--------------------------------------------------------------------------
                */

                $table
                    ->boolean(
                        'allow_manual_adjustment'
                    )
                    ->default(true);

                /*
                |--------------------------------------------------------------------------
                | Additional Settings
                |--------------------------------------------------------------------------
                */

                $table
                    ->json('settings')
                    ->nullable();

                $table->timestamps();

                /*
                |--------------------------------------------------------------------------
                | One Policy Per Organization
                |--------------------------------------------------------------------------
                */

                $table->unique(
                    'organization_id',
                    'hris_att_policy_org_uq'
                );
            }
        );
    }

    public function down(): void
    {
        Schema::dropIfExists(
            'hris_attendance_policies'
        );
    }
};
