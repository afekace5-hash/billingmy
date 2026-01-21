<?php

namespace App\Filters;

use CodeIgniter\HTTP\RequestInterface;
use CodeIgniter\HTTP\ResponseInterface;
use CodeIgniter\Filters\FilterInterface;
use App\Helpers\JWTHelper;

class ApiAuthFilter implements FilterInterface
{
    public function before(RequestInterface $request, $arguments = null)
    {
        // Get token from Authorization header
        $authHeader = $request->getHeaderLine('Authorization');

        if (empty($authHeader)) {
            return service('response')
                ->setJSON([
                    'success' => false,
                    'message' => 'Token tidak ditemukan. Silakan login terlebih dahulu.',
                    'error_code' => 'TOKEN_MISSING'
                ])
                ->setStatusCode(401);
        }

        // Extract token (format: "Bearer <token>")
        $token = null;
        if (preg_match('/Bearer\s+(.*)$/i', $authHeader, $matches)) {
            $token = $matches[1];
        } else {
            $token = $authHeader; // If no Bearer prefix
        }

        // Validate token
        $decoded = JWTHelper::validateToken($token);

        if (!$decoded) {
            return service('response')
                ->setJSON([
                    'success' => false,
                    'message' => 'Token tidak valid atau sudah kadaluarsa. Silakan login kembali.',
                    'error_code' => 'TOKEN_INVALID'
                ])
                ->setStatusCode(401);
        }

        // Check if token is expired
        if (isset($decoded->exp) && $decoded->exp < time()) {
            return service('response')
                ->setJSON([
                    'success' => false,
                    'message' => 'Token sudah kadaluarsa. Silakan login kembali.',
                    'error_code' => 'TOKEN_EXPIRED'
                ])
                ->setStatusCode(401);
        }

        // Store customer data in request for use in controllers
        $request->customerId = $decoded->data->customer_id ?? null;
        $request->customerData = $decoded->data ?? null;

        return $request;
    }

    public function after(RequestInterface $request, ResponseInterface $response, $arguments = null)
    {
        // Do nothing
    }
}
