<?php

namespace App\Controllers;

use App\Controllers\BaseController;
use App\Models\InvoiceModel;
use App\Models\CustomerModel;
use App\Models\WhatsappNotifSettingModel;
use App\Models\WhatsappMessageLogModel;
use App\Models\CompanyModel;

class WhatsappBillingNotification extends BaseController
{
    protected $invoiceModel;
    protected $customerModel;
    protected $notifModel;
    protected $messageLogModel;
    protected $companyModel;

    public function __construct()
    {
        $this->invoiceModel = new InvoiceModel();
        $this->customerModel = new CustomerModel();
        $this->notifModel = new WhatsappNotifSettingModel();
        $this->messageLogModel = new WhatsappMessageLogModel();
        $this->companyModel = new CompanyModel();
    }

    /**
     * Main method untuk cron job - mengirim semua notifikasi tagihan
     * Dapat dipanggil via cron atau manual
     */
    public function sendAllNotifications()
    {
        // Validasi secret key untuk keamanan (opsional tapi direkomendasikan)
        $secret = $this->request->getGet('secret');
        $expectedSecret = getenv('WHATSAPP_CRON_SECRET');

        // Jika secret key diset di .env, maka wajib cocok
        if (!empty($expectedSecret) && $secret !== $expectedSecret) {
            log_message('warning', 'WhatsApp Billing Notification: Unauthorized access attempt');
            return $this->response->setJSON([
                'status' => 'error',
                'message' => 'Unauthorized access. Invalid secret key.'
            ])->setStatusCode(403);
        }

        log_message('info', 'WhatsApp Billing Notification: Starting automated notification process');

        try {
            $results = [
                'h_day' => $this->sendDueDateNotifications(),      // H (hari jatuh tempo)
                'h_minus_1' => $this->sendPreDueDateNotifications(1),   // H-1
                'h_minus_3' => $this->sendPreDueDateNotifications(3),   // H-3  
                'h_minus_7' => $this->sendPreDueDateNotifications(7),   // H-7
                'payment_confirmations' => $this->sendPaymentConfirmations() // Konfirmasi pembayaran
            ];

            $totalSent = array_sum(array_map(function ($result) {
                return $result['sent'] ?? 0;
            }, $results));

            log_message('info', "WhatsApp Billing Notification: Process completed. Total messages sent: {$totalSent}");

            return $this->response->setJSON([
                'status' => 'success',
                'message' => "Notification process completed. Total messages sent: {$totalSent}",
                'details' => $results
            ]);
        } catch (\Exception $e) {
            log_message('error', 'WhatsApp Billing Notification Error: ' . $e->getMessage());
            return $this->response->setJSON([
                'status' => 'error',
                'message' => 'Failed to send notifications: ' . $e->getMessage()
            ]);
        }
    }

    /**
     * Kirim notifikasi pada hari jatuh tempo (H)
     */
    public function sendDueDateNotifications()
    {
        if (!$this->isNotificationEnabled('notif_invoice')) {
            return ['status' => 'disabled', 'sent' => 0, 'message' => 'Due date notifications are disabled'];
        }

        $today = date('Y-m-d');
        $currentPeriod = date('Y-m');

        // Ambil invoice yang jatuh tempo hari ini dan belum lunas
        $dueInvoices = $this->invoiceModel
            ->select('customer_invoices.*, customers.nama_pelanggan, customers.telepphone, customers.nomor_layanan')
            ->join('customers', 'customers.id_customers = customer_invoices.customer_id', 'left')
            ->where('customer_invoices.due_date', $today)
            ->where('customer_invoices.status !=', 'paid')
            ->where('customer_invoices.periode', $currentPeriod)
            ->where('customers.telepphone IS NOT NULL')
            ->where('customers.telepphone !=', '')
            ->findAll();

        $sent = 0;
        foreach ($dueInvoices as $invoice) {
            if ($this->sendBillReminderMessage($invoice, 'due_date')) {
                $sent++;
            }
        }

        return ['status' => 'success', 'sent' => $sent, 'type' => 'due_date'];
    }

    /**
     * Kirim notifikasi sebelum jatuh tempo (H-1, H-3, H-7)
     */
    public function sendPreDueDateNotifications($daysBefore)
    {
        if (!$this->isNotificationEnabled('notif_reminder')) {
            return ['status' => 'disabled', 'sent' => 0, 'message' => "H-{$daysBefore} notifications are disabled"];
        }

        $targetDate = date('Y-m-d', strtotime("+{$daysBefore} days"));
        $currentPeriod = date('Y-m');

        // Ambil invoice yang akan jatuh tempo dalam X hari dan belum lunas
        $upcomingInvoices = $this->invoiceModel
            ->select('customer_invoices.*, customers.nama_pelanggan, customers.telepphone, customers.nomor_layanan')
            ->join('customers', 'customers.id_customers = customer_invoices.customer_id', 'left')
            ->where('customer_invoices.due_date', $targetDate)
            ->where('customer_invoices.status !=', 'paid')
            ->where('customer_invoices.periode', $currentPeriod)
            ->where('customers.telepphone IS NOT NULL')
            ->where('customers.telepphone !=', '')
            ->findAll();

        $sent = 0;
        foreach ($upcomingInvoices as $invoice) {
            // Cek apakah sudah pernah dikirim notifikasi untuk tipe ini
            if (!$this->hasBeenNotified($invoice['customer_id'], $invoice['periode'], "reminder_h_minus_{$daysBefore}")) {
                if ($this->sendBillReminderMessage($invoice, "h_minus_{$daysBefore}")) {
                    $sent++;
                    $this->markAsNotified($invoice['customer_id'], $invoice['periode'], "reminder_h_minus_{$daysBefore}");
                }
            }
        }

        return ['status' => 'success', 'sent' => $sent, 'type' => "h_minus_{$daysBefore}"];
    }

    /**
     * Kirim konfirmasi pembayaran untuk tagihan yang baru dibayar
     */
    public function sendPaymentConfirmations()
    {
        if (!$this->isNotificationEnabled('notif_payment')) {
            return ['status' => 'disabled', 'sent' => 0, 'message' => 'Payment confirmation notifications are disabled'];
        }

        $today = date('Y-m-d');

        // Ambil invoice yang dibayar hari ini
        $paidToday = $this->invoiceModel
            ->select('customer_invoices.*, customers.nama_pelanggan, customers.telepphone, customers.nomor_layanan')
            ->join('customers', 'customers.id_customers = customer_invoices.customer_id', 'left')
            ->where('customer_invoices.status', 'paid')
            ->where('DATE(customer_invoices.updated_at)', $today)
            ->where('customers.telepphone IS NOT NULL')
            ->where('customers.telepphone !=', '')
            ->findAll();

        $sent = 0;
        foreach ($paidToday as $invoice) {
            // Cek apakah sudah pernah dikirim konfirmasi pembayaran
            if (!$this->hasBeenNotified($invoice['customer_id'], $invoice['periode'], 'payment_confirmation')) {
                if ($this->sendPaymentConfirmationMessage($invoice)) {
                    $sent++;
                    $this->markAsNotified($invoice['customer_id'], $invoice['periode'], 'payment_confirmation');
                }
            }
        }

        return ['status' => 'success', 'sent' => $sent, 'type' => 'payment_confirmation'];
    }

    /**
     * Kirim pesan pengingat tagihan
     */
    private function sendBillReminderMessage($invoice, $type)
    {
        try {
            $template = $this->getBillReminderTemplate();
            if (!$template) {
                log_message('error', 'Bill reminder template not found');
                return false;
            }
            $company = $this->companyModel->first();
            $companyName = $company['name'] ?? 'Nama Perusahaan';

            // Generate payment link
            $paymentLink = $this->generatePaymentLink($invoice['nomor_layanan'] ?? $invoice['invoice_no']);

            // Replace template variables (support {village}, {district}, {city}, {adderss})
            $customer = $this->customerModel->where('id_customers', $invoice['customer_id'])->first();

            // Calculate PPN and other fees
            $tarif = (float)($invoice['bill'] ?? 0);
            $ppnRate = 0; // Default 0%, can be configured
            $totalPpn = $tarif * ($ppnRate / 100);
            $diskon = (float)($invoice['discount'] ?? 0);
            $biayaLain = (float)($invoice['additional_fee'] ?? 0);
            $totalTagihan = $tarif + $totalPpn - $diskon + $biayaLain;

            $message = str_replace(
                [
                    '{company}',
                    '{customer}',
                    '{tanggal}',
                    '{tagihan}',
                    '{periode}',
                    '{no_layanan}',
                    '{link_payment}',
                    '{bank_data}',
                    '{village}',
                    '{district}',
                    '{city}',
                    '{adderss}',
                    '{paket}',
                    '{tarif}',
                    '{ppn}',
                    '{totalppn}',
                    '{diskon}',
                    '{biaya}'
                ],
                [
                    $companyName,
                    $invoice['nama_pelanggan'] ?? 'Pelanggan',
                    date('d/m/Y', strtotime($invoice['due_date'])),
                    'Rp ' . number_format($totalTagihan, 0, ',', '.'),
                    $this->formatPeriode($invoice['periode']),
                    $invoice['nomor_layanan'] ?? $invoice['invoice_no'],
                    $paymentLink,
                    $this->getBankData(),
                    $customer['village'] ?? '-',
                    $customer['district'] ?? '-',
                    $customer['city'] ?? '-',
                    $customer['address'] ?? '-',
                    $invoice['package'] ?? 'Paket',
                    'Rp ' . number_format($tarif, 0, ',', '.'),
                    $ppnRate . '%',
                    'Rp ' . number_format($totalPpn, 0, ',', '.'),
                    'Rp ' . number_format($diskon, 0, ',', '.'),
                    'Rp ' . number_format($biayaLain, 0, ',', '.')
                ],
                $template
            );

            // Tambahkan informasi tipe notifikasi pada pesan
            if ($type === 'due_date') {
                $message .= "\n\n⚠️ *Tagihan jatuh tempo HARI INI*";
            } elseif (strpos($type, 'h_minus') === 0) {
                $days = str_replace('h_minus_', '', $type);
                $message .= "\n\n📅 *Tagihan akan jatuh tempo dalam {$days} hari*";
            }

            // Log pesan ke database
            $this->logMessage([
                'customer_id' => $invoice['customer_id'],
                'customer_name' => $invoice['nama_pelanggan'],
                'phone_number' => $invoice['telepphone'],
                'template_type' => 'bill_reminder',
                'message_content' => $message,
                'notification_type' => $type,
                'status' => 'pending'
            ]);

            // Kirim via WhatsApp API (implementasi tergantung sistem yang digunakan)
            return $this->sendWhatsAppMessage($invoice['telepphone'], $message);
        } catch (\Exception $e) {
            log_message('error', 'Failed to send bill reminder: ' . $e->getMessage());
            return false;
        }
    }

    /**
     * Kirim konfirmasi pembayaran
     */
    private function sendPaymentConfirmationMessage($invoice)
    {
        try {
            $template = $this->getPaymentConfirmationTemplate();
            if (!$template) {
                log_message('error', 'Payment confirmation template not found');
                return false;
            }

            $company = $this->companyModel->first();
            $companyName = $company['name'] ?? 'Nama Perusahaan';

            // Calculate PPN and other fees
            $tarif = (float)($invoice['bill'] ?? 0);
            $ppnRate = 0; // Default 0%, can be configured
            $totalPpn = $tarif * ($ppnRate / 100);
            $diskon = (float)($invoice['discount'] ?? 0);
            $biayaLain = (float)($invoice['additional_fee'] ?? 0);
            $totalTagihan = $tarif + $totalPpn - $diskon + $biayaLain;

            // Replace template variables
            $message = str_replace(
                ['{company}', '{customer}', '{no_invoice}', '{tanggal}', '{total}', '{periode}', '{paket}', '{tarif}', '{ppn}', '{totalppn}', '{diskon}', '{biaya}'],
                [
                    $companyName,
                    $invoice['nama_pelanggan'] ?? 'Pelanggan',
                    $invoice['invoice_no'],
                    date('d/m/Y'),
                    'Rp ' . number_format($totalTagihan, 0, ',', '.'),
                    $this->formatPeriode($invoice['periode']),
                    $invoice['package'] ?? 'Paket',
                    'Rp ' . number_format($tarif, 0, ',', '.'),
                    $ppnRate . '%',
                    'Rp ' . number_format($totalPpn, 0, ',', '.'),
                    'Rp ' . number_format($diskon, 0, ',', '.'),
                    'Rp ' . number_format($biayaLain, 0, ',', '.')
                ],
                $template
            );

            // Log pesan ke database
            $this->logMessage([
                'customer_id' => $invoice['customer_id'],
                'customer_name' => $invoice['nama_pelanggan'],
                'phone_number' => $invoice['telepphone'],
                'template_type' => 'bill_paid',
                'message_content' => $message,
                'notification_type' => 'payment_confirmation',
                'status' => 'pending'
            ]);

            // Kirim via WhatsApp API
            return $this->sendWhatsAppMessage($invoice['telepphone'], $message);
        } catch (\Exception $e) {
            log_message('error', 'Failed to send payment confirmation: ' . $e->getMessage());
            return false;
        }
    }

    /**
     * Cek apakah notifikasi tipe tertentu sudah diaktifkan
     */
    private function isNotificationEnabled($type)
    {
        $settings = $this->notifModel->orderBy('id', 'desc')->first();
        if (!$settings) {
            return false; // Default disabled jika tidak ada setting
        }

        return !empty($settings[$type]);
    }

    /**
     * Cek apakah customer sudah pernah dinotifikasi untuk periode dan tipe tertentu
     */
    private function hasBeenNotified($customerId, $periode, $notificationType)
    {
        $existing = $this->messageLogModel
            ->where('customer_id', $customerId)
            ->where('notification_type', $notificationType)
            ->where('DATE(created_at)', date('Y-m-d'))
            ->first();

        return !empty($existing);
    }

    /**
     * Tandai bahwa customer sudah dinotifikasi
     */
    private function markAsNotified($customerId, $periode, $notificationType)
    {
        // Log sudah dibuat di sendBillReminderMessage, tidak perlu action tambahan
        log_message('info', "Customer {$customerId} marked as notified for {$notificationType}");
    }

    /**
     * Get template pengingat tagihan
     */
    private function getBillReminderTemplate()
    {
        try {
            $db = \Config\Database::connect();
            $query = $db->query("SELECT bill_reminder FROM whatsapp_templates WHERE id = 1");
            $result = $query->getRow();

            return $result->bill_reminder ?? null;
        } catch (\Exception $e) {
            log_message('error', 'Failed to get bill reminder template: ' . $e->getMessage());
            return null;
        }
    }

    /**
     * Get template konfirmasi pembayaran
     */
    private function getPaymentConfirmationTemplate()
    {
        try {
            $db = \Config\Database::connect();
            $query = $db->query("SELECT bill_paid FROM whatsapp_templates WHERE id = 1");
            $result = $query->getRow();

            return $result->bill_paid ?? null;
        } catch (\Exception $e) {
            log_message('error', 'Failed to get payment confirmation template: ' . $e->getMessage());
            return null;
        }
    }

    /**
     * Get template by type (universal method)
     */
    private function getTemplateByType($type)
    {
        try {
            $db = \Config\Database::connect();
            $query = $db->query("SELECT content FROM whatsapp_templates WHERE type = ? AND is_active = 1", [$type]);
            $result = $query->getRow();

            return $result->content ?? null;
        } catch (\Exception $e) {
            log_message('error', "Failed to get template for type '{$type}': " . $e->getMessage());
            return null;
        }
    }

    /**
     * Format periode YYYY-MM menjadi "Bulan YYYY"
     */
    private function formatPeriode($periode)
    {
        if (!$periode || !preg_match('/^\d{4}-\d{2}$/', $periode)) {
            return $periode;
        }

        $monthNames = [
            '01' => 'Januari',
            '02' => 'Februari',
            '03' => 'Maret',
            '04' => 'April',
            '05' => 'Mei',
            '06' => 'Juni',
            '07' => 'Juli',
            '08' => 'Agustus',
            '09' => 'September',
            '10' => 'Oktober',
            '11' => 'November',
            '12' => 'Desember'
        ];

        $parts = explode('-', $periode);
        $year = $parts[0];
        $month = $parts[1];

        return ($monthNames[$month] ?? $month) . ' ' . $year;
    }

    /**
     * Log pesan ke database
     */
    private function logMessage($data)
    {
        try {
            $this->messageLogModel->insert($data);
        } catch (\Exception $e) {
            log_message('error', 'Failed to log WhatsApp message: ' . $e->getMessage());
        }
    }

    /**
     * Kirim pesan WhatsApp (implementasi tergantung sistem yang digunakan)
     */
    private function sendWhatsAppMessage($phoneNumber, $message)
    {
        try {
            // Ambil device WhatsApp yang aktif
            $deviceModel = new \App\Models\WhatsappDeviceModel();
            $device = $deviceModel->orderBy('id', 'desc')->first();

            if (!$device) {
                log_message('error', 'No WhatsApp device configured');
                return false;
            }

            // Format nomor telepon
            $phoneNumber = $this->formatPhoneNumber($phoneNumber);

            // Kirim via API menggunakan format GET yang benar
            $baseUrl = getenv('WHATSAPP_BASE_URL') ?: 'https://wazero.kimonet.my.id';
            $url = $baseUrl . '/send-message';
            $data = [
                'api_key' => $device['api_key'],
                'sender' => $device['number'],
                'number' => $phoneNumber,
                'message' => $message
            ];

            $queryParams = http_build_query($data);
            $fullUrl = $url . '?' . $queryParams;

            $ch = curl_init($fullUrl);
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
            curl_setopt($ch, CURLOPT_TIMEOUT, 30);
            curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 10);
            curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
            curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);

            $response = curl_exec($ch);
            $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
            $curlError = curl_error($ch);
            @curl_close($ch); // Suppress deprecation warning in PHP 8.4+

            if ($curlError) {
                log_message('error', "WhatsApp API CURL error: {$curlError}");
                return false;
            }

            if ($httpCode === 200) {
                $result = json_decode($response, true);
                if (isset($result['status']) && $result['status'] === true) {
                    log_message('info', "WhatsApp message sent successfully to {$phoneNumber}");
                    return true;
                } else {
                    log_message('error', "WhatsApp API error response: " . ($result['msg'] ?? $result['message'] ?? 'Unknown error'));
                }
            }

            log_message('error', "WhatsApp API error: HTTP {$httpCode}, Response: {$response}");
            return false;
        } catch (\Exception $e) {
            log_message('error', 'WhatsApp send error: ' . $e->getMessage());
            return false;
        }
    }

    /**
     * Format nomor telepon ke format internasional
     */
    private function formatPhoneNumber($phone)
    {
        // Hapus karakter non-digit
        $phone = preg_replace('/[^0-9]/', '', $phone);

        // Jika dimulai dengan 0, ganti dengan 62
        if (substr($phone, 0, 1) === '0') {
            $phone = '62' . substr($phone, 1);
        }

        // Jika belum diawali 62, tambahkan
        if (substr($phone, 0, 2) !== '62') {
            $phone = '62' . $phone;
        }

        return $phone;
    }

    /**
     * Method untuk testing manual
     */
    public function testNotification()
    {
        // Test dengan satu customer
        $testInvoice = $this->invoiceModel
            ->select('customer_invoices.*, customers.nama_pelanggan, customers.telepphone, customers.nomor_layanan')
            ->join('customers', 'customers.id_customers = customer_invoices.customer_id', 'left')
            ->where('customer_invoices.status !=', 'paid')
            ->where('customers.telepphone IS NOT NULL')
            ->where('customers.telepphone !=', '')
            ->first();

        if (!$testInvoice) {
            return $this->response->setJSON([
                'status' => 'error',
                'message' => 'No test invoice found'
            ]);
        }

        $result = $this->sendBillReminderMessage($testInvoice, 'test');

        return $this->response->setJSON([
            'status' => $result ? 'success' : 'error',
            'message' => $result ? 'Test message sent successfully' : 'Failed to send test message',
            'invoice' => $testInvoice['invoice_no'],
            'customer' => $testInvoice['nama_pelanggan']
        ]);
    }

    /**
     * Send isolir/isolation notifications
     */
    public function sendIsolirNotifications()
    {
        if (!$this->isNotificationEnabled('notif_other')) {
            return [
                'status' => 'skipped',
                'message' => 'Isolir notifications are disabled',
                'sent' => 0,
                'failed' => 0,
                'skipped' => 0
            ];
        }

        $sent = 0;
        $failed = 0;
        $skipped = 0;

        try {
            // Get customers that were isolated today
            $isolatedToday = $this->customerModel
                ->where('DATE(updated_at)', date('Y-m-d'))
                ->where('status', 'isolir')
                ->findAll();

            foreach ($isolatedToday as $customer) {
                // Skip if no phone
                if (empty($customer['telepphone'])) {
                    $skipped++;
                    continue;
                }

                // Check if already notified today
                if ($this->hasBeenNotified($customer['id'], 'isolir_notification', date('Y-m-d'))) {
                    $skipped++;
                    continue;
                }

                // Send notification
                if ($this->sendIsolirNotificationMessage($customer)) {
                    $this->markAsNotified($customer['id'], 'isolir_notification', date('Y-m-d'));
                    $sent++;
                } else {
                    $failed++;
                }
            }

            return [
                'status' => 'success',
                'sent' => $sent,
                'failed' => $failed,
                'skipped' => $skipped,
                'message' => "Sent {$sent} isolir notifications"
            ];
        } catch (\Exception $e) {
            log_message('error', 'Error sending isolir notifications: ' . $e->getMessage());
            return [
                'status' => 'error',
                'message' => $e->getMessage(),
                'sent' => $sent,
                'failed' => $failed,
                'skipped' => $skipped
            ];
        }
    }

    /**
     * Send isolir notification message to customer
     */
    private function sendIsolirNotificationMessage($customer)
    {
        try {
            $template = $this->getTemplateByType('isolir_reminder');
            if (!$template) {
                log_message('error', 'Isolir reminder template not found');
                return false;
            }

            $company = $this->companyModel->first();
            $companyName = $company['name'] ?? 'Nama Perusahaan';

            // Get unpaid invoices
            $unpaidInvoices = $this->invoiceModel
                ->where('customer_id', $customer['id'])
                ->where('status', 'unpaid')
                ->findAll();

            $totalUnpaid = array_sum(array_column($unpaidInvoices, 'bill'));
            $invoiceCount = count($unpaidInvoices);

            // Replace template variables
            $message = str_replace(
                [
                    '{company}',
                    '{customer}',
                    '{total}',
                    '{count}',
                    '{no_layanan}',
                    '{village}',
                    '{district}',
                    '{city}',
                    '{address}'
                ],
                [
                    $companyName,
                    $customer['nama_pelanggan'] ?? 'Pelanggan',
                    'Rp ' . number_format($totalUnpaid, 0, ',', '.'),
                    $invoiceCount,
                    $customer['nomor_layanan'] ?? '-',
                    $customer['village'] ?? '-',
                    $customer['district'] ?? '-',
                    $customer['city'] ?? '-',
                    $customer['address'] ?? '-'
                ],
                $template
            );

            // Log pesan ke database
            $this->logMessage([
                'customer_id' => $customer['id'],
                'customer_name' => $customer['nama_pelanggan'],
                'phone_number' => $customer['telepphone'],
                'template_type' => 'isolir_reminder',
                'message_content' => $message,
                'notification_type' => 'isolir_notification',
                'status' => 'pending'
            ]);

            // Send via WhatsApp API
            return $this->sendWhatsAppMessage($customer['telepphone'], $message);
        } catch (\Exception $e) {
            log_message('error', 'Failed to send isolir notification: ' . $e->getMessage());
            return false;
        }
    }

    /**
     * Method untuk mengirim notifikasi manual berdasarkan tipe
     */
    public function sendManualNotification($type = null)
    {
        if (!$type) {
            return $this->response->setJSON([
                'status' => 'error',
                'message' => 'Notification type required (due_date, h_minus_1, h_minus_3, h_minus_7, payment_confirmation, isolir)'
            ]);
        }

        switch ($type) {
            case 'due_date':
                $result = $this->sendDueDateNotifications();
                break;
            case 'h_minus_1':
                $result = $this->sendPreDueDateNotifications(1);
                break;
            case 'h_minus_3':
                $result = $this->sendPreDueDateNotifications(3);
                break;
            case 'h_minus_7':
                $result = $this->sendPreDueDateNotifications(7);
                break;
            case 'payment_confirmation':
                $result = $this->sendPaymentConfirmations();
                break;
            case 'isolir':
                $result = $this->sendIsolirNotifications();
                break;
            default:
                return $this->response->setJSON([
                    'status' => 'error',
                    'message' => 'Invalid notification type'
                ]);
        }
        return $this->response->setJSON($result);
    }

    /**
     * Generate payment link for billing check
     */
    private function generatePaymentLink($nomorLayanan)
    {
        if (empty($nomorLayanan)) {
            return '';
        }

        // Generate direct billing URL: domain.com/[nomor_layanan]
        return base_url($nomorLayanan);
    }

    /**
     * Get bank transfer information
     */
    private function getBankData()
    {
        // Ambil data bank aktif dari tabel banks
        $db = \Config\Database::connect();
        $banks = $db->table('banks')
            ->where('is_active', 1)
            ->get()
            ->getResult();

        if (!$banks || count($banks) === 0) {
            return "Data bank tidak tersedia.";
        }

        $result = "*Data Rekening Bank:*\n";
        foreach ($banks as $bank) {
            $result .= "- " . $bank->bank_name . " a.n " . $bank->account_holder . " (" . $bank->account_number . ")\n";
        }
        return $result;
    }
}
