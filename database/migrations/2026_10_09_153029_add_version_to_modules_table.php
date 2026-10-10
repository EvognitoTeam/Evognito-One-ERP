<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (!Schema::hasColumn('modules', 'version')) {
            Schema::table('modules', function (Blueprint $table) {
                $table->string('version', 50)
                    ->default('1.0.0')
                    ->after('name');
            });
        }
    }

    public function down(): void
    {
        //
    }
};
