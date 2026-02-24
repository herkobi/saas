<?php

declare(strict_types=1);

namespace App\Http\Requests\Panel\User;

use App\Enums\UserStatus;
use App\Enums\UserType;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UserFilterRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    /**
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        return [
            'search' => ['nullable', 'string', 'max:255'],
            'status' => ['nullable', Rule::enum(UserStatus::class)],
            'user_type' => ['nullable', Rule::enum(UserType::class)],
            'created_from' => ['nullable', 'date', 'before_or_equal:created_to'],
            'created_to' => ['nullable', 'date', 'after_or_equal:created_from'],
            'sort_field' => ['nullable', 'string', Rule::in(['name', 'email', 'created_at', 'last_login_at'])],
            'sort_direction' => ['nullable', 'string', Rule::in(['asc', 'desc'])],
            'per_page' => ['nullable', 'integer', 'min:5', 'max:100'],
        ];
    }

    /**
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'search.max' => 'Arama terimi en fazla 255 karakter olabilir.',
            'status.enum' => 'Geçersiz durum değeri.',
            'user_type.enum' => 'Geçersiz kullanıcı tipi.',
            'created_from.date' => 'Geçersiz başlangıç tarihi.',
            'created_to.date' => 'Geçersiz bitiş tarihi.',
        ];
    }
}
