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
        | Sales Areas
        |--------------------------------------------------------------------------
        */

        if (
            !Schema::hasTable(
                'sales_areas'
            )
        ) {
            Schema::create(
                'sales_areas',
                function (
                    Blueprint $table
                ) {
                    $table->id();

                    $table
                        ->char(
                            'ulid',
                            26
                        )
                        ->unique();

                    $table
                        ->foreignId(
                            'organization_id'
                        )
                        ->constrained(
                            'organizations'
                        )
                        ->cascadeOnDelete();

                    $table
                        ->string(
                            'code',
                            60
                        )
                        ->nullable();

                    $table
                        ->string(
                            'name',
                            160
                        );

                    $table
                        ->text(
                            'description'
                        )
                        ->nullable();

                    $table
                        ->boolean(
                            'is_active'
                        )
                        ->default(
                            true
                        );

                    $table->timestamps();

                    $table->unique(
                        [
                            'organization_id',
                            'code',
                        ],
                        'sales_area_org_code_uq'
                    );

                    $table->index(
                        [
                            'organization_id',
                            'is_active',
                        ],
                        'sales_area_org_active_idx'
                    );
                }
            );
        }


        /*
        |--------------------------------------------------------------------------
        | Sales Employee Profiles
        |--------------------------------------------------------------------------
        |
        | Core Employee tetap menjadi identity/structure source.
        | Sales-specific data berada di tabel one-to-one ini.
        |
        */

        if (
            !Schema::hasTable(
                'sales_employee_profiles'
            )
        ) {
            Schema::create(
                'sales_employee_profiles',
                function (
                    Blueprint $table
                ) {
                    $table->id();

                    $table
                        ->char(
                            'ulid',
                            26
                        )
                        ->unique();

                    $table
                        ->foreignId(
                            'organization_id'
                        )
                        ->constrained(
                            'organizations'
                        )
                        ->cascadeOnDelete();

                    $table
                        ->foreignId(
                            'employee_id'
                        )
                        ->constrained(
                            'employees'
                        )
                        ->cascadeOnDelete();

                    $table
                        ->foreignId(
                            'supervisor_employee_id'
                        )
                        ->nullable()
                        ->constrained(
                            'employees'
                        )
                        ->nullOnDelete();

                    $table
                        ->foreignId(
                            'area_id'
                        )
                        ->nullable()
                        ->constrained(
                            'sales_areas'
                        )
                        ->nullOnDelete();

                    $table
                        ->boolean(
                            'is_field_sales'
                        )
                        ->default(
                            true
                        );

                    $table
                        ->boolean(
                            'is_active'
                        )
                        ->default(
                            true
                        );

                    $table
                        ->unsignedInteger(
                            'daily_visit_target'
                        )
                        ->default(
                            0
                        );

                    $table
                        ->json(
                            'settings'
                        )
                        ->nullable();

                    $table->timestamps();

                    $table->unique(
                        [
                            'organization_id',
                            'employee_id',
                        ],
                        'sales_emp_org_employee_uq'
                    );

                    $table->index(
                        [
                            'organization_id',
                            'area_id',
                        ],
                        'sales_emp_org_area_idx'
                    );

                    $table->index(
                        [
                            'organization_id',
                            'supervisor_employee_id',
                        ],
                        'sales_emp_org_supervisor_idx'
                    );
                }
            );
        }


        /*
        |--------------------------------------------------------------------------
        | Customers
        |--------------------------------------------------------------------------
        |
        | Nama tidak dibuat unique karena customer dengan nama sama dapat
        | memiliki lokasi berbeda.
        |
        */

        if (
            !Schema::hasTable(
                'sales_customers'
            )
        ) {
            Schema::create(
                'sales_customers',
                function (
                    Blueprint $table
                ) {
                    $table->id();

                    $table
                        ->char(
                            'ulid',
                            26
                        )
                        ->unique();

                    $table
                        ->foreignId(
                            'organization_id'
                        )
                        ->constrained(
                            'organizations'
                        )
                        ->cascadeOnDelete();

                    $table
                        ->foreignId(
                            'area_id'
                        )
                        ->nullable()
                        ->constrained(
                            'sales_areas'
                        )
                        ->nullOnDelete();

                    $table
                        ->string(
                            'code',
                            80
                        )
                        ->nullable();

                    $table
                        ->string(
                            'name',
                            200
                        );

                    $table
                        ->string(
                            'contact_name',
                            160
                        )
                        ->nullable();

                    $table
                        ->string(
                            'phone',
                            50
                        )
                        ->nullable();

                    $table
                        ->string(
                            'email',
                            190
                        )
                        ->nullable();

                    $table
                        ->text(
                            'address'
                        )
                        ->nullable();

                    $table
                        ->string(
                            'city',
                            120
                        )
                        ->nullable();

                    $table
                        ->decimal(
                            'latitude',
                            10,
                            7
                        )
                        ->nullable();

                    $table
                        ->decimal(
                            'longitude',
                            10,
                            7
                        )
                        ->nullable();

                    $table
                        ->unsignedInteger(
                            'geofence_radius_meters'
                        )
                        ->default(
                            100
                        );

                    $table
                        ->boolean(
                            'geofence_enabled'
                        )
                        ->default(
                            true
                        );

                    $table
                        ->boolean(
                            'is_active'
                        )
                        ->default(
                            true
                        );

                    $table
                        ->text(
                            'notes'
                        )
                        ->nullable();

                    $table->timestamps();
                    $table->softDeletes();

                    $table->unique(
                        [
                            'organization_id',
                            'code',
                        ],
                        'sales_cust_org_code_uq'
                    );

                    $table->index(
                        [
                            'organization_id',
                            'name',
                        ],
                        'sales_cust_org_name_idx'
                    );

                    $table->index(
                        [
                            'organization_id',
                            'area_id',
                            'is_active',
                        ],
                        'sales_cust_org_area_active_idx'
                    );
                }
            );
        }


        /*
        |--------------------------------------------------------------------------
        | Sales Products
        |--------------------------------------------------------------------------
        */

        if (
            !Schema::hasTable(
                'sales_products'
            )
        ) {
            Schema::create(
                'sales_products',
                function (
                    Blueprint $table
                ) {
                    $table->id();

                    $table
                        ->char(
                            'ulid',
                            26
                        )
                        ->unique();

                    $table
                        ->foreignId(
                            'organization_id'
                        )
                        ->constrained(
                            'organizations'
                        )
                        ->cascadeOnDelete();

                    $table
                        ->string(
                            'code',
                            100
                        )
                        ->nullable();

                    $table
                        ->string(
                            'name',
                            200
                        );

                    $table
                        ->string(
                            'packaging',
                            160
                        )
                        ->nullable();

                    $table
                        ->string(
                            'principal_product_code',
                            120
                        )
                        ->nullable();

                    $table
                        ->text(
                            'description'
                        )
                        ->nullable();

                    $table
                        ->boolean(
                            'is_active'
                        )
                        ->default(
                            true
                        );

                    $table->timestamps();
                    $table->softDeletes();

                    $table->unique(
                        [
                            'organization_id',
                            'code',
                        ],
                        'sales_prod_org_code_uq'
                    );

                    $table->index(
                        [
                            'organization_id',
                            'name',
                        ],
                        'sales_prod_org_name_idx'
                    );
                }
            );
        }


        /*
        |--------------------------------------------------------------------------
        | Visit Schedules
        |--------------------------------------------------------------------------
        */

        if (
            !Schema::hasTable(
                'sales_visit_schedules'
            )
        ) {
            Schema::create(
                'sales_visit_schedules',
                function (
                    Blueprint $table
                ) {
                    $table->id();

                    $table
                        ->char(
                            'ulid',
                            26
                        )
                        ->unique();

                    $table
                        ->foreignId(
                            'organization_id'
                        )
                        ->constrained(
                            'organizations'
                        )
                        ->cascadeOnDelete();

                    $table
                        ->foreignId(
                            'employee_id'
                        )
                        ->constrained(
                            'employees'
                        )
                        ->cascadeOnDelete();

                    $table
                        ->foreignId(
                            'customer_id'
                        )
                        ->constrained(
                            'sales_customers'
                        )
                        ->cascadeOnDelete();

                    $table
                        ->foreignId(
                            'area_id'
                        )
                        ->nullable()
                        ->constrained(
                            'sales_areas'
                        )
                        ->nullOnDelete();

                    $table
                        ->date(
                            'start_date'
                        );

                    $table
                        ->date(
                            'end_date'
                        );

                    $table
                        ->time(
                            'planned_start_time'
                        )
                        ->nullable();

                    $table
                        ->time(
                            'planned_end_time'
                        )
                        ->nullable();

                    $table
                        ->string(
                            'status',
                            30
                        )
                        ->default(
                            'scheduled'
                        );

                    $table
                        ->string(
                            'purpose',
                            255
                        )
                        ->nullable();

                    $table
                        ->text(
                            'notes'
                        )
                        ->nullable();

                    $table
                        ->foreignId(
                            'created_by'
                        )
                        ->nullable()
                        ->constrained(
                            'users'
                        )
                        ->nullOnDelete();

                    $table
                        ->foreignId(
                            'updated_by'
                        )
                        ->nullable()
                        ->constrained(
                            'users'
                        )
                        ->nullOnDelete();

                    $table->timestamps();
                    $table->softDeletes();

                    $table->index(
                        [
                            'organization_id',
                            'start_date',
                            'end_date',
                        ],
                        'sales_visit_org_date_idx'
                    );

                    $table->index(
                        [
                            'organization_id',
                            'employee_id',
                            'status',
                        ],
                        'sales_visit_org_emp_status_idx'
                    );

                    $table->index(
                        [
                            'organization_id',
                            'customer_id',
                        ],
                        'sales_visit_org_customer_idx'
                    );
                }
            );
        }


        /*
        |--------------------------------------------------------------------------
        | Visit Products
        |--------------------------------------------------------------------------
        */

        if (
            !Schema::hasTable(
                'sales_visit_schedule_products'
            )
        ) {
            Schema::create(
                'sales_visit_schedule_products',
                function (
                    Blueprint $table
                ) {
                    $table->id();

                    $table
                        ->foreignId(
                            'organization_id'
                        )
                        ->constrained(
                            'organizations'
                        )
                        ->cascadeOnDelete();

                    $table
                        ->foreignId(
                            'visit_schedule_id'
                        )
                        ->constrained(
                            'sales_visit_schedules'
                        )
                        ->cascadeOnDelete();

                    $table
                        ->foreignId(
                            'product_id'
                        )
                        ->constrained(
                            'sales_products'
                        )
                        ->cascadeOnDelete();

                    $table->timestamps();

                    $table->unique(
                        [
                            'visit_schedule_id',
                            'product_id',
                        ],
                        'sales_visit_product_uq'
                    );

                    $table->index(
                        [
                            'organization_id',
                            'product_id',
                        ],
                        'sales_visit_product_org_idx'
                    );
                }
            );
        }
    }

    public function down(): void
    {
        Schema::dropIfExists(
            'sales_visit_schedule_products'
        );

        Schema::dropIfExists(
            'sales_visit_schedules'
        );

        Schema::dropIfExists(
            'sales_products'
        );

        Schema::dropIfExists(
            'sales_customers'
        );

        Schema::dropIfExists(
            'sales_employee_profiles'
        );

        Schema::dropIfExists(
            'sales_areas'
        );
    }
};
