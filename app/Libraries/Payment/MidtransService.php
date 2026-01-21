<?php

namespace App\Libraries\Payment;

use App\Libraries\Payment\PaymentGatewayInterface;

class MidtransService implements PaymentGatewayInterface
{
    private $serverKey;
    private $clientKey;
    private $merchantId;
    private $environment;
    private $baseUrl;

    public function __construct(array $config)
    {
        $this->serverKey = $config['api_key'] ?? '';
        $this->clientKey = $config['api_secret'] ?? '';
        $this->merchantId = $config['merchant_code'] ?? '';
        $this->environment = $config['environment'] ?? 'sandbox';

        $this->baseUrl = $this->environment === 'production'
            ? 'https://api.midtrans.com'
            : 'https://api.sandbox.midtrans.com';
    }
    public function createTransaction(array $data): array
    {
        try {
            // Validate required data
            if (empty($data['order_id']) || empty($data['amount']) || $data['amount'] <= 0) {
                throw new \Exception('Order ID and valid amount are required');
            }            // Format phone number untuk Midtrans (harus dimulai dengan +62)
            $phone = $data['customer_phone'] ?? '';
            if (!empty($phone)) {
                // Remove any non-numeric characters except +
                $phone = preg_replace('/[^0-9+]/', '', $phone);

                // Jika sudah ada +62 di awal, biarkan
                if (substr($phone, 0, 3) === '+62') {
                    // sudah benar
                }
                // Jika dimulai dengan 62, tambahkan +
                elseif (substr($phone, 0, 2) === '62') {
                    $phone = '+' . $phone;
                }
                // Convert 08 to +628
                elseif (substr($phone, 0, 2) === '08') {
                    $phone = '+62' . substr($phone, 1);
                }
                // Jika dimulai dengan 8, tambahkan +62
                elseif (substr($phone, 0, 1) === '8') {
                    $phone = '+62' . $phone;
                }
                // Default jika format tidak dikenali
                else {
                    $phone = '+628123456789';
                }
            } else {
                $phone = '+628123456789'; // default phone
            }

            // Log phone number conversion untuk debugging
            log_message('debug', 'Phone conversion: ' . ($data['customer_phone'] ?? 'empty') . ' -> ' . $phone);

            // Validate email format
            $email = $data['customer_email'] ?? 'customer@example.com';
            if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
                $email = 'customer@example.com';
            }

            $payload = [
                'transaction_details' => [
                    'order_id' => $data['order_id'],
                    'gross_amount' => (int) $data['amount']
                ],
                'customer_details' => [
                    'first_name' => $data['customer_name'] ?? 'Customer',
                    'email' => $email,
                    'phone' => $phone
                ]
            ];

            // Handle item_details - support order_items dari request atau default
            if (isset($data['order_items']) && is_array($data['order_items']) && !empty($data['order_items'])) {
                // Use order_items from request (from CustomerDashboard/PublicBilling)
                $itemDetails = [];
                foreach ($data['order_items'] as $index => $item) {
                    // Batasi panjang id dan name (Midtrans max 50 karakter)
                    $itemId = isset($item['id']) ? $item['id'] : 'item-' . ($index + 1);
                    if (strlen($itemId) > 50) {
                        $itemId = substr($itemId, 0, 50);
                    }

                    $itemName = $item['name'] ?? 'Item ' . ($index + 1);
                    if (strlen($itemName) > 50) {
                        $itemName = substr($itemName, 0, 47) . '...';
                    }

                    $itemDetails[] = [
                        'id' => $itemId,
                        'price' => (int) ($item['price'] ?? 0),
                        'quantity' => (int) ($item['quantity'] ?? 1),
                        'name' => $itemName
                    ];
                }
                $payload['item_details'] = $itemDetails;
            } else {
                // Default item_details jika tidak ada order_items
                $itemName = $data['description'] ?? 'Internet Service Payment';
                if (strlen($itemName) > 50) {
                    $itemName = substr($itemName, 0, 47) . '...';
                }

                $payload['item_details'] = [
                    [
                        'id' => 'item-1',
                        'price' => (int) $data['amount'],
                        'quantity' => 1,
                        'name' => $itemName
                    ]
                ];
            }            // Set enabled_payments berdasarkan method yang dipilih user
            if (isset($data['method']) && !empty($data['method'])) {
                // Map method name ke format Midtrans jika perlu
                $method = $this->mapPaymentMethod($data['method']);
                $payload['enabled_payments'] = [$method];
                // Untuk beberapa metode, perlu setting khusus
                switch ($method) {
                    case 'credit_card':
                        $payload['credit_card'] = [
                            'secure' => true,
                            'save_card' => false
                        ];
                        break;
                    case 'gopay':
                        $payload['gopay'] = [
                            'enable_callback' => true,
                            'callback_url' => $data['callback_url'] ?? ''
                        ];
                        break;
                    case 'shopeepay':
                        $payload['shopeepay'] = [
                            'callback_url' => $data['callback_url'] ?? ''
                        ];
                        break;
                    case 'cstore':
                        // Untuk Indomaret/Alfamart
                        $payload['cstore'] = [
                            'store' => strtolower($data['method']) === 'indomaret' ? 'indomaret' : 'alfamart'
                        ];
                        break;
                }

                // Log method yang dipilih
                log_message('debug', 'Payment method selected: ' . $data['method'] . ' -> mapped to: ' . $method);
            } else {
                // Jika tidak ada method spesifik, tampilkan beberapa pilihan
                $payload['enabled_payments'] = [
                    'bca_va',
                    'bni_va',
                    'bri_va',
                    'echannel',
                    'gopay',
                    'qris'
                ];
            }
            // Get expiry time from config (default 24 hours)
            $expiryHours = isset($this->config['payment_expiry_hours']) ? (int)$this->config['payment_expiry_hours'] : 24;

            $payload['expiry'] = [
                'start_time' => date('Y-m-d H:i:s O'),
                'unit' => 'hours',
                'duration' => $expiryHours
            ];

            log_message('info', 'Midtrans payment expiry set to: ' . $expiryHours . ' hours');

            // Add notification/callback configuration
            if (isset($data['callback_url']) && !empty($data['callback_url'])) {
                $payload['callbacks'] = [
                    'finish' => $data['return_url'] ?? base_url(),
                ];

                // Add notification URL directly to payload (for automatic configuration)
                $payload['notification_url'] = $data['callback_url'];

                // Set notification URL for server-to-server callback
                log_message('info', 'Midtrans notification URL configured: ' . $data['callback_url']);
            } else {
                // Set default notification URL if not provided
                $defaultCallbackUrl = base_url('payment/callback/midtrans');
                $payload['notification_url'] = $defaultCallbackUrl;
                log_message('info', 'Midtrans default notification URL set: ' . $defaultCallbackUrl);
            }

            // Log payload untuk debugging
            log_message('debug', 'Midtrans payload: ' . json_encode($payload));

            $response = $this->makeRequest('/snap/v1/transactions', 'POST', $payload);
            return [
                'success' => isset($response['token']),
                'data' => $response,
                'message' => isset($response['token']) ? 'Transaction created successfully' : ($response['status_message'] ?? 'Failed to create transaction'),
                'payment_url' => $response['redirect_url'] ?? '',
                'transaction_id' => $data['order_id'], // Use order_id as transaction_id for snap
                'token' => $response['token'] ?? ''
            ];
        } catch (\Exception $e) {
            return [
                'success' => false,
                'message' => 'Midtrans Error: ' . $e->getMessage(),
                'data' => []
            ];
        }
    }

    /**
     * Create charge transaction using Core API (for direct VA number)
     */
    public function createCharge(array $data): array
    {
        try {
            // Validate required data
            if (empty($data['order_id']) || empty($data['amount']) || $data['amount'] <= 0) {
                throw new \Exception('Order ID and valid amount are required');
            }

            // Format phone number
            $phone = $data['customer_phone'] ?? '';
            if (!empty($phone)) {
                $phone = preg_replace('/[^0-9+]/', '', $phone);
                if (substr($phone, 0, 3) === '+62') {
                    // already correct
                } elseif (substr($phone, 0, 2) === '62') {
                    $phone = '+' . $phone;
                } elseif (substr($phone, 0, 2) === '08') {
                    $phone = '+62' . substr($phone, 1);
                } elseif (substr($phone, 0, 1) === '8') {
                    $phone = '+62' . $phone;
                } else {
                    $phone = '+628123456789';
                }
            } else {
                $phone = '+628123456789';
            }

            $email = $data['customer_email'] ?? 'customer@example.com';
            if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
                $email = 'customer@example.com';
            }

            $payload = [
                'payment_type' => $this->getCorePaymentType($data['payment_type']),
                'transaction_details' => [
                    'order_id' => $data['order_id'],
                    'gross_amount' => (int) $data['amount']
                ],
                'customer_details' => [
                    'first_name' => $data['customer_name'] ?? 'Customer',
                    'email' => $email,
                    'phone' => $phone
                ]
            ];

            // Add bank-specific details
            $paymentType = $data['payment_type'];
            if (in_array($paymentType, ['bca', 'bni', 'bri', 'permata'])) {
                $payload['bank_transfer'] = [
                    'bank' => $paymentType
                ];
            } elseif ($paymentType === 'mandiri') {
                $payload['echannel'] = [
                    'bill_info1' => 'Payment For:',
                    'bill_info2' => $data['item_details'][0]['name'] ?? 'Order'
                ];
            } elseif (in_array($paymentType, ['alfamart', 'indomaret'])) {
                $payload['cstore'] = [
                    'store' => $paymentType,
                    'message' => $data['item_details'][0]['name'] ?? 'Payment'
                ];
            } elseif ($paymentType === 'gopay') {
                $payload['gopay'] = [
                    'enable_callback' => true,
                    'callback_url' => base_url('payment/callback/midtrans')
                ];
            } elseif ($paymentType === 'qris') {
                $payload['qris'] = [
                    'acquirer' => 'gopay'
                ];
            } elseif ($paymentType === 'shopeepay') {
                $payload['shopeepay'] = [
                    'callback_url' => base_url('payment/callback/midtrans')
                ];
            }

            log_message('debug', 'Midtrans Core API payload: ' . json_encode($payload));

            $response = $this->makeRequest('/v2/charge', 'POST', $payload);

            log_message('debug', 'Midtrans Core API Response: ' . json_encode($response));

            // Extract VA number or payment code
            $paymentCode = '';
            $paymentUrl = '';

            if (isset($response['va_numbers'][0]['va_number'])) {
                // Bank VA
                $paymentCode = $response['va_numbers'][0]['va_number'];
            } elseif (isset($response['bill_key'])) {
                // Mandiri bill payment
                $paymentCode = $response['bill_key'] . ' / ' . $response['biller_code'];
            } elseif (isset($response['permata_va_number'])) {
                // Permata VA
                $paymentCode = $response['permata_va_number'];
            } elseif (isset($response['payment_code'])) {
                // Alfamart/Indomaret
                $paymentCode = $response['payment_code'];
            } elseif (isset($response['actions'])) {
                // For GoPay/QRIS/ShopeePay - get deeplink or QR
                foreach ($response['actions'] as $action) {
                    if ($action['name'] === 'generate-qr-code') {
                        $paymentUrl = $action['url'];
                    } elseif ($action['name'] === 'deeplink-redirect') {
                        $paymentUrl = $action['url'];
                    }
                }
                $paymentCode = $response['transaction_id'] ?? '';
            }

            return [
                'success' => isset($response['transaction_status']) && in_array($response['transaction_status'], ['pending', 'settlement']),
                'data' => $response,
                'message' => $response['status_message'] ?? 'Transaction created',
                'payment_code' => $paymentCode,
                'transaction_id' => $response['transaction_id'] ?? $data['order_id'],
                'payment_url' => $paymentUrl
            ];
        } catch (\Exception $e) {
            $errorMessage = $e->getMessage();

            // Check if error is about payment channel activation
            if (strpos($errorMessage, 'not activated') !== false) {
                $errorMessage = 'Payment channel belum diaktifkan di Midtrans Dashboard. Silakan aktifkan payment channel "' . ($data['payment_type'] ?? 'unknown') . '" terlebih dahulu di: https://dashboard.midtrans.com/ → Settings → Payment Channels';
            }

            log_message('error', 'Midtrans createCharge error: ' . $errorMessage);

            return [
                'success' => false,
                'message' => $errorMessage,
                'data' => []
            ];
        }
    }

    /**
     * Get Core API payment type from code
     */
    private function getCorePaymentType($code): string
    {
        $mapping = [
            'bca' => 'bank_transfer',
            'bni' => 'bank_transfer',
            'bri' => 'bank_transfer',
            'permata' => 'bank_transfer',
            'mandiri' => 'echannel',
            'gopay' => 'gopay',
            'qris' => 'qris',
            'shopeepay' => 'shopeepay',
            'alfamart' => 'cstore',
            'indomaret' => 'cstore'
        ];
        return $mapping[$code] ?? 'bank_transfer';
    }

    public function getTransactionStatus(string $transactionId): array
    {
        try {
            $response = $this->makeRequest("/v2/{$transactionId}/status", 'GET');

            return [
                'success' => isset($response['transaction_id']),
                'status' => $this->mapStatus($response['transaction_status'] ?? ''),
                'data' => $response,
                'message' => isset($response['transaction_id']) ? 'Status retrieved successfully' : 'Failed to get status'
            ];
        } catch (\Exception $e) {
            return [
                'success' => false,
                'message' => 'Midtrans Error: ' . $e->getMessage(),
                'data' => []
            ];
        }
    }

    public function getPaymentMethods(): array
    {
        try {
            // Check if Midtrans is properly configured
            if (empty($this->serverKey) || empty($this->clientKey)) {
                return [
                    'success' => false,
                    'message' => 'Midtrans tidak dikonfigurasi dengan benar',
                    'data' => []
                ];
            }

            // Try to fetch active payment methods from Midtrans API
            $activeMethods = $this->fetchActivePaymentMethods();

            if (!empty($activeMethods)) {
                return [
                    'success' => true,
                    'data' => $activeMethods,
                    'message' => 'Payment methods retrieved from Midtrans API'
                ];
            }

            // Fallback to basic methods if API call fails
            $basicMethods = [
                [
                    'code' => 'credit_card',
                    'name' => 'Credit Card',
                    'type' => 'card',
                    'active' => true
                ],
                [
                    'code' => 'bca_va',
                    'name' => 'BCA Virtual Account',
                    'type' => 'bank_transfer',
                    'active' => true
                ],
                [
                    'code' => 'bni_va',
                    'name' => 'BNI Virtual Account',
                    'type' => 'bank_transfer',
                    'active' => true
                ],
                [
                    'code' => 'bri_va',
                    'name' => 'BRI Virtual Account',
                    'type' => 'bank_transfer',
                    'active' => true
                ],
                [
                    'code' => 'echannel',
                    'name' => 'Mandiri Virtual Account',
                    'type' => 'bank_transfer',
                    'active' => true
                ],
                [
                    'code' => 'gopay',
                    'name' => 'GoPay',
                    'type' => 'ewallet',
                    'active' => true
                ],
                [
                    'code' => 'qris',
                    'name' => 'QRIS',
                    'type' => 'qr_code',
                    'active' => true
                ]
            ];

            return [
                'success' => true,
                'data' => $basicMethods,
                'message' => 'Payment methods retrieved (basic configuration)'
            ];
        } catch (\Exception $e) {
            return [
                'success' => false,
                'message' => 'Midtrans Error: ' . $e->getMessage(),
                'data' => []
            ];
        }
    }

    /**
     * Fetch active payment methods from Midtrans API
     */
    private function fetchActivePaymentMethods(): array
    {
        try {
            // Make API call to get merchant info which includes enabled payment methods
            $response = $this->makeRequest('/v1/payment_methods', 'GET');

            if (isset($response['enabled_payments']) && is_array($response['enabled_payments'])) {
                $activeMethods = [];
                $methodMap = $this->getMethodMapping();

                foreach ($response['enabled_payments'] as $method) {
                    if (isset($methodMap[$method])) {
                        $activeMethods[] = $methodMap[$method];
                    }
                }

                return $activeMethods;
            }

            return [];
        } catch (\Exception $e) {
            log_message('info', 'Could not fetch Midtrans payment methods: ' . $e->getMessage());
            return [];
        }
    }

    /**
     * Get method mapping for Midtrans payment types
     */
    private function getMethodMapping(): array
    {
        return [
            'credit_card' => [
                'code' => 'credit_card',
                'name' => 'Credit Card',
                'type' => 'card',
                'active' => true
            ],
            'bca_va' => [
                'code' => 'bca_va',
                'name' => 'BCA Virtual Account',
                'type' => 'bank_transfer',
                'active' => true
            ],
            'bni_va' => [
                'code' => 'bni_va',
                'name' => 'BNI Virtual Account',
                'type' => 'bank_transfer',
                'active' => true
            ],
            'bri_va' => [
                'code' => 'bri_va',
                'name' => 'BRI Virtual Account',
                'type' => 'bank_transfer',
                'active' => true
            ],
            'echannel' => [
                'code' => 'echannel',
                'name' => 'Mandiri Virtual Account',
                'type' => 'bank_transfer',
                'active' => true
            ],
            'gopay' => [
                'code' => 'gopay',
                'name' => 'GoPay',
                'type' => 'ewallet',
                'active' => true
            ],
            'ovo' => [
                'code' => 'ovo',
                'name' => 'OVO',
                'type' => 'ewallet',
                'active' => true
            ],
            'dana' => [
                'code' => 'dana',
                'name' => 'DANA',
                'type' => 'ewallet',
                'active' => true
            ],
            'shopeepay' => [
                'code' => 'shopeepay',
                'name' => 'ShopeePay',
                'type' => 'ewallet',
                'active' => true
            ],
            'qris' => [
                'code' => 'qris',
                'name' => 'QRIS',
                'type' => 'qr_code',
                'active' => true
            ]
        ];
    }

    public function handleCallback(array $data): array
    {
        return [
            'order_id' => $data['order_id'] ?? '',
            'transaction_id' => $data['transaction_id'] ?? '',
            'status' => $this->mapStatus($data['transaction_status'] ?? ''),
            'amount' => $data['gross_amount'] ?? 0,
            'paid_amount' => $data['gross_amount'] ?? 0,
            'payment_method' => $data['payment_type'] ?? '',
            'raw_data' => $data
        ];
    }

    public function verifyCallback(array $data, string $signature): bool
    {
        $orderId = $data['order_id'] ?? '';
        $statusCode = $data['status_code'] ?? '';
        $grossAmount = $data['gross_amount'] ?? '';

        $calculatedSignature = hash('sha512', $orderId . $statusCode . $grossAmount . $this->serverKey);

        return hash_equals($calculatedSignature, $signature);
    }
    public function testConnection(): array
    {
        try {
            // Check if required credentials are configured
            if (empty($this->serverKey)) {
                return [
                    'success' => false,
                    'message' => 'Midtrans credentials not configured. Please set Server Key from your Midtrans dashboard.',
                    'data' => [
                        'missing_config' => [
                            'server_key' => empty($this->serverKey)
                        ],
                        'setup_instructions' => [
                            'step1' => 'Visit https://dashboard.midtrans.com/',
                            'step2' => 'Go to Settings > Configuration',
                            'step3' => 'Copy your Server Key (starts with SB-Mid-server for sandbox)',
                            'step4' => 'Configure it in Payment Gateway settings'
                        ]
                    ]
                ];
            }

            // Test connection by creating a minimal test transaction to validate credentials
            // We'll create a transaction and then immediately cancel it
            try {
                $testOrderId = 'test-connection-' . time();
                $testPayload = [
                    'transaction_details' => [
                        'order_id' => $testOrderId,
                        'gross_amount' => 1000 // Minimal amount
                    ],
                    'customer_details' => [
                        'first_name' => 'Test',
                        'email' => 'test@example.com'
                    ],
                    'item_details' => [
                        [
                            'id' => 'test-item',
                            'price' => 1000,
                            'quantity' => 1,
                            'name' => 'Connection Test'
                        ]
                    ]
                ];

                // Try to create a transaction (this will validate auth and format)
                $response = $this->makeRequest('/v2/charge', 'POST', $testPayload);

                // If we get a response without authentication error, the credentials work
                if (isset($response['status_code'])) {
                    // Try to cancel the test transaction to clean up
                    try {
                        $this->makeRequest("/v2/{$testOrderId}/cancel", 'POST');
                    } catch (\Exception $e) {
                        // Ignore cancel errors - transaction might not exist or be cancellable
                    }

                    return [
                        'success' => true,
                        'message' => 'Midtrans connection successful - API credentials validated',
                        'data' => [
                            'auth_status' => 'Valid',
                            'environment' => $this->environment,
                            'server_key_prefix' => substr($this->serverKey, 0, 15) . '...',
                            'test_transaction_id' => $testOrderId
                        ]
                    ];
                } else {
                    return [
                        'success' => false,
                        'message' => 'Midtrans API returned unexpected response format',
                        'data' => [
                            'environment' => $this->environment,
                            'response' => $response
                        ]
                    ];
                }
            } catch (\Exception $apiError) {
                $errorMsg = $apiError->getMessage();

                // Check for specific error types
                if (strpos($errorMsg, 'Authentication failed') !== false || strpos($errorMsg, '401') !== false) {
                    return [
                        'success' => false,
                        'message' => 'Midtrans connection failed - Invalid Server Key',
                        'data' => [
                            'error' => 'Authentication failed (401)',
                            'current_key_prefix' => substr($this->serverKey, 0, 15) . '...',
                            'environment' => $this->environment,
                            'hint' => 'Check if your Server Key is correct for the selected environment (sandbox/production)'
                        ]
                    ];
                }

                if (strpos($errorMsg, 'Not found') !== false || strpos($errorMsg, '404') !== false) {
                    return [
                        'success' => false,
                        'message' => 'Midtrans API endpoint not found - Check API version or environment',
                        'data' => [
                            'error' => 'Endpoint not found (404)',
                            'base_url' => $this->baseUrl,
                            'environment' => $this->environment,
                            'hint' => 'Verify environment setting and API endpoint'
                        ]
                    ];
                }

                if (strpos($errorMsg, 'Bad request') !== false || strpos($errorMsg, '400') !== false) {
                    // 400 errors still mean the connection works and auth is valid
                    return [
                        'success' => true,
                        'message' => 'Midtrans connection successful - API responded (credentials valid)',
                        'data' => [
                            'status' => 'API reachable and authenticated',
                            'note' => 'Test request format caused 400 error, but this confirms credentials work',
                            'environment' => $this->environment
                        ]
                    ];
                }

                // For other errors, connection might still be valid but there's another issue
                return [
                    'success' => false,
                    'message' => 'Midtrans connection test failed: ' . $errorMsg,
                    'data' => [
                        'error' => $errorMsg,
                        'environment' => $this->environment,
                        'base_url' => $this->baseUrl,
                        'suggestion' => 'Check your Server Key and network connectivity'
                    ]
                ];
            }
        } catch (\Exception $e) {
            return [
                'success' => false,
                'message' => 'Connection test failed: ' . $e->getMessage(),
                'data' => [
                    'error_details' => $e->getMessage(),
                    'current_environment' => $this->environment,
                    'base_url' => $this->baseUrl,
                    'suggestion' => 'Verify your Midtrans Server Key and environment setting'
                ]
            ];
        }
    }
    private function makeRequest(string $endpoint, string $method = 'GET', array $data = []): array
    {
        $client = \Config\Services::curlrequest();

        $options = [
            'headers' => [
                'Content-Type' => 'application/json',
                'Authorization' => 'Basic ' . base64_encode($this->serverKey . ':'),
                'Accept' => 'application/json'
            ],
            'timeout' => 30,
            'http_errors' => false, // Don't throw exceptions on HTTP error status codes
            'verify' => false // Disable SSL verification for local testing
        ];

        if ($method === 'POST' && !empty($data)) {
            $options['json'] = $data;
        }

        try {
            $response = $client->request($method, $this->baseUrl . $endpoint, $options);
            $statusCode = $response->getStatusCode();
            $body = $response->getBody();
            $decoded = json_decode($body, true) ?? [];

            // Log the response for debugging
            log_message('debug', "Midtrans API Response: Status {$statusCode}, Body: " . substr($body, 0, 500));

            // Handle different status codes
            switch ($statusCode) {
                case 401:
                    throw new \Exception("Authentication failed - Invalid Server Key (HTTP 401)");

                case 400:
                    $errorMessage = $decoded['error_messages'][0] ?? 'Bad request format';
                    throw new \Exception("Bad request: {$errorMessage} (HTTP 400)");

                case 404:
                    $errorMessage = $decoded['error_messages'][0] ?? 'Endpoint not found';
                    throw new \Exception("Not found: {$errorMessage} (HTTP 404)");

                case 405:
                    throw new \Exception("Method not allowed (HTTP 405)");

                case 429:
                    throw new \Exception("Rate limit exceeded (HTTP 429)");

                case 500:
                case 502:
                case 503:
                    throw new \Exception("Midtrans server error (HTTP {$statusCode})");

                default:
                    if ($statusCode >= 400) {
                        $errorMessage = $decoded['error_messages'][0] ?? 'Unknown error';
                        throw new \Exception("API error: {$errorMessage} (HTTP {$statusCode})");
                    }
            }

            return $decoded;
        } catch (\Exception $e) {
            // Re-throw with more context
            throw new \Exception("Midtrans API request failed: " . $e->getMessage());
        }
    }

    private function mapStatus(string $status): string
    {
        $statusMap = [
            'capture' => 'SUCCESS',
            'settlement' => 'SUCCESS',
            'pending' => 'PENDING',
            'deny' => 'FAILED',
            'cancel' => 'FAILED',
            'expire' => 'EXPIRED',
            'failure' => 'FAILED',
            'refund' => 'REFUNDED',
            'partial_refund' => 'REFUNDED'
        ];
        return $statusMap[strtolower($status)] ?? 'PENDING';
    }

    private function mapPaymentMethod(string $method): string
    {
        // Map payment method names ke format yang diterima Midtrans
        $methodMap = [
            'bca_va' => 'bca_va',
            'bni_va' => 'bni_va',
            'bri_va' => 'bri_va',
            'echannel' => 'echannel', // Mandiri VA
            'permata_va' => 'permata_va',
            'other_va' => 'other_va',
            'gopay' => 'gopay',
            'shopeepay' => 'shopeepay',
            'ovo' => 'ovo',
            'dana' => 'dana',
            'linkaja' => 'linkaja',
            'qris' => 'qris',
            'credit_card' => 'credit_card',
            'bca_klikpay' => 'bca_klikpay',
            'bca_klikbca' => 'bca_klikbca',
            'bri_epay' => 'bri_epay',
            'cimb_clicks' => 'cimb_clicks',
            'danamon_online' => 'danamon_online',
            'indomaret' => 'cstore',
            'alfamart' => 'cstore'
        ];

        return $methodMap[strtolower($method)] ?? $method;
    }

    /**
     * Get admin fee for a specific payment method
     */
    public function getMethodFee($methodCode)
    {
        // Admin fees untuk Midtrans
        $fees = [
            'credit_card' => 4000,       // Credit card
            'bca_va' => 4000,            // VA Bank BCA
            'bni_va' => 4000,            // VA Bank BNI
            'bri_va' => 4000,            // VA Bank BRI
            'mandiri_va' => 4000,        // VA Bank Mandiri (echannel)
            'echannel' => 4000,          // Mandiri e-channel
            'permata_va' => 4000,        // VA Bank Permata
            'other_va' => 4000,          // VA banks lainnya
            'gopay' => 2,                // GoPay 2% fee
            'qris' => 0.7,               // QRIS 0.7% fee
            'shopeepay' => 4000,         // ShopeePay
            'akulaku' => 4000,           // Akulaku
            'kredivo' => 4000,           // Kredivo
            'bank_transfer' => 4000,     // Manual bank transfer
            'cstore' => 4000,            // Convenience store (Indomaret/Alfamart)
        ];

        // Return fee untuk method yang diminta, default 4000 jika tidak ditemukan
        return $fees[$methodCode] ?? 4000;
    }
}
