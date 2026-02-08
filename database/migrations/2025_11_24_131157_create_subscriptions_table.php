<?php

declare(strict_types=1);

use App\Enums\SubscriptionStatus;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('subscriptions', function (Blueprint $table) {
            $table->ulid('id')->primary();

            $table->foreignUlid('tenant_id')
                  ->constrained('tenants')
                  ->cascadeOnDelete();

            $table->foreignUlid('plan_price_id')
                  ->constrained('plan_prices')
                  ->restrictOnDelete();

            $table->foreignUlid('next_plan_price_id')
                  ->nullable()
                  ->constrained('plan_prices')
                  ->nullOnDelete();

            $table->string('status')
                  ->default(SubscriptionStatus::ACTIVE->value);

            $table->timestamp('starts_at');
            $table->timestamp('ends_at')->nullable();
            $table->timestamp('trial_ends_at')->nullable();
            $table->timestamp('canceled_at')->nullable();
            $table->timestamp('grace_period_ends_at')->nullable();

            $table->decimal('custom_price', 10, 2)->nullable();
            $table->string('custom_currency', 3)->default(config('herkobi.payment.currency'));

            $table->timestamps();
            $table->softDeletes();

            $table->index('status');
            $table->index(['ends_at', 'canceled_at']);
            $table->index('trial_ends_at');
            $table->index(['tenant_id', 'created_at']);
            $table->index('next_plan_price_id');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('subscriptions');
    }
};
