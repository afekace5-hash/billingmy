<?php

namespace App\Controllers;

use App\Controllers\BaseController;
use App\Models\CustomerModel;
use App\Models\AutoIsolirConfigModel;
use App\Services\WhatsAppService;

/**
 * Isolir Controller
 * 
 * Menangani halaman isolir dan notifikasi isolir ke customer
 */
class Isolir extends BaseController
{
    protected $customerModel;
    protected $configModel;
    protected $whatsappService;

    public function __construct()
    {
        $this->customerModel = new CustomerModel();
        $this->configModel = new AutoIsolirConfigModel();
        $this->whatsappService = new WhatsAppService();
    }

    /**
     * GET: /isolir
     * Halaman isolir publik - bisa diakses dari redirect IP MikroTik
     */
    public function index()
    {
        $pppoeUsername = $this->request->getGet('username');
        $type = $this->request->getGet('type') ?? 'overdue';
        $msg = $this->request->getGet('msg');

        // Get customer info
        $customer = null;
        $isolirInfo = null;

        if ($pppoeUsername) {
            $customer = $this->customerModel
                ->select('id_customers, nama_pelanggan, pppoe_username, id_lokasi_server, isolir_status, isolir_date, isolir_reason')
                ->where('pppoe_username', $pppoeUsername)
                ->first();

            if ($customer) {
                $isolirInfo = [
                    'nama_pelanggan' => $customer['nama_pelanggan'],
                    'username' => $customer['pppoe_username'],
                    'isolir_date' => $customer['isolir_date'],
                    'isolir_reason' => $customer['isolir_reason'],
                    'type' => $type,
                    'custom_message' => $msg
                ];
            }
        }

        $data = [
            'title' => 'Layanan Terputus',
            'isolirInfo' => $isolirInfo,
            'type' => $type
        ];

        // Log isolir page access
        log_message('info', 'Isolir page accessed - Username: ' . ($pppoeUsername ?? 'unknown') . ', Type: ' . $type);

        return view('isolir/index', $data);
    }

    /**
     * GET: /isolir/status/{customer_id}
     * Check apakah customer masih diisolir (AJAX)
     */
    public function checkStatus($customerId)
    {
        if (!$this->request->isAJAX()) {
            return $this->response->setStatusCode(403);
        }

        $customer = $this->customerModel->find($customerId);

        if (!$customer) {
            return $this->response->setJSON([
                'success' => false,
                'message' => 'Customer tidak ditemukan'
            ]);
        }

        return $this->response->setJSON([
            'success' => true,
            'isolated' => $customer['isolir_status'] == 1,
            'isolir_date' => $customer['isolir_date'],
            'isolir_reason' => $customer['isolir_reason']
        ]);
    }

    /**
     * POST: /isolir/send-notification/{customer_id}
     * Kirim notifikasi WhatsApp ke customer yang diisolir
     */
    public function sendNotification($customerId)
    {
        if (!$this->request->isAJAX()) {
            return $this->response->setStatusCode(403);
        }

        try {
            $customer = $this->customerModel->find($customerId);

            if (!$customer) {
                return $this->response->setJSON([
                    'success' => false,
                    'message' => 'Customer tidak ditemukan'
                ]);
            }

            // Send WhatsApp notification
            $message = $this->buildIsolirNotificationMessage($customer);
            $result = $this->whatsappService->sendIsolirNotification($customer, $message);

            if ($result['success']) {
                return $this->response->setJSON([
                    'success' => true,
                    'message' => 'Notifikasi berhasil dikirim'
                ]);
            } else {
                return $this->response->setJSON([
                    'success' => false,
                    'message' => 'Gagal mengirim notifikasi: ' . $result['message']
                ]);
            }
        } catch (\Exception $e) {
            log_message('error', 'Error sending isolir notification: ' . $e->getMessage());
            return $this->response->setJSON([
                'success' => false,
                'message' => 'Terjadi kesalahan: ' . $e->getMessage()
            ]);
        }
    }

    /**
     * Build isolir notification message
     */
    private function buildIsolirNotificationMessage($customer)
    {
        $message = "📵 *Notifikasi Isolir Layanan*\n\n";
        $message .= "Yth. " . $customer['nama_pelanggan'] . "\n\n";
        $message .= "Layanan PPPoE Anda dengan username:\n";
        $message .= "`" . $customer['pppoe_username'] . "`\n\n";
        $message .= "Telah diputus karena pembayaran tagihan belum diterima.\n\n";

        if ($customer['isolir_date']) {
            $message .= "Waktu Isolir: " . date('d M Y H:i', strtotime($customer['isolir_date'])) . "\n";
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
}
