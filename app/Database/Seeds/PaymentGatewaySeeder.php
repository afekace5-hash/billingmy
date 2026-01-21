<?php

namespace App\Database\Seeds;

use CodeIgniter\Database\Seeder;

class PaymentGatewaySeeder extends Seeder
{
    public function run()
    {
        $data = [
            [
                // 'gateway_name' => 'Tripay',
                // 'gateway_type' => 'tripay',
                'is_active' => 0,
                'api_key' => '',
                'api_secret' => '',
                'merchant_code' => '',
                'private_key' => '',
                'callback_key' => '',
                'environment' => 'sandbox',
                'settings' => json_encode([
                    // 'webhook_url' => base_url('payment/callback/tripay'),
                    'timeout' => 24,
                    'auto_settle' => true
                ]),
                'created_at' => date('Y-m-d H:i:s'),
                'updated_at' => date('Y-m-d H:i:s')
            ],
            [
                'gateway_name' => 'Xendit',
                'gateway_type' => 'xendit',
                'is_active' => 0,
                'api_key' => '',
                'api_secret' => '',
                'merchant_code' => '',
                'private_key' => '',
                'callback_key' => '',
                'environment' => 'test',
                'settings' => json_encode([
                    'webhook_url' => base_url('payment/callback/xendit'),
                    'timeout' => 24,
                    'auto_settle' => true
                ]),
                'created_at' => date('Y-m-d H:i:s'),
                'updated_at' => date('Y-m-d H:i:s')
            ],
            [
                'gateway_name' => 'Midtrans',
                'gateway_type' => 'midtrans',
                'is_active' => 0,
                'api_key' => '',
                'api_secret' => '',
                'merchant_code' => '',
                'private_key' => '',
                'callback_key' => '',
                'environment' => 'sandbox',
                'settings' => json_encode([
                    'webhook_url' => base_url('payment/callback/midtrans'),
                    'timeout' => 24,
                    'auto_settle' => true
                ]),
                'created_at' => date('Y-m-d H:i:s'),
                'updated_at' => date('Y-m-d H:i:s')
            ],
            [
                // 'gateway_name' => 'DOKU',
                // 'gateway_type' => 'doku',
                'is_active' => 0,
                'api_key' => '',
                'api_secret' => '',
                'merchant_code' => '',
                'private_key' => '',
                'callback_key' => '',
                'environment' => 'sandbox',
                'settings' => json_encode([
                    // 'webhook_url' => base_url('payment/callback/doku'),
                    'timeout' => 24,
                    'auto_settle' => true
                ]),
                'created_at' => date('Y-m-d H:i:s'),
                'updated_at' => date('Y-m-d H:i:s')
            ],
            [
                'gateway_name' => 'Duitku',
                'gateway_type' => 'duitku',
                'is_active' => 0,
                'api_key' => '',
                'api_secret' => '',
                'merchant_code' => '',
                'private_key' => '',
                'callback_key' => '',
                'environment' => 'sandbox',
                'settings' => json_encode([
                    'webhook_url' => base_url('payment/callback/duitku'),
                    'timeout' => 24,
                    'auto_settle' => true
                ]),
                'created_at' => date('Y-m-d H:i:s'),
                'updated_at' => date('Y-m-d H:i:s')
            ]
        ];

        // Insert data
        $this->db->table('payment_gateways')->insertBatch($data);
    }
}
