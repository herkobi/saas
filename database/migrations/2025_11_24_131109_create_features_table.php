<?php

/**
 * Create Features Table Migration
 *
 * Defines the system capabilities/features that can be assigned to plans.
 * Used to control access limits and feature toggles.
 *
 * @package    Database\Migrations
 * @author     Herkobi
 * @copyright  2025 Herkobi
 * @license    MIT
 * @version    1.0.0
 * @since      1.0.0
 */

declare(strict_types=1);

use App\Enums\FeatureType;
use App\Enums\ResetPeriod;
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
        Schema::create('features', function (Blueprint $table) {
            $table->ulid('id')->primary();

            $table->string('code')->unique()->index();
            $table->string('name');
            $table->string('slug')->unique();
            $table->text('description')->nullable();

            $table->string('type')
                  ->default(FeatureType::LIMIT->value);

            $table->boolean('is_active')->default(true);
            $table->string('unit')->nullable();
            $table->string('reset_period')
                  ->default(value: ResetPeriod::Monthly->value);

            $table->timestamps();
            $table->softDeletes();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('features');
    }
};
