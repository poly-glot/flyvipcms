<?php

declare(strict_types=1);

namespace App\Services;

use Closure;
use Throwable;

trait Atomic
{
    private function atomic(Closure $work): mixed
    {
        $this->db->transException(true)->transStart();

        try {
            $result = $work();
        } catch (Throwable $error) {
            $this->db->transRollback();

            throw $error;
        }

        $this->db->transComplete();

        return $result;
    }
}
