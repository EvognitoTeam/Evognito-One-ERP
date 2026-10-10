<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('registration_invites', function (Blueprint $table) {
            $table->id();

            $table->char('ulid', 26)->unique();

            $table->char('token_hash', 64)->unique();

            $table->text('token_encrypted');

            $table->string('token_fingerprint', 16)
                ->nullable()
                ->index();

            $table->string('organization_name')
                ->nullable();

            $table->string('organization_type', 30)
                ->default('business');

            $table->string('contact_name')
                ->nullable();

            $table->string('contact_email')
                ->nullable();

            $table->string('contact_phone', 50)
                ->nullable();

            $table->json('locked_fields')
                ->nullable();

            $table->json('module_ids')
                ->nullable();

            $table->json('pricing_snapshot')
                ->nullable();

            $table->timestamp('expires_at');

            $table->timestamp('used_at')
                ->nullable();

            $table->timestamp('revoked_at')
                ->nullable();

            $table->foreignId('created_by')
                ->nullable()
                ->constrained('users')
                ->nullOnDelete();

            $table->foreignId('used_organization_id')
                ->nullable()
                ->constrained('organizations')
                ->nullOnDelete();

            $table->foreignId('used_by_user_id')
                ->nullable()
                ->constrained('users')
                ->nullOnDelete();

            $table->timestamps();

            $table->index([
                'expires_at',
                'used_at',
                'revoked_at',
            ]);
        });

        Schema::create('registration_requests', function (Blueprint $table) {
            $table->id();

            $table->char('ulid', 26)
                ->unique();

            $table->string('source', 30)
                ->default('self')
                ->index();

            $table->foreignId('invite_id')
                ->nullable()
                ->constrained('registration_invites')
                ->nullOnDelete();

            $table->string('organization_name');

            $table->string('organization_type', 30)
                ->default('business');

            $table->string('owner_name');

            $table->string('email');

            $table->string('phone', 50)
                ->nullable();

            /*
             * Tidak pernah simpan password plaintext.
             */
            $table->string('password_hash');

            $table->json('module_ids')
                ->nullable();

            $table->json('pricing_snapshot')
                ->nullable();

            $table->string('status', 30)
                ->default('submitted')
                ->index();

            $table->timestamp('submitted_at')
                ->nullable();

            $table->timestamp('reviewed_at')
                ->nullable();

            $table->foreignId('reviewed_by')
                ->nullable()
                ->constrained('users')
                ->nullOnDelete();

            $table->timestamp('completed_at')
                ->nullable();

            $table->text('rejection_reason')
                ->nullable();

            $table->foreignId('created_organization_id')
                ->nullable()
                ->constrained('organizations')
                ->nullOnDelete();

            $table->foreignId('created_user_id')
                ->nullable()
                ->constrained('users')
                ->nullOnDelete();

            $table->json('metadata')
                ->nullable();

            $table->timestamps();

            $table->index([
                'email',
                'status',
            ]);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('registration_requests');
        Schema::dropIfExists('registration_invites');
    }
};
