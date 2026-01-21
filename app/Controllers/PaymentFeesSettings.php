<?php

namespace App\Controllers;

use App\Controllers\BaseController;
use App\Models\PaymentGatewayModel;

class PaymentFeesSettings extends BaseController
{
    protected $paymentGatewayModel;

    public function __construct()
    {
        $this->paymentGatewayModel = new PaymentGatewayModel();
    }

    public function index()
    {
        $gateways = $this->paymentGatewayModel->getGatewaySettings();

        $data = [
            'title' => 'Pengaturan Biaya Admin Payment Gateway',
            'gateways' => $gateways,
        ];

        return view('settings/payment_fees', $data);
    }

    public function update()
    {
        try {
            $gatewayType = $this->request->getPost('gateway_type');
            $fees = $this->request->getPost('fees');

            if (empty($gatewayType)) {
                return $this->response->setJSON([
                    'success' => false,
                    'message' => 'Gateway type is required'
                ]);
            }

            // Validate and sanitize fees data
            $cleanFees = [];
            if (is_array($fees)) {
                foreach ($fees as $method => $fee) {
                    $cleanFees[$method] = max(0, (int)$fee); // Ensure non-negative integer
                }
            }

            // Update gateway admin fees
            $gateway = $this->paymentGatewayModel->getGatewayByType($gatewayType);

            if (!$gateway) {
                return $this->response->setJSON([
                    'success' => false,
                    'message' => 'Gateway not found'
                ]);
            }

            $updateData = [
                'admin_fees' => json_encode($cleanFees),
                'updated_at' => date('Y-m-d H:i:s')
            ];

            $updated = $this->paymentGatewayModel->update($gateway['id'], $updateData);

            if ($updated) {
                return $this->response->setJSON([
                    'success' => true,
                    'message' => 'Biaya admin berhasil diperbarui'
                ]);
            } else {
                return $this->response->setJSON([
                    'success' => false,
                    'message' => 'Gagal memperbarui biaya admin'
                ]);
            }
        } catch (\Exception $e) {
            log_message('error', 'PaymentFeesSettings Error: ' . $e->getMessage());

            return $this->response->setJSON([
                'success' => false,
                'message' => 'Terjadi kesalahan sistem: ' . $e->getMessage()
            ]);
        }
    }

    public function getGatewayFees($gatewayType)
    {
        try {
            $gateway = $this->paymentGatewayModel->getGatewayByType($gatewayType);

            if (!$gateway) {
                return $this->response->setJSON([
                    'success' => false,
                    'message' => 'Gateway not found'
                ]);
            }

            $adminFees = json_decode($gateway['admin_fees'] ?? '{}', true);

            return $this->response->setJSON([
                'success' => true,
                'data' => $adminFees,
                'gateway_name' => $gateway['gateway_name']
            ]);
        } catch (\Exception $e) {
            return $this->response->setJSON([
                'success' => false,
                'message' => 'Error: ' . $e->getMessage()
            ]);
        }
    }
}
