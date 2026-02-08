<?php

/**
 * Create Tenants Table Migration
 *
 * This migration creates the core tenants table which represents organizations
 * or companies in the SaaS application. It includes fields for identification
 * (code, slug, domain), status management, and JSON-based configuration storage.
 *
 * @package    Database\Migrations
 * @author     Herkobi
 * @copyright  2025 Herkobi
 * @license    MIT
 * @version    1.0.0
 * @since      1.0.0
 */

declare(strict_types=1);

use App\Enums\SubscriptionStatus;
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
        Schema::create('tenants', function (Blueprint $table) {
            $table->ulid('id')->primary();

            $table->string('status')
                  ->default(SubscriptionStatus::ACTIVE->value)
                  ->index();

            $table->string('code')->unique();
            $table->string('slug')->unique();
            $table->string('domain')->nullable()->unique();

            $table->json('account')->nullable();
            $table->json('data')->nullable();

            $table->timestamps();
            $table->softDeletes();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('tenants');
    }
};
