<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('performance_products', function (Blueprint $table) {
            $table->id();
            $table->char('ulid', 26)->unique();
            $table->foreignId('organization_id')->constrained('organizations')->cascadeOnDelete();
            $table->string('code', 80)->nullable();
            $table->string('name', 160);
            $table->text('description')->nullable();
            $table->boolean('is_active')->default(true);
            $table->timestamps();
            $table->softDeletes();
            $table->unique(['organization_id', 'code'], 'perf_prod_org_code_uq');
            $table->index(['organization_id', 'name'], 'perf_prod_org_name_idx');
        });

        Schema::create('performance_employee_products', function (Blueprint $table) {
            $table->id();
            $table->foreignId('organization_id')->constrained('organizations')->cascadeOnDelete();
            $table->foreignId('employee_id')->constrained('employees')->cascadeOnDelete();
            $table->foreignId('product_id')->constrained('performance_products')->cascadeOnDelete();
            $table->boolean('is_primary')->default(false);
            $table->date('effective_from')->nullable();
            $table->date('effective_until')->nullable();
            $table->boolean('is_active')->default(true);
            $table->timestamps();
            $table->unique(['organization_id', 'employee_id', 'product_id'], 'perf_emp_prod_uq');
            $table->index(['organization_id', 'employee_id', 'is_active'], 'perf_emp_prod_emp_idx');
        });

        Schema::create('performance_kpis', function (Blueprint $table) {
            $table->id();
            $table->char('ulid', 26)->unique();
            $table->foreignId('organization_id')->constrained('organizations')->cascadeOnDelete();
            $table->string('code', 80)->nullable();
            $table->string('name', 220);
            $table->text('description')->nullable();
            $table->string('unit', 80)->nullable();
            $table->string('default_frequency', 50)->default('annual');
            $table->string('default_calculation_type', 30)->default('HIGHER_BETTER');
            $table->boolean('is_active')->default(true);
            $table->timestamps();
            $table->softDeletes();
            $table->unique(['organization_id', 'code'], 'perf_kpi_org_code_uq');
        });

        Schema::create('performance_kpi_product_assignments', function (Blueprint $table) {
            $table->id();
            $table->char('ulid', 26)->unique();
            $table->foreignId('organization_id')->constrained('organizations')->cascadeOnDelete();
            $table->foreignId('kpi_id')->constrained('performance_kpis')->cascadeOnDelete();
            $table->foreignId('product_id')->constrained('performance_products')->cascadeOnDelete();
            $table->decimal('weight', 7, 2)->default(0);
            $table->decimal('target', 15, 2)->default(0);
            $table->decimal('target_time', 12, 2)->default(0);
            $table->string('frequency', 50)->default('annual');
            $table->string('calculation_type', 30)->default('HIGHER_BETTER');
            $table->decimal('quantity_weight', 5, 2)->default(70);
            $table->decimal('time_weight', 5, 2)->default(30);
            $table->decimal('max_achievement', 6, 2)->default(150);
            $table->text('notes')->nullable();
            $table->boolean('is_active')->default(true);
            $table->timestamps();
            $table->unique(['organization_id', 'kpi_id', 'product_id'], 'perf_kpi_prod_uq');
            $table->index(['organization_id', 'product_id', 'is_active'], 'perf_kpi_prod_idx');
        });

        Schema::create('performance_policies', function (Blueprint $table) {
            $table->id();
            $table->foreignId('organization_id')->constrained('organizations')->cascadeOnDelete();
            $table->decimal('competency_weight', 5, 2)->default(40);
            $table->decimal('kpi_weight', 5, 2)->default(60);
            $table->boolean('require_level1_approval')->default(true);
            $table->boolean('require_level2_approval')->default(true);
            $table->boolean('require_employee_acknowledgement')->default(true);
            $table->json('settings')->nullable();
            $table->timestamps();
            $table->unique('organization_id', 'perf_policy_org_uq');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('performance_policies');
        Schema::dropIfExists('performance_kpi_product_assignments');
        Schema::dropIfExists('performance_kpis');
        Schema::dropIfExists('performance_employee_products');
        Schema::dropIfExists('performance_products');
    }
};
