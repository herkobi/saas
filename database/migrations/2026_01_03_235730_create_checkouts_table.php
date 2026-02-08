<?php

/**
 * Create Checkouts Table Migration
 *
 * Creates the checkouts table for storing checkout sessions
 * linked to tenants and plan prices.
 *
 * @package    Database\Migrations
 * @author     Herkobi
 * @copyright  2025 Herkobi
 * @license    MIT
 * @version    1.0.0
 * @since      1.0.0
 */

declare(strict_types=1);

use App\Enums\CheckoutStatus;
use App\Enums\CheckoutType;
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
        Schema::create('checkouts', function (Blueprint $table) {
            $table->ulid('id')->primary();
            $table->foreignUlid('tenant_id')->constrained()->cascadeOnDelete();
            $table->foreignUlid('plan_price_id')->nullable()->constrained()->restrictOnDelete();
            $table->foreignUlid('addon_id')->nullable()->constrained()->cascadeOnDelete();
            $table->integer('quantity')->default(1);
            $table->foreignUlid('payment_id')->nullable()->constrained()->nullOnDelete();
            $table->string('merchant_oid')->unique();
            $table->string('type')->default(CheckoutType::NEW->value);
            $table->string('status')->default(CheckoutStatus::PENDING->value)->index();
            $table->decimal('amount', 12, 2);
            $table->decimal('proration_credit', 12, 2)->default(0);
            $table->decimal('final_amount', 12, 2);
            $table->string('currency', 3)->default(config('herkobi.payment.currency'));
            $table->string('paytr_token')->nullable();
            $table->json('billing_info')->nullable();
            $table->json('metadata')->nullable();
            $table->timestamp('expires_at')->nullable();
            $table->timestamp('completed_at')->nullable();
            $table->timestamps();
            $table->softDeletes();

            $table->index(['tenant_id', 'status']);
            $table->index('expires_at');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('checkouts');
    }
};
