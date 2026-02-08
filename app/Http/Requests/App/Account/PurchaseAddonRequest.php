<?php

declare(strict_types=1);

namespace App\Http\Requests\App\Account;

use Illuminate\Foundation\Http\FormRequest;

class PurchaseAddonRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'addon_id' => ['required', 'ulid', 'exists:addons,id'],
            'quantity' => ['required', 'integer', 'min:1', 'max:100'],
        ];
    }

    public function messages(): array
    {
        return [
            'addon_id.required' => 'Eklenti seçimi zorunludur.',
            'addon_id.exists' => 'Seçilen eklenti bulunamadı.',
            'quantity.required' => 'Adet belirtilmelidir.',
            'quantity.min' => 'Minimum 1 adet seçilmelidir.',
            'quantity.max' => 'Maksimum 100 adet seçilebilir.',
        ];
    }
}
