<?php

declare(strict_types=1);

namespace App\Enums;

enum AddonType: string
{
    case INCREMENT = 'increment';
    case UNLIMITED = 'unlimited';
    case BOOLEAN = 'boolean';

    public function label(): string
    {
        return match ($this) {
            self::INCREMENT => 'Artırım',
            self::UNLIMITED => 'Sınırsız',
            self::BOOLEAN => 'Açık/Kapalı',
        };
    }

    public function description(): string
    {
        return match ($this) {
            self::INCREMENT => 'Mevcut limite belirtilen değeri ekler (Örn: +10 kullanıcı)',
            self::UNLIMITED => 'Limiti tamamen kaldırır ve sınırsız yapar',
            self::BOOLEAN => 'Özelliği aktif veya pasif hale getirir',
        };
    }

    public function icon(): string
    {
        return match ($this) {
            self::INCREMENT => 'plus',
            self::UNLIMITED => 'infinity',
            self::BOOLEAN => 'toggle-right',
        };
    }

    public function badge(): string
    {
        return match ($this) {
            self::INCREMENT => 'primary',
            self::UNLIMITED => 'success',
            self::BOOLEAN => 'info',
        };
    }

    public static function options(): array
    {
        return collect(self::cases())
            ->mapWithKeys(fn($case) => [$case->value => $case->label()])
            ->toArray();
    }
}
