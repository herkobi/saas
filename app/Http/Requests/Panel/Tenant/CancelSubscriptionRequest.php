<?php

/**
 * Cancel Subscription Request
 *
 * This request handles validation for cancelling a tenant's subscription
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
 * Form request for subscription cancellation.
 *
 * Validates the immediate cancellation flag for subscription
 * cancel operations.
 */
class CancelSubscriptionRequest extends FormRequest
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
            'immediate' => ['nullable', 'boolean'],
            'reason' => ['nullable', 'string', 'max:500'],
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
            'immediate' => 'hemen iptal et',
            'reason' => 'iptal nedeni',
        ];
    }
}
