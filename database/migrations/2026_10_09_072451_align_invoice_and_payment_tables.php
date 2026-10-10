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
        | Module User Licenses
        |--------------------------------------------------------------------------
        |
        | Dipakai untuk billing scope: licensed_users.
        |
        */

        if (!Schema::hasTable('module_user_licenses')) {
            Schema::create('module_user_licenses', function (Blueprint $table) {
                $table->id();

                $table->foreignId('organization_id')
                    ->constrained('organizations')
                    ->cascadeOnDelete();

                $table->foreignId('module_id')
                    ->constrained('modules')
                    ->cascadeOnDelete();

                $table->foreignId('user_id')
                    ->constrained('users')
                    ->cascadeOnDelete();

                $table->string('status', 30)
                    ->default('active')
                    ->index();

                $table->timestamp('started_at')
                    ->nullable();

                $table->timestamp('ended_at')
                    ->nullable();

                $table->json('metadata')
                    ->nullable();

                $table->timestamps();

                $table->unique([
                    'organization_id',
                    'module_id',
                    'user_id',
                ], 'module_user_license_unique');
            });
        }

        /*
        |--------------------------------------------------------------------------
        | Invoices
        |--------------------------------------------------------------------------
        */

        if (!Schema::hasColumn('invoices', 'ulid')) {
            Schema::table('invoices', function (Blueprint $table) {
                $table->char('ulid', 26)
                    ->nullable()
                    ->unique()
                    ->after('id');
            });
        }

        if (!Schema::hasColumn('invoices', 'invoice_number')) {
            Schema::table('invoices', function (Blueprint $table) {
                $table->string('invoice_number', 100)
                    ->nullable()
                    ->unique();
            });
        }

        if (!Schema::hasColumn('invoices', 'organization_id')) {
            Schema::table('invoices', function (Blueprint $table) {
                $table->foreignId('organization_id')
                    ->nullable()
                    ->constrained('organizations')
                    ->nullOnDelete();
            });
        }

        if (!Schema::hasColumn('invoices', 'organization_subscription_id')) {
            Schema::table('invoices', function (Blueprint $table) {
                $table->foreignId('organization_subscription_id')
                    ->nullable()
                    ->constrained('organization_subscriptions')
                    ->nullOnDelete();
            });
        }

        if (!Schema::hasColumn('invoices', 'period_start')) {
            Schema::table('invoices', function (Blueprint $table) {
                $table->date('period_start')
                    ->nullable();
            });
        }

        if (!Schema::hasColumn('invoices', 'period_end')) {
            Schema::table('invoices', function (Blueprint $table) {
                $table->date('period_end')
                    ->nullable();
            });
        }

        if (!Schema::hasColumn('invoices', 'issued_at')) {
            Schema::table('invoices', function (Blueprint $table) {
                $table->timestamp('issued_at')
                    ->nullable();
            });
        }

        if (!Schema::hasColumn('invoices', 'due_at')) {
            Schema::table('invoices', function (Blueprint $table) {
                $table->timestamp('due_at')
                    ->nullable()
                    ->index();
            });
        }

        if (!Schema::hasColumn('invoices', 'status')) {
            Schema::table('invoices', function (Blueprint $table) {
                $table->string('status', 30)
                    ->default('draft')
                    ->index();
            });
        }

        if (!Schema::hasColumn('invoices', 'currency')) {
            Schema::table('invoices', function (Blueprint $table) {
                $table->string('currency', 10)
                    ->default('IDR');
            });
        }

        if (!Schema::hasColumn('invoices', 'subtotal')) {
            Schema::table('invoices', function (Blueprint $table) {
                $table->decimal('subtotal', 15, 2)
                    ->default(0);
            });
        }

        if (!Schema::hasColumn('invoices', 'additional_total')) {
            Schema::table('invoices', function (Blueprint $table) {
                $table->decimal('additional_total', 15, 2)
                    ->default(0);
            });
        }

        if (!Schema::hasColumn('invoices', 'discount_total')) {
            Schema::table('invoices', function (Blueprint $table) {
                $table->decimal('discount_total', 15, 2)
                    ->default(0);
            });
        }

        if (!Schema::hasColumn('invoices', 'tax_total')) {
            Schema::table('invoices', function (Blueprint $table) {
                $table->decimal('tax_total', 15, 2)
                    ->default(0);
            });
        }

        if (!Schema::hasColumn('invoices', 'total')) {
            Schema::table('invoices', function (Blueprint $table) {
                $table->decimal('total', 15, 2)
                    ->default(0);
            });
        }

        if (!Schema::hasColumn('invoices', 'paid_at')) {
            Schema::table('invoices', function (Blueprint $table) {
                $table->timestamp('paid_at')
                    ->nullable();
            });
        }

        if (!Schema::hasColumn('invoices', 'notes')) {
            Schema::table('invoices', function (Blueprint $table) {
                $table->text('notes')
                    ->nullable();
            });
        }

        if (!Schema::hasColumn('invoices', 'metadata')) {
            Schema::table('invoices', function (Blueprint $table) {
                $table->json('metadata')
                    ->nullable();
            });
        }

        /*
        |--------------------------------------------------------------------------
        | Invoice Items
        |--------------------------------------------------------------------------
        */

        if (!Schema::hasColumn('invoice_items', 'module_id')) {
            Schema::table('invoice_items', function (Blueprint $table) {
                $table->foreignId('module_id')
                    ->nullable()
                    ->constrained('modules')
                    ->nullOnDelete();
            });
        }

        if (!Schema::hasColumn('invoice_items', 'type')) {
            Schema::table('invoice_items', function (Blueprint $table) {
                $table->string('type', 50)
                    ->default('charge');
            });
        }

        if (!Schema::hasColumn('invoice_items', 'code')) {
            Schema::table('invoice_items', function (Blueprint $table) {
                $table->string('code', 100)
                    ->nullable();
            });
        }

        if (!Schema::hasColumn('invoice_items', 'description')) {
            Schema::table('invoice_items', function (Blueprint $table) {
                $table->string('description')
                    ->nullable();
            });
        }

        if (!Schema::hasColumn('invoice_items', 'quantity')) {
            Schema::table('invoice_items', function (Blueprint $table) {
                $table->decimal('quantity', 15, 2)
                    ->default(1);
            });
        }

        if (!Schema::hasColumn('invoice_items', 'unit_price')) {
            Schema::table('invoice_items', function (Blueprint $table) {
                $table->decimal('unit_price', 15, 2)
                    ->default(0);
            });
        }

        if (!Schema::hasColumn('invoice_items', 'amount')) {
            Schema::table('invoice_items', function (Blueprint $table) {
                $table->decimal('amount', 15, 2)
                    ->default(0);
            });
        }

        if (!Schema::hasColumn('invoice_items', 'metadata')) {
            Schema::table('invoice_items', function (Blueprint $table) {
                $table->json('metadata')
                    ->nullable();
            });
        }

        /*
        |--------------------------------------------------------------------------
        | Payments
        |--------------------------------------------------------------------------
        */

        if (!Schema::hasColumn('payments', 'ulid')) {
            Schema::table('payments', function (Blueprint $table) {
                $table->char('ulid', 26)
                    ->nullable()
                    ->unique()
                    ->after('id');
            });
        }

        if (!Schema::hasColumn('payments', 'payment_number')) {
            Schema::table('payments', function (Blueprint $table) {
                $table->string('payment_number', 100)
                    ->nullable()
                    ->unique();
            });
        }

        if (!Schema::hasColumn('payments', 'organization_id')) {
            Schema::table('payments', function (Blueprint $table) {
                $table->foreignId('organization_id')
                    ->nullable()
                    ->constrained('organizations')
                    ->nullOnDelete();
            });
        }

        if (!Schema::hasColumn('payments', 'invoice_id')) {
            Schema::table('payments', function (Blueprint $table) {
                $table->foreignId('invoice_id')
                    ->nullable()
                    ->constrained('invoices')
                    ->nullOnDelete();
            });
        }

        if (!Schema::hasColumn('payments', 'provider')) {
            Schema::table('payments', function (Blueprint $table) {
                $table->string('provider', 50)
                    ->default('manual');
            });
        }

        if (!Schema::hasColumn('payments', 'method')) {
            Schema::table('payments', function (Blueprint $table) {
                $table->string('method', 50)
                    ->nullable();
            });
        }

        if (!Schema::hasColumn('payments', 'status')) {
            Schema::table('payments', function (Blueprint $table) {
                $table->string('status', 30)
                    ->default('pending')
                    ->index();
            });
        }

        if (!Schema::hasColumn('payments', 'amount')) {
            Schema::table('payments', function (Blueprint $table) {
                $table->decimal('amount', 15, 2)
                    ->default(0);
            });
        }

        if (!Schema::hasColumn('payments', 'currency')) {
            Schema::table('payments', function (Blueprint $table) {
                $table->string('currency', 10)
                    ->default('IDR');
            });
        }

        if (!Schema::hasColumn('payments', 'reference')) {
            Schema::table('payments', function (Blueprint $table) {
                $table->string('reference')
                    ->nullable();
            });
        }

        if (!Schema::hasColumn('payments', 'provider_reference')) {
            Schema::table('payments', function (Blueprint $table) {
                $table->string('provider_reference')
                    ->nullable()
                    ->index();
            });
        }

        if (!Schema::hasColumn('payments', 'paid_at')) {
            Schema::table('payments', function (Blueprint $table) {
                $table->timestamp('paid_at')
                    ->nullable();
            });
        }

        if (!Schema::hasColumn('payments', 'notes')) {
            Schema::table('payments', function (Blueprint $table) {
                $table->text('notes')
                    ->nullable();
            });
        }

        if (!Schema::hasColumn('payments', 'metadata')) {
            Schema::table('payments', function (Blueprint $table) {
                $table->json('metadata')
                    ->nullable();
            });
        }
    }

    public function down(): void
    {
        /*
         * Alignment migration sengaja tidak menghapus field
         * agar rollback tidak merusak data billing existing.
         */
    }
};
