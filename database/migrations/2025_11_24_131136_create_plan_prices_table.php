<?php

/**
 * Create Plan Prices Table Migration
 *
 * Manages pricing variations for plans (e.g., Monthly vs Yearly).
 * Handles billing intervals, trial periods, and currency settings.
 *
 * @package    Database\Migrations
 * @author     Herkobi
 * @copyright  2025 Herkobi
 * @license    MIT
 * @version    1.0.0
 * @since      1.0.0
 */

declare(strict_types=1);

use App\Enums\PlanInterval;
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
        Schema::create('plan_prices', function (Blueprint $table) {
            $table->ulid('id')->primary();

            $table->foreignUlid('plan_id')
                  ->constrained('plans')
                  ->cascadeOnDelete();

            $table->decimal('price', 10, 2);
            $table->string('currency', 3)->default(config('herkobi.payment.currency' ));

            $table->string('interval')
                  ->default(PlanInterval::MONTH->value);

            $table->integer('interval_count')->default(1);

            $table->integer('trial_days')->default(0);

            $table->timestamps();
            $table->softDeletes();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('plan_prices');
    }
};
