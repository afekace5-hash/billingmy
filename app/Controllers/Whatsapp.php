<?php

namespace App\Controllers;

use App\Controllers\BaseController;
use App\Models\WhatsappLogModel;

class Whatsapp extends BaseController
// Logout device WhatsApp

{
    /**
     * Simpan nomor device dan api_key ke database
     */
    public function saveDevice()
    {
        $number = $this->request->getPost('number');
        $api_key = $this->request->getPost('api_key');
        if (!$number || !$api_key) {
            return $this->response->setJSON([
                'status' => false,
                'msg' => 'Nomor dan API Key wajib diisi.'
            ]);
        }

        $deviceModel = new \App\Models\WhatsappDeviceModel();
        // update jika sudah ada, insert jika belum
        $existing = $deviceModel->where('number', $number)->first();
        if ($existing) {
            $deviceModel->update($existing['id'], [
                'api_key' => $api_key
            ]);
        } else {
            $deviceModel->insert([
                'number' => $number,
                'api_key' => $api_key
            ]);
        }

        return $this->response->setJSON([
            'status' => true,
            'msg' => 'Device/API Key berhasil disimpan.'
        ]);
    }
    public function checkStatus()
    {
        $number = $this->request->getPost('number');
        $baseUrl = getenv('WHATSAPP_BASE_URL') ?: 'https://wazero.kimonet.my.id';
        $url = $baseUrl . '/mpwa/status'; // MPWA endpoint (remote)
        $data = ['number' => $number];

        $ch = curl_init($url);
        curl_setopt($ch, CURLOPT_POST, 1);
        curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($data));
        curl_setopt($ch, CURLOPT_HTTPHEADER, ['Content-Type: application/json']);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        $response = curl_exec($ch);
        curl_close($ch);

        return $this->response->setJSON(json_decode($response, true));
    }

    public function getQrCode()
    {
        $number = $this->request->getPost('number');
        if (!$number) {
            return $this->response->setJSON([
                'status' => false,
                'message' => 'Nomor wajib diisi'
            ]);
        }

        // Ambil api_key dari database
        $deviceModel = new \App\Models\WhatsappDeviceModel();
        $device = $deviceModel->where('number', $number)->first();
        if (!$device) {
            return $this->response->setJSON([
                'status' => false,
                'message' => 'Device/API Key tidak ditemukan untuk nomor ini'
            ]);
        }
        $api_key = $device['api_key'];

        // Request ke API eksternal
        $baseUrl = getenv('WHATSAPP_BASE_URL') ?: 'https://wazero.kimonet.my.id';
        $url = $baseUrl . '/generate-qr';

        // Endpoint ini HANYA menerima GET method (bukan POST)
        $data = [
            'device' => $number,
            'api_key' => $api_key,
            'force' => 'false' // Convert boolean to string
        ];

        // Build URL with query parameters
        $queryString = http_build_query($data);
        $fullUrl = $url . '?' . $queryString;

        log_message('info', 'WhatsApp Generate QR URL: ' . $fullUrl);

        // Use GET method
        $ch = curl_init($fullUrl);
        curl_setopt($ch, CURLOPT_HTTPGET, true);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_TIMEOUT, 30);
        curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 10);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
        curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, false);
        curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
        curl_setopt($ch, CURLOPT_HTTPHEADER, [
            'Accept: application/json'
        ]);

        $response = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        $curlError = curl_error($ch);
        curl_close($ch);

        if ($curlError) {
            log_message('error', 'WhatsApp getQrCode cURL Error: ' . $curlError);
            return $this->response->setJSON([
                'status' => false,
                'message' => 'Gagal terhubung ke server WhatsApp: ' . $curlError
            ]);
        }

        // Parse response terlebih dahulu untuk cek isi pesan
        $decoded = json_decode($response, true);

        // HTTP 400 dari wazero bisa jadi valid response jika device sudah connected
        if ($httpCode == 400 && $decoded && isset($decoded['msg'])) {
            if (
                stripos($decoded['msg'], 'already connected') !== false ||
                stripos($decoded['msg'], 'sudah terhubung') !== false
            ) {
                // Device sudah terhubung - ini valid
                log_message('info', 'Device already connected (HTTP 400): ' . $decoded['msg']);
                return $this->response->setJSON([
                    'status' => true,
                    'connected' => true,
                    'message' => 'Perangkat sudah terhubung'
                ]);
            }
        }

        // Validasi HTTP code untuk response lainnya
        if (!$response || ($httpCode !== 200 && $httpCode !== 400)) {
            log_message('error', 'WhatsApp getQrCode HTTP Error: ' . $httpCode . ' - Response: ' . $response);
            return $this->response->setJSON([
                'status' => false,
                'message' => 'Server WhatsApp tidak merespons dengan baik (HTTP ' . $httpCode . ')'
            ]);
        }

        if (!$decoded) {
            log_message('error', 'WhatsApp getQrCode Invalid JSON Response: ' . $response);
            return $this->response->setJSON([
                'status' => false,
                'message' => 'Response dari server tidak valid'
            ]);
        }

        // Log response untuk debugging
        log_message('info', 'WhatsApp getQrCode Response: ' . json_encode($decoded));

        // Handle response sesuai dokumentasi wazero API
        // Response 1: QR Code belum di-scan (status: false, qrcode: data:image/png;base64,...)
        if (isset($decoded['status']) && $decoded['status'] === false && isset($decoded['qrcode'])) {
            return $this->response->setJSON([
                'status' => false,
                'qrcode' => $decoded['qrcode'],
                'message' => $decoded['message'] ?? $decoded['msg'] ?? 'Please scan QR code'
            ]);
        }

        // Response 2: Device sudah terhubung (status: false, msg: "Device already connected!")
        if (isset($decoded['status']) && $decoded['status'] === false && isset($decoded['msg'])) {
            if (
                stripos($decoded['msg'], 'already connected') !== false ||
                stripos($decoded['msg'], 'sudah terhubung') !== false
            ) {
                return $this->response->setJSON([
                    'status' => true,
                    'connected' => true,
                    'message' => 'Perangkat sudah terhubung'
                ]);
            }
        }

        // Response 3: Status true (terhubung)
        if (isset($decoded['status']) && $decoded['status'] === true) {
            return $this->response->setJSON([
                'status' => true,
                'connected' => true,
                'message' => $decoded['msg'] ?? $decoded['message'] ?? 'Perangkat sudah terhubung'
            ]);
        }

        // Response 4: Connected field
        if (isset($decoded['connected']) && $decoded['connected'] === true) {
            return $this->response->setJSON([
                'status' => true,
                'connected' => true,
                'message' => $decoded['msg'] ?? $decoded['message'] ?? 'Perangkat sudah terhubung'
            ]);
        }

        // Response 5: Processing
        if (isset($decoded['status']) && $decoded['status'] === 'processing') {
            return $this->response->setJSON([
                'status' => 'processing',
                'message' => $decoded['msg'] ?? $decoded['message'] ?? 'Sedang memproses...'
            ]);
        }

        // Response 6: Error atau status tidak diketahui
        return $this->response->setJSON([
            'status' => false,
            'message' => $decoded['msg'] ?? $decoded['message'] ?? $decoded['error'] ?? 'Gagal mendapatkan QR code',
            'debug' => $decoded // Untuk debugging
        ]);
    }

    // Contoh endpoint kirim pesan jika dibutuhkan
    public function send()
    {
        $number = $this->request->getPost('number');
        $message = $this->request->getPost('message');

        $baseUrl = getenv('WHATSAPP_BASE_URL') ?: 'https://wazero.kimonet.my.id';
        $url = $baseUrl . '/send-message'; // MPWA endpoint (remote)
        $data = [
            'number' => $number,
            'message' => $message
        ];

        $ch = curl_init($url);
        curl_setopt($ch, CURLOPT_POST, 1);
        curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($data));
        curl_setopt($ch, CURLOPT_HTTPHEADER, ['Content-Type: application/json']);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        $response = curl_exec($ch);
        $httpcode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);

        $status = 'failed';
        if ($httpcode == 200) {
            $res = json_decode($response, true);
            if (isset($res['status']) && $res['status'] == true) {
                $status = 'success';
            }
        }

        // Simpan log (opsional)
        if (class_exists(WhatsappLogModel::class)) {
            $logModel = new WhatsappLogModel();
            $logModel->insert([
                'number' => $number,
                'message' => $message,
                'status' => $status,
                'response' => $response,
                'created_at' => date('Y-m-d H:i:s')
            ]);
        }

        return $this->response->setJSON([
            'status' => $status,
            'response' => $response
        ]);
    }
    public function logout()
    {
        $number = $this->request->getPost('number');
        if (!$number) {
            return $this->response->setJSON([
                'status' => false,
                'message' => 'Nomor wajib diisi'
            ]);
        }

        // Ambil api_key dari database
        $deviceModel = new \App\Models\WhatsappDeviceModel();
        $device = $deviceModel->where('number', $number)->first();
        if (!$device) {
            return $this->response->setJSON([
                'status' => false,
                'message' => 'Device/API Key tidak ditemukan untuk nomor ini'
            ]);
        }
        $api_key = $device['api_key'];

        $baseUrl = getenv('WHATSAPP_BASE_URL') ?: 'https://wazero.kimonet.my.id';
        $url = $baseUrl . '/logout-device';
        $data = [
            'sender' => $number,
            'api_key' => $api_key
        ];
        $ch = curl_init($url);
        curl_setopt($ch, CURLOPT_POST, 1);
        curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($data));
        curl_setopt($ch, CURLOPT_HTTPHEADER, ['Content-Type: application/json']);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        $response = curl_exec($ch);
        curl_close($ch);

        $decoded = json_decode($response, true);
        if (isset($decoded['status']) && $decoded['status'] == true) {
            return $this->response->setJSON([
                'status' => true,
                'message' => $decoded['message'] ?? 'Logout berhasil'
            ]);
        } else {
            return $this->response->setJSON([
                'status' => false,
                'message' => $decoded['message'] ?? 'Logout gagal',
                'raw_response' => $response
            ]);
        }
    }

    // Hapus nomor WhatsApp
    public function deleteNumber()
    {
        $number = $this->request->getPost('number');
        if (!$number) {
            return $this->response->setJSON([
                'status' => false,
                'message' => 'Nomor wajib diisi'
            ]);
        }        // Contoh: request ke API eksternal untuk hapus nomor (ganti URL dan parameter sesuai kebutuhan)
        $url = site_url('whatsapp/number/delete');
        $data = ['number' => $number];
        $ch = curl_init($url);
        curl_setopt($ch, CURLOPT_POST, 1);
        curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query($data));
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        $response = curl_exec($ch);
        curl_close($ch);

        $decoded = json_decode($response, true);
        if (isset($decoded['status']) && $decoded['status'] == true) {
            return $this->response->setJSON([
                'status' => true,
                'message' => $decoded['message'] ?? 'Nomor berhasil dihapus'
            ]);
        } else {
            return $this->response->setJSON([
                'status' => false,
                'message' => $decoded['message'] ?? 'Gagal menghapus nomor'
            ]);
        }
    }

    public function saveNotifSettings()
    {
        $data = $this->request->getPost();
        $number = isset($data['phone']) ? $data['phone'] : null;
        if (!$number) {
            return $this->response->setJSON([
                'status' => 'error',
                'title' => 'Gagal',
                'message' => 'Nomor WhatsApp tidak ditemukan.'
            ]);
        }
        $notifModel = new \App\Models\WhatsappNotifSettingModel();
        // Mapping checkbox ke field DB
        $saveData = [
            'number' => $number,
            'notif_invoice' => isset($data['on_due']) ? 1 : 0,
            'notif_payment' => isset($data['on_payment_created']) ? 1 : 0,
            'notif_reminder' => (isset($data['one_day_before_due']) || isset($data['three_day_before_due']) || isset($data['seven_day_before_due'])) ? 1 : 0, // bisa dipecah jika field DB ada
            'notif_other' => (isset($data['on_isolated']) || isset($data['on_customer_created'])) ? 1 : 0,
            'updated_at' => date('Y-m-d H:i:s')
        ];
        // Cek sudah ada atau belum
        $existing = $notifModel->where('number', $number)->first();
        if ($existing) {
            $notifModel->update($existing['id'], $saveData);
        } else {
            $notifModel->insert($saveData);
        }
        return $this->response->setJSON([
            'status' => 'success',
            'title' => 'Berhasil',
            'message' => 'Pengaturan notifikasi berhasil disimpan'
        ]);
    }

    public function reset()
    {
        try {
            // Hapus semua device yang tersimpan
            $deviceModel = new \App\Models\WhatsappDeviceModel();
            $devices = $deviceModel->findAll();

            $baseUrl = getenv('WHATSAPP_BASE_URL') ?: 'https://wazero.kimonet.my.id';

            // Logout semua device dari API
            foreach ($devices as $device) {
                $url = $baseUrl . '/logout-device';
                $data = [
                    'sender' => $device['number'],
                    'api_key' => $device['api_key']
                ];

                $ch = curl_init($url);
                curl_setopt($ch, CURLOPT_POST, 1);
                curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($data));
                curl_setopt($ch, CURLOPT_HTTPHEADER, ['Content-Type: application/json']);
                curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
                curl_setopt($ch, CURLOPT_TIMEOUT, 10);
                curl_exec($ch);
                curl_close($ch);
            }

            // Hapus semua data device dari database
            $deviceModel->truncate();

            // Hapus notification settings
            $notifModel = new \App\Models\WhatsappNotifSettingModel();
            $notifModel->truncate();

            return redirect()->to('whatsapp')->with('success', 'WhatsApp berhasil direset. Semua device telah dilogout dan data dihapus.');
        } catch (\Exception $e) {
            log_message('error', 'WhatsApp Reset Error: ' . $e->getMessage());
            return redirect()->to('whatsapp')->with('error', 'Gagal mereset WhatsApp: ' . $e->getMessage());
        }
    }
}
