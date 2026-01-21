<?php

namespace App\Models;

use CodeIgniter\Model;

class WhatsappIncomingMessageModel extends Model
{
    protected $table = 'whatsapp_incoming_messages';
    protected $primaryKey = 'id';
    protected $useAutoIncrement = true;
    protected $returnType = 'array';
    protected $useSoftDeletes = false;
    protected $protectFields = true;
    protected $allowedFields = [
        'customer_id',
        'customer_name',
        'phone_number',
        'message_content',
        'message_type',
        'received_at',
        'is_read',
        'reply_sent',
        'replied_at'
    ];

    // Dates
    protected $useTimestamps = true;
    protected $dateFormat = 'datetime';
    protected $createdField = 'created_at';
    protected $updatedField = 'updated_at';
    protected $deletedField = 'deleted_at';

    // Validation
    protected $validationRules = [
        'phone_number' => 'required|max_length[20]',
        'message_content' => 'required',
        'message_type' => 'in_list[text,image,document,audio,video,location]'
    ];
    protected $validationMessages = [
        'phone_number' => [
            'required' => 'Phone number is required'
        ],
        'message_content' => [
            'required' => 'Message content is required'
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
     * Get unread messages
     */
    public function getUnreadMessages($limit = 50)
    {
        return $this->where('is_read', 0)
            ->orderBy('received_at', 'DESC')
            ->limit($limit)
            ->findAll();
    }

    /**
     * Get messages by customer
     */
    public function getCustomerMessages($customerId, $limit = 100)
    {
        return $this->where('customer_id', $customerId)
            ->orderBy('received_at', 'DESC')
            ->limit($limit)
            ->findAll();
    }

    /**
     * Get messages by phone number
     */
    public function getPhoneMessages($phone, $limit = 100)
    {
        return $this->where('phone_number', $phone)
            ->orderBy('received_at', 'DESC')
            ->limit($limit)
            ->findAll();
    }

    /**
     * Mark message as read
     */
    public function markAsRead($messageId)
    {
        return $this->update($messageId, [
            'is_read' => 1,
            'read_at' => date('Y-m-d H:i:s')
        ]);
    }

    /**
     * Mark message as replied
     */
    public function markAsReplied($messageId)
    {
        return $this->update($messageId, [
            'reply_sent' => 1,
            'replied_at' => date('Y-m-d H:i:s')
        ]);
    }

    /**
     * Get message statistics
     */
    public function getStats($days = 7)
    {
        $fromDate = date('Y-m-d', strtotime("-{$days} days"));

        return [
            'total_messages' => $this->where('received_at >=', $fromDate)->countAllResults(false),
            'unread_messages' => $this->where('is_read', 0)->countAllResults(false),
            'messages_by_type' => $this->select('message_type, COUNT(*) as count')
                ->where('received_at >=', $fromDate)
                ->groupBy('message_type')
                ->findAll(),
            'top_senders' => $this->select('phone_number, customer_name, COUNT(*) as message_count')
                ->where('received_at >=', $fromDate)
                ->groupBy('phone_number')
                ->orderBy('message_count', 'DESC')
                ->limit(10)
                ->findAll()
        ];
    }
}
