<?php

declare(strict_types=1);

namespace App\Enums;

enum CheckoutType: string
{
    case NEW = 'new';
    case RENEW = 'renew';
    case UPGRADE = 'upgrade';
    case DOWNGRADE = 'downgrade';
    case ADDON = 'addon';
    case ADDON_RENEW = 'addon_renew';

    public function label(): string
    {
        return match ($this) {
            self::NEW => 'Yeni Abonelik',
            self::RENEW => 'Abonelik Yenileme',
            self::UPGRADE => 'Plan Yükseltme',
            self::DOWNGRADE => 'Plan Düşürme',
            self::ADDON => 'Eklenti Satın Alma',
            self::ADDON_RENEW => 'Eklenti Yenileme',
        };
    }

    public function badge(): string
    {
        return match ($this) {
            self::NEW => 'success',
            self::RENEW => 'info',
            self::UPGRADE => 'primary',
            self::DOWNGRADE => 'warning',
            self::ADDON => 'secondary',
            self::ADDON_RENEW => 'secondary',
        };
    }

    public static function options(): array
    {
        return collect(self::cases())
            ->mapWithKeys(fn($case) => [$case->value => $case->label()])
            ->toArray();
    }
}
