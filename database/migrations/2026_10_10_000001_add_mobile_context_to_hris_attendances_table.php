<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('hris_attendances', function (Blueprint $table) {
            if (!Schema::hasColumn('hris_attendances', 'clock_in_accuracy_meters')) {
                $table
                    ->decimal('clock_in_accuracy_meters', 8, 2)
                    ->nullable()
                    ->after('clock_in_longitude');
            }

            if (!Schema::hasColumn('hris_attendances', 'clock_out_accuracy_meters')) {
                $table
                    ->decimal('clock_out_accuracy_meters', 8, 2)
                    ->nullable()
                    ->after('clock_out_longitude');
            }
        });
    }

    public function down(): void
    {
        Schema::table('hris_attendances', function (Blueprint $table) {
            $columns = [];

            if (Schema::hasColumn('hris_attendances', 'clock_in_accuracy_meters')) {
                $columns[] = 'clock_in_accuracy_meters';
            }

            if (Schema::hasColumn('hris_attendances', 'clock_out_accuracy_meters')) {
                $columns[] = 'clock_out_accuracy_meters';
            }

            if ($columns !== []) {
                $table->dropColumn($columns);
            }
        });
    }
};
