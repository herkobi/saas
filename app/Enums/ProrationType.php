<?php

declare(strict_types=1);

namespace App\Enums;

enum ProrationType: string
{
    case IMMEDIATE = 'immediate';
    case END_OF_PERIOD = 'end_of_period';

    public function isImmediate(): bool
    {
        return $this === self::IMMEDIATE;
    }

    public function label(): string
    {
        return match ($this) {
            self::IMMEDIATE => 'Anında Geçiş',
            self::END_OF_PERIOD => 'Dönem Sonunda Geçiş',
        };
    }

    public static function options(): array
    {
        return collect(self::cases())
            ->mapWithKeys(fn ($case) => [$case->value => $case->label()])
            ->toArray();
    }
}
