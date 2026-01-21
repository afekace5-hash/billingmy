<?php

namespace App\Models;

use CodeIgniter\Model;

class WhatsappLogModel extends Model
{
    protected $table = 'wa_logs';
    protected $primaryKey = 'id';
    protected $allowedFields = ['sender', 'number', 'message', 'status', 'response', 'created_at'];
    public $timestamps = false;
}
