<?php

namespace App\Enum;

final class PaymentMethod extends Enum
{
    const TRANSFER = 1;
    const CREDIT_CARD = 2;
    const CASH = 3;

    static function getTypeTitle($type)
    {
        switch ($type) {
            case self::TRANSFER:
                return 'Havale/EFT';
            case self::CREDIT_CARD:
                return 'Kredi Kartı';
            case self::CASH:
                return 'Nakit';
            default:
                throw new \Exception('Invalid type');
        }
    }
}
