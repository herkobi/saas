<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('payments', function (Blueprint $table) {
            $table->ulid('id')->primary();
            $table->foreignUlid('tenant_id')->constrained()->restrictOnDelete();
            $table->foreignUlid('subscription_id')->nullable()->constrained()->nullOnDelete();
            $table->foreignUlid('addon_id')->nullable()->constrained()->nullOnDelete();
            $table->string('gateway')->default(config('herkobi.payment.gateway'));
            $table->string('gateway_payment_id')->nullable();
            $table->decimal('amount', 12, 2);
            $table->string('currency', 3)->default(config('herkobi.payment.currency'));
            $table->string('status')->default('pending');
            $table->string('description')->nullable();
            $table->json('gateway_response')->nullable();
            $table->json('metadata')->nullable();
            $table->timestamp('paid_at')->nullable();
            $table->timestamp('refunded_at')->nullable();
            $table->timestamp('invoiced_at')->nullable();
            $table->timestamps();
            $table->softDeletes();

            $table->index('paid_at');
            $table->index(['tenant_id', 'status', 'paid_at']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('payments');
    }
};
