<?php

namespace App\Models;

use CodeIgniter\Model;

class WamooWebhookLogModel extends Model
{
    protected $table = 'wamoo_webhook_logs';
    protected $primaryKey = 'id';
    protected $useAutoIncrement = true;
    protected $returnType = 'array';
    protected $useSoftDeletes = false;
    protected $protectFields = true;
    protected $allowedFields = [
        'event_type',
        'phone_number',
        'provider',
        'webhook_data',
        'processed_at',
        'ip_address',
        'user_agent',
        'status',
        'error_message'
    ];

    // Dates
    protected $useTimestamps = true;
    protected $dateFormat = 'datetime';
    protected $createdField = 'created_at';
    protected $updatedField = 'updated_at';
    protected $deletedField = 'deleted_at';

    // Validation
    protected $validationRules = [
        'event_type' => 'required|max_length[50]',
        'webhook_data' => 'required'
    ];
    protected $validationMessages = [
        'event_type' => [
            'required' => 'Event type is required',
            'max_length' => 'Event type cannot exceed 50 characters'
        ]
    ];
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
     * Get webhook statistics
     */
    public function getStats($days = 7)
    {
        $fromDate = date('Y-m-d', strtotime("-{$days} days"));

        return [
            'total' => $this->where('processed_at >=', $fromDate)->countAllResults(false),
            'by_event_type' => $this->select('event_type, COUNT(*) as count')
                ->where('processed_at >=', $fromDate)
                ->groupBy('event_type')
                ->findAll(),
            'recent' => $this->where('processed_at >=', $fromDate)
                ->orderBy('processed_at', 'DESC')
                ->limit(20)
                ->findAll()
        ];
    }

    /**
     * Get delivery status updates
     */
    public function getDeliveryUpdates($phone = null, $limit = 50)
    {
        $builder = $this->where('event_type', 'delivery_status');

        if ($phone) {
            $builder->where('phone_number', $phone);
        }

        return $builder->orderBy('processed_at', 'DESC')
            ->limit($limit)
            ->findAll();
    }

    /**
     * Get incoming messages
     */
    public function getIncomingMessages($phone = null, $limit = 50)
    {
        $builder = $this->where('event_type', 'incoming_message');

        if ($phone) {
            $builder->where('phone_number', $phone);
        }

        return $builder->orderBy('processed_at', 'DESC')
            ->limit($limit)
            ->findAll();
    }

    /**
     * Clean old webhook logs (older than X days)
     */
    public function cleanOldLogs($days = 30)
    {
        $cutoffDate = date('Y-m-d', strtotime("-{$days} days"));
        return $this->where('processed_at <', $cutoffDate)->delete();
    }
}
