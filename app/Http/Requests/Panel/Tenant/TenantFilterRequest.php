<?php

/**
 * Tenant Filter Request
 *
 * This request handles validation for tenant listing filter parameters
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

use App\Enums\SubscriptionStatus;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

/**
 * Form request for tenant listing filters.
 *
 * Validates search, status, plan, date range, and sorting parameters
 * for tenant listing operations.
 */
class TenantFilterRequest extends FormRequest
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
            'status' => ['nullable', Rule::enum(SubscriptionStatus::class)],
            'subscription_status' => ['nullable', Rule::enum(SubscriptionStatus::class)],
            'plan_id' => ['nullable', 'string', 'exists:plans,id'],
            'created_from' => ['nullable', 'date', 'before_or_equal:created_to'],
            'created_to' => ['nullable', 'date', 'after_or_equal:created_from'],
            'sort_field' => ['nullable', 'string', Rule::in(['code', 'slug', 'created_at', 'updated_at'])],
            'sort_direction' => ['nullable', 'string', Rule::in(['asc', 'desc'])],
            'per_page' => ['nullable', 'integer', 'min:5', 'max:100'],
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
            'search' => 'arama',
            'status' => 'durum',
            'subscription_status' => 'abonelik durumu',
            'plan_id' => 'plan',
            'created_from' => 'başlangıç tarihi',
            'created_to' => 'bitiş tarihi',
            'sort_field' => 'sıralama alanı',
            'sort_direction' => 'sıralama yönü',
            'per_page' => 'sayfa başına kayıt',
        ];
    }
}
