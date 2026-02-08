<?php

/**
 * Create Tenant Features Table Migration
 *
 * Stores tenant-specific overrides for feature limits.
 * Allows a specific tenant to have a custom limit (value) for a feature,
 * bypassing the default limit defined in their subscription plan.
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
        Schema::create('tenant_features', function (Blueprint $table) {
            $table->ulid('id')->primary();

            $table->foreignUlid('tenant_id')
                  ->constrained('tenants')
                  ->cascadeOnDelete();

            $table->foreignUlid('feature_id')
                  ->constrained('features')
                  ->cascadeOnDelete();

            $table->string('value');

            $table->timestamps();

            $table->unique(['tenant_id', 'feature_id']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('tenant_features');
    }
};
