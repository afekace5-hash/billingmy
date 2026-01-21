<?php

namespace App\Filters;

use CodeIgniter\Filters\FilterInterface;
use CodeIgniter\HTTP\RequestInterface;
use CodeIgniter\HTTP\ResponseInterface;
use App\Models\CustomerModel;

class BillingFilter implements FilterInterface
{
    public function before(RequestInterface $request, $arguments = null)
    {
        $uri = service('uri');
        $segment = $uri->getSegment(1);

        // Debug logging
        log_message('info', 'BillingFilter - Raw URI: ' . $request->getUri());
        log_message('info', 'BillingFilter - Checking segment: ' . ($segment ?? 'NULL/EMPTY'));

        // Jika segment kosong (root /), skip filter ini
        if (empty($segment)) {
            log_message('info', 'BillingFilter - Skipping empty segment (root /)');
            return;
        }

        // Jika segment merupakan route yang sudah didefinisikan, lewati
        $reservedRoutes = [
            'dashboard',
            'login',
            'auth',
            'customers',
            'clustering',
            'internet-packages',
            'server-locations',
            'invoices',
            'routers',
            'settings',
            'whatsapp',
            'arus_kas',
            'payment',
            'billing',
            'documentation',
            'api',
            'admin',
            'assets',
            'backend',
            'css',
            'js',
            'uploads',
            'public',
            'system',
            'vendor',
            'favicon.ico',
            'robots.txt',
            'customer',
            'clustering',
            'lokasi-server',
            'apiproxy',
            'invoice',
            'companies',
            'test_direct_billing.php',
            'test_api_final.php',
            'debug_whatsapp.php',
            'validate_whatsapp_setup.php',
            'spark',
            'composer.json',
            'preload.php',
            'cek-tagihan',
            'public-billing'
        ];

        if (in_array($segment, $reservedRoutes)) {
            log_message('info', 'BillingFilter - Skipping reserved route: ' . $segment);
            return;
        }

        // Skip jika segment mengandung file extension
        if (strpos($segment, '.') !== false) {
            log_message('info', 'BillingFilter - Skipping file with extension: ' . $segment);
            return;
        }

        // Cek apakah segment adalah nomor layanan yang valid
        $customerModel = new CustomerModel();
        $customer = $customerModel->where('nomor_layanan', $segment)->first();

        if ($customer) {
            log_message('info', 'BillingFilter - Customer found: ' . json_encode($customer));
        } else {
            log_message('info', 'BillingFilter - Customer NOT found for segment: ' . $segment);
        }

        if (!$customer) {
            // Jika bukan nomor layanan valid, redirect ke 404 atau halaman utama
            throw \CodeIgniter\Exceptions\PageNotFoundException::forPageNotFound();
        }

        // Jika valid, lanjutkan ke controller
        return;
    }

    public function after(RequestInterface $request, ResponseInterface $response, $arguments = null)
    {
        // Do something here
    }
}
