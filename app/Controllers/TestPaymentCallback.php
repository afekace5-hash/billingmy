<?php

namespace App\Controllers;

use App\Controllers\BaseController;
use App\Models\InvoiceModel;
use App\Models\CustomerModel;

class TestPaymentCallback extends BaseController
{
    public function index()
    {
        return view('test/payment_callback_test');
    }

    /**
     * Create a test invoice for testing payment callback
     */
    public function createTestInvoice()
    {
        try {
            $customerModel = new CustomerModel();
            $invoiceModel = new InvoiceModel();

            // Get first customer for testing
            $customer = $customerModel->first();

            if (!$customer) {
                return $this->response->setJSON([
                    'success' => false,
                    'message' => 'No customer found. Please create a customer first.'
                ]);
            }

            // Create test invoice
            $testOrderId = 'TEST-INV-' . time() . '-' . rand(100000, 999999);
            $invoiceData = [
                'customer_id' => $customer['id_customers'],
                'periode' => date('Y-m'),
                'bill' => 150000,
                'status' => 'unpaid',
                'transaction_id' => $testOrderId,
                'payment_gateway' => 'midtrans',
                'payment_method' => 'bank_transfer',
                'notes' => 'Test invoice for callback testing'
            ];

            $invoiceId = $invoiceModel->insert($invoiceData);

            if ($invoiceId) {
                return $this->response->setJSON([
                    'success' => true,
                    'message' => 'Test invoice created successfully',
                    'data' => [
                        'invoice_id' => $invoiceId,
                        'order_id' => $testOrderId,
                        'customer_name' => $customer['nama_pelanggan'],
                        'amount' => 150000
                    ]
                ]);
            } else {
                return $this->response->setJSON([
                    'success' => false,
                    'message' => 'Failed to create test invoice'
                ]);
            }
        } catch (\Exception $e) {
            return $this->response->setJSON([
                'success' => false,
                'message' => 'Error: ' . $e->getMessage()
            ]);
        }
    }

    /**
     * Simulate payment callback
     */
    public function simulateCallback()
    {
        $orderId = $this->request->getPost('order_id');
        $status = $this->request->getPost('status') ?: 'settlement';

        if (!$orderId) {
            return $this->response->setJSON([
                'success' => false,
                'message' => 'Order ID is required'
            ]);
        }

        // Prepare test notification data
        $testNotification = [
            "transaction_time" => date('Y-m-d H:i:s'),
            "transaction_status" => $status,
            "transaction_id" => "test-" . time(),
            "status_message" => "Success, transaction is found",
            "status_code" => "200",
            "signature_key" => "test-signature-key",
            "settlement_time" => date('Y-m-d H:i:s'),
            "payment_type" => "bank_transfer",
            "order_id" => $orderId,
            "merchant_id" => "test-merchant-id",
            "gross_amount" => "150000.00",
            "fraud_status" => "accept",
            "currency" => "IDR"
        ];

        // Call the callback endpoint
        $callbackUrl = base_url('payment/callback/midtrans');

        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $callbackUrl);
        curl_setopt($ch, CURLOPT_POST, 1);
        curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($testNotification));
        curl_setopt($ch, CURLOPT_HTTPHEADER, [
            'Content-Type: application/json',
            'Content-Length: ' . strlen(json_encode($testNotification))
        ]);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_TIMEOUT, 30);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false); // For local testing

        $response = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        $error = curl_error($ch);
        curl_close($ch);

        return $this->response->setJSON([
            'success' => !$error && $httpCode == 200,
            'message' => $error ?: 'Callback simulation completed',
            'callback_response' => $response,
            'http_code' => $httpCode,
            'test_data' => $testNotification
        ]);
    }

    /**
     * Check invoice status after callback
     */
    public function checkInvoiceStatus()
    {
        $orderId = $this->request->getGet('order_id');

        if (!$orderId) {
            return $this->response->setJSON([
                'success' => false,
                'message' => 'Order ID is required'
            ]);
        }

        $invoiceModel = new InvoiceModel();
        $invoice = $invoiceModel->where('transaction_id', $orderId)->first();

        if (!$invoice) {
            return $this->response->setJSON([
                'success' => false,
                'message' => 'Invoice not found'
            ]);
        }

        // Get payment transaction record
        $paymentTransactionModel = new \App\Models\PaymentTransactionModel();
        $paymentTransaction = $paymentTransactionModel->where('transaction_code', $orderId)->first();

        return $this->response->setJSON([
            'success' => true,
            'invoice' => $invoice,
            'payment_transaction' => $paymentTransaction
        ]);
    }
}
