<?php

namespace App\Models;

use CodeIgniter\Model;

class Client_model extends Model
{

    protected $table = "clients";

    protected $primaryKey = 'id';

    protected $returnType     = 'array';

    protected $allowedFields = [
        'client_name', 'api_key', 'is_valid',
    ];
}
