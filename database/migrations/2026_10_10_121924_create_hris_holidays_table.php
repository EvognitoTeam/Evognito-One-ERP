<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create(
            'hris_holidays',
            function (Blueprint $table) {
                $table->id();

                $table
                    ->char('ulid', 26)
                    ->unique();

                $table
                    ->foreignId('organization_id')
                    ->constrained('organizations')
                    ->cascadeOnDelete();

                /*
                |--------------------------------------------------------------------------
                | Holiday Period
                |--------------------------------------------------------------------------
                */

                $table->date('holiday_date');

                $table
                    ->date('end_date')
                    ->nullable();

                /*
                |--------------------------------------------------------------------------
                | Information
                |--------------------------------------------------------------------------
                */

                $table->string('name');

                $table
                    ->string('color', 20)
                    ->nullable();

                $table
                    ->boolean('is_recurring')
                    ->default(false);

                $table
                    ->text('description')
                    ->nullable();

                /*
                |--------------------------------------------------------------------------
                | State
                |--------------------------------------------------------------------------
                */

                $table
                    ->boolean('is_active')
                    ->default(true);

                $table->timestamps();
                $table->softDeletes();

                /*
                |--------------------------------------------------------------------------
                | Indexes
                |--------------------------------------------------------------------------
                */

                $table->index(
                    [
                        'organization_id',
                        'holiday_date',
                    ],
                    'hris_hol_org_date_idx'
                );

                $table->index(
                    [
                        'organization_id',
                        'is_recurring',
                    ],
                    'hris_hol_org_rec_idx'
                );

                $table->index(
                    [
                        'organization_id',
                        'is_active',
                    ],
                    'hris_hol_org_active_idx'
                );
            }
        );
    }

    public function down(): void
    {
        Schema::dropIfExists(
            'hris_holidays'
        );
    }
};
