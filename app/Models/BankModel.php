<?php

namespace App\Models;

use CodeIgniter\Model;

class BankModel extends Model
{
    protected $table = 'banks';
    protected $primaryKey = 'id';
    protected $allowedFields = ['bank_name', 'account_number', 'account_holder', 'is_active'];
    protected $useTimestamps = true;
}
