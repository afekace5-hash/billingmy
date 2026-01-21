<?php

namespace App\Controllers;

use CodeIgniter\RESTful\ResourceController;
use CodeIgniter\API\ResponseTrait;

class WhatsAppWebhookUniversal extends ResourceController
{
    use ResponseTrait;

    protected $format = 'json';

    /**
     * Universal WhatsApp Webhook Endpoint
     * Menangani semua jenis webhook dari berbagai provider WhatsApp
     * URL: https://yourdomain.com/api/whatsapp/webhook
     */
    public function receive()
    {
        try {
            // Get raw POST data
            $rawData = $this->request->getBody();
            $headers = $this->request->getHeaders();

            // Log semua webhook yang masuk
            log_message('info', 'WhatsApp Webhook Received: ' . $rawData);
            log_message('info', 'WhatsApp Webhook Headers: ' . json_encode($headers));

            // Parse JSON data
            $data = json_decode($rawData, true);
            if (!$data) {
                log_message('error', 'WhatsApp Webhook: Invalid JSON data');
                return $this->fail('Invalid JSON data', 400);
            }

            // Detect webhook provider
            $provider = $this->detectWebhookProvider($data, $headers);
            log_message('info', "Detected WhatsApp Provider: {$provider}");

            // Process webhook berdasarkan provider
            switch ($provider) {
                case 'wamoo':
                    return $this->processWamooWebhook($data);

                case 'whatsapp_business_api':
                case 'meta':
                    return $this->processMetaWebhook($data);

                case 'twilio':
                    return $this->processTwilioWebhook($data);

                case 'wati':
                    return $this->processWatiWebhook($data);

                case 'ultramsg':
                    return $this->processUltraMsgWebhook($data);

                case 'green_api':
                    return $this->processGreenApiWebhook($data);

                default:
                    return $this->processGenericWebhook($data, $provider);
            }
        } catch (\Exception $e) {
            log_message('error', 'WhatsApp Webhook Error: ' . $e->getMessage());
            return $this->fail('Webhook processing failed: ' . $e->getMessage(), 500);
        }
    }

    /**
     * Detect webhook provider berdasarkan data dan headers
     */
    private function detectWebhookProvider($data, $headers)
    {
        // Check headers untuk identifikasi provider
        $userAgent = $headers['User-Agent'] ?? $headers['user-agent'] ?? '';

        // Wamoo detection
        if (stripos($userAgent, 'wamoo') !== false || isset($data['wamoo'])) {
            return 'wamoo';
        }

        // Meta WhatsApp Business API detection
        if (isset($data['object']) && $data['object'] === 'whatsapp_business_account') {
            return 'whatsapp_business_api';
        }

        // Twilio detection
        if (isset($data['AccountSid']) || stripos($userAgent, 'twilio') !== false) {
            return 'twilio';
        }

        // WATI detection
        if (isset($data['eventType']) || stripos($userAgent, 'wati') !== false) {
            return 'wati';
        }

        // UltraMsg detection
        if (isset($data['type']) && isset($data['instance_id'])) {
            return 'ultramsg';
        }

        // Green API detection
        if (isset($data['typeWebhook']) || stripos($userAgent, 'green-api') !== false) {
            return 'green_api';
        }

        return 'generic';
    }

    /**
     * Process Wamoo webhook
     */
    private function processWamooWebhook($data)
    {
        $eventType = $data['event'] ?? $data['type'] ?? 'unknown';

        switch ($eventType) {
            case 'message_status':
            case 'delivery_status':
                return $this->handleDeliveryStatus($data, 'wamoo');

            case 'incoming_message':
            case 'message_received':
                return $this->handleIncomingMessage($data, 'wamoo');

            case 'device_status':
                return $this->handleDeviceStatus($data, 'wamoo');

            default:
                return $this->logGenericWebhook($data, 'wamoo', $eventType);
        }
    }

    /**
     * Process Meta WhatsApp Business API webhook
     */
    private function processMetaWebhook($data)
    {
        if (isset($data['entry'])) {
            foreach ($data['entry'] as $entry) {
                if (isset($entry['changes'])) {
                    foreach ($entry['changes'] as $change) {
                        if ($change['field'] === 'messages') {
                            $this->processMetaMessages($change['value']);
                        }
                    }
                }
            }
        }

        return $this->respond(['status' => 'success']);
    }

    /**
     * Process Meta messages
     */
    private function processMetaMessages($value)
    {
        // Process incoming messages
        if (isset($value['messages'])) {
            foreach ($value['messages'] as $message) {
                $this->handleIncomingMessage([
                    'phone' => $message['from'],
                    'message' => $message['text']['body'] ?? $message['type'],
                    'message_type' => $message['type'],
                    'timestamp' => date('Y-m-d H:i:s', $message['timestamp'])
                ], 'meta');
            }
        }

        // Process message status updates
        if (isset($value['statuses'])) {
            foreach ($value['statuses'] as $status) {
                $this->handleDeliveryStatus([
                    'message_id' => $status['id'],
                    'phone' => $status['recipient_id'],
                    'status' => $status['status'],
                    'timestamp' => date('Y-m-d H:i:s', $status['timestamp'])
                ], 'meta');
            }
        }
    }

    /**
     * Process Twilio webhook
     */
    private function processTwilioWebhook($data)
    {
        if (isset($data['MessageStatus'])) {
            // Status update
            return $this->handleDeliveryStatus([
                'message_id' => $data['MessageSid'] ?? '',
                'phone' => $data['To'] ?? '',
                'status' => strtolower($data['MessageStatus']),
                'timestamp' => date('Y-m-d H:i:s')
            ], 'twilio');
        } else if (isset($data['Body'])) {
            // Incoming message
            return $this->handleIncomingMessage([
                'phone' => $data['From'] ?? '',
                'message' => $data['Body'] ?? '',
                'message_type' => 'text',
                'timestamp' => date('Y-m-d H:i:s')
            ], 'twilio');
        }

        return $this->logGenericWebhook($data, 'twilio', 'unknown');
    }

    /**
     * Process WATI webhook
     */
    private function processWatiWebhook($data)
    {
        $eventType = $data['eventType'] ?? 'unknown';

        switch ($eventType) {
            case 'message':
                return $this->handleIncomingMessage([
                    'phone' => $data['waId'] ?? '',
                    'message' => $data['text'] ?? '',
                    'message_type' => $data['type'] ?? 'text',
                    'timestamp' => date('Y-m-d H:i:s', $data['created'] ?? time())
                ], 'wati');

            case 'message_status':
                return $this->handleDeliveryStatus([
                    'message_id' => $data['id'] ?? '',
                    'phone' => $data['waId'] ?? '',
                    'status' => $data['status'] ?? 'unknown',
                    'timestamp' => date('Y-m-d H:i:s')
                ], 'wati');

            default:
                return $this->logGenericWebhook($data, 'wati', $eventType);
        }
    }

    /**
     * Process UltraMsg webhook
     */
    private function processUltraMsgWebhook($data)
    {
        $type = $data['type'] ?? 'unknown';

        switch ($type) {
            case 'message':
                return $this->handleIncomingMessage([
                    'phone' => $data['from'] ?? '',
                    'message' => $data['body'] ?? '',
                    'message_type' => 'text',
                    'timestamp' => date('Y-m-d H:i:s')
                ], 'ultramsg');

            case 'delivery':
                return $this->handleDeliveryStatus([
                    'message_id' => $data['id'] ?? '',
                    'phone' => $data['to'] ?? '',
                    'status' => 'delivered',
                    'timestamp' => date('Y-m-d H:i:s')
                ], 'ultramsg');

            default:
                return $this->logGenericWebhook($data, 'ultramsg', $type);
        }
    }

    /**
     * Process Green API webhook
     */
    private function processGreenApiWebhook($data)
    {
        $typeWebhook = $data['typeWebhook'] ?? 'unknown';

        switch ($typeWebhook) {
            case 'incomingMessageReceived':
                $messageData = $data['messageData'] ?? [];
                return $this->handleIncomingMessage([
                    'phone' => $messageData['chatId'] ?? '',
                    'message' => $messageData['textMessageData']['textMessage'] ?? '',
                    'message_type' => $messageData['typeMessage'] ?? 'text',
                    'timestamp' => date('Y-m-d H:i:s', $messageData['timestamp'] ?? time())
                ], 'green_api');

            case 'outgoingMessageStatus':
                $messageData = $data['messageData'] ?? [];
                return $this->handleDeliveryStatus([
                    'message_id' => $messageData['idMessage'] ?? '',
                    'phone' => $messageData['chatId'] ?? '',
                    'status' => strtolower($messageData['status'] ?? 'unknown'),
                    'timestamp' => date('Y-m-d H:i:s')
                ], 'green_api');

            default:
                return $this->logGenericWebhook($data, 'green_api', $typeWebhook);
        }
    }

    /**
     * Process generic webhook
     */
    private function processGenericWebhook($data, $provider)
    {
        return $this->logGenericWebhook($data, $provider, 'unknown');
    }

    /**
     * Handle delivery status updates (universal)
     */
    private function handleDeliveryStatus($data, $provider)
    {
        $phone = $this->cleanPhoneNumber($data['phone'] ?? $data['number'] ?? $data['to'] ?? '');
        $status = $this->normalizeStatus($data['status'] ?? 'unknown');
        $messageId = $data['message_id'] ?? $data['id'] ?? null;
        $timestamp = $data['timestamp'] ?? date('Y-m-d H:i:s');

        if (!$phone) {
            log_message('warning', "Webhook {$provider}: No phone number in delivery status");
            return $this->respond(['status' => 'warning', 'message' => 'No phone number provided']);
        }

        // Update message log
        $logModel = new \App\Models\WhatsappMessageLogModel();

        // Find message by phone number atau message ID
        $message = null;
        if ($messageId) {
            $message = $logModel->where('external_message_id', $messageId)->first();
        }

        if (!$message) {
            $message = $logModel->where('phone_number', $phone)
                ->where('created_at >=', date('Y-m-d H:i:s', strtotime('-2 hours')))
                ->orderBy('created_at', 'DESC')
                ->first();
        }

        if ($message) {
            $updateData = [
                'delivery_status' => $status,
                'provider' => $provider,
                'delivered_at' => ($status === 'delivered') ? $timestamp : $message['delivered_at'],
                'read_at' => ($status === 'read') ? $timestamp : $message['read_at'],
                'failed_at' => (in_array($status, ['failed', 'rejected', 'undelivered'])) ? $timestamp : $message['failed_at'],
                'updated_at' => date('Y-m-d H:i:s')
            ];

            $logModel->update($message['id'], $updateData);
            log_message('info', "Webhook {$provider}: Updated delivery status for {$phone} to {$status}");
        }

        // Log webhook event
        $this->logWebhookEvent('delivery_status', $data, $phone, $provider);

        return $this->respond([
            'status' => 'success',
            'message' => 'Delivery status updated',
            'provider' => $provider,
            'phone' => $phone,
            'delivery_status' => $status
        ]);
    }

    /**
     * Handle incoming messages (universal)
     */
    private function handleIncomingMessage($data, $provider)
    {
        $phone = $this->cleanPhoneNumber($data['phone'] ?? $data['from'] ?? $data['number'] ?? '');
        $message = $data['message'] ?? $data['text'] ?? $data['body'] ?? '';
        $messageType = $data['message_type'] ?? $data['type'] ?? 'text';
        $timestamp = $data['timestamp'] ?? date('Y-m-d H:i:s');

        if (!$phone || !$message) {
            log_message('warning', "Webhook {$provider}: Incomplete incoming message data");
            return $this->respond(['status' => 'warning', 'message' => 'Incomplete message data']);
        }

        // Find customer
        $customerModel = new \App\Models\CustomerModel();
        $customer = $customerModel->where('phone', $phone)
            ->orWhere('phone', 'LIKE', '%' . substr($phone, -8))
            ->first();

        // Save incoming message
        $incomingModel = new \App\Models\WhatsappIncomingMessageModel();
        $incomingModel->insert([
            'customer_id' => $customer['id'] ?? null,
            'customer_name' => $customer['name'] ?? 'Unknown',
            'phone_number' => $phone,
            'message_content' => $message,
            'message_type' => $messageType,
            'provider' => $provider,
            'received_at' => $timestamp,
            'is_read' => 0,
            'created_at' => date('Y-m-d H:i:s')
        ]);

        // Log webhook event
        $this->logWebhookEvent('incoming_message', $data, $phone, $provider);

        log_message('info', "Webhook {$provider}: Incoming message from {$phone}: " . substr($message, 0, 50));

        return $this->respond([
            'status' => 'success',
            'message' => 'Incoming message processed',
            'provider' => $provider,
            'phone' => $phone,
            'customer' => $customer['name'] ?? 'Unknown'
        ]);
    }

    /**
     * Handle device status updates
     */
    private function handleDeviceStatus($data, $provider)
    {
        $deviceNumber = $data['device'] ?? $data['phone'] ?? $data['number'] ?? null;
        $status = $data['status'] ?? $data['device_status'] ?? 'unknown';
        $timestamp = $data['timestamp'] ?? date('Y-m-d H:i:s');

        if (!$deviceNumber) {
            return $this->respond(['status' => 'warning', 'message' => 'No device number provided']);
        }

        // Update device status
        $deviceModel = new \App\Models\WhatsappDeviceModel();
        $device = $deviceModel->where('phone_number', $deviceNumber)->first();

        if ($device) {
            $deviceModel->update($device['id'], [
                'connection_status' => $status,
                'provider' => $provider,
                'last_seen' => $timestamp,
                'updated_at' => date('Y-m-d H:i:s')
            ]);
        }

        // Log webhook event
        $this->logWebhookEvent('device_status', $data, $deviceNumber, $provider);

        return $this->respond([
            'status' => 'success',
            'message' => 'Device status updated',
            'provider' => $provider,
            'device' => $deviceNumber,
            'device_status' => $status
        ]);
    }

    /**
     * Log generic webhook
     */
    private function logGenericWebhook($data, $provider, $eventType)
    {
        $this->logWebhookEvent($eventType, $data, null, $provider);

        return $this->respond([
            'status' => 'success',
            'message' => 'Webhook received and logged',
            'provider' => $provider,
            'event_type' => $eventType
        ]);
    }

    /**
     * Log webhook events
     */
    private function logWebhookEvent($eventType, $data, $phone = null, $provider = 'unknown')
    {
        $webhookLogModel = new \App\Models\WamooWebhookLogModel();
        $webhookLogModel->insert([
            'event_type' => $eventType,
            'phone_number' => $phone,
            'provider' => $provider,
            'webhook_data' => json_encode($data),
            'processed_at' => date('Y-m-d H:i:s'),
            'ip_address' => $this->request->getIPAddress(),
            'user_agent' => $this->request->getUserAgent()->getAgentString(),
            'status' => 'success'
        ]);
    }

    /**
     * Clean and normalize phone number
     */
    private function cleanPhoneNumber($phone)
    {
        // Remove non-numeric characters
        $phone = preg_replace('/[^0-9]/', '', $phone);

        // Add country code if missing
        if (substr($phone, 0, 1) === '8') {
            $phone = '62' . $phone;
        } elseif (substr($phone, 0, 2) === '08') {
            $phone = '62' . substr($phone, 1);
        }

        return $phone;
    }

    /**
     * Normalize status dari berbagai provider
     */
    private function normalizeStatus($status)
    {
        $status = strtolower($status);

        // Mapping status dari berbagai provider
        $statusMap = [
            'sent' => 'sent',
            'delivered' => 'delivered',
            'read' => 'read',
            'failed' => 'failed',
            'rejected' => 'failed',
            'undelivered' => 'failed',
            'accepted' => 'sent',
            'queued' => 'pending',
            'sending' => 'pending'
        ];

        return $statusMap[$status] ?? $status;
    }

    /**
     * Webhook verification untuk beberapa provider
     */
    public function verify()
    {
        // Meta WhatsApp verification
        $hubChallenge = $this->request->getGet('hub_challenge');
        $hubVerifyToken = $this->request->getGet('hub_verify_token');

        if ($hubChallenge && $hubVerifyToken) {
            // Verify token sesuai konfigurasi
            $expectedToken = env('WHATSAPP_VERIFY_TOKEN', 'your_verify_token');

            if ($hubVerifyToken === $expectedToken) {
                return $this->response->setBody($hubChallenge);
            }
        }

        return $this->fail('Verification failed', 403);
    }

    /**
     * Test endpoint
     */
    public function test()
    {
        return $this->respond([
            'status' => 'success',
            'message' => 'Universal WhatsApp webhook endpoint is working',
            'timestamp' => date('Y-m-d H:i:s'),
            'server' => $_SERVER['HTTP_HOST'] ?? 'localhost',
            'supported_providers' => [
                'wamoo',
                'whatsapp_business_api',
                'meta',
                'twilio',
                'wati',
                'ultramsg',
                'green_api',
                'generic'
            ]
        ]);
    }

    /**
     * Get webhook statistics
     */
    public function stats()
    {
        $webhookLogModel = new \App\Models\WamooWebhookLogModel();

        $stats = [
            'total_webhooks' => $webhookLogModel->countAll(),
            'today_webhooks' => $webhookLogModel->where('DATE(processed_at)', date('Y-m-d'))->countAllResults(),
            'by_provider' => $webhookLogModel->select('provider, COUNT(*) as count')
                ->groupBy('provider')
                ->findAll(),
            'by_event_type' => $webhookLogModel->select('event_type, COUNT(*) as count')
                ->groupBy('event_type')
                ->findAll(),
            'recent_events' => $webhookLogModel->orderBy('processed_at', 'DESC')->limit(20)->findAll()
        ];

        return $this->respond($stats);
    }
}
