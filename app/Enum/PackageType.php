<?php

namespace App\Enum;

final class PackageType extends Enum
{
    const DEMO = 0;
    const MONTLY = 1;
    const YEARLY = 2;

    static function getTypeTitle($type)
    {
        switch ($type) {
            case self::DEMO:
                return 'Demo';
            case self::MONTLY:
                return 'Aylık';
            case self::YEARLY:
                return 'Yıllık';
            default:
                throw new \Exception('Invalid type');
        }
    }

    static function setFinishDate($type, $startDate)
    {
        if (!in_array($type, self::getTypes())) {
            throw new \Exception('Package type is not valid');
        }

        switch ($type) {
            case self::DEMO:
                return $startDate + (60 * 60 * 24 * 15);
            case self::MONTLY:
                return $startDate + (60 * 60 * 24 * 30);
            case self::YEARLY:
                return $startDate + (60 * 60 * 24 * 365);
        }
    }
}
