<?php

/**
 * Change Plan Request
 *
 * Validates data for changing subscription plan.
 *
 * @package    App\Http\Requests\App\Account
 * @author     Herkobi
 * @copyright  2025 Herkobi
 * @license    MIT
 * @version    1.0.0
 * @since      1.0.0
 */

declare(strict_types=1);

namespace App\Http\Requests\App\Account;

use Illuminate\Foundation\Http\FormRequest;

/**
 * Request validation for plan change operations.
 *
 * Handles validation of new plan price selection.
 */
class ChangePlanRequest extends FormRequest
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
            'plan_price_id' => ['required', 'string', 'exists:plan_prices,id'],
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
            'plan_price_id.required' => 'Plan seçimi zorunludur.',
            'plan_price_id.exists' => 'Seçilen plan bulunamadı.',
        ];
    }
}
