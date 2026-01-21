<?php

namespace App\Models;

use CodeIgniter\Model;

class ServerLocationModel extends Model
{
    protected $table = 'lokasi_server';
    protected $primaryKey = 'id_lokasi';
    protected $allowedFields = [
        'name',
        'ip_router',
        'username',
        'password',
        'port_api',
        'address',
        'due_date',
        'tax',
        'tax_amount',
        'is_connected',
        'ping_status',
        'auto_ping_enabled',
        'api_features',
        'router_name',
        'ip_address',
        'timezone',
        'description',
        'last_checked',
        'last_sync',
        'lokasi',
        'jenis_isolir',
        'local_ip',
        'legacy_login',
        'remote_url',
        'comment_nat',
        'notes'
    ];
}
