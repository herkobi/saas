<?php

/**
 * Create Subscription Request
 *
 * This request handles validation for creating a new subscription
 * for a tenant in the panel context.
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
 * Form request for subscription creation.
 *
 * Validates plan price selection and optional trial days
 * for new subscription creation.
 */
class CreateSubscriptionRequest extends FormRequest
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
            'trial_days' => ['nullable', 'integer', 'min:0', 'max:365'],
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
            'plan_price_id' => 'plan fiyatı',
            'trial_days' => 'deneme süresi',
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
            'plan_price_id.required' => 'Lütfen bir plan seçin.',
            'plan_price_id.exists' => 'Seçilen plan bulunamadı.',
            'trial_days.min' => 'Deneme süresi 0 günden az olamaz.',
            'trial_days.max' => 'Deneme süresi 365 günden fazla olamaz.',
        ];
    }
}
