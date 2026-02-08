<?php

declare(strict_types=1);

namespace App\Http\Requests\Panel\Plan;

use Illuminate\Foundation\Http\FormRequest;

class SyncPlanFeaturesRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'features' => ['required', 'array'],
            'features.*.feature_id' => ['required', 'exists:features,id', 'distinct'],
            'features.*.value' => ['nullable', 'string', 'max:500'],
        ];
    }

    public function messages(): array
    {
        return [
            'features.required' => 'Özellik listesi zorunludur.',
            'features.array' => 'Özellik listesi geçerli bir dizi olmalıdır.',

            'features.*.feature_id.required' => 'Özellik seçimi zorunludur.',
            'features.*.feature_id.exists' => 'Seçilen özellik sistemde mevcut değil.',

            'features.*.value.string' => 'Özellik değeri metin olmalıdır.',
            'features.*.value.max' => 'Özellik değeri 500 karakterden uzun olamaz.',
        ];
    }
}
