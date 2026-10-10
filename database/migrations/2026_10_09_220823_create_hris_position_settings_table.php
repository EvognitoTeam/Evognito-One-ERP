<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create(
            'hris_position_settings',
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
                    ->foreignId('position_id')
                    ->constrained('positions')
                    ->cascadeOnDelete();

                $table
                    ->time('clock_in')
                    ->default('08:00:00');

                $table
                    ->time('clock_out')
                    ->default('17:00:00');

                $table
                    ->boolean('is_mobile')
                    ->default(false);

                $table
                    ->unsignedInteger('late_tolerance_minutes')
                    ->default(0);

                $table
                    ->json('settings')
                    ->nullable();

                $table->timestamps();

                $table->unique(
                    [
                        'organization_id',
                        'position_id',
                    ],
                    'hris_pos_org_position_uq'
                );

                $table->index(
                    'organization_id',
                    'hris_pos_org_idx'
                );
            }
        );
    }

    public function down(): void
    {
        Schema::dropIfExists(
            'hris_position_settings'
        );
    }
};
