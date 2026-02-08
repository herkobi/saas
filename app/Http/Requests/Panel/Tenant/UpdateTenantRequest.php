<?php

/**
 * Update Tenant Request
 *
 * This request handles validation for tenant update operations
 * in the panel context.
 *
 * @package    App\Http\Requests\Panel\Tenant
 * @author     Herkobi
 * @copyright  2025 Herkobi
 * @license    MIT
 * @version    1.0.0
 * @since      1.0.0
 */

declare(strict_types=1);

namespace App\Http\Requests\Panel\Tenant;

use App\Enums\SubscriptionStatus;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

/**
 * Form request for tenant update operations.
 *
 * Validates tenant information including code, slug, domain,
 * account details, and status.
 */
class UpdateTenantRequest extends FormRequest
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
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        $tenantId = $this->route('tenant')?->id;

        return [
            'code' => [
                'sometimes',
                'required',
                'string',
                'max:50',
                Rule::unique('tenants', 'code')->ignore($tenantId),
            ],
            'slug' => [
                'sometimes',
                'required',
                'string',
                'max:100',
                'regex:/^[a-z0-9-]+$/',
                Rule::unique('tenants', 'slug')->ignore($tenantId),
            ],
            'domain' => [
                'nullable',
                'string',
                'max:255',
                Rule::unique('tenants', 'domain')->ignore($tenantId),
            ],
            'status' => ['sometimes', 'required', Rule::enum(SubscriptionStatus::class)],
            'account' => ['nullable', 'array'],
            'account.name' => ['nullable', 'string', 'max:255'],
            'account.email' => ['nullable', 'email', 'max:255'],
            'account.phone' => ['nullable', 'string', 'max:50'],
            'account.address' => ['nullable', 'string', 'max:500'],
            'account.city' => ['nullable', 'string', 'max:100'],
            'account.state' => ['nullable', 'string', 'max:100'],
            'account.country' => ['nullable', 'string', 'max:100'],
            'account.postal_code' => ['nullable', 'string', 'max:20'],
            'account.tax_id' => ['nullable', 'string', 'max:50'],
            'account.tax_office' => ['nullable', 'string', 'max:100'],
            'data' => ['nullable', 'array'],
        ];
    }

    /**
     * Get custom attributes for validator errors.
     *
     * @return array<string, string>
     */
    public function attributes(): array
    {
        return [
            'code' => 'tenant kodu',
            'slug' => 'tenant slug',
            'domain' => 'domain',
            'status' => 'durum',
            'account' => 'hesap bilgileri',
            'account.name' => 'firma adı',
            'account.email' => 'e-posta',
            'account.phone' => 'telefon',
            'account.address' => 'adres',
            'account.city' => 'şehir',
            'account.state' => 'ilçe',
            'account.country' => 'ülke',
            'account.postal_code' => 'posta kodu',
            'account.tax_id' => 'vergi numarası',
            'account.tax_office' => 'vergi dairesi',
            'data' => 'ek veriler',
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
            'slug.regex' => 'Slug sadece küçük harf, rakam ve tire içerebilir.',
            'code.unique' => 'Bu tenant kodu zaten kullanılıyor.',
            'slug.unique' => 'Bu slug zaten kullanılıyor.',
            'domain.unique' => 'Bu domain zaten kullanılıyor.',
        ];
    }
}
