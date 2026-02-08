<?php

/**
 * Store Plan Price Request
 *
 * Validates data for creating a new plan price.
 * Ensures all required pricing fields including interval,
 * currency, and trial period are properly validated.
 *
 * @package    App\Http\Requests\Panel\Plan
 * @author     Herkobi
 * @copyright  2025 Herkobi
 * @license    MIT
 * @version    1.0.0
 * @since      1.0.0
 */

declare(strict_types=1);

namespace App\Http\Requests\Panel\Plan;

use App\Enums\PlanInterval;
use App\Helpers\CurrencyHelper;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

/**
 * Store Plan Price Request
 *
 * Handles validation for plan price creation operations.
 */
class StorePlanPriceRequest extends FormRequest
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
            'price' => ['required', 'numeric', 'min:0'],
            'currency' => ['required', 'string', 'size:3', 'in:' . CurrencyHelper::defaultCode()],
            'interval' => ['required', Rule::enum(PlanInterval::class)],
            'interval_count' => ['required', 'integer', 'min:1', 'max:12'],
            'trial_days' => ['nullable', 'integer', 'min:0', 'max:365'],
        ];
    }

    /**
     * Get the error messages for the defined validation rules.
     *
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'price.required' => 'Fiyat zorunludur.',
            'price.numeric' => 'Fiyat sayısal olmalıdır.',
            'price.min' => 'Fiyat negatif olamaz.',

            'currency.required' => 'Para birimi zorunludur.',
            'currency.string' => 'Para birimi geçerli bir kod olmalıdır.',
            'currency.size' => 'Para birimi 3 karakterli kod olmalıdır.',
            'currency.in' => 'Sadece ' . CurrencyHelper::defaultCode() . ' para birimi kullanılabilir.',

            'interval.required' => 'Faturalandırma periyodu zorunludur.',
            'interval.enum' => 'Geçersiz faturalandırma periyodu.',

            'interval_count.required' => 'Dönem sayısı zorunludur.',
            'interval_count.integer' => 'Dönem sayısı tam sayı olmalıdır.',
            'interval_count.min' => 'Dönem sayısı en az 1 olmalıdır.',
            'interval_count.max' => 'Dönem sayısı en fazla 12 olabilir.',

            'trial_days.integer' => 'Deneme süresi tam sayı olmalıdır.',
            'trial_days.min' => 'Deneme süresi negatif olamaz.',
            'trial_days.max' => 'Deneme süresi en fazla 365 gün olabilir.',
        ];
    }
}
