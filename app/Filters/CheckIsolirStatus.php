<?php

namespace App\Filters;

use CodeIgniter\HTTP\RequestInterface;
use CodeIgniter\HTTP\ResponseInterface;
use CodeIgniter\Filters\FilterInterface;
use App\Models\CustomerModel;

/**
 * CheckIsolirStatus Filter
 * 
 * Mendeteksi apakah customer sedang diisolir berdasarkan PPPoE username
 * Jika diisolir, redirect ke halaman isolir
 */
class CheckIsolirStatus implements FilterInterface
{
    public function before(RequestInterface $request, $arguments = null)
    {
        // Jangan check di halaman isolir sendiri
        if (
            strpos($request->getUri(), 'isolir.php') !== false ||
            strpos($request->getUri(), 'payment') !== false
        ) {
            return;
        }

        // Get PPPoE username from query string atau cookie
        $pppoeUsername = $this->request->getGet('username') ??
            $this->request->getCookie('pppoe_username') ??
            null;

        if (!$pppoeUsername) {
            return;
        }

        // Check isolir status di database
        $customerModel = new CustomerModel();
        $customer = $customerModel->where('pppoe_username', $pppoeUsername)
            ->first();

        if ($customer && $customer['isolir_status'] == 1) {
            // Customer diisolir, redirect ke halaman isolir
            $isolirPageUrl = $this->getIsolirPageUrl($customer['id_lokasi_server']);

            // Build redirect URL with parameters
            $redirectUrl = $isolirPageUrl . '?username=' . urlencode($pppoeUsername);
            if ($customer['isolir_reason']) {
                $redirectUrl .= '&msg=' . urlencode($customer['isolir_reason']);
            }

            // Default type adalah overdue (tagihan)
            $type = 'overdue';
            if (strpos($customer['isolir_reason'] ?? '', 'maintenance') !== false) {
                $type = 'maintenance';
            }
            $redirectUrl .= '&type=' . $type;

            return redirect()->to($redirectUrl);
        }
    }

    public function after(RequestInterface $request, ResponseInterface $response, $arguments = null)
    {
        // Do nothing
    }

    /**
     * Get isolir page URL dari konfigurasi router
     */
    private function getIsolirPageUrl($routerId)
    {
        $configModel = new \App\Models\AutoIsolirConfigModel();
        $config = $configModel->where('router_id', $routerId)->first();

        if ($config && !empty($config['isolir_page_url'])) {
            return $config['isolir_page_url'];
        }

        // Default fallback
        return 'https://isolir.kimonet.my.id/';
    }
}
