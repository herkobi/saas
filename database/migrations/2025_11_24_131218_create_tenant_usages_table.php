<?php

/**
 * Create Tenant Usages Table Migration
 *
 * Tracks the consumption/usage of metered features by tenants.
 * Used for billing (overage calculations) or enforcing hard limits
 * on resources like storage, API calls, or email sends.
 *
 * @package    Database\Migrations
 * @author     Herkobi
 * @copyright  2025 Herkobi
 * @license    MIT
 * @version    1.0.0
 * @since      1.0.0
 */

declare(strict_types=1);

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
        Schema::create('tenant_usages', function (Blueprint $table) {
            $table->ulid('id')->primary();

            $table->foreignUlid('tenant_id')
                  ->constrained('tenants')
                  ->cascadeOnDelete();

            $table->foreignUlid('feature_id')
                  ->constrained('features')
                  ->cascadeOnDelete();

            $table->decimal('used', 12, 2)->default(0);

            $table->timestamp('cycle_ends_at')->nullable();

            $table->timestamps();

            $table->unique(['tenant_id', 'feature_id']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('tenant_usages');
    }
};
