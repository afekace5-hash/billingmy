<?php

namespace App\Controllers\Api;

use App\Controllers\BaseController;

class PaymentFees extends BaseController
{
    public function index()
    {
        try {
            // Get payment method fees from database or configuration
            $paymentModel = new \App\Models\PaymentGatewayModel();
            $activeGateways = $paymentModel->getActiveGateways();

            $fees = [];

            foreach ($activeGateways as $gateway) {
                $gatewayType = $gateway['gateway_type'];
                $adminFeesJson = $gateway['admin_fees'] ?? '{}';
                $adminFees = json_decode($adminFeesJson, true) ?: [];

                // Merge fees from this gateway
                if (!empty($adminFees)) {
                    $fees = array_merge($fees, $adminFees);
                } else {
                    // Use default fees if no custom fees configured
                    switch ($gatewayType) {
                        case 'midtrans':
                            $defaultFees = [
                                'credit_card' => 0,
                                'bca_va' => 4000,
                                'bni_va' => 4000,
                                'bri_va' => 4000,
                                'mandiri_va' => 4000,
                                'echannel' => 4000,
                                'gopay' => 0,
                                'qris' => 0
                            ];
                            $fees = array_merge($fees, $defaultFees);
                            break;

                        case 'duitku':
                            $defaultFees = [
                                'bca_va' => 4000,
                                'bni_va' => 4000,
                                'bri_va' => 4000,
                                'mandiri_va' => 4000,
                                'ovo' => 0,
                                'dana' => 0,
                                'linkaja' => 0,
                                'shopeepay' => 1500,
                                'qris' => 0
                            ];
                            $fees = array_merge($fees, $defaultFees);
                            break;
                    }
                }
            }

            // Default fees if no gateway configuration found
            if (empty($fees)) {
                $fees = [
                    'credit_card' => 0,
                    'bca_va' => 4000,
                    'bni_va' => 4000,
                    'bri_va' => 4000,
                    'mandiri_va' => 4000,
                    'permata_va' => 4000,
                    'echannel' => 4000,
                    'gopay' => 0,
                    'ovo' => 0,
                    'dana' => 0,
                    'shopeepay' => 1500,
                    'linkaja' => 0,
                    'qris' => 0
                ];
            }

            return $this->response->setJSON([
                'success' => true,
                'data' => $fees,
                'message' => 'Payment fees retrieved successfully'
            ]);
        } catch (\Exception $e) {
            log_message('error', 'PaymentFees API Error: ' . $e->getMessage());

            return $this->response->setJSON([
                'success' => false,
                'message' => 'Gagal memuat biaya admin: ' . $e->getMessage(),
                'data' => []
            ]);
        }
    }
}
