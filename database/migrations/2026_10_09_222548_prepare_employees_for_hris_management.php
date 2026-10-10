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
        | Employee Identity
        |--------------------------------------------------------------------------
        |
        | Employee tidak harus mempunyai User account.
        | Karena itu nama / email / phone tetap tersedia
        | langsung pada master Employee.
        |
        */

        Schema::table(
            'employees',
            function (Blueprint $table) {
                if (
                    !Schema::hasColumn(
                        'employees',
                        'name'
                    )
                ) {
                    $table
                        ->string('name')
                        ->nullable();
                }

                if (
                    !Schema::hasColumn(
                        'employees',
                        'email'
                    )
                ) {
                    $table
                        ->string('email')
                        ->nullable();
                }

                if (
                    !Schema::hasColumn(
                        'employees',
                        'phone'
                    )
                ) {
                    $table
                        ->string('phone', 50)
                        ->nullable();
                }
            }
        );

        /*
        |--------------------------------------------------------------------------
        | User Account Optional
        |--------------------------------------------------------------------------
        */

        if (
            Schema::hasColumn(
                'employees',
                'user_id'
            )
        ) {
            Schema::table(
                'employees',
                function (Blueprint $table) {
                    $table
                        ->unsignedBigInteger(
                            'user_id'
                        )
                        ->nullable()
                        ->change();
                }
            );
        }
    }

    public function down(): void
    {
        /*
        |--------------------------------------------------------------------------
        | Intentionally Empty
        |--------------------------------------------------------------------------
        |
        | Tidak kita balikkan user_id ke NOT NULL karena employee
        | yang sudah dibuat tanpa login account bisa rusak.
        |
        */
    }
};
