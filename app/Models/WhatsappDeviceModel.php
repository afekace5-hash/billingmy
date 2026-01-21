<?php

namespace App\Models;

use CodeIgniter\Model;

class WhatsappDeviceModel extends Model
{
    protected $table = 'whatsapp_devices';
    protected $primaryKey = 'id';
    protected $allowedFields = ['number', 'api_key', 'created_at', 'updated_at'];
    protected $useTimestamps = true;
}
