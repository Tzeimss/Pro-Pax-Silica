<?php

namespace App\Models;

use CodeIgniter\Model;

class UserModel extends Model
{
    protected $table = 'users';
    protected $primaryKey = 'id';
    protected $returnType = 'array';
    protected $allowedFields = [
        'first_name', 'last_name', 'middle_name', 'birthday', 'gender', 'email',
        'phone', 'address', 'username', 'password_hash',
        'role', 'is_active', 'must_change_password',
    ];
    protected $useTimestamps = true;
}
