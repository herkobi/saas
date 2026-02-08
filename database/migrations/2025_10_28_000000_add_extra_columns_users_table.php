<?php

/**
 * Add Extra Columns to Users Table Migration
 *
 * @package    Database\Migrations
 * @author     Herkobi
 * @copyright  2025 Herkobi
 * @license    MIT
 * @version    1.0.0
 * @since      1.0.0
 */

declare(strict_types=1);

use App\Enums\UserStatus;
use App\Enums\UserType;
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
        Schema::table('users', function (Blueprint $table) {
            $table->tinyInteger('status')->default(UserStatus::ACTIVE->value)->after('email');
            $table->string('user_type')->default(UserType::TENANT->value)->after('status');
            $table->string('title')->nullable()->after('user_type');
            $table->text('bio')->nullable()->after('title');
            $table->timestamp('last_login_at')->nullable()->after('bio');
            $table->softDeletes()->after('last_login_at');
            $table->timestamp('anonymized_at')->nullable()->after('deleted_at');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn([
                'status',
                'user_type',
                'title',
                'bio',
                'last_login_at',
                'anonymized_at',
            ]);
            $table->dropSoftDeletes();
        });
    }
};
