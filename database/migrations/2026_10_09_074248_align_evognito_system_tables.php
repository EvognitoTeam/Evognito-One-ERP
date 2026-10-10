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
        | Integrations
        |--------------------------------------------------------------------------
        */

        if (!Schema::hasTable('integrations')) {
            Schema::create('integrations', function (Blueprint $table) {
                $table->id();

                $table->char('ulid', 26)
                    ->unique();

                $table->string('code', 100)
                    ->unique();

                $table->string('name');

                $table->string('category', 100)
                    ->default('other')
                    ->index();

                $table->string('provider', 100)
                    ->nullable();

                $table->string('environment', 30)
                    ->default('production');

                $table->string('status', 30)
                    ->default('inactive')
                    ->index();

                $table->string('base_url')
                    ->nullable();

                $table->string('webhook_url')
                    ->nullable();

                /*
                 * Konfigurasi non-sensitive.
                 */
                $table->json('config')
                    ->nullable();

                /*
                 * Credential encrypted.
                 *
                 * Jangan JSON karena ciphertext hasil
                 * encryption bukan JSON valid.
                 */
                $table->longText('credentials')
                    ->nullable();

                $table->timestamp('last_checked_at')
                    ->nullable();

                $table->string('last_check_status', 30)
                    ->nullable();

                $table->text('last_check_message')
                    ->nullable();

                $table->text('notes')
                    ->nullable();

                $table->foreignId('created_by')
                    ->nullable()
                    ->constrained('users')
                    ->nullOnDelete();

                $table->timestamps();
            });
        }

        /*
        |--------------------------------------------------------------------------
        | Platform Settings
        |--------------------------------------------------------------------------
        */

        if (!Schema::hasTable('platform_settings')) {
            Schema::create('platform_settings', function (Blueprint $table) {
                $table->id();

                $table->string('key')
                    ->unique();

                $table->longText('value')
                    ->nullable();

                $table->string('group', 100)
                    ->default('general')
                    ->index();

                $table->string('label')
                    ->nullable();

                $table->text('description')
                    ->nullable();

                $table->string('type', 50)
                    ->default('string');

                $table->boolean('is_public')
                    ->default(false);

                $table->timestamps();
            });
        } else {
            if (!Schema::hasColumn(
                'platform_settings',
                'group'
            )) {
                Schema::table(
                    'platform_settings',
                    function (Blueprint $table) {
                        $table->string(
                            'group',
                            100
                        )
                            ->default('general')
                            ->index();
                    }
                );
            }

            if (!Schema::hasColumn(
                'platform_settings',
                'label'
            )) {
                Schema::table(
                    'platform_settings',
                    function (Blueprint $table) {
                        $table->string('label')
                            ->nullable();
                    }
                );
            }

            if (!Schema::hasColumn(
                'platform_settings',
                'description'
            )) {
                Schema::table(
                    'platform_settings',
                    function (Blueprint $table) {
                        $table->text('description')
                            ->nullable();
                    }
                );
            }

            if (!Schema::hasColumn(
                'platform_settings',
                'type'
            )) {
                Schema::table(
                    'platform_settings',
                    function (Blueprint $table) {
                        $table->string(
                            'type',
                            50
                        )
                            ->default('string');
                    }
                );
            }

            if (!Schema::hasColumn(
                'platform_settings',
                'is_public'
            )) {
                Schema::table(
                    'platform_settings',
                    function (Blueprint $table) {
                        $table->boolean(
                            'is_public'
                        )
                            ->default(false);
                    }
                );
            }
        }

        /*
        |--------------------------------------------------------------------------
        | Audit Logs
        |--------------------------------------------------------------------------
        */

        if (!Schema::hasTable('audit_logs')) {
            Schema::create('audit_logs', function (Blueprint $table) {
                $table->id();

                $table->char('ulid', 26)
                    ->unique();

                $table->foreignId('organization_id')
                    ->nullable()
                    ->constrained('organizations')
                    ->nullOnDelete();

                $table->foreignId('user_id')
                    ->nullable()
                    ->constrained('users')
                    ->nullOnDelete();

                $table->string('event', 150)
                    ->nullable()
                    ->index();

                $table->string('action', 50)
                    ->nullable()
                    ->index();

                $table->string('subject_type')
                    ->nullable()
                    ->index();

                $table->unsignedBigInteger('subject_id')
                    ->nullable()
                    ->index();

                $table->text('description')
                    ->nullable();

                $table->string('route')
                    ->nullable();

                $table->string('method', 20)
                    ->nullable();

                $table->string('ip_address', 64)
                    ->nullable();

                $table->text('user_agent')
                    ->nullable();

                $table->json('metadata')
                    ->nullable();

                $table->timestamp('created_at')
                    ->nullable()
                    ->index();
            });
        } else {
            if (!Schema::hasColumn('audit_logs', 'ulid')) {
                Schema::table(
                    'audit_logs',
                    function (Blueprint $table) {
                        $table->char('ulid', 26)
                            ->nullable()
                            ->unique();
                    }
                );
            }

            if (!Schema::hasColumn(
                'audit_logs',
                'organization_id'
            )) {
                Schema::table(
                    'audit_logs',
                    function (Blueprint $table) {
                        $table->foreignId(
                            'organization_id'
                        )
                            ->nullable()
                            ->constrained(
                                'organizations'
                            )
                            ->nullOnDelete();
                    }
                );
            }

            if (!Schema::hasColumn(
                'audit_logs',
                'user_id'
            )) {
                Schema::table(
                    'audit_logs',
                    function (Blueprint $table) {
                        $table->foreignId(
                            'user_id'
                        )
                            ->nullable()
                            ->constrained('users')
                            ->nullOnDelete();
                    }
                );
            }

            if (!Schema::hasColumn(
                'audit_logs',
                'event'
            )) {
                Schema::table(
                    'audit_logs',
                    function (Blueprint $table) {
                        $table->string(
                            'event',
                            150
                        )
                            ->nullable()
                            ->index();
                    }
                );
            }

            if (!Schema::hasColumn(
                'audit_logs',
                'action'
            )) {
                Schema::table(
                    'audit_logs',
                    function (Blueprint $table) {
                        $table->string(
                            'action',
                            50
                        )
                            ->nullable()
                            ->index();
                    }
                );
            }

            if (!Schema::hasColumn(
                'audit_logs',
                'subject_type'
            )) {
                Schema::table(
                    'audit_logs',
                    function (Blueprint $table) {
                        $table->string(
                            'subject_type'
                        )
                            ->nullable()
                            ->index();
                    }
                );
            }

            if (!Schema::hasColumn(
                'audit_logs',
                'subject_id'
            )) {
                Schema::table(
                    'audit_logs',
                    function (Blueprint $table) {
                        $table->unsignedBigInteger(
                            'subject_id'
                        )
                            ->nullable()
                            ->index();
                    }
                );
            }

            if (!Schema::hasColumn(
                'audit_logs',
                'description'
            )) {
                Schema::table(
                    'audit_logs',
                    function (Blueprint $table) {
                        $table->text(
                            'description'
                        )
                            ->nullable();
                    }
                );
            }

            if (!Schema::hasColumn(
                'audit_logs',
                'route'
            )) {
                Schema::table(
                    'audit_logs',
                    function (Blueprint $table) {
                        $table->string('route')
                            ->nullable();
                    }
                );
            }

            if (!Schema::hasColumn(
                'audit_logs',
                'method'
            )) {
                Schema::table(
                    'audit_logs',
                    function (Blueprint $table) {
                        $table->string(
                            'method',
                            20
                        )
                            ->nullable();
                    }
                );
            }

            if (!Schema::hasColumn(
                'audit_logs',
                'ip_address'
            )) {
                Schema::table(
                    'audit_logs',
                    function (Blueprint $table) {
                        $table->string(
                            'ip_address',
                            64
                        )
                            ->nullable();
                    }
                );
            }

            if (!Schema::hasColumn(
                'audit_logs',
                'user_agent'
            )) {
                Schema::table(
                    'audit_logs',
                    function (Blueprint $table) {
                        $table->text('user_agent')
                            ->nullable();
                    }
                );
            }

            if (!Schema::hasColumn(
                'audit_logs',
                'metadata'
            )) {
                Schema::table(
                    'audit_logs',
                    function (Blueprint $table) {
                        $table->json('metadata')
                            ->nullable();
                    }
                );
            }

            if (!Schema::hasColumn(
                'audit_logs',
                'created_at'
            )) {
                Schema::table(
                    'audit_logs',
                    function (Blueprint $table) {
                        $table->timestamp(
                            'created_at'
                        )
                            ->nullable()
                            ->index();
                    }
                );
            }
        }
    }

    public function down(): void
    {
        /*
         * Tidak drop alignment field
         * supaya data system tidak hilang.
         */
    }
};
