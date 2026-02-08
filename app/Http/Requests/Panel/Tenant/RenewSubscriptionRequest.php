<?php

/**
 * Renew Subscription Request
 *
 * This request handles validation for renewing a tenant's subscription
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
 * Form request for subscription renewal.
 *
 * Validates optional parameters for subscription renewal operations
 * such as extending trial or grace periods.
 */
class RenewSubscriptionRequest extends FormRequest
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
            'extend_trial_days' => ['nullable', 'integer', 'min:1', 'max:365'],
            'extend_grace_days' => ['nullable', 'integer', 'min:1', 'max:90'],
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
            'extend_trial_days' => 'deneme süresi uzatma',
            'extend_grace_days' => 'ek süre uzatma',
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
            'extend_trial_days.min' => 'Deneme süresi en az 1 gün olmalıdır.',
            'extend_trial_days.max' => 'Deneme süresi en fazla 365 gün olabilir.',
            'extend_grace_days.min' => 'Ek süre en az 1 gün olmalıdır.',
            'extend_grace_days.max' => 'Ek süre en fazla 90 gün olabilir.',
        ];
    }
}
