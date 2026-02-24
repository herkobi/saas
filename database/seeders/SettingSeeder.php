<?php

/**
 * Setting Seeder
 *
 * Seeds the default system settings for initial setup.
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

use App\Models\Setting;
use Illuminate\Database\Seeder;

/**
 * Seeder for default system settings.
 *
 * Creates initial settings for general and company information.
 */
class SettingSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run(): void
    {
        $settings = [
            // General Settings
            [
                'key' => 'site_name',
                'value' => 'Herkobi',
                'type' => 'string',
                'is_public' => true,
            ],
            [
                'key' => 'site_description',
                'value' => null,
                'type' => 'string',
                'is_public' => true,
            ],
            [
                'key' => 'logo_light',
                'value' => null,
                'type' => 'file',
                'is_public' => true,
            ],
            [
                'key' => 'logo_dark',
                'value' => null,
                'type' => 'file',
                'is_public' => true,
            ],
            [
                'key' => 'favicon',
                'value' => null,
                'type' => 'file',
                'is_public' => true,
            ],
            [
                'key' => 'email',
                'value' => 'info@herkobi.com',
                'type' => 'string',
                'is_public' => true,
            ],
            [
                'key' => 'phone',
                'value' => null,
                'type' => 'string',
                'is_public' => true,
            ],
            [
                'key' => 'mail_from_name',
                'value' => 'Herkobi',
                'type' => 'string',
                'is_public' => false,
            ],
            [
                'key' => 'mail_from_address',
                'value' => 'noreply@herkobi.com',
                'type' => 'string',
                'is_public' => false,
            ],

            // Company Settings
            [
                'key' => 'company_name',
                'value' => 'Herkobi Yazılım A.Ş.',
                'type' => 'string',
                'is_public' => false,
            ],
            [
                'key' => 'company_address',
                'value' => 'Levent Mah. Büyükdere Cad. No:123',
                'type' => 'string',
                'is_public' => false,
            ],
            [
                'key' => 'company_district',
                'value' => 'Beşiktaş',
                'type' => 'string',
                'is_public' => false,
            ],
            [
                'key' => 'company_city',
                'value' => 'İstanbul',
                'type' => 'string',
                'is_public' => false,
            ],
            [
                'key' => 'company_postcode',
                'value' => '34330',
                'type' => 'string',
                'is_public' => false,
            ],
            [
                'key' => 'tax_number',
                'value' => '1234567890',
                'type' => 'string',
                'is_public' => false,
            ],
            [
                'key' => 'tax_office',
                'value' => 'Beşiktaş Vergi Dairesi',
                'type' => 'string',
                'is_public' => false,
            ],
            [
                'key' => 'mersis_number',
                'value' => '0123456789012345',
                'type' => 'string',
                'is_public' => false,
            ],
            [
                'key' => 'kep_email',
                'value' => 'herkobi@hs01.kep.tr',
                'type' => 'string',
                'is_public' => false,
            ],
            [
                'key' => 'invoice_prefix',
                'value' => 'INV-',
                'type' => 'string',
                'is_public' => false,
            ],
        ];

        foreach ($settings as $setting) {
            Setting::updateOrCreate(
                ['key' => $setting['key']],
                $setting
            );
        }
    }
}
