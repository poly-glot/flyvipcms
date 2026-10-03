<?php

declare(strict_types=1);

namespace App\Models;

class PilotDocumentModel extends AppModel
{
    protected $table = 'pilot_documents';

    protected $allowedFields = ['pilot_id', 'country', 'doc_type', 'doc_number', 'issued_on', 'expires_on'];
}
