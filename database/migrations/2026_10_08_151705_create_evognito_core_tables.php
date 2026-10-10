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
        | Organizations
        |--------------------------------------------------------------------------
        */

        Schema::create('organizations', function (Blueprint $table) {
            $table->id();

            $table->char('ulid', 26)->unique();

            $table->string('type', 30)
                ->default('business')
                ->index();

            $table->string('name');

            $table->string('slug')
                ->unique();

            $table->string('legal_name')
                ->nullable();

            $table->string('email')
                ->nullable();

            $table->string('phone', 50)
                ->nullable();

            $table->text('address')
                ->nullable();

            $table->string('logo')
                ->nullable();

            $table->string('timezone', 64)
                ->default('Asia/Jakarta');

            $table->string('locale', 10)
                ->default('id');

            $table->string('status', 30)
                ->default('active')
                ->index();

            $table->json('metadata')
                ->nullable();

            $table->timestamps();
            $table->softDeletes();
        });

        /*
        |--------------------------------------------------------------------------
        | User ↔ Organization Membership
        |--------------------------------------------------------------------------
        */

        Schema::create('organization_users', function (Blueprint $table) {
            $table->id();

            $table->foreignId('organization_id')
                ->constrained('organizations')
                ->cascadeOnDelete();

            $table->foreignId('user_id')
                ->constrained('users')
                ->cascadeOnDelete();

            $table->string('status', 30)
                ->default('active')
                ->index();

            $table->boolean('is_default')
                ->default(false);

            $table->timestamp('joined_at')
                ->nullable();

            $table->timestamps();

            $table->unique(
                ['organization_id', 'user_id'],
                'organization_users_org_user_unique'
            );
        });

        /*
        |--------------------------------------------------------------------------
        | Branches
        |--------------------------------------------------------------------------
        */

        Schema::create('branches', function (Blueprint $table) {
            $table->id();

            $table->char('ulid', 26)
                ->unique();

            $table->foreignId('organization_id')
                ->constrained('organizations')
                ->cascadeOnDelete();

            $table->string('code', 100)
                ->nullable();

            $table->string('name');

            $table->text('address')
                ->nullable();

            $table->decimal('latitude', 10, 7)
                ->nullable();

            $table->decimal('longitude', 10, 7)
                ->nullable();

            $table->unsignedInteger('radius_meters')
                ->default(50);

            $table->string('timezone', 64)
                ->nullable();

            $table->string('status', 30)
                ->default('active')
                ->index();

            $table->json('metadata')
                ->nullable();

            $table->timestamps();
            $table->softDeletes();

            $table->unique(
                ['organization_id', 'code'],
                'branches_org_code_unique'
            );
        });

        /*
        |--------------------------------------------------------------------------
        | Departments
        |--------------------------------------------------------------------------
        */

        Schema::create('departments', function (Blueprint $table) {
            $table->id();

            $table->char('ulid', 26)
                ->unique();

            $table->foreignId('organization_id')
                ->constrained('organizations')
                ->cascadeOnDelete();

            $table->unsignedBigInteger('parent_id')
                ->nullable();

            $table->string('code', 100)
                ->nullable();

            $table->string('name');

            $table->text('description')
                ->nullable();

            $table->string('status', 30)
                ->default('active')
                ->index();

            $table->json('metadata')
                ->nullable();

            $table->timestamps();
            $table->softDeletes();

            $table->foreign('parent_id')
                ->references('id')
                ->on('departments')
                ->nullOnDelete();

            $table->unique(
                ['organization_id', 'code'],
                'departments_org_code_unique'
            );
        });

        /*
        |--------------------------------------------------------------------------
        | Positions
        |--------------------------------------------------------------------------
        */

        Schema::create('positions', function (Blueprint $table) {
            $table->id();

            $table->char('ulid', 26)
                ->unique();

            $table->foreignId('organization_id')
                ->constrained('organizations')
                ->cascadeOnDelete();

            $table->foreignId('department_id')
                ->nullable()
                ->constrained('departments')
                ->nullOnDelete();

            $table->string('code', 100)
                ->nullable();

            $table->string('name');

            $table->string('level', 100)
                ->nullable();

            $table->string('status', 30)
                ->default('active')
                ->index();

            $table->json('metadata')
                ->nullable();

            $table->timestamps();
            $table->softDeletes();

            $table->unique(
                ['organization_id', 'code'],
                'positions_org_code_unique'
            );
        });

        /*
        |--------------------------------------------------------------------------
        | Employees
        |--------------------------------------------------------------------------
        */

        Schema::create('employees', function (Blueprint $table) {
            $table->id();

            $table->char('ulid', 26)
                ->unique();

            $table->foreignId('organization_id')
                ->constrained('organizations')
                ->cascadeOnDelete();

            $table->foreignId('user_id')
                ->nullable()
                ->constrained('users')
                ->nullOnDelete();

            $table->string('employee_number', 100);

            $table->foreignId('branch_id')
                ->nullable()
                ->constrained('branches')
                ->nullOnDelete();

            $table->foreignId('department_id')
                ->nullable()
                ->constrained('departments')
                ->nullOnDelete();

            $table->foreignId('position_id')
                ->nullable()
                ->constrained('positions')
                ->nullOnDelete();

            $table->unsignedBigInteger('supervisor_employee_id')
                ->nullable();

            $table->string('phone', 50)
                ->nullable();

            $table->date('join_date')
                ->nullable();

            $table->string('employment_status', 50)
                ->nullable();

            $table->string('employment_type', 50)
                ->nullable();

            $table->string('status', 30)
                ->default('active')
                ->index();

            $table->json('metadata')
                ->nullable();

            $table->timestamps();
            $table->softDeletes();

            $table->unique(
                ['organization_id', 'employee_number'],
                'employees_org_number_unique'
            );
        });

        Schema::table('employees', function (Blueprint $table) {
            $table->foreign('supervisor_employee_id')
                ->references('id')
                ->on('employees')
                ->nullOnDelete();
        });

        /*
        |--------------------------------------------------------------------------
        | Modules
        |--------------------------------------------------------------------------
        */

        Schema::create('modules', function (Blueprint $table) {
            $table->id();

            $table->char('ulid', 26)
                ->unique();

            $table->string('code', 100)
                ->unique();

            $table->string('name');

            $table->text('description')
                ->nullable();

            $table->string('category', 100)
                ->nullable()
                ->index();

            $table->string('icon', 100)
                ->nullable();

            $table->string('type', 30)
                ->default('external');

            $table->string('entry_url')
                ->nullable();

            $table->string('api_url')
                ->nullable();

            $table->string('status', 30)
                ->default('active')
                ->index();

            $table->unsignedInteger('sort_order')
                ->default(0);

            $table->json('metadata')
                ->nullable();

            $table->timestamps();
        });

        /*
        |--------------------------------------------------------------------------
        | Organization Modules
        |--------------------------------------------------------------------------
        */

        Schema::create('organization_modules', function (Blueprint $table) {
            $table->id();

            $table->foreignId('organization_id')
                ->constrained('organizations')
                ->cascadeOnDelete();

            $table->foreignId('module_id')
                ->constrained('modules')
                ->cascadeOnDelete();

            $table->boolean('enabled')
                ->default(true)
                ->index();

            $table->string('status', 30)
                ->default('active')
                ->index();

            $table->timestamp('started_at')
                ->nullable();

            $table->timestamp('expires_at')
                ->nullable();

            $table->json('settings')
                ->nullable();

            $table->timestamps();

            $table->unique(
                ['organization_id', 'module_id'],
                'organization_modules_org_module_unique'
            );
        });

        /*
        |--------------------------------------------------------------------------
        | Roles
        |--------------------------------------------------------------------------
        */

        Schema::create('roles', function (Blueprint $table) {
            $table->id();

            $table->char('ulid', 26)
                ->unique();

            $table->foreignId('organization_id')
                ->nullable()
                ->constrained('organizations')
                ->cascadeOnDelete();

            $table->string('scope', 30)
                ->default('organization')
                ->index();

            $table->string('code', 100)
                ->index();

            $table->string('name');

            $table->text('description')
                ->nullable();

            $table->boolean('is_system')
                ->default(false);

            $table->timestamps();

            $table->index(
                ['organization_id', 'scope', 'code'],
                'roles_org_scope_code_index'
            );
        });

        /*
        |--------------------------------------------------------------------------
        | Permissions
        |--------------------------------------------------------------------------
        */

        Schema::create('permissions', function (Blueprint $table) {
            $table->id();

            $table->foreignId('module_id')
                ->nullable()
                ->constrained('modules')
                ->nullOnDelete();

            $table->string('code', 150)
                ->unique();

            $table->string('name');

            $table->text('description')
                ->nullable();

            $table->timestamps();
        });

        /*
        |--------------------------------------------------------------------------
        | Role Permissions
        |--------------------------------------------------------------------------
        */

        Schema::create('role_permissions', function (Blueprint $table) {
            $table->foreignId('role_id')
                ->constrained('roles')
                ->cascadeOnDelete();

            $table->foreignId('permission_id')
                ->constrained('permissions')
                ->cascadeOnDelete();

            $table->primary([
                'role_id',
                'permission_id',
            ]);
        });

        /*
        |--------------------------------------------------------------------------
        | User Roles
        |--------------------------------------------------------------------------
        */

        Schema::create('user_roles', function (Blueprint $table) {
            $table->id();

            $table->foreignId('user_id')
                ->constrained('users')
                ->cascadeOnDelete();

            $table->foreignId('role_id')
                ->constrained('roles')
                ->cascadeOnDelete();

            $table->foreignId('organization_id')
                ->nullable()
                ->constrained('organizations')
                ->cascadeOnDelete();

            $table->timestamps();

            $table->index(
                ['user_id', 'organization_id'],
                'user_roles_user_org_index'
            );
        });

        /*
        |--------------------------------------------------------------------------
        | Platform Settings
        |--------------------------------------------------------------------------
        */

        Schema::create('platform_settings', function (Blueprint $table) {
            $table->id();

            $table->string('key')
                ->unique();

            $table->json('value')
                ->nullable();

            $table->timestamps();
        });

        /*
        |--------------------------------------------------------------------------
        | Organization Settings
        |--------------------------------------------------------------------------
        */

        Schema::create('organization_settings', function (Blueprint $table) {
            $table->id();

            $table->foreignId('organization_id')
                ->constrained('organizations')
                ->cascadeOnDelete();

            $table->string('key');

            $table->json('value')
                ->nullable();

            $table->timestamps();

            $table->unique(
                ['organization_id', 'key'],
                'organization_settings_org_key_unique'
            );
        });

        /*
        |--------------------------------------------------------------------------
        | Audit Logs
        |--------------------------------------------------------------------------
        */

        Schema::create('audit_logs', function (Blueprint $table) {
            $table->id();

            $table->foreignId('organization_id')
                ->nullable()
                ->constrained('organizations')
                ->nullOnDelete();

            $table->foreignId('user_id')
                ->nullable()
                ->constrained('users')
                ->nullOnDelete();

            $table->foreignId('module_id')
                ->nullable()
                ->constrained('modules')
                ->nullOnDelete();

            $table->string('event', 150);

            $table->string('auditable_type')
                ->nullable();

            $table->unsignedBigInteger('auditable_id')
                ->nullable();

            $table->string('ip_address', 45)
                ->nullable();

            $table->text('user_agent')
                ->nullable();

            $table->json('payload')
                ->nullable();

            $table->timestamp('created_at')
                ->useCurrent();

            $table->index([
                'organization_id',
                'created_at',
            ]);

            $table->index([
                'auditable_type',
                'auditable_id',
            ]);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('audit_logs');
        Schema::dropIfExists('organization_settings');
        Schema::dropIfExists('platform_settings');

        Schema::dropIfExists('user_roles');
        Schema::dropIfExists('role_permissions');
        Schema::dropIfExists('permissions');
        Schema::dropIfExists('roles');

        Schema::dropIfExists('organization_modules');
        Schema::dropIfExists('modules');

        Schema::dropIfExists('employees');
        Schema::dropIfExists('positions');
        Schema::dropIfExists('departments');
        Schema::dropIfExists('branches');

        Schema::dropIfExists('organization_users');
        Schema::dropIfExists('organizations');
    }
};
