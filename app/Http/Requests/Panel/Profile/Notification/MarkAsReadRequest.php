<?php

/**
 * Panel Mark As Read Request
 *
 * This request validates the data for marking a panel notification as read.
 *
 * @package    App\Http\Requests\Panel\Profile\Notification
 * @author     Herkobi
 * @copyright  2025 Herkobi
 * @license    MIT
 * @version    1.0.0
 * @since      1.0.0
 */

declare(strict_types=1);

namespace App\Http\Requests\Panel\Profile\Notification;

use Illuminate\Foundation\Http\FormRequest;

/**
 * Request for marking a panel notification as read.
 *
 * Validates notification ID for marking as read operations.
 */
class MarkAsReadRequest extends FormRequest
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
            'notification_id' => ['required', 'uuid', 'exists:notifications,id'],
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
            'notification_id' => 'bildirim',
        ];
    }
}
