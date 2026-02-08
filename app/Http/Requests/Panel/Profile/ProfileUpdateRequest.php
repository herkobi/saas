<?php

/**
 * Panel Profile Update Request
 *
 * This form request handles validation for panel user profile update operations.
 * It validates name field for profile updates.
 *
 * @package    App\Http\Requests\Panel\Profile
 * @author     Herkobi
 * @copyright  2025 Herkobi
 * @license    MIT
 * @version    1.0.0
 * @since      1.0.0
 */

declare(strict_types=1);

namespace App\Http\Requests\Panel\Profile;

use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;

/**
 * Form request for panel profile update validation.
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
            'title' => ['nullable', 'string', 'max:100'],
            'bio' => ['nullable', 'string', 'max:500'],
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
            'title.max' => 'Ünvan en fazla 100 karakter olabilir.',
            'bio.max' => 'Biyografi en fazla 500 karakter olabilir.',
        ];
    }
}
