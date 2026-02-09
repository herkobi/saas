<?php

/**
 * Extend Trial Request
 *
 * This form request handles validation for extending a tenant's
 * subscription trial period in the panel context.
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

use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;

/**
 * Form request for trial period extension.
 *
 * Validates the number of days to extend a tenant's trial period.
 */
class ExtendTrialRequest extends FormRequest
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
     * @return array<string, ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'days' => ['required', 'integer', 'min:1', 'max:365'],
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
            'days.required' => 'Gün sayısı alanı zorunludur.',
            'days.integer' => 'Gün sayısı bir tam sayı olmalıdır.',
            'days.min' => 'Deneme süresi en az 1 gün olmalıdır.',
            'days.max' => 'Deneme süresi en fazla 365 gün olabilir.',
        ];
    }
}
