<?php

namespace App\Models;

use CodeIgniter\Model;

class RouterOSModel extends Model
{
    protected $table = 'lokasi_server';
    protected $primaryKey = 'id_lokasi';
    protected $allowedFields = [
        'name',
        'ip_router',
        'username',
        'password',
        'password_router',
        'port_api',
        'email',
        'local_ip',
        'legacy_login',
        'remote_url',
        'comment_nat',
        'notes',
        'jenis_isolir',
        'lokasi',
        'domain_name',
        'type',
        'status',
        'district',
        'village',
        'address',
        'due_date',
        'tax',
        'tax_amount',
        'is_connected',
        'last_sync',
        'created_by',
        'created_at',
        'updated_at',
        // Ping monitoring fields
        'ping_status',
        'last_ping_check',
        'last_ping_response_time',
        'ping_failures_count',
        'last_online_time',
        'total_uptime_hours',
        'auto_ping_enabled'
    ];
    protected $useTimestamps = true; // Enable timestamps
    protected $createdField = 'created_at';
    protected $updatedField = 'updated_at';
    protected $returnType = 'array';

    // Validation rules untuk input data
    protected $validationRules = [
        'name' => 'required|max_length[255]',
        'ip_router' => 'required|valid_ip_or_domain',
        'username' => 'required|max_length[100]',
        'password_router' => 'required|max_length[255]',
        'port_api' => 'required|integer|greater_than[0]'
    ];

    protected $validationMessages = [
        'name' => [
            'required' => 'Nama router wajib diisi',
            'max_length' => 'Nama router maksimal 255 karakter'
        ],
        'ip_router' => [
            'required' => 'IP Router wajib diisi',
            'valid_ip_or_domain' => 'Format IP atau domain tidak valid'
        ],
        'username' => [
            'required' => 'Username wajib diisi',
            'max_length' => 'Username maksimal 100 karakter'
        ],
        'password_router' => [
            'required' => 'Password wajib diisi',
            'max_length' => 'Password maksimal 255 karakter'
        ],
        'port_api' => [
            'required' => 'Port API wajib diisi',
            'integer' => 'Port API harus berupa angka',
            'greater_than' => 'Port API harus lebih besar dari 0'
        ]
    ];

    /**
     * Get router by status
     */
    public function getByStatus($status = 'active')
    {
        return $this->where('status', $status)->findAll();
    }

    /**
     * Get router by district
     */
    public function getByDistrict($district)
    {
        return $this->where('district', $district)->findAll();
    }

    /**
     * Check router connection status
     */
    public function updateConnectionStatus($id, $status)
    {
        return $this->update($id, ['is_connected' => $status]);
    }
}
