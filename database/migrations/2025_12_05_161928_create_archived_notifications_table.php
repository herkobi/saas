<?php

/**
 * Create Archived Notifications Table Migration
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
        Schema::create('archived_notifications', function (Blueprint $table) {
            $table->ulid('id')->primary();

            $table->string('type');

            $table->string('notifiable_type');
            $table->ulid('notifiable_id');
            $table->index(['notifiable_type', 'notifiable_id']);

            $table->ulid('tenant_id')->nullable();
            $table->foreign('tenant_id')
                ->references('id')
                ->on('tenants')
                ->onDelete('cascade');
            $table->index('tenant_id');

            $table->json('data');

            $table->timestamp('read_at')->nullable();

            $table->timestamp('archived_at');
            $table->timestamp('anonymized_at')->nullable();

            $table->index('archived_at');
            $table->index('anonymized_at');

            $table->timestamps();
            $table->index('created_at');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('archived_notifications');
    }
};
