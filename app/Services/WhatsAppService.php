<?php

namespace App\Services;

use Config\WhatsApp as WhatsAppConfig;

class WhatsAppService
{
    protected $config;
    protected $db;

    public function __construct()
    {
        $this->config = new WhatsAppConfig();
        $this->db = \Config\Database::connect();
    }

    /**
     * Send WhatsApp message
     */
    public function sendMessage($number, $message, $sender = null)
    {
        try {
            // Log the attempt
            log_message('info', 'Attempting to send WhatsApp message to: ' . $number);

            // Get device/API key from database
            $device = $this->getActiveDevice($sender);
            if (!$device) {
                log_message('error', 'No active WhatsApp device found');
                return [
                    'success' => false,
                    'message' => 'No active WhatsApp device configured'
                ];
            }

            // Prepare message data
            $messageData = [
                'number' => $this->formatNumber($number),
                'message' => $message,
                'sender' => $device['number'],
                'api_key' => $device['api_key']
            ];

            // Send message using configured service
            $result = $this->sendViaAPI($messageData);

            // Log the result
            $this->logMessage($number, $message, $result);

            return $result;
        } catch (\Exception $e) {
            log_message('error', 'WhatsApp send error: ' . $e->getMessage());
            return [
                'success' => false,
                'message' => 'Error sending WhatsApp: ' . $e->getMessage()
            ];
        }
    }

    /**
     * Send new customer notification
     */
    public function sendNewCustomerNotification($customerData)
    {
        try {
            // Get customer's phone number - check multiple possible field names
            $phoneNumber = $customerData['telepphone'] ?? $customerData['no_tlp'] ?? $customerData['no_hp'] ?? $customerData['phone'] ?? null;

            if (empty($phoneNumber)) {
                log_message('warning', 'Customer phone number is empty for customer: ' . ($customerData['nama_pelanggan'] ?? 'unknown'));
                return [
                    'success' => false,
                    'message' => 'Customer phone number is required'
                ];
            }

            // Get message template from database
            $message = $this->getNewCustomerTemplate($customerData);

            // Send message
            $result = $this->sendMessage($phoneNumber, $message);

            // Log to WhatsApp message logs table
            $this->logToMessageTable($customerData, $phoneNumber, $message, $result);

            return $result;
        } catch (\Exception $e) {
            log_message('error', 'Error sending new customer notification: ' . $e->getMessage());
            return [
                'success' => false,
                'message' => 'Error sending notification: ' . $e->getMessage()
            ];
        }
    }

    /**
     * Format new customer welcome message
     */
    private function formatNewCustomerMessage($customerData)
    {
        $name = $customerData['nama_pelanggan'] ?? 'Customer';
        $serviceNumber = $customerData['nomor_layanan'] ?? '-';
        $packageName = $customerData['package_name'] ?? 'Paket Internet';
        $installDate = $customerData['tgl_pasang'] ?? date('Y-m-d');

        // Format date to Indonesian format
        $formattedDate = date('d/m/Y', strtotime($installDate));

        $message = "🎉 *Selamat Datang di Layanan Internet Kami!*\n\n";
        $message .= "Halo *{$name}*,\n\n";
        $message .= "Terima kasih telah mempercayai kami sebagai penyedia layanan internet Anda. Berikut informasi akun Anda:\n\n";
        $message .= "📋 *Detail Pelanggan:*\n";
        $message .= "• Nama: {$name}\n";
        $message .= "• Nomor Layanan: {$serviceNumber}\n";
        $message .= "• Paket: {$packageName}\n";
        $message .= "• Tanggal Pemasangan: {$formattedDate}\n\n";
        $message .= "📞 *Layanan Pelanggan:*\n";
        $message .= "Jika ada pertanyaan atau kendala, silakan hubungi tim support kami.\n\n";
        $message .= "Terima kasih telah bergabung dengan kami! 🚀";

        return $message;
    }

    /**
     * Get active WhatsApp device
     */
    private function getActiveDevice($preferredSender = null)
    {
        try {
            $builder = $this->db->table('whatsapp_devices');

            if ($preferredSender) {
                $builder->where('number', $preferredSender);
            }

            $device = $builder->orderBy('id', 'DESC')->get()->getRow();

            if ($device) {
                return [
                    'number' => $device->number,
                    'api_key' => $device->api_key
                ];
            }

            return null;
        } catch (\Exception $e) {
            log_message('error', 'Error getting WhatsApp device: ' . $e->getMessage());
            return null;
        }
    }

    /**
     * Format phone number for WhatsApp
     */
    private function formatNumber($number)
    {
        // Remove all non-numeric characters
        $number = preg_replace('/[^0-9]/', '', $number);

        // Remove leading zeros
        $number = ltrim($number, '0');

        // Add Indonesian country code if not present
        if (!str_starts_with($number, '62')) {
            $number = '62' . $number;
        }

        return $number;
    }

    /**
     * Send message via API
     */
    private function sendViaAPI($data)
    {
        $url = $this->config->apiUrl;

        // Log the request attempt
        log_message('debug', '[WA-API] Attempting to send message to: ' . $data['number']);
        log_message('debug', '[WA-API] API URL: ' . $url);
        log_message('debug', '[WA-API] Sender: ' . $data['sender']);
        log_message('debug', '[WA-API] Message length: ' . strlen($data['message']));

        // Try POST request first (more reliable for longer messages)
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query([
            'api_key' => $data['api_key'],
            'sender' => $data['sender'],
            'number' => $data['number'],
            'message' => $data['message']
        ]));
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_TIMEOUT, $this->config->timeout);
        curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, $this->config->connectTimeout);
        curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);

        $response = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        $error = curl_error($ch);
        curl_close($ch);

        // Log response for debugging
        log_message('debug', '[WA-API] HTTP Code: ' . $httpCode);
        if ($response) {
            log_message('debug', '[WA-API] Response (first 500 chars): ' . substr($response, 0, 500));
        }

        if ($error) {
            log_message('error', '[WA-API] CURL Error: ' . $error);
            return [
                'success' => false,
                'message' => 'CURL Error: ' . $error
            ];
        }

        if ($httpCode === 200) {
            $responseData = json_decode($response, true);

            if (isset($responseData['status']) && $responseData['status'] === true) {
                log_message('info', '[WA-API] Message sent successfully');
                return [
                    'success' => true,
                    'message' => $responseData['msg'] ?? 'WhatsApp message sent successfully',
                    'response' => $responseData
                ];
            } else {
                $errMsg = $responseData['msg'] ?? $responseData['message'] ?? 'Unknown error';
                log_message('warning', '[WA-API] API returned error: ' . $errMsg);
                return [
                    'success' => false,
                    'message' => 'API returned error: ' . $errMsg,
                    'response' => $responseData
                ];
            }
        } else {
            log_message('error', '[WA-API] HTTP Error ' . $httpCode . ': ' . substr($response ?? '', 0, 200));
            return [
                'success' => false,
                'message' => 'HTTP Error: ' . $httpCode,
                'response' => $response
            ];
        }
    }

    /**
     * Log WhatsApp message
     */
    private function logMessage($number, $message, $result)
    {
        try {
            // Use the existing whatsapp_message_logs table instead of whatsapp_logs
            if ($this->db->tableExists('whatsapp_message_logs')) {
                $this->db->table('whatsapp_message_logs')->insert([
                    'phone_number' => $number,
                    'template_type' => 'general',
                    'message_content' => $message,
                    'status' => $result['success'] ? 'sent' : 'failed',
                    'error_message' => $result['success'] ? null : $result['message'],
                    'sent_at' => $result['success'] ? date('Y-m-d H:i:s') : null,
                    'created_at' => date('Y-m-d H:i:s'),
                    'updated_at' => date('Y-m-d H:i:s')
                ]);
            }
        } catch (\Exception $e) {
            log_message('error', 'Error logging WhatsApp message: ' . $e->getMessage());
        }
    }

    /**
     * Get new customer template from database
     */
    private function getNewCustomerTemplate($customerData)
    {
        try {
            // Get template from database

            $template = $this->db->table('whatsapp_templates')
                ->select('new_customer')
                ->orderBy('id', 'DESC')
                ->get()
                ->getRow();

            // Debug log untuk melihat isi template dari database
            log_message('debug', '[WA-TEMPLATE] Isi kolom new_customer dari DB: ' . print_r($template, true));

            $messageTemplate = $template->new_customer ?? $this->getDefaultNewCustomerTemplate();
            log_message('debug', '[WA-TEMPLATE] Template yang dipakai: ' . $messageTemplate);

            // Replace placeholders with actual data
            // Generate payment link and bank data
            $paymentLink = $this->generatePaymentLink($customerData['nomor_layanan'] ?? null);
            $bankData = $this->getBankData();

            // Get bandwidth from package if available
            $bandwidth = '-';
            if (!empty($customerData['id_paket'])) {
                $package = $this->db->table('package_profiles')->where('id', $customerData['id_paket'])->get()->getRow();
                if ($package) {
                    $bandwidth = $package->bandwidth_profile ?? $package->bandwidth ?? '-';
                }
            }

            $replacements = [
                '{no_layanan}' => $customerData['nomor_layanan'] ?? '-',
                '{customer}' => $customerData['nama_pelanggan'] ?? 'Customer',
                '{phone}' => $customerData['telepphone'] ?? $customerData['no_tlp'] ?? $customerData['no_hp'] ?? '-',
                '{village}' => $customerData['village'] ?? $customerData['desa'] ?? '-',
                '{district}' => $customerData['district'] ?? $customerData['kecamatan'] ?? '-',
                '{city}' => $customerData['city'] ?? $customerData['kota'] ?? '-',
                '{address}' => $customerData['address'] ?? $customerData['alamat'] ?? '-',
                '{Alamat}' => $customerData['address'] ?? $customerData['alamat'] ?? '-',  // Support uppercase
                '{paket}' => $customerData['package_name'] ?? 'Paket Internet',
                '{harga}' => number_format($customerData['tarif'] ?? 0, 0, ',', '.'),
                '{bandwidth}' => $bandwidth,
                '{company}' => 'PT. KIMONET DIGITAL SYNERGY',
                '{link_payment}' => $paymentLink,
                '{bank_data}' => $bankData,
            ];

            $message = str_replace(array_keys($replacements), array_values($replacements), $messageTemplate);
            return $message;
        } catch (\Exception $e) {
            log_message('error', 'Error getting new customer template: ' . $e->getMessage());
            return $this->getDefaultNewCustomerTemplate();
        }
    }


    /**
     * Generate payment link for a customer
     */
    private function generatePaymentLink($nomorLayanan)
    {
        if (empty($nomorLayanan)) {
            return '';
        }
        // Cek apakah ada invoice dengan nomor layanan ini
        // Cari berdasarkan customer dengan nomor_layanan
        $customer = $this->db->table('customers')
            ->where('nomor_layanan', $nomorLayanan)
            ->get()
            ->getRow();

        if ($customer) {
            // Cari invoice terbaru untuk customer ini
            $invoice = $this->db->table('customer_invoices')
                ->where('customer_id', $customer->id_customers)
                ->orderBy('id', 'DESC')
                ->get()
                ->getRow();

            if ($invoice && !empty($invoice->payment_link)) {
                return $invoice->payment_link;
            }
        }

        // Fallback: generate direct billing URL internal
        // Gunakan base_url dari helper CodeIgniter
        return base_url($nomorLayanan);
    }

    /**
     * Get bank transfer information
     */
    private function getBankData()
    {
        // Ambil data bank aktif dari tabel banks
        $banks = $this->db->table('banks')
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

    /**
     * Get default new customer template if database template is not available
     */
    private function getDefaultNewCustomerTemplate()
    {
        return "🎉 *Selamat Datang di Layanan Internet Kami!*\n\n" .
            "Halo *{customer}*,\n\n" .
            "Terima kasih telah mempercayai kami sebagai penyedia layanan internet Anda.\n\n" .
            "📋 *Detail Pelanggan:*\n" .
            "• Nama: {customer}\n" .
            "• Nomor Layanan: {no_layanan}\n" .
            "• Paket: {paket}\n" .
            "• Total Tagihan: Rp {harga}\n\n" .
            "📞 *Layanan Pelanggan:*\n" .
            "Jika ada pertanyaan atau kendala, silakan hubungi tim support kami.\n\n" .
            "Terima kasih telah bergabung dengan kami! 🚀";
    }

    /**
     * Send isolir notification to customer
     * Dijalankan ketika customer diisolir karena telat bayar
     */
    public function sendIsolirNotification($customerData, $message = null)
    {
        try {
            // Get customer's phone number
            $phoneNumber = $customerData['telepphone'] ?? $customerData['no_tlp'] ?? $customerData['no_hp'] ?? $customerData['phone'] ?? null;

            if (empty($phoneNumber)) {
                log_message('warning', 'Customer phone number is empty for isolir notification: ' . ($customerData['nama_pelanggan'] ?? 'unknown'));
                return [
                    'success' => false,
                    'message' => 'Customer phone number is required'
                ];
            }

            // Build message if not provided
            if (empty($message)) {
                $message = $this->buildIsolirMessage($customerData);
            }

            // Send message
            $result = $this->sendMessage($phoneNumber, $message);

            // Log to WhatsApp message logs table
            $this->logToMessageTable($customerData, $phoneNumber, $message, $result, 'isolir');

            return $result;
        } catch (\Exception $e) {
            log_message('error', 'Error sending isolir notification: ' . $e->getMessage());
            return [
                'success' => false,
                'message' => 'Error sending isolir notification: ' . $e->getMessage()
            ];
        }
    }

    /**
     * Build isolir notification message
     */
    private function buildIsolirMessage($customerData)
    {
        $message = "📵 *Notifikasi Isolir Layanan*\n\n";
        $message .= "Yth. " . ($customerData['nama_pelanggan'] ?? 'Pelanggan') . "\n\n";

        if (!empty($customerData['pppoe_username'])) {
            $message .= "Layanan PPPoE Anda dengan username:\n";
            $message .= "`" . $customerData['pppoe_username'] . "`\n\n";
        }

        $message .= "Telah diputus karena pembayaran tagihan belum diterima.\n\n";

        if (!empty($customerData['isolir_date'])) {
            $message .= "Waktu Isolir: " . date('d M Y H:i', strtotime($customerData['isolir_date'])) . "\n";
        }

        if (!empty($customerData['tgl_tempo'])) {
            $message .= "Jatuh Tempo: " . date('d M Y', strtotime($customerData['tgl_tempo'])) . "\n";
        }

        $message .= "\n💰 *Untuk mengaktifkan kembali:*\n";
        $message .= "1. Silakan lakukan pembayaran tagihan Anda\n";
        $message .= "2. Konfirmasi pembayaran melalui panel pelanggan\n";
        $message .= "3. Koneksi akan aktif otomatis dalam 10 menit\n\n";

        $message .= "📊 Panel Pelanggan: https://billing.kimonet.my.id\n";
        $message .= "💬 Support: https://wa.me/62895383112127\n\n";
        $message .= "Terima kasih atas perhatian Anda.\n";
        $message .= "Kimonet ISP";

        return $message;
    }

    /**
     * Log message to whatsapp_message_logs table
     */
    private function logToMessageTable($customerData, $phoneNumber, $message, $result, $templateType = 'new_customer')
    {
        try {
            if (!$this->db->tableExists('whatsapp_message_logs')) {
                return;
            }

            $this->db->table('whatsapp_message_logs')->insert([
                'customer_id' => $customerData['id_customers'] ?? null,
                'customer_name' => $customerData['nama_pelanggan'] ?? null,
                'phone_number' => $phoneNumber,
                'template_type' => $templateType,
                'message_content' => $message,
                'status' => $result['success'] ? 'sent' : 'failed',
                'error_message' => $result['success'] ? null : $result['message'],
                'sent_at' => $result['success'] ? date('Y-m-d H:i:s') : null,
                'created_at' => date('Y-m-d H:i:s'),
                'updated_at' => date('Y-m-d H:i:s')
            ]);
        } catch (\Exception $e) {
            log_message('error', 'Error logging to message table: ' . $e->getMessage());
        }
    }
}
