<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        /*
        |--------------------------------------------------------------------------
        | Employee Compensation
        |--------------------------------------------------------------------------
        */

        Schema::create(
            'hris_employee_compensations',
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

                $table
                    ->decimal(
                        'basic_salary',
                        15,
                        2
                    )
                    ->default(0);

                $table
                    ->decimal(
                        'fixed_allowance',
                        15,
                        2
                    )
                    ->default(0);

                $table
                    ->decimal(
                        'daily_bonus',
                        15,
                        2
                    )
                    ->default(0);

                $table
                    ->decimal(
                        'annual_bonus',
                        15,
                        2
                    )
                    ->default(0);

                $table
                    ->decimal(
                        'late_deduction_per_minute',
                        15,
                        2
                    )
                    ->default(0);

                $table
                    ->decimal(
                        'absence_deduction_per_day',
                        15,
                        2
                    )
                    ->default(0);

                $table
                    ->decimal(
                        'other_fixed_deduction',
                        15,
                        2
                    )
                    ->default(0);

                $table
                    ->char(
                        'currency',
                        3
                    )
                    ->default('IDR');

                $table
                    ->date('effective_from')
                    ->nullable();

                $table
                    ->boolean('is_active')
                    ->default(true);

                $table->timestamps();

                $table->unique(
                    [
                        'organization_id',
                        'employee_id',
                    ],
                    'hris_comp_org_emp_uq'
                );
            }
        );


        /*
        |--------------------------------------------------------------------------
        | Payroll Period
        |--------------------------------------------------------------------------
        */

        Schema::create(
            'hris_payroll_periods',
            function (Blueprint $table) {
                $table->id();

                $table
                    ->char('ulid', 26)
                    ->unique();

                $table
                    ->foreignId('organization_id')
                    ->constrained('organizations')
                    ->cascadeOnDelete();

                $table->string('name');

                $table->date('period_start');

                $table->date('period_end');

                $table
                    ->date('pay_date')
                    ->nullable();

                /*
                | draft
                | processed
                | paid
                */

                $table
                    ->string('status', 30)
                    ->default('draft');

                $table
                    ->text('notes')
                    ->nullable();

                $table
                    ->timestamp('generated_at')
                    ->nullable();

                $table
                    ->foreignId('generated_by')
                    ->nullable()
                    ->constrained('users')
                    ->nullOnDelete();

                $table
                    ->foreignId('created_by')
                    ->nullable()
                    ->constrained('users')
                    ->nullOnDelete();

                $table->timestamps();

                $table->unique(
                    [
                        'organization_id',
                        'period_start',
                        'period_end',
                    ],
                    'hris_pay_period_org_dates_uq'
                );

                $table->index(
                    [
                        'organization_id',
                        'status',
                    ],
                    'hris_pay_period_org_status_idx'
                );
            }
        );


        /*
        |--------------------------------------------------------------------------
        | Payroll
        |--------------------------------------------------------------------------
        */

        Schema::create(
            'hris_payrolls',
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
                    ->foreignId('payroll_period_id')
                    ->constrained(
                        'hris_payroll_periods'
                    )
                    ->cascadeOnDelete();

                $table
                    ->foreignId('employee_id')
                    ->constrained('employees')
                    ->cascadeOnDelete();

                /*
                |--------------------------------------------------------------------------
                | Employee Snapshot
                |--------------------------------------------------------------------------
                */

                $table
                    ->string('employee_number')
                    ->nullable();

                $table->string('employee_name');

                $table
                    ->string('position_name')
                    ->nullable();

                $table
                    ->string('department_name')
                    ->nullable();

                $table
                    ->string('branch_name')
                    ->nullable();

                /*
                |--------------------------------------------------------------------------
                | Attendance Snapshot
                |--------------------------------------------------------------------------
                */

                $table
                    ->unsignedInteger('scheduled_days')
                    ->default(0);

                $table
                    ->unsignedInteger('present_days')
                    ->default(0);

                $table
                    ->unsignedInteger('leave_days')
                    ->default(0);

                $table
                    ->unsignedInteger('sick_days')
                    ->default(0);

                $table
                    ->unsignedInteger('permission_days')
                    ->default(0);

                $table
                    ->unsignedInteger('absent_days')
                    ->default(0);

                $table
                    ->unsignedInteger('late_minutes')
                    ->default(0);

                /*
                |--------------------------------------------------------------------------
                | Compensation Snapshot
                |--------------------------------------------------------------------------
                */

                $table
                    ->decimal(
                        'basic_salary',
                        15,
                        2
                    )
                    ->default(0);

                $table
                    ->decimal(
                        'fixed_allowance',
                        15,
                        2
                    )
                    ->default(0);

                $table
                    ->decimal(
                        'daily_bonus_rate',
                        15,
                        2
                    )
                    ->default(0);

                $table
                    ->decimal(
                        'daily_bonus_total',
                        15,
                        2
                    )
                    ->default(0);

                $table
                    ->decimal(
                        'annual_bonus',
                        15,
                        2
                    )
                    ->default(0);

                /*
                |--------------------------------------------------------------------------
                | Deduction Snapshot
                |--------------------------------------------------------------------------
                */

                $table
                    ->decimal(
                        'late_deduction_rate',
                        15,
                        2
                    )
                    ->default(0);

                $table
                    ->decimal(
                        'late_deduction_total',
                        15,
                        2
                    )
                    ->default(0);

                $table
                    ->decimal(
                        'absence_deduction_rate',
                        15,
                        2
                    )
                    ->default(0);

                $table
                    ->decimal(
                        'absence_deduction_total',
                        15,
                        2
                    )
                    ->default(0);

                $table
                    ->decimal(
                        'other_fixed_deduction',
                        15,
                        2
                    )
                    ->default(0);

                /*
                |--------------------------------------------------------------------------
                | Totals
                |--------------------------------------------------------------------------
                */

                $table
                    ->decimal(
                        'gross_salary',
                        15,
                        2
                    )
                    ->default(0);

                $table
                    ->decimal(
                        'total_deductions',
                        15,
                        2
                    )
                    ->default(0);

                $table
                    ->decimal(
                        'net_salary',
                        15,
                        2
                    )
                    ->default(0);

                $table
                    ->char(
                        'currency',
                        3
                    )
                    ->default('IDR');

                /*
                |--------------------------------------------------------------------------
                | State
                |--------------------------------------------------------------------------
                */

                $table
                    ->string('status', 30)
                    ->default('draft');

                $table
                    ->timestamp('paid_at')
                    ->nullable();

                $table
                    ->foreignId('paid_by')
                    ->nullable()
                    ->constrained('users')
                    ->nullOnDelete();

                $table
                    ->text('notes')
                    ->nullable();

                $table->timestamps();

                $table->unique(
                    [
                        'payroll_period_id',
                        'employee_id',
                    ],
                    'hris_pay_period_emp_uq'
                );

                $table->index(
                    [
                        'organization_id',
                        'status',
                    ],
                    'hris_pay_org_status_idx'
                );
            }
        );


        /*
        |--------------------------------------------------------------------------
        | Payroll Items
        |--------------------------------------------------------------------------
        |
        | Untuk adjustment manual berikutnya:
        |
        | earning
        | deduction
        |
        */

        Schema::create(
            'hris_payroll_items',
            function (Blueprint $table) {
                $table->id();

                $table
                    ->char('ulid', 26)
                    ->unique();

                $table
                    ->foreignId('payroll_id')
                    ->constrained(
                        'hris_payrolls'
                    )
                    ->cascadeOnDelete();

                $table
                    ->string('type', 30);

                $table
                    ->string('code', 100)
                    ->nullable();

                $table->string('name');

                $table
                    ->decimal(
                        'amount',
                        15,
                        2
                    )
                    ->default(0);

                $table
                    ->boolean('is_system')
                    ->default(false);

                $table
                    ->text('notes')
                    ->nullable();

                $table->timestamps();

                $table->index(
                    [
                        'payroll_id',
                        'type',
                    ],
                    'hris_pay_item_type_idx'
                );
            }
        );
    }


    public function down(): void
    {
        Schema::dropIfExists(
            'hris_payroll_items'
        );

        Schema::dropIfExists(
            'hris_payrolls'
        );

        Schema::dropIfExists(
            'hris_payroll_periods'
        );

        Schema::dropIfExists(
            'hris_employee_compensations'
        );
    }
};