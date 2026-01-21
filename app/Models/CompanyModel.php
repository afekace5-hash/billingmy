<?php

namespace App\Models;

use CodeIgniter\Model;

class CompanyModel extends Model
{
    protected $table = 'companies';
    protected $primaryKey = 'id';
    protected $allowedFields = [
        'name',
        'email',
        'address',
        'phone',
        'website',
        'logo',
        'updated_at',
        'created_at',
    ];
    protected $useTimestamps = true;
}
