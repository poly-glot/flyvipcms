<?php

declare(strict_types=1);

namespace App\Models;

class AirportModel extends AppModel
{
    public const COST_FIELDS = ['travel_allowance', 'take_off_cost', 'landing_cost', 'stay_cost', 'airport_cost', 'handling_cost'];

    protected $table = 'airports';

    protected $allowedFields = [
        'name', 'call_sign', 'airport_type', 'customs_contact', 'fbo_contact', 'hotel_contact',
        'travel_allowance', 'take_off_cost', 'landing_cost', 'stay_cost', 'airport_cost', 'handling_cost',
        'total_cost', 'comments',
    ];

    protected $beforeInsert = ['sumCosts'];

    protected $beforeUpdate = ['sumCosts'];

    protected function sumCosts(array $event): array
    {
        $data = $event['data'];
        $present = array_intersect(self::COST_FIELDS, array_keys($data));

        if ($present !== []) {
            $data['total_cost'] = array_sum(array_map(static fn (string $field): float => (float) $data[$field], $present));
        }

        $event['data'] = $data;

        return $event;
    }
}
