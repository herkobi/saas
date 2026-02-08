<?php

/**
 * Subscription Filter Request
 *
 * This request handles validation for subscription filtering
 * operations in the panel.
 *
 * @package    App\Http\Requests\Panel\Subscription
 * @author     Herkobi
 * @copyright  2025 Herkobi
 * @license    MIT
 * @version    1.0.0
 * @since      1.0.0
 */

declare(strict_types=1);

namespace App\Http\Requests\Panel\Subscription;

use App\Enums\SubscriptionStatus;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

/**
 * Form request for validating subscription filter parameters.
 *
 * Validates search, status, plan, date range, and sorting parameters
 * for subscription listing operations.
 */
class SubscriptionFilterRequest extends FormRequest
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
            'search' => ['nullable', 'string', 'max:255'],
            'status' => ['nullable', 'string', Rule::in(array_column(SubscriptionStatus::cases(), 'value'))],
            'plan_id' => ['nullable', 'string', 'exists:plans,id'],
            'tenant_id' => ['nullable', 'string', 'exists:tenants,id'],
            'trial' => ['nullable', 'string', Rule::in(['active', 'ended'])],
            'date_from' => ['nullable', 'date'],
            'date_to' => ['nullable', 'date', 'after_or_equal:date_from'],
            'expires_soon' => ['nullable', 'integer', 'min:1', 'max:365'],
            'sort' => ['nullable', 'string', Rule::in(['created_at', 'starts_at', 'ends_at', 'trial_ends_at'])],
            'direction' => ['nullable', 'string', Rule::in(['asc', 'desc'])],
            'per_page' => ['nullable', 'integer', 'min:5', 'max:100'],
        ];
    }

    /**
     * Get custom attribute names for validation errors.
     *
     * @return array<string, string>
     */
    public function attributes(): array
    {
        return [
            'search' => 'arama',
            'status' => 'durum',
            'plan_id' => 'plan',
            'tenant_id' => 'hesap',
            'trial' => 'deneme',
            'date_from' => 'başlangıç tarihi',
            'date_to' => 'bitiş tarihi',
            'expires_soon' => 'yakında sona erecek',
            'sort' => 'sıralama',
            'direction' => 'yön',
            'per_page' => 'sayfa başına',
        ];
    }
}
