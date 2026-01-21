<?php

namespace App\Controllers\Api;

use App\Controllers\BaseController;
use App\Libraries\Payment\PaymentGatewayFactory;

class PaymentMethods extends BaseController
{
    public function index()
    {
        try {
            // Get active gateways
            $gatewayModel = new \App\Models\PaymentGatewayModel();
            $activeGateways = $gatewayModel->where('is_active', 1)->findAll();

            if (empty($activeGateways)) {
                return $this->response->setJSON([
                    'success' => false,
                    'message' => 'Tidak ada payment gateway yang aktif',
                    'payment_methods' => []
                ]);
            }

            $allPaymentMethods = [];

            // Process each active gateway
            foreach ($activeGateways as $gateway) {
                $gatewayType = $gateway['gateway_type'];

                try {
                    if ($gatewayType === 'duitku') {
                        // Use DuitkuService directly (same as dashboard)
                        $duitkuService = new \App\Libraries\Payment\DuitkuService($gateway);
                        $methods = $duitkuService->getAvailablePaymentMethods();

                        // Transform methods to match expected format
                        foreach ($methods as $method) {
                            $adminFee = $this->getMethodAdminFee($method['code'], $gatewayType, [$gateway]);

                            // Determine fee type (percentage or fixed)
                            $feeType = 'fixed';
                            $percentageMethods = ['OV', 'DA', 'SP', 'SA', 'FT', 'ovo', 'dana', 'shopeepay', 'qris'];
                            if (in_array($method['code'], $percentageMethods)) {
                                $feeType = 'percent';
                            }

                            // Debug log
                            log_message('debug', "Duitku method: {$method['name']} (code: {$method['code']}) - Fee: {$adminFee}, Type: {$feeType}");

                            $allPaymentMethods[] = [
                                'gateway' => $gatewayType,
                                'gateway_name' => $gateway['gateway_name'],
                                'code' => $method['code'],
                                'name' => $method['name'],
                                'type' => $method['type'] ?? 'other',
                                'active' => true,
                                'provider' => $this->getMethodProvider($method['code'], $gatewayType),
                                'admin_fee' => $adminFee,
                                'admin_fee_type' => $feeType
                            ];
                        }

                        log_message('info', "Duitku methods loaded: " . count($methods));
                    } elseif ($gatewayType === 'midtrans') {
                        // Use MidtransService for Midtrans
                        $midtransService = new \App\Libraries\Payment\MidtransService($gateway);
                        $result = $midtransService->getPaymentMethods();

                        if ($result['success'] && !empty($result['data'])) {
                            foreach ($result['data'] as $method) {
                                $adminFee = $this->getMethodAdminFee($method['code'], $gatewayType, [$gateway]);

                                // Determine fee type for Midtrans (gopay and qris are percentage)
                                $feeType = 'fixed';
                                if (in_array($method['code'], ['gopay', 'qris'])) {
                                    $feeType = 'percent';
                                }

                                $allPaymentMethods[] = [
                                    'gateway' => $gatewayType,
                                    'gateway_name' => $gateway['gateway_name'],
                                    'code' => $method['code'],
                                    'name' => $method['name'],
                                    'type' => $method['type'] ?? 'other',
                                    'active' => $method['active'] ?? true,
                                    'provider' => $this->getMethodProvider($method['code'], $gatewayType),
                                    'admin_fee' => $adminFee,
                                    'admin_fee_type' => $feeType
                                ];
                            }

                            log_message('info', "Midtrans methods loaded: " . count($result['data']));
                        }
                    } elseif ($gatewayType === 'flip') {
                        // Use FlipService for Flip payment gateway
                        $flipService = new \App\Libraries\Payment\FlipService($gateway);
                        $methods = $flipService->getAvailablePaymentMethods();

                        if (!empty($methods)) {
                            foreach ($methods as $method) {
                                $adminFee = $this->getMethodAdminFee($method['code'], $gatewayType, [$gateway]);

                                // All Flip fees are fixed (not percentage)
                                $feeType = 'fixed';

                                $allPaymentMethods[] = [
                                    'gateway' => $gatewayType,
                                    'gateway_name' => $gateway['gateway_name'],
                                    'code' => $method['code'],
                                    'name' => $method['name'],
                                    'type' => $method['type'] ?? 'other',
                                    'active' => true,
                                    'provider' => $this->getMethodProvider($method['code'], $gatewayType),
                                    'admin_fee' => $adminFee,
                                    'admin_fee_type' => $feeType
                                ];
                            }

                            log_message('info', "Flip methods loaded: " . count($methods));
                        }
                    }
                } catch (\Exception $e) {
                    log_message('error', "Failed to load methods for {$gatewayType}: " . $e->getMessage());
                    continue;
                }
            }

            if (empty($allPaymentMethods)) {
                return $this->response->setJSON([
                    'success' => false,
                    'message' => 'Tidak ada metode pembayaran yang tersedia. Gateway yang aktif belum dikonfigurasi dengan benar.',
                    'payment_methods' => []
                ]);
            }

            return $this->response->setJSON([
                'success' => true,
                'message' => 'Payment methods retrieved successfully',
                'payment_methods' => $allPaymentMethods,
                'total_methods' => count($allPaymentMethods),
                'gateways' => array_unique(array_column($allPaymentMethods, 'gateway'))
            ]);
        } catch (\Exception $e) {
            log_message('error', 'PaymentMethods API Error: ' . $e->getMessage());

            return $this->response->setJSON([
                'success' => false,
                'message' => 'Gagal memuat metode pembayaran: ' . $e->getMessage(),
                'payment_methods' => [],
                'error_details' => $e->getMessage()
            ]);
        }
    }

    /**
     * Get provider name for payment method display
     */
    private function getMethodProvider($methodCode, $gatewayType)
    {
        // More specific provider names based on method and gateway
        $providers = [
            'credit_card' => 'Visa/Mastercard',
            'bca_va' => 'Bank BCA',
            'bni_va' => 'Bank BNI',
            'bri_va' => 'Bank BRI',
            'mandiri_va' => 'Bank Mandiri',
            'echannel' => 'Bank Mandiri',
            'permata_va' => 'Bank Permata',
            'gopay' => 'GoPay',
            'ovo' => 'OVO',
            'dana' => 'DANA',
            'shopeepay' => 'ShopeePay',
            'linkaja' => 'LinkAja',
            'qris' => 'QRIS',
            // Flip payment methods
            'bni' => 'Bank BNI',
            'bri' => 'Bank BRI',
            'mandiri' => 'Bank Mandiri',
            'bca' => 'Bank BCA',
            'permata' => 'Bank Permata',
            'cimb' => 'CIMB Niaga',
            'danamon' => 'Bank Danamon',
            'maybank' => 'Maybank',
            'alfamart' => 'Alfamart',
            'indomaret' => 'Indomaret'
        ];

        return $providers[$methodCode] ?? ucfirst($gatewayType);
    }

    /**
     * Get admin fee directly from payment gateway service
     */
    private function getMethodAdminFee($methodCode, $gatewayType, $activeGateways)
    {
        try {
            // Find the specific gateway config
            $gatewayConfig = null;
            foreach ($activeGateways as $gateway) {
                if ($gateway['gateway_type'] === $gatewayType) {
                    $gatewayConfig = $gateway;
                    break;
                }
            }

            if (!$gatewayConfig) {
                return $this->getDefaultAdminFee($methodCode);
            }

            // Try to get fee from the gateway service directly
            switch ($gatewayType) {
                case 'midtrans':
                    return $this->getMidtransMethodFee($methodCode, $gatewayConfig);
                case 'duitku':
                    return $this->getDuitkuMethodFee($methodCode, $gatewayConfig);
                default:
                    return $this->getDefaultAdminFee($methodCode);
            }
        } catch (\Exception $e) {
            log_message('warning', 'Could not get admin fee for ' . $methodCode . ': ' . $e->getMessage());
            return $this->getDefaultAdminFee($methodCode);
        }
    }

    /**
     * Get admin fee from Midtrans service
     */
    private function getMidtransMethodFee($methodCode, $gatewayConfig)
    {
        // Try to load Midtrans service and get actual fees
        try {
            $midtransService = new \App\Libraries\Payment\MidtransService($gatewayConfig);

            // Check if the service has a method to get fees
            if (method_exists($midtransService, 'getMethodFee')) {
                return $midtransService->getMethodFee($methodCode);
            }

            // Fallback to default Midtrans fees
            $midtransFees = [
                'credit_card' => 4000,
                'bca_va' => 4000,
                'bni_va' => 4000,
                'bri_va' => 4000,
                'mandiri_va' => 4000,
                'echannel' => 4000,
                'gopay' => 2,      // 2% fee
                'qris' => 0.7      // 0.7% fee
            ];

            $fee = $duitkuFees[$methodCode] ?? 4000;
            log_message('debug', "Duitku Method Fee: {$methodCode} = {$fee}");
            return $fee;
        } catch (\Exception $e) {
            return $this->getDefaultAdminFee($methodCode);
        }
    }

    /**
     * Get admin fee from Duitku service
     */
    private function getDuitkuMethodFee($methodCode, $gatewayConfig)
    {
        try {
            $duitkuService = new \App\Libraries\Payment\DuitkuService($gatewayConfig);

            if (method_exists($duitkuService, 'getMethodFee')) {
                return $duitkuService->getMethodFee($methodCode);
            }

            // Fallback to default Duitku fees based on actual rates
            $duitkuFees = [
                // Virtual Accounts (fixed fee per transaction)
                'BC' => 5000,      // BCA
                'M2' => 4000,      // Mandiri
                'BN' => 4000,      // BNI (Maybank)
                'BRI' => 4000,     // BRI
                'VA' => 4000,      // Maybank VA
                'I1' => 4000,      // BNI VA
                'B1' => 4000,      // CIMB Niaga

                // E-Wallets (percentage)
                'OV' => 1.67,      // OVO - 1.67%
                'DA' => 1.67,      // DANA - 1.67%
                'LF' => 3330,      // LinkAja - Rp. 3.330 fixed
                'SA' => 1.67,      // ShopeePay - 2%
                'SP' => 2,         // ShopeePay - 2%

                // QRIS (percentage)
                'FT' => 0.7,       // QRIS - 0.7%

                // Retail (fixed + MDR)
                'A1' => 2500,      // Alfamart - Rp. 2.500
                'I1' => 1000,      // Indomaret - MDR + Rp. 1.000

                // Artha Graha & Sahabat Sampoerna (fixed)
                'AG' => 1500,      // Artha Graha - Rp. 1.500
                'S1' => 1500,      // Sampoerna - Rp. 1.500

                // Pegadaian & POS Indonesia
                'PG' => 2500,      // Pegadaian - Rp. 2.500
                'PO' => 2500,      // POS Indonesia - Rp. 2.500

                // Legacy codes (for backward compatibility)
                'bca_va' => 5000,
                'bni_va' => 4000,
                'bri_va' => 4000,
                'mandiri_va' => 4000,
                'ovo' => 1.67,
                'dana' => 1.67,
                'linkaja' => 3330,
                'shopeepay' => 2,
                'qris' => 0.7,
                'alfamart' => 2500,
                'indomaret' => 1000
            ];

            // GoPay via Duitku (if available)
            if ($methodCode === 'gopay') {
                return 2; // 2% fee
            }

            return $duitkuFees[$methodCode] ?? 4000;
        } catch (\Exception $e) {
            return $this->getDefaultAdminFee($methodCode);
        }
    }

    /**
     * Get default admin fee for a payment method
     */
    private function getDefaultAdminFee($methodCode)
    {
        // Default fees
        $defaultFees = [
            'credit_card' => 4000,
            'bca_va' => 4000,
            'bni_va' => 4000,
            'bri_va' => 4000,
            'mandiri_va' => 4000,
            'permata_va' => 4000,
            'echannel' => 4000,
            'gopay' => 2,      // 2% fee
            'ovo' => 4000,
            'dana' => 4000,
            'shopeepay' => 4000,
            'linkaja' => 4000,
            'qris' => 0.7      // 0.7% fee
        ];

        return $defaultFees[$methodCode] ?? 4000;
    }
}
