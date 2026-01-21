<?php

namespace App\Models;

use CodeIgniter\Model;

class PaymentRequestLogModel extends Model
{
    protected $table = 'payment_request_logs';
    protected $primaryKey = 'id';
    protected $useAutoIncrement = true;
    protected $returnType = 'array';
    protected $useSoftDeletes = false;
    protected $protectFields = true;
    protected $allowedFields = [
        'invoice_id',
        'invoice_no',
        'customer_id',
        'customer_name',
        'payment_gateway',
        'payment_method',
        'method_code',
        'amount',
        'status',
        'payment_code',
        'payment_url',
        'request_data',
        'response_data',
        'error_message',
        'ip_address',
        'user_agent',
        'created_at',
        'updated_at'
    ];

    protected bool $allowEmptyInserts = false;
    protected bool $updateOnlyChanged = true;

    protected array $casts = [];
    protected array $castHandlers = [];

    // Dates
    protected $useTimestamps = true;
    protected $dateFormat = 'datetime';
    protected $createdField = 'created_at';
    protected $updatedField = 'updated_at';
    protected $deletedField = 'deleted_at';

    // Validation
    protected $validationRules = [];
    protected $validationMessages = [];
    protected $skipValidation = false;
    protected $cleanValidationRules = true;

    // Callbacks
    protected $allowCallbacks = true;
    protected $beforeInsert = [];
    protected $afterInsert = [];
    protected $beforeUpdate = [];
    protected $afterUpdate = [];
    protected $beforeFind = [];
    protected $afterFind = [];
    protected $beforeDelete = [];
    protected $afterDelete = [];

    /**
     * Get logs by invoice ID
     */
    public function getLogsByInvoice($invoiceId)
    {
        return $this->where('invoice_id', $invoiceId)
            ->orderBy('created_at', 'DESC')
            ->findAll();
    }

    /**
     * Get recent logs
     */
    public function getRecentLogs($limit = 50)
    {
        return $this->orderBy('created_at', 'DESC')
            ->limit($limit)
            ->findAll();
    }

    /**
     * Get logs by status
     */
    public function getLogsByStatus($status)
    {
        return $this->where('status', $status)
            ->orderBy('created_at', 'DESC')
            ->findAll();
    }

    /**
     * Get statistics
     */
    public function getStatistics($startDate = null, $endDate = null)
    {
        $builder = $this->builder();

        if ($startDate && $endDate) {
            $builder->where('created_at >=', $startDate)
                ->where('created_at <=', $endDate);
        }

        return [
            'total' => $builder->countAllResults(false),
            'success' => $builder->where('status', 'success')->countAllResults(false),
            'failed' => $builder->where('status', 'failed')->countAllResults(false),
            'pending' => $builder->where('status', 'pending')->countAllResults(false),
            'expired' => $builder->where('status', 'expired')->countAllResults(false),
        ];
    }
}
