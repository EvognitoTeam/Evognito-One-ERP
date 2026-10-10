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
        | Subscription Plans
        |--------------------------------------------------------------------------
        */

        if (!Schema::hasColumn('subscription_plans', 'ulid')) {
            Schema::table('subscription_plans', function (Blueprint $table) {
                $table->char('ulid', 26)
                    ->nullable()
                    ->unique()
                    ->after('id');
            });
        }

        if (!Schema::hasColumn('subscription_plans', 'code')) {
            Schema::table('subscription_plans', function (Blueprint $table) {
                $table->string('code', 100)
                    ->nullable()
                    ->unique();
            });
        }

        if (!Schema::hasColumn('subscription_plans', 'description')) {
            Schema::table('subscription_plans', function (Blueprint $table) {
                $table->text('description')
                    ->nullable();
            });
        }

        if (!Schema::hasColumn('subscription_plans', 'status')) {
            Schema::table('subscription_plans', function (Blueprint $table) {
                $table->string('status', 30)
                    ->default('active')
                    ->index();
            });
        }

        if (!Schema::hasColumn('subscription_plans', 'is_public')) {
            Schema::table('subscription_plans', function (Blueprint $table) {
                $table->boolean('is_public')
                    ->default(true);
            });
        }

        if (!Schema::hasColumn('subscription_plans', 'billing_interval')) {
            Schema::table('subscription_plans', function (Blueprint $table) {
                $table->string('billing_interval', 30)
                    ->default('monthly');
            });
        }

        if (!Schema::hasColumn('subscription_plans', 'currency')) {
            Schema::table('subscription_plans', function (Blueprint $table) {
                $table->string('currency', 10)
                    ->default('IDR');
            });
        }

        if (!Schema::hasColumn('subscription_plans', 'base_price')) {
            Schema::table('subscription_plans', function (Blueprint $table) {
                $table->decimal('base_price', 15, 2)
                    ->default(0);
            });
        }

        if (!Schema::hasColumn('subscription_plans', 'price_per_user')) {
            Schema::table('subscription_plans', function (Blueprint $table) {
                $table->decimal('price_per_user', 15, 2)
                    ->default(0);
            });
        }

        if (!Schema::hasColumn('subscription_plans', 'included_users')) {
            Schema::table('subscription_plans', function (Blueprint $table) {
                $table->unsignedInteger('included_users')
                    ->default(0);
            });
        }

        if (!Schema::hasColumn('subscription_plans', 'billing_user_scope')) {
            Schema::table('subscription_plans', function (Blueprint $table) {
                $table->string('billing_user_scope', 50)
                    ->default('licensed_users');
            });
        }

        if (!Schema::hasColumn('subscription_plans', 'trial_days')) {
            Schema::table('subscription_plans', function (Blueprint $table) {
                $table->unsignedInteger('trial_days')
                    ->default(0);
            });
        }

        if (!Schema::hasColumn('subscription_plans', 'sort_order')) {
            Schema::table('subscription_plans', function (Blueprint $table) {
                $table->unsignedInteger('sort_order')
                    ->default(0);
            });
        }

        if (!Schema::hasColumn('subscription_plans', 'metadata')) {
            Schema::table('subscription_plans', function (Blueprint $table) {
                $table->json('metadata')
                    ->nullable();
            });
        }

        if (!Schema::hasColumn('subscription_plans', 'deleted_at')) {
            Schema::table('subscription_plans', function (Blueprint $table) {
                $table->softDeletes();
            });
        }

        /*
        |--------------------------------------------------------------------------
        | Plan Modules
        |--------------------------------------------------------------------------
        */

        if (!Schema::hasColumn('plan_modules', 'billing_mode')) {
            Schema::table('plan_modules', function (Blueprint $table) {
                $table->string('billing_mode', 30)
                    ->default('included')
                    ->after('module_id');
            });
        }

        if (!Schema::hasColumn('plan_modules', 'pricing_type')) {
            Schema::table('plan_modules', function (Blueprint $table) {
                $table->string('pricing_type', 30)
                    ->nullable();
            });
        }

        if (!Schema::hasColumn('plan_modules', 'fixed_price')) {
            Schema::table('plan_modules', function (Blueprint $table) {
                $table->decimal('fixed_price', 15, 2)
                    ->default(0);
            });
        }

        if (!Schema::hasColumn('plan_modules', 'price_per_user')) {
            Schema::table('plan_modules', function (Blueprint $table) {
                $table->decimal('price_per_user', 15, 2)
                    ->default(0);
            });
        }

        if (!Schema::hasColumn('plan_modules', 'included_users')) {
            Schema::table('plan_modules', function (Blueprint $table) {
                $table->unsignedInteger('included_users')
                    ->default(0);
            });
        }

        if (!Schema::hasColumn('plan_modules', 'minimum_users')) {
            Schema::table('plan_modules', function (Blueprint $table) {
                $table->unsignedInteger('minimum_users')
                    ->nullable();
            });
        }

        if (!Schema::hasColumn('plan_modules', 'maximum_users')) {
            Schema::table('plan_modules', function (Blueprint $table) {
                $table->unsignedInteger('maximum_users')
                    ->nullable();
            });
        }

        if (!Schema::hasColumn('plan_modules', 'billing_user_scope')) {
            Schema::table('plan_modules', function (Blueprint $table) {
                $table->string('billing_user_scope', 50)
                    ->nullable();
            });
        }

        if (!Schema::hasColumn('plan_modules', 'billing_interval')) {
            Schema::table('plan_modules', function (Blueprint $table) {
                $table->string('billing_interval', 30)
                    ->nullable();
            });
        }

        if (!Schema::hasColumn('plan_modules', 'settings')) {
            Schema::table('plan_modules', function (Blueprint $table) {
                $table->json('settings')
                    ->nullable();
            });
        }

        /*
        |--------------------------------------------------------------------------
        | Organization Subscriptions
        |--------------------------------------------------------------------------
        */

        if (!Schema::hasColumn('organization_subscriptions', 'ulid')) {
            Schema::table('organization_subscriptions', function (Blueprint $table) {
                $table->char('ulid', 26)
                    ->nullable()
                    ->unique()
                    ->after('id');
            });
        }

        if (!Schema::hasColumn('organization_subscriptions', 'subscription_plan_id')) {
            Schema::table('organization_subscriptions', function (Blueprint $table) {
                $table->foreignId('subscription_plan_id')
                    ->nullable()
                    ->constrained('subscription_plans')
                    ->nullOnDelete();
            });
        }

        if (!Schema::hasColumn('organization_subscriptions', 'status')) {
            Schema::table('organization_subscriptions', function (Blueprint $table) {
                $table->string('status', 30)
                    ->default('active')
                    ->index();
            });
        }

        if (!Schema::hasColumn('organization_subscriptions', 'starts_at')) {
            Schema::table('organization_subscriptions', function (Blueprint $table) {
                $table->timestamp('starts_at')
                    ->nullable();
            });
        }

        if (!Schema::hasColumn('organization_subscriptions', 'trial_ends_at')) {
            Schema::table('organization_subscriptions', function (Blueprint $table) {
                $table->timestamp('trial_ends_at')
                    ->nullable();
            });
        }

        if (!Schema::hasColumn('organization_subscriptions', 'current_period_start')) {
            Schema::table('organization_subscriptions', function (Blueprint $table) {
                $table->timestamp('current_period_start')
                    ->nullable();
            });
        }

        if (!Schema::hasColumn('organization_subscriptions', 'current_period_end')) {
            Schema::table('organization_subscriptions', function (Blueprint $table) {
                $table->timestamp('current_period_end')
                    ->nullable();
            });
        }

        if (!Schema::hasColumn('organization_subscriptions', 'ends_at')) {
            Schema::table('organization_subscriptions', function (Blueprint $table) {
                $table->timestamp('ends_at')
                    ->nullable();
            });
        }

        if (!Schema::hasColumn('organization_subscriptions', 'auto_renew')) {
            Schema::table('organization_subscriptions', function (Blueprint $table) {
                $table->boolean('auto_renew')
                    ->default(true);
            });
        }

        if (!Schema::hasColumn('organization_subscriptions', 'settings')) {
            Schema::table('organization_subscriptions', function (Blueprint $table) {
                $table->json('settings')
                    ->nullable();
            });
        }
    }

    public function down(): void
    {
        /*
         * Alignment migration sengaja tidak melakukan
         * drop column agar rollback tidak merusak
         * tabel billing yang sudah ada sebelumnya.
         */
    }
};
