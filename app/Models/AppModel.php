<?php

declare(strict_types=1);

namespace App\Models;

use CodeIgniter\Model;

abstract class AppModel extends Model
{
    protected $returnType = 'array';

    protected $useTimestamps = true;

    protected $useSoftDeletes = true;

    public function menu(string $label = 'name'): array
    {
        return array_column($this->orderBy($label)->findAll(), $label, 'id');
    }
}
