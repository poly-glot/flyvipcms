<?php

declare(strict_types=1);

namespace App\Domain;

final class FlightPricing
{
    public static function seatPrice(string $routeCost, string $aircraftTotalCost): string
    {
        return Money::add($routeCost, $aircraftTotalCost);
    }

    public static function charge(string $seatPrice, int $seats): string
    {
        return Money::mul($seatPrice, $seats);
    }
}
