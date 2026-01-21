<?php
// filepath: c:\xampp\htdocs\interneter\app\Controllers\ApiProxy.php
namespace App\Controllers;

use CodeIgniter\HTTP\Client;

class ApiProxy extends BaseController
{
    /**
     * Endpoint lokal untuk pengecekan koneksi Mikrotik.
     * Terima POST: ip_router, username, password_router, port_api
     * Kembalikan status sukses/error (tanpa akses ke wifinetbill.com)
     */
    public function checkMikrotikConnection()
    {
        // Simulasi koneksi Mikrotik lokal (atau ganti dengan library Mikrotik lokal jika ada)
        $ip = $this->request->getPost('ip_router');
        $username = $this->request->getPost('username');
        $password = $this->request->getPost('password_router');
        $port = $this->request->getPost('port_api');

        // TODO: Ganti dengan library Mikrotik asli (RouterOS API PHP Client) jika tersedia di lokal
        // Contoh stub: sukses jika IP, username, password, port tidak kosong
        if ($ip && $username && $password && $port) {
            // Simulasi sukses
            return $this->response->setJSON([
                'success' => true,
                'message' => 'Koneksi ke Mikrotik lokal berhasil!'
            ]);
        } else {
            return $this->response->setJSON([
                'success' => false,
                'message' => 'Data tidak lengkap.'
            ]);
        }
    }
}
