<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (
            !Schema::hasColumn(
                'invoices',
                'source'
            )
        ) {
            Schema::table(
                'invoices',
                function (
                    Blueprint $table
                ) {
                    $table
                        ->string(
                            'source',
                            30
                        )
                        ->default(
                            'subscription'
                        )
                        ->index();
                }
            );
        }

        if (
            !Schema::hasColumn(
                'invoices',
                'title'
            )
        ) {
            Schema::table(
                'invoices',
                function (
                    Blueprint $table
                ) {
                    $table
                        ->string('title')
                        ->nullable();
                }
            );
        }

        if (
            !Schema::hasColumn(
                'invoices',
                'created_by'
            )
        ) {
            Schema::table(
                'invoices',
                function (
                    Blueprint $table
                ) {
                    $table
                        ->foreignId(
                            'created_by'
                        )
                        ->nullable()
                        ->constrained(
                            'users'
                        )
                        ->nullOnDelete();
                }
            );
        }
    }

    public function down(): void
    {
        /*
         * Tidak drop column agar invoice
         * existing tidak rusak.
         */
    }
};
