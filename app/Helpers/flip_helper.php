<?php

/**
 * Flip Payment Gateway Helper Functions
 * 
 * Helper functions untuk memudahkan integrasi Flip payment gateway
 * di berbagai bagian aplikasi.
 */

if (!function_exists('create_flip_payment')) {
    /**
     * Buat payment link menggunakan Flip
     * 
     * @param array $data Data pembayaran
     * @return array Response dengan payment_url
     */
    function create_flip_payment(array $data): array
    {
        try {
            $flipService = \App\Libraries\Payment\PaymentGatewayFactory::create('flip');

            if (!$flipService) {
                return [
                    'success' => false,
                    'message' => 'Flip gateway tidak tersedia atau tidak aktif'
                ];
            }

            return $flipService->createTransaction($data);
        } catch (\Exception $e) {
            log_message('error', 'Flip helper error: ' . $e->getMessage());
            return [
                'success' => false,
                'message' => $e->getMessage()
            ];
        }
    }
}

if (!function_exists('get_flip_payment_status')) {
    /**
     * Cek status pembayaran Flip
     * 
     * @param string $transactionId Transaction ID / Bill Link ID
     * @return array Status pembayaran
     */
    function get_flip_payment_status(string $transactionId): array
    {
        try {
            $flipService = \App\Libraries\Payment\PaymentGatewayFactory::create('flip');

            if (!$flipService) {
                return [
                    'success' => false,
                    'message' => 'Flip gateway tidak tersedia'
                ];
            }

            return $flipService->getTransactionStatus($transactionId);
        } catch (\Exception $e) {
            log_message('error', 'Flip status check error: ' . $e->getMessage());
            return [
                'success' => false,
                'message' => $e->getMessage()
            ];
        }
    }
}

if (!function_exists('flip_format_currency')) {
    /**
     * Format mata uang IDR untuk Flip
     * 
     * @param int $amount Jumlah dalam rupiah
     * @return string Formatted currency
     */
    function flip_format_currency(int $amount): string
    {
        return 'Rp ' . number_format($amount, 0, ',', '.');
    }
}

if (!function_exists('is_flip_active')) {
    /**
     * Cek apakah Flip payment gateway aktif
     * 
     * @return bool
     */
    function is_flip_active(): bool
    {
        try {
            $paymentModel = new \App\Models\PaymentGatewayModel();
            $config = $paymentModel->getActiveGatewayByType('flip');

            return !empty($config) && $config['is_active'] == 1;
        } catch (\Exception $e) {
            log_message('error', 'Error checking Flip status: ' . $e->getMessage());
            return false;
        }
    }
}

if (!function_exists('get_flip_payment_methods')) {
    /**
     * Dapatkan daftar metode pembayaran Flip yang tersedia
     * 
     * @return array
     */
    function get_flip_payment_methods(): array
    {
        try {
            $flipService = \App\Libraries\Payment\PaymentGatewayFactory::create('flip');

            if (!$flipService) {
                return [];
            }

            $result = $flipService->getPaymentMethods();
            return $result['success'] ? $result['data'] : [];
        } catch (\Exception $e) {
            log_message('error', 'Error getting Flip payment methods: ' . $e->getMessage());
            return [];
        }
    }
}

if (!function_exists('flip_callback_url')) {
    /**
     * Generate callback URL untuk Flip
     * 
     * @return string Callback URL
     */
    function flip_callback_url(): string
    {
        return base_url('payment/callback/flip');
    }
}

if (!function_exists('calculate_flip_admin_fee')) {
    /**
     * Hitung biaya admin Flip berdasarkan metode pembayaran
     * 
     * @param string $methodCode Kode metode pembayaran
     * @return int Biaya admin
     */
    function calculate_flip_admin_fee(string $methodCode): int
    {
        try {
            $paymentModel = new \App\Models\PaymentGatewayModel();
            $config = $paymentModel->getActiveGatewayByType('flip');

            if (!$config || empty($config['admin_fees'])) {
                return 0;
            }

            $adminFees = json_decode($config['admin_fees'], true);
            return $adminFees[$methodCode] ?? 0;
        } catch (\Exception $e) {
            log_message('error', 'Error calculating Flip admin fee: ' . $e->getMessage());
            return 0;
        }
    }
}

if (!function_exists('flip_total_with_admin_fee')) {
    /**
     * Hitung total pembayaran dengan biaya admin
     * 
     * @param int $amount Jumlah tagihan
     * @param string $methodCode Kode metode pembayaran
     * @return int Total pembayaran
     */
    function flip_total_with_admin_fee(int $amount, string $methodCode): int
    {
        $adminFee = calculate_flip_admin_fee($methodCode);
        return $amount + $adminFee;
    }
}

if (!function_exists('flip_map_status')) {
    /**
     * Map status dari Flip ke internal status
     * 
     * @param string $flipStatus Status dari Flip
     * @return string Internal status
     */
    function flip_map_status(string $flipStatus): string
    {
        $statusMap = [
            'SUCCESSFUL' => 'paid',
            'PENDING' => 'pending',
            'FAILED' => 'failed',
            'CANCELLED' => 'cancelled'
        ];

        return $statusMap[strtoupper($flipStatus)] ?? 'unknown';
    }
}

if (!function_exists('generate_flip_payment_button')) {
    /**
     * Generate HTML button untuk pembayaran via Flip
     * 
     * @param string $invoiceNo Nomor invoice
     * @param int $amount Jumlah tagihan
     * @param array $customerData Data customer
     * @return string HTML button
     */
    function generate_flip_payment_button(string $invoiceNo, int $amount, array $customerData): string
    {
        if (!is_flip_active()) {
            return '';
        }

        $buttonHtml = sprintf(
            '<button type="button" class="btn btn-primary pay-with-flip" 
                data-invoice="%s" 
                data-amount="%d" 
                data-customer-name="%s" 
                data-customer-email="%s" 
                data-customer-phone="%s">
                <i class="fa fa-credit-card"></i> Bayar dengan Flip
            </button>',
            htmlspecialchars($invoiceNo),
            $amount,
            htmlspecialchars($customerData['name'] ?? ''),
            htmlspecialchars($customerData['email'] ?? ''),
            htmlspecialchars($customerData['phone'] ?? '')
        );

        return $buttonHtml;
    }
}

if (!function_exists('flip_payment_badge')) {
    /**
     * Generate badge HTML untuk status pembayaran Flip
     * 
     * @param string $status Status pembayaran
     * @return string HTML badge
     */
    function flip_payment_badge(string $status): string
    {
        $badges = [
            'paid' => '<span class="badge bg-success">Lunas</span>',
            'pending' => '<span class="badge bg-warning">Menunggu Pembayaran</span>',
            'failed' => '<span class="badge bg-danger">Gagal</span>',
            'cancelled' => '<span class="badge bg-secondary">Dibatalkan</span>',
        ];

        return $badges[$status] ?? '<span class="badge bg-info">' . ucfirst($status) . '</span>';
    }
}

if (!function_exists('test_flip_connection')) {
    /**
     * Test koneksi ke Flip API
     * 
     * @return array Test result
     */
    function test_flip_connection(): array
    {
        try {
            $flipService = \App\Libraries\Payment\PaymentGatewayFactory::create('flip');

            if (!$flipService) {
                return [
                    'success' => false,
                    'message' => 'Flip gateway tidak tersedia'
                ];
            }

            return $flipService->testConnection();
        } catch (\Exception $e) {
            log_message('error', 'Flip connection test error: ' . $e->getMessage());
            return [
                'success' => false,
                'message' => $e->getMessage()
            ];
        }
    }
}

if (!function_exists('flip_get_config')) {
    /**
     * Dapatkan konfigurasi Flip saat ini
     * 
     * @return array|null Konfigurasi Flip
     */
    function flip_get_config(): ?array
    {
        try {
            $paymentModel = new \App\Models\PaymentGatewayModel();
            return $paymentModel->getActiveGatewayByType('flip');
        } catch (\Exception $e) {
            log_message('error', 'Error getting Flip config: ' . $e->getMessage());
            return null;
        }
    }
}
