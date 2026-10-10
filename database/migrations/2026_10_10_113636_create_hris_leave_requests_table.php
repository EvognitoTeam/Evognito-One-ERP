<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create(
            'hris_leave_requests',
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
                | Leave Period
                |--------------------------------------------------------------------------
                */

                $table->date('start_date');

                $table->date('end_date');

                $table
                    ->time('start_time')
                    ->nullable();

                $table
                    ->time('end_time')
                    ->nullable();

                $table
                    ->unsignedInteger('duration_days')
                    ->default(1);

                /*
                |--------------------------------------------------------------------------
                | Request Information
                |--------------------------------------------------------------------------
                |
                | cuti
                | izin
                | sakit
                |
                */

                $table
                    ->string('type', 30);

                $table
                    ->string('detail_type', 100)
                    ->nullable();

                $table
                    ->text('reason');

                /*
                |--------------------------------------------------------------------------
                | Emergency Contact
                |--------------------------------------------------------------------------
                */

                $table
                    ->string('emergency_name')
                    ->nullable();

                $table
                    ->string('emergency_address')
                    ->nullable();

                $table
                    ->string('emergency_phone', 50)
                    ->nullable();

                /*
                |--------------------------------------------------------------------------
                | Attachment
                |--------------------------------------------------------------------------
                */

                $table
                    ->string('attachment_path')
                    ->nullable();

                $table
                    ->string('attachment_original_name')
                    ->nullable();

                /*
                |--------------------------------------------------------------------------
                | Approval
                |--------------------------------------------------------------------------
                */

                $table
                    ->string('status', 30)
                    ->default('pending');

                $table
                    ->foreignId('approved_by')
                    ->nullable()
                    ->constrained('users')
                    ->nullOnDelete();

                $table
                    ->timestamp('approved_at')
                    ->nullable();

                $table
                    ->timestamp('rejected_at')
                    ->nullable();

                $table
                    ->text('approval_notes')
                    ->nullable();

                /*
                |--------------------------------------------------------------------------
                | Leave Quota Protection
                |--------------------------------------------------------------------------
                |
                | Menandai bahwa kuota sudah dipotong.
                | Mencegah double deduction.
                |
                */

                $table
                    ->boolean('quota_applied')
                    ->default(false);

                /*
                |--------------------------------------------------------------------------
                | Source
                |--------------------------------------------------------------------------
                */

                $table
                    ->string('source', 30)
                    ->default('manual');

                /*
                |--------------------------------------------------------------------------
                | Audit
                |--------------------------------------------------------------------------
                */

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
                | Indexes
                |--------------------------------------------------------------------------
                */

                $table->index(
                    [
                        'organization_id',
                        'status',
                    ],
                    'hris_leave_org_status_idx'
                );

                $table->index(
                    [
                        'organization_id',
                        'employee_id',
                    ],
                    'hris_leave_org_emp_idx'
                );

                $table->index(
                    [
                        'organization_id',
                        'start_date',
                        'end_date',
                    ],
                    'hris_leave_org_period_idx'
                );
            }
        );
    }

    public function down(): void
    {
        Schema::dropIfExists(
            'hris_leave_requests'
        );
    }
};
