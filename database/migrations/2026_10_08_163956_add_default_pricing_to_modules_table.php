<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('modules', function (Blueprint $table) {
            $table->string('pricing_type', 30)
                ->default('fixed')
                ->after('status');

            $table->decimal('fixed_price', 15, 2)
                ->default(0)
                ->after('pricing_type');

            $table->decimal('price_per_user', 15, 2)
                ->default(0)
                ->after('fixed_price');

            $table->unsignedInteger('included_users')
                ->default(0)
                ->after('price_per_user');

            $table->unsignedInteger('minimum_users')
                ->nullable()
                ->after('included_users');

            $table->unsignedInteger('maximum_users')
                ->nullable()
                ->after('minimum_users');

            $table->string('billing_user_scope', 50)
                ->default('licensed_users')
                ->after('maximum_users');

            $table->string('billing_interval', 30)
                ->default('monthly')
                ->after('billing_user_scope');
        });
    }

    public function down(): void
    {
        Schema::table('modules', function (Blueprint $table) {
            $table->dropColumn([
                'pricing_type',
                'fixed_price',
                'price_per_user',
                'included_users',
                'minimum_users',
                'maximum_users',
                'billing_user_scope',
                'billing_interval',
            ]);
        });
    }
};
