<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (!Schema::hasTable('sales_visit_attendances')) {
            Schema::create('sales_visit_attendances', function (Blueprint $table) {
                $table->id();
                $table->char('ulid', 26)->unique();
                $table->foreignId('organization_id')->constrained('organizations')->cascadeOnDelete();
                $table->foreignId('visit_schedule_id')->constrained('sales_visit_schedules')->cascadeOnDelete();
                $table->foreignId('employee_id')->constrained('employees')->cascadeOnDelete();
                $table->foreignId('customer_id')->constrained('sales_customers')->cascadeOnDelete();
                $table->date('visit_date');
                $table->dateTime('clock_in')->nullable();
                $table->dateTime('clock_out')->nullable();
                $table->decimal('clock_in_latitude', 10, 7)->nullable();
                $table->decimal('clock_in_longitude', 10, 7)->nullable();
                $table->decimal('clock_out_latitude', 10, 7)->nullable();
                $table->decimal('clock_out_longitude', 10, 7)->nullable();
                $table->decimal('clock_in_accuracy_meters', 8, 2)->nullable();
                $table->decimal('clock_out_accuracy_meters', 8, 2)->nullable();
                $table->decimal('clock_in_distance_meters', 10, 2)->nullable();
                $table->decimal('clock_out_distance_meters', 10, 2)->nullable();
                $table->boolean('clock_in_within_geofence')->nullable();
                $table->boolean('clock_out_within_geofence')->nullable();
                $table->string('photo_in')->nullable();
                $table->string('photo_out')->nullable();
                $table->string('status', 30)->default('open');
                $table->text('notes')->nullable();
                $table->json('metadata')->nullable();
                $table->timestamps();
                $table->softDeletes();

                $table->unique(
                    ['visit_schedule_id', 'visit_date'],
                    'sales_visit_att_schedule_date_uq'
                );
                $table->index(
                    ['organization_id', 'employee_id', 'visit_date'],
                    'sales_visit_att_org_emp_date_idx'
                );
                $table->index(
                    ['organization_id', 'customer_id', 'visit_date'],
                    'sales_visit_att_org_customer_date_idx'
                );
            });
        }

        if (!Schema::hasTable('sales_location_tracks')) {
            Schema::create('sales_location_tracks', function (Blueprint $table) {
                $table->id();
                $table->foreignId('organization_id')->constrained('organizations')->cascadeOnDelete();
                $table->foreignId('employee_id')->constrained('employees')->cascadeOnDelete();
                $table->foreignId('visit_schedule_id')->nullable()->constrained('sales_visit_schedules')->nullOnDelete();
                $table->dateTime('recorded_at');
                $table->decimal('latitude', 10, 7);
                $table->decimal('longitude', 10, 7);
                $table->decimal('accuracy_meters', 8, 2)->nullable();
                $table->decimal('speed_mps', 8, 2)->nullable();
                $table->decimal('heading_degrees', 8, 2)->nullable();
                $table->unsignedTinyInteger('battery_level')->nullable();
                $table->boolean('is_moving')->nullable();
                $table->string('source', 30)->default('mobile');
                $table->json('metadata')->nullable();
                $table->timestamps();

                $table->index(
                    ['organization_id', 'employee_id', 'recorded_at'],
                    'sales_track_org_emp_time_idx'
                );
                $table->index(
                    ['organization_id', 'visit_schedule_id', 'recorded_at'],
                    'sales_track_org_visit_time_idx'
                );
            });
        }

        if (!Schema::hasTable('sales_receipts')) {
            Schema::create('sales_receipts', function (Blueprint $table) {
                $table->id();
                $table->char('ulid', 26)->unique();
                $table->foreignId('organization_id')->constrained('organizations')->cascadeOnDelete();
                $table->foreignId('visit_schedule_id')->constrained('sales_visit_schedules')->cascadeOnDelete();
                $table->foreignId('employee_id')->constrained('employees')->cascadeOnDelete();
                $table->foreignId('customer_id')->constrained('sales_customers')->cascadeOnDelete();
                $table->string('file_path');
                $table->string('original_name');
                $table->string('mime_type', 100)->nullable();
                $table->unsignedBigInteger('file_size')->nullable();
                $table->decimal('amount', 15, 2)->nullable();
                $table->text('notes')->nullable();
                $table->dateTime('uploaded_at')->nullable();
                $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
                $table->timestamps();

                $table->index(
                    ['organization_id', 'visit_schedule_id'],
                    'sales_receipt_org_visit_idx'
                );
            });
        }

        if (!Schema::hasTable('sales_records')) {
            Schema::create('sales_records', function (Blueprint $table) {
                $table->id();
                $table->char('ulid', 26)->unique();
                $table->foreignId('organization_id')->constrained('organizations')->cascadeOnDelete();
                $table->string('import_batch_ulid', 26)->nullable();
                $table->string('product_name')->nullable();
                $table->string('packaging')->nullable();
                $table->string('principal_product_code')->nullable();
                $table->string('customer_name')->nullable();
                $table->string('customer_number', 100)->nullable();
                $table->text('address')->nullable();
                $table->string('area')->nullable();
                $table->date('invoice_date')->nullable();
                $table->string('delivery_note_no')->nullable();
                $table->string('invoice_no')->nullable();
                $table->string('salesman')->nullable();
                $table->decimal('unit_1', 15, 4)->nullable();
                $table->decimal('quantity', 15, 4)->nullable();
                $table->decimal('ratio_2', 15, 4)->nullable();
                $table->string('package_unit')->nullable();
                $table->decimal('customer_tier_price', 15, 2)->nullable();
                $table->decimal('pl_price', 15, 2)->nullable();
                $table->decimal('gross_amount', 18, 2)->nullable();
                $table->decimal('sales_dpp', 18, 2)->nullable();
                $table->decimal('liters', 15, 4)->nullable();
                $table->string('market_sector')->nullable();
                $table->string('category')->nullable();
                $table->string('customer_share_code')->nullable();
                $table->decimal('owing', 18, 2)->nullable();
                $table->string('department_name')->nullable();
                $table->json('raw_payload')->nullable();
                $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
                $table->timestamps();
                $table->softDeletes();

                $table->index(
                    ['organization_id', 'invoice_date'],
                    'sales_record_org_date_idx'
                );
                $table->index(
                    ['organization_id', 'salesman'],
                    'sales_record_org_salesman_idx'
                );
                $table->index(
                    ['organization_id', 'customer_name'],
                    'sales_record_org_customer_idx'
                );
                $table->index(
                    ['organization_id', 'import_batch_ulid'],
                    'sales_record_org_batch_idx'
                );
            });
        }

        if (!Schema::hasTable('sales_policies')) {
            Schema::create('sales_policies', function (Blueprint $table) {
                $table->id();
                $table->char('ulid', 26)->unique();
                $table->foreignId('organization_id')->constrained('organizations')->cascadeOnDelete();

                $table->boolean('visit_geofence_required')->default(true);
                $table->unsignedInteger('default_customer_radius_meters')->default(100);
                $table->boolean('allow_outside_geofence')->default(false);
                $table->boolean('visit_photo_required')->default(true);
                $table->boolean('receipt_upload_required')->default(false);

                $table->boolean('tracking_enabled')->default(true);
                $table->unsignedInteger('tracking_moving_interval_seconds')->default(10);
                $table->unsignedInteger('tracking_active_interval_seconds')->default(30);
                $table->unsignedInteger('tracking_idle_interval_seconds')->default(120);
                $table->unsignedInteger('tracking_min_distance_meters')->default(15);
                $table->unsignedInteger('tracking_accuracy_limit_meters')->default(100);
                $table->time('tracking_work_start')->default('07:00:00');
                $table->time('tracking_work_end')->default('18:00:00');
                $table->unsignedInteger('tracking_retention_days')->default(90);

                $table->boolean('prevent_visit_after_work_end')->default(true);
                $table->boolean('require_daily_attendance_before_visit')->default(true);
                $table->json('settings')->nullable();
                $table->timestamps();

                $table->unique(
                    'organization_id',
                    'sales_policy_org_uq'
                );
            });
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('sales_policies');
        Schema::dropIfExists('sales_records');
        Schema::dropIfExists('sales_receipts');
        Schema::dropIfExists('sales_location_tracks');
        Schema::dropIfExists('sales_visit_attendances');
    }
};
