<?php

declare(strict_types=1);

namespace App\Enums;

enum InvitationStatus: int
{
    case PENDING = 1;
    case ACCEPTED = 2;
    case EXPIRED = 3;
    case REVOKED = 4;

    public function label(): string
    {
        return match ($this) {
            self::PENDING => 'Beklemede',
            self::ACCEPTED => 'Kabul Edildi',
            self::EXPIRED => 'Süresi Doldu',
            self::REVOKED => 'İptal Edildi',
        };
    }

    public function color(): string
    {
        return match ($this) {
            self::PENDING => 'warning',
            self::ACCEPTED => 'success',
            self::EXPIRED => 'secondary',
            self::REVOKED => 'danger',
        };
    }
}
