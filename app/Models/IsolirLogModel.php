<?php

namespace App\Models;

use CodeIgniter\Model;

class IsolirLogModel extends Model
{
    protected $table = 'isolir_logs';
    protected $primaryKey = 'id';
    protected $allowedFields = [
        'customer_id',
        'router_id',
        'username',
        'action',
        'old_profile',
        'new_profile',
        'isolir_ip',
        'reason',
        'notes',
        'created_at',
        'updated_at'
    ];
    protected $useTimestamps = true;
    protected $createdField = 'created_at';
    protected $updatedField = 'updated_at';
    protected $returnType = 'array';

    // Validation rules
    protected $validationRules = [
        'customer_id' => 'required|integer',
        'router_id' => 'required|integer',
        'username' => 'required|max_length[100]',
        'action' => 'required|in_list[isolir,restore]'
    ];

    protected $validationMessages = [
        'customer_id' => [
            'required' => 'Customer ID wajib diisi',
            'integer' => 'Customer ID harus berupa angka'
        ],
        'router_id' => [
            'required' => 'Router ID wajib diisi',
            'integer' => 'Router ID harus berupa angka'
        ],
        'username' => [
            'required' => 'Username wajib diisi',
            'max_length' => 'Username maksimal 100 karakter'
        ],
        'action' => [
            'required' => 'Action wajib diisi',
            'in_list' => 'Action harus isolir atau restore'
        ]
    ];

    /**
     * Get isolir logs by customer
     */
    public function getByCustomer($customerId)
    {
        return $this->where('customer_id', $customerId)
            ->orderBy('created_at', 'DESC')
            ->findAll();
    }

    /**
     * Get isolir logs by router
     */
    public function getByRouter($routerId)
    {
        return $this->where('router_id', $routerId)
            ->orderBy('created_at', 'DESC')
            ->findAll();
    }

    /**
     * Get isolir logs by action
     */
    public function getByAction($action = 'isolir')
    {
        return $this->where('action', $action)
            ->orderBy('created_at', 'DESC')
            ->findAll();
    }

    /**
     * Get recent isolir logs
     */
    public function getRecentLogs($limit = 50)
    {
        return $this->select('isolir_logs.*, customers.name as customer_name, lokasi_server.nama as router_name')
            ->join('customers', 'customers.id = isolir_logs.customer_id', 'left')
            ->join('lokasi_server', 'lokasi_server.id_lokasi = isolir_logs.router_id', 'left')
            ->orderBy('isolir_logs.created_at', 'DESC')
            ->limit($limit)
            ->findAll();
    }

    /**
     * Count isolir by period
     */
    public function countByPeriod($startDate, $endDate, $action = null)
    {
        $builder = $this->where('created_at >=', $startDate)
            ->where('created_at <=', $endDate);

        if ($action) {
            $builder->where('action', $action);
        }

        return $builder->countAllResults();
    }
}
