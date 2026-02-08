<?php

/**
 * Update Setting Request
 *
 * Validates system settings update requests including file uploads.
 *
 * @package    App\Http\Requests\Panel\Settings
 * @author     Herkobi
 * @copyright  2025 Herkobi
 * @license    MIT
 * @version    1.0.0
 * @since      1.0.0
 */

declare(strict_types=1);

namespace App\Http\Requests\Panel\Settings;

use Illuminate\Foundation\Http\FormRequest;

/**
 * Request validation for updating system settings.
 */
class UpdateSettingRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     *
     * @return bool
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, array<int, mixed>>
     */
    public function rules(): array
    {
        return [
            // General Settings
            'site_name' => ['required', 'string', 'max:255'],
            'site_description' => ['nullable', 'string', 'max:500'],
            'logo_light' => ['nullable', 'image', 'mimes:png,jpg,jpeg,svg', 'max:2048'],
            'logo_dark' => ['nullable', 'image', 'mimes:png,jpg,jpeg,svg', 'max:2048'],
            'favicon' => ['nullable', 'image', 'mimes:png,ico,svg', 'max:512'],
            'email' => ['required', 'email', 'max:255'],
            'phone' => ['nullable', 'string', 'max:50'],
            'mail_from_name' => ['required', 'string', 'max:255'],
            'mail_from_address' => ['required', 'email', 'max:255'],

            // Company Settings
            'company_name' => ['nullable', 'string', 'max:255'],
            'company_address' => ['nullable', 'string', 'max:500'],
            'company_district' => ['nullable', 'string', 'max:100'],
            'company_city' => ['nullable', 'string', 'max:100'],
            'company_postcode' => ['nullable', 'string', 'max:10'],
            'tax_number' => ['nullable', 'string', 'max:50'],
            'tax_office' => ['nullable', 'string', 'max:255'],
            'mersis_number' => ['nullable', 'string', 'size:16'],
            'kep_email' => ['nullable', 'email', 'max:255'],
            'invoice_prefix' => ['required', 'string', 'max:10'],
        ];
    }

    /**
     * Get custom validation messages.
     *
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            // Site Name
            'site_name.required' => 'Site adı zorunludur.',
            'site_name.string' => 'Site adı geçerli bir metin olmalıdır.',
            'site_name.max' => 'Site adı 255 karakterden uzun olamaz.',

            // Site Description
            'site_description.string' => 'Açıklama geçerli bir metin olmalıdır.',
            'site_description.max' => 'Açıklama 500 karakteri aşamaz.',

            // Logo Light
            'logo_light.image' => 'Açık tema logosu geçerli bir resim dosyası olmalıdır.',
            'logo_light.mimes' => 'Açık tema logosu PNG, JPG, JPEG veya SVG formatında olmalıdır.',
            'logo_light.max' => 'Açık tema logosu 2MB\'dan büyük olamaz.',

            // Logo Dark
            'logo_dark.image' => 'Koyu tema logosu geçerli bir resim dosyası olmalıdır.',
            'logo_dark.mimes' => 'Koyu tema logosu PNG, JPG, JPEG veya SVG formatında olmalıdır.',
            'logo_dark.max' => 'Koyu tema logosu 2MB\'dan büyük olamaz.',

            // Favicon
            'favicon.image' => 'Favicon geçerli bir resim dosyası olmalıdır.',
            'favicon.mimes' => 'Favicon PNG, ICO veya SVG formatında olmalıdır.',
            'favicon.max' => 'Favicon 512KB\'dan büyük olamaz.',

            // Email
            'email.required' => 'E-posta adresi zorunludur.',
            'email.email' => 'Geçerli bir e-posta adresi giriniz.',
            'email.max' => 'E-posta adresi 255 karakteri aşamaz.',

            // Phone
            'phone.string' => 'Telefon numarası metin olmalıdır.',
            'phone.max' => 'Telefon numarası 50 karakteri aşamaz.',

            // Mail From Name
            'mail_from_name.required' => 'Gönderici adı zorunludur.',
            'mail_from_name.string' => 'Gönderici adı metin olmalıdır.',
            'mail_from_name.max' => 'Gönderici adı 255 karakteri aşamaz.',

            // Mail From Address
            'mail_from_address.required' => 'Gönderici e-posta adresi zorunludur.',
            'mail_from_address.email' => 'Geçerli bir gönderici e-posta adresi giriniz.',
            'mail_from_address.max' => 'Gönderici e-posta adresi 255 karakteri aşamaz.',

            // Company Name
            'company_name.string' => 'Firma adı metin olmalıdır.',
            'company_name.max' => 'Firma adı 255 karakteri aşamaz.',

            // Company Address
            'company_address.string' => 'Adres metin olmalıdır.',
            'company_address.max' => 'Adres 500 karakteri aşamaz.',

            // Company District
            'company_district.string' => 'İlçe metin olmalıdır.',
            'company_district.max' => 'İlçe 100 karakteri aşamaz.',

            // Company City
            'company_city.string' => 'İl metin olmalıdır.',
            'company_city.max' => 'İl 100 karakteri aşamaz.',

            // Company Postcode
            'company_postcode.string' => 'Posta kodu metin olmalıdır.',
            'company_postcode.max' => 'Posta kodu 10 karakteri aşamaz.',

            // Tax Number
            'tax_number.string' => 'Vergi numarası metin olmalıdır.',
            'tax_number.max' => 'Vergi numarası 50 karakteri aşamaz.',

            // Tax Office
            'tax_office.string' => 'Vergi dairesi metin olmalıdır.',
            'tax_office.max' => 'Vergi dairesi 255 karakteri aşamaz.',

            // Mersis Number
            'mersis_number.string' => 'Mersis numarası metin olmalıdır.',
            'mersis_number.size' => 'Mersis numarası 16 karakter olmalıdır.',

            // KEP Email
            'kep_email.email' => 'Geçerli bir KEP e-posta adresi giriniz.',
            'kep_email.max' => 'KEP e-posta adresi 255 karakteri aşamaz.',

            // Invoice Prefix
            'invoice_prefix.required' => 'Fatura ön eki zorunludur.',
            'invoice_prefix.string' => 'Fatura ön eki metin olmalıdır.',
            'invoice_prefix.max' => 'Fatura ön eki 10 karakteri aşamaz.',
        ];
    }
}
