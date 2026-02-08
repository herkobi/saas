<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('plan_features', function (Blueprint $table) {
            $table->foreignUlid('plan_id')
                  ->constrained('plans')
                  ->cascadeOnDelete();

            $table->foreignUlid('feature_id')
                  ->constrained('features')
                  ->cascadeOnDelete();

            $table->string('value')->nullable();
            $table->timestamps();

            $table->primary(['plan_id', 'feature_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('plan_features');
    }
};
