<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->char('ulid', 26)
                ->nullable()
                ->unique()
                ->after('id');

            $table->string('username')
                ->nullable()
                ->unique()
                ->after('email');

            $table->string('phone', 50)
                ->nullable()
                ->after('username');

            $table->string('status', 30)
                ->default('active')
                ->index()
                ->after('password');

            $table->boolean('is_super_admin')
                ->default(false)
                ->index()
                ->after('status');

            $table->timestamp('last_login_at')
                ->nullable()
                ->after('is_super_admin');

            $table->softDeletes();
        });
    }

    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropSoftDeletes();

            $table->dropColumn([
                'ulid',
                'username',
                'phone',
                'status',
                'is_super_admin',
                'last_login_at',
            ]);
        });
    }
};
