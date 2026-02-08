<?php

declare(strict_types=1);

use App\Enums\InvitationStatus;
use App\Enums\TenantUserRole;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('tenant_invitations', function (Blueprint $table) {
            $table->ulid('id')->primary();

            $table->foreignUlid('tenant_id')
                ->constrained('tenants')
                ->cascadeOnDelete();

            $table->string('email');

            $table->unsignedTinyInteger('role')
                ->default(TenantUserRole::STAFF->value);

            $table->string('token', 64);

            $table->unsignedTinyInteger('status')
                ->default(InvitationStatus::PENDING->value);

            $table->foreignUlid('invited_by')
                ->constrained('users')
                ->cascadeOnDelete();

            $table->foreignUlid('accepted_by')
                ->nullable()
                ->constrained('users')
                ->nullOnDelete();

            $table->timestamp('expires_at');
            $table->timestamp('accepted_at')->nullable();

            $table->timestamps();
            $table->softDeletes();

            $table->index(['tenant_id', 'status']);
            $table->index(['email', 'status']);
            $table->index('token');
            $table->index('expires_at');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('tenant_invitations');
    }
};
