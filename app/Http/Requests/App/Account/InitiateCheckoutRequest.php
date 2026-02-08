<?php

/**
 * Initiate Checkout Request
 *
 * Validates data for initiating a checkout session.
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

use App\Enums\CheckoutType;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

/**
 * Request validation for checkout initiation.
 *
 * Handles validation of plan price, checkout type, and billing information.
 */
class InitiateCheckoutRequest extends FormRequest
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
            'plan_price_id' => ['required_unless:type,addon', 'nullable', 'string', 'exists:plan_prices,id'],
            'addon_id' => ['required_if:type,addon', 'nullable', 'string', 'exists:addons,id'],
            'type' => [
                'required',
                'string',
                Rule::enum(CheckoutType::class),
            ],
            'billing_info' => ['nullable', 'array'],
            'billing_info.company_name' => ['nullable', 'string', 'max:255'],
            'billing_info.tax_number' => ['nullable', 'string', 'max:50'],
            'billing_info.tax_office' => ['nullable', 'string', 'max:255'],
            'billing_info.address' => ['nullable', 'string', 'max:500'],
            'billing_info.city' => ['nullable', 'string', 'max:100'],
            'billing_info.country' => ['nullable', 'string', 'max:100'],
            'billing_info.phone' => ['nullable', 'string', 'max:20'],
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
            'plan_price_id.required_unless' => 'Plan seçimi zorunludur.',
            'plan_price_id.exists' => 'Seçilen plan bulunamadı.',
            'addon_id.required_if' => 'Eklenti seçimi zorunludur.',
            'addon_id.exists' => 'Seçilen eklenti bulunamadı.',
            'type.required' => 'İşlem tipi zorunludur.',
            'type.in' => 'Geçersiz işlem tipi.',
        ];
    }
}
