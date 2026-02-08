<?php

namespace App\Enums;

enum ResetPeriod: string
{
    case Daily = 'daily';
    case Weekly = 'weekly';
    case Monthly = 'monthly';
    case Yearly = 'yearly';

    public function label(): string
    {
        return match ($this) {
            self::Daily => 'Günlük',
            self::Weekly => 'Haftalık',
            self::Monthly => 'Aylık',
            self::Yearly => 'Yıllık',
        };
    }
}
