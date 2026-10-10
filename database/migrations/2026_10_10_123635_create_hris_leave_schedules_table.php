<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create(
            'hris_leave_schedules',
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

                $table->date('start_date');

                $table->date('end_date');

                $table
                    ->unsignedInteger('duration_days')
                    ->default(1);

                $table
                    ->string('title')
                    ->default('Annual Leave');

                $table
                    ->text('notes')
                    ->nullable();

                /*
                |--------------------------------------------------------------------------
                | Status
                |--------------------------------------------------------------------------
                |
                | scheduled
                | cancelled
                |
                */

                $table
                    ->string('status', 30)
                    ->default('scheduled');

                /*
                |--------------------------------------------------------------------------
                | Quota
                |--------------------------------------------------------------------------
                |
                | true = quota sudah dipotong.
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
                    ->default('admin');

                /*
                |--------------------------------------------------------------------------
                | Cancellation
                |--------------------------------------------------------------------------
                */

                $table
                    ->foreignId('cancelled_by')
                    ->nullable()
                    ->constrained('users')
                    ->nullOnDelete();

                $table
                    ->timestamp('cancelled_at')
                    ->nullable();

                $table
                    ->text('cancellation_reason')
                    ->nullable();

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

                $table->index(
                    [
                        'organization_id',
                        'employee_id',
                        'status',
                    ],
                    'hris_ls_org_emp_status_idx'
                );

                $table->index(
                    [
                        'organization_id',
                        'start_date',
                        'end_date',
                    ],
                    'hris_ls_org_period_idx'
                );
            }
        );
    }

    public function down(): void
    {
        Schema::dropIfExists(
            'hris_leave_schedules'
        );
    }
};
