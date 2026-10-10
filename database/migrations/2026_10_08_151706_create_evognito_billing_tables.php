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

        Schema::create('subscription_plans', function (Blueprint $table) {
            $table->id();

            $table->char('ulid', 26)
                ->unique();

            $table->string('code', 100)
                ->unique();

            $table->string('name');

            $table->text('description')
                ->nullable();

            $table->string('billing_interval', 30)
                ->default('monthly');

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
        | Plan Modules
        |--------------------------------------------------------------------------
        |
        | pricing_type:
        | - fixed
        | - per_user
        | - hybrid
        |
        */

        Schema::create('plan_modules', function (Blueprint $table) {
            $table->id();

            $table->foreignId('plan_id')
                ->constrained('subscription_plans')
                ->cascadeOnDelete();

            $table->foreignId('module_id')
                ->constrained('modules')
                ->cascadeOnDelete();

            $table->string('pricing_type', 30)
                ->default('fixed');

            $table->decimal('fixed_price', 15, 2)
                ->default(0);

            $table->decimal('price_per_user', 15, 2)
                ->default(0);

            $table->unsignedInteger('included_users')
                ->default(0);

            $table->unsignedInteger('minimum_users')
                ->nullable();

            $table->unsignedInteger('maximum_users')
                ->nullable();

            $table->string('billing_user_scope', 50)
                ->default('licensed_users');

            $table->string('billing_interval', 30)
                ->nullable();

            $table->json('settings')
                ->nullable();

            $table->timestamps();

            $table->unique([
                'plan_id',
                'module_id',
            ]);
        });

        /*
        |--------------------------------------------------------------------------
        | Organization Subscriptions
        |--------------------------------------------------------------------------
        */

        Schema::create('organization_subscriptions', function (Blueprint $table) {
            $table->id();

            $table->foreignId('organization_id')
                ->constrained('organizations')
                ->cascadeOnDelete();

            $table->foreignId('plan_id')
                ->nullable()
                ->constrained('subscription_plans')
                ->nullOnDelete();

            $table->string('status', 30)
                ->default('active')
                ->index();

            $table->timestamp('started_at')
                ->nullable();

            $table->timestamp('current_period_start')
                ->nullable();

            $table->timestamp('current_period_end')
                ->nullable();

            $table->timestamp('canceled_at')
                ->nullable();

            $table->json('settings')
                ->nullable();

            $table->timestamps();
        });

        /*
        |--------------------------------------------------------------------------
        | Custom Pricing Per Organization
        |--------------------------------------------------------------------------
        */

        Schema::create('organization_module_pricing', function (Blueprint $table) {
            $table->id();

            $table->foreignId('organization_id')
                ->constrained('organizations')
                ->cascadeOnDelete();

            $table->foreignId('module_id')
                ->constrained('modules')
                ->cascadeOnDelete();

            $table->string('pricing_type', 30)
                ->default('fixed');

            $table->decimal('fixed_price', 15, 2)
                ->default(0);

            $table->decimal('price_per_user', 15, 2)
                ->default(0);

            $table->unsignedInteger('included_users')
                ->default(0);

            $table->unsignedInteger('minimum_users')
                ->nullable();

            $table->unsignedInteger('maximum_users')
                ->nullable();

            $table->string('billing_user_scope', 50)
                ->default('licensed_users');

            $table->date('effective_from')
                ->nullable();

            $table->date('effective_until')
                ->nullable();

            $table->json('settings')
                ->nullable();

            $table->timestamps();

            $table->index([
                'organization_id',
                'module_id',
                'effective_from',
            ], 'org_module_pricing_lookup_index');
        });

        /*
        |--------------------------------------------------------------------------
        | Invoices
        |--------------------------------------------------------------------------
        */

        Schema::create('invoices', function (Blueprint $table) {
            $table->id();

            $table->char('ulid', 26)
                ->unique();

            $table->foreignId('organization_id')
                ->constrained('organizations')
                ->cascadeOnDelete();

            $table->foreignId('subscription_id')
                ->nullable()
                ->constrained('organization_subscriptions')
                ->nullOnDelete();

            $table->string('invoice_number', 100)
                ->unique();

            $table->date('billing_period_start')
                ->nullable();

            $table->date('billing_period_end')
                ->nullable();

            $table->date('issued_at');

            $table->date('due_at');

            $table->decimal('subtotal', 15, 2)
                ->default(0);

            $table->decimal('additional_total', 15, 2)
                ->default(0);

            $table->decimal('discount_total', 15, 2)
                ->default(0);

            $table->decimal('tax_total', 15, 2)
                ->default(0);

            $table->decimal('total', 15, 2)
                ->default(0);

            $table->string('currency', 10)
                ->default('IDR');

            $table->string('status', 30)
                ->default('draft')
                ->index();

            $table->timestamp('paid_at')
                ->nullable();

            $table->json('metadata')
                ->nullable();

            $table->timestamps();
            $table->softDeletes();

            $table->index([
                'organization_id',
                'status',
            ]);
        });

        /*
        |--------------------------------------------------------------------------
        | Invoice Items
        |--------------------------------------------------------------------------
        */

        Schema::create('invoice_items', function (Blueprint $table) {
            $table->id();

            $table->foreignId('invoice_id')
                ->constrained('invoices')
                ->cascadeOnDelete();

            $table->foreignId('module_id')
                ->nullable()
                ->constrained('modules')
                ->nullOnDelete();

            $table->string('type', 50);

            $table->string('description');

            $table->decimal('quantity', 15, 4)
                ->default(1);

            $table->decimal('unit_price', 15, 2)
                ->default(0);

            $table->decimal('amount', 15, 2)
                ->default(0);

            $table->json('metadata')
                ->nullable();

            $table->timestamps();
        });

        /*
        |--------------------------------------------------------------------------
        | Payments
        |--------------------------------------------------------------------------
        */

        Schema::create('payments', function (Blueprint $table) {
            $table->id();

            $table->char('ulid', 26)
                ->unique();

            $table->foreignId('organization_id')
                ->constrained('organizations')
                ->cascadeOnDelete();

            $table->foreignId('invoice_id')
                ->constrained('invoices')
                ->cascadeOnDelete();

            $table->string('provider', 50)
                ->default('midtrans');

            $table->string('order_id', 150)
                ->unique();

            $table->string('provider_transaction_id', 255)
                ->nullable();

            $table->string('payment_type', 100)
                ->nullable();

            $table->string('bank', 50)
                ->nullable();

            $table->string('va_number', 150)
                ->nullable();

            $table->decimal('amount', 15, 2);

            $table->string('currency', 10)
                ->default('IDR');

            $table->string('status', 50)
                ->default('pending')
                ->index();

            $table->timestamp('paid_at')
                ->nullable();

            $table->timestamp('expires_at')
                ->nullable();

            $table->json('response')
                ->nullable();

            $table->timestamps();
        });

        /*
        |--------------------------------------------------------------------------
        | Payment Logs
        |--------------------------------------------------------------------------
        */

        Schema::create('payment_logs', function (Blueprint $table) {
            $table->id();

            $table->foreignId('payment_id')
                ->nullable()
                ->constrained('payments')
                ->nullOnDelete();

            $table->string('provider', 50);

            $table->string('external_status', 100)
                ->nullable();

            $table->string('internal_status', 100)
                ->nullable();

            $table->char('payload_hash', 64)
                ->unique();

            $table->json('payload')
                ->nullable();

            $table->timestamp('created_at')
                ->useCurrent();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('payment_logs');
        Schema::dropIfExists('payments');

        Schema::dropIfExists('invoice_items');
        Schema::dropIfExists('invoices');

        Schema::dropIfExists('organization_module_pricing');
        Schema::dropIfExists('organization_subscriptions');

        Schema::dropIfExists('plan_modules');
        Schema::dropIfExists('subscription_plans');
    }
};
