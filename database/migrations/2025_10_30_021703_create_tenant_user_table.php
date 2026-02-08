<?php

/**
 * Create Tenant User Pivot Table Migration
 *
 * Establishes the Many-to-Many relationship between Tenants and Users.
 * Includes role definition for access control within the tenant context.
 *
 * @package    Database\Migrations
 * @author     Herkobi
 * @copyright  2025 Herkobi
 * @license    MIT
 * @version    1.0.0
 * @since      1.0.0
 */

declare(strict_types=1);

use App\Enums\TenantUserRole;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('tenant_user', function (Blueprint $table) {
            //$table->ulid('id')->primary();
            $table->foreignUlid('tenant_id')
                  ->constrained('tenants')
                  ->cascadeOnDelete();

            $table->foreignUlid('user_id')
                  ->constrained('users')
                  ->cascadeOnDelete();

            $table->unsignedTinyInteger('role')
                  ->default(TenantUserRole::OWNER->value);

            $table->timestamp('joined_at')->nullable();

            $table->unique(['tenant_id', 'user_id']);

            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('tenant_user');
    }
};
