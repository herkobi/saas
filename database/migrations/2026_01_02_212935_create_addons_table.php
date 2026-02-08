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
        Schema::create('addons', function (Blueprint $table) {
            $table->ulid('id')->primary();
            $table->string('name');
            $table->string('slug')->unique();
            $table->text('description')->nullable();

            $table->ulid('feature_id');
            $table->foreign('feature_id')->references('id')->on('features')->cascadeOnDelete();

            $table->string('addon_type');
            $table->string('value')->nullable();

            $table->decimal('price', 10, 2);
            $table->string('currency', 3)->default(config('herkobi.payment.currency'));

            $table->boolean('is_recurring')->default(false);
            $table->string('interval')->nullable(); // day, month, year
            $table->integer('interval_count')->nullable();

            $table->boolean('is_active')->default(true);
            $table->boolean('is_public')->default(true);

            $table->timestamps();
            $table->softDeletes();

            $table->index(['feature_id', 'is_active']);
            $table->index(['is_public', 'is_active']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('addons');
    }
};
