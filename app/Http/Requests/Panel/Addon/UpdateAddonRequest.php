<?php

declare(strict_types=1);

namespace App\Http\Requests\Panel\Addon;

use App\Enums\AddonType;
use App\Enums\PlanInterval;
use App\Helpers\CurrencyHelper;
use App\Models\Feature;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Validator;

class UpdateAddonRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        $currency = CurrencyHelper::defaultCode();
        $addonId = $this->route('addon')->id;

        return [
            'name' => ['required', 'string', 'max:255'],
            'slug' => ['required', 'string', 'max:255', Rule::unique('addons', 'slug')->ignore($addonId)],
            'description' => ['nullable', 'string'],

            'feature_id' => ['required', 'ulid', 'exists:features,id'],
            'addon_type' => ['required', Rule::enum(AddonType::class)],
            'value' => ['nullable', 'string', 'max:500'],

            'price' => ['required', 'numeric', 'min:0'],
            'currency' => ['required', 'string', 'size:3', Rule::in([$currency])],

            'is_recurring' => ['boolean'],
            'interval' => ['nullable', 'required_if:is_recurring,1', Rule::enum(PlanInterval::class)],
            'interval_count' => ['nullable', 'required_if:is_recurring,1', 'integer', 'min:1'],

            'is_active' => ['boolean'],
            'is_public' => ['boolean'],
        ];
    }

    public function withValidator(Validator $validator): void
    {
        $validator->after(function (Validator $validator) {
            $featureId = $this->input('feature_id');
            $addonTypeValue = $this->input('addon_type');
            $value = $this->input('value');

            if (! $featureId || ! $addonTypeValue) {
                return;
            }

            $feature = Feature::query()->select(['id', 'type'])->find($featureId);
            if (! $feature) {
                return;
            }

            $allowedAddonTypeValues = array_map(
                fn ($t) => $t->value,
                $feature->type->allowedAddonTypes()
            );

            if (! in_array($addonTypeValue, $allowedAddonTypeValues, true)) {
                $validator->errors()->add('addon_type', 'Seçilen eklenti tipi, bu özelliğin tipi ile uyumlu değil.');
                return;
            }

            if ($addonTypeValue === AddonType::INCREMENT->value) {
                if ($value === null || trim((string)$value) === '') {
                    $validator->errors()->add('value', 'Artırım tipinde değer zorunludur.');
                    return;
                }

                if (! preg_match('/^\d+$/', trim((string)$value))) {
                    $validator->errors()->add('value', 'Artırım değeri pozitif tam sayı olmalıdır.');
                    return;
                }

                if ((int) $value <= 0) {
                    $validator->errors()->add('value', 'Artırım değeri 1 veya daha büyük olmalıdır.');
                }
            }
        });
    }

    public function messages(): array
    {
        return [
            'name.required' => 'Eklenti adı zorunludur.',
            'feature_id.required' => 'Özellik seçimi zorunludur.',
            'addon_type.required' => 'Eklenti tipi zorunludur.',
            'price.required' => 'Fiyat zorunludur.',
            'currency.in' => 'Para birimi sistem ayarları ile uyumlu değil.',
            'interval.required_if' => 'Tekrarlayan ödeme seçildiyse periyot zorunludur.',
            'interval_count.required_if' => 'Tekrarlayan ödeme seçildiyse çarpan zorunludur.',
        ];
    }
}
