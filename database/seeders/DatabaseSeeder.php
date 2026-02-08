<?php

/**
 * Database Seeder
 *
 * The main entry point for database seeding.
 * Calls individual seeders to populate the database with initial data
 * for users, tenants, and system settings.
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

use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     *
     * @return void
     */
    public function run(): void
    {
        // System / panel
        $this->call(SettingSeeder::class);
        $this->call(PanelUserSeeder::class);

        // Catalog
        $this->call(FeatureSeeder::class);
        $this->call(PlanSeeder::class);
        $this->call(AddonSeeder::class);

        // Tenants (>=12) + subscription scenarios + initial payments
        $this->call(TenantOwnerSeeder::class);

        // Staff users (plan limitlerine göre kalabalık)
        $this->call(TenantUserSeeder::class);

        // Billing / overrides / addons
        $this->call(PaymentSeeder::class);
        $this->call(TenantAddonSeeder::class);
        $this->call(TenantFeatureSeeder::class);

        // Usage (tüm LIMIT/METERED feature’lar)
        $this->call(TenantUsageSeeder::class);

        // Activity logs (listener formatına uygun)
        $this->call(ActivitySeeder::class);
    }
}
