<?php

namespace App\Controllers;

class TestDuitku extends BaseController
{
    public function index()
    {
        $gatewayModel = new \App\Models\PaymentGatewayModel();
        $duitku = $gatewayModel->where('gateway_type', 'duitku')->first();

        if (!$duitku) {
            return $this->response->setJSON([
                'error' => 'Duitku not found in database'
            ]);
        }

        return $this->response->setJSON([
            'gateway_name' => $duitku['gateway_name'],
            'gateway_type' => $duitku['gateway_type'],
            'is_active' => $duitku['is_active'],
            'api_key' => !empty($duitku['api_key']) ? substr($duitku['api_key'], 0, 10) . '...' : 'EMPTY',
            'merchant_code' => $duitku['merchant_code'] ?? 'EMPTY',
            'merchant_key' => !empty($duitku['merchant_key']) ? substr($duitku['merchant_key'], 0, 10) . '...' : 'EMPTY',
            'environment' => $duitku['environment'] ?? 'not set'
        ]);
    }
}
