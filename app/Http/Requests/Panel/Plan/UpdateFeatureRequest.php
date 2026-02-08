<?php

/**
 * Update Feature Request
 *
 * Validates data for updating an existing feature.
 * Ensures unique code constraint excludes current feature.
 *
 * @package    App\Http\Requests\Panel\Plan
 * @author     Herkobi
 * @copyright  2025 Herkobi
 * @license    MIT
 * @version    1.0.0
 * @since      1.0.0
 */

declare(strict_types=1);

namespace App\Http\Requests\Panel\Plan;

use App\Enums\FeatureType;
use App\Enums\ResetPeriod;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

/**
 * Update Feature Request
 *
 * Handles validation for feature update operations.
 */
class UpdateFeatureRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    /**
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        $featureId = $this->route('feature')->id;

        return [
            'name' => ['required', 'string', 'max:150'],
            'code' => [
                'required',
                'string',
                'max:150',
                Rule::unique('features', 'code')->ignore($featureId),
            ],
            'description' => ['nullable', 'string', 'max:500'],

            'type' => ['required', Rule::enum(FeatureType::class)],

            // Serbest kalsın (ama bazı tiplerde zorunlu)
            'unit' => ['nullable', 'string', 'max:50'],

            // Artık enum
            'reset_period' => ['nullable', Rule::enum(ResetPeriod::class)],

            'is_active' => ['boolean'],
        ];
    }

    public function withValidator($validator): void
    {
        // LIMIT veya METERED ise unit zorunlu
        $validator->sometimes('unit', ['required'], function ($input) {
            return in_array($input->type ?? null, [
                FeatureType::LIMIT->value,
                FeatureType::METERED->value,
            ], true);
        });

        // METERED ise reset_period zorunlu
        $validator->sometimes('reset_period', ['required'], function ($input) {
            return ($input->type ?? null) === FeatureType::METERED->value;
        });
    }

    /**
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'name.required' => 'Özellik adı zorunludur.',
            'name.max' => 'Özellik adı 150 karakteri geçemez.',

            'code.required' => 'Kod alanı zorunludur.',
            'code.unique' => 'Bu kod zaten kullanımda.',
            'code.max' => 'Kod en fazla 150 karakter olabilir.',

            'description.max' => 'Açıklama en fazla 500 karakter olabilir.',

            'type.required' => 'Tip seçilmelidir.',
            'type.enum' => 'Geçersiz özellik tipi.',

            'unit.required' => 'Limit veya sayaçlı tip için birim zorunludur.',
            'unit.max' => 'Birim en fazla 50 karakter olabilir.',

            'reset_period.required' => 'Sayaçlı tip için sıfırlama periyodu zorunludur.',
            'reset_period.enum' => 'Geçersiz sıfırlama periyodu seçimi.',

            'is_active.boolean' => 'Aktiflik bilgisi doğru formatta olmalıdır.',
        ];
    }
}
