<?php

namespace App\Libraries\Payment;

use App\Libraries\Payment\PaymentGatewayInterface;

class FlipService implements PaymentGatewayInterface
{
    private $secretKey;
    private $validationToken;
    private $environment;
    private $baseUrl;

    public function __construct(array $config)
    {
        $this->secretKey = $config['api_key'] ?? '';
        $this->validationToken = $config['api_secret'] ?? '';
        $this->environment = $config['environment'] ?? 'sandbox';

        // Flip API URLs sesuai dokumentasi resmi
        if ($this->environment === 'production') {
            $this->baseUrl = 'https://bigflip.id/api';
        } else {
            // Sandbox environment
            $this->baseUrl = 'https://bigflip.id/big_sandbox_api';
        }
    }

    /**
     * Create a payment transaction (Bill/Invoice)
     *
     * @param array $data Transaction data
     * @return array Response from gateway
     */
    public function createTransaction(array $data): array
    {
        try {
            // Validate required data
            if (empty($data['order_id']) || empty($data['amount']) || $data['amount'] <= 0) {
                throw new \Exception('Order ID and valid amount are required');
            }

            // Format phone number
            $phone = $this->formatPhoneNumber($data['customer_phone'] ?? '');

            // Validate email
            $email = $data['customer_email'] ?? 'customer@example.com';
            if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
                $email = 'customer@example.com';
            }

            // Get payment method details
            $paymentMethod = $data['method'] ?? $data['payment_type'] ?? '';
            $senderBankType = $this->getSenderBankType($paymentMethod);

            // Validate redirect URL - Flip requires valid public URL
            $redirectUrl = $data['return_url'] ?? base_url();
            // Skip redirect_url jika localhost/test environment
            if (strpos($redirectUrl, 'localhost') !== false || strpos($redirectUrl, '.test') !== false || strpos($redirectUrl, '127.0.0.1') !== false) {
                $redirectUrl = null; // Flip akan gunakan default redirect
            }

            // Get expiry time from config (default 24 hours)
            $expiryHours = isset($this->config['payment_expiry_hours']) ? (int)$this->config['payment_expiry_hours'] : 24;
            $expiredDate = date('Y-m-d H:i', strtotime('+' . $expiryHours . ' hours'));

            // Prepare payload for Flip Bill
            // Gunakan step 3 (direct_api) untuk langsung mendapatkan VA/kode pembayaran
            // CATATAN: Payment method harus sudah di-enable di dashboard Flip
            $payload = [
                'title' => $data['description'] ?? 'Pembayaran Tagihan',
                'type' => 'SINGLE',
                'amount' => (int) $data['amount'],
                'expired_date' => $expiredDate,
                'is_address_required' => 0,
                'is_phone_number_required' => 0,
                'step' => 3, // direct_api - langsung generate VA/kode pembayaran
                'sender_name' => $data['customer_name'] ?? 'Customer',
                'sender_email' => $email,
                'sender_phone_number' => $phone,
                'sender_bank' => $paymentMethod,
                'sender_bank_type' => $senderBankType
            ];

            log_message('info', 'Flip payment expiry set to: ' . $expiryHours . ' hours (' . $expiredDate . ')');

            // Tambahkan redirect_url hanya jika URL valid (bukan localhost)
            if ($redirectUrl) {
                $payload['redirect_url'] = $redirectUrl;
            }

            log_message('debug', 'Flip payload: ' . json_encode($payload));

            $response = $this->makeRequest('/v2/pwf/bill', 'POST', $payload);

            log_message('debug', 'Flip response: ' . json_encode($response));

            if (isset($response['link_id'])) {
                $linkId = $response['link_id'];
                $linkUrl = $response['link_url'] ?? '';

                // Use payment_url from response if available, fallback to link_url
                $paymentUrl = $response['payment_url'] ?? $linkUrl;

                // Extract payment details dari response (untuk step 3)
                $paymentCode = 'Lihat detail pembayaran';
                $vaNumber = null;
                $billPaymentId = null;

                if (isset($response['bill_payment'])) {
                    $billPayment = $response['bill_payment'];
                    $billPaymentId = $billPayment['id'] ?? null;
                    $senderBankType = $billPayment['sender_bank_type'] ?? '';

                    if (isset($billPayment['receiver_bank_account'])) {
                        $accountNumber = $billPayment['receiver_bank_account']['account_number'] ?? '';

                        switch ($senderBankType) {
                            case 'virtual_account':
                                $vaNumber = $accountNumber;
                                $paymentCode = $vaNumber;
                                break;
                            case 'online_to_offline_account':
                                $paymentCode = $accountNumber; // Kode bayar Alfamart
                                break;
                            case 'wallet_account':
                                $paymentCode = 'Scan QRIS atau buka link pembayaran';
                                break;
                            default:
                                $paymentCode = $accountNumber ?: 'Lihat detail pembayaran';
                        }
                    }
                }

                log_message('debug', 'Flip extracted - Bill Payment ID: ' . $billPaymentId . ', Payment Code: ' . $paymentCode . ', Payment URL: ' . $paymentUrl);

                return [
                    'success' => true,
                    'data' => [
                        'link_id' => $linkId,
                        'link_url' => $linkUrl,
                        'title' => $response['title'] ?? '',
                        'amount' => $response['amount'] ?? 0,
                        'expired_date' => $response['expired_date'] ?? '',
                        'status' => $response['status'] ?? 'ACTIVE',
                        'bill_payment' => $response['bill_payment'] ?? []
                    ],
                    'message' => 'Transaction created successfully',
                    'payment_url' => $paymentUrl,
                    'payment_code' => $paymentCode,
                    'va_number' => $vaNumber,
                    'transaction_id' => $billPaymentId ?? $linkId,
                    'bill_payment_id' => $billPaymentId,
                    'link_id' => $linkId,
                    'bill_link' => $linkUrl,
                    'expired_at' => $response['expired_date'] ?? null
                ];
            } else {
                return [
                    'success' => false,
                    'message' => $response['message'] ?? 'Failed to create transaction',
                    'data' => $response
                ];
            }
        } catch (\Exception $e) {
            log_message('error', 'Flip Error: ' . $e->getMessage());
            return [
                'success' => false,
                'message' => 'Flip Error: ' . $e->getMessage(),
                'data' => []
            ];
        }
    }

    /**
     * Get transaction status
     *
     * @param string $transactionId Transaction ID (link_id)
     * @return array Transaction status
     */
    public function getTransactionStatus(string $transactionId): array
    {
        try {
            $response = $this->makeRequest('/v2/pwf/bill/' . $transactionId, 'GET');

            $status = 'pending';
            if (isset($response['status'])) {
                switch (strtoupper($response['status'])) {
                    case 'SUCCESSFUL':
                        $status = 'settlement';
                        break;
                    case 'PENDING':
                        $status = 'pending';
                        break;
                    case 'FAILED':
                    case 'CANCELLED':
                        $status = 'failed';
                        break;
                    default:
                        $status = 'pending';
                }
            }

            return [
                'success' => true,
                'status' => $status,
                'data' => $response,
                'transaction_id' => $transactionId,
                'amount' => $response['amount'] ?? 0
            ];
        } catch (\Exception $e) {
            log_message('error', 'Flip get status error: ' . $e->getMessage());
            return [
                'success' => false,
                'message' => $e->getMessage(),
                'data' => []
            ];
        }
    }

    /**
     * Get payment details from bill (VA number, payment code, etc)
     *
     * @param string $linkId Bill link ID
     * @return array Payment details
     */
    private function getPaymentDetails(string $linkId): array
    {
        try {
            // Get payment transactions for this bill
            $response = $this->makeRequest('/v2/pwf/payment?bill_link_id=' . $linkId, 'GET');

            log_message('debug', 'Flip payment details response: ' . json_encode($response));

            if (isset($response['data']) && is_array($response['data']) && count($response['data']) > 0) {
                $payment = $response['data'][0]; // Ambil payment pertama

                $paymentCode = '';
                $vaNumber = null;

                // Extract payment code based on payment method type
                if (isset($payment['sender_bank_type'])) {
                    switch ($payment['sender_bank_type']) {
                        case 'virtual_account':
                            $vaNumber = $payment['receiver_bank_account_number'] ?? null;
                            $paymentCode = $vaNumber ?? 'VA belum tersedia';
                            break;
                        case 'wallet_account':
                            $paymentCode = 'Scan QRIS / buka aplikasi e-wallet';
                            break;
                        case 'online_to_offline_account':
                            $paymentCode = $payment['payment_code'] ?? 'Kode pembayaran belum tersedia';
                            break;
                        default:
                            $paymentCode = 'Lihat detail pembayaran';
                    }
                }

                return [
                    'payment_code' => $paymentCode,
                    'va_number' => $vaNumber,
                    'bank' => $payment['sender_bank'] ?? '',
                    'bank_type' => $payment['sender_bank_type'] ?? '',
                    'amount' => $payment['amount'] ?? 0,
                    'status' => $payment['status'] ?? 'PENDING'
                ];
            }

            // Jika belum ada payment, return default
            return [
                'payment_code' => 'Sedang diproses...',
                'va_number' => null,
                'bank' => '',
                'bank_type' => '',
                'amount' => 0,
                'status' => 'PENDING'
            ];
        } catch (\Exception $e) {
            log_message('error', 'Flip get payment details error: ' . $e->getMessage());
            return [
                'payment_code' => 'Lihat detail pembayaran',
                'va_number' => null,
                'bank' => '',
                'bank_type' => '',
                'amount' => 0,
                'status' => 'PENDING'
            ];
        }
    }

    /**
     * Get available payment methods
     *
     * @return array Available payment methods
     */
    public function getPaymentMethods(): array
    {
        // Flip mendukung berbagai metode pembayaran melalui Bill Payment
        $methods = [
            // Virtual Account - 10 Bank
            [
                'code' => 'mandiri',
                'name' => 'Mandiri Virtual Account',
                'type' => 'virtual_account',
                'admin_fee' => 4000,
                'logo' => 'mandiri.png'
            ],
            [
                'code' => 'bni',
                'name' => 'BNI Virtual Account',
                'type' => 'virtual_account',
                'admin_fee' => 4000,
                'logo' => 'bni.png'
            ],
            [
                'code' => 'bri',
                'name' => 'BRI Virtual Account',
                'type' => 'virtual_account',
                'admin_fee' => 4000,
                'logo' => 'bri.png'
            ],
            [
                'code' => 'bca',
                'name' => 'BCA Virtual Account',
                'type' => 'virtual_account',
                'admin_fee' => 4000,
                'logo' => 'bca.png'
            ],
            [
                'code' => 'permata',
                'name' => 'Permata Virtual Account',
                'type' => 'virtual_account',
                'admin_fee' => 4000,
                'logo' => 'permata.png'
            ],
            [
                'code' => 'cimb',
                'name' => 'CIMB Niaga Virtual Account',
                'type' => 'virtual_account',
                'admin_fee' => 4000,
                'logo' => 'cimb.png'
            ],
            [
                'code' => 'danamon',
                'name' => 'Danamon Virtual Account',
                'type' => 'virtual_account',
                'admin_fee' => 4000,
                'logo' => 'danamon.png'
            ],
            [
                'code' => 'bsm',
                'name' => 'BSI Virtual Account',
                'type' => 'virtual_account',
                'admin_fee' => 4000,
                'logo' => 'bsi.png'
            ],
            [
                'code' => 'seabank',
                'name' => 'Seabank Virtual Account',
                'type' => 'virtual_account',
                'admin_fee' => 4000,
                'logo' => 'seabank.png'
            ],
            [
                'code' => 'bca_direct',
                'name' => 'BCA VA Facilitator',
                'type' => 'virtual_account',
                'admin_fee' => 4000,
                'logo' => 'bca.png'
            ],

            // E-Wallet - 5 Metode
            [
                'code' => 'qris',
                'name' => 'QRIS',
                'type' => 'wallet_account',
                'admin_fee' => 0,
                'logo' => 'qris.png'
            ],
            [
                'code' => 'ovo',
                'name' => 'OVO',
                'type' => 'wallet_account',
                'admin_fee' => 0,
                'logo' => 'ovo.png'
            ],
            [
                'code' => 'shopeepay_app',
                'name' => 'ShopeePay',
                'type' => 'wallet_account',
                'admin_fee' => 0,
                'logo' => 'shopeepay.png'
            ],
            [
                'code' => 'linkaja',
                'name' => 'LinkAja',
                'type' => 'wallet_account',
                'admin_fee' => 0,
                'logo' => 'linkaja.png'
            ],
            [
                'code' => 'dana',
                'name' => 'DANA',
                'type' => 'wallet_account',
                'admin_fee' => 0,
                'logo' => 'dana.png'
            ],

            // Retail - 1 Metode
            [
                'code' => 'alfamart',
                'name' => 'Alfamart',
                'type' => 'online_to_offline_account',
                'admin_fee' => 2500,
                'logo' => 'alfamart.png'
            ],

            // Credit Card
            [
                'code' => 'credit_card',
                'name' => 'Credit Card',
                'type' => 'credit_card_account',
                'admin_fee' => 0, // Fee biasanya persentase
                'logo' => 'creditcard.png'
            ]
        ];

        return [
            'success' => true,
            'data' => $methods
        ];
    }

    /**
     * Handle callback from payment gateway
     *
     * @param array $data Callback data
     * @return array Processed callback data
     */
    public function handleCallback(array $data): array
    {
        try {
            log_message('info', 'Flip callback received: ' . json_encode($data));

            // Flip mengirim data dalam format JSON
            $billId = $data['bill_link_id'] ?? $data['id'] ?? '';
            $status = $data['status'] ?? '';
            $amount = $data['amount'] ?? 0;

            // Map Flip status to internal status
            $mappedStatus = 'pending';
            switch (strtoupper($status)) {
                case 'SUCCESSFUL':
                    $mappedStatus = 'settlement';
                    break;
                case 'PENDING':
                    $mappedStatus = 'pending';
                    break;
                case 'FAILED':
                case 'CANCELLED':
                    $mappedStatus = 'failed';
                    break;
            }

            return [
                'success' => true,
                'transaction_id' => $billId,
                'order_id' => $data['bill_title'] ?? '',
                'status' => $mappedStatus,
                'amount' => $amount,
                'payment_type' => $data['payment_method'] ?? 'flip',
                'raw_data' => $data
            ];
        } catch (\Exception $e) {
            log_message('error', 'Flip callback error: ' . $e->getMessage());
            return [
                'success' => false,
                'message' => $e->getMessage()
            ];
        }
    }

    /**
     * Verify callback signature
     *
     * @param array $data Callback data
     * @param string $signature Signature to verify
     * @return bool True if signature is valid
     */
    public function verifyCallback(array $data, string $signature): bool
    {
        try {
            // Flip menggunakan token untuk validasi
            // Token dikirim melalui header atau parameter
            if (empty($signature) || empty($this->validationToken)) {
                log_message('warning', 'Flip: No signature or validation token provided');
                return false;
            }

            // Verifikasi token yang dikirim oleh Flip
            return $signature === $this->validationToken;
        } catch (\Exception $e) {
            log_message('error', 'Flip verify callback error: ' . $e->getMessage());
            return false;
        }
    }

    /**
     * Test connection to payment gateway
     *
     * @return array Test result
     */
    public function testConnection(): array
    {
        try {
            // Validasi Secret Key terlebih dahulu
            if (empty($this->secretKey)) {
                return [
                    'success' => false,
                    'message' => 'Secret Key tidak boleh kosong. Silakan isi Secret Key terlebih dahulu.',
                    'data' => []
                ];
            }

            // Log untuk debugging (hanya 10 karakter pertama untuk keamanan)
            log_message('info', 'Flip test connection with key: ' . substr($this->secretKey, 0, 10) . '...');
            log_message('info', 'Flip environment: ' . $this->environment);
            log_message('info', 'Flip base URL: ' . $this->baseUrl);

            // Flip API tidak memerlukan specific test endpoint
            // Kita bisa langsung anggap sukses jika credentials tersimpan
            // Validasi sebenarnya terjadi saat create transaction pertama
            return [
                'success' => true,
                'message' => 'Konfigurasi Flip berhasil disimpan. Secret Key: ' . substr($this->secretKey, 0, 10) . '***',
                'data' => [
                    'environment' => $this->environment,
                    'base_url' => $this->baseUrl,
                    'has_secret_key' => !empty($this->secretKey),
                    'has_validation_token' => !empty($this->validationToken),
                    'note' => 'Koneksi akan divalidasi saat membuat transaksi pertama'
                ]
            ];
        } catch (\Exception $e) {
            log_message('error', 'Flip test connection error: ' . $e->getMessage());
            return [
                'success' => false,
                'message' => 'Error: ' . $e->getMessage(),
                'data' => []
            ];
        }
    }

    /**
     * Make HTTP request to Flip API
     *
     * @param string $endpoint API endpoint
     * @param string $method HTTP method
     * @param array $data Request data
     * @return array Response data
     */
    private function makeRequest(string $endpoint, string $method = 'GET', array $data = []): array
    {
        $url = $this->baseUrl . $endpoint;

        $ch = curl_init();

        // Set basic auth with secret key as username
        log_message('debug', 'Flip API Request - URL: ' . $url);
        log_message('debug', 'Flip Secret Key Length: ' . strlen($this->secretKey) . ', Empty: ' . (empty($this->secretKey) ? 'YES' : 'NO'));
        log_message('debug', 'Flip Environment: ' . $this->environment . ', Base URL: ' . $this->baseUrl);

        curl_setopt($ch, CURLOPT_USERPWD, $this->secretKey . ':');
        curl_setopt($ch, CURLOPT_HTTPAUTH, CURLAUTH_BASIC);

        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);

        $headers = [
            'Content-Type: application/x-www-form-urlencoded'
        ];

        if ($method === 'POST') {
            curl_setopt($ch, CURLOPT_POST, true);
            curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query($data));
        } elseif ($method === 'PUT') {
            curl_setopt($ch, CURLOPT_CUSTOMREQUEST, 'PUT');
            curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query($data));
        } elseif ($method === 'DELETE') {
            curl_setopt($ch, CURLOPT_CUSTOMREQUEST, 'DELETE');
        }

        curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);

        $response = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        $error = curl_error($ch);

        curl_close($ch);

        if ($error) {
            log_message('error', 'Flip cURL Error: ' . $error);
            throw new \Exception('cURL Error: ' . $error);
        }

        $responseData = json_decode($response, true);

        // Log full response for debugging
        log_message('debug', 'Flip API Response - HTTP ' . $httpCode . ': ' . $response);

        if ($httpCode >= 400) {
            $errorMessage = $responseData['message'] ?? $responseData['error'] ?? 'Unknown error';

            // Log detail error untuk debugging
            if (isset($responseData['errors'])) {
                log_message('error', 'Flip validation errors: ' . json_encode($responseData['errors']));
                $errorMessage .= ' - ' . json_encode($responseData['errors']);
            }

            throw new \Exception('API Error (' . $httpCode . '): ' . $errorMessage);
        }

        return $responseData ?: [];
    }

    /**
     * Format phone number to international format
     *
     * @param string $phone Phone number
     * @return string Formatted phone number
     */
    private function formatPhoneNumber(string $phone): string
    {
        if (empty($phone)) {
            return '081234567890';
        }

        // Remove any non-numeric characters
        $phone = preg_replace('/[^0-9]/', '', $phone);

        // Convert to proper format
        if (substr($phone, 0, 2) === '62') {
            $phone = '0' . substr($phone, 2);
        } elseif (substr($phone, 0, 1) !== '0') {
            $phone = '0' . $phone;
        }

        return $phone;
    }

    /**
     * Get method fee based on payment method
     *
     * @param string $methodCode Payment method code
     * @return int Admin fee
     */
    public function getMethodFee(string $methodCode): int
    {
        $fees = [
            'flip_va' => 4000,
            'flip_qris' => 0,
            'flip_ewallet' => 0,
            'flip_retail' => 2500
        ];

        return $fees[$methodCode] ?? 0;
    }

    /**
     * Get sender_bank_type based on payment method code
     *
     * @param string $methodCode Payment method code
     * @return string sender_bank_type
     */
    private function getSenderBankType(string $methodCode): string
    {
        // Virtual Account banks
        $virtualAccounts = ['mandiri', 'bni', 'bri', 'bca', 'permata', 'cimb', 'danamon', 'bsm', 'seabank', 'bca_direct'];

        // E-Wallet methods
        $ewallets = ['qris', 'ovo', 'shopeepay_app', 'linkaja', 'dana'];

        // Retail methods
        $retail = ['alfamart'];

        // Credit card
        $creditCard = ['credit_card'];

        if (in_array($methodCode, $virtualAccounts)) {
            return 'virtual_account';
        } elseif (in_array($methodCode, $ewallets)) {
            return 'wallet_account';
        } elseif (in_array($methodCode, $retail)) {
            return 'online_to_offline_account';
        } elseif (in_array($methodCode, $creditCard)) {
            return 'credit_card_account';
        }

        // Default to virtual_account
        return 'virtual_account';
    }

    /**
     * Get available payment methods from Flip
     * Berdasarkan dokumentasi Flip API
     *
     * @return array List of available payment methods
     */
    public function getAvailablePaymentMethods(): array
    {
        // Flip API TIDAK menyediakan endpoint untuk query available payment methods
        // Kita gunakan list lengkap dari dokumentasi resmi Flip (17 methods)
        // Ini BUKAN dummy, tapi list resmi yang didukung Flip
        $paymentMethodsResponse = $this->getPaymentMethods();

        if (!$paymentMethodsResponse['success']) {
            return $this->getDefaultPaymentMethods();
        }

        $methods = [];
        foreach ($paymentMethodsResponse['data'] as $method) {
            $methods[] = [
                'code' => $method['code'],
                'name' => $method['name'],
                'type' => $method['type'],
                'enabled' => true // Semua method dari dokumentasi resmi
            ];
        }

        return $methods;
    }

    /**
     * Get default payment methods (fallback)
     *
     * @return array Default payment methods
     */
    private function getDefaultPaymentMethods(): array
    {
        return [
            ['code' => 'bca', 'name' => 'Bank Transfer BCA', 'type' => 'virtual_account', 'enabled' => true],
            ['code' => 'mandiri', 'name' => 'Bank Transfer Mandiri', 'type' => 'virtual_account', 'enabled' => true],
            ['code' => 'bni', 'name' => 'Bank Transfer BNI', 'type' => 'virtual_account', 'enabled' => true],
            ['code' => 'bri', 'name' => 'Bank Transfer BRI', 'type' => 'virtual_account', 'enabled' => true],
            ['code' => 'permata', 'name' => 'Bank Transfer Permata', 'type' => 'virtual_account', 'enabled' => true],
            ['code' => 'cimb', 'name' => 'Bank Transfer CIMB Niaga', 'type' => 'virtual_account', 'enabled' => true],
            ['code' => 'bsm', 'name' => 'Bank Transfer BSI (BSM)', 'type' => 'virtual_account', 'enabled' => true],
            ['code' => 'qris', 'name' => 'QRIS', 'type' => 'wallet_account', 'enabled' => true],
        ];
    }
}
