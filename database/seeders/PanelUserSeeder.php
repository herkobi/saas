<?php

/**
 * Panel User Seeder
 *
 * Seeds the database with the initial Platform Administrator account.
 * This user has full access to the management panel and system configuration.
 *
 * @package    Database\Seeders
 * @author     Herkobi
 * @copyright  2025 Herkobi
 * @license    MIT
 * @version    1.0.0
 * @since      1.0.0
 */

declare(strict_types=1);

namespace Database\Seeders;

use App\Enums\UserType;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class PanelUserSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * Creates the default admin user if it doesn't exist.
     *
     * @return void
     */
    public function run(): void
    {
        // Create or update the default platform admin
        User::updateOrCreate(
            ['email' => 'admin@admin.com'],
            [
                'name' => 'Panel Admin',
                'email' => 'admin@admin.com',
                'password' => Hash::make('password'), // Default password, should be changed in prod
                'user_type' => UserType::ADMIN,
                'email_verified_at' => now(),
            ]
        );
    }
}
