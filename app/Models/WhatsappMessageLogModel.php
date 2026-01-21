<?php

namespace App\Models;

use CodeIgniter\Model;

class WhatsappMessageLogModel extends Model
{
    protected $table = 'whatsapp_message_logs';
    protected $primaryKey = 'id';
    protected $allowedFields = [
        'customer_id',
        'customer_name',
        'phone_number',
        'template_type',
        'message_content',
        'status', // pending, sent, failed
        'error_message',
        'sent_at',
        'delivery_status', // pending, delivered, read, failed, rejected
        'delivered_at',
        'read_at',
        'failed_at',
        'created_at',
        'updated_at'
    ];

    protected $useTimestamps = true;
    protected $returnType = 'array';

    public function getPendingMessages($limit = 20)
    {
        return $this->where('status', 'pending')
            ->orderBy('created_at', 'DESC')
            ->limit($limit)
            ->findAll();
    }

    public function getErrorMessages($limit = 20)
    {
        return $this->where('status', 'failed')
            ->orderBy('created_at', 'DESC')
            ->limit($limit)
            ->findAll();
    }

    public function getRecentMessages($limit = 20)
    {
        return $this->whereIn('status', ['pending', 'failed'])
            ->orderBy('created_at', 'DESC')
            ->limit($limit)
            ->findAll();
    }

    public function getPendingCount()
    {
        return $this->where('status', 'pending')->countAllResults();
    }

    public function getErrorCount()
    {
        return $this->where('status', 'failed')->countAllResults();
    }

    public function getTodayStats()
    {
        $today = date('Y-m-d');

        return [
            'sent_today' => $this->where('status', 'sent')
                ->where('DATE(created_at)', $today)
                ->countAllResults(),
            'pending_today' => $this->where('status', 'pending')
                ->where('DATE(created_at)', $today)
                ->countAllResults(),
            'failed_today' => $this->where('status', 'failed')
                ->where('DATE(created_at)', $today)
                ->countAllResults()
        ];
    }
}
