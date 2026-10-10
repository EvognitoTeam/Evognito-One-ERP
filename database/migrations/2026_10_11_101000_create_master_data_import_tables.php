<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (!Schema::hasTable('master_data_imports')) {
            Schema::create('master_data_imports', function (Blueprint $table) {
                $table->id();
                $table->char('ulid', 26)->unique();
                $table->foreignId('organization_id')->constrained('organizations')->cascadeOnDelete();
                $table->string('type', 60)->default('employee_master');
                $table->string('original_filename', 255);
                $table->string('sheet_name', 160)->nullable();
                $table->string('file_hash', 64)->nullable();
                $table->string('status', 30)->default('processing');
                $table->unsignedInteger('total_source_rows')->default(0);
                $table->unsignedInteger('valid_employee_rows')->default(0);
                $table->unsignedInteger('blank_rows')->default(0);
                $table->unsignedInteger('products_found')->default(0);
                $table->unsignedInteger('products_created')->default(0);
                $table->unsignedInteger('rows_with_product')->default(0);
                $table->unsignedInteger('rows_without_product')->default(0);
                $table->unsignedInteger('unresolved_supervisor_level1')->default(0);
                $table->unsignedInteger('unresolved_supervisor_level2')->default(0);
                $table->json('summary')->nullable();
                $table->text('error_message')->nullable();
                $table->foreignId('uploaded_by')->nullable()->constrained('users')->nullOnDelete();
                $table->timestamps();

                $table->index(
                    ['organization_id', 'type', 'status'],
                    'mdi_org_type_status_idx'
                );
                $table->index(
                    ['organization_id', 'created_at'],
                    'mdi_org_created_idx'
                );
            });
        }

        if (!Schema::hasTable('master_data_import_rows')) {
            Schema::create('master_data_import_rows', function (Blueprint $table) {
                $table->id();
                $table->foreignId('import_id')->constrained('master_data_imports')->cascadeOnDelete();
                $table->foreignId('organization_id')->constrained('organizations')->cascadeOnDelete();
                $table->unsignedInteger('source_row_number');
                $table->unsignedInteger('source_no')->nullable();

                $table->string('employee_number', 120)->nullable();
                $table->string('employee_name', 220)->nullable();
                $table->string('employee_name_normalized', 220)->nullable();

                $table->string('company_name', 220)->nullable();
                $table->string('company_code', 80)->nullable();
                $table->string('position_name', 220)->nullable();
                $table->string('operation_type', 120)->nullable();
                $table->string('level_name', 120)->nullable();

                $table->string('supervisor_level1_raw', 220)->nullable();
                $table->string('supervisor_level1_normalized', 220)->nullable();
                $table->string('supervisor_level2_raw', 220)->nullable();
                $table->string('supervisor_level2_normalized', 220)->nullable();

                $table->string('product_raw', 500)->nullable();
                $table->json('product_names')->nullable();
                $table->json('product_ids')->nullable();

                $table->string('home_based', 180)->nullable();
                $table->text('area')->nullable();
                $table->text('assigned_companies')->nullable();
                $table->date('join_date')->nullable();
                $table->string('tenure_text', 120)->nullable();
                $table->string('employment_status', 80)->nullable();
                $table->string('suspension_status', 120)->nullable();
                $table->string('birth_place', 180)->nullable();
                $table->date('birth_date')->nullable();
                $table->string('age_text', 120)->nullable();
                $table->string('gender', 80)->nullable();
                $table->string('religion', 80)->nullable();
                $table->string('phone', 80)->nullable();
                $table->string('email', 220)->nullable();

                $table->json('raw_data')->nullable();
                $table->json('warnings')->nullable();
                $table->timestamps();

                $table->index(
                    ['organization_id', 'employee_number'],
                    'mdir_org_empno_idx'
                );
                $table->index(
                    ['organization_id', 'employee_name_normalized'],
                    'mdir_org_name_idx'
                );
                $table->index(
                    ['import_id', 'source_row_number'],
                    'mdir_import_row_idx'
                );
            });
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('master_data_import_rows');
        Schema::dropIfExists('master_data_imports');
    }
};
