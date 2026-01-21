<?php

namespace App\Models;

use CodeIgniter\Model;

class WhatsappNotifSettingModel extends Model
{
    protected $table = 'whatsapp_notif_settings';
    protected $primaryKey = 'id';
    protected $allowedFields = [
        'number', // nomor WhatsApp
        'notif_invoice',
        'notif_payment',
        'notif_reminder',
        'notif_other',
        'updated_at'
    ];
    protected $useTimestamps = false;
    protected $dateFormat = 'datetime';
    protected $updatedField = 'updated_at';
}
