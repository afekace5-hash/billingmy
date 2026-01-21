<?php

namespace App\Libraries\Payment;

use App\Libraries\Payment\PaymentGatewayInterface;

class DuitkuService implements PaymentGatewayInterface
{
    private $merchantCode;
    private $apiKey;
    private $environment;
    private $baseUrl;

    public function __construct(array $config)
    {
        $this->merchantCode = $config['merchant_code'] ?? '';
        $this->apiKey = $config['api_key'] ?? '';
        $this->environment = $config['environment'] ?? 'sandbox';

        $this->baseUrl = $this->environment === 'production'
            ? 'https://passport.duitku.com'
            : 'https://sandbox.duitku.com';
    }

    public function createTransaction(array $data): array
    {
        try {
            // Validate configuration first
            if (empty($this->merchantCode) || empty($this->apiKey)) {
                log_message('error', 'Duitku configuration missing - Merchant Code: ' . (empty($this->merchantCode) ? 'EMPTY' : 'OK') . ', API Key: ' . (empty($this->apiKey) ? 'EMPTY' : 'OK'));
                return [
                    'success' => false,
                    'message' => 'Konfigurasi Duitku tidak lengkap. Hubungi administrator.',
                    'data' => []
                ];
            }

            // Define variables first
            $merchantOrderId = $data['order_id'];
            $paymentAmount = (int) $data['amount'];

            // Get payment method from data
            $paymentMethod = $data['method'] ?? 'BC';

            log_message('info', 'Received payment method from request: ' . $paymentMethod);

            // Create signature: MD5(merchantCode + merchantOrderId + paymentAmount + apiKey)
            $signature = md5($this->merchantCode . $merchantOrderId . $paymentAmount . $this->apiKey);

            // Clean phone number (remove spaces, dashes)
            $phoneNumber = preg_replace('/[^0-9]/', '', $data['customer_phone'] ?? '08123456789');
            // Duitku accepts both 08xxx and 628xxx format, keep as is if valid
            if (!preg_match('/^(08|628)/', $phoneNumber)) {
                $phoneNumber = '08123456789'; // fallback
            }

            // Build payload according to Duitku API documentation
            // Get expiry time from config (default 24 hours)
            $expiryHours = isset($this->config['payment_expiry_hours']) ? (int)$this->config['payment_expiry_hours'] : 24;
            $expiryMinutes = $expiryHours * 60;

            $payload = [
                'merchantCode' => $this->merchantCode,
                'paymentAmount' => $paymentAmount,
                'paymentMethod' => $paymentMethod,
                'merchantOrderId' => $merchantOrderId,
                'productDetails' => $data['description'] ?? 'Pembayaran Tagihan Internet',
                'customerVaName' => substr($data['customer_name'], 0, 20), // Max 20 chars
                'email' => $data['customer_email'],
                'phoneNumber' => $phoneNumber,
                'callbackUrl' => $data['callback_url'] ?? base_url('payment/callback/duitku'),
                'returnUrl' => $data['return_url'] ?? base_url(),
                'signature' => $signature,
                'expiryPeriod' => $expiryMinutes // Expiry time in minutes from config
            ];

            log_message('info', 'Duitku payment expiry set to: ' . $expiryHours . ' hours (' . $expiryMinutes . ' minutes)');

            // Log payload for debugging
            log_message('info', 'Duitku API URL: ' . $this->baseUrl . '/webapi/api/merchant/v2/inquiry');
            log_message('info', 'Duitku Merchant Code: ' . $this->merchantCode);
            log_message('info', 'Duitku Signature String: ' . $this->merchantCode . ' + ' . $merchantOrderId . ' + ' . $paymentAmount . ' + [API_KEY]');
            log_message('info', 'Duitku Signature: ' . $signature);
            log_message('info', 'Duitku Create Transaction Payload: ' . json_encode($payload, JSON_PRETTY_PRINT));

            $response = $this->makeRequest('/webapi/api/merchant/v2/inquiry', 'POST', $payload);

            // Log response for debugging
            log_message('info', 'Duitku Create Transaction Response: ' . json_encode($response));
            log_message('info', 'Duitku Response Status Code: ' . ($response['statusCode'] ?? 'NOT SET'));

            // Check if response has error message
            if (isset($response['statusCode']) && $response['statusCode'] !== '00') {
                $errorMsg = $response['statusMessage'] ?? 'Unknown error';

                // Add more details if available
                if (isset($response['fullResponse'])) {
                    $errorMsg .= ' - ' . $response['fullResponse'];
                }

                log_message('error', 'Duitku transaction failed: ' . $errorMsg);
                return [
                    'success' => false,
                    'data' => $response,
                    'message' => $errorMsg,
                    'payment_url' => '',
                    'transaction_id' => '',
                    'va_number' => ''
                ];
            }

            return [
                'success' => isset($response['statusCode']) && $response['statusCode'] === '00',
                'data' => $response,
                'message' => $response['statusMessage'] ?? 'Transaction created',
                'payment_url' => $response['paymentUrl'] ?? '',
                'transaction_id' => $response['reference'] ?? '',
                'va_number' => $response['vaNumber'] ?? ''
            ];
        } catch (\Exception $e) {
            return [
                'success' => false,
                'message' => 'Duitku Error: ' . $e->getMessage(),
                'data' => []
            ];
        }
    }

    public function getTransactionStatus(string $transactionId): array
    {
        try {
            $signature = md5($this->merchantCode . $transactionId . $this->apiKey);

            $payload = [
                'merchantCode' => $this->merchantCode,
                'merchantOrderId' => $transactionId,
                'signature' => $signature
            ];

            $response = $this->makeRequest('/webapi/api/merchant/transactionStatus', 'POST', $payload);

            return [
                'success' => isset($response['statusCode']) && $response['statusCode'] === '00',
                'status' => $this->mapStatus($response['statusCode'] ?? ''),
                'data' => $response,
                'message' => $response['statusMessage'] ?? 'Status retrieved'
            ];
        } catch (\Exception $e) {
            return [
                'success' => false,
                'message' => 'Duitku Error: ' . $e->getMessage(),
                'data' => []
            ];
        }
    }

    public function getPaymentMethods(): array
    {
        try {
            // Check if Duitku is properly configured
            if (empty($this->merchantCode) || empty($this->apiKey)) {
                log_message('error', 'Duitku config incomplete - merchantCode: ' . (empty($this->merchantCode) ? 'EMPTY' : 'OK') . ', apiKey: ' . (empty($this->apiKey) ? 'EMPTY' : 'OK'));
                return [
                    'success' => false,
                    'message' => 'Duitku tidak dikonfigurasi dengan benar',
                    'data' => []
                ];
            }

            log_message('info', 'Fetching Duitku payment methods from API...');

            // Try to fetch active payment methods from Duitku API
            $activeMethods = $this->fetchActivePaymentMethods();

            if (!empty($activeMethods)) {
                log_message('info', 'Successfully fetched ' . count($activeMethods) . ' payment methods from Duitku API');
                return [
                    'success' => true,
                    'data' => $activeMethods,
                    'message' => 'Payment methods retrieved from Duitku API'
                ];
            }

            // Fallback to default methods if API call fails
            log_message('warning', 'Duitku API returned no methods, using default fallback');
            $defaultMethods = $this->getDefaultPaymentMethods();

            // Add 'active' flag to each method for compatibility
            $defaultMethods = array_map(function ($method) {
                $method['active'] = true;
                return $method;
            }, $defaultMethods);

            log_message('info', 'Using ' . count($defaultMethods) . ' default payment methods');

            return [
                'success' => true,
                'data' => $defaultMethods,
                'message' => 'Payment methods retrieved (using default fallback methods)'
            ];
        } catch (\Exception $e) {
            return [
                'success' => false,
                'message' => 'Duitku Error: ' . $e->getMessage(),
                'data' => []
            ];
        }
    }

    /**
     * Get available payment methods (public wrapper)
     */
    public function getAvailablePaymentMethods(): array
    {
        // Try to fetch from API, fallback to default methods if fails
        $methods = $this->fetchActivePaymentMethods();

        if (empty($methods)) {
            log_message('info', 'Using default payment methods as fallback');
            return $this->getDefaultPaymentMethods();
        }

        return $methods;
    }

    /**
     * Get default payment methods (fallback when API fails)
     */
    private function getDefaultPaymentMethods(): array
    {
        return [
            [
                'code' => 'BC',
                'name' => 'BCA Virtual Account',
                'type' => 'bank_transfer'
            ],
            [
                'code' => 'M2',
                'name' => 'Mandiri Virtual Account',
                'type' => 'bank_transfer'
            ],
            [
                'code' => 'BN',
                'name' => 'BNI Virtual Account',
                'type' => 'bank_transfer'
            ],
            [
                'code' => 'BRI',
                'name' => 'BRI Virtual Account',
                'type' => 'bank_transfer'
            ],
            [
                'code' => 'FT',
                'name' => 'QRIS (Permata)',
                'type' => 'qris'
            ],
            [
                'code' => 'OV',
                'name' => 'OVO',
                'type' => 'ewallet'
            ],
            [
                'code' => 'DA',
                'name' => 'DANA',
                'type' => 'ewallet'
            ],
            [
                'code' => 'SP',
                'name' => 'ShopeePay',
                'type' => 'ewallet'
            ],
            [
                'code' => 'LF',
                'name' => 'LinkAja',
                'type' => 'ewallet'
            ],
            [
                'code' => 'A1',
                'name' => 'Alfamart',
                'type' => 'retail'
            ],
            [
                'code' => 'I1',
                'name' => 'Indomaret',
                'type' => 'retail'
            ]
        ];
    }

    /**
     * Fetch active payment methods from Duitku API
     */
    private function fetchActivePaymentMethods(): array
    {
        try {
            $datetime = date('Y-m-d H:i:s');
            $amount = '10000';

            // Signature for payment methods: SHA256(merchantCode + amount + datetime + apiKey)
            // NOTE: Different from transaction signature which uses MD5!
            $signatureString = $this->merchantCode . $amount . $datetime . $this->apiKey;
            $signature = hash('sha256', $signatureString);

            $requestData = [
                'merchantcode' => $this->merchantCode,
                'amount' => $amount,
                'datetime' => $datetime,
                'signature' => $signature
            ];

            // Log signature generation for debugging
            log_message('info', 'Duitku Payment Methods Signature String: ' . $this->merchantCode . ' + ' . $amount . ' + ' . $datetime . ' + [API_KEY]');
            log_message('info', 'Duitku Payment Methods Signature (SHA256): ' . $signature);
            log_message('info', 'Duitku Payment Methods Request: ' . json_encode($requestData));

            // Make API call to get available payment methods
            $response = $this->makeRequest('/webapi/api/merchant/paymentmethod/getpaymentmethod', 'POST', $requestData);

            // Log response for debugging
            log_message('info', 'Duitku Payment Methods Response: ' . json_encode($response));

            if (isset($response['paymentFee']) && is_array($response['paymentFee'])) {
                $activeMethods = [];
                $methodMap = $this->getMethodMapping();

                foreach ($response['paymentFee'] as $method) {
                    $code = $method['paymentMethod'] ?? '';
                    if (isset($methodMap[$code])) {
                        $activeMethods[] = $methodMap[$code];
                    }
                }

                log_message('info', 'Active payment methods found: ' . count($activeMethods));
                return $activeMethods;
            }

            log_message('warning', 'No paymentFee in Duitku response');
            return [];
        } catch (\Exception $e) {
            log_message('error', 'Could not fetch Duitku payment methods: ' . $e->getMessage());
            log_message('error', 'Stack trace: ' . $e->getTraceAsString());
            return [];
        }
    }

    /**
     * Get method mapping for Duitku payment types
     */
    private function getMethodMapping(): array
    {
        return [
            'VC' => [
                'code' => 'VC',
                'name' => 'Credit Card',
                'type' => 'card',
                'active' => true
            ],
            'M2' => [
                'code' => 'M2',
                'name' => 'Mandiri Virtual Account',
                'type' => 'bank_transfer',
                'active' => true
            ],
            'VA' => [
                'code' => 'VA',
                'name' => 'Maybank Virtual Account',
                'type' => 'bank_transfer',
                'active' => true
            ],
            'I1' => [
                'code' => 'I1',
                'name' => 'BNI Virtual Account',
                'type' => 'bank_transfer',
                'active' => true
            ],
            'B1' => [
                'code' => 'B1',
                'name' => 'CIMB Niaga Virtual Account',
                'type' => 'bank_transfer',
                'active' => true
            ],
            'BT' => [
                'code' => 'BT',
                'name' => 'Permata Virtual Account',
                'type' => 'bank_transfer',
                'active' => true
            ],
            'A1' => [
                'code' => 'A1',
                'name' => 'ATM Bersama',
                'type' => 'bank_transfer',
                'active' => true
            ],
            'AG' => [
                'code' => 'AG',
                'name' => 'Bank Artha Graha',
                'type' => 'bank_transfer',
                'active' => true
            ],
            'OV' => [
                'code' => 'OV',
                'name' => 'OVO',
                'type' => 'ewallet',
                'active' => true
            ],
            'DA' => [
                'code' => 'DA',
                'name' => 'DANA',
                'type' => 'ewallet',
                'active' => true
            ],
            'LA' => [
                'code' => 'LA',
                'name' => 'LinkAja',
                'type' => 'ewallet',
                'active' => true
            ],
            'SP' => [
                'code' => 'SP',
                'name' => 'ShopeePay',
                'type' => 'ewallet',
                'active' => true
            ],
            'FT' => [
                'code' => 'FT',
                'name' => 'Retail (Alfamart/Indomaret)',
                'type' => 'over_counter',
                'active' => true
            ]
        ];
    }

    public function handleCallback(array $data): array
    {
        return [
            'order_id' => $data['merchantOrderId'] ?? '',
            'transaction_id' => $data['reference'] ?? '',
            'status' => $this->mapStatus($data['resultCode'] ?? ''),
            'amount' => $data['amount'] ?? 0,
            'paid_amount' => $data['amount'] ?? 0,
            'payment_method' => $data['paymentCode'] ?? '',
            'raw_data' => $data
        ];
    }

    public function verifyCallback(array $data, string $signature): bool
    {
        $merchantOrderId = $data['merchantOrderId'] ?? '';
        $amount = $data['amount'] ?? '';
        $resultCode = $data['resultCode'] ?? '';

        // Formula untuk callback: MD5(merchantcode + amount + merchantOrderId + apiKey)
        $calculatedSignature = md5($this->merchantCode . $amount . $merchantOrderId . $this->apiKey);

        return hash_equals($calculatedSignature, $signature);
    }
    public function testConnection(): array
    {
        try {
            // Check if required credentials are configured
            if (empty($this->merchantCode) || empty($this->apiKey)) {
                return [
                    'success' => false,
                    'message' => 'Duitku credentials not configured. Please set Merchant Code and API Key.',
                    'data' => [
                        'missing_config' => [
                            'merchant_code' => empty($this->merchantCode),
                            'api_key' => empty($this->apiKey)
                        ]
                    ]
                ];
            }

            $testOrderId = 'test-' . time();
            $testAmount = 10000;

            $payload = [
                'merchantCode' => $this->merchantCode,
                'paymentAmount' => $testAmount,
                'paymentMethod' => 'BC',
                'merchantOrderId' => $testOrderId,
                'productDetails' => 'Test Connection',
                'customerVaName' => 'Test Customer',
                'email' => 'test@example.com',
                'phoneNumber' => '08123456789',
                'callbackUrl' => base_url('payment/callback/duitku'),
                'returnUrl' => base_url(),
                'signature' => $this->createSignature($testOrderId, $testAmount),
                'expiryPeriod' => 10
            ];

            $response = $this->makeRequest('/webapi/api/merchant/v2/inquiry', 'POST', $payload);

            return [
                'success' => isset($response['statusCode']),
                'message' => isset($response['statusCode']) ? 'Duitku connection successful' : 'Duitku connection failed',
                'data' => $response
            ];
        } catch (\Exception $e) {
            return [
                'success' => false,
                'message' => 'Connection test failed: ' . $e->getMessage(),
                'data' => []
            ];
        }
    }

    private function createSignature(string $orderId, int $amount): string
    {
        return md5($this->merchantCode . $orderId . $amount . $this->apiKey);
    }

    /**
     * Generate signature for Duitku API requests
     */
    private function generateSignature(array $params): string
    {
        $signatureString = '';
        foreach ($params as $key => $value) {
            $signatureString .= $value;
        }
        $signatureString .= $this->apiKey;

        return md5($signatureString);
    }

    private function makeRequest(string $endpoint, string $method = 'GET', array $data = []): array
    {
        try {
            $client = \Config\Services::curlrequest();

            $options = [
                'headers' => [
                    'Content-Type' => 'application/json'
                ],
                'timeout' => 30,
                'verify' => false, // Disable SSL verification for development
                'http_errors' => false // Don't throw exception on 4xx/5xx
            ];

            if ($method === 'POST' && !empty($data)) {
                $options['json'] = $data;
            }

            log_message('info', 'Making request to: ' . $this->baseUrl . $endpoint);

            $response = $client->request($method, $this->baseUrl . $endpoint, $options);

            $statusCode = $response->getStatusCode();
            $body = $response->getBody();

            log_message('info', 'Response Status: ' . $statusCode);
            log_message('info', 'Response Body: ' . $body);

            if ($statusCode >= 400) {
                log_message('error', 'HTTP Error ' . $statusCode . ': ' . $body);

                // Try to parse error message from response
                $errorData = json_decode($body, true);
                $errorMsg = 'HTTP Error ' . $statusCode;

                if ($errorData) {
                    if (isset($errorData['Message'])) {
                        $errorMsg .= ': ' . $errorData['Message'];
                    } elseif (isset($errorData['message'])) {
                        $errorMsg .= ': ' . $errorData['message'];
                    } elseif (isset($errorData['error'])) {
                        $errorMsg .= ': ' . $errorData['error'];
                    }
                }

                return [
                    'statusCode' => 'HTTP_ERROR_' . $statusCode,
                    'statusMessage' => $errorMsg,
                    'fullResponse' => substr($body, 0, 500)
                ];
            }

            $decoded = json_decode($body, true);

            if (json_last_error() !== JSON_ERROR_NONE) {
                log_message('error', 'JSON decode error: ' . json_last_error_msg());
                return [
                    'statusCode' => 'JSON_ERROR',
                    'statusMessage' => 'Invalid JSON response: ' . json_last_error_msg()
                ];
            }

            return $decoded ?? [];
        } catch (\Exception $e) {
            log_message('error', 'Request exception: ' . $e->getMessage());
            return [
                'statusCode' => 'EXCEPTION',
                'statusMessage' => 'Request failed: ' . $e->getMessage()
            ];
        }
    }

    private function mapStatus(string $status): string
    {
        $statusMap = [
            '00' => 'SUCCESS',
            '01' => 'PENDING',
            '02' => 'FAILED',
            'success' => 'SUCCESS',
            'pending' => 'PENDING',
            'failed' => 'FAILED',
            'cancelled' => 'FAILED',
            'expired' => 'EXPIRED'
        ];

        return $statusMap[strtolower($status)] ?? 'PENDING';
    }

    /**
     * Get admin fee for a specific payment method
     */
    public function getMethodFee($methodCode)
    {
        // Admin fees untuk Duitku berdasarkan rate resmi
        $fees = [
            // Virtual Accounts (fixed fee)
            'BC' => 5000,           // BCA VA - Rp. 5.000
            'M2' => 4000,           // Mandiri VA - Rp. 4.000
            'BN' => 4000,           // BNI (Maybank) VA - Rp. 4.000 (lainnya)
            'BRI' => 4000,          // BRI VA - Rp. 4.000
            'VA' => 4000,           // Maybank VA - Rp. 4.000
            'I1' => 4000,           // BNI VA - Rp. 4.000
            'B1' => 4000,           // CIMB Niaga - Rp. 4.000

            // E-Wallets (percentage - nilai sudah dalam %)
            'OV' => 1.67,           // OVO - 1,67%
            'DA' => 1.67,           // DANA - 1,67%
            'LF' => 3330,           // LinkAja - Rp. 3.330 fixed
            'SP' => 2,              // ShopeePay - 2%
            'SA' => 2,              // ShopeePay alternative - 2%

            // QRIS (percentage)
            'FT' => 0.7,            // QRIS - 0,7%

            // Retail (fixed fee)
            'A1' => 2500,           // Alfamart - Rp. 2.500
            'I1' => 1000,           // Indomaret - Rp. 1.000 (MDR + Rp. 1.000)

            // Artha Graha & Sahabat Sampoerna
            'AG' => 1500,           // Artha Graha - Rp. 1.500
            'S1' => 1500,           // Sampoerna - Rp. 1.500

            // Pegadaian & POS Indonesia
            'PG' => 2500,           // Pegadaian - Rp. 2.500
            'PO' => 2500,           // POS Indonesia - Rp. 2.500

            // Legacy lowercase codes (for backward compatibility)
            'bca_va' => 5000,
            'bni_va' => 4000,
            'bri_va' => 4000,
            'mandiri_va' => 4000,
            'permata_va' => 4000,
            'bjb_va' => 4000,
            'ovo' => 1.67,
            'dana' => 1.67,
            'linkaja' => 3330,
            'shopeepay' => 2,
            'qris' => 0.7,
            'cc' => 4000,
            'alfamart' => 2500,
            'indomaret' => 1000,
            'bank_transfer' => 4000,
        ];

        // Return fee untuk method yang diminta, default 4000 jika tidak ditemukan
        return $fees[$methodCode] ?? 4000;
    }
}
