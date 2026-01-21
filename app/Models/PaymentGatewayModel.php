<?php

namespace App\Models;

use CodeIgniter\Model;

class PaymentGatewayModel extends Model
{
    protected $table = 'payment_gateways';
    protected $primaryKey = 'id';
    protected $allowedFields = [
        'gateway_name',
        'gateway_type',
        'is_active',
        'api_key',
        'api_secret',
        'merchant_code',
        'private_key',
        'callback_key',
        'environment',
        'settings',
        'admin_fees',
        'created_at',
        'updated_at'
    ];
    protected $useTimestamps = true;

    /**
     * Get active payment gateways with formatted methods
     */
    public function getActiveGateways()
    {
        $gateways = $this->where('is_active', 1)->findAll();
        $methods = [];
        $addedCodes = []; // Tracking untuk menghindari duplikasi

        foreach ($gateways as $gateway) {
            $adminFees = json_decode($gateway['admin_fees'] ?? '{}', true);

            if ($gateway['gateway_type'] === 'midtrans') {
                // Parse settings to get enabled payment channels
                $settings = json_decode($gateway['settings'] ?? '{}', true);
                $enabledChannels = $settings['enabled_channels'] ?? [];

                // Jika enabled_channels kosong, gunakan default channels yang aktif
                if (empty($enabledChannels)) {
                    // Default channels sesuai screenshot user: QRIS, GoPay, BNI, BRI, Mandiri, Permata
                    $enabledChannels = ['qris', 'gopay', 'bni', 'bri', 'mandiri', 'permata'];
                }

                // Mapping semua metode yang tersedia
                $allMidtransMethods = [
                    'bca' => ['name' => 'Transfer Bank BCA', 'admin_fee' => $adminFees['bank_transfer'] ?? 4000],
                    'bni' => ['name' => 'Transfer Bank BNI', 'admin_fee' => $adminFees['bank_transfer'] ?? 4000],
                    'bri' => ['name' => 'Transfer Bank BRI', 'admin_fee' => $adminFees['bank_transfer'] ?? 4000],
                    'mandiri' => ['name' => 'Transfer Bank Mandiri', 'admin_fee' => $adminFees['bank_transfer'] ?? 4000],
                    'permata' => ['name' => 'Transfer Bank Permata', 'admin_fee' => $adminFees['bank_transfer'] ?? 4000],
                    'gopay' => ['name' => 'GoPay', 'admin_fee' => $adminFees['gopay'] ?? 0],
                    'shopeepay' => ['name' => 'ShopeePay', 'admin_fee' => $adminFees['shopeepay'] ?? 2500],
                    'qris' => ['name' => 'QRIS', 'admin_fee' => $adminFees['qris'] ?? 0],
                    'alfamart' => ['name' => 'Alfamart', 'admin_fee' => $adminFees['cstore'] ?? 2500],
                    'indomaret' => ['name' => 'Indomaret', 'admin_fee' => $adminFees['cstore'] ?? 2500],
                ];

                // Hanya tambahkan metode yang ada di enabled_channels
                foreach ($enabledChannels as $channelCode) {
                    if (isset($allMidtransMethods[$channelCode]) && !isset($addedCodes[$channelCode])) {
                        $method = $allMidtransMethods[$channelCode];
                        $methods[] = [
                            'gateway' => $gateway['gateway_type'],
                            'gateway_name' => $gateway['gateway_name'],
                            'code' => $channelCode,
                            'name' => $method['name'],
                            'admin_fee' => $method['admin_fee']
                        ];
                        $addedCodes[$channelCode] = true;
                    }
                }
            } elseif ($gateway['gateway_type'] === 'duitku') {
                // Get Duitku payment methods from API
                try {
                    $duitkuService = new \App\Libraries\Payment\DuitkuService($gateway);
                    $duitkuResult = $duitkuService->getPaymentMethods();

                    if ($duitkuResult['success'] && !empty($duitkuResult['data'])) {
                        foreach ($duitkuResult['data'] as $method) {
                            if (!isset($addedCodes[$method['code']])) {
                                // Get admin fee for this method
                                $adminFee = $duitkuService->getMethodFee($method['code']);

                                $methods[] = [
                                    'gateway' => $gateway['gateway_type'],
                                    'gateway_name' => $gateway['gateway_name'],
                                    'code' => $method['code'], // Use Duitku's actual code (BC, M2, etc)
                                    'name' => $method['name'],
                                    'type' => $method['type'] ?? 'other',
                                    'admin_fee' => $adminFee
                                ];
                                $addedCodes[$method['code']] = true;
                            }
                        }
                    } else {
                        // Fallback to default methods if API call fails
                        log_message('warning', 'Failed to get Duitku payment methods from API, using fallback');
                    }
                } catch (\Exception $e) {
                    log_message('error', 'Error getting Duitku payment methods: ' . $e->getMessage());
                }
            } elseif ($gateway['gateway_type'] === 'flip') {
                // Get Flip payment methods
                try {
                    $flipService = new \App\Libraries\Payment\FlipService($gateway);
                    $flipResult = $flipService->getPaymentMethods();

                    if ($flipResult['success'] && !empty($flipResult['data'])) {
                        foreach ($flipResult['data'] as $method) {
                            if (!isset($addedCodes[$method['code']])) {
                                // Get admin fee for this method
                                $adminFee = $flipService->getMethodFee($method['code']);

                                $methods[] = [
                                    'gateway' => $gateway['gateway_type'],
                                    'gateway_name' => $gateway['gateway_name'],
                                    'code' => $method['code'],
                                    'name' => $method['name'],
                                    'type' => $method['type'] ?? 'other',
                                    'admin_fee' => $adminFee
                                ];
                                $addedCodes[$method['code']] = true;
                            }
                        }
                    }
                } catch (\Exception $e) {
                    log_message('error', 'Error getting Flip payment methods: ' . $e->getMessage());
                }
            }
        }

        log_message('info', 'getActiveGateways() returning ' . count($methods) . ' payment methods');
        if (count($methods) > 0) {
            log_message('info', 'Sample method: ' . json_encode($methods[0]));
        }

        return $methods;
    }
    /**
     * Get gateway by type
     */
    public function getGatewayByType($type)
    {
        return $this->where('gateway_type', $type)->first();
    }

    /**
     * Get active gateway by type
     */
    public function getActiveGatewayByType($type)
    {
        return $this->where('gateway_type', $type)->where('is_active', 1)->first();
    }

    /**
     * Get active gateway configurations (not methods)
     * Returns array of gateway configs with gateway_type, gateway_name, is_active, etc.
     */
    public function getActiveGatewayConfigs()
    {
        return $this->where('is_active', 1)->findAll();
    }

    /**
     * Get all gateway settings as key-value pairs
     */
    public function getGatewaySettings()
    {
        $gateways = $this->findAll();
        $settings = [];

        foreach ($gateways as $gateway) {
            $settings[$gateway['gateway_type']] = [
                'id' => $gateway['id'],
                'name' => $gateway['gateway_name'],
                'is_active' => $gateway['is_active'],
                'api_key' => $gateway['api_key'],
                'api_secret' => $gateway['api_secret'],
                'merchant_code' => $gateway['merchant_code'],
                'private_key' => $gateway['private_key'],
                'callback_key' => $gateway['callback_key'],
                'environment' => $gateway['environment'],
                'settings' => json_decode($gateway['settings'] ?? '{}', true),
                'admin_fees' => json_decode($gateway['admin_fees'] ?? '{}', true)
            ];
        }

        return $settings;
    }

    /**
     * Save or update gateway configuration
     */
    public function saveGatewayConfig($gatewayType, $data)
    {
        $existing = $this->where('gateway_type', $gatewayType)->first();

        if ($existing) {
            return $this->update($existing['id'], $data);
        } else {
            $data['gateway_type'] = $gatewayType;
            return $this->insert($data);
        }
    }
}
