<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table(
            'hris_attendances',
            function (Blueprint $table) {
                $table
                    ->foreignId('work_location_id')
                    ->nullable()
                    ->after('employee_id')
                    ->constrained(
                        'hris_work_locations'
                    )
                    ->nullOnDelete();

                $table
                    ->unsignedInteger(
                        'clock_in_distance_meters'
                    )
                    ->nullable()
                    ->after(
                        'clock_in_longitude'
                    );

                $table
                    ->boolean(
                        'clock_in_within_geofence'
                    )
                    ->nullable()
                    ->after(
                        'clock_in_distance_meters'
                    );

                $table
                    ->unsignedInteger(
                        'clock_out_distance_meters'
                    )
                    ->nullable()
                    ->after(
                        'clock_out_longitude'
                    );

                $table
                    ->boolean(
                        'clock_out_within_geofence'
                    )
                    ->nullable()
                    ->after(
                        'clock_out_distance_meters'
                    );

                $table->index(
                    [
                        'organization_id',
                        'work_location_id',
                    ],
                    'hris_att_org_location_idx'
                );
            }
        );
    }

    public function down(): void
    {
        Schema::table(
            'hris_attendances',
            function (Blueprint $table) {
                $table->dropIndex(
                    'hris_att_org_location_idx'
                );

                $table->dropForeign([
                    'work_location_id',
                ]);

                $table->dropColumn([
                    'work_location_id',

                    'clock_in_distance_meters',
                    'clock_in_within_geofence',

                    'clock_out_distance_meters',
                    'clock_out_within_geofence',
                ]);
            }
        );
    }
};
