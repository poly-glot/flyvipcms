<?php

declare(strict_types=1);

namespace Tests\Unit;

use App\Domain\QuarterCalendar;
use DateTimeImmutable;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;

final class QuarterCalendarTest extends TestCase
{
    #[DataProvider('joiningDueDates')]
    public function testFirstDueAfterJoiningIsFifthOfNextQuarter(string $paidOn, string $due): void
    {
        $this->assertSame($due, QuarterCalendar::firstDueAfterJoining(new DateTimeImmutable($paidOn))->format('Y-m-d'));
    }

    public static function joiningDueDates(): array
    {
        return [
            'q1' => ['2026-02-14', '2026-04-05'],
            'q2' => ['2026-05-01', '2026-07-05'],
            'q3' => ['2026-09-30', '2026-10-05'],
            'q4 rolls the year' => ['2026-11-20', '2027-01-05'],
        ];
    }

    public function testQuarterStartAndNext(): void
    {
        $date = new DateTimeImmutable('2026-08-17');

        $this->assertSame('2026-07-01', QuarterCalendar::quarterStart($date)->format('Y-m-d'));
        $this->assertSame('2026-10-01', QuarterCalendar::nextQuarterStart($date)->format('Y-m-d'));
    }

    public function testTermAndQuarterWindows(): void
    {
        $start = new DateTimeImmutable('2026-10-01');

        $this->assertSame('2027-09-30', QuarterCalendar::termEnd($start)->format('Y-m-d'));
        $this->assertSame('2027-04-01', QuarterCalendar::quarterStartInTerm($start, 3)->format('Y-m-d'));
        $this->assertSame('2026-12-31', QuarterCalendar::quarterEnd($start)->format('Y-m-d'));
        $this->assertSame('2027-10-05', QuarterCalendar::dueAfter(new DateTimeImmutable('2027-09-30'))->format('Y-m-d'));
    }
}
