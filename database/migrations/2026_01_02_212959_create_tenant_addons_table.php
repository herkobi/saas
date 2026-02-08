<?php

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
        Schema::create('tenant_addons', function (Blueprint $table) {
            $table->ulid('id')->primary();

            $table->ulid('tenant_id');
            $table->foreign('tenant_id')->references('id')->on('tenants')->cascadeOnDelete();

            $table->ulid('addon_id');
            $table->foreign('addon_id')->references('id')->on('addons')->cascadeOnDelete();

            $table->integer('quantity')->default(1);

            $table->decimal('custom_price', 10, 2)->nullable();
            $table->string('custom_currency', 3)->nullable();

            $table->timestamp('started_at')->nullable();
            $table->timestamp('expires_at')->nullable();

            $table->boolean('is_active')->default(true);
            $table->json('metadata')->nullable();

            $table->timestamps();

            $table->unique(['tenant_id', 'addon_id']);
            $table->index(['tenant_id', 'is_active']);
            $table->index(['addon_id', 'is_active']);
            $table->index('expires_at');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('tenant_addons');
    }
};
