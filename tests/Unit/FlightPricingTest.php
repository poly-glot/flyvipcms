<?php

declare(strict_types=1);

namespace Tests\Unit;

use App\Domain\FlightPricing;
use PHPUnit\Framework\TestCase;

final class FlightPricingTest extends TestCase
{
    public function testSeatPriceIsRoutePlusAircraft(): void
    {
        $this->assertSame('650.00', FlightPricing::seatPrice('400.00', '250.00'));
    }

    public function testChargeScalesBySeats(): void
    {
        $this->assertSame('3900.00', FlightPricing::charge('650.00', 6));
        $this->assertSame('1300.00', FlightPricing::charge('650.00', 2));
    }
}
