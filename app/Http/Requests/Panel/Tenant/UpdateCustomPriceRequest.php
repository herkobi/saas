<?php

declare(strict_types=1);

namespace App\Http\Requests\Panel\Tenant;

use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;

class UpdateCustomPriceRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    /**
     * @return array<string, ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'custom_price' => ['nullable', 'numeric', 'min:0', 'max:999999.99'],
            'custom_currency' => ['nullable', 'string', 'size:3'],
        ];
    }

    /**
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'custom_price.numeric' => 'Fiyat geçerli bir sayı olmalıdır.',
            'custom_price.min' => 'Fiyat 0 veya daha büyük olmalıdır.',
            'custom_price.max' => 'Fiyat en fazla 999.999,99 olabilir.',
            'custom_currency.size' => 'Para birimi 3 karakter olmalıdır.',
        ];
    }
}
