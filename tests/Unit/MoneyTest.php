<?php

declare(strict_types=1);

namespace Tests\Unit;

use App\Domain\Money;
use PHPUnit\Framework\TestCase;

final class MoneyTest extends TestCase
{
    public function testArithmeticKeepsTwoDecimals(): void
    {
        $this->assertSame('0.30', Money::add('0.10', '0.20'));
        $this->assertSame('9.99', Money::sub('10.00', '0.01'));
        $this->assertSame('3000.00', Money::mul('1000', 3));
        $this->assertSame('3000.00', Money::quarter('12000'));
    }

    public function testComparisonAndSign(): void
    {
        $this->assertSame(0, Money::compare('1.00', '1'));
        $this->assertTrue(Money::isPositive('0.01'));
        $this->assertFalse(Money::isPositive('0'));
        $this->assertSame('-5.00', Money::negate('5'));
    }

    public function testNormaliseHandlesNullAndNumbers(): void
    {
        $this->assertSame('0.00', Money::normalise(null));
        $this->assertSame('12.50', Money::normalise(12.5));
        $this->assertSame('7.00', Money::normalise('7'));
    }
}
