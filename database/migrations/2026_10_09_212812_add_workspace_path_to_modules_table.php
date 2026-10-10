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
                'modules',
                'workspace_path'
            )
        ) {
            Schema::table(
                'modules',
                function (Blueprint $table) {
                    $table
                        ->string(
                            'workspace_path',
                            255
                        )
                        ->nullable()
                        ->after('type');
                }
            );
        }
    }

    public function down(): void
    {
        //
    }
};
