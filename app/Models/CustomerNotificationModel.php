<?php

namespace App\Models;

use CodeIgniter\Model;

class CustomerNotificationModel extends Model
{
    protected $table = 'customer_notifications';
    protected $primaryKey = 'id';
    protected $useAutoIncrement = true;
    protected $returnType = 'array';
    protected $useSoftDeletes = false;
    protected $protectFields = true;
    protected $allowedFields = [
        'customer_id',
        'title',
        'message',
        'type',
        'is_read',
        'read_at',
        'data',
        'sent_via',
        'created_at',
        'updated_at'
    ];

    protected $useTimestamps = true;
    protected $createdField = 'created_at';
    protected $updatedField = 'updated_at';

    protected $validationRules = [
        'customer_id' => 'required|integer',
        'title' => 'required|max_length[255]',
        'message' => 'required',
        'type' => 'required|in_list[invoice,payment,isolir,promo,system,general]'
    ];

    /**
     * Get notifications for a specific customer
     */
    public function getCustomerNotifications($customerId, $limit = 50, $offset = 0)
    {
        return $this->where('customer_id', $customerId)
            ->orderBy('created_at', 'DESC')
            ->findAll($limit, $offset);
    }

    /**
     * Get unread notification count for customer
     */
    public function getUnreadCount($customerId)
    {
        return $this->where('customer_id', $customerId)
            ->where('is_read', false)
            ->countAllResults();
    }

    /**
     * Mark notification as read
     */
    public function markAsRead($notificationId, $customerId)
    {
        return $this->where('id', $notificationId)
            ->where('customer_id', $customerId)
            ->set([
                'is_read' => true,
                'read_at' => date('Y-m-d H:i:s')
            ])
            ->update();
    }

    /**
     * Mark all notifications as read for customer
     */
    public function markAllAsRead($customerId)
    {
        return $this->where('customer_id', $customerId)
            ->where('is_read', false)
            ->set([
                'is_read' => true,
                'read_at' => date('Y-m-d H:i:s')
            ])
            ->update();
    }

    /**
     * Create notification for customer
     */
    public function createNotification($customerId, $title, $message, $type = 'general', $data = null)
    {
        $notificationData = [
            'customer_id' => $customerId,
            'title' => $title,
            'message' => $message,
            'type' => $type,
            'data' => $data ? json_encode($data) : null,
            'sent_via' => 'app',
            'is_read' => false
        ];

        return $this->insert($notificationData);
    }
}
