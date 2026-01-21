<?php

namespace App\Filters;

use CodeIgniter\Filters\FilterInterface;
use CodeIgniter\HTTP\RequestInterface;
use CodeIgniter\HTTP\ResponseInterface;

/**
 * Customer Authentication Filter
 * Untuk memastikan customer sudah login di portal
 */
class CustomerAuth implements FilterInterface
{
    public function before(RequestInterface $request, $arguments = null)
    {
        $session = session();

        // Check if customer is logged in
        if (!$session->has('is_customer_logged_in') || !$session->get('is_customer_logged_in')) {
            // Save intended URL
            $session->set('customer_redirect_url', current_url());

            // Redirect to customer login
            return redirect()->to(site_url('customer-portal'))
                ->with('error', 'Silakan login terlebih dahulu');
        }

        // Check if session is still valid
        $lastActivity = $session->get('customer_last_activity');
        $sessionExpiration = config('CustomerPortal')->sessionExpiration ?? 7200;

        if ($lastActivity && (time() - $lastActivity > $sessionExpiration)) {
            // Session expired
            $session->remove('is_customer_logged_in');
            $session->remove('customer_id');
            $session->remove('customer_name');
            $session->remove('customer_number');

            return redirect()->to(site_url('customer-portal'))
                ->with('error', 'Sesi Anda telah berakhir. Silakan login kembali');
        }

        // Update last activity
        $session->set('customer_last_activity', time());

        return $request;
    }

    public function after(RequestInterface $request, ResponseInterface $response, $arguments = null)
    {
        // Do nothing
        return $response;
    }
}
