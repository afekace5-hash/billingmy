<?php

namespace App\Controllers;

use App\Controllers\BaseController;

class WhatsappMessage extends BaseController
{
    // Endpoint untuk AJAX: get customers by branch
    public function getCustomersByBranch()
    {
        $branchId = $this->request->getGet('branch_id');
        $customerModel = new \App\Models\CustomerModel();
        if ($branchId && $branchId !== 'all') {
            $customers = $customerModel->where('branch_id', $branchId)->findAll();
        } else {
            $customers = $customerModel->findAll();
        }
        $result = [];
        foreach ($customers as $i => $cust) {
            $result[] = [
                'no' => $i + 1,
                'name' => $cust['nama_pelanggan'] ?? '',
                'phone' => $cust['telepphone'] ?? ''
            ];
        }
        return $this->response->setJSON($result);
    }
    public function accountList()
    {
        // Get WhatsApp accounts from database
        $waDeviceModel = new \App\Models\WhatsappDeviceModel();
        $accounts = $waDeviceModel->findAll();

        return view('whatsapp/account', [
            'accounts' => $accounts
        ]);
    }

    public function addAccount()
    {
        if ($this->request->getMethod() !== 'post') {
            return redirect()->back()->with('error', 'Invalid request');
        }

        $data = [
            'number' => $this->request->getPost('phone_number'),
            'api_key' => bin2hex(random_bytes(16)),
            'created_at' => date('Y-m-d H:i:s')
        ];

        $waDeviceModel = new \App\Models\WhatsappDeviceModel();
        if ($waDeviceModel->insert($data)) {
            return redirect()->to('whatsapp/account')->with('success', 'Account added successfully');
        } else {
            return redirect()->back()->with('error', 'Failed to add account');
        }
    }

    public function getQRCode($id)
    {
        try {
            // Get account details
            $waDeviceModel = new \App\Models\WhatsappDeviceModel();
            $account = $waDeviceModel->find($id);

            if (!$account) {
                return $this->response->setJSON([
                    'success' => false,
                    'message' => 'Account not found'
                ]);
            }

            // Get WhatsApp base URL and API key from environment
            $baseUrl = getenv('WHATSAPP_BASE_URL') ?: 'https://wazero.difihome.my.id';
            $apiKey = $account['api_key'] ?? '';
            $device = $account['number'];

            // Validate API key
            if (empty($apiKey)) {
                log_message('error', 'API Key not found for device: ' . $device);
                return $this->response->setJSON([
                    'success' => false,
                    'message' => 'API Key tidak ditemukan. Silakan tambahkan API Key untuk perangkat ini.'
                ]);
            }

            // Request QR Code from WhatsApp API using GET method with query parameters
            $queryParams = http_build_query([
                'device' => $device,
                'api_key' => $apiKey,
                'force' => 1 // Create device if not exists
            ]);
            $url = $baseUrl . '/generate-qr?' . $queryParams;

            log_message('info', 'Requesting QR Code for device: ' . $device . ' to URL: ' . $url);

            $ch = curl_init($url);
            curl_setopt($ch, CURLOPT_HTTPHEADER, [
                'Accept: application/json'
            ]);
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
            curl_setopt($ch, CURLOPT_TIMEOUT, 30);
            curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
            curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);

            $response = curl_exec($ch);
            $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
            $curlError = curl_error($ch);
            $curlInfo = curl_getinfo($ch);
            curl_close($ch);

            log_message('info', 'QR Code API Response - HTTP Code: ' . $httpCode);
            log_message('info', 'QR Code API Response Body: ' . substr($response, 0, 500));
            log_message('info', 'CURL Info: ' . json_encode($curlInfo));

            if ($curlError) {
                log_message('error', 'QR Code curl error: ' . $curlError);
                return $this->response->setJSON([
                    'success' => false,
                    'message' => 'Tidak dapat terhubung ke server WhatsApp: ' . $curlError
                ]);
            }

            $result = json_decode($response, true);

            if (!$result) {
                log_message('error', 'Invalid JSON response from QR API: ' . $response);
                return $this->response->setJSON([
                    'success' => false,
                    'message' => 'Response dari server tidak valid'
                ]);
            }

            // Handle HTTP 200 - Success
            if ($httpCode == 200) {
                // Check if QR Code is generated (status can be true or false, check for qrcode field)
                if (isset($result['qrcode'])) {
                    log_message('info', 'QR Code generated successfully for device: ' . $device);
                    return $this->response->setJSON([
                        'success' => true,
                        'qrcode' => $result['qrcode'],
                        'message' => $result['message'] ?? 'Scan QR Code ini dengan WhatsApp di ponsel Anda'
                    ]);
                }

                // Other success responses
                return $this->response->setJSON([
                    'success' => true,
                    'message' => $result['msg'] ?? $result['message'] ?? 'Berhasil'
                ]);
            }

            // Handle HTTP 400 - Bad Request (includes "already connected")
            if ($httpCode == 400) {
                // Check if device already connected
                if (isset($result['msg']) && strpos(strtolower($result['msg']), 'already connected') !== false) {
                    log_message('info', 'Device already connected: ' . $device);
                    return $this->response->setJSON([
                        'success' => true,
                        'already_connected' => true,
                        'message' => 'Perangkat sudah terhubung! WhatsApp siap digunakan.'
                    ]);
                }

                // Handle validation errors
                if (isset($result['errors'])) {
                    $errorMessages = [];
                    foreach ($result['errors'] as $field => $messages) {
                        $errorMessages[] = implode(', ', (array)$messages);
                    }
                    return $this->response->setJSON([
                        'success' => false,
                        'message' => 'Validation error: ' . implode('; ', $errorMessages)
                    ]);
                }

                // Other 400 errors
                return $this->response->setJSON([
                    'success' => false,
                    'message' => $result['msg'] ?? $result['message'] ?? 'Bad Request'
                ]);
            }

            // Handle other HTTP errors
            log_message('error', 'QR Code API returned HTTP ' . $httpCode . ': ' . $response);
            return $this->response->setJSON([
                'success' => false,
                'message' => 'Server WhatsApp API error (HTTP ' . $httpCode . '): ' . ($result['msg'] ?? $result['message'] ?? 'Unknown error')
            ]);
        } catch (\Exception $e) {
            log_message('error', 'getQRCode error: ' . $e->getMessage());
            log_message('error', 'Stack trace: ' . $e->getTraceAsString());
            return $this->response->setJSON([
                'success' => false,
                'message' => 'Error: ' . $e->getMessage()
            ]);
        }
    }

    public function settings()
    {
        // Get notification settings from whatsapp_notif_settings table
        $settingsModel = new \App\Models\WhatsappNotifSettingModel();
        $settings = $settingsModel->first();

        if (!$settings) {
            $settings = [
                'notif_invoice' => 0,
                'notif_payment' => 0,
                'notif_reminder' => 0,
                'notif_other' => 0
            ];
        }

        return view('whatsapp/settings', ['settings' => $settings]);
    }

    public function saveSettings()
    {
        $settingsModel = new \App\Models\WhatsappNotifSettingModel();

        $data = [
            'notif_invoice' => $this->request->getPost('notif_invoice') ? 1 : 0,
            'notif_payment' => $this->request->getPost('notif_payment') ? 1 : 0,
            'notif_reminder' => $this->request->getPost('notif_reminder') ? 1 : 0,
            'notif_other' => $this->request->getPost('notif_other') ? 1 : 0,
            'updated_at' => date('Y-m-d H:i:s')
        ];

        try {
            $settings = $settingsModel->first();
            if ($settings) {
                $settingsModel->update($settings['id'], $data);
            } else {
                $data['number'] = '';
                $settingsModel->insert($data);
            }

            return redirect()->to('whatsapp/settings')->with('success', 'Pengaturan notifikasi berhasil disimpan');
        } catch (\Exception $e) {
            log_message('error', 'Error saving notification settings: ' . $e->getMessage());
            return redirect()->back()->with('error', 'Gagal menyimpan pengaturan: ' . $e->getMessage());
        }
    }

    public function blastMessage()
    {
        $waDeviceModel = new \App\Models\WhatsappDeviceModel();
        $waDevice = $waDeviceModel->orderBy('id', 'desc')->first();
        $wa_number = $waDevice ? $waDevice['number'] : '';
        return view('message/blast-message', compact('wa_number'));
    }
    public function templateMessage()
    {
        // Load existing templates from database
        $templates = $this->getExistingTemplates();

        // Add info notification about template status
        if (empty($templates) || count($templates) == 0) {
            session()->setFlashdata('warning', 'Belum ada template yang tersimpan. Silakan isi template di bawah ini.');
        } else {
            session()->setFlashdata('info', 'Template berhasil dimuat dari database. Anda dapat mengedit dan menyimpan perubahan.');
        }

        return view('message/template-message', ['templates' => $templates]);
    }
    public function systemInfo()
    {
        // Get real system statistics
        $messageLogModel = new \App\Models\WhatsappMessageLogModel();
        $customerModel = new \App\Models\CustomerModel();

        // Real statistics from database
        $todayStats = $messageLogModel->getTodayStats();
        $activeCustomers = $customerModel->where('status_tagihan', 'Lunas')->countAllResults();

        // Count templates from database
        $templatesCount = 0;
        try {
            $db = \Config\Database::connect();
            $query = $db->query("SELECT * FROM whatsapp_templates WHERE id = 1");
            $template = $query->getRow();

            if ($template) {
                if (!empty($template->bill_reminder)) $templatesCount++;
                if (!empty($template->bill_paid)) $templatesCount++;
                if (!empty($template->new_customer)) $templatesCount++;
                if (!empty($template->isolir_reminder)) $templatesCount++;
                if (!empty($template->isolir_open)) $templatesCount++;
            }
        } catch (\Exception $e) {
            $templatesCount = 0;
        }

        // Get real message logs
        $pendingMessages = $messageLogModel->getPendingMessages(20);
        $errorMessages = $messageLogModel->getErrorMessages(20);
        $pendingCount = $messageLogModel->getPendingCount();
        $errorCount = $messageLogModel->getErrorCount();

        $stats = [
            'templates_count' => $templatesCount,
            'messages_sent_today' => $todayStats['sent_today'],
            'pending_count' => $pendingCount,
            'error_count' => $errorCount,
            'active_customers' => $activeCustomers,
            'last_update' => date('H:i'),
            'connection_status' => $this->checkWhatsAppConnection()
        ];

        $data = [
            'stats' => $stats,
            'pending_messages' => $pendingMessages,
            'error_messages' => $errorMessages,
            'pending_count' => $pendingCount,
            'error_count' => $errorCount
        ];

        session()->setFlashdata('info', 'Informasi sistem WhatsApp telah dimuat dari database real.');

        return view('message/whatsapp-info', $data);
    }

    public function broadcastList()
    {
        // Get broadcast data from database
        $broadcastModel = new \App\Models\BroadcastModel();
        $branchModel = new \App\Models\BranchModel();
        $broadcasts = $broadcastModel->orderBy('created_at', 'DESC')->findAll();
        $branches = $branchModel->orderBy('branch_name', 'ASC')->findAll();

        return view('whatsapp/broadcast', [
            'broadcasts' => $broadcasts,
            'branches' => $branches
        ]);
    }

    public function createBroadcast()
    {
        if ($this->request->getMethod() !== 'post') {
            return redirect()->back()->with('error', 'Invalid request method');
        }

        // Get form data
        $data = [
            'type' => $this->request->getPost('type'),
            'branch' => $this->request->getPost('branch'),
            'area' => $this->request->getPost('area'),
            'title' => $this->request->getPost('title'),
            'message' => $this->request->getPost('message'),
            'scheduled_at' => $this->request->getPost('scheduled_at'),
            'target_users' => $this->request->getPost('target_users'),
            'created_by' => session()->get('username') ?? 'Admin',
            'status' => 'scheduled'
        ];

        // Handle image upload
        $image = $this->request->getFile('image');
        if ($image && $image->isValid() && !$image->hasMoved()) {
            $newName = $image->getRandomName();
            $image->move(ROOTPATH . 'public/uploads/broadcast', $newName);
            $data['image'] = $newName;
        }

        // Calculate total users based on target
        $data['total_users'] = $this->calculateTargetUsers($data['target_users']);

        // Save to database
        $broadcastModel = new \App\Models\BroadcastModel();

        if ($broadcastModel->insert($data)) {
            return redirect()->to('whatsapp/broadcast')->with('success', 'Broadcast created successfully');
        } else {
            return redirect()->back()->with('error', 'Failed to create broadcast')->withInput();
        }
    }

    public function deleteBroadcast($id)
    {
        if ($this->request->getMethod() !== 'post') {
            return $this->response->setJSON(['success' => false, 'message' => 'Invalid request method']);
        }

        // Delete from database
        $broadcastModel = new \App\Models\BroadcastModel();

        if ($broadcastModel->delete($id)) {
            return $this->response->setJSON(['success' => true, 'message' => 'Broadcast deleted successfully']);
        } else {
            return $this->response->setJSON(['success' => false, 'message' => 'Failed to delete broadcast']);
        }
    }

    private function calculateTargetUsers($target)
    {
        $customerModel = new \App\Models\CustomerModel();

        switch ($target) {
            case 'all':
                return $customerModel->countAllResults();
            case 'active':
                return $customerModel->where('status_tagihan', 'Lunas')->countAllResults();
            case 'overdue':
                return $customerModel->where('status_tagihan', 'Belum Lunas')->countAllResults();
            default:
                return 0;
        }
    }

    public function notificationView()
    {
        // Get notification data
        // For now, using empty array - you can add real notification data later
        $notifications = [];

        return view('whatsapp/notification', [
            'notifications' => $notifications
        ]);
    }

    public function sendTemplate()
    {
        // Handle template message sending logic here
        $templateType = $this->request->getPost('template_type');
        $sentTo = $this->request->getPost('sentTo');
        $customer = $this->request->getPost('customer');
        $receiver = $this->request->getPost('receiver');
        $templateMessage = $this->request->getPost('template_message');

        // Validate required fields
        if (!$templateType) {
            return redirect()->back()->with('error', 'Template harus dipilih');
        }

        if ($sentTo === 'customer' && !$customer) {
            return redirect()->back()->with('error', 'Pelanggan harus dipilih');
        }

        if ($sentTo === 'manual' && !$receiver) {
            return redirect()->back()->with('error', 'Nomor penerima harus diisi');
        }

        // TODO: Implement actual WhatsApp sending logic here
        // This would typically involve:
        // 1. Getting customer data if sentTo is 'customer'
        // 2. Replacing template variables with actual data
        // 3. Sending the message via WhatsApp API

        return redirect()->back()->with('success', 'Template message berhasil dikirim');
    }

    public function sendBlast()
    {
        $blastTarget = $this->request->getPost('blast_target');
        $packageId = $this->request->getPost('package_id');
        $phoneNumbers = $this->request->getPost('phone_numbers');
        $blastMessage = $this->request->getPost('blast_message');

        // Validate required fields
        if (!$blastTarget) {
            return redirect()->back()->with('error', 'Target blast harus dipilih');
        }

        if (!$blastMessage) {
            return redirect()->back()->with('error', 'Pesan blast harus diisi');
        }

        if ($blastTarget === 'by_package' && !$packageId) {
            return redirect()->back()->with('error', 'Paket harus dipilih');
        }

        if ($blastTarget === 'custom_list' && !$phoneNumbers) {
            return redirect()->back()->with('error', 'Daftar nomor telepon harus diisi');
        }

        // TODO: Implement actual blast sending logic

        return redirect()->back()->with('success', 'Blast message berhasil dikirim');
    }
    public function countTarget()
    {
        $target = $this->request->getPost('target');
        $packageId = $this->request->getPost('package_id');

        $count = 0;
        $customerModel = new \App\Models\CustomerModel();

        try {
            switch ($target) {
                case 'all_customers':
                    $count = $customerModel->countAllResults();
                    break;
                case 'active_customers':
                    $count = $customerModel->where('status_tagihan', 1)->countAllResults();
                    break;
                case 'overdue_customers':
                    // Get customers with unpaid invoices
                    $db = \Config\Database::connect();
                    $builder = $db->table('customers c');
                    $builder->join('customer_invoices ci', 'ci.customer_id = c.id_customers', 'inner');
                    $builder->where('ci.status !=', 'paid');
                    $builder->where('c.status_tagihan', 1);
                    $builder->groupBy('c.id_customers');
                    $count = $builder->countAllResults();
                    break;
                case 'by_package':
                    if ($packageId) {
                        $count = $customerModel->where('id_paket', $packageId)
                            ->where('status_tagihan', 1)
                            ->countAllResults();
                    }
                    break;
            }
        } catch (\Exception $e) {
            log_message('error', 'Error counting target customers: ' . $e->getMessage());
            $count = 0;
        }

        return $this->response->setJSON([
            'success' => true,
            'count' => $count
        ]);
    }
    public function previewTarget()
    {
        $target = $this->request->getPost('target');
        $packageId = $this->request->getPost('package_id');
        $phoneNumbers = $this->request->getPost('phone_numbers');

        $preview = [];
        $targetName = '';
        $customerModel = new \App\Models\CustomerModel();

        try {
            switch ($target) {
                case 'all_customers':
                    $targetName = 'Semua Pelanggan';
                    $customers = $customerModel->select('nama_pelanggan as name, telepphone as phone')
                        ->limit(10)
                        ->findAll();
                    foreach ($customers as $customer) {
                        if (!empty($customer['phone'])) {
                            $preview[] = [
                                'name' => $customer['name'] ?: 'N/A',
                                'phone' => $customer['phone']
                            ];
                        }
                    }
                    break;

                case 'active_customers':
                    $targetName = 'Pelanggan Aktif';
                    $customers = $customerModel->select('nama_pelanggan as name, telepphone as phone')
                        ->where('status_tagihan', 1)
                        ->limit(10)
                        ->findAll();
                    foreach ($customers as $customer) {
                        if (!empty($customer['phone'])) {
                            $preview[] = [
                                'name' => $customer['name'] ?: 'N/A',
                                'phone' => $customer['phone']
                            ];
                        }
                    }
                    break;

                case 'overdue_customers':
                    $targetName = 'Pelanggan Menunggak';
                    $db = \Config\Database::connect();
                    $builder = $db->table('customers c');
                    $builder->select('c.nama_pelanggan as name, c.telepphone as phone');
                    $builder->join('customer_invoices ci', 'ci.customer_id = c.id_customers', 'inner');
                    $builder->where('ci.status !=', 'paid');
                    $builder->where('c.status_tagihan', 1);
                    $builder->groupBy('c.id_customers');
                    $builder->limit(10);
                    $result = $builder->get()->getResultArray();

                    foreach ($result as $customer) {
                        if (!empty($customer['phone'])) {
                            $preview[] = [
                                'name' => $customer['name'] ?: 'N/A',
                                'phone' => $customer['phone']
                            ];
                        }
                    }
                    break;

                case 'by_package':
                    $targetName = 'Berdasarkan Paket';
                    if ($packageId) {
                        $customers = $customerModel->select('nama_pelanggan as name, telepphone as phone')
                            ->where('id_paket', $packageId)
                            ->where('status_tagihan', 1)
                            ->limit(10)
                            ->findAll();
                        foreach ($customers as $customer) {
                            if (!empty($customer['phone'])) {
                                $preview[] = [
                                    'name' => $customer['name'] ?: 'N/A',
                                    'phone' => $customer['phone']
                                ];
                            }
                        }
                    }
                    break;

                case 'custom_list':
                    $targetName = 'Daftar Custom';
                    $numbers = explode("\n", $phoneNumbers);
                    foreach ($numbers as $index => $number) {
                        if (trim($number)) {
                            $preview[] = ['name' => 'Custom #' . ($index + 1), 'phone' => trim($number)];
                        }
                        if (count($preview) >= 10) break;
                    }
                    break;
            }
        } catch (\Exception $e) {
            log_message('error', 'Error previewing target customers: ' . $e->getMessage());
            $preview = [];
        }

        return $this->response->setJSON([
            'success' => true,
            'target_name' => $targetName,
            'count' => count($preview),
            'preview' => array_slice($preview, 0, 10)
        ]);
    }
    public function saveTemplates()
    {
        // Get all template data from the form
        $billReminder = $this->request->getPost('bill_reminder');
        $billPaid = $this->request->getPost('bill_paid');
        $newCustomer = $this->request->getPost('new_customer');
        $isolirReminder = $this->request->getPost('isolir_reminder');
        $isolirOpen = $this->request->getPost('isolir_open');

        // Handle upload gambar bill_reminder_image
        $billReminderImage = null;
        $file = $this->request->getFile('bill_reminder_image');
        if ($file && $file->isValid() && !$file->hasMoved()) {
            $newName = 'bill_reminder_' . time() . '.' . $file->getExtension();
            $uploadPath = WRITEPATH . 'uploads/';
            if (!is_dir($uploadPath)) {
                mkdir($uploadPath, 0777, true);
            }
            $file->move($uploadPath, $newName);
            $billReminderImage = $newName;
        }

        // Validate templates are not empty
        $templatesCount = 0;
        if (!empty($billReminder)) $templatesCount++;
        if (!empty($billPaid)) $templatesCount++;
        if (!empty($newCustomer)) $templatesCount++;
        if (!empty($isolirReminder)) $templatesCount++;
        if (!empty($isolirOpen)) $templatesCount++;

        try {
            $db = \Config\Database::connect();

            // Check if templates record exists
            $query = $db->query("SELECT * FROM whatsapp_templates WHERE id = 1");
            $result = $query->getRow();

            $templateData = [
                'bill_reminder' => $billReminder,
                'bill_paid' => $billPaid,
                'new_customer' => $newCustomer,
                'isolir_reminder' => $isolirReminder,
                'isolir_open' => $isolirOpen,
                'updated_at' => date('Y-m-d H:i:s')
            ];
            // Simpan path gambar jika ada upload baru
            if ($billReminderImage) {
                $templateData['bill_reminder_image'] = $billReminderImage;
            } elseif ($result && isset($result->bill_reminder_image)) {
                // Jika tidak upload baru, gunakan gambar lama
                $templateData['bill_reminder_image'] = $result->bill_reminder_image;
            }

            if ($result) {
                // Update existing record
                $builder = $db->table('whatsapp_templates');
                $builder->where('id', 1);
                $builder->update($templateData);
                $action = 'diperbarui';
            } else {
                // Insert new record
                $templateData['id'] = 1;
                $templateData['created_at'] = date('Y-m-d H:i:s');
                $builder = $db->table('whatsapp_templates');
                $builder->insert($templateData);
                $action = 'disimpan';
            }

            // Create detailed success message
            $successMessage = "Template WhatsApp berhasil {$action}! ";
            $successMessage .= "({$templatesCount} template aktif) ";
            $successMessage .= "pada " . date('d/m/Y H:i:s');

            return redirect()->back()->with('success', $successMessage);
        } catch (\Exception $e) {
            log_message('error', 'Failed to save WhatsApp templates: ' . $e->getMessage());
            return redirect()->back()->with('error', 'Gagal menyimpan template: ' . $e->getMessage());
        }
    }

    private function getExistingTemplates()
    {
        try {
            $db = \Config\Database::connect();

            // First, try to create the table if it doesn't exist
            $this->ensureTemplatesTableExists();

            $query = $db->query("SELECT * FROM whatsapp_templates WHERE id = 1");
            $result = $query->getRow();

            if ($result) {
                return (array) $result;
            }

            // Return default templates if no data found
            return $this->getDefaultTemplates();
        } catch (\Exception $e) {
            log_message('error', 'Failed to load WhatsApp templates: ' . $e->getMessage());
            return $this->getDefaultTemplates();
        }
    }

    private function ensureTemplatesTableExists()
    {
        try {
            $db = \Config\Database::connect();

            // Check if table exists
            $query = $db->query("SHOW TABLES LIKE 'whatsapp_templates'");
            if ($query->getNumRows() == 0) {
                // Create the table
                $sql = "CREATE TABLE `whatsapp_templates` (
                    `id` INT(11) UNSIGNED NOT NULL AUTO_INCREMENT,
                    `bill_reminder` TEXT NULL,
                    `bill_paid` TEXT NULL,
                    `new_customer` TEXT NULL,
                    `isolir_reminder` TEXT NULL,
                    `isolir_open` TEXT NULL,
                    `created_at` DATETIME NULL,
                    `updated_at` DATETIME NULL,
                    PRIMARY KEY (`id`)
                ) ENGINE=InnoDB";

                $db->query($sql);

                // Insert default templates
                $defaultTemplates = $this->getDefaultTemplates();
                $defaultTemplates['id'] = 1;
                $defaultTemplates['created_at'] = date('Y-m-d H:i:s');
                $defaultTemplates['updated_at'] = date('Y-m-d H:i:s');

                $builder = $db->table('whatsapp_templates');
                $builder->insert($defaultTemplates);
            }
        } catch (\Exception $e) {
            log_message('error', 'Failed to ensure templates table exists: ' . $e->getMessage());
        }
    }
    private function checkWhatsAppConnection()
    {
        try {
            $deviceModel = new \App\Models\WhatsappDeviceModel();
            $device = $deviceModel->first();

            if (!$device) {
                return 'Not Configured';
            }

            // Here you could implement actual connection check to WhatsApp API
            // For now, assume connected if device exists
            return 'Connected';
        } catch (\Exception $e) {
            return 'Disconnected';
        }
    }
    public function getMessageLogs()
    {
        $messageLogModel = new \App\Models\WhatsappMessageLogModel();
        $type = $this->request->getGet('type') ?: 'all';

        $pendingMessages = $messageLogModel->getPendingMessages(20);
        $errorMessages = $messageLogModel->getErrorMessages(20);

        return $this->response->setJSON([
            'success' => true,
            'pending_messages' => $pendingMessages,
            'error_messages' => $errorMessages,
            'pending_count' => $messageLogModel->getPendingCount(),
            'error_count' => $messageLogModel->getErrorCount()
        ]);
    }    // addTestMessage function removed - no more dummy data generation    // generateTestMessage function removed - no more dummy data generation

    private function getRandomErrorMessage()
    {
        $errors = [
            'WhatsApp device not connected',
            'Invalid phone number format',
            'Rate limit exceeded',
            'Message too long',
            'Template not found',
            'Network timeout'
        ];

        return $errors[array_rand($errors)];
    }
    public function retryMessage()
    {
        try {
            // Log raw request
            log_message('debug', 'retryMessage - Content-Type: ' . $this->request->getHeaderLine('Content-Type'));
            log_message('debug', 'retryMessage - Raw POST data: ' . json_encode($this->request->getPost()));

            // Get message_id from POST data
            $messageId = $this->request->getPost('message_id');

            log_message('debug', 'retryMessage - Message ID from POST: ' . ($messageId ?? 'NULL'));

            if ($messageId === null || $messageId === '') {
                log_message('error', 'retryMessage called without message_id');
                return $this->response->setJSON([
                    'success' => false,
                    'message' => 'Message ID is required'
                ]);
            }

            log_message('info', "Attempting to retry message ID: {$messageId}");

            $messageLogModel = new \App\Models\WhatsappMessageLogModel();
            $message = $messageLogModel->find($messageId);

            if (!$message) {
                log_message('error', "Message not found: ID {$messageId}");
                return $this->response->setJSON([
                    'success' => false,
                    'message' => 'Message not found'
                ]);
            }

            log_message('info', "Message found - Status: {$message['status']}, Phone: {$message['phone_number']}");

            // Only allow retry for pending or failed messages
            if (!in_array($message['status'], ['pending', 'failed'])) {
                log_message('warning', "Attempted to retry message with status: {$message['status']}");
                return $this->response->setJSON([
                    'success' => false,
                    'message' => 'Only pending or failed messages can be retried'
                ]);
            }

            // Get WhatsApp device configuration for the sender
            $deviceModel = new \App\Models\WhatsappDeviceModel();
            $device = $deviceModel->first(); // Get first available device

            if (!$device) {
                log_message('error', 'No WhatsApp device configured');
                return $this->response->setJSON([
                    'success' => false,
                    'message' => 'No WhatsApp device configured'
                ]);
            }

            log_message('info', "Using device: {$device['number']} to retry message");

            // Attempt to resend the message
            $result = $this->sendWhatsAppMessage(
                $device['number'],
                $message['phone_number'],
                $message['message_content'],
                $message['customer_name'],
                $message['customer_id']
            );

            if ($result['status'] === 'success') {
                // Update message status to sent
                $messageLogModel->update($messageId, [
                    'status' => 'sent',
                    'sent_at' => date('Y-m-d H:i:s'),
                    'error_message' => null,
                    'updated_at' => date('Y-m-d H:i:s')
                ]);

                log_message('info', "Message retry successful for ID: {$messageId}");

                return $this->response->setJSON([
                    'success' => true,
                    'message' => 'Message sent successfully'
                ]);
            } else {
                // Update with new error message
                $messageLogModel->update($messageId, [
                    'status' => 'failed',
                    'error_message' => $result['message'] ?? 'Retry failed',
                    'updated_at' => date('Y-m-d H:i:s')
                ]);

                log_message('error', "Message retry failed for ID: {$messageId} - " . ($result['message'] ?? 'Unknown error'));

                return $this->response->setJSON([
                    'success' => false,
                    'message' => 'Retry failed: ' . ($result['message'] ?? 'Unknown error')
                ]);
            }
        } catch (\Exception $e) {
            log_message('error', 'Error in retryMessage: ' . $e->getMessage());
            log_message('error', 'Stack trace: ' . $e->getTraceAsString());
            return $this->response->setJSON([
                'success' => false,
                'message' => 'System error: ' . $e->getMessage()
            ]);
        }
    }

    public function removeMessage()
    {
        try {
            // Log raw request
            log_message('debug', 'removeMessage - Content-Type: ' . $this->request->getHeaderLine('Content-Type'));
            log_message('debug', 'removeMessage - Raw POST data: ' . json_encode($this->request->getPost()));

            // Get message_id from POST data
            $messageId = $this->request->getPost('message_id');
            log_message('debug', 'removeMessage - Message ID from POST: ' . ($messageId ?? 'NULL'));

            if ($messageId === null || $messageId === '') {
                log_message('error', 'removeMessage called without message_id');
                return $this->response->setJSON([
                    'success' => false,
                    'message' => 'Message ID is required'
                ]);
            }

            $messageId = (int)$messageId;
            if ($messageId <= 0) {
                log_message('error', 'removeMessage called with invalid message_id: ' . $messageId);
                return $this->response->setJSON([
                    'success' => false,
                    'message' => 'Invalid Message ID'
                ]);
            }

            log_message('info', "Attempting to remove message ID: {$messageId}");

            $messageLogModel = new \App\Models\WhatsappMessageLogModel();
            $message = $messageLogModel->find($messageId);

            if (!$message) {
                log_message('error', "Message not found: ID {$messageId}");
                return $this->response->setJSON([
                    'success' => false,
                    'message' => 'Message not found'
                ]);
            }

            log_message('info', "Message found - Status: {$message['status']}, Phone: {$message['phone_number']}");

            // Only allow removal of pending or failed messages
            if (!in_array($message['status'], ['pending', 'failed'])) {
                log_message('warning', "Attempted to remove message with status: {$message['status']}");
                return $this->response->setJSON([
                    'success' => false,
                    'message' => 'Only pending or failed messages can be removed'
                ]);
            }

            // Delete the message from database
            $result = $messageLogModel->delete($messageId);

            if ($result) {
                log_message('info', "Message removed successfully: ID {$messageId}, Phone: {$message['phone_number']}");

                return $this->response->setJSON([
                    'success' => true,
                    'message' => 'Message removed successfully'
                ]);
            } else {
                log_message('error', "Failed to delete message from database: ID {$messageId}");
                return $this->response->setJSON([
                    'success' => false,
                    'message' => 'Failed to remove message from database'
                ]);
            }
        } catch (\Exception $e) {
            log_message('error', 'Error in removeMessage: ' . $e->getMessage());
            log_message('error', 'Stack trace: ' . $e->getTraceAsString());
            return $this->response->setJSON([
                'success' => false,
                'message' => 'System error: ' . $e->getMessage()
            ]);
        }
    }

    private function getDefaultTemplates()
    {
        return [
            'bill_reminder' => "```{company}```\n\n_Halo {customer},_\nTagihan Anda jatuh tempo pada:\n\n*Tanggal*: {tanggal}\n*Total Tagihan*: {tagihan}\n*Periode*: {periode}\n\n{bank_data}\n\n{link_payment}\n\n_Abaikan pesan ini jika Anda sudah melakukan pembayaran_\n\n*Terima kasih*",
            'bill_paid' => "```{company}```\n\n_Halo {customer},_\n\nTerima kasih sudah melakukan pembayaran\n\n*No Invoice*: {no_invoice}\n*Tanggal*: {tanggal}\n*Jumlah pembayaran*: {total}\n*Tunggakan*: {tunggakan}\n*Periode*: {periode}\n\n*Terima kasih*",
            'new_customer' => "```{company}```\n\n_Halo {customer},_\n\nTerima kasih sudah menjadi pelanggan kami :\n\nPaket : {paket},\nHarga : {harga},\nBandwidth : {bandwidth},\nTanggal Jatuh tempo :  {tanggal}\n\njika ada kendala silahkan hubungi kami\n\n*Terima kasih*",
            'isolir_reminder' => "```{company}```\n\n_Halo {customer},_\n\nLayanan Internet Anda telah di isolir otomatis oleh sistem, karena anda belum melakukan pembayaran tagihan.\nberikut informasi tagihan Anda :\n\n*Paket* : {paket},\n*Total Tagihan*: {tagihan}\n*Periode*: {periode}\n*Tanggal Jatuh tempo* :  {tanggal}\n\nInfo Pembayaran, transfer sesuai tagihan yang tertera pada pesan ini : \n\n{bank_data}\n\n{link_payment}\n\natau bisa melakukan pembayaran tagihan secara langsung ke rumah.\n_Segera lakukan pembayaran, agar internet Anda bisa digunakan kembali_\n\n*Terima kasih*",
            'isolir_open' => "```{company}```\n\n_Halo {customer},_\n\nTerima kasih sudah melakukan pembayaran, sistem telah membuka isolir internet Anda .\n\n*Terima kasih*"
        ];
    }
    /**
     * Simulate demo message sending for testing without real API calls
     */
    private function simulateDemoMessage($sender, $recipient, $message, $customerName, $customerId)
    {
        $whatsappConfig = new \Config\WhatsApp();

        // Simulate processing time
        $demoSettings = isset($whatsappConfig->demoSettings) ? $whatsappConfig->demoSettings : ['delay_min' => 1, 'delay_max' => 2, 'success_rate' => 100];
        $delay = rand($demoSettings['delay_min'], $demoSettings['delay_max']);
        sleep($delay);

        // Simulate success/failure based on configured success rate
        $success = rand(1, 100) <= $demoSettings['success_rate'];

        if ($success) {
            log_message('info', 'DEMO MODE: Message sent successfully to ' . $recipient);

            // Log successful demo message
            $logModel = new \App\Models\WhatsappMessageLogModel();
            $logModel->insert([
                'customer_id' => $customerId,
                'customer_name' => $customerName,
                'phone_number' => $recipient,
                'template_type' => 'text_message',
                'message_content' => $message,
                'status' => 'sent',
                'error_message' => null,
                'sent_at' => date('Y-m-d H:i:s')
            ]);

            return [
                'status' => 'success',
                'message' => 'Pesan berhasil dikirim (Demo Mode)',
                'demo_mode' => true
            ];
        } else {
            $errorMessages = [
                'Demo: Network timeout',
                'Demo: Invalid phone number',
                'Demo: Rate limit exceeded',
                'Demo: Device not connected'
            ];
            $errorMessage = $errorMessages[array_rand($errorMessages)];

            log_message('error', 'DEMO MODE: ' . $errorMessage);

            // Log failed demo message
            $logModel = new \App\Models\WhatsappMessageLogModel();
            $logModel->insert([
                'customer_id' => $customerId,
                'customer_name' => $customerName,
                'phone_number' => $recipient,
                'template_type' => 'text_message',
                'message_content' => $message,
                'status' => 'failed',
                'error_message' => $errorMessage,
                'sent_at' => null
            ]);

            return [
                'status' => 'failed',
                'message' => $errorMessage,
                'demo_mode' => true
            ];
        }
    }
    /**
     * Try primary API only (backup services disabled per user request)
     */
    private function tryMultipleApiServices($sender, $recipient, $message, $device, $whatsappConfig)
    {
        // Send using primary API only (backup services disabled per user request)
        $result = $this->sendToSingleApi($sender, $recipient, $message, $device, $whatsappConfig);

        if ($result['status'] === 'success') {
            return $result;
        }

        // If primary fails, return the failure result
        log_message('error', 'WhatsApp API failed: ' . json_encode($result));
        return [
            'status' => 'failed',
            'message' => 'Layanan WhatsApp API gagal. Coba lagi nanti.',
            'error' => $result
        ];
    }

    /**
     * Send message using single API (primary)
     */
    private function sendToSingleApi($sender, $recipient, $message, $device, $whatsappConfig)
    {
        return $this->sendToApiUrl($whatsappConfig->apiUrl, $sender, $recipient, $message, $device, $whatsappConfig);
    }
    /**
     * Send message to specific API URL with enhanced error handling
     */
    private function sendToApiUrl($url, $sender, $recipient, $message, $device, $whatsappConfig)
    {
        try {
            // Determine API service configuration based on URL
            $serviceConfig = null;
            foreach ($whatsappConfig->apiServices as $serviceName => $config) {
                if (strpos($url, parse_url($config['url'], PHP_URL_HOST)) !== false) {
                    $serviceConfig = $config;
                    break;
                }
            }

            // Default to Wamoo configuration if no specific service found
            if (!$serviceConfig) {
                $serviceConfig = $whatsappConfig->apiServices['wamoo'];
            }

            // Prepare data based on service configuration
            $requestData = [];
            foreach ($serviceConfig['fields'] as $serviceField => $internalField) {
                switch ($internalField) {
                    case 'api_key':
                        $requestData[$serviceField] = $device['api_key'];
                        break;
                    case 'sender':
                        $requestData[$serviceField] = $sender;
                        break;
                    case 'number':
                        $requestData[$serviceField] = $recipient;
                        break;
                    case 'message':
                        $requestData[$serviceField] = $message;
                        break;
                }
            }

            if ($whatsappConfig->logRequests) {
                log_message('info', 'WhatsApp API Request to ' . $url . ' (' . $serviceConfig['method'] . '): ' . json_encode($requestData));
            }

            // Handle different request methods
            if ($serviceConfig['method'] === 'GET') {
                // For GET requests (like Wamoo), append query parameters to URL
                $queryParams = http_build_query($requestData);
                $fullUrl = $url . '?' . $queryParams;

                $ch = curl_init($fullUrl);
                curl_setopt($ch, CURLOPT_HTTPGET, true);
            } else {
                // For POST requests
                $ch = curl_init($url);
                curl_setopt($ch, CURLOPT_POST, 1);

                if ($serviceConfig['data_format'] === 'json') {
                    curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($requestData));
                    curl_setopt($ch, CURLOPT_HTTPHEADER, ['Content-Type: application/json']);
                } else {
                    // Form data
                    curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query($requestData));
                    curl_setopt($ch, CURLOPT_HTTPHEADER, ['Content-Type: application/x-www-form-urlencoded']);
                }
            }

            // Set common cURL options
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
            curl_setopt($ch, CURLOPT_TIMEOUT, $whatsappConfig->timeout);
            curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, $whatsappConfig->connectTimeout);
            curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
            curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
            curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, false);
            curl_setopt($ch, CURLOPT_USERAGENT, 'WhatsApp-Gateway/2.0');
            curl_setopt($ch, CURLOPT_ENCODING, ''); // Enable compression

            $response = curl_exec($ch);
            $httpcode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
            $curlError = curl_error($ch);
            $curlInfo = curl_getinfo($ch);
            curl_close($ch);

            if ($whatsappConfig->logResponses) {
                log_message('info', 'WhatsApp API Response from ' . $url . ': HTTP ' . $httpcode . ' - ' . $response);
            }

            return $this->processApiResponse($response, $httpcode, $curlError, $curlInfo, $url);
        } catch (\Exception $e) {
            log_message('error', 'Exception in sendToApiUrl: ' . $e->getMessage());
            return [
                'status' => 'error',
                'message' => 'Error saat mengirim ke API: ' . $e->getMessage()
            ];
        }
    }

    /**
     * Send WhatsApp message using WhatsApp Service
     */
    private function sendWhatsAppMessage($sender, $recipient, $message, $customerName = '', $customerId = null)
    {
        try {
            // Use WhatsApp Service to send message
            $whatsappService = new \App\Services\WhatsAppService();
            $result = $whatsappService->sendMessage($recipient, $message, $sender);

            // Return standardized response
            return [
                'status' => $result['success'] ? 'success' : 'failed',
                'message' => $result['message'] ?? ($result['success'] ? 'Message sent successfully' : 'Failed to send message')
            ];
        } catch (\Exception $e) {
            log_message('error', 'sendWhatsAppMessage error: ' . $e->getMessage());
            return [
                'status' => 'failed',
                'message' => 'Error sending message: ' . $e->getMessage()
            ];
        }
    }

    /**
     * Process API response with enhanced error handling
     */
    private function processApiResponse($response, $httpcode, $curlError, $curlInfo, $url)
    {
        $status = 'failed';
        $responseData = null;
        $errorMessage = '';

        if ($curlError) {
            // Handle specific cURL errors with user-friendly messages
            if (strpos($curlError, 'timeout') !== false || strpos($curlError, 'timed out') !== false) {
                $errorMessage = 'WhatsApp API tidak merespons dalam waktu yang ditentukan. Server mungkin sedang sibuk.';
            } elseif (strpos($curlError, 'resolve') !== false) {
                $errorMessage = 'Tidak dapat menemukan server WhatsApp. Periksa koneksi internet Anda.';
            } elseif (strpos($curlError, 'connect') !== false) {
                $errorMessage = 'Gagal terhubung ke server WhatsApp. Server mungkin sedang down.';
            } elseif (strpos($curlError, 'SSL') !== false) {
                $errorMessage = 'Masalah sertifikat SSL dengan server WhatsApp.';
            } else {
                $errorMessage = 'Koneksi ke WhatsApp API bermasalah: ' . $curlError;
            }
            log_message('error', 'cURL Error for ' . $url . ': ' . $curlError);
        } elseif ($httpcode == 200) {
            $responseData = json_decode($response, true);
            if (is_array($responseData)) {
                if (isset($responseData['status']) && $responseData['status'] == true) {
                    $status = 'success';
                } elseif (isset($responseData['success']) && $responseData['success'] == true) {
                    $status = 'success';
                } else {
                    $errorMessage = 'API Response: ' . ($responseData['msg'] ?? $responseData['message'] ?? $responseData['error'] ?? $response);
                }
            } else {
                // Some APIs return plain text success messages
                if (strpos(strtolower($response), 'success') !== false || strpos($response, 'sent') !== false) {
                    $status = 'success';
                } else {
                    $errorMessage = 'Response tidak valid: ' . $response;
                }
            }
        } elseif ($httpcode == 0) {
            $errorMessage = 'Tidak dapat terhubung ke server WhatsApp. Periksa koneksi internet atau coba lagi nanti.';
        } elseif ($httpcode >= 400 && $httpcode < 500) {
            $responseData = json_decode($response, true);
            if (is_array($responseData) && isset($responseData['message'])) {
                $errorMessage = 'API Error: ' . $responseData['message'];
            } else {
                $errorMessage = 'WhatsApp API error: Permintaan tidak valid (HTTP ' . $httpcode . ')';
            }
        } elseif ($httpcode >= 500) {
            $errorMessage = 'Server WhatsApp API mengalami gangguan (HTTP ' . $httpcode . '). Coba lagi dalam beberapa menit.';
        } else {
            $errorMessage = 'HTTP Error ' . $httpcode . ': ' . ($response ?: 'No response');
        }

        return [
            'status' => $status,
            'message' => $status === 'success' ? 'Pesan berhasil dikirim' : $errorMessage,
            'response' => $responseData,
            'http_code' => $httpcode,
            'api_url' => $url
        ];
    }

    /**
     * Check if WhatsApp device is connected
     */
    private function checkDeviceConnection($sender, $apiKey)
    {
        try {
            $baseUrl = getenv('WHATSAPP_BASE_URL') ?: 'https://wazero.kimonet.my.id';
            $url = $baseUrl . '/generate-qr'; // Gunakan endpoint yang sama

            $data = [
                'device' => $sender,
                'api_key' => $apiKey,
                'force' => 'false'
            ];

            $queryParams = http_build_query($data);
            $fullUrl = $url . '?' . $queryParams;

            $ch = curl_init($fullUrl);
            curl_setopt($ch, CURLOPT_HTTPGET, true);
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
            curl_setopt($ch, CURLOPT_TIMEOUT, 10);
            curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 5);
            curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
            curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, false);
            curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);

            $response = curl_exec($ch);
            $httpcode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
            curl_close($ch);

            if ($response) {
                $result = json_decode($response, true);

                if ($result) {
                    // HTTP 400 dengan "already connected" berarti sudah terhubung
                    if ($httpcode == 400 && isset($result['msg'])) {
                        if (
                            stripos($result['msg'], 'already connected') !== false ||
                            stripos($result['msg'], 'sudah terhubung') !== false
                        ) {
                            log_message('info', 'Device connection check: Already connected (HTTP 400)');
                            return true;
                        }
                    }

                    // HTTP 200 - Check status field
                    if ($httpcode == 200) {
                        // Status true = connected
                        if (isset($result['status']) && $result['status'] === true) {
                            return true;
                        }

                        // Connected field
                        if (isset($result['connected']) && $result['connected'] === true) {
                            return true;
                        }

                        // State field
                        if (isset($result['state']) && strtolower($result['state']) === 'connected') {
                            return true;
                        }

                        // Message contains "already connected"
                        if (isset($result['msg']) || isset($result['message'])) {
                            $message = $result['msg'] ?? $result['message'] ?? '';
                            if (
                                stripos($message, 'already connected') !== false ||
                                stripos($message, 'sudah terhubung') !== false
                            ) {
                                return true;
                            }
                        }
                    }
                }
            }

            log_message('warning', 'Device connection check failed: HTTP ' . $httpcode . ' - ' . $response);
            return false;
        } catch (\Exception $e) {
            log_message('error', 'Error checking device connection: ' . $e->getMessage());
            return false; // Assume disconnected if check fails
        }
    }
}
