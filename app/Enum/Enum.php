<?php

namespace App\Enum;

use ReflectionClass;

abstract class Enum
{
    static function getTypes()
    {
        return (new ReflectionClass(get_called_class()))->getConstants();
    }

    abstract static function getTypeTitle($type);
}
