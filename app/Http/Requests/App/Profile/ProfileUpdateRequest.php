<?php

/**
 * Tenant Profile Update Request
 *
 * This form request handles validation for tenant user profile update operations.
 * It validates name field for profile updates.
 *
 * @package    App\Http\Requests\App\Profile
 * @author     Herkobi
 * @copyright  2025 Herkobi
 * @license    MIT
 * @version    1.0.0
 * @since      1.0.0
 */

declare(strict_types=1);

namespace App\Http\Requests\App\Profile;

use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;

/**
 * Form request for tenant profile update validation.
 *
 * Validates profile update data including name field.
 * Email is read-only and cannot be changed.
 */
class ProfileUpdateRequest extends FormRequest
{
    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'name' => ['required', 'string', 'max:255'],
        ];
    }

    /**
     * Get custom messages for validator errors.
     *
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'name.required' => 'Ad Soyad alanı zorunludur.',
            'name.string' => 'Ad Soyad metin formatında olmalıdır.',
            'name.max' => 'Ad Soyad en fazla 255 karakter olabilir.',
        ];
    }
}
