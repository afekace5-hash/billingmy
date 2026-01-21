<?php

namespace App\Controllers\Api;

use App\Controllers\BaseController;
use App\Models\CustomerModel;
use App\Models\InvoiceModel;
use App\Models\CustomerNotificationModel;

class PaymentController extends BaseController
{
    protected $customerModel;
    protected $invoiceModel;
    protected $notificationModel;

    public function __construct()
    {
        $this->customerModel = new CustomerModel();
        $this->invoiceModel = new InvoiceModel();
        $this->notificationModel = new CustomerNotificationModel();
    }

    /**
     * Get available payment methods
     * GET /api/payment/methods
     */
    public function methods()
    {
        $methods = [
            'bank_transfer' => [
                'name' => 'Transfer Bank',
                'options' => [
                    ['code' => 'bca_va', 'name' => 'BCA Virtual Account', 'fee' => 0],
                    ['code' => 'bni_va', 'name' => 'BNI Virtual Account', 'fee' => 0],
                    ['code' => 'bri_va', 'name' => 'BRI Virtual Account', 'fee' => 0],
                    ['code' => 'mandiri_va', 'name' => 'Mandiri Virtual Account', 'fee' => 0],
                    ['code' => 'permata_va', 'name' => 'Permata Virtual Account', 'fee' => 0]
                ]
            ],
            'e_wallet' => [
                'name' => 'E-Wallet',
                'options' => [
                    ['code' => 'gopay', 'name' => 'GoPay', 'fee' => 0],
                    ['code' => 'ovo', 'name' => 'OVO', 'fee' => 0],
                    ['code' => 'dana', 'name' => 'DANA', 'fee' => 0],
                    ['code' => 'shopeepay', 'name' => 'ShopeePay', 'fee' => 0],
                    ['code' => 'linkaja', 'name' => 'LinkAja', 'fee' => 0]
                ]
            ],
            'retail' => [
                'name' => 'Retail Store',
                'options' => [
                    ['code' => 'alfamart', 'name' => 'Alfamart', 'fee' => 2500],
                    ['code' => 'indomaret', 'name' => 'Indomaret', 'fee' => 2500]
                ]
            ],
            'credit_card' => [
                'name' => 'Kartu Kredit',
                'options' => [
                    ['code' => 'credit_card', 'name' => 'Visa/Mastercard/JCB', 'fee' => 0]
                ]
            ]
        ];

        return $this->response->setJSON([
            'success' => true,
            'message' => 'Metode pembayaran berhasil dimuat',
            'data' => $methods
        ]);
    }

    /**
     * Create payment for invoice
     * POST /api/payment/create
     * Body: { "invoice_id": 123, "payment_method": "bca_va", "gateway": "midtrans" }
     */
    public function create()
    {
        $customerId = $this->request->customerId;

        $rules = [
            'invoice_id' => 'required|integer',
            'payment_method' => 'required',
            'gateway' => 'required|in_list[midtrans,duitku]'
        ];

        if (!$this->validate($rules)) {
            return $this->response->setJSON([
                'success' => false,
                'message' => 'Data tidak lengkap',
                'errors' => $this->validator->getErrors()
            ])->setStatusCode(400);
        }

        $invoiceId = $this->request->getPost('invoice_id');
        $paymentMethod = $this->request->getPost('payment_method');
        $gateway = $this->request->getPost('gateway');

        // Get invoice
        $db = \Config\Database::connect();
        $invoice = $db->table('wstmp_invoices i')
            ->select('i.*, c.nomor_layanan, c.nama_pelanggan, c.email, c.telepphone')
            ->join('customers c', 'c.id_customers = i.id_customers', 'left')
            ->where('i.id_inv', $invoiceId)
            ->where('i.id_customers', $customerId)
            ->get()
            ->getRowArray();

        if (!$invoice) {
            return $this->response->setJSON([
                'success' => false,
                'message' => 'Tagihan tidak ditemukan'
            ])->setStatusCode(404);
        }

        // Check if already paid
        if ($invoice['status'] === 'Lunas') {
            return $this->response->setJSON([
                'success' => false,
                'message' => 'Tagihan sudah dibayar'
            ])->setStatusCode(400);
        }

        // Create payment based on gateway
        if ($gateway === 'midtrans') {
            $result = $this->createMidtransPayment($invoice, $paymentMethod);
        } else {
            $result = $this->createDuitkuPayment($invoice, $paymentMethod);
        }

        if ($result['success']) {
            // Create notification
            $this->notificationModel->createNotification(
                $customerId,
                'Menunggu Pembayaran',
                "Pembayaran tagihan {$invoice['nomor_layanan']} sedang diproses. Silakan selesaikan pembayaran.",
                'payment',
                ['invoice_id' => $invoiceId, 'transaction_code' => $result['data']['transaction_code']]
            );
        }

        return $this->response->setJSON($result);
    }

    /**
     * Check payment status
     * GET /api/payment/status/{transaction_code}
     */
    public function status($transactionCode)
    {
        $customerId = $this->request->customerId;

        $db = \Config\Database::connect();

        // Get customer
        $customer = $this->customerModel->find($customerId);

        // Get payment transaction
        $payment = $db->table('payment_transactions')
            ->where('transaction_code', $transactionCode)
            ->where('customer_number', $customer['nomor_layanan'])
            ->get()
            ->getRowArray();

        if (!$payment) {
            return $this->response->setJSON([
                'success' => false,
                'message' => 'Transaksi tidak ditemukan'
            ])->setStatusCode(404);
        }

        // Determine payment instructions based on method
        $instructions = $this->getPaymentInstructions($payment);

        return $this->response->setJSON([
            'success' => true,
            'message' => 'Status pembayaran berhasil dimuat',
            'data' => [
                'transaction' => $payment,
                'instructions' => $instructions
            ]
        ]);
    }

    /**
     * Cancel payment
     * POST /api/payment/cancel/{transaction_code}
     */
    public function cancel($transactionCode)
    {
        $customerId = $this->request->customerId;

        $db = \Config\Database::connect();

        // Get customer
        $customer = $this->customerModel->find($customerId);

        // Get payment transaction
        $payment = $db->table('payment_transactions')
            ->where('transaction_code', $transactionCode)
            ->where('customer_number', $customer['nomor_layanan'])
            ->where('status', 'pending')
            ->get()
            ->getRowArray();

        if (!$payment) {
            return $this->response->setJSON([
                'success' => false,
                'message' => 'Transaksi tidak ditemukan atau tidak dapat dibatalkan'
            ])->setStatusCode(404);
        }

        // Update status to cancelled
        $db->table('payment_transactions')
            ->where('id', $payment['id'])
            ->update(['status' => 'cancelled', 'updated_at' => date('Y-m-d H:i:s')]);

        return $this->response->setJSON([
            'success' => true,
            'message' => 'Pembayaran berhasil dibatalkan'
        ]);
    }

    /**
     * Create Midtrans payment
     */
    private function createMidtransPayment($invoice, $paymentMethod)
    {
        try {
            // Load Midtrans library
            $midtransServerKey = getenv('midtrans.serverKey') ?: '';
            $midtransClientKey = getenv('midtrans.clientKey') ?: '';
            $isProduction = getenv('midtrans.isProduction') === 'true';

            if (empty($midtransServerKey)) {
                return [
                    'success' => false,
                    'message' => 'Midtrans belum dikonfigurasi'
                ];
            }

            \Midtrans\Config::$serverKey = $midtransServerKey;
            \Midtrans\Config::$isProduction = $isProduction;
            \Midtrans\Config::$isSanitized = true;
            \Midtrans\Config::$is3ds = true;

            $transactionCode = 'PAY-' . time() . '-' . $invoice['id_inv'];
            $grossAmount = (int) $invoice['subtotal'];

            $params = [
                'transaction_details' => [
                    'order_id' => $transactionCode,
                    'gross_amount' => $grossAmount
                ],
                'customer_details' => [
                    'first_name' => $invoice['nama_pelanggan'],
                    'email' => $invoice['email'],
                    'phone' => $invoice['telepphone']
                ],
                'item_details' => [
                    [
                        'id' => 'INV-' . $invoice['id_inv'],
                        'price' => $grossAmount,
                        'quantity' => 1,
                        'name' => 'Tagihan Internet - ' . $invoice['nomor_layanan']
                    ]
                ]
            ];

            // Set payment type based on method
            if (in_array($paymentMethod, ['bca_va', 'bni_va', 'bri_va', 'mandiri_va', 'permata_va'])) {
                $params['payment_type'] = 'bank_transfer';
                $params['bank_transfer'] = ['bank' => str_replace('_va', '', $paymentMethod)];
            } elseif (in_array($paymentMethod, ['gopay', 'shopeepay'])) {
                $params['payment_type'] = $paymentMethod;
            }

            $snapToken = \Midtrans\Snap::getSnapToken($params);

            // Save to payment_transactions
            $db = \Config\Database::connect();
            $db->table('payment_transactions')->insert([
                'transaction_code' => $transactionCode,
                'customer_number' => $invoice['nomor_layanan'],
                'customer_name' => $invoice['nama_pelanggan'],
                'payment_method' => 'midtrans',
                'channel' => $paymentMethod,
                'amount' => $grossAmount,
                'total_amount' => $grossAmount,
                'status' => 'pending',
                'created_at' => date('Y-m-d H:i:s')
            ]);

            return [
                'success' => true,
                'message' => 'Pembayaran berhasil dibuat',
                'data' => [
                    'transaction_code' => $transactionCode,
                    'snap_token' => $snapToken,
                    'redirect_url' => 'https://app.sandbox.midtrans.com/snap/v2/vtweb/' . $snapToken,
                    'amount' => $grossAmount,
                    'payment_method' => $paymentMethod,
                    'gateway' => 'midtrans'
                ]
            ];
        } catch (\Exception $e) {
            log_message('error', 'Midtrans payment error: ' . $e->getMessage());
            return [
                'success' => false,
                'message' => 'Gagal membuat pembayaran: ' . $e->getMessage()
            ];
        }
    }

    /**
     * Create Duitku payment
     */
    private function createDuitkuPayment($invoice, $paymentMethod)
    {
        try {
            $merchantCode = getenv('duitku.merchantCode') ?: '';
            $apiKey = getenv('duitku.apiKey') ?: '';
            $isProduction = getenv('duitku.environment') === 'production';

            if (empty($merchantCode) || empty($apiKey)) {
                return [
                    'success' => false,
                    'message' => 'Duitku belum dikonfigurasi'
                ];
            }

            $transactionCode = 'PAY-' . time() . '-' . $invoice['id_inv'];
            $amount = (int) $invoice['subtotal'];

            $baseUrl = $isProduction ? 'https://passport.duitku.com' : 'https://sandbox.duitku.com';
            $callbackUrl = base_url('payment-callback-duitku');
            $returnUrl = base_url('payment-return');

            // Map payment method to Duitku code
            $paymentCode = $this->mapToDuitkuCode($paymentMethod);

            $params = [
                'merchantCode' => $merchantCode,
                'paymentAmount' => $amount,
                'paymentMethod' => $paymentCode,
                'merchantOrderId' => $transactionCode,
                'productDetails' => 'Tagihan Internet - ' . $invoice['nomor_layanan'],
                'customerVaName' => $invoice['nama_pelanggan'],
                'email' => $invoice['email'],
                'phoneNumber' => $invoice['telepphone'],
                'callbackUrl' => $callbackUrl,
                'returnUrl' => $returnUrl,
                'expiryPeriod' => 1440 // 24 hours
            ];

            $signature = md5($merchantCode . $transactionCode . $amount . $apiKey);
            $params['signature'] = $signature;

            // Call Duitku API
            $ch = curl_init();
            curl_setopt($ch, CURLOPT_URL, $baseUrl . '/webapi/api/merchant/v2/inquiry');
            curl_setopt($ch, CURLOPT_POST, true);
            curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($params));
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
            curl_setopt($ch, CURLOPT_HTTPHEADER, ['Content-Type: application/json']);

            $response = curl_exec($ch);
            curl_close($ch);

            $result = json_decode($response, true);

            if (isset($result['paymentUrl'])) {
                // Save to payment_transactions
                $db = \Config\Database::connect();
                $db->table('payment_transactions')->insert([
                    'transaction_code' => $transactionCode,
                    'customer_number' => $invoice['nomor_layanan'],
                    'customer_name' => $invoice['nama_pelanggan'],
                    'payment_method' => 'duitku',
                    'channel' => $paymentMethod,
                    'amount' => $amount,
                    'total_amount' => $amount,
                    'status' => 'pending',
                    'payment_code' => $result['reference'] ?? null,
                    'created_at' => date('Y-m-d H:i:s')
                ]);

                return [
                    'success' => true,
                    'message' => 'Pembayaran berhasil dibuat',
                    'data' => [
                        'transaction_code' => $transactionCode,
                        'payment_url' => $result['paymentUrl'],
                        'reference' => $result['reference'] ?? null,
                        'va_number' => $result['vaNumber'] ?? null,
                        'amount' => $amount,
                        'payment_method' => $paymentMethod,
                        'gateway' => 'duitku'
                    ]
                ];
            }

            return [
                'success' => false,
                'message' => $result['Message'] ?? 'Gagal membuat pembayaran'
            ];
        } catch (\Exception $e) {
            log_message('error', 'Duitku payment error: ' . $e->getMessage());
            return [
                'success' => false,
                'message' => 'Gagal membuat pembayaran: ' . $e->getMessage()
            ];
        }
    }

    /**
     * Map payment method to Duitku code
     */
    private function mapToDuitkuCode($method)
    {
        $map = [
            'bca_va' => 'BCA',
            'bni_va' => 'BNI',
            'bri_va' => 'BRI',
            'mandiri_va' => 'MANDIRI',
            'permata_va' => 'PERMATA',
            'gopay' => 'GOPAY',
            'ovo' => 'OVO',
            'dana' => 'DANA',
            'shopeepay' => 'SHOPEE',
            'linkaja' => 'LINKAJA',
            'alfamart' => 'ALFAMART',
            'indomaret' => 'INDOMARET'
        ];

        return $map[$method] ?? 'BCA';
    }

    /**
     * Get payment instructions
     */
    private function getPaymentInstructions($payment)
    {
        $instructions = [
            'title' => 'Cara Pembayaran',
            'steps' => []
        ];

        if (strpos($payment['channel'], '_va') !== false) {
            $bank = strtoupper(str_replace('_va', '', $payment['channel']));
            $instructions['steps'] = [
                "Buka aplikasi mobile banking atau ATM {$bank}",
                "Pilih menu Transfer atau Bayar",
                "Pilih Virtual Account",
                "Masukkan nomor VA: {$payment['payment_code']}",
                "Masukkan jumlah: Rp " . number_format($payment['amount'], 0, ',', '.'),
                "Konfirmasi dan selesaikan pembayaran",
                "Simpan bukti pembayaran"
            ];
        } elseif (in_array($payment['channel'], ['gopay', 'ovo', 'dana', 'shopeepay'])) {
            $wallet = strtoupper($payment['channel']);
            $instructions['steps'] = [
                "Buka aplikasi {$wallet}",
                "Scan QR Code atau gunakan link pembayaran",
                "Konfirmasi jumlah pembayaran",
                "Selesaikan pembayaran",
                "Tunggu notifikasi pembayaran berhasil"
            ];
        }

        return $instructions;
    }
}
