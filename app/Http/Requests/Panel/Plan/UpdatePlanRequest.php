<?php

declare(strict_types=1);

namespace App\Http\Requests\Panel\Plan;

use App\Enums\ProrationType;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdatePlanRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        $planId = $this->route('plan')->id;

        return [
            'name' => ['required', 'string', 'max:255'],
            'slug' => ['nullable', 'string', 'max:255', Rule::unique('plans')->ignore($planId)],
            'description' => ['nullable', 'string', 'max:1000'],
            'tenant_id' => ['nullable', 'ulid', 'exists:tenants,id'],
            'is_free' => ['nullable', 'boolean'],
            'is_active' => ['nullable', 'boolean'],
            'is_public' => ['nullable', 'boolean'],
            'grace_period_days' => ['required', 'integer', 'min:0', 'max:30'],
            'upgrade_proration_type' => ['nullable', Rule::enum(ProrationType::class)],
            'downgrade_proration_type' => ['nullable', Rule::enum(ProrationType::class)],
        ];
    }

    public function messages(): array
    {
        return [
            'name.required' => 'Plan adı zorunludur.',
            'name.string' => 'Plan adı metin olmalıdır.',
            'name.max' => 'Plan adı en fazla 255 karakter olabilir.',

            'slug.string' => 'Slug metin olmalıdır.',
            'slug.max' => 'Slug en fazla 255 karakter olabilir.',
            'slug.unique' => 'Bu slug zaten kullanılıyor.',

            'description.string' => 'Açıklama metin olmalıdır.',
            'description.max' => 'Açıklama en fazla 1000 karakter olabilir.',

            'tenant_id.ulid' => 'Tenant ID geçerli bir ULID olmalıdır.',
            'tenant_id.exists' => 'Seçilen tenant bulunamadı.',

            'is_free.boolean' => 'Ücretsiz plan bilgisi doğru formatta olmalıdır.',

            'is_active.required' => 'Aktiflik bilgisi zorunludur.',
            'is_active.boolean' => 'Aktiflik bilgisi doğru formatta olmalıdır.',

            'is_public.required' => 'Plan yayın durumu zorunludur.',
            'is_public.boolean' => 'Yayın durumu doğru formatta olmalıdır.',

            'grace_period_days.required' => 'Ek ödeme süresi zorunludur.',
            'grace_period_days.integer' => 'Ek ödeme süresi rakam olmalıdır.',
            'grace_period_days.min' => 'Ek ödeme süresi en az 0 gün olmalıdır.',
            'grace_period_days.max' => 'Ek ödeme süresi en fazla 30 gün olabilir.',

            'upgrade_proration_type.enum' => 'Geçersiz yükseltme geçiş tipi.',
            'downgrade_proration_type.enum' => 'Geçersiz düşürme geçiş tipi.',
        ];
    }
}
