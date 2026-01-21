<?php

namespace App\Libraries\Payment;

use App\Models\PaymentGatewayModel;

class PaymentGatewayFactory
{
    private static $gatewayModel;
    public static function create(string $gatewayType): ?PaymentGatewayInterface
    {
        self::$gatewayModel = self::$gatewayModel ?: new PaymentGatewayModel();

        $config = self::$gatewayModel->getActiveGatewayByType($gatewayType);

        if (!$config || !$config['is_active']) {
            return null;
        }
        switch ($gatewayType) {
            // case 'tripay':
            //     return new TripayService($config);

            // case 'xendit':
            //     return new XenditService($config);

            case 'midtrans':
                return new MidtransService($config);

                // case 'doku':
                //     return new DOKUService($config);

            case 'duitku':
                return new DuitkuService($config);

            case 'flip':
                return new FlipService($config);

            default:
                return null;
        }
    }

    public static function getActiveGateways(): array
    {
        self::$gatewayModel = self::$gatewayModel ?: new PaymentGatewayModel();
        // Use findAll() to get array of gateway configs, not payment methods
        $activeGateways = self::$gatewayModel->where('is_active', 1)->findAll();
        $services = [];

        foreach ($activeGateways as $gateway) {
            if (!isset($gateway['gateway_type'])) continue;
            $service = self::create($gateway['gateway_type']);
            if ($service) {
                $services[$gateway['gateway_type']] = [
                    'service' => $service,
                    'config' => $gateway
                ];
            }
        }

        return $services;
    }

    public static function getAvailablePaymentMethods(): array
    {
        $activeGateways = self::getActiveGateways();
        $allMethods = [];

        foreach ($activeGateways as $gatewayType => $gateway) {
            // Check if gateway service is available and properly configured
            if (!$gateway['service']) {
                continue;
            }

            $methods = $gateway['service']->getPaymentMethods();
            if ($methods['success'] && !empty($methods['data'])) {
                $allMethods[$gatewayType] = [
                    'name' => $gateway['config']['gateway_name'],
                    'methods' => $methods['data'],
                    'message' => $methods['message'] ?? 'Available'
                ];
            } else {
                // Log why this gateway was skipped
                log_message('info', "Gateway {$gatewayType} skipped: " . ($methods['message'] ?? 'No methods available'));
            }
        }

        return $allMethods;
    }
}
