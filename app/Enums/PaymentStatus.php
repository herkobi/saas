<?php

/**
 * Payment Status Enum
 *
 * Defines the available statuses for payments within the system,
 * providing type-safe status management with helpers.
 *
 * @package    App\Enums
 * @author     Herkobi
 * @copyright  2025 Herkobi
 * @license    MIT
 * @version    1.0.0
 * @since      1.0.0
 */

declare(strict_types=1);

namespace App\Enums;

/**
 * Enum representing payment statuses.
 *
 * Tracks the lifecycle of a payment from pending
 * through completion or failure states.
 */
enum PaymentStatus: string
{
    case PENDING = 'pending';
    case PROCESSING = 'processing';
    case COMPLETED = 'completed';
    case FAILED = 'failed';
    case REFUNDED = 'refunded';
    case CANCELLED = 'cancelled';

    /**
     * Get human-readable label for the payment status.
     *
     * @return string
     */
    public function label(): string
    {
        return match ($this) {
            self::PENDING => 'Beklemede',
            self::PROCESSING => 'İşleniyor',
            self::COMPLETED => 'Tamamlandı',
            self::FAILED => 'Başarısız',
            self::REFUNDED => 'İade Edildi',
            self::CANCELLED => 'İptal Edildi',
        };
    }

    /**
     * Get badge variant for UI display.
     *
     * @return string
     */
    public function badge(): string
    {
        return match ($this) {
            self::PENDING => 'warning',
            self::PROCESSING => 'info',
            self::COMPLETED => 'success',
            self::FAILED => 'danger',
            self::REFUNDED => 'secondary',
            self::CANCELLED => 'secondary',
        };
    }

    /**
     * Check if the payment is in a final state.
     *
     * @return bool
     */
    public function isFinal(): bool
    {
        return in_array($this, [
            self::COMPLETED,
            self::FAILED,
            self::REFUNDED,
            self::CANCELLED,
        ]);
    }

    /**
     * Check if the payment was successful.
     *
     * @return bool
     */
    public function isSuccessful(): bool
    {
        return $this === self::COMPLETED;
    }

    /**
     * Check if the payment can be refunded.
     *
     * @return bool
     */
    public function canRefund(): bool
    {
        return $this === self::COMPLETED;
    }

    /**
     * Check if the payment can be refunded (alias).
     *
     * @return bool
     */
    public function canBeRefunded(): bool
    {
        return $this->canRefund();
    }

    /**
     * Check if the payment can be cancelled.
     *
     * @return bool
     */
    public function canCancel(): bool
    {
        return in_array($this, [
            self::PENDING,
            self::PROCESSING,
        ]);
    }

    /**
     * Check if the payment can be cancelled (alias).
     *
     * @return bool
     */
    public function canBeCancelled(): bool
    {
        return $this->canCancel();
    }

    /**
     * Get all available options for forms.
     *
     * @return array<string, string>
     */
    public static function options(): array
    {
        return collect(self::cases())
            ->mapWithKeys(fn($case) => [$case->value => $case->label()])
            ->toArray();
    }
}
