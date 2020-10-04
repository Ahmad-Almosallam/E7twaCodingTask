<?php

namespace App\Models;

use CodeIgniter\Model;

class User_model extends Model
{

    protected $table = "users";

    protected $primaryKey = 'id';

    protected $returnType     = 'array';

    protected $allowedFields = [
        'name', 'age', 'email', 'password', 'is_admin'
    ];
    protected $beforeInsert = ['hashPassword'];
    protected $beforeUpdate = ['hashPassword'];

    protected function hashPassword(array $data)
    {
        if (!isset($data['data']['password']))
            return $data;

        $password = $data['data']['password'];
        $data['data']['password'] = password_hash($password, PASSWORD_DEFAULT);


        return $data;
    }

    public function validateUser($data, $password)
    {
        if (password_verify($password, $data)) {
            return true;
        }
        return false;
    }
}
