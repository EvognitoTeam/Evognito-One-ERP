<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create(
            'hris_work_locations',
            function (Blueprint $table) {
                $table->id();

                $table
                    ->char('ulid', 26)
                    ->unique();

                $table
                    ->foreignId('organization_id')
                    ->constrained('organizations')
                    ->cascadeOnDelete();

                $table
                    ->foreignId('branch_id')
                    ->nullable()
                    ->constrained('branches')
                    ->nullOnDelete();

                $table->string('name');

                $table
                    ->string('type', 30)
                    ->default('office');

                $table
                    ->string('address')
                    ->nullable();

                $table
                    ->decimal(
                        'latitude',
                        10,
                        7
                    );

                $table
                    ->decimal(
                        'longitude',
                        10,
                        7
                    );

                $table
                    ->unsignedInteger(
                        'radius_meters'
                    )
                    ->default(50);

                $table
                    ->boolean(
                        'geofence_enabled'
                    )
                    ->default(true);

                $table
                    ->boolean('is_active')
                    ->default(true);

                $table
                    ->text('notes')
                    ->nullable();

                $table->timestamps();

                $table->index(
                    [
                        'organization_id',
                        'is_active',
                    ],
                    'hris_loc_org_active_idx'
                );

                $table->index(
                    [
                        'organization_id',
                        'branch_id',
                    ],
                    'hris_loc_org_branch_idx'
                );
            }
        );
    }

    public function down(): void
    {
        Schema::dropIfExists(
            'hris_work_locations'
        );
    }
};
