<?php

/**
 * Change Subscription Plan Request
 *
 * This request handles validation for changing a tenant's subscription
 * plan in the panel context.
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
 * Form request for subscription plan change.
 *
 * Validates new plan price selection and immediate application flag
 * for plan change operations.
 */
class ChangeSubscriptionPlanRequest extends FormRequest
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
            'immediate' => ['nullable', 'boolean'],
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
            'plan_price_id' => 'yeni plan fiyatı',
            'immediate' => 'hemen uygula',
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
            'plan_price_id.required' => 'Lütfen yeni bir plan seçin.',
            'plan_price_id.exists' => 'Seçilen plan bulunamadı.',
        ];
    }
}
