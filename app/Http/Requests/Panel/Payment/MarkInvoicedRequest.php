<?php

/**
 * Mark Invoiced Request
 *
 * Validates data for marking multiple payments as invoiced.
 *
 * @package    App\Http\Requests\Panel\Payment
 * @author     Herkobi
 * @copyright  2025 Herkobi
 * @license    MIT
 * @version    1.0.0
 * @since      1.0.0
 */

declare(strict_types=1);

namespace App\Http\Requests\Panel\Payment;

use Illuminate\Foundation\Http\FormRequest;

/**
 * Request validation for bulk invoice marking.
 *
 * Handles validation of payment IDs array.
 */
class MarkInvoicedRequest extends FormRequest
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
            'payment_ids' => ['required', 'array', 'min:1'],
            'payment_ids.*' => ['required', 'ulid', 'exists:payments,id'],
        ];
    }
}
