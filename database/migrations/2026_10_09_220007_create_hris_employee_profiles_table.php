<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create(
            'hris_employee_profiles',
            function (Blueprint $table) {
                $table->id();

                $table
                    ->char('ulid', 26)
                    ->unique();

                /*
                |--------------------------------------------------------------------------
                | Core References
                |--------------------------------------------------------------------------
                */

                $table
                    ->foreignId('organization_id')
                    ->constrained('organizations')
                    ->cascadeOnDelete();

                $table
                    ->foreignId('employee_id')
                    ->constrained('employees')
                    ->cascadeOnDelete();

                $table
                    ->foreignId('supervisor_employee_id')
                    ->nullable()
                    ->constrained(
                        'employees'
                    )
                    ->nullOnDelete();

                /*
                |--------------------------------------------------------------------------
                | Employment Information
                |--------------------------------------------------------------------------
                */

                $table
                    ->string(
                        'employment_type',
                        50
                    )
                    ->nullable();

                $table
                    ->date('join_date')
                    ->nullable();

                $table
                    ->date('end_date')
                    ->nullable();

                /*
                |--------------------------------------------------------------------------
                | HR Information
                |--------------------------------------------------------------------------
                */

                $table
                    ->unsignedInteger(
                        'leave_quota'
                    )
                    ->default(10);

                /*
                |--------------------------------------------------------------------------
                | Debt Minutes
                |--------------------------------------------------------------------------
                |
                | HRIS lama menggunakan debt_hours.
                |
                | Di V2 kita simpan sebagai menit supaya:
                |
                | 30  = 30 menit
                | 60  = 1 jam
                | 90  = 1 jam 30 menit
                |
                */

                $table
                    ->integer('debt_minutes')
                    ->default(0);

                $table
                    ->text('notes')
                    ->nullable();

                $table->timestamps();

                /*
                |--------------------------------------------------------------------------
                | Unique
                |--------------------------------------------------------------------------
                |
                | Satu employee hanya mempunyai satu
                | HRIS profile per organization.
                |
                */

                $table->unique(
                    [
                        'organization_id',
                        'employee_id',
                    ],
                    'hris_emp_org_employee_uq'
                );

                /*
                |--------------------------------------------------------------------------
                | Indexes
                |--------------------------------------------------------------------------
                |
                | Nama index ditentukan manual supaya
                | tidak melebihi batas identifier MySQL.
                |
                */

                $table->index(
                    [
                        'organization_id',
                        'employment_type',
                    ],
                    'hris_emp_org_type_idx'
                );

                $table->index(
                    [
                        'organization_id',
                        'supervisor_employee_id',
                    ],
                    'hris_emp_org_supervisor_idx'
                );
            }
        );
    }

    public function down(): void
    {
        Schema::dropIfExists(
            'hris_employee_profiles'
        );
    }
};
