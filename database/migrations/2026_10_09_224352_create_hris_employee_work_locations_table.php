<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create(
            'hris_employee_work_locations',
            function (Blueprint $table) {
                $table->id();

                $table
                    ->foreignId('organization_id')
                    ->constrained('organizations')
                    ->cascadeOnDelete();

                $table
                    ->foreignId('employee_id')
                    ->constrained('employees')
                    ->cascadeOnDelete();

                $table
                    ->foreignId('work_location_id')
                    ->constrained(
                        'hris_work_locations'
                    )
                    ->cascadeOnDelete();

                $table
                    ->boolean('is_primary')
                    ->default(false);

                $table->timestamps();

                $table->unique(
                    [
                        'employee_id',
                        'work_location_id',
                    ],
                    'hris_emp_location_uq'
                );

                $table->index(
                    [
                        'organization_id',
                        'employee_id',
                    ],
                    'hris_emp_loc_org_emp_idx'
                );
            }
        );
    }

    public function down(): void
    {
        Schema::dropIfExists(
            'hris_employee_work_locations'
        );
    }
};
