<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create(
            'hris_attendances',
            function (Blueprint $table) {
                $table->id();

                $table
                    ->char('ulid', 26)
                    ->unique();

                $table
                    ->foreignId('organization_id')
                    ->constrained('organizations')
                    ->cascadeOnDelete();

                $table
                    ->foreignId('employee_id')
                    ->constrained('employees')
                    ->cascadeOnDelete();

                /*
                |--------------------------------------------------------------------------
                | Attendance Date
                |--------------------------------------------------------------------------
                */

                $table->date(
                    'attendance_date'
                );

                /*
                |--------------------------------------------------------------------------
                | Actual Time
                |--------------------------------------------------------------------------
                */

                $table
                    ->dateTime('clock_in')
                    ->nullable();

                $table
                    ->dateTime('clock_out')
                    ->nullable();

                /*
                |--------------------------------------------------------------------------
                | Schedule Snapshot
                |--------------------------------------------------------------------------
                |
                | Disimpan sebagai snapshot supaya kalau
                | jadwal position berubah bulan depan,
                | attendance lama tidak ikut berubah.
                |
                */

                $table
                    ->time('scheduled_clock_in')
                    ->nullable();

                $table
                    ->time('scheduled_clock_out')
                    ->nullable();

                /*
                |--------------------------------------------------------------------------
                | Status
                |--------------------------------------------------------------------------
                */

                $table
                    ->string('status', 30)
                    ->default('open');

                $table
                    ->boolean('is_late')
                    ->default(false);

                $table
                    ->unsignedInteger('late_minutes')
                    ->default(0);

                $table
                    ->unsignedInteger('work_minutes')
                    ->nullable();

                /*
                |--------------------------------------------------------------------------
                | Method
                |--------------------------------------------------------------------------
                |
                | manual
                | web
                | mobile
                | device
                |
                */

                $table
                    ->string('method', 30)
                    ->default('manual');

                /*
                |--------------------------------------------------------------------------
                | Clock In Evidence
                |--------------------------------------------------------------------------
                */

                $table
                    ->string('photo_in')
                    ->nullable();

                $table
                    ->decimal(
                        'clock_in_latitude',
                        10,
                        7
                    )
                    ->nullable();

                $table
                    ->decimal(
                        'clock_in_longitude',
                        10,
                        7
                    )
                    ->nullable();

                /*
                |--------------------------------------------------------------------------
                | Clock Out Evidence
                |--------------------------------------------------------------------------
                */

                $table
                    ->string('photo_out')
                    ->nullable();

                $table
                    ->decimal(
                        'clock_out_latitude',
                        10,
                        7
                    )
                    ->nullable();

                $table
                    ->decimal(
                        'clock_out_longitude',
                        10,
                        7
                    )
                    ->nullable();

                /*
                |--------------------------------------------------------------------------
                | Additional
                |--------------------------------------------------------------------------
                */

                $table
                    ->text('notes')
                    ->nullable();

                $table
                    ->json('metadata')
                    ->nullable();

                $table
                    ->foreignId('created_by')
                    ->nullable()
                    ->constrained('users')
                    ->nullOnDelete();

                $table
                    ->foreignId('updated_by')
                    ->nullable()
                    ->constrained('users')
                    ->nullOnDelete();

                $table->timestamps();
                $table->softDeletes();

                /*
                |--------------------------------------------------------------------------
                | Daily Attendance
                |--------------------------------------------------------------------------
                */

                $table->unique(
                    [
                        'organization_id',
                        'employee_id',
                        'attendance_date',
                    ],
                    'hris_att_org_emp_date_uq'
                );

                $table->index(
                    [
                        'organization_id',
                        'attendance_date',
                    ],
                    'hris_att_org_date_idx'
                );

                $table->index(
                    [
                        'organization_id',
                        'is_late',
                    ],
                    'hris_att_org_late_idx'
                );

                $table->index(
                    [
                        'organization_id',
                        'status',
                    ],
                    'hris_att_org_status_idx'
                );
            }
        );
    }

    public function down(): void
    {
        Schema::dropIfExists(
            'hris_attendances'
        );
    }
};
