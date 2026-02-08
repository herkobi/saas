<?php

/**
 * Sync Tenant Features Request
 *
 * This request handles validation for syncing tenant feature overrides
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

use Illuminate\Foundation\Http\FormRequest;

/**
 * Form request for tenant feature override synchronization.
 *
 * Validates feature ID and value pairs for bulk override operations.
 */
class SyncTenantFeaturesRequest extends FormRequest
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
        return [
            'overrides' => ['required', 'array'],
            'overrides.*.feature_id' => ['required', 'string', 'exists:features,id'],
            'overrides.*.value' => ['required', 'string', 'max:255'],
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
            'overrides' => 'özellik değerleri',
            'overrides.*.feature_id' => 'özellik',
            'overrides.*.value' => 'değer',
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
            'overrides.required' => 'En az bir özellik değeri belirtmelisiniz.',
            'overrides.*.feature_id.required' => 'Özellik seçimi zorunludur.',
            'overrides.*.feature_id.exists' => 'Seçilen özellik bulunamadı.',
            'overrides.*.value.required' => 'Özellik değeri zorunludur.',
            'overrides.*.value.max' => 'Özellik değeri en fazla 255 karakter olabilir.',
        ];
    }
}
