<?php

declare(strict_types=1);

namespace App\Domain;

use DateTimeImmutable;

final class QuarterCalendar
{
    public const int GRACE_DAYS = 5;

    public static function quarterStart(DateTimeImmutable $date): DateTimeImmutable
    {
        $firstMonth = intdiv((int) $date->format('n') - 1, 3) * 3 + 1;

        return $date->setDate((int) $date->format('Y'), $firstMonth, 1)->setTime(0, 0);
    }

    public static function nextQuarterStart(DateTimeImmutable $date): DateTimeImmutable
    {
        return self::quarterStart($date)->modify('+3 months');
    }

    public static function termEnd(DateTimeImmutable $start): DateTimeImmutable
    {
        return $start->modify('+1 year -1 day');
    }

    public static function quarterStartInTerm(DateTimeImmutable $termStart, int $quarter): DateTimeImmutable
    {
        return $termStart->modify(sprintf('+%d months', ($quarter - 1) * 3));
    }

    public static function quarterEnd(DateTimeImmutable $quarterStart): DateTimeImmutable
    {
        return $quarterStart->modify('+3 months -1 day');
    }

    public static function dueAfter(DateTimeImmutable $end): DateTimeImmutable
    {
        return $end->modify(sprintf('+%d days', self::GRACE_DAYS));
    }

    public static function firstDueAfterJoining(DateTimeImmutable $paidOn): DateTimeImmutable
    {
        return self::dueAfter(self::quarterEnd(self::quarterStart($paidOn)));
    }
}
