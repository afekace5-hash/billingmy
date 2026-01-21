<?php

namespace App\Controllers\Api;

use App\Controllers\BaseController;
use App\Models\CustomerNotificationModel;

class NotificationController extends BaseController
{
    protected $notificationModel;

    public function __construct()
    {
        $this->notificationModel = new CustomerNotificationModel();
    }

    /**
     * Get customer notifications
     * GET /api/notifications
     * Query params: ?limit=50&page=1&type=invoice|payment|isolir|promo|system
     */
    public function index()
    {
        /** @var int $customerId */
        $customerId = $this->request->customerId ?? null;

        $limit = $this->request->getGet('limit') ?? 50;
        $page = $this->request->getGet('page') ?? 1;
        $type = $this->request->getGet('type');
        $offset = ($page - 1) * $limit;

        $builder = $this->notificationModel->where('customer_id', $customerId);

        if ($type) {
            $builder->where('type', $type);
        }

        // Get total count
        $total = $builder->countAllResults(false);

        // Get paginated notifications
        $notifications = $builder
            ->orderBy('created_at', 'DESC')
            ->findAll($limit, $offset);

        // Get unread count
        $unreadCount = $this->notificationModel->getUnreadCount($customerId);

        // Parse JSON data field
        foreach ($notifications as &$notif) {
            if ($notif['data']) {
                $notif['data'] = json_decode($notif['data'], true);
            }
        }

        return $this->response->setJSON([
            'success' => true,
            'message' => 'Notifikasi berhasil dimuat',
            'data' => [
                'notifications' => $notifications,
                'unread_count' => $unreadCount,
                'pagination' => [
                    'total' => $total,
                    'per_page' => (int)$limit,
                    'current_page' => (int)$page,
                    'total_pages' => ceil($total / $limit)
                ]
            ]
        ]);
    }

    /**
     * Get unread notification count
     * GET /api/notifications/unread-count
     */
    public function unreadCount()
    {
        /** @var int $customerId */
        $customerId = $this->request->customerId ?? null;

        $count = $this->notificationModel->getUnreadCount($customerId);

        return $this->response->setJSON([
            'success' => true,
            'message' => 'Jumlah notifikasi belum dibaca',
            'data' => [
                'unread_count' => $count
            ]
        ]);
    }

    /**
     * Mark notification as read
     * PUT /api/notifications/{id}/read
     */
    public function markAsRead($notificationId)
    {
        $customerId = $this->request->customerId;

        $notification = $this->notificationModel
            ->where('id', $notificationId)
            ->where('customer_id', $customerId)
            ->first();

        if (!$notification) {
            return $this->response->setJSON([
                'success' => false,
                'message' => 'Notifikasi tidak ditemukan'
            ])->setStatusCode(404);
        }

        $this->notificationModel->markAsRead($notificationId, $customerId);

        return $this->response->setJSON([
            'success' => true,
            'message' => 'Notifikasi ditandai sudah dibaca'
        ]);
    }

    /**
     * Mark all notifications as read
     * PUT /api/notifications/read-all
     */
    public function markAllAsRead()
    {
        /** @var int $customerId */
        $customerId = $this->request->customerId ?? null;

        $this->notificationModel->markAllAsRead($customerId);

        return $this->response->setJSON([
            'success' => true,
            'message' => 'Semua notifikasi ditandai sudah dibaca'
        ]);
    }

    /**
     * Delete notification
     * DELETE /api/notifications/{id}
     */
    public function delete($notificationId)
    {
        $customerId = $this->request->customerId;

        $notification = $this->notificationModel
            ->where('id', $notificationId)
            ->where('customer_id', $customerId)
            ->first();

        if (!$notification) {
            return $this->response->setJSON([
                'success' => false,
                'message' => 'Notifikasi tidak ditemukan'
            ])->setStatusCode(404);
        }

        $this->notificationModel->delete($notificationId);

        return $this->response->setJSON([
            'success' => true,
            'message' => 'Notifikasi berhasil dihapus'
        ]);
    }

    /**
     * Register FCM token (moved from AuthController for better organization)
     * POST /api/notifications/register-fcm
     * Body: { "fcm_token": "firebase_token", "device_type": "android|ios" }
     */
    public function registerFCM()
    {
        $customerId = $this->request->customerId;

        $fcmToken = $this->request->getPost('fcm_token');
        $deviceType = $this->request->getPost('device_type') ?? 'android';

        if (empty($fcmToken)) {
            return $this->response->setJSON([
                'success' => false,
                'message' => 'FCM token wajib diisi'
            ])->setStatusCode(400);
        }

        $customerModel = new \App\Models\CustomerModel();
        $customerModel->update($customerId, [
            'fcm_token' => $fcmToken,
            'device_info' => json_encode([
                'type' => $deviceType,
                'updated_at' => date('Y-m-d H:i:s')
            ])
        ]);

        return $this->response->setJSON([
            'success' => true,
            'message' => 'FCM token berhasil didaftarkan'
        ]);
    }

    /**
     * Test send notification (for testing purposes)
     * POST /api/notifications/test
     * Body: { "title": "Test", "message": "Test message", "type": "general" }
     */
    public function testNotification()
    {
        $customerId = $this->request->customerId;

        $title = $this->request->getPost('title') ?? 'Test Notification';
        $message = $this->request->getPost('message') ?? 'This is a test notification';
        $type = $this->request->getPost('type') ?? 'general';

        $notificationId = $this->notificationModel->createNotification(
            $customerId,
            $title,
            $message,
            $type
        );

        if ($notificationId) {
            return $this->response->setJSON([
                'success' => true,
                'message' => 'Test notifikasi berhasil dibuat',
                'data' => [
                    'notification_id' => $notificationId
                ]
            ]);
        }

        return $this->response->setJSON([
            'success' => false,
            'message' => 'Gagal membuat notifikasi'
        ])->setStatusCode(500);
    }
}
