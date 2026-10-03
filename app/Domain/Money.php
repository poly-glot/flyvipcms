<?php

declare(strict_types=1);

namespace App\Domain;

final class Money
{
    private const int SCALE = 2;

    public static function normalise(string|int|float|null $value): string
    {
        return number_format((float) $value, self::SCALE, '.', '');
    }

    public static function add(string $a, string $b): string
    {
        return bcadd($a, $b, self::SCALE);
    }

    public static function sub(string $a, string $b): string
    {
        return bcsub($a, $b, self::SCALE);
    }

    public static function mul(string $a, int $times): string
    {
        return bcmul($a, (string) $times, self::SCALE);
    }

    public static function quarter(string $yearly): string
    {
        return bcdiv($yearly, '4', self::SCALE);
    }

    public static function compare(string $a, string $b): int
    {
        return bccomp($a, $b, self::SCALE);
    }

    public static function isPositive(string $value): bool
    {
        return self::compare($value, '0') > 0;
    }

    public static function negate(string $value): string
    {
        return bcmul($value, '-1', self::SCALE);
    }
}
