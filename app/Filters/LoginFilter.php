<?php

namespace App\Filters;

use CodeIgniter\HTTP\RequestInterface;
use CodeIgniter\HTTP\ResponseInterface;
use CodeIgniter\Filters\FilterInterface;

class LoginFilter implements FilterInterface
{
    public function before(RequestInterface $request, $arguments = null)
    {
        if (!session('id_user')) {
            // Check if this is an AJAX request or expects JSON
            $isAjax = $request->getHeaderLine('X-Requested-With') === 'XMLHttpRequest' ||
                $request->getHeaderLine('Content-Type') === 'application/json' ||
                $request->getHeaderLine('Accept') === 'application/json' ||
                strpos($request->getHeaderLine('Accept'), 'application/json') !== false;

            if ($isAjax) {

                // Return JSON error for AJAX requests
                $response = service('response');
                return $response->setStatusCode(401)->setJSON([
                    'success' => false,
                    'message' => 'Session expired. Please login again.',
                    'redirect' => site_url('login')
                ]);
            }

            // Redirect for regular requests
            return redirect()->to(site_url('login'));
        }
    }

    public function after(RequestInterface $request, ResponseInterface $response, $arguments = null)
    {
        // Do something here
    }
}
