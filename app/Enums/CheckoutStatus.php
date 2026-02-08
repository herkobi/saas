<?php

/**
 * Checkout Status Enum
 *
 * Defines the available statuses for checkout sessions within the system,
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
 * Enum representing checkout session statuses.
 *
 * Tracks the lifecycle of a checkout from pending
 * through completion, expiration, or cancellation.
 */
enum CheckoutStatus: string
{
    case PENDING = 'pending';
    case PROCESSING = 'processing';
    case COMPLETED = 'completed';
    case FAILED = 'failed';
    case EXPIRED = 'expired';
    case CANCELLED = 'cancelled';

    /**
     * Get human-readable label for the checkout status.
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
            self::EXPIRED => 'Süresi Doldu',
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
            self::FAILED => 'destructive',
            self::EXPIRED => 'secondary',
            self::CANCELLED => 'secondary',
        };
    }

    /**
     * Check if the checkout is in a final state.
     *
     * @return bool
     */
    public function isFinal(): bool
    {
        return in_array($this, [
            self::COMPLETED,
            self::FAILED,
            self::EXPIRED,
            self::CANCELLED,
        ]);
    }

    /**
     * Check if the checkout can be processed.
     *
     * @return bool
     */
    public function canProcess(): bool
    {
        return $this === self::PENDING;
    }

    /**
     * Check if the checkout can be cancelled.
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
